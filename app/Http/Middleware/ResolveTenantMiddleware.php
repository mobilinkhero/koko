<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantMiddleware
{
    /**
     * Handle an incoming request.
     * Optimized to work better with Spatie multitenancy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenantParam = $request->route('tenant');

            if ($tenantParam) {
                // Use Spatie's tenant finder for consistency
                $tenant = app(\App\Multitenancy\PathTenantFinder::class)->findForRequest($request);

                if (! $tenant) {
                    // Fallback to direct lookup if finder doesn't work
                    $tenant = Tenant::where('subdomain', $tenantParam)->first();
                }

                if ($tenant) {
                    // Always set tenant context, even for expired/inactive tenants
                    // This allows users to login and create tickets
                    $tenant->makeCurrent();

                    // Set tenant status in session for UI warnings
                    if (! $this->isValidTenant($tenant)) {
                        session()->put('tenant_status_warning', [
                            'status' => $tenant->status,
                            'expires_at' => $tenant->expires_at,
                            'message' => $this->getTenantStatusMessage($tenant),
                        ]);
                    } else {
                        session()->forget('tenant_status_warning');
                    }
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            // In production, continue without failing
            if (! config('app.debug')) {
                return $next($request);
            }

            throw $e;
        }
    }

    /**
     * Check if tenant is valid and active.
     */
    private function isValidTenant(Tenant $tenant): bool
    {
        // Check if tenant is active
        if ($tenant->status !== 'active') {
            return false;
        }

        // Check expiration
        if ($tenant->expires_at && $tenant->expires_at->isPast()) {
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
     * Handle requests to invalid tenants (kept for potential future use).
     * Note: Currently we allow access to expired/inactive tenants,
     * but this method can be used for specific restricted actions.
     */
    private function handleInvalidTenant(Tenant $tenant, Request $request): Response
    {
        // For now, we don't block access - just set the tenant context
        // This method is kept for future use if needed
        $tenant->makeCurrent();

        session()->put('tenant_status_error', [
            'status' => $tenant->status,
            'expires_at' => $tenant->expires_at,
            'message' => $this->getTenantStatusMessage($tenant),
        ]);

        return redirect()->route('tenant.dashboard')->with('warning',
            $this->getTenantStatusMessage($tenant)
        );
    }
}
