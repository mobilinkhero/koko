<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Models\Invoice\Invoice;
use App\Models\Transaction;
use Corbital\LaravelEmails\Facades\Email;

class SendTransactionCreatedEmail
{
    /**
     * Handle the event.
     */
    public function handle(TransactionCreated $event)
    {
        $transaction = Transaction::find($event->transactionId);
        if (! $transaction) {
            app_log('Cannot send transaction notification email: Transaction not found', 'warning', null, [
                'transaction_id' => $event->transactionId,
            ]);

            return;
        }

        $invoice = Invoice::find($event->invoiceId);
        if (! $invoice) {
            app_log('Cannot send transaction notification email: Invoice not found', 'warning', null, [
                'transaction_id' => $event->transactionId,
                'invoice_id' => $event->invoiceId,
            ]);

            return;
        }

        // Get all admin users
        $adminUsers = \App\Models\User::withoutGlobalScopes()
            ->where('user_type', 'admin')
            ->where('is_admin', true)
            ->pluck('id', 'email')
            ->toArray();

        if (empty($adminUsers)) {
            return;
        }

        try {
            foreach ($adminUsers as $email => $adminId) {
                $content = render_email_template('transection-created-reminder-mail-to-admin', [
                    'transactionId' => $transaction->id,
                    'tenantId' => $invoice->tenant_id,
                    'userId' => $adminId,
                ]);

                $subject = get_email_subject('transection-created-reminder-mail-to-admin', [
                    'transactionId' => $transaction->id,
                    'tenantId' => $invoice->tenant_id,
                    'userId' => $adminId,
                ]);
                if (is_smtp_valid()) {
                    Email::to($email)
                        ->subject($subject)
                        ->content($content)
                        ->send();
                }
            }
        } catch (\Exception $e) {
            app_log('Failed to send transaction notification email to admin(s)', 'error', $e, [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
