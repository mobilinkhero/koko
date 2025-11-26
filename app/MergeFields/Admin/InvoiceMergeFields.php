<?php

namespace App\MergeFields\Admin;

use App\Models\Invoice\Invoice;

class InvoiceMergeFields
{
    public function name(): string
    {
        return 'invoice-group';
    }

    public function templates(): array
    {
        return [
            'invoice-receipt',
        ];
    }

    public function build(): array
    {
        return [
            ['name' => 'Invoice Number',        'key' => '{invoice_number}'],
            ['name' => 'Invoice Title',         'key' => '{invoice_title}'],
            ['name' => 'Invoice Description',   'key' => '{invoice_description}'],
            ['name' => 'Invoice Status',        'key' => '{invoice_status}'],
            ['name' => 'Invoice Type',          'key' => '{invoice_type}'],
            ['name' => 'Invoice Total Amount',  'key' => '{invoice_total}'],
            ['name' => 'Invoice Tax Total',   'key' => '{invoice_tax_total}'],
            ['name' => 'Invoice Fee',           'key' => '{invoice_fee}'],
            ['name' => 'Invoice Currency',      'key' => '{invoice_currency}'],
            ['name' => 'Invoice Due Date',      'key' => '{invoice_due_date}'],
            ['name' => 'Invoice Paid At',       'key' => '{invoice_paid_at}'],
            ['name' => 'Invoice Cancelled At',  'key' => '{invoice_cancelled_at}'],
            ['name' => 'Invoice Items Table',   'key' => '{invoice_items_table}'],
            ['name' => 'Invoice Payment URL',   'key' => '{invoice_payment_url}'],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['invoiceId'])) {
            return [];
        }

        $invoice = Invoice::with(['currency', 'items'])->findOrFail($context['invoiceId']);
        $tenant_subdomain = tenant_subdomain_by_tenant_id($context['tenantId']);

        // Calculate total from invoice items
        $subtotal = $invoice->items->sum(function ($item) {
            return $item->amount * $item->quantity;
        });

        // Get tax details from the invoice taxes
        $taxDetails = $invoice->getTaxDetails();
        $taxAmount = $invoice->total_tax_amount;
        $total = $subtotal + $invoice->fee + $taxAmount;

        return [
            '{invoice_number}' => $invoice->invoice_number ?? 'N/A',
            '{invoice_title}' => $invoice->title,
            '{invoice_description}' => $invoice->description ?? '',
            '{invoice_status}' => ucfirst($invoice->status),
            '{invoice_type}' => ucfirst($invoice->type),
            '{invoice_total}' => number_format($total, 2),
            '{invoice_tax_total}' => number_format($taxAmount, 2),
            '{invoice_fee}' => number_format($invoice->fee, 2),
            '{invoice_currency}' => $invoice->currency->code ?? '',
            '{invoice_due_date}' => optional($invoice->due_date)->toDateString(),
            '{invoice_paid_at}' => optional($invoice->paid_at)->toDateTimeString(),
            '{invoice_cancelled_at}' => optional($invoice->cancelled_at)->toDateTimeString(),
            '{invoice_items_table}' => $this->buildInvoiceItemsTable($invoice),
            '{invoice_payment_url}' => tenant_route('tenant.invoices.pay', ['id' => $invoice->id, 'subdomain' => $tenant_subdomain]),
        ];
    }

    protected function buildInvoiceItemsTable(Invoice $invoice): string
    {
        if ($invoice->items->isEmpty()) {
            return 'No items found.';
        }

        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<thead><tr><th>Title</th><th>Description</th><th>Quantity</th><th>Amount</th><th>Total</th></tr></thead><tbody>';

        foreach ($invoice->items as $item) {
            $total = $item->quantity * $item->amount;
            $html .= '<tr>';
            $html .= '<td>'.e($item->title).'</td>';
            $html .= '<td>'.e($item->description ?? '-').'</td>';
            $html .= '<td>'.$item->quantity.'</td>';
            $html .= '<td>'.number_format($item->amount, 2).'</td>';
            $html .= '<td>'.number_format($total, 2).'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
