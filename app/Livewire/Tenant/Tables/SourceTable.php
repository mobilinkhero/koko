<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\Source;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class SourceTable extends PowerGridComponent
{
    public string $tableName = 'source-table-3kfsxg-table';

    protected array $usedSourceIds = [];

    public function boot()
    {
        $this->loadUsedSourceIds();
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

        return Source::query()
            ->selectRaw('sources.*, (SELECT COUNT(*) FROM sources i2 WHERE i2.id <= sources.id AND i2.tenant_id = ?) as row_num', [$tenantId])
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
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('name'), 'name')
                ->sortable()
                ->searchable(),

            Column::action(t('action'))
                ->hidden(! checkPermission(['tenant.source.edit', 'tenant.source.delete'])),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Source $source)
    {
        $actions = [];

        if (checkPermission('tenant.source.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editSource', ['sourceId' => $source->id]);
        }

        $isSourceUsed = in_array($source->id, $this->usedSourceIds);

        if (checkPermission('tenant.source.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch(
                    $isSourceUsed ? 'notify' : 'confirmDelete',
                    $isSourceUsed
                        ? ['message' => t('source_in_use_notify'), 'type' => 'warning']
                        : ['sourceId' => $source->id]
                );
        }

        return $actions ?? [];
    }

    protected function loadUsedSourceIds(): void
    {
        $subdomain = tenant_subdomain();
        $table = $subdomain.'_contacts';

        if (\Schema::hasTable($table)) {
            $this->usedSourceIds = DB::table($table)
                ->select('source_id')
                ->distinct()
                ->pluck('source_id')
                ->toArray();
        }
    }
}
