<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'email.mailer' => '',
        'email.smtp_port' => '',
        'email.smtp_username' => '',
        'email.smtp_password' => '',
        'email.smtp_encryption' => '',
        'email.sender_name' => '',
        'email.sender_email' => '',
        'email.smtp_host' => '',
    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->settings) as $key) {
            if ($this->migrator->exists($key)) {
                $this->migrator->delete($key);
            }
        }
    }
};
