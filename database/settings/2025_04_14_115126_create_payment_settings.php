<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        // Payment Gateway Settings
        'payment.default_gateway' => 'offline',

        'payment.offline_enabled' => true,
        'payment.offline_description' => 'Pay via direct bank transfer.',
        'payment.offline_instructions' => 'Please transfer the amount to our bank account and email the receipt.',

        'payment.stripe_enabled' => false,
        'payment.stripe_key' => '',
        'payment.stripe_secret' => '',
        'payment.stripe_webhook_secret' => '',
        'payment.stripe_webhook_id' => '',

        // Tax Settings
        'payment.tax_enabled' => false,
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
