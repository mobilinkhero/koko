<?php

namespace Corbital\LaravelEmails\Listeners;

use Corbital\LaravelEmails\Events\EmailFailed;
use Corbital\LaravelEmails\Events\EmailScheduled;
use Corbital\LaravelEmails\Events\EmailSent;
use Corbital\LaravelEmails\Models\EmailLog;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\LaravelSettings\Exceptions\SettingsMissingException;

class LogEmailStatus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the email sent event.
     *
     * @return void
     */
    public function handleEmailSent(EmailSent $event)
    {
        if (! $this->isLoggingEnabled()) {
            return;
        }

        // Create a log entry for the sent email
        EmailLog::create([
            'template_id' => $event->templateId,
            'recipients' => json_encode($event->recipients),
            'subject' => $event->subject,
            'body' => 'Email content not logged',
            'success' => true,
            'sent_at' => now(),
        ]);
    }

    /**
     * Handle the email failed event.
     *
     * @return void
     */
    public function handleEmailFailed(EmailFailed $event)
    {
        if (! $this->isLoggingEnabled()) {
            return;
        }

        // Create a log entry for the failed email
        EmailLog::create([
            'template_id' => $event->templateId,
            'recipients' => json_encode($event->recipients),
            'subject' => $event->subject,
            'body' => 'Email content not logged',
            'success' => false,
            'error' => $event->error,
        ]);
    }

    /**
     * Handle the email scheduled event.
     *
     * @return void
     */
    public function handleEmailScheduled(EmailScheduled $event)
    {
        if (! $this->isLoggingEnabled()) {
            return;
        }

        // Create a log entry for the scheduled email
        EmailLog::create([
            'template_id' => $event->templateId,
            'recipients' => json_encode($event->recipients),
            'subject' => $event->subject,
            'body' => 'Email content not logged',
            'scheduled_for' => $event->scheduledFor,
        ]);
    }

    /**
     * Check if email logging is enabled.
     */
    protected function isLoggingEnabled(): bool
    {
        // First check the config
        if (! config('laravel-emails.enable_logging', true)) {
            return false;
        }

        // Then check the settings
        try {
            $settings = app(EmailSettings::class);

            return $settings->log_retention_days > 0;
        } catch (SettingsMissingException $e) {
            // If settings are not available, fall back to config
            return true;
        }
    }
}
