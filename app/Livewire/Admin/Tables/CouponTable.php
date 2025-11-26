<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CouponTable extends PowerGridComponent
{
    public string $tableName = 'coupon-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->withoutLoading(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function datasource(): Builder
    {
        return Coupon::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('code')
            ->add('name')
            ->add('description')
            ->add('type')
            ->add('value')
            ->add('is_active')
            ->add('usage_limit')
            ->add('usage_count')
            ->add('type_formatted', function (Coupon $model) {
                return $model->type === 'percentage'
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">'.t('percentage').'</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">'.t('fixed_amount').'</span>';
            })
            ->add('value_formatted', function (Coupon $model) {
                return $model->type === 'percentage'
                    ? $model->value.'%'
                    : get_base_currency()->format($model->value);
            })
            ->add('status_formatted', function (Coupon $model) {
                return $model->is_active
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">'.t('active').'</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">'.t('inactive').'</span>';
            })
            ->add('usage_formatted', function (Coupon $model) {
                $usageText = $model->usage_limit
                    ? $model->usage_count.' / '.$model->usage_limit
                    : $model->usage_count.' / ∞';

                return '<button
                    class="text-blue-600 hover:text-blue-800 cursor-pointer font-medium"
                    wire:click="$dispatch(\'showUsageDetails\', {couponId: '.$model->id.'})"
                    title="Click to view usage details"
                >'.$usageText.'</button>';
            })

            ->add('expires_at_formatted', fn (Coupon $model) => $model->expires_at ? $model->expires_at->format('Y-m-d') : '-')
            ->add('created_at_formatted', fn (Coupon $model) => $model->created_at->format('Y-m-d H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Type', 'type_formatted', 'type')
                ->sortable()
                ->searchable()
                ->bodyAttribute('capitalize'),

            Column::make('Value', 'value_formatted', 'value')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status_formatted', 'is_active')
                ->sortable(),

            Column::make('Usage', 'usage_formatted', 'usage_count')
                ->sortable(),

            Column::make('Expires', 'expires_at_formatted')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('code')
                ->operators(['contains']),

            Filter::inputText('name')
                ->operators(['contains']),

            Filter::select('type', 'type')
                ->dataSource(collect([
                    ['value' => 'percentage', 'label' => 'Percentage'],
                    ['value' => 'fixed_amount', 'label' => 'Fixed Amount'],
                ]))
                ->optionValue('value')
                ->optionLabel('label'),

            Filter::boolean('is_active'),
        ];
    }

    public function actions(Coupon $row): array
    {
        $actions = [];

        // Toggle status button
        $statusText = $row->is_active ? t('deactivate') : t('activate');
        $statusClass = $row->is_active
            ? 'inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-warning-600 rounded shadow-sm hover:bg-warning-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-warning-600 justify-center'
            : 'inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-success-600 rounded shadow-sm hover:bg-success-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-success-600 justify-center';

        $actions[] = Button::add('toggle-status')
            ->slot($statusText)
            ->id()
            ->class($statusClass)
            ->dispatch('toggleStatus', ['id' => $row->id]);

        // Edit button
        $actions[] = Button::add('edit')
            ->slot(t('edit'))
            ->id()
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
            ->route('admin.coupons.edit', ['id' => $row->id]);

        // Delete button
        $actions[] = Button::add('delete')
            ->slot(t('delete'))
            ->id()
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
            ->dispatch('confirmDelete', ['id' => $row->id]);

        return $actions ?? [];
    }
}
