<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

trait WithTenantContext
{
    public $tenantId;

    public function mountWithTenantContext()
    {
        $tenant = current_tenant();
        $this->tenantId = $tenant ? $tenant->id : null;

        if (! $this->tenantId && (session()->has('current_tenant_id') || ! empty(Auth::user()->tenant_id))) {
            $this->tenantId = session('current_tenant_id') ?? Auth::user()->tenant_id;
        }
    }

    public function getTenantProperty()
    {
        if (! $this->tenantId) {
            return null;
        }

        return Tenant::find($this->tenantId);
    }

    public function bootWithTenantContext()
    {
        // This will run before any Livewire-specific requests
        if (! $this->tenantId && $tenant = current_tenant()) {
            $this->tenantId = $tenant->id;
        }

        // If we have a tenant ID but no current tenant, make it current
        if ($this->tenantId && ! Tenant::checkCurrent()) {
            $tenant = Tenant::find($this->tenantId);
            if ($tenant) {
                $tenant->makeCurrent();
            }
        }
    }
}
