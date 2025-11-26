<?php

namespace Modules\Tickets\Livewire\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\DepartmentTranslation;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class DepartmentsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'departments-table';

    public string $sortField = 'name';

    public string $sortDirection = 'ASC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->withoutLoading()
                ->showToggleColumns()
                ->showSearchInput()
                ->includeViewOnTop('Tickets::admin.departments.table-header'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
            PowerGrid::exportable('departments')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Builder
    {
        return Department::query()
            ->withCount('tickets')
            ->select('departments.*');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('description')
            ->add('status')
            ->add('status_formatted', function ($department) {
                return match ($department->status) {
                    'active' => '<span class="badge bg-success">Active</span>',
                    'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                    default => '<span class="badge bg-light">'.ucfirst($department->status).'</span>',
                };
            })
            ->add('tickets_count')
            ->add('created_at')
            ->add('created_at_formatted', fn ($department) => Carbon::parse($department->created_at)->format('M d, Y H:i'))
            ->add('updated_at')
            ->add('updated_at_formatted', fn ($department) => Carbon::parse($department->updated_at)->diffForHumans());
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Description', 'description')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status_formatted')
                ->sortable(),

            Column::make('Tickets', 'tickets_count')
                ->sortable(),

            Column::make('Created', 'created_at_formatted')
                ->sortable(),

            Column::make('Updated', 'updated_at_formatted')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('status')
                ->dataSource([
                    ['id' => 'active', 'name' => 'Active'],
                    ['id' => 'inactive', 'name' => 'Inactive'],
                ])
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::datepicker('created_at'),
        ];
    }

    public function actions(Department $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="fas fa-edit"></i>')
                ->class('btn btn-sm btn-outline-primary')
                ->tooltip('Edit Department')
                ->emit('openEditModal', ['departmentId' => $row->id]),

            Button::add('translations')
                ->slot('<i class="fas fa-language"></i>')
                ->class('btn btn-sm btn-outline-info')
                ->tooltip('Manage Translations')
                ->emit('openTranslationsModal', ['departmentId' => $row->id]),

            Button::add('toggle-status')
                ->slot($row->status === 'active' ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>')
                ->class($row->status === 'active' ? 'btn btn-sm btn-outline-warning' : 'btn btn-sm btn-outline-success')
                ->tooltip($row->status === 'active' ? 'Deactivate' : 'Activate')
                ->dispatch('toggleStatus', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fas fa-trash"></i>')
                ->class('btn btn-sm btn-outline-danger')
                ->tooltip('Delete')
                ->confirm('Are you sure you want to delete this department? This action cannot be undone.')
                ->dispatch('deleteDepartment', ['id' => $row->id]),
        ];
    }

    public function actionRules(Department $row): array
    {
        return [
            // Hide delete button if department has tickets
            Button::hide('delete', fn () => $row->tickets_count > 0),
        ];
    }

    #[\Livewire\Attributes\On('toggleStatus')]
    public function toggleStatus(int $id): void
    {
        $department = Department::findOrFail($id);
        $newStatus = $department->status === 'active' ? 'inactive' : 'active';

        $department->update(['status' => $newStatus]);

        $this->notification()->success(
            $title = 'Status Updated!',
            $description = t('department_status_changed ').ucfirst($newStatus)
        );
    }

    #[\Livewire\Attributes\On('deleteDepartment')]
    public function deleteDepartment(int $id): void
    {
        $department = Department::findOrFail($id);

        // Check if department has tickets
        if ($department->tickets()->count() > 0) {
            $this->notification()->error(
                $title = 'Cannot Delete!',
                $description = t('department_has_tickets_assigned')
            );

            return;
        }

        // Delete translations first
        DepartmentTranslation::where('department_id', $id)->delete();

        // Delete the department
        $department->delete();

        $this->notification()->success(
            $title = 'Department Deleted!',
            $description = t('department_deleted_successfully')
        );
    }

    // Bulk Actions
    public function bulkDelete(): void
    {
        if (empty($this->checkboxValues)) {
            $this->notification()->warning(t('no_departments_selected'));

            return;
        }

        // Check if any selected departments have tickets
        $departmentsWithTickets = Department::whereIn('id', $this->checkboxValues)
            ->withCount('tickets')
            ->having('tickets_count', '>', 0)
            ->count();

        if ($departmentsWithTickets > 0) {
            $this->notification()->error(
                $title = 'Cannot Delete!',
                $description = t('selected_departments_have_tickets')
            );

            return;
        }

        // Delete translations first
        DepartmentTranslation::whereIn('department_id', $this->checkboxValues)->delete();

        // Delete departments
        Department::whereIn('id', $this->checkboxValues)->delete();

        $this->notification()->success(
            $title = 'Departments Deleted!',
            $description = count($this->checkboxValues).t(' department_deleted_successfully')
        );

        $this->checkboxValues = [];
    }

    public function bulkActivate(): void
    {
        if (empty($this->checkboxValues)) {
            $this->notification()->warning(t('no_departments_selected'));

            return;
        }

        Department::whereIn('id', $this->checkboxValues)->update(['status' => 'active']);

        $this->notification()->success(
            $title = 'Departments Activated!',
            $description = count($this->checkboxValues).t(' departments_have_been_activated')
        );

        $this->checkboxValues = [];
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->checkboxValues)) {
            $this->notification()->warning(t('no_departments_selected'));

            return;
        }

        Department::whereIn('id', $this->checkboxValues)->update(['status' => 'inactive']);

        $this->notification()->success(
            $title = 'Departments Deactivated!',
            $description = count($this->checkboxValues).t('departments_have_been_deactivated')
        );

        $this->checkboxValues = [];
    }
}
