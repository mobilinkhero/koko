<?php

namespace App\Livewire\Invoice;

use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    /**
     * Event listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refreshInvoices' => '$refresh',
        'viewInvoice' => 'viewInvoice',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.invoices.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function viewInvoice($invoiceId)
    {
        return redirect()->to(tenant_route('tenant.invoices.show', ['id' => $invoiceId]));
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-invoice-table-a0eyaf-table');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.invoice.invoice-list');
    }
}
