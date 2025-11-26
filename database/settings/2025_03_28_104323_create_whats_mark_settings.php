<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'whats-mark.wm_version' => '',
        'whats-mark.wm_verification_id' => '',
        'whats-mark.wm_verification_token' => '',
        'whats-mark.wm_last_verification' => '',
        'whats-mark.wm_support_until' => '',
        'whats-mark.wm_validate' => true,
        'whats-mark.whatsmark_latest_version' => '1.0.0',
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
