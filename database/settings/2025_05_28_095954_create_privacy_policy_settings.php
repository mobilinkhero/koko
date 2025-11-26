<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'privacy-policy.title' => 'Privacy Policy',
        'privacy-policy.content' => '<h2>Privacy Policy</h2><p>Your privacy is important to us. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p><p>Please read this Privacy Policy carefully. If you do not agree with the terms of this Privacy Policy, please do not access the application.</p>',
        'privacy-policy.updated_at' => null,
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
