<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantDeleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::current();

        if ($tenant && $tenant->isDataDeleted()) {
            // Logout the user and redirect to the main login page
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'This account has been permanently deleted and is no longer accessible.');
        }

        return $next($request);
    }
}
