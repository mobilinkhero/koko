<?php

namespace Corbital\Settings\Http\Middleware;

use Closure;
use Corbital\Settings\Facades\Settings;
use Illuminate\Http\Request;

class ScopeTenantSettings
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId) {
            // Bind the tenant settings manager to the container
            app()->instance('settings', Settings::forTenant($tenantId));
        }

        return $next($request);
    }

    /**
     * Resolve the tenant ID from the request.
     *
     * @return string|int|null
     */
    protected function resolveTenantId(Request $request)
    {
        // Option 1: Subdomain-based identification
        if (config('settings.tenant_subdomain_identification', false)) {
            $subdomain = explode('.', $request->getHost())[0] ?? null;
            if ($subdomain && $subdomain !== 'www') {
                return $subdomain;
            }
        }

        // Option 2: Header-based identification
        $headerName = config('settings.tenant_header_name', 'X-Tenant-ID');
        if ($request->hasHeader($headerName)) {
            return $request->header($headerName);
        }

        // Option 3: Query parameter-based identification
        $paramName = config('settings.tenant_param_name', 'tenant_id');
        if ($request->has($paramName)) {
            return $request->input($paramName);
        }

        // Option 4: Route parameter-based identification
        if ($request->route('tenant')) {
            return $request->route('tenant');
        }

        // Option 5: Session-based identification
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        return null;
    }
}
