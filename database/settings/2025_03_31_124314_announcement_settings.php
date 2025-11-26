<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'announcement.isEnable' => '',
        'announcement.message' => '',
        'announcement.link' => '',
        'announcement.link_text' => '',
        'announcement.background_color' => '',
        'announcement.link_text_color' => '',
        'announcement.message_color' => '',
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
