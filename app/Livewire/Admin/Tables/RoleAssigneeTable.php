<?php

namespace App\Livewire\Admin\Tables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RoleAssigneeTable extends PowerGridComponent
{
    public string $tableName = 'role-assignee-table-pkhhln-table';

    public $role_id;

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return $this->role_id
            ? User::where('role_id', $this->role_id)->where('user_type', 'admin')
            : User::whereRaw('1 = 0');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()

            ->add('firstname', function ($model) {
                return $model->firstname.' '.$model->lastname;
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'firstname')
                ->searchable()
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }
}
