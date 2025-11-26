<?php

namespace App\Listeners;

use App\Events\PaymentRejected;
use App\Models\Tenant;
use App\Models\Transaction;
use Corbital\LaravelEmails\Facades\Email;

class SendPaymentRejectedMail
{
    /**
     * Handle the event.
     */
    public function handle(PaymentRejected $event)
    {
        $transaction = $event->transaction;
        $reason = $event->reason;
        if ($transaction->status !== Transaction::STATUS_FAILED) {
            return;
        }
        // Send payment rejection email
        $user = getUserByTenantId($transaction->invoice->tenant_id);
        $tenant = Tenant::find($transaction->invoice->tenant_id);

        if ($user && $user->email) {
            try {
                $content = render_email_template('payment-rejected', ['tenantId' => $user->tenant_id, 'invoiceId' => $transaction->invoice->id]);
                $subject = get_email_subject('payment-rejected', ['tenantId' => $user->tenant_id, 'invoiceId' => $transaction->invoice->id]);
                if (is_smtp_valid()) {
                    $result = Email::to($user->email)
                        ->subject($subject)
                        ->content($content)
                        ->send();
                }

                if (! $result) {
                    app_log('Failed to send payment rejection email', 'error', null, [
                        'transaction_id' => $transaction->id,
                        'email' => $user->email,
                    ]);
                }
            } catch (\Exception $e) {
                app_log('Error sending payment rejection email', 'error', $e, [
                    'transaction_id' => $transaction->id,
                    'email' => $user->email,
                ]);

                return false;
            }
        }
    }
}
