<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use App\Models\Tenant;
use App\Models\Transaction;
use Corbital\LaravelEmails\Facades\Email;

class SendPaymentApprovedMail
{
    /**
     * Handle the event.
     */
    public function handle(PaymentApproved $event)
    {
        $transaction = $event->transaction;
        if ($transaction->status !== Transaction::STATUS_SUCCESS) {
            return;
        }

        $user = getUserByTenantId($transaction->invoice->tenant_id);
        $tenant = Tenant::find($transaction->invoice->tenant_id);

        if ($user && $user->email) {
            try {

                $content = render_email_template('payment-approved', ['tenantId' => $user->tenant_id, 'transactionId' => $transaction->id, 'invoiceId' => $transaction->invoice->id]);
                $subject = get_email_subject('payment-approved', ['tenantId' => $user->tenant_id, 'transactionId' => $transaction->id, 'invoiceId' => $transaction->invoice->id]);

                $pdfPath = $transaction->invoice->savePdf();
                if (is_smtp_valid()) {
                    // Send email with attachment
                    $result = Email::to($user->email)
                        ->subject($subject)
                        ->content($content)
                        ->attach($pdfPath)
                        ->send();

                    // Clean up temporary file
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }
                }

                if (! $result) {
                    app_log('Failed to send payment approval email', 'error', null, [
                        'transaction_id' => $transaction->id,
                        'email' => $user->email,
                    ]);
                }
            } catch (\Exception $e) {
                app_log('Error sending payment approval email', 'error', $e, [
                    'transaction_id' => $transaction->id,
                    'email' => $user->email,
                ]);

                return false;
            }
        }
    }
}
