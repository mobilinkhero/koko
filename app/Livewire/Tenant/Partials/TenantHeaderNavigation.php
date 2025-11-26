<?php

namespace App\Livewire\Tenant\Partials;

use Livewire\Component;

class TenantHeaderNavigation extends Component
{
    /**
     * The name of the tenant.
     *
     * @var string
     */
    public function tenant_cache($tenant_id)
    {

        clear_tenant_cache($tenant_id);
        $this->notify(['type' => 'success', 'message' => t('cache_cleared_successfully')]);
    }

    public function render()
    {
        return view('livewire.tenant.partials.tenant-header-navigation');
    }
}
