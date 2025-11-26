<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTenantSecurity
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if no tenant or no authenticated user
        if (! Tenant::checkCurrent() || ! Auth::check()) {
            return response()->view('errors.404', [], 404);
        }

        $user = Auth::user();
        $tenant = Tenant::current();

        // Check if user belongs to current tenant or is super admin
        if ($user->tenant_id !== $tenant->id && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if tenant is active and set appropriate warnings
        if ($tenant->status !== 'active' || ($tenant->expires_at && $tenant->expires_at->isPast())) {
            session()->put('tenant_status_warning', [
                'status' => $tenant->status,
                'expires_at' => $tenant->expires_at,
                'message' => $this->getTenantStatusMessage($tenant),
            ]);
            session()->flash('notification', ['type' => 'warning', 'message' => $this->getTenantStatusMessage($tenant)]);

            return redirect()->to(tenant_route('tenant.tickets.index'));
        } else {
            session()->forget('tenant_status_warning');
        }

        return $next($request);
    }

    /**
     * Get appropriate status message for tenant.
     */
    private function getTenantStatusMessage(Tenant $tenant): string
    {
        if ($tenant->expires_at && $tenant->expires_at->isPast()) {
            return 'Your subscription has expired. You can still create support tickets.';
        }

        return match ($tenant->status) {
            'suspended' => 'Your account has been suspended. You can still create support tickets.',
            'expired' => 'Your subscription has expired. You can still create support tickets.',
            'deactive' => 'Your account is currently inactive. You can still create support tickets.',
            default => 'Your account has limited access. You can still create support tickets.'
        };
    }
}
