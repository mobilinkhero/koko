<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class GroupsTable extends PowerGridComponent
{
    public string $tableName = 'groups-table-q5rszw-table';

    protected array $usedGroupIds = [];

    public function boot()
    {
        $this->loadusedGroupIds();
    }

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
        $tenantId = tenant_id();

        return Group::query()
            ->selectRaw('*, (SELECT COUNT(*) FROM `groups` i2 WHERE i2.id <= groups.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', $tenantId);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('row_num')
            ->add('id')
            // ->add('name')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [

            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::action('action'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Group $group)
    {
        $actions = [];

        if (checkPermission('tenant.source.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-indigo-600 rounded shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 justify-center')
                ->dispatch('editGroup', ['groupId' => $group->id]);
        }

        $isGroupUsed = in_array($group->id, $this->usedGroupIds);

        if (checkPermission('tenant.source.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch(
                    $isGroupUsed ? 'notify' : 'confirmDelete',
                    $isGroupUsed
                        ? ['message' => t('group_in_use_notify'), 'type' => 'warning']
                        : ['groupId' => $group->id]
                );
        }

        return $actions ?? [];
    }

    protected function loadUsedGroupIds(): void
    {
        $subdomain = tenant_subdomain();
        $table = $subdomain.'_contacts';

        $rawGroupIds = DB::table($table)
            ->pluck('group_id')
            ->toArray();

        $this->usedGroupIds = collect($rawGroupIds)
            ->flatMap(function ($item) {
                return is_string($item) ? json_decode($item, true) : (array) $item;
            })
            ->unique()
            ->values()
            ->toArray();

    }
}
