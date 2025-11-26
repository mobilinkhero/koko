<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'whatsapp.wm_fb_app_id' => '',
        'whatsapp.wm_fb_app_secret' => '',
        'whatsapp.is_webhook_connected' => '0',
    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }

        if (! $this->migrator->exists('whatsapp.api_version')) {
            $this->migrator->add('whatsapp.api_version', 'v21.0');
        }
        if (! $this->migrator->exists('whatsapp.daily_limit')) {
            $this->migrator->add('whatsapp.daily_limit', '1000');
        }
        if (! $this->migrator->exists('whatsapp.webhook_verify_token')) {
            $this->migrator->add('whatsapp.webhook_verify_token', Str::random(16));
        }

        if (! $this->migrator->exists('whatsapp.queue')) {
            $this->migrator->add('whatsapp.queue', json_encode([
                'name' => 'whatsapp-messages',
                'connection' => 'database',
                'retry_after' => 180,
                'timeout' => 60,
            ]));
        }

        if (! $this->migrator->exists('whatsapp.paths')) {
            $this->migrator->add('whatsapp.paths', json_encode([
                'qrcodes' => storage_path('app/public/whatsapp/qrcodes'),
                'media' => storage_path('app/public/whatsapp/media'),
            ]));
        }

        if (! $this->migrator->exists('whatsapp.logging')) {
            $this->migrator->add('whatsapp.logging', json_encode([
                'enabled' => false,
                'channel' => 'whatsapp',
                'level' => 'info',
            ]));
        }
    }

    public function down(): void
    {
        $additionalSettings = [
            'whatsapp.api_version' => '',
            'whatsapp.daily_limit' => '',
            'whatsapp.queue' => '',
            'whatsapp.paths' => '',
            'whatsapp.logging' => '',
            'whatsapp.webhook_verify_token' => '',
        ];

        $this->settings = array_merge($this->settings, $additionalSettings);

        foreach (array_keys($this->settings) as $key) {
            if ($this->migrator->exists($key)) {
                $this->migrator->delete($key);
            }
        }
    }
};
