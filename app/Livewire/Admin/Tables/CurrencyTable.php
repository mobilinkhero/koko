<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Currency;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CurrencyCache;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CurrencyTable extends PowerGridComponent
{
    public string $tableName = 'currency-table-n9y47e-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

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
        return Currency::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('symbol')
            ->add('name');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Symbol', 'symbol')
                ->sortable()
                ->searchable(),

            Column::make('Base Currency', 'is_default')
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0')
                ->bodyAttribute('flex align-center mt-2')
                ->sortable()
                ->searchable(),

            Column::action('Action')
                ->hidden(! checkPermission(['admin.currency.edit', 'admin.currency.delete'])),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Currency $row): array
    {
        $actions = [];

        if (checkPermission('admin.currency.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editCurrency', ['id' => $row->id]);
        }

        if (checkPermission('admin.currency.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('confirmDelete', ['id' => $row->id]);
        }

        return $actions ?? [];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('admin.currency.edit')) {
            $existingCurrency = Currency::query()->where('is_default', '1')->firstOrFail();

            $plans = Plan::where('currency_id', $existingCurrency->id)->get();
            if ($plans->isNotEmpty()) {
                // Check if any of these plans have a subscription
                $planIds = $plans->pluck('id');

                $hasSubscription = Subscription::whereIn('plan_id', $planIds)->exists();

                if ($hasSubscription) {
                    $this->notify([
                        'type' => 'danger',
                        'message' => t('cannot_change_base_currency_subscription_exists'),
                    ]);

                    // Re-enable the switch by refreshing the table
                    $this->dispatch('pg:eventRefresh-'.$this->tableName);

                    return;
                }
            }

            // If setting to true/1/on
            if ($value == 1 || $value == '1' || $value === true || $value === 'true') {
                Currency::query()->update(['is_default' => 0]);

                $currency = Currency::query()->where('id', $id)->firstOrFail();
                $currency->$field = $value;
                $currency->save();

                Plan::where('currency_id', $existingCurrency->id)
                    ->update(['currency_id' => $currency->id]);

                $this->notify([
                    'message' => t('update_base_currency'),
                    'type' => 'success',
                ]);

                $this->dispatch('pg:eventRefresh-'.$this->tableName);
            } else {
                // Don't allow turning off the base currency without selecting another one
                $currentDefaults = Currency::query()->where('is_default', 1)->count();

                if ($currentDefaults <= 1) {
                    $this->notify([
                        'message' => t('must_one_base_currency'),
                        'type' => 'danger',
                    ]);

                    // Re-enable the switch by refreshing the table
                    $this->dispatch('pg:eventRefresh-'.$this->tableName);

                    return;
                }
            }
            CurrencyCache::clearCache();
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type' => 'warning',
            ]);
        }
    }
}
