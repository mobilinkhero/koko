<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\BotFlow;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class FlowBotTable extends PowerGridComponent
{
    public string $tableName = 'flow-bot-table-9nci5n-table';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    protected FeatureService $featureLimitChecker;

    public function boot(FeatureService $featureLimitChecker): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $tenantId = tenant_id();

        return BotFlow::query()
            ->selectRaw('bot_flows.*, (SELECT COUNT(*) FROM bot_flows i2 WHERE i2.id <= bot_flows.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', $tenantId);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('tenant_id')
            ->add('name')
            ->add('description')
            ->add('flow_data')
            ->add('is_active')
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

            Column::make('Description', 'description')
                ->sortable()
                ->searchable()
                ->bodyAttribute('truncate text-wrap max-w-[400px]'),

            Column::make('Is active', 'is_active')
                ->toggleable(hasPermission: true, trueLabel: t('public'))
                ->sortable()
                ->searchable()
                ->bodyAttribute('flex align-center mt-2'),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(BotFlow $row): array
    {
        $actions = [];
        if (checkPermission('tenant.bot_flow.view') || checkPermission('tenant.bot_flow.create')) {
            $actions[] = Button::add('flow')
                ->slot(t('flow'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-success-600 rounded shadow-sm hover:bg-success-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-success-600 justify-center')
                ->dispatch('editRedirect', ['flowId' => $row->id]);
        }
        if (checkPermission('tenant.bot_flow.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editFlow', ['flowId' => $row->id]);
        }
        if (checkPermission('tenant.bot_flow.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('confirmDelete', ['flowId' => $row->id]);
        }

        return $actions ?? [];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        $botflow = BotFlow::find($id);
        if ($botflow) {
            $botflow->is_active = ($value === '1') ? 1 : 0;
            $botflow->save();

            $statusMessage = $botflow->is_active
                ? t('bot_flow_activate')
                : t('bot_flow_deactivate');

            $this->notify([
                'message' => $statusMessage,
                'type' => 'success',
            ]);
        }
    }
}
