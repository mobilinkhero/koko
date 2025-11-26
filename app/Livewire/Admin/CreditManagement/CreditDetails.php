<?php

namespace App\Livewire\Admin\CreditManagement;

use App\Models\CreditTransaction;
use App\Models\Tenant;
use App\Models\TenantCreditBalance;
use Livewire\Component;

class CreditDetails extends Component
{
    public $tenantId;

    public function mount($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function render()
    {
        $tenant = Tenant::findOrFail($this->tenantId);
        $creditHistory = CreditTransaction::where('tenant_id', $this->tenantId)
            ->with(['currency', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        $creditBalances = TenantCreditBalance::where('tenant_id', $this->tenantId)
            ->with(['currency'])
            ->get();

        return view('livewire.admin.credit-management.credit-details', [
            'creditHistory' => $creditHistory,
            'tenant' => $tenant,
            'creditBalances' => $creditBalances,
        ]);
    }
}
