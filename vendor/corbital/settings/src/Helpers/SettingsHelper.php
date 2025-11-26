<?php

use Corbital\Settings\Facades\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

if (! function_exists('get_settings_classes')) {
    /**
     * Get all settings classes.
     */
    function get_settings_classes(): array
    {
        return Cache::remember('settings.classes', 3600, function () {
            return collect(scandir(app_path('Settings')))
                ->filter(fn ($file) => str_ends_with($file, 'Settings.php'))
                ->mapWithKeys(function ($file) {
                    $className = str_replace('.php', '', $file);
                    $group = Str::kebab(str_replace('Settings', '', $className));

                    return [$group => "App\\Settings\\{$className}"];
                })
                ->toArray();
        });
    }
}

if (! function_exists('get_settings_by_group')) {
    /**
     * Get all settings for a specific group with cache invalidation support.
     *
     * @throws \Corbital\Settings\Exceptions\SettingsException
     */
    function get_settings_by_group(string $group, mixed $default = null)
    {
        if (! Schema::hasTable('settings')) {
            return null;
        }
        // Static cache for in-memory storage
        static $instances = [];
        static $lastInvalidationCheck = null;
        try {
            // Check if cache was invalidated and clear static cache if needed
            $currentInvalidationTime = Cache::get('settings_static_cache_invalidated_at');

            if ($currentInvalidationTime && $currentInvalidationTime !== $lastInvalidationCheck) {
                $instances = [];
                $lastInvalidationCheck = $currentInvalidationTime;
            }
            $settingsClasses = get_settings_classes();

            if (! isset($settingsClasses[$group])) {
                return $default;
            }
            // Return cached instance if available
            if (! isset($instances[$group])) {
                $instances[$group] = app($settingsClasses[$group]);
            }

            // Get fresh instance and cache it
            $instances[$group] = Settings::group($group);

            return $instances[$group];
        } catch (\Throwable $e) {
            report($e);

            return $default;
        }
    }
}

if (! function_exists('get_all_settings')) {
    /**
     * Get all settings from all groups.
     */
    function get_all_settings(): array
    {
        // In-memory instance cache for the current request
        static $instances = [];
        static $lastInvalidationCheck = null;

        try {
            // Check if cache was invalidated and clear static cache if needed
            $currentInvalidationTime = Cache::get('settings_static_cache_invalidated_at');
            if ($currentInvalidationTime && $currentInvalidationTime !== $lastInvalidationCheck) {
                $instances = [];
                $lastInvalidationCheck = $currentInvalidationTime;
            }

            return collect(get_settings_classes())
                ->mapWithKeys(function ($class, $group) use (&$instances) {
                    if (! isset($instances[$group])) {
                        $instances[$group] = app($class);
                    }

                    return [$group => $instances[$group]];
                })
                ->toArray();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}

if (! function_exists('set_setting')) {
    /**
     * Update a specific setting.
     *
     * @throws \Corbital\Settings\Exceptions\SettingsException
     */
    function set_setting(string $key, mixed $value): bool
    {
        try {
            [$group, $setting] = explode('.', $key);
            $settingsClasses = get_settings_classes();

            if (! isset($settingsClasses[$group])) {
                return false;
            }

            $settings = app($settingsClasses[$group]);

            // Only update if the setting exists
            if (! property_exists($settings, $setting)) {
                return false;
            }

            $settings->$setting = $value;
            $settings->save();

            // Clear specific setting cache
            Cache::forget("settings.{$group}.{$setting}");

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}

if (! function_exists('set_settings_batch')) {
    /**
     * Update multiple settings for a group.
     *
     * @throws \Corbital\Settings\Exceptions\SettingsException
     */
    function set_settings_batch(string $group, array $settings): bool
    {
        try {
            $settingsClasses = get_settings_classes();
            if (! isset($settingsClasses[$group])) {
                return false;
            }

            $settingsObject = app($settingsClasses[$group]);
            $settingsToUpdate = [];

            foreach ($settings as $key => $value) {
                if (property_exists($settingsObject, $key)) {
                    $settingsObject->$key = $value;
                    $settingsToUpdate[$key] = $value;
                }
            }

            if (! empty($settingsToUpdate)) {
                $settingsObject->save();

                foreach ($settingsToUpdate as $key => $value) {
                    Cache::forget("settings.{$group}.{$key}");
                }
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}

if (! function_exists('get_setting')) {
    /**
     * Get a specific setting.
     *
     * @throws \Corbital\Settings\Exceptions\SettingsException
     */
    function get_setting(string $key, mixed $default = null): mixed
    {
        try {
            [$group, $setting] = explode('.', $key);

            $settings = get_settings_by_group($group);

            // Cache individual setting values for 30 minutes to reduce DB calls if settings don't change often
            return Cache::remember("settings.{$group}.{$setting}", now()->addMinutes(30), function () use ($settings, $setting, $default) {
                return $settings->$setting ?? $default;
            });
        } catch (\Throwable $e) {
            report($e);

            return $default;
        }
    }
}

if (! function_exists('get_batch_settings')) {
    /**
     * Get multiple settings efficiently in a single operation.
     * This function reduces database queries by batching setting retrievals.
     *
     * @param  array  $keys  Array of setting keys in 'group.setting' format
     * @return array Associative array with keys as setting names and values as setting values
     *
     * @throws \Corbital\Settings\Exceptions\SettingsException
     */
    function get_batch_settings(array $keys): array
    {
        $result = [];
        $groupedKeys = [];

        // Group keys by their settings group to minimize database calls
        foreach ($keys as $key) {
            if (strpos($key, '.') !== false) {
                [$group, $setting] = explode('.', $key, 2);
                $groupedKeys[$group][] = $setting;
            }
        }

        // Load settings by group and extract required values
        foreach ($groupedKeys as $group => $settings) {
            try {
                $groupSettings = get_settings_by_group($group);

                foreach ($settings as $setting) {
                    $fullKey = $group.'.'.$setting;
                    $result[$fullKey] = $groupSettings->$setting ?? null;
                }
            } catch (\Throwable $e) {
                // If group fails to load, set all its settings to null
                foreach ($settings as $setting) {
                    $result[$group.'.'.$setting] = null;
                }
            }
        }

        return $result;
    }
}

if (! function_exists('settings')) {
    /**
     * Main settings function that handles all operations.
     *
     * @throws \Corbital\Settings\Exceptions\SettingsException
     */
    function settings(?string $key = null, mixed $value = null, mixed $default = null): mixed
    {
        $setting = get_batch_settings([$key]);

        return (is_null($key))
            ? get_all_settings()
            : (! is_null($value) ? set_setting($key, $value) : $setting[$key] ?? $default);
    }
}
if (! function_exists('get_settings_groups')) {
    /**
     * Get all available settings groups.
     */
    function get_settings_groups(): array
    {
        return array_keys(settings()->toArray());
    }
}
if (! function_exists('clear_settings_cache')) {
    /**
     * Clear the settings cache.
     */
    function clear_settings_cache(): bool
    {
        return Settings::clearCache();
    }
}

if (! function_exists('refresh_settings_cache')) {
    /**
     * Refresh the settings cache.
     */
    function refresh_settings_cache(): bool
    {
        return Settings::refreshCache();
    }
}
