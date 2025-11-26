<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Spatie\LaravelSettings\Events\SettingsSaved;

class ClearSpatieSettingsCache
{
    /**
     * Handle Spatie settings save event to clear cache.
     */
    public function handle(SettingsSaved $event): void
    {
        $settings = $event->settings;
        $group = $settings->group();

        $cachePrefix = config('settings.cache_prefix', 'settings_');

        // Clear admin/global setting caches for this group
        Cache::forget("{$cachePrefix}{$group}");
        Cache::forget("{$cachePrefix}group_{$group}");
        Cache::forget("{$cachePrefix}{$group}_object");

        // Clear individual setting caches (important for get_setting function)
        $settingsClass = get_class($settings);
        $reflection = new \ReflectionClass($settingsClass);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            if (! $property->isStatic()) {
                $propertyName = $property->getName();
                Cache::forget("{$cachePrefix}{$group}.{$propertyName}");
            }
        }

        // Clear Laravel container bindings to prevent stale instances
        try {
            $settingsClass = get_class($settings);

            // Clear container bindings for the settings class
            $containerBindings = [
                $settingsClass,
                "settings.{$group}",
                $group,
            ];

            foreach ($containerBindings as $binding) {
                try {
                    if (app()->bound($binding)) {
                        app()->forgetInstance($binding);
                    }
                } catch (\Exception $e) {
                    // Ignore binding errors
                }
            }
        } catch (\Exception $e) {
            // Ignore container errors
        }

        // Clear settings classes cache
        Cache::forget("{$cachePrefix}classes");

        // Clear cache tags if supported by the cache driver
        try {
            Cache::tags(['settings', $group])->flush();
        } catch (\Exception $e) {
            // Cache driver doesn't support tags
        }

        // Set invalidation marker for helper functions
        try {
            Cache::put('settings_static_cache_invalidated_at', now()->timestamp, 300);
        } catch (\Exception $e) {
            // Ignore cache errors
        }
    }
}
