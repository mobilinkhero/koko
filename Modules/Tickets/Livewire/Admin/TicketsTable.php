<?php

declare(strict_types=1);

namespace Modules\Tickets\Livewire\Admin;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Modules\Tickets\Events\TicketStatusChanged;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Actions;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class TicketsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'admin-tickets-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    // Bulk actions properties
    public bool $showBulkActions = false;

    public string $bulkActionType = '';

    public string $bulkActionValue = '';

    public array $selectedTickets = [];

    // Filter properties
    public $selectedDepartment = null;

    public $selectedStatus = null;

    public $selectedPriority = null;

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->withoutLoading()
                ->showToggleColumns()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage(perPageValues: [10, 25, 50, 100])
                ->showRecordCount(),
            PowerGrid::exportable('all-tickets')
                ->stripTags(true)
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function header(): array
    {
        $buttons = [];

        $alternativePositionClass = ' md:top-[4.5rem] sm:top-[4.75rem] top-[3.6rem] xs:top-[6.5rem] left-[170px] lg:left-[182px] md:left-[211px] sm:left-[207px]';
        $buttonClass = $alternativePositionClass;

        $ticketsCount = Cache::remember('Tickets_count', 60, function () {
            return Ticket::count();
        });

        if ($ticketsCount > 0) {
            $buttons[] = Button::add('bulk-delete')
                ->id()
                ->slot(t('bulk_delete').'(<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class("inline-flex items-center justify-center px-3 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-600 whitespace-nowrap $buttonClass")
                ->dispatch('bulkDelete.'.$this->tableName, []);
        }

        return $buttons;
    }

    public function datasource(): Builder
    {
        $user = Auth::user();
        $userId = (string) $user->id;

        $query = Ticket::query()
            ->with([
                'tenant',
                'department',
                'replies' => fn ($q) => $q->latest()->limit(1),
            ])
            ->select('tickets.*');

        if (! $user->is_admin) {
            $query->where(function ($subQuery) use ($userId) {
                // Check in tickets.assignee_id (JSON field)
                $subQuery->where(function ($q) use ($userId) {
                    $q->whereNotNull('tickets.assignee_id')
                        ->where('tickets.assignee_id', '!=', '')
                        ->whereJsonContains('tickets.assignee_id', $userId);
                });

                // Check in department.assignee_id (stringified JSON with integers)
                $subQuery->orWhereHas('department', function ($q) use ($userId) {
                    $pattern = '[[:<:]]'.$userId.'[[:>:]]'; // word boundary

                    $q->whereNotNull('assignee_id')
                        ->where('assignee_id', '!=', '')
                        ->whereRaw('assignee_id REGEXP ?', [
                            '\\['.$userId.'\\]'     // exactly [5]
                                .'|\\['.$userId.','
                                .'|,'.$userId.','
                                .'|,'.$userId.'\\]',
                        ]);
                });
            });
        }

        if ($this->selectedDepartment) {
            $query->where('department_id', $this->selectedDepartment);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->selectedPriority) {
            $query->where('priority', $this->selectedPriority);
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'tenant' => [
                'company_name',
                'subdomain',
            ],
            'tenantStaff' => [
                'firstname',
                'lastname',
                'email',
            ],
            'department' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('ticket_id', fn (Ticket $ticket) => '<a href="'.route('admin.tickets.show', $ticket).'" class="font-mono text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">'.$ticket->ticket_id.'</a>')
            ->add('subject', fn (Ticket $ticket) => '<a href="'.route('admin.tickets.show', $ticket).'" class="text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400">'.e($ticket->subject).'</a>')
            ->add('company_name', fn (Ticket $ticket) => $ticket->tenant->company_name ?? 'N/A')
            ->add('priority', fn (Ticket $ticket) => view('Tickets::components.ticket-priority-badge', ['priority' => $ticket->priority]))
            ->add('status', fn (Ticket $ticket) => view('Tickets::components.ticket-status-badge', ['status' => $ticket->status]))
            ->add('created_at_formatted', function ($ticket) {
                return '<div class="relative group">
                <span class="cursor-default" data-tippy-content="'.format_date_time($ticket->created_at).'">'
                    .\Carbon\Carbon::parse($ticket->created_at)->diffForHumans(['options' => \Carbon\Carbon::JUST_NOW]).
                    '</span>
            </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(t('ID'))
                ->field('ticket_id')
                ->searchable()
                ->sortable(),

            Column::add()
                ->title(t('subject'))
                ->field('subject')
                ->searchable()
                ->sortable(),

            Column::add()
                ->title(t('tenant'))
                ->field('company_name')
                ->searchable()
                ->sortable(),

            Column::add()
                ->title(t('priority'))
                ->field('priority')
                ->sortable(),

            Column::add()
                ->title(t('status'))
                ->field('status')
                ->sortable(),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
                ->title(t('actions'))
                ->headerAttribute('text-center', 'width: 100px')
                ->bodyAttribute('text-center'),
        ];
    }

    public function filters(): array
    {
        // Cache departments for better performance - 5 minutes
        $departments = Cache::remember('ticket_filter_departments', 300, function () {
            return Department::where('status', true)
                ->select(['id', 'name'])
                ->get()
                ->map(function ($dept) {
                    return ['id' => $dept->id, 'name' => $dept->name];
                })
                ->toArray();
        });

        // Cache tenants for better performance - 5 minutes
        $tenants = Cache::remember('ticket_filter_tenants', 300, function () {
            return Tenant::whereHas('tickets')
                ->select(['id', 'company_name'])
                ->get()
                ->map(function ($tenant) {
                    return ['id' => $tenant->id, 'company_name' => $tenant->company_name];
                })
                ->toArray();
        });

        return [
            Filter::select('status', 'tickets.status')
                ->dataSource([
                    ['id' => 'open', 'name' => 'Open'],
                    ['id' => 'answered', 'name' => 'Answered'],
                    ['id' => 'on_hold', 'name' => 'On Hold'],
                    ['id' => 'closed', 'name' => 'Closed'],
                ])
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::select('priority', 'tickets.priority')
                ->dataSource([
                    ['id' => 'low', 'name' => 'Low'],
                    ['id' => 'medium', 'name' => 'Medium'],
                    ['id' => 'high', 'name' => 'High'],
                    ['id' => 'urgent', 'name' => 'Urgent'],
                ])
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::select('department_id')
                ->dataSource($departments)
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::select('tenant_id')
                ->dataSource($tenants)
                ->optionValue('id')
                ->optionLabel('company_name'),

            Filter::datepicker('created_at', 'tickets.created_at'),
        ];
    }

    public function actions(Ticket $row): array
    {
        return [
            Button::add('view')
                ->slot('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>')
                ->id('view')
                ->class('inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 bg-primary-100 rounded hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-primary-900 dark:text-primary-200')
                ->tooltip('View Ticket')
                ->route('admin.tickets.show', ['ticket' => $row->id]),

            Button::add('quick-status')
                ->slot('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>')
                ->id('quick-status')
                ->class('inline-flex items-center px-2 py-1 text-xs font-medium text-success-600 bg-success-100 rounded hover:bg-success-200 focus:outline-none focus:ring-2 focus:ring-success-500 dark:bg-success-900 dark:text-success-200 ml-1')
                ->tooltip('Quick Status Change')
                ->dispatch('quickStatusChange', ['data' => $row])
                ->can($row->status !== 'closed', 'Ticket is closed'),

            Button::add('delete')
                ->slot('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 011-1h4a1 1 0 011 1m-7 0h8" />
                </svg>')
                ->id('delete')
                ->class('inline-flex items-center px-2 py-1 text-xs font-medium text-danger-600 bg-danger-100 rounded hover:bg-danger-200 focus:outline-none focus:ring-2 focus:ring-danger-500 dark:bg-danger-900 dark:text-danger-200 ml-1')
                ->tooltip('Delete Ticket')
                ->dispatch('confirmDelete', ['ticketId' => $row->id]),
        ];
    }

    #[On('quickStatusChange')]
    public function quickStatusChange(array $data): void
    {
        $id = $data['id'];
        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status;

        $statusFlow = [
            'open' => 'answered',
            'answered' => 'closed',
            'on_hold' => 'open',
            'closed' => 'open',
        ];

        $newStatus = $statusFlow[$ticket->status] ?? 'open';

        $ticket->update([
            'status' => $newStatus,
            'admin_viewed' => true,
        ]);

        // Dispatch the TicketStatusChanged event
        event(new TicketStatusChanged($ticket, $oldStatus, true));

        $this->notify([
            'type' => 'success',
            'message' => 'Ticket #'.$ticket->ticket_id.' status changed to '.ucfirst($newStatus),
        ]);
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $selectedIds = $this->checkboxValues;
        if (! empty($selectedIds) && count($selectedIds) !== 0) {
            $this->dispatch('confirmDelete', $selectedIds);
            $this->checkboxValues = [];
        } else {
            $this->notify(['type' => 'danger', 'message' => t('no_contact_selected')]);
        }
    }

    public function getBulkActionTitle(): string
    {
        return match ($this->bulkActionType) {
            'status' => t('change_status'),
            'priority' => t('change_priority'),
            'assign' => t('assign_tickets'),
            'delete' => t('delete_tickets'),
            default => '',
        };
    }

    public function addAssignees(array $userIds)
    {
        if (empty($this->selectedTickets) || empty($userIds)) {
            return;
        }

        $tickets = Ticket::whereIn('id', $this->selectedTickets)->get();

        foreach ($tickets as $ticket) {
            $currentAssignees = $ticket->assignee_id ?? [];
            $newAssignees = array_unique(array_merge($currentAssignees, $userIds));
            $ticket->update(['assignee_id' => $newAssignees]);

            // Trigger assignment notification
            event(new \Modules\Tickets\Events\TicketAssigned($ticket));
        }

        // Clear UI state
        $this->showBulkActions = false;
        $this->selectedTickets = [];
        $this->dispatch('refreshTicket');

        // Show success notification
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => t('assignees_added_successfully'),
        ]);
    }

    public function removeAssignees(array $userIds)
    {
        if (empty($this->selectedTickets) || empty($userIds)) {
            return;
        }

        $tickets = Ticket::whereIn('id', $this->selectedTickets)->get();

        foreach ($tickets as $ticket) {
            $currentAssignees = $ticket->assignee_id ?? [];
            $newAssignees = array_values(array_diff($currentAssignees, $userIds));
            $ticket->update(['assignee_id' => $newAssignees]);

            // Trigger assignment notification for remaining assignees
            if (! empty($newAssignees)) {
                event(new \Modules\Tickets\Events\TicketAssigned($ticket));
            }
        }

        // Clear UI state
        $this->showBulkActions = false;
        $this->selectedTickets = [];
        $this->dispatch('refreshTicket');

        // Show success notification
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => t('assignees_removed_successfully'),
        ]);
    }
}
