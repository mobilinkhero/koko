<?php

namespace Corbital\LaravelEmails\Services;

use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    protected $settings;

    public function __construct(EmailSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Configure mail settings from the database.
     */
    public function configure(): void
    {
        try {
            // Set mail configuration from settings
            Config::set('mail.default', $this->settings->mail_mailer);
            Config::set('mail.mailers.smtp.host', $this->settings->mail_host);
            Config::set('mail.mailers.smtp.port', $this->settings->mail_port);
            Config::set('mail.mailers.smtp.username', $this->settings->mail_username);
            Config::set('mail.mailers.smtp.password', $this->settings->mail_password);
            Config::set('mail.mailers.smtp.encryption', $this->settings->mail_encryption);

            // Set from address
            Config::set('mail.from.address', $this->settings->sender_email);
            Config::set('mail.from.name', $this->settings->sender_name);

        } catch (\Exception $e) {
            app_log('Failed to configure mail settings from EmailSettings', 'error', $e);

        }
    }
}
