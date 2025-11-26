<?php

namespace App\Livewire\Admin\CreditManagement;

use App\Models\TenantCreditBalance;
use Livewire\Component;

class CreditList extends Component
{
    public $creditBalances;

    public function mount()
    {
        $this->creditBalances = TenantCreditBalance::with(['tenant', 'currency'])->get();
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-credit-table-0f9zyv-table');
    }

    public function render()
    {
        return view('livewire.admin.credit-management.credit-list');
    }
}
