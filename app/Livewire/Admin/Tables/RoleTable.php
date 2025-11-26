<?php

namespace App\Livewire\Admin\Tables;

use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Role;

final class RoleTable extends PowerGridComponent
{
    public string $tableName = 'role-table-7crbpz-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->withoutLoading()
                ->showToggleColumns()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = Role::query()
            ->where('tenant_id', null);

        return $query;
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name', function ($row) {
                return e(truncate_text($row->name, 30)); // Truncate here
            })
            ->add('created_at_formatted', function ($row) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($row->created_at).'">'
                    .\Carbon\Carbon::parse($row->created_at)->diffForHumans(['options' => \Carbon\Carbon::JUST_NOW]).
                    '</span>
                    </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('id'), 'id')
                ->sortable()
                ->searchable(),

            Column::make(t('name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action(t('action'))
                ->hidden(! checkPermission(['admin.roles.edit', 'admin.roles.delete'])),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Role $role): array
    {
        $actions = [];

        if (checkPermission('admin.roles.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editRole', ['roleId' => $role->id]);
        }

        $isUserAssigned = $role->users()->exists();

        if (checkPermission('admin.roles.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch(
                    $isUserAssigned ? 'notify' : 'confirmDelete',
                    $isUserAssigned
                        ? ['message' => t('role_in_use_notify'), 'type' => 'warning']
                        : ['roleId' => $role->id]
                );
        }

        return $actions ?? [];
    }
}
