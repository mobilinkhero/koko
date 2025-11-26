<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TransactionTable extends PowerGridComponent
{
    public string $tableName = 'transaction-table-twkqil-table';

    public string $loadingComponent = 'components.custom-loading';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    protected $listeners = ['refreshTable' => '$refresh'];

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showToggleColumns()
                ->showSearchInput()
                ->withoutLoading(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Transaction::query()
            ->with(['invoice', 'currency'])
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 0
                    WHEN status = 'success' THEN 1
                    WHEN status = 'failed' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'desc');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('customer_name', function ($transaction) {
                if (! $transaction->invoice?->tenant_id) {
                    return 'N/A';
                }

                $user = getUserByTenantId($transaction->invoice->tenant_id);

                if (! $user) {
                    return 'N/A';
                }

                return ($user->firstname ?? '').' '.($user->lastname ?? '');
            })
            ->add('status', function ($transaction) {
                return match ($transaction->status) {
                    Transaction::STATUS_SUCCESS => '<span class="bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">Success</span>',
                    Transaction::STATUS_FAILED => '<span class="bg-danger-100 text-danger-800 dark:text-danger-400 dark:bg-danger-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">Failed</span>',
                    Transaction::STATUS_PENDING => '<span class="bg-warning-100 text-warning-800 dark:text-warning-400 dark:bg-warning-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">Pending</span>',
                    default => '<span class="bg-gray-100 text-gray-800 dark:text-gray-400 dark:bg-gray-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">'.ucfirst($transaction->status).'</span>',
                };
            })

            ->add('type', function ($transaction) {
                $color = match ($transaction->type) {
                    'stripe' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
                    'offline' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
                };

                return '<span class="'.$color.' px-2.5 py-0.5 rounded-full text-xs font-medium">'.ucfirst($transaction->type).'</span>';
            })

            ->add('amount_with_tax', function ($transaction) {
                return $this->getInvoiceTotalWithTax($transaction);
            })

            ->add('amount_formatted', function ($transaction) {
                $subtotal = $transaction->invoice?->subTotal();

                return $subtotal ? get_base_currency()->format($subtotal) : '-';
            })

            ->add('created_at_formatted', function ($transaction) {
                return '<div class="relative group">
                <span class="cursor-default" data-tippy-content="'.format_date_time($transaction->created_at).'">'
                    .Carbon::parse($transaction->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
            </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id'),

            Column::make('Customer', 'customer_name'),

            Column::make('Payment Gateway', 'type')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Amount', 'amount_formatted', 'amount')
                ->searchable(),

            Column::make('Amount (With Tax)', 'amount_with_tax'),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->searchable()
                ->sortable(),

            Column::action(t('action'))
                ->hidden(! checkPermission(['admin.transactions.view', 'admin.transactions.actions'])),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('status')
                ->dataSource([
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'success', 'label' => 'Success'],
                    ['value' => 'failed', 'label' => 'Failed'],
                ])
                ->optionValue('value')
                ->optionLabel('label'),

            Filter::datepicker('created_at'),

            Filter::select('payment_gateway')
                ->dataSource(
                    collect(['stripe', 'offline'])->map(fn ($val) => [
                        'value' => $val,
                        'label' => ucfirst($val),
                    ])->toArray()
                )
                ->optionValue('value')
                ->optionLabel('label'),
        ];
    }

    public function actions(Transaction $row): array
    {
        $actions = [];

        if (checkPermission('admin.transactions.view')) {
            $actions[] = Button::add('edit')
                ->slot('View Details')
                ->class('inline-flex items-center justify-center px-3 py-1 text-sm border border-info-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-info-100 text-info-700 hover:bg-info-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-300 dark:bg-slate-700 dark:border-slate-500 dark:text-info-400 dark:hover:border-info-600 dark:hover:bg-info-600 dark:hover:text-white dark:focus:ring-offset-slate-800')
                ->route('admin.transactions.show', [$row->id]);
        }

        return $actions ?? [];
    }

    public function getInvoiceTotalWithTax($transaction): string
    {
        $invoice = $transaction->invoice;

        if (! $invoice) {
            return get_base_currency()->format($transaction->amount);
        }

        $subtotal = $invoice->subTotal();
        $taxDetails = $invoice->getTaxDetails();

        $taxAmount = 0;

        foreach ($taxDetails as $tax) {
            $amount = $tax['amount'];
            if ($amount <= 0 && $tax['rate'] > 0) {
                $amount = $subtotal * ($tax['rate'] / 100);
            }
            $taxAmount += $amount;
        }

        $fee = $invoice->fee ?: 0;
        $calculatedTotal = $subtotal + $taxAmount + $fee;

        if (abs($calculatedTotal - $invoice->total()) > 0.01) {
            return get_base_currency()->format($calculatedTotal);
        }

        return $invoice->formattedTotal(); // This already includes tax if precomputed
    }
}
