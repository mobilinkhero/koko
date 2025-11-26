<?php

namespace Corbital\Settings\Listeners;

use Corbital\Settings\Events\SettingCreated;
use Corbital\Settings\Events\SettingDeleted;
use Corbital\Settings\Events\SettingUpdated;
use Illuminate\Support\Facades\Cache;

class ClearSettingsCache
{
    /**
     * Handle setting cache invalidation when settings are created, updated, or deleted.
     */
    public function handle(SettingCreated|SettingUpdated|SettingDeleted $event): void
    {
        $cachePrefix = config('settings.cache_prefix', 'settings_');

        if (isset($event->tenantId) && $event->tenantId) {
            // Clear tenant-specific setting caches
            $tenantHash = hash('xxh3', (string) $event->tenantId);
            $groupKeyHash = hash('xxh3', $event->group);
            $keyHash = hash('xxh3', "{$event->group}.{$event->key}");

            // Clear all possible tenant cache key formats
            Cache::forget("{$cachePrefix}tn_{$tenantHash}_{$keyHash}");
            Cache::forget("{$cachePrefix}tn_{$tenantHash}_{$groupKeyHash}");
            Cache::forget("{$cachePrefix}tenant_{$event->tenantId}_{$event->group}.{$event->key}");
            Cache::forget("{$cachePrefix}tenant_{$event->tenantId}_{$event->group}");
        } else {
            // Clear admin/global setting caches
            Cache::forget("{$cachePrefix}{$event->group}.{$event->key}");
            Cache::forget("{$cachePrefix}{$event->group}");

            // Clear additional cache variations
            Cache::forget("{$cachePrefix}group_{$event->group}");
            Cache::forget("{$cachePrefix}{$event->group}_object");

            // Clear Laravel container bindings to prevent stale instances
            try {
                $settingsManager = app('settings');
                $settingsClasses = $settingsManager->getSettingsClasses();

                if (isset($settingsClasses[$event->group])) {
                    $settingsClass = $settingsClasses[$event->group];

                    // Clear container bindings for the settings class
                    $containerBindings = [
                        $settingsClass,
                        "settings.{$event->group}",
                        $event->group,
                        'App\\Settings\\'.ucfirst($event->group).'Settings',
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
                }
            } catch (\Exception $e) {
                // Ignore container errors
            }
        }

        // Clear settings classes cache
        Cache::forget("{$cachePrefix}classes");

        // Clear cache tags if supported by the cache driver
        try {
            Cache::tags(['settings', $event->group])->flush();
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
