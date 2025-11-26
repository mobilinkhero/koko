<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

if (! function_exists('get_batch_settings')) {
    /**
     * Get multiple settings at once
     *
     * @param array $keys Array of setting keys in format 'group.key'
     * @return array Associative array with 'group.key' => value
     */
    function get_batch_settings(array $keys): array
    {
        $result = [];
        $keysToFetch = [];
        
        // Parse keys and prepare cache lookup
        foreach ($keys as $fullKey) {
            [$group, $key] = explode('.', $fullKey, 2);
            
            // Try cache first
            $cacheKey = "setting_{$group}_{$key}";
            $cached = Cache::get($cacheKey);
            
            if ($cached !== null) {
                $result[$fullKey] = $cached;
            } else {
                $keysToFetch[] = ['group' => $group, 'key' => $key, 'fullKey' => $fullKey];
            }
        }
        
        // Fetch remaining from database
        if (!empty($keysToFetch)) {
            $groups = array_unique(array_column($keysToFetch, 'group'));
            
            $settings = DB::table('settings')
                ->whereIn('group', $groups)
                ->get()
                ->keyBy(function ($item) {
                    return $item->group . '.' . $item->key;
                });
            
            foreach ($keysToFetch as $keyData) {
                $fullKey = $keyData['fullKey'];
                $value = $settings->get($fullKey)->value ?? null;
                
                // Cache for 1 hour
                Cache::put("setting_{$keyData['group']}_{$keyData['key']}", $value, 3600);
                
                $result[$fullKey] = $value;
            }
        }
        
        return $result;
    }
}

if (! function_exists('get_setting')) {
    /**
     * Get a single setting value
     *
     * @param string $group Setting group
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function get_setting(string $group, string $key, $default = null)
    {
        $cacheKey = "setting_{$group}_{$key}";
        
        // Try cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Fetch from database
        $setting = DB::table('settings')
            ->where('group', $group)
            ->where('key', $key)
            ->first();
        
        $value = $setting->value ?? $default;
        
        // Cache for 1 hour
        Cache::put($cacheKey, $value, 3600);
        
        return $value;
    }
}

if (! function_exists('save_setting')) {
    /**
     * Save or update a setting
     *
     * @param string $group Setting group
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    function save_setting(string $group, string $key, $value): bool
    {
        try {
            DB::table('settings')->updateOrInsert(
                ['group' => $group, 'key' => $key],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            
            // Clear cache
            Cache::forget("setting_{$group}_{$key}");
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to save setting', [
                'group' => $group,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}

if (! function_exists('get_settings_by_group')) {
    /**
     * Get all settings for a specific group
     *
     * @param string $group Setting group
     * @return array Associative array with key => value
     */
    function get_settings_by_group(string $group): array
    {
        $cacheKey = "settings_group_{$group}";
        
        // Try cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Fetch from database
        $settings = DB::table('settings')
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
        
        // Cache for 1 hour
        Cache::put($cacheKey, $settings, 3600);
        
        return $settings;
    }
}

if (! function_exists('delete_setting')) {
    /**
     * Delete a setting
     *
     * @param string $group Setting group
     * @param string $key Setting key
     * @return bool
     */
    function delete_setting(string $group, string $key): bool
    {
        try {
            DB::table('settings')
                ->where('group', $group)
                ->where('key', $key)
                ->delete();
            
            // Clear cache
            Cache::forget("setting_{$group}_{$key}");
            Cache::forget("settings_group_{$group}");
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete setting', [
                'group' => $group,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}
