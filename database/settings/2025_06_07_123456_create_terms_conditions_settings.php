<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'terms-conditions.title' => 'Terms and Conditions',
        'terms-conditions.content' => '<h2>Terms and Conditions</h2><p>Please read these Terms and Conditions carefully before using our service. Your access to and use of the service is conditioned on your acceptance of and compliance with these Terms.</p><p>By accessing or using the service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the service.</p>',
        'terms-conditions.updated_at' => null,
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
        foreach ($this->settings as $key => $value) {
            if ($this->migrator->exists($key)) {
                $this->migrator->delete($key);
            }
        }
    }
};
