<?php

namespace App\Livewire\Admin\Tables;

use App\Models\TenantCreditBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CreditTable extends PowerGridComponent
{
    public string $tableName = 'credit-table-0f9zyv-table';

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
        return TenantCreditBalance::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('customer_name', function ($row) {
                $user = getUserByTenantId($row->tenant_id);

                return $user->firstname.' '.$user->lastname;
            })
            ->add('balance')
            ->add('balance_formatted', function ($row) {
                $subtotal = $row->balance;

                return $subtotal ? get_base_currency()->format($subtotal) : '-';
            })
            ->add('updated_at_formatted', function ($row) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($row->updated_at).'">'
                    .Carbon::parse($row->updated_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Customer', 'customer_name'),
            Column::make('Balance', 'balance_formatted', 'balance')
                ->sortable()
                ->searchable(),
            Column::make('Updated at', 'updated_at_formatted', 'updated_at')
                ->sortable(),
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

    public function actions(TenantCreditBalance $row): array
    {
        $actions = [];
        $actions[] = Button::add('edit')
            ->slot('View Details')
            ->class('inline-flex items-center justify-center px-3 py-1 text-sm border border-info-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-info-100 text-info-700 hover:bg-info-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-300 dark:bg-slate-700 dark:border-slate-500 dark:text-info-400 dark:hover:border-info-600 dark:hover:bg-info-600 dark:hover:text-white dark:focus:ring-offset-slate-800')
            ->route('admin.credit-management.details', [$row->tenant_id]);

        return $actions ?? [];
    }
}
