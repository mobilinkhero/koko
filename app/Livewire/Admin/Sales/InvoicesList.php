<?php

namespace App\Livewire\Admin\Sales;

use Livewire\Component;

class InvoicesList extends Component
{
    public function mount()
    {
        if (! checkPermission('admin.invoices.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-invoices-table-hgitwy-table');
    }

    public function render()
    {
        return view('livewire.admin.sales.invoices-list');
    }
}
