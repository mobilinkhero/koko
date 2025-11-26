<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class StatusTable extends PowerGridComponent
{
    public string $tableName = 'status-table';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    protected array $usedStatusIds = [];

    public function boot()
    {
        $this->loadUsedStatusIds();
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
        $tenantId = tenant_id();

        return Status::query()
            ->selectRaw('statuses.*, (SELECT COUNT(*) FROM statuses i2 WHERE i2.id <= statuses.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', $tenantId);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('row_num') // Add the row number field
            ->add('color', fn ($value) => $value->color === $value->color ? '<span class="inline-flex items-center rounded-full px-2 py-2" style="background-color: '.e($value->color).';"></span>' : 'N/A');
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(t('color'), 'color')
                ->sortable()
                ->searchable(),

            Column::action(t('action'))
                ->hidden(! checkPermission(['tenant.status.edit', 'tenant.status.delete'])),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Status $status)
    {
        $actions = [];

        if (checkPermission('tenant.status.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editStatus', ['statusId' => $status->id]);
        }
        $isStatusUsed = in_array($status->id, $this->usedStatusIds);

        if (checkPermission('tenant.status.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch(
                    $isStatusUsed ? 'notify' : 'confirmDelete',
                    $isStatusUsed
                        ? ['message' => t('status_delete_in_use_notify'), 'type' => 'warning']
                        : ['statusId' => $status->id]
                );
        }

        return $actions ?? [];
    }

    protected function loadUsedStatusIds(): void
    {
        $subdomain = tenant_subdomain();
        $table = $subdomain.'_contacts';

        if (\Schema::hasTable($table)) {
            $this->usedStatusIds = DB::table($table)
                ->select('status_id')
                ->distinct()
                ->pluck('status_id')
                ->toArray();
        }
    }
}
