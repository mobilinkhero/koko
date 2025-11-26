<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use Corbital\LaravelEmails\Facades\Email;

class SendInvoiceReceipt
{
    /**
     * Handle the event.
     */
    public function handle(InvoicePaid $event)
    {
        $invoice = $event->invoice;
        $user = getUserByTenantId($invoice->tenant_id);

        // Check if the invoice has an email address
        if (empty($user->email)) {
            app_log('Cannot send invoice receipt: No billing email found', 'warning', null, [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
            ]);

            return;
        }

        try {
            $content = render_email_template('invoice-receipt', ['tenantId' => $user->tenant_id, 'invoiceId' => $invoice->id]);
            $subject = get_email_subject('invoice-receipt', ['tenantId' => $user->tenant_id, 'invoiceId' => $invoice->id]);

            $pdfPath = $invoice->savePdf();
            if (is_smtp_valid()) {
                $result = Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->attach($pdfPath)
                    ->send();

                if ($result) {
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }
                }

            }

        } catch (\Exception $e) {
            app_log('Failed to send invoice receipt', 'error', $e, [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
            ]);

            return false;
        }
    }
}
