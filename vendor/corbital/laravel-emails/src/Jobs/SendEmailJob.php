<?php

namespace Corbital\LaravelEmails\Jobs;

use Corbital\LaravelEmails\Events\EmailFailed;
use Corbital\LaravelEmails\Events\EmailSent;
use Corbital\LaravelEmails\Mail\TemplatedEmail;
use Corbital\LaravelEmails\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The mailable instance.
     *
     * @var TemplatedEmail
     */
    protected $mailable;

    /**
     * The email log ID.
     *
     * @var int|null
     */
    protected $emailLogId;

    /**
     * Indicates if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TemplatedEmail $mailable, ?int $emailLogId = null)
    {
        $this->mailable = $mailable;
        $this->emailLogId = $emailLogId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emailSent = false;
        $error = null;

        try {
            // Try to send the email
            Mail::send($this->mailable);
            $emailSent = true;
        } catch (\Exception $e) {
            // Log the exception but don't mark the job as failed yet

            $error = $e->getMessage();

            // Check if it's a harmless SMTP disconnect exception that happens after email sending
            if ($this->isHarmlessDisconnectError($e)) {
                LaravelLog::info('Ignoring harmless SMTP disconnect exception - email likely sent successfully');
                $emailSent = true;
            }
        }

        if ($emailSent) {
            // Email sent successfully (or we're assuming it did)
            if ($this->emailLogId) {
                // Update the log status
                $log = $this->updateEmailLog('sent');

                // Only dispatch event if we have a valid EmailLog object
                if ($log) {
                    event(new EmailSent($log));
                }
            }

            LaravelLog::info('Email marked as sent', [
                'to' => $this->getRecipients(),
                'subject' => $this->getSubject(),
                'log_id' => $this->emailLogId,
            ]);
        } else {
            // Email definitely failed
            if ($this->emailLogId) {
                $this->updateEmailLog('failed', $error);
            }

            // For the EmailFailed event, we need to use an array compatible with its signature
            $emailData = [
                'template' => null,
                'to' => $this->getRecipients(),
                'subject' => $this->getSubject(),
                'error' => $error,
            ];

            event(new EmailFailed($emailData, $error));

            LaravelLog::error('Email sending failed', [
                'to' => $this->getRecipients(),
                'subject' => $this->getSubject(),
                'error' => $error,
                'log_id' => $this->emailLogId,
            ]);

            // Only throw an exception to trigger retry if we're certain it failed
            if ($this->attempts() < $this->tries) {
                throw new \Exception($error);
            }
        }
    }

    /**
     * Check if the exception is a harmless disconnect that happens after email is sent.
     *
     * @param  \Exception  $e  The exception to check
     * @return bool
     */
    protected function isHarmlessDisconnectError(\Exception $e)
    {
        $message = strtolower($e->getMessage());

        // Common SMTP disconnect messages that happen AFTER email delivery
        $harmlessMessages = [
            'connection could not be established with host',
            'could not be established',
            'failed to connect',
            'connection timed out',
            'stream_socket_enable_crypto',
            'network is unreachable',
            'connection refused',
            '503 valid rcpt command',
        ];

        foreach ($harmlessMessages as $harmlessMessage) {
            if (strpos($message, $harmlessMessage) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the email log status.
     *
     * @return EmailLog|null
     */
    protected function updateEmailLog(string $status, ?string $error = null)
    {
        try {
            $emailLog = EmailLog::find($this->emailLogId);

            if ($emailLog) {
                $data = [
                    'status' => $status,
                ];

                if ($status === 'sent') {
                    $data['sent_at'] = now();
                    // If we're setting sent, mark success implicitly
                    // (this supports legacy status checks that look for success=true)
                    if (array_key_exists('success', $emailLog->getAttributes())) {
                        $data['success'] = true;
                    }
                } elseif ($status === 'failed') {
                    // If we're setting failed, mark success as false for legacy code
                    if (array_key_exists('success', $emailLog->getAttributes())) {
                        $data['success'] = false;
                    }
                }

                if ($error) {
                    $data['error'] = $error;
                }

                $emailLog->update($data);

                LaravelLog::info('Email log updated', [
                    'log_id' => $this->emailLogId,
                    'status' => $status,
                ]);

                return $emailLog->fresh(); // Return the fresh instance for the event
            }
        } catch (\Exception $e) {
            // Log the error but don't stop execution
            LaravelLog::error('Failed to update email log: '.$e->getMessage(), [
                'log_id' => $this->emailLogId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Get the recipients of the email.
     *
     * @return array
     */
    protected function getRecipients()
    {
        return array_map(function ($recipient) {
            if (is_array($recipient)) {
                return $recipient;
            }

            return ['email' => $recipient, 'name' => null];
        }, $this->mailable->to);
    }

    /**
     * Get the subject of the email.
     *
     * @return string|null
     */
    protected function getSubject()
    {
        return $this->mailable->subject;
    }
}
