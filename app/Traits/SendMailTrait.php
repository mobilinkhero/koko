<?php

namespace App\Traits;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

trait SendMailTrait
{
    protected function sendMail($to, Mailable $mailable): array
    {
        $recipients = is_array($to) ? array_filter($to) : [$to];

        foreach ($recipients as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = t('invalid_email_address').' : '.$email;

                app_log($errorMessage, 'error', null, [
                    'recipients' => $recipients,
                    'mailable' => get_class($mailable),
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]);

                return [
                    'status' => false,
                    'message' => $errorMessage,
                ];
            }
        }

        try {
            Mail::to($recipients)->send($mailable);

            return [
                'status' => true,
                'message' => t('  '),
            ];
        } catch (\Throwable $e) {

            app_log(t('mail_sending_failed').' : '.$e->getMessage(), 'error', $e, [
                'recipients' => $recipients,
                'mailable' => get_class($mailable),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'status' => false,
                'message' => t('failed_to_send_mail'),
                'error' => $e->getMessage(),
            ];
        }
    }
}
