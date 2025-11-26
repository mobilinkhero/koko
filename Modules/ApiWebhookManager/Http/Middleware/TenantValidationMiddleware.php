<?php

namespace Modules\ApiWebhookManager\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = request()->route('subdomain');
        $tenant_id = Tenant::where('subdomain', $subdomain)->value('id');

        // Check if the user is logged in and has 'admin' user type
        if (! $tenant_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => 'Invalid tenant subdomain',
            ], 500);
        }
        $request->merge(['tenant_id' => $tenant_id]);

        return $next($request);
    }
}
