<?php

namespace Modules\Tickets\Livewire\Client;

use Livewire\Component;
use Modules\Tickets\Models\Ticket;

class TicketsList extends Component
{
    public $confirmingDeletion = false;

    public $ticket_id = null;

    protected $listeners = [
        'confirmDelete' => 'confirmDelete',
        'viewTicket' => 'viewTicket',
    ];

    public function confirmDelete($ticketId)
    {
        $this->ticket_id = $ticketId;
        $this->confirmingDeletion = true;
    }

    public function viewTicket($ticketId)
    {
        return redirect()->to(tenant_route('tenant.tickets.show', [
            'ticket' => $ticketId,
        ]));
    }

    public function delete()
    {
        if (is_array($this->ticket_id) && count($this->ticket_id) !== 0) {
            $selectedIds = $this->ticket_id;
            // dispatch(function () use ($selectedIds) {
            Ticket::whereIn('id', $selectedIds)
                ->chunk(100, function ($tickets) {
                    foreach ($tickets as $ticket) {
                        $ticket->delete();
                    }
                });
            // })->afterResponse();
            $this->ticket_id = null;
            $this->js('window.pgBulkActions.clearAll()');
            $this->notify([
                'type' => 'success',
                'message' => t('tickets_delete_successfully'),
            ]);
        } else {

            $ticket = Ticket::findOrFail($this->ticket_id);
            $this->ticket_id = null;
            $ticket->delete();

            $this->notify([
                'type' => 'success',
                'message' => t('ticket_delete_success'),
            ]);
        }

        $this->confirmingDeletion = false;

        $this->dispatch('pg:eventRefresh-client-tickets-table');
    }

    public function render()
    {
        return view('Tickets::livewire.client.tickets-list');
    }
}
