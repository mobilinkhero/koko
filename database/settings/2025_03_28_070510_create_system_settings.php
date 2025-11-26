<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'system.site_name' => 'Whatsmark saas',
        'system.site_description' => 'Whatsmark SaaS is a cloud-based platform for automating and managing WhatsApp marketing campaigns',
        'system.timezone' => 'UTC',
        'system.date_format' => 'Y-m-d',
        'system.time_format' => '24',
        'system.active_language' => 'en',
        'system.company_name' => '',
        'system.company_country_id' => '',
        'system.company_email' => '',
        'system.company_city' => '',
        'system.company_state' => '',
        'system.company_zip_code' => '',
        'system.company_address' => '',
    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
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
