<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class InvoiceTable extends PowerGridComponent
{
    public string $tableName = 'invoice-table-a0eyaf-table';

    public bool $deferLoading = true;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public bool $showFilters = false;

    public string $loadingComponent = 'components.custom-loading';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {

        return [
            PowerGrid::header()
                ->withoutLoading()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $tenantId = tenant_id();

        return Invoice::query()
            ->selectRaw('invoices.*, (SELECT COUNT(*) FROM invoices i2 WHERE i2.id <= invoices.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', $tenantId)
            ->with(['items', 'tenant', 'taxes'])
            ->withSum('items as total_amount', 'amount');
    }

    public function relationSearch(): array
    {
        return [
            'items' => [
                'amount',
                'description',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('row_num')
            ->add('id')
            ->add('invoice_number', fn ($row) => $row->invoice_number ?? format_draft_invoice_number())
            ->add('created_at_formatted', fn ($invoice) => \Carbon\Carbon::parse($invoice->created_at)->format('M j, Y'))
            ->add('title_description', function ($invoice) {
                return $invoice->title.($invoice->description ? ' <span class="text-slate-500" data-tippy-content="'.e($invoice->description).'">- '.e(truncate_text($invoice->description, 30)).'</span>' : '');
            })
            ->add('status_badge', function ($invoice) {
                if ($invoice->status === 'paid') {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/50 text-success-800 dark:text-success-400">
                        <span class="h-1.5 w-1.5 bg-success-500 rounded-full mr-1.5 inline-block"></span>Paid
                    </span>';
                } elseif ($invoice->status === 'new') {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/50 text-warning-800 dark:text-warning-400">
                        <span class="h-1.5 w-1.5 bg-warning-500 rounded-full mr-1.5 inline-block"></span>Unpaid
                    </span>';
                }

                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800/50 text-gray-800 dark:text-gray-300">
                    <span class="h-1.5 w-1.5 bg-gray-400 rounded-full mr-1.5 inline-block"></span>'.e(ucfirst($invoice->status)).'
                </span>';
            })

            ->add('total_amount', function ($row) {
                return $row->formatAmount($row->total_amount);
            })

            ->add('formatted_amount', function ($invoice) {
                return $this->totalAmount($invoice);
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make('Invoice #', 'invoice_number')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable()
                ->bodyAttribute('whitespace-nowrap'),

            Column::make('Title / Description', 'title_description', 'title', 'description')
                ->sortable()
                ->bodyAttribute(
                    'text-slate-900 dark:text-white text-sm'
                )
                ->searchable(),

            Column::make('Status', 'status_badge', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Total', 'total_amount', 'total_amount')
                ->sortable()
                ->searchable(),

            Column::make('Total (With Tax)', 'formatted_amount'),

            Column::action('Actions')
                ->hidden(! checkPermission('tenant.invoices.view')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('status')
                ->dataSource([
                    ['id' => 'paid', 'name' => 'Paid'],
                    ['id' => 'new', 'name' => 'Unpaid'],
                ])
                ->optionValue('id')
                ->optionLabel('name'),
        ];
    }

    public function actions(Invoice $row): array
    {
        return [
            Button::add('view')
                ->slot('View Details')
                ->class('inline-flex items-center justify-center px-3 py-1 text-sm border border-info-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-info-100 text-info-700 hover:bg-info-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-300 dark:bg-slate-700 dark:border-slate-500 dark:text-info-400 dark:hover:border-info-600 dark:hover:bg-info-600 dark:hover:text-white dark:focus:ring-offset-slate-800')
                ->dispatch('viewInvoice', ['invoiceId' => $row->id]),

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
