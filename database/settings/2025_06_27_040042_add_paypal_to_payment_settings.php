<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        // PayPal Settings
        'payment.paypal_enabled' => false,
        'payment.paypal_mode' => 'sandbox',
        'payment.paypal_client_id' => '',
        'payment.paypal_client_secret' => '',
        'payment.paypal_webhook_id' => '',
        'payment.paypal_brand_name' => 'WhatsMarks',
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
