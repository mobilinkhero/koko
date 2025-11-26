<?php

namespace App\MergeFields\Admin;

use App\Models\Transaction;

class TransactionMergeFields
{
    public function name(): string
    {
        return 'transaction-group';
    }

    public function templates(): array
    {
        return [
            'transection-created-reminder-mail-to-admin',
        ];
    }

    public function build(): array
    {
        return [
            ['name' => 'Transaction Id', 'key' => '{transaction_id}'],
            ['name' => 'Transaction Type',         'key' => '{transaction_type}'],
            ['name' => 'Transaction Status',       'key' => '{transaction_status}'],
            ['name' => 'Transaction Amount',       'key' => '{transaction_amount}'],
            ['name' => 'Transaction Currency',     'key' => '{transaction_currency}'],
            ['name' => 'Transaction Description',  'key' => '{transaction_description}'],
            ['name' => 'Transaction Error',        'key' => '{transaction_error}'],
            ['name' => 'Transaction Created At',   'key' => '{transaction_created_at}'],
            ['name' => 'Transaction Payment Method', 'key' => '{transaction_payment_method}'],
            ['name' => 'Transaction Invoice ID',   'key' => '{transaction_invoice_id}'],
            ['name' => 'Transaction URL',           'key' => '{transaction_url}'],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['transactionId'])) {
            return [];
        }

        $transaction = Transaction::with(['currency', 'paymentMethod'])->findOrFail($context['transactionId']);

        return [
            '{transaction_id}' => $transaction->idempotency_key ?? '',
            '{transaction_type}' => $transaction->type,
            '{transaction_status}' => $transaction->status,
            '{transaction_amount}' => number_format($transaction->amount, 2),
            '{transaction_currency}' => $transaction->currency->code ?? '',
            '{transaction_description}' => $transaction->description ?? '',
            '{transaction_error}' => $transaction->error ?? '',
            '{transaction_created_at}' => optional($transaction->created_at)->toDateTimeString(),
            '{transaction_payment_method}' => $transaction->paymentMethod->name ?? 'N/A',
            '{transaction_invoice_id}' => $transaction->invoice_id,
            '{transaction_url}' => route('admin.transactions.show', ['id' => $transaction->id]),
        ];
    }
}
