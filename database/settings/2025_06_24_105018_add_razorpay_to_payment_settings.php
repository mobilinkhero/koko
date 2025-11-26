<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        try {
            // Add settings only if they don't exist
            if (! $this->settingExists('payment.razorpay_enabled')) {
                $this->migrator->add('payment.razorpay_enabled', false);
            }

            if (! $this->settingExists('payment.razorpay_key_id')) {
                $this->migrator->add('payment.razorpay_key_id', '');
            }

            if (! $this->settingExists('payment.razorpay_key_secret')) {
                $this->migrator->add('payment.razorpay_key_secret', '');
            }

            if (! $this->settingExists('payment.razorpay_webhook_secret')) {
                $this->migrator->add('payment.razorpay_webhook_secret', '');
            }
        } catch (\Exception $e) {
            // Log or handle the exception
            // Most likely the setting already exists
        }
    }

    /**
     * Check if a setting already exists
     */
    private function settingExists(string $name): bool
    {
        try {
            $parts = explode('.', $name);
            $group = $parts[0];
            $property = $parts[1];

            // Try to query the setting
            DB::table('settings')
                ->where('group', $group)
                ->where('name', $property)
                ->firstOrFail();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
};
