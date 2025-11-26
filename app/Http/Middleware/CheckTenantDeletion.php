<?php

namespace App\Http\Middleware;

use App\Facades\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantDeletion
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::current();

        // If tenant is marked for deletion, show account deleted message
        if ($tenant && $tenant->deleted_date) {
            // Return a view showing account deletion notice
            return response()->view('tenant.account-deleted', [
                'tenant' => $tenant,
                'deletion_date' => $tenant->deleted_date,
            ], 403);
        }

        return $next($request);
    }
}
