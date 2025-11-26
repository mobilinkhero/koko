<?php

namespace Corbital\LaravelEmails\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    // Basic sender information
    public string $sender_name;

    public string $sender_email;

    public string $default_layout_template;

    public string $email_signature;

    // Queue settings
    public bool $queue_emails = true;

    public string $queue_connection = 'database';

    public string $queue_name = 'emails';

    // Email retry and log settings
    public int $max_email_retries = 3;

    public int $log_retention_days = 30;

    public bool $enable_scheduling = true;

    // Mail settings
    public string $mail_mailer = 'smtp';

    public string $mail_host = 'smtp.example.com';

    public int $mail_port = 587;

    public ?string $mail_username = null;

    public ?string $mail_password = null;

    public ?string $mail_encryption = null;

    /**
     * Get the settings group name.
     */
    public static function group(): string
    {
        return 'email';
    }

    /**
     * Get the validation rules for the settings.
     */
    public static function validationRules(): array
    {
        return [
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_email' => ['required', 'email', 'max:255'],
            'default_layout_template' => ['nullable', 'string', 'max:255'],
            'email_signature' => ['nullable', 'string'],
            'queue_emails' => ['boolean'],
            'queue_connection' => ['string'],
            'queue_name' => ['string'],
            'max_email_retries' => ['required', 'integer', 'min:1', 'max:10'],
            'log_retention_days' => ['required', 'integer', 'min:1'],
            'enable_scheduling' => ['boolean'],

            // SMTP validation rules
            'mail_mailer' => ['required', 'string'],
            'mail_host' => ['required', 'string'],
            'mail_port' => ['required', 'integer'],
            'mail_username' => ['nullable', 'string'],
            'mail_password' => ['nullable', 'string'],
            'mail_encryption' => ['nullable', 'string'],
        ];
    }
}
