<?php

namespace App\Livewire\Admin\Tables;

use Illuminate\Database\Eloquent\Builder;
use Modules\Tickets\Models\Department;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class DepartmentTable extends PowerGridComponent
{
    public string $tableName = 'department-table-l3bdsm-table';

    public bool $deferLoading = true;

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $showFilters = false;

    public string $loadingComponent = 'components.custom-loading';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->withoutLoading(),

            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Department::query()->withCount('tickets');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('description', function ($row) {
                return e(truncate_text($row->description, 50));
            })
            ->add('status')
            ->add('status_formatted', function ($department) {
                return $department->status ?
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">Active</span>' :
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200">Inactive</span>';
            })
            ->add('tickets_count')
            ->add('assignees_formatted', function ($department) {
                if (empty($department->assignee_id)) {
                    return '<span class="text-gray-400">--</span>';
                }

                // Decode JSON string to array
                $userIds = json_decode(is_array($department->assignee_id) ? json_encode($department->assignee_id) : $department->assignee_id, true) ?: [];

                if (empty($userIds)) {
                    return '<span class="text-gray-400">--</span>';
                }

                // Get the first 3 assignees
                $assignees = \App\Models\User::whereIn('id', array_slice($userIds, 0, 3))
                    ->select(['id', 'firstname', 'lastname'])
                    ->get();

                $totalAssignees = count($userIds);

                $html = '<div class="flex flex-col">';

                foreach ($assignees as $assignee) {
                    $html .= '<span class="text-xs">'.e($assignee->firstname.' '.$assignee->lastname).'</span>';
                }

                if ($totalAssignees > 3) {
                    $html .= '<span class="text-xs text-gray-500">+'.($totalAssignees - 3).' more</span>';
                }

                $html .= '</div>';

                return $html;
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name')
                ->searchable()
                ->sortable(),

            Column::make('Description', 'description')
                ->searchable()
                ->sortable(),

            Column::make('Status', 'status_formatted', 'status')
                ->sortable(),

            Column::make('Tickets', 'tickets_count')
                ->sortable(),

            Column::make('Assignees', 'assignees_formatted'),

            Column::make(t('active'), 'status')
                ->searchable()
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0')
                ->bodyAttribute('flex align-center mt-2'),

            Column::action(t('action'))
                ->hidden(! checkPermission(['admin.department.edit', 'admin.department.delete'])),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::boolean('status')
                ->label('Active', 'Inactive'),
        ];
    }

    public function actions(Department $department): array
    {
        $actions = [];

        if (checkPermission('admin.department.edit')) {

            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editDepartment', ['id' => $department->id]);
        }

        if (checkPermission('admin.department.delete')) {

            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('confirmDelete', ['id' => $department->id]);
        }

        return $actions ?? [];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('admin.department.edit')) {
            $department = Department::find($id);
            if ($department) {
                $department->status = ($value === '1') ? 1 : 0;
                $department->save();

                $statusMessage = $department->status
                    ? t('department_is_activated')
                    : t('department_is_deactivated');

                $this->notify([
                    'message' => $statusMessage,
                    'type' => 'success',
                ]);
            }
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type' => 'warning',
            ]);
        }
    }
}
