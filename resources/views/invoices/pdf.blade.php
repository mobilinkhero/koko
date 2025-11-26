<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ t('invoice') }} #{{ $invoice->invoice_number ?? format_draft_invoice_number() }}</title>
    <style>
        /* Modern Professional Invoice Styling - Dompdf Compatible */
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #2d3748;
            position: relative;
            font-size: 14px;
            line-height: 1.6;
            background-color: #f8fafc;
        }

        /* Main Container */
        .invoice-container {
            max-width: 850px;
            margin: 8px auto;
            padding: 15px;
            position: relative;
            background-color: #fff;
            border-radius: 10px;
        }

        /* Modern Header */
        .invoice-header {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-header::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Logo Styling */
        .logo {
            float: left;
            max-width: 200px;
            width: 40%;
        }

        .logo img {
            max-height: 70px;
            width: auto;
        }

        .logo h2 {
            color: #4f46e5;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }

        /* Invoice Info Section */
        .invoice-info {
            float: right;
            text-align: right;
            width: 55%;
        }

        .invoice-title {
            color: #4f46e5;
            font-size: 36px;
            font-weight: bold;
            margin: 0 0 10px;
            letter-spacing: 0.5px;
        }

        .invoice-meta {
            font-size: 15px;
            line-height: 1.6;
            color: #64748b;
        }

        .invoice-meta strong {
            color: #334155;
        }

        /* Status Badge */
        .status-container {
            margin-bottom: 15px;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Status Colors */
        .status-new {
            background-color: #eff6ff;
            color: #2563eb;
            border: 1px solid #93c5fd;
        }

        .status-paid {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #6ee7b7;
        }

        .status-cancelled {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        /* Two-column Layout for Addresses using floats */
        .address-section {
            margin-bottom: 15px;
            width: 100%;
        }

        .address-section::after {
            content: "";
            display: table;
            clear: both;
        }

        .address-block {
            float: left;
            width: 48%;
            margin-right: 4%;
            padding: 8px;
            background-color: #f8fafc;
            border-radius: 8px;
        }

        .address-block:last-child {
            margin-right: 0;
        }

        .address-block h3 {
            color: #4f46e5;
            font-size: 15px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        .address-content strong {
            display: inline-block;
            margin-bottom: 2px;
            color: #334155;
        }

        .address-content {
            font-size: 12px;
            line-height: 1.2;
        }

        /* Table Styling */
        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .invoice-items thead {
            background-color: #4f46e5;
            color: white;
        }

        .invoice-items th {
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }

        .invoice-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .invoice-items tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .item-title {
            font-weight: bold;
            color: #334155;
            margin-bottom: 5px;
        }

        .item-description {
            color: #64748b;
            font-size: 14px;
        }

        /* Totals Section */
        .invoice-summary {
            float: right;
            width: 350px;
        }

        .invoice-summary-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
        }

        .invoice-summary-table th {
            text-align: left;
            padding: 8px 10px;
            font-weight: bold;
            color: #4b5563;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-summary-table td {
            text-align: right;
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Breakdown Section */
        .price-breakdown {
            background-color: #f0f9ff;
            border-left: 3px solid #4f46e5;
            padding: 10px 15px;
        }

        .price-breakdown-title {
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }

        .price-breakdown-item {
            padding: 3px 0 3px 10px;
            color: #4b5563;
            font-size: 14px;
        }

        .breakdown-base {
            font-weight: bold;
            color: #334155;
        }

        .tax-row th,
        .tax-row td {
            font-size: 15px;
            color: #4b5563;
        }

        .total-row th,
        .total-row td {
            border-top: 2px solid #4f46e5;
            font-weight: bold;
            font-size: 16px;
            color: #4f46e5;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        /* Payment Information */
        .payment-details {
            clear: both;
            margin-top: 20px;
            padding: 15px;
            background-color: #ecfdf5;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .payment-title {
            font-weight: bold;
            color: #059669;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .payment-info {
            color: #065f46;
            font-size: 14px;
        }

        /* Footer */
        .invoice-footer {
            clear: both;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        /* Watermark - Simplified for Dompdf */
        .watermark {
            position: absolute;
            top: 400px;
            left: 200px;
            font-size: 120px;
            font-weight: bold;
            text-transform: uppercase;
            opacity: 0.03;
            z-index: 0;
            pointer-events: none;
        }

        .watermark-paid {
            color: #059669;
        }

        .watermark-new {
            color: #2563eb;
        }

        .watermark-cancelled {
            color: #dc2626;
        }

        /* Bank Details */
        .bank-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 14px;
            color: #4b5563;
        }

        .bank-details h3 {
            color: #4f46e5;
            font-size: 16px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }

        /* Bank details table */
        .bank-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bank-details-table td {
            padding: 4px 0;
            border: none;
        }

        .bank-details-label {
            font-weight: bold;
            width: 140px;
            color: #334155;
        }

        .bank-details-value {
            color: #4b5563;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    @php
    // Safely get invoice settings with null checks
    $invoiceSettings = $invoiceSettings ?? new stdClass();
    $bankDetails = [
    'bank_name' => $invoiceSettings->bank_name ?? '',
    'account_name' => $invoiceSettings->account_name ?? '',
    'account_number' => $invoiceSettings->account_number ?? '',
    'ifsc_code' => $invoiceSettings->ifsc_code ?? '',
    ];
    $footerText = $invoiceSettings->footer_text ?? '';

    // Ensure required variables exist
    $company_name = $company_name ?? config('app.name', 'Company Name');
    $company_logo = $company_logo ?? null;
    $invoice = $invoice ?? null;
    $tenant = $tenant ?? null;
    $items = $items ?? collect();
    @endphp

    <!-- Status Watermark -->
    @if ($invoice)
    <div class="watermark watermark-{{ strtolower($invoice->status ?? 'new') }}">
        {{ strtoupper($invoice->status ?? 'NEW') }}
    </div>
    @endif

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="logo">
                @if ($company_logo)
                <img src="{{ $company_logo }}" alt="{{ $company_name }}" />
                @else
                <h2>{{ $company_name }}</h2>
                @endif
            </div>

            <div class="invoice-info">
                @if ($invoice)
                <div class="status-container">
                    <span class="status-badge status-{{ strtolower($invoice->status ?? 'new') }}">
                        {{ ucfirst($invoice->status ?? 'New') }}
                    </span>
                </div>
                <div class="invoice-title">{{ t('invoice') }}</div>
                <div class="invoice-meta">
                    <strong>{{ t('invoice_number_label') }}:</strong>
                    {{ $invoice->invoice_number ?? format_draft_invoice_number() }}<br>
                    <strong>{{ t('date_label') }}:</strong>
                    {{ $invoice->created_at
                    ? $invoice->created_at->format('F j, Y')
                    : date("F j,
                    Y") }}<br>

                    @foreach ($transactions as $transaction)
                    <strong>
                       {{ t('transaction_id') }}:
                    </strong>

                     @if ($transaction->type === 'offline')
                        {{ $transaction->metadata['payment_reference'] ?? '-' }}
                        @else
                        {{ $transaction->idempotency_key ?? '-' }}
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="address-section">
            <div class="address-block">
                <h3>{{ t('from') }}</h3>
                <div class="address-content">
                    <strong>{{ $company_name }}</strong><br>
                    @if (isset($invoiceSettings->company_address) && !empty($invoiceSettings->company_address))
                    {!! nl2br(e($invoiceSettings->company_address)) !!}
                    @endif
                    @php
                        do_action('invoice_pdf_company_info', $invoice,'');
                    @endphp
                </div>
            </div>

            @if ($tenant)
            <div class="address-block">
                <h3>{{ t('bill_to') }}</h3>
                <div class="address-content">
                    <strong>{{ $tenant->billing_name ?? ($tenant->company_name ?? 'Customer') }}</strong><br>
                    @if (!empty($tenant->billing_address))
                    {!! nl2br(e($tenant->billing_address)) !!}@if (!empty($tenant->billing_city))
                    ,
                    @endif
                    <br>
                    @endif
                    @if (!empty($tenant->billing_city))
                    {{ $tenant->billing_city }}@if (!empty($tenant->billing_state))
                    , {{ $tenant->billing_state }}
                    @endif
                    {{ $tenant->billing_zip_code ?? '' }}<br>
                    @endif
                    @if (!empty($tenant->billing_phone))
                    {{ $tenant->billing_phone }}<br>
                    @endif
                    @if (!empty($tenant->billing_email))
                    {{ $tenant->billing_email }}
                    @endif
                    @php
                        do_action('custom_invoice_additional_billing_info_pdf',$tenant);
                    @endphp
                </div>
            </div>
            @endif
        </div>

        @if ($items && $items->count() > 0)
        <table class="invoice-items">
            <thead>
                <tr>
                    <th width="55%">{{ t('description') }}</th>
                    <th width="15%">{{ t('price') }}</th>
                    <th width="10%">{{ t('quantity') }}</th>
                    <th width="20%">{{ t('amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr>
                    <td>
                        <div class="item-title">{{ $item->title ?? t('default_item_title') }}</div>
                        @if (!empty($item->description))
                        <div class="item-description">{!! nl2br(e($item->description)) !!}</div>
                        @endif
                    </td>
                    <td style="font-family: 'DejaVu Sans';">
                        {{ $invoice ? $invoice->formatAmount($item->amount ?? 0) : number_format($item->amount ?? 0, 2)
                        }}
                    </td>
                    <td>{{ $item->quantity ?? 1 }}</td>
                    <td style="font-family: 'DejaVu Sans';">
                        {{ $invoice
                        ? $invoice->formatAmount(($item->amount ?? 0) * ($item->quantity ?? 1))
                        : number_format(($item->amount ?? 0) * ($item->quantity ?? 1), 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if ($invoice)
        <div class="invoice-summary">
            <table class="invoice-summary-table">
                @php
                $taxDetails = $invoice->getTaxDetails();
                $subtotal = $invoice->subTotal();
                $calculatedTaxAmount = 0;

                // Calculate actual tax amount if needed
                foreach ($taxDetails as $tax) {
                $amt = $tax['amount'] ?? 0;
                if ($amt <= 0 && ($tax['rate'] ?? 0)> 0) {
                    $amt = $subtotal * (($tax['rate'] ?? 0) / 100);
                    }
                    $calculatedTaxAmount += $amt;
                    }

                    $fee = $invoice->fee ?? 0;
                    $calculatedTotal = $subtotal + $calculatedTaxAmount + $fee;
                    @endphp

                    <tr>
                        <th>{{ t('subtotal') }}</th>
                        <td style="font-family: 'DejaVu Sans';">{{ $invoice->formatAmount($subtotal) }}</td>
                    </tr>

                    @if (count($taxDetails) > 0)

                    @foreach ($taxDetails as $tax)
                    <tr class="tax-row">
                        <th>{{ $tax['name'] ?? t('tax_label') }} ({{ $tax['formatted_rate'] ?? '0%' }})</th>
                        <td style="font-family: 'DejaVu Sans';">
                            {{ ($tax['amount'] ?? 0) <= 0 && ($tax['rate'] ?? 0)> 0
                                ? $invoice->formatAmount($subtotal * (($tax['rate'] ?? 0) / 100))
                                : $tax['formatted_amount'] ?? $invoice->formatAmount(0) }}
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="tax-row">
                        <th>{{ t('tax ') }}(0%)</th>
                        <td>{{ $invoice->formatAmount(0) }}</td>
                    </tr>
                    @endif

                    @if ($fee > 0)
                    <tr>
                        <th>{{ t('fee') }}</th>
                        <td>{{ $invoice->formatAmount($fee) }}</td>
                    </tr>
                    @endif

                    @if (count($creditTransactions) > 0)
                    <tr class="tax-row">
                        <th>{{ t('credit_applied') }}</th>
                        <td style="font-family: 'DejaVu Sans';">
                            @php
                            $credits = $creditTransactions->sum('amount');
                            if ($credits > $calculatedTotal) {
                            $credits = $calculatedTotal;
                            }
                            @endphp
                            {{ '- ' . $invoice->formatAmount($credits) }}
                        </td>
                    </tr>
                    <tr class="tax-row">
                        <th>{{ $invoice->status == 'paid' ? t('amount_paid') : t('amount_due') }}</th>
                        <td style="font-family: 'DejaVu Sans';">
                            @php
                            $finalamount = $calculatedTotal - $credits;
                            @endphp
                            {{ $invoice->formatAmount($finalamount) }}
                        </td>
                    </tr>
                    @endif

                    <tr class="total-row">
                        <th>{{ t('total') }}</th>
                        <td style="font-family: 'DejaVu Sans';">{{ $invoice->formatAmount($calculatedTotal) }}</td>
                    </tr>

            </table>
        </div>
        @endif

        <div class="clearfix"></div>

        @if ($invoice && $invoice->status === 'paid')
        <div class="payment-details">
            <div class="payment-title">{{ t('payment_information') }}</div>
            <div class="payment-info">
                {{ t('paid_on') }} {{ $invoice->paid_at ? $invoice->paid_at->format('F j, Y') : 'N/A' }}<br>
                {{ t('payment_received_message') }}
            </div>
        </div>
        @endif

        <div class="invoice-footer">
            @if (!empty($footerText))
            {!! nl2br(e($footerText)) !!}
            @else
            {{ t('payment_successful_message') }}
            @endif
        </div>
    </div>
</body>

</html>
