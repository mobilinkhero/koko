<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class EnsureTenantForLivewire
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to Livewire requests
        if (str_starts_with($request->path(), 'livewire')) {
            $tenant = current_tenant();

            if ($tenant) {
                // Make sure the tenant is set as current in Spatie's mechanism
                BaseTenant::checkCurrent();

                if (! BaseTenant::current() || BaseTenant::current()->getKey() !== $tenant->getKey()) {
                    $tenant->makeCurrent();
                }
            }
        }

        return $next($request);
    }
}
