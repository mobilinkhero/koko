<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class InvoicesTable extends PowerGridComponent
{
    public string $tableName = 'invoices-table-hgitwy-table';

    public string $sortField = 'invoice_number';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Invoice::query()
            ->selectRaw('invoices.*, (SELECT COUNT(*) FROM invoices i2 WHERE i2.id <= invoices.id ) as row_num')
            ->with(['items', 'tenant', 'taxes'])
            ->withSum('items as total_amount', 'amount')
            ->withSum('tenant as tenant_company_name', 'company_name');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('invoice_number', fn ($row) => $row->invoice_number ?? format_draft_invoice_number())
            ->add('tenant', fn ($row) => $row->tenant->company_name)
            ->add('status', function ($row) {
                if ($row->status == 'paid') {
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800"><svg class="mr-1.5 h-2 w-2 text-success-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>'.t('paid').'</span>';
                } elseif ($row->status === 'new') {
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-warning-100 text-warning-800">'.t('unpaid').'</span>';
                }

                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800/50 text-gray-800 dark:text-gray-300">
                <span class="h-1.5 w-1.5 bg-gray-400 rounded-full mr-1.5 inline-block"></span>'.e(ucfirst($row->status)).'
            </span>';
            })

            ->add('total_amount', function ($row) {
                return $row->formatAmount($row->total_amount);
            })

            ->add('created_at_formatted', function ($row) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($row->created_at).'">'
                    .Carbon::parse($row->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            })

            ->add('formatted_amount', function ($invoice) {
                return $this->totalAmount($invoice);
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Invoice #', 'invoice_number')
                ->searchable()
                ->sortable(),

            Column::make(t('tenant'), 'tenant', 'tenant_company_name')
                ->searchable()
                ->sortable(),

            Column::make(t('status'), 'status')
                ->searchable()
                ->sortable(),

            Column::make(t('amount'), 'total_amount', 'total_amount')
                ->searchable()
                ->sortable(),

            Column::make('Total (With Tax)', 'formatted_amount'),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->searchable()
                ->sortable(),

            Column::action(t('action'))
                ->hidden(! checkPermission('admin.invoices.view')),

        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions($row): array
    {
        $actions = [];

        if (checkPermission('admin.invoices.view')) {
            $actions[] = Button::add('view')
                ->slot('View Details')
                ->class('inline-flex items-center justify-center px-3 py-1 text-sm border border-info-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-info-100 text-info-700 hover:bg-info-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-300 dark:bg-slate-700 dark:border-slate-500 dark:text-info-400 dark:hover:border-info-600 dark:hover:bg-info-600 dark:hover:text-white dark:focus:ring-offset-slate-800')
                ->route('admin.invoices.show', [$row->id]);
        }

        return $actions ?? [];
    }

    public function relationSearch(): array
    {
        return [
            'tenant' => [
                'company_name',
            ],
        ];
    }

    public function totalAmount($invoice)
    {
        // Ensure we calculate and display the correct total with tax

        $subtotal = $invoice->subTotal();
        $taxDetails = $invoice->getTaxDetails();

        $taxAmount = 0;

        // Calculate actual tax amount if needed
        foreach ($taxDetails as $tax) {
            $amount = $tax['amount'];
            if ($amount <= 0 && $tax['rate'] > 0) {
                $amount = $subtotal * ($tax['rate'] / 100);
            }
            $taxAmount += $amount;
        }

        $fee = $invoice->fee ?: 0;
        $calculatedTotal = $subtotal + $taxAmount + $fee;

        // Use calculated total if different from invoice total
        if (abs($calculatedTotal - $invoice->total()) > 0.01) {
            $totalDisplay = $invoice->formatAmount($calculatedTotal);
        } else {
            $totalDisplay = $invoice->formattedTotal();
        }

        return $totalDisplay;
    }
}
