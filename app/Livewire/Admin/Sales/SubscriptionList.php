<?php

namespace App\Livewire\Admin\Sales;

use Livewire\Component;

class SubscriptionList extends Component
{
    public function mount()
    {
        if (! checkPermission('admin.subscription.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-subscription-table-t5n8qk-table');
    }

    public function render()
    {
        return view('livewire.admin.sales.subscription-list');
    }
}
