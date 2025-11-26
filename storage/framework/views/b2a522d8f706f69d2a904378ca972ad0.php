<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?php echo e(t('invoice')); ?> #<?php echo e($invoice->invoice_number ?? format_draft_invoice_number()); ?></title>
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
    <?php
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
    ?>

    <!-- Status Watermark -->
    <?php if($invoice): ?>
    <div class="watermark watermark-<?php echo e(strtolower($invoice->status ?? 'new')); ?>">
        <?php echo e(strtoupper($invoice->status ?? 'NEW')); ?>

    </div>
    <?php endif; ?>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="logo">
                <?php if($company_logo): ?>
                <img src="<?php echo e($company_logo); ?>" alt="<?php echo e($company_name); ?>" />
                <?php else: ?>
                <h2><?php echo e($company_name); ?></h2>
                <?php endif; ?>
            </div>

            <div class="invoice-info">
                <?php if($invoice): ?>
                <div class="status-container">
                    <span class="status-badge status-<?php echo e(strtolower($invoice->status ?? 'new')); ?>">
                        <?php echo e(ucfirst($invoice->status ?? 'New')); ?>

                    </span>
                </div>
                <div class="invoice-title"><?php echo e(t('invoice')); ?></div>
                <div class="invoice-meta">
                    <strong><?php echo e(t('invoice_number_label')); ?>:</strong>
                    <?php echo e($invoice->invoice_number ?? format_draft_invoice_number()); ?><br>
                    <strong><?php echo e(t('date_label')); ?>:</strong>
                    <?php echo e($invoice->created_at
                    ? $invoice->created_at->format('F j, Y')
                    : date("F j,
                    Y")); ?><br>

                    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <strong>
                       <?php echo e(t('transaction_id')); ?>:
                    </strong>

                     <?php if($transaction->type === 'offline'): ?>
                        <?php echo e($transaction->metadata['payment_reference'] ?? '-'); ?>

                        <?php else: ?>
                        <?php echo e($transaction->idempotency_key ?? '-'); ?>

                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="address-section">
            <div class="address-block">
                <h3><?php echo e(t('from')); ?></h3>
                <div class="address-content">
                    <strong><?php echo e($company_name); ?></strong><br>
                    <?php if(isset($invoiceSettings->company_address) && !empty($invoiceSettings->company_address)): ?>
                    <?php echo nl2br(e($invoiceSettings->company_address)); ?>

                    <?php endif; ?>
                    <?php
                        do_action('invoice_pdf_company_info', $invoice,'');
                    ?>
                </div>
            </div>

            <?php if($tenant): ?>
            <div class="address-block">
                <h3><?php echo e(t('bill_to')); ?></h3>
                <div class="address-content">
                    <strong><?php echo e($tenant->billing_name ?? ($tenant->company_name ?? 'Customer')); ?></strong><br>
                    <?php if(!empty($tenant->billing_address)): ?>
                    <?php echo nl2br(e($tenant->billing_address)); ?><?php if(!empty($tenant->billing_city)): ?>
                    ,
                    <?php endif; ?>
                    <br>
                    <?php endif; ?>
                    <?php if(!empty($tenant->billing_city)): ?>
                    <?php echo e($tenant->billing_city); ?><?php if(!empty($tenant->billing_state)): ?>
                    , <?php echo e($tenant->billing_state); ?>

                    <?php endif; ?>
                    <?php echo e($tenant->billing_zip_code ?? ''); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($tenant->billing_phone)): ?>
                    <?php echo e($tenant->billing_phone); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($tenant->billing_email)): ?>
                    <?php echo e($tenant->billing_email); ?>

                    <?php endif; ?>
                    <?php
                        do_action('custom_invoice_additional_billing_info_pdf',$tenant);
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if($items && $items->count() > 0): ?>
        <table class="invoice-items">
            <thead>
                <tr>
                    <th width="55%"><?php echo e(t('description')); ?></th>
                    <th width="15%"><?php echo e(t('price')); ?></th>
                    <th width="10%"><?php echo e(t('quantity')); ?></th>
                    <th width="20%"><?php echo e(t('amount')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="item-title"><?php echo e($item->title ?? t('default_item_title')); ?></div>
                        <?php if(!empty($item->description)): ?>
                        <div class="item-description"><?php echo nl2br(e($item->description)); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-family: 'DejaVu Sans';">
                        <?php echo e($invoice ? $invoice->formatAmount($item->amount ?? 0) : number_format($item->amount ?? 0, 2)); ?>

                    </td>
                    <td><?php echo e($item->quantity ?? 1); ?></td>
                    <td style="font-family: 'DejaVu Sans';">
                        <?php echo e($invoice
                        ? $invoice->formatAmount(($item->amount ?? 0) * ($item->quantity ?? 1))
                        : number_format(($item->amount ?? 0) * ($item->quantity ?? 1), 2)); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if($invoice): ?>
        <div class="invoice-summary">
            <table class="invoice-summary-table">
                <?php
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
                    ?>

                    <tr>
                        <th><?php echo e(t('subtotal')); ?></th>
                        <td style="font-family: 'DejaVu Sans';"><?php echo e($invoice->formatAmount($subtotal)); ?></td>
                    </tr>

                    <?php if(count($taxDetails) > 0): ?>

                    <?php $__currentLoopData = $taxDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="tax-row">
                        <th><?php echo e($tax['name'] ?? t('tax_label')); ?> (<?php echo e($tax['formatted_rate'] ?? '0%'); ?>)</th>
                        <td style="font-family: 'DejaVu Sans';">
                            <?php echo e(($tax['amount'] ?? 0) <= 0 && ($tax['rate'] ?? 0)> 0
                                ? $invoice->formatAmount($subtotal * (($tax['rate'] ?? 0) / 100))
                                : $tax['formatted_amount'] ?? $invoice->formatAmount(0)); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                    <tr class="tax-row">
                        <th><?php echo e(t('tax ')); ?>(0%)</th>
                        <td><?php echo e($invoice->formatAmount(0)); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if($fee > 0): ?>
                    <tr>
                        <th><?php echo e(t('fee')); ?></th>
                        <td><?php echo e($invoice->formatAmount($fee)); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if(count($creditTransactions) > 0): ?>
                    <tr class="tax-row">
                        <th><?php echo e(t('credit_applied')); ?></th>
                        <td style="font-family: 'DejaVu Sans';">
                            <?php
                            $credits = $creditTransactions->sum('amount');
                            if ($credits > $calculatedTotal) {
                            $credits = $calculatedTotal;
                            }
                            ?>
                            <?php echo e('- ' . $invoice->formatAmount($credits)); ?>

                        </td>
                    </tr>
                    <tr class="tax-row">
                        <th><?php echo e($invoice->status == 'paid' ? t('amount_paid') : t('amount_due')); ?></th>
                        <td style="font-family: 'DejaVu Sans';">
                            <?php
                            $finalamount = $calculatedTotal - $credits;
                            ?>
                            <?php echo e($invoice->formatAmount($finalamount)); ?>

                        </td>
                    </tr>
                    <?php endif; ?>

                    <tr class="total-row">
                        <th><?php echo e(t('total')); ?></th>
                        <td style="font-family: 'DejaVu Sans';"><?php echo e($invoice->formatAmount($calculatedTotal)); ?></td>
                    </tr>

            </table>
        </div>
        <?php endif; ?>

        <div class="clearfix"></div>

        <?php if($invoice && $invoice->status === 'paid'): ?>
        <div class="payment-details">
            <div class="payment-title"><?php echo e(t('payment_information')); ?></div>
            <div class="payment-info">
                <?php echo e(t('paid_on')); ?> <?php echo e($invoice->paid_at ? $invoice->paid_at->format('F j, Y') : 'N/A'); ?><br>
                <?php echo e(t('payment_received_message')); ?>

            </div>
        </div>
        <?php endif; ?>

        <div class="invoice-footer">
            <?php if(!empty($footerText)): ?>
            <?php echo nl2br(e($footerText)); ?>

            <?php else: ?>
            <?php echo e(t('payment_successful_message')); ?>

            <?php endif; ?>
        </div>
    </div>
</body>

</html>
<?php /**PATH /home/ahtisham/app.chatvoo.com/storage/framework/views/2da50ffe2c96e0d304bbe71421310e6d.blade.php ENDPATH**/ ?>