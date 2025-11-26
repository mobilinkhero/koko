<?php

namespace Modules\Tickets\Http\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;

class TicketDetails extends Component
{
    public Ticket $ticket;

    public $status;

    public $priority;

    public $department_id;

    public $showAssigneeModal = false;

    public $availableAssignees = [];

    protected $listeners = ['refreshTicket'];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->status = $ticket->status;
        $this->priority = $ticket->priority;
        $this->department_id = $ticket->department_id;
        $this->loadAvailableAssignees();
    }

    public function loadAvailableAssignees()
    {
        $this->availableAssignees = User::where('user_type', 'admin')
            ->whereNotIn('id', $this->ticket->assignee_id ?? [])
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function showAssigneeModal()
    {
        $this->showAssigneeModal = true;
    }

    public function addAssignee($userId)
    {
        $currentAssignees = $this->ticket->assignee_id ?? [];
        $currentAssignees[] = $userId;
        $this->ticket->update(['assignee_id' => array_unique($currentAssignees)]);

        $this->showAssigneeModal = false;
        $this->loadAvailableAssignees();
        $this->emit('refreshTicket');
    }

    public function removeAssignee($userId)
    {
        $currentAssignees = $this->ticket->assignee_id ?? [];
        $currentAssignees = array_filter($currentAssignees, fn ($id) => $id != $userId);
        $this->ticket->update(['assignee_id' => array_values($currentAssignees)]);

        $this->loadAvailableAssignees();
        $this->emit('refreshTicket');
    }

    public function changeStatus()
    {
        $this->ticket->update(['status' => $this->status]);
        $this->emit('refreshTicket');
    }

    public function changePriority()
    {
        $this->ticket->update(['priority' => $this->priority]);
        $this->emit('refreshTicket');
    }

    public function changeDepartment()
    {
        $this->ticket->update(['department_id' => $this->department_id]);
        $this->emit('refreshTicket');
    }

    public function refreshTicket()
    {
        $this->ticket->refresh();
    }

    public function render()
    {
        $departments = Department::active()->get();

        return view('Tickets::livewire.admin.ticket-details', [
            'departments' => $departments,
        ]);
    }
}
