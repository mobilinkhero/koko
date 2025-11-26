<?php

namespace App\Listeners;

use App\Events\TransactionSuccessful;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Queue\InteractsWithQueue;

class SendTransactionSuccessfulEmail
{
    use InteractsWithQueue;

    public function handle(TransactionSuccessful $event)
    {
        try {
            $transaction = $event->transaction;
            $invoice = $transaction->invoice;
            $user = $invoice->user ?? $invoice->tenant->adminUser;

            if (! $user || ! $user->email) {
                return;
            }

            $content = render_email_template('transaction-success', ['transactionId' => $transaction->id, 'userId' => $user->id, 'tenantId' => $invoice->tenant_id]);
            $subject = get_email_subject('transaction-success', ['transactionId' => $transaction->id, 'userId' => $user->id, 'tenantId' => $invoice->tenant_id]);
            if (is_smtp_valid()) {
                Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            app_log('Failed to send transaction success email', 'error', $e, [
                'transaction_id' => $event->transaction->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
