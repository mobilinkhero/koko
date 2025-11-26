<?php

namespace App\Multitenancy\Tasks;

use Corbital\Settings\Models\TenantSetting;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantSettingsTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        // Load tenant settings into config
        $cacheKey = "tenant_{$tenant->getKey()}_settings";

        $settings = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($tenant) {
            // First get settings grouped by group
            return TenantSetting::where('tenant_id', $tenant->getKey())
                ->get()
                ->groupBy('group')
                ->map(function ($groupSettings) {
                    // Transform each group's settings into a key-value collection
                    return $groupSettings->mapWithKeys(function ($setting) {
                        return [$setting->key => $setting->value];
                    });
                })
                ->toArray();
        });

        // Add settings to config
        foreach ($settings as $group => $groupSettings) {
            config(["tenant.{$group}" => $groupSettings]);
        }
    }

    public function forgetCurrent(): void
    {
        // Clear tenant-specific config
        // No need for complex operations as config is request-scoped
    }
}
