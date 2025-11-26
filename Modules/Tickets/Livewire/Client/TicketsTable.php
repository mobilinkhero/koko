<?php

namespace Modules\Tickets\Livewire\Client;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class TicketsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'client-tickets-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

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
                ->showPerPage()
                ->showRecordCount(),
            PowerGrid::exportable('my-tickets')
                ->stripTags(true)
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Builder
    {
        return Ticket::query()
            ->select([
                'tickets.*',
                'departments.name as department_name',
            ])
            ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->with(['assignedUsers', 'department'])
            ->withCount('replies');
    }

    public function relationSearch(): array
    {
        return [
            'department' => [
                'name',
            ],
            'tenant' => [
                'company_name',
            ],
            'tenantStaff' => [
                'firstname',
                'lastname',
                'email',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('ticket_id', function (Ticket $ticket) {
                return '<a href="'.tenant_route('tenant.tickets.show', ['ticket' => $ticket->id]).'" class="text-primary-600 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400">'.e($ticket->ticket_id).'</a>';
            })
            ->add('subject', function (Ticket $ticket) {
                return '<a href="'.tenant_route('tenant.tickets.show', ['ticket' => $ticket->id]).'" class="text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400">'.e($ticket->subject).'</a>';
            })
            ->add('priority', fn (Ticket $ticket) => view('Tickets::components.ticket-priority-badge', ['priority' => $ticket->priority]))
            ->add('status', fn (Ticket $ticket) => view('Tickets::components.ticket-status-badge', ['status' => $ticket->status]))
            ->add('department_name', fn (Ticket $ticket) => $ticket->department->name ?? 'N/A')
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
                ->title(t('department'))
                ->field('department_name')
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
                ->field('actions')
                ->headerAttribute('text-center', 'width: 100px;')
                ->bodyAttribute('text-center'),
        ];
    }

    public function filters(): array
    {
        static $departments = null;
        if ($departments === null) {
            // Cache departments for the request to avoid duplicate queries
            $departments = Department::where('status', true)->get(['id', 'name'])->map(function ($dept) {
                return ['id' => $dept->id, 'name' => $dept->name];
            })->toArray();
        }

        return [
            Filter::select('status')
                ->dataSource([
                    ['id' => 'open', 'name' => 'Open'],
                    ['id' => 'in_progress', 'name' => 'In Progress'],
                    ['id' => 'resolved', 'name' => 'Resolved'],
                    ['id' => 'closed', 'name' => 'Closed'],
                ])
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::select('priority')
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

            Filter::datepicker('created_at'),
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
                ->dispatch('viewTicket', [$row->id]),
        ];
    }

    #[\Livewire\Attributes\On('closeTicket')]
    public function closeTicket(array $data): void
    {
        $id = $data['id'];
        $ticket = Ticket::where('id', $id)
            ->where('tenant_id', Auth::id())
            ->firstOrFail();

        if ($ticket->status !== 'closed') {
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            $this->notification()->success(
                'Ticket Closed',
                'Ticket #'.$ticket->ticket_number.t(' has_been_closed_successfully')
            );
        } else {
            $this->notification()->warning(
                'Already Closed',
                t('this_ticket_already_closed')
            );
        }
    }
}
