<?php

namespace Corbital\LaravelEmails\Commands;

use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ShowEmailSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:show-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display current email settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Current Email Settings:');
        $this->newLine();

        try {
            $settings = app(EmailSettings::class);

            // Display sender information
            $this->line('<fg=blue>Sender Information</>');
            $this->line('Sender Name: '.$settings->sender_name);
            $this->line('Sender Email: '.$settings->sender_email);
            $this->line('Default Layout: '.$settings->default_layout_template);
            $this->newLine();

            // Display queue settings
            $this->line('<fg=blue>Queue Settings</>');
            $this->line('Queue Enabled: '.($settings->queue_emails ? 'Yes' : 'No'));
            $this->line('Queue Connection: '.$settings->queue_connection);
            $this->line('Queue Name: '.$settings->queue_name);
            $this->line('Max Email Retries: '.$settings->max_email_retries);
            $this->newLine();

            // Display log settings
            $this->line('<fg=blue>Log Settings</>');
            $this->line('Log Retention Days: '.$settings->log_retention_days);
            $this->line('Enable Scheduling: '.($settings->enable_scheduling ? 'Yes' : 'No'));
            $this->newLine();

            // Display mail server settings
            $this->line('<fg=blue>Mail Server Settings</>');
            $this->line('Mail Driver: '.$settings->mail_mailer);
            $this->line('Mail Host: '.$settings->mail_host);
            $this->line('Mail Port: '.$settings->mail_port);
            $this->line('Mail Encryption: '.($settings->mail_encryption ?? 'None'));
            $this->line('Mail Username: '.($settings->mail_username ? str_repeat('*', strlen($settings->mail_username)) : 'Not set'));
            $this->line('Mail Password: '.($settings->mail_password ? '********' : 'Not set'));

        } catch (\Exception $e) {
            $this->error('Failed to load settings: '.$e->getMessage());

            // Display fallback configuration
            $this->warn('Using fallback configuration from config files:');
            $this->line('Mail From: '.Config::get('mail.from.address', 'Not set'));
            $this->line('Mail From Name: '.Config::get('mail.from.name', 'Not set'));
            $this->line('Mail Driver: '.Config::get('mail.default', 'Not set'));

            return 1;
        }

        return 0;
    }
}
