<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'invoice.bank_name' => '',
        'invoice.account_name' => '',
        'invoice.account_number' => '',
        'invoice.ifsc_code' => '',
        'invoice.prefix' => 'INV',
        'invoice.footer_text' => 'Thanks for your purchase. Contact support for help.',
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
