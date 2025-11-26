<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class SimplifiedTenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Use Spatie's tenant finder for consistent tenant detection
            $tenant = app(\App\Multitenancy\PathTenantFinder::class)->findForRequest($request);

            if ($tenant) {
                // Always allow access but show warnings for non-active tenants
                $tenant->makeCurrent();

                // Check tenant status and set warnings
                if (! $this->isActiveTenant($tenant)) {
                    session()->put('tenant_status_warning', [
                        'status' => $tenant->status,
                        'expires_at' => $tenant->expires_at,
                        'message' => $this->getTenantStatusMessage($tenant),
                    ]);
                } else {
                    session()->forget('tenant_status_warning');
                }

                // Optimize path rewriting with better logic
                $this->rewriteRequestPath($request, $tenant->subdomain);

                // Log tenant access for debugging (only in debug mode)
                if (config('app.debug')) {
                    Log::debug('Tenant context established', [
                        'tenant_id' => $tenant->id,
                        'subdomain' => $tenant->subdomain,
                        'original_path' => $request->getPathInfo(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Tenant middleware error', [
                'error' => $e->getMessage(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            // In production, continue without tenant context rather than failing
            if (! config('app.debug')) {
                return $next($request);
            }

            throw $e;
        }
    }

    /**
     * Check if tenant is active and not expired.
     */
    private function isActiveTenant(Tenant $tenant): bool
    {
        // Check if tenant is active
        if ($tenant->status !== 'active') {
            return false;
        }

        // Check expiration if the tenant has an expiry date
        if ($tenant->expires_at && $tenant->expires_at->isPast()) {
            // Update status to expired if not already done
            if ($tenant->status !== 'expired') {
                $tenant->update(['status' => 'expired']);
            }

            return false;
        }

        return true;
    }

    /**
     * Get appropriate status message for tenant.
     */
    private function getTenantStatusMessage(Tenant $tenant): string
    {
        if ($tenant->expires_at && $tenant->expires_at->isPast()) {
            return 'Your subscription has expired. Please renew to continue using all features.';
        }

        return match ($tenant->status) {
            'suspended' => 'Your account has been suspended. Please contact support.',
            'expired' => 'Your subscription has expired. Please renew to continue using all features.',
            'inactive' => 'Your account is currently inactive. Please contact support.',
            default => 'Your account has limited access. Please contact support.'
        };
    }

    /**
     * Handle invalid tenant scenarios.
     */
    private function handleInvalidTenant(Tenant $tenant, Request $request): Response
    {
        Log::info('Accessing invalid tenant', [
            'tenant_id' => $tenant->id,
            'subdomain' => $tenant->subdomain,
            'status' => $tenant->status,
            'expires_at' => $tenant->expires_at?->toDateTimeString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Try to redirect to appropriate status page
        try {
            return redirect()->route("tenant.{$tenant->status}", ['tenant' => $tenant->subdomain]);
        } catch (\Exception $e) {
            // Fallback to a generic suspended page or 404
            Log::warning('No route found for tenant status', [
                'status' => $tenant->status,
                'tenant_id' => $tenant->id,
            ]);

            return response()->view('errors.tenant-unavailable', [
                'tenant' => $tenant,
                'status' => $tenant->status,
            ], 503);
        }
    }

    /**
     * Rewrite request path to remove tenant subdomain prefix.
     * Optimized for better performance and edge case handling.
     */
    private function rewriteRequestPath(Request $request, string $subdomain): void
    {
        $path = $request->path();
        $segments = explode('/', trim($path, '/'));

        // Only rewrite if the first segment matches the tenant subdomain
        if (! empty($segments) && $segments[0] === $subdomain) {
            // Remove the tenant subdomain from path
            array_shift($segments);

            // Build new path
            $newPath = '/'.implode('/', $segments);

            // Ensure we don't end up with empty path
            if ($newPath === '/' && count($segments) === 0) {
                $newPath = '/';
            }

            // Update both REQUEST_URI and PathInfo for consistency
            $queryString = $request->getQueryString();
            $fullUri = $newPath.($queryString ? '?'.$queryString : '');

            $request->server->set('REQUEST_URI', $fullUri);

            // Laravel's setPathInfo method for proper routing
            if (method_exists($request, 'setPathInfo')) {
                $request->setPathInfo($newPath);
            }

            // Update the path info in the request for Laravel's router
            $request->headers->set('X-Original-Path', $path);
            $request->headers->set('X-Tenant-Subdomain', $subdomain);
        }
    }
}
