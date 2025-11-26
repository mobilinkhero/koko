<?php

namespace Corbital\LaravelEmails\Commands;

use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitializeEmailSettingsCommand extends Command
{
    protected $signature = 'email:init-settings';

    protected $description = 'Initialize email settings for the package';

    public function handle()
    {
        $this->info('Initializing email settings...');

        try {
            // Check if settings table exists
            if (! Schema::hasTable('settings')) {
                $this->error('Settings table not found. Please run: php artisan migrate');

                return 1;
            }

            // Check if settings already exist
            $settingsExist = DB::table('settings')
                ->where('group', 'email')
                ->exists();

            if ($settingsExist) {
                $this->info('Email settings already exist. Updating default values...');
            } else {
                $this->info('Creating new email settings...');
            }

            // Initialize or update settings
            $settings = app(EmailSettings::class);
            $settings->sender_name = config('app.name');
            $settings->sender_email = config('mail.from.address', 'hello@example.com');
            $settings->default_layout_template = 'default';
            $settings->email_signature = 'Kind regards,<br>The '.config('app.name').' Team';
            $settings->queue_emails = true;
            $settings->queue_connection = 'database';
            $settings->queue_name = 'emails';
            $settings->max_email_retries = config('laravel-emails.retries', 3);
            $settings->log_retention_days = config('laravel-emails.logs_retention_days', 30);
            $settings->enable_scheduling = true;
            $settings->mail_mailer = env('MAIL_MAILER', 'smtp');
            $settings->mail_host = env('MAIL_HOST', 'smtp.example.com');
            $settings->mail_port = (int) env('MAIL_PORT', 587);
            $settings->mail_username = env('MAIL_USERNAME', '');
            $settings->mail_password = env('MAIL_PASSWORD', '');
            $settings->mail_encryption = env('MAIL_ENCRYPTION', 'tls');
            $settings->save();

            $this->info('Email settings initialized successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to initialize email settings: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }
}
