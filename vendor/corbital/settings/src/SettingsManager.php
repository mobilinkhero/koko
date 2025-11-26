<?php

namespace Corbital\Settings;

use Corbital\Settings\Events\SettingUpdated;
use Corbital\Settings\Exceptions\SettingsException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingsManager
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Cache TTL in seconds.
     */
    protected ?int $cacheTtl;

    /**
     * Cache key prefix.
     */
    protected string $cachePrefix;

    /**
     * Create a new settings manager instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        // Use Spatie's cache configuration with fallbacks to our enhanced settings
        $cacheConfig = config('settings.cache', []);
        $this->cacheTtl = $cacheConfig['ttl'] ?? config('settings.cache_ttl', 3600);
        $this->cachePrefix = $cacheConfig['prefix'] ?? config('settings.cache_prefix', 'settings_');
    }

    /**
     * Get a tenant-specific settings manager.
     */
    public function forTenant(string|int $tenantId): TenantSettingsManager
    {
        return new TenantSettingsManager($tenantId);
    }

    /**
     * Get a settings manager for the current tenant.
     */
    public function forCurrentTenant(): TenantSettingsManager
    {
        $tenantId = $this->resolveCurrentTenantId();

        if (! $tenantId) {
            throw new SettingsException('No current tenant found.');
        }

        return $this->forTenant($tenantId);
    }

    /**
     * Resolve the current tenant ID from the request.
     */
    protected function resolveCurrentTenantId(): string|int|null
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        // Option 1: Subdomain-based identification
        if (config('settings.tenant_subdomain_identification', false)) {
            $subdomain = explode('.', $request->getHost())[0] ?? null;
            if ($subdomain && $subdomain !== 'www') {
                return $subdomain;
            }
        }

        // Option 2: Header-based identification
        $headerName = config('settings.tenant_header_name', 'X-Tenant-ID');
        if ($request->hasHeader($headerName)) {
            return $request->header($headerName);
        }

        // Option 3: Query parameter-based identification
        $paramName = config('settings.tenant_param_name', 'tenant_id');
        if ($request->has($paramName)) {
            return $request->input($paramName);
        }

        // Option 4: Route parameter-based identification
        if ($request->route('tenant')) {
            return $request->route('tenant');
        }

        // Option 5: Session-based identification
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        return null;
    }

    /**
     * Get all settings classes.
     */
    public function getSettingsClasses(): array
    {
        $cacheKey = "{$this->cachePrefix}classes";

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            // First try to get settings classes from Spatie's registered settings
            $registeredClasses = $this->getRegisteredSettings();

            if (! empty($registeredClasses)) {
                return $registeredClasses;
            }

            // Fall back to scanning the settings directory if Spatie's registry is empty
            $settingsPath = app_path('Settings');

            if (! is_dir($settingsPath)) {
                return [];
            }

            return collect(scandir($settingsPath))
                ->filter(fn ($file) => str_ends_with($file, 'Settings.php'))
                ->mapWithKeys(function ($file) {
                    $className = str_replace('.php', '', $file);
                    $group = Str::kebab(str_replace('Settings', '', $className));

                    return [$group => "App\\Settings\\{$className}"];
                })
                ->toArray();
        });
    }

    /**
     * Get registered settings from Spatie's repository.
     */
    protected function getRegisteredSettings(): array
    {
        if (! class_exists('Spatie\\LaravelSettings\\Settings')) {
            return [];
        }

        try {
            // Get settings repository from Spatie if available
            if (app()->has('settings.repository')) {
                $repository = app('settings.repository');
                $method = method_exists($repository, 'getSettingsClasses') ? 'getSettingsClasses' : 'getSettings';

                if (method_exists($repository, $method)) {
                    $settings = $repository->{$method}();

                    // Transform to our expected format
                    return collect($settings)
                        ->mapWithKeys(function ($className) {
                            // If class implements group() method, use that
                            if (method_exists($className, 'group')) {
                                $group = $className::group();
                            } else {
                                // Otherwise extract from class name
                                $classBaseName = class_basename($className);
                                $group = Str::kebab(str_replace('Settings', '', $classBaseName));
                            }

                            return [$group => $className];
                        })
                        ->toArray();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return [];
    }

    /**
     * Get all settings.
     */
    public function all(): array
    {
        $result = [];

        foreach ($this->getSettingsClasses() as $group => $class) {
            try {
                $result[$group] = $this->group($group);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $result;
    }

    /**
     * Get settings by group.
     *
     * @throws SettingsException
     */
    public function group(string $group)
    {
        $cacheKey = "{$this->cachePrefix}{$group}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($group) {
            $settingsClasses = $this->getSettingsClasses();

            if (! isset($settingsClasses[$group])) {
                throw SettingsException::classNotFound($group);
            }

            return app($settingsClasses[$group]);
        });
    }

    /**
     * Get a specific setting.
     *
     * @throws SettingsException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            if (! str_contains($key, '.')) {
                throw SettingsException::invalidKeyFormat($key);
            }

            [$group, $setting] = explode('.', $key);

            $cacheKey = "{$this->cachePrefix}{$group}.{$setting}";

            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($group, $setting) {
                $settings = $this->group($group);

                if (! property_exists($settings, $setting)) {
                    throw SettingsException::propertyNotFound($group, $setting);
                }

                return $settings->$setting;
            });
        } catch (SettingsException $e) {
            if ($default !== null) {
                return $default;
            }

            throw $e;
        } catch (\Throwable $e) {
            report($e);

            if ($default !== null) {
                return $default;
            }

            throw $e;
        }
    }

    /**
     * Set a specific setting.
     *
     * @throws SettingsException
     */
    public function set(string $key, mixed $value): bool
    {
        if (! str_contains($key, '.')) {
            throw SettingsException::invalidKeyFormat($key);
        }

        [$group, $setting] = explode('.', $key);

        $settings = $this->group($group);

        if (! property_exists($settings, $setting)) {
            throw SettingsException::propertyNotFound($group, $setting);
        }

        $oldValue = $settings->$setting;

        // Apply filter hook to value
        $value = apply_filters('settings.value', $value, ['group' => $group, 'key' => $setting, 'old_value' => $oldValue]);

        do_action('settings.before_save', $group, $setting, $value, $oldValue);

        $settings->$setting = $value;
        $settings->save();

        // Clear the specific setting cache
        Cache::forget("{$this->cachePrefix}{$group}.{$setting}");
        Cache::forget("{$this->cachePrefix}{$group}");

        // Dispatch event
        if ($oldValue !== $value) {
            event(new SettingUpdated($group, $setting, $value, $oldValue));
        }

        do_action('settings.after_save', $group, $setting, $value, $oldValue);

        return true;
    }

    /**
     * Set multiple settings at once.
     *
     * @throws SettingsException
     */
    public function setBatch(string $group, array $settings): bool
    {
        $settingsObject = $this->group($group);
        $settingsToUpdate = [];
        $oldValues = [];

        foreach ($settings as $key => $value) {
            if (property_exists($settingsObject, $key)) {
                $oldValues[$key] = $settingsObject->$key;
                $settingsObject->$key = $value;
                $settingsToUpdate[$key] = $value;
            } else {
                throw SettingsException::propertyNotFound($group, $key);
            }
        }

        if (! empty($settingsToUpdate)) {
            $settingsObject->save();

            // Clear caches and dispatch events
            Cache::forget("{$this->cachePrefix}{$group}");

            foreach ($settingsToUpdate as $key => $value) {
                Cache::forget("{$this->cachePrefix}{$group}.{$key}");

                if ($oldValues[$key] !== $value) {
                    event(new SettingUpdated($group, $key, $value, $oldValues[$key]));
                }
            }
        }

        return true;
    }

    /**
     * Clear all settings cache.
     */
    public function clearCache(): bool
    {
        try {
            // Clear the settings classes cache
            Cache::forget("{$this->cachePrefix}classes");

            // Clear individual setting caches
            foreach ($this->getSettingsClasses() as $group => $class) {
                try {
                    $settings = app($class);
                    Cache::forget("{$this->cachePrefix}{$group}");

                    foreach (get_object_vars($settings) as $key => $value) {
                        if (! str_starts_with($key, '_')) {
                            Cache::forget("{$this->cachePrefix}{$group}.{$key}");
                        }
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Refresh the settings cache.
     */
    public function refreshCache(): bool
    {
        try {
            $this->clearCache();

            // Get fresh list of classes without using cache
            $classes = $this->getSettingsClassesWithoutCache();

            // Store in cache
            Cache::put("{$this->cachePrefix}classes", $classes, $this->cacheTtl);

            // Rebuild the cache for each group/setting
            foreach ($classes as $group => $class) {
                try {
                    $settings = $this->groupWithoutCache($group);
                    Cache::put("{$this->cachePrefix}{$group}", $settings, $this->cacheTtl);

                    foreach (get_object_vars($settings) as $key => $value) {
                        if (! str_starts_with($key, '_')) {
                            Cache::put("{$this->cachePrefix}{$group}.{$key}", $value, $this->cacheTtl);
                        }
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Get settings classes directly without using cache
     */
    protected function getSettingsClassesWithoutCache(): array
    {
        // First try to get settings classes from Spatie's registered settings
        $registeredClasses = $this->getRegisteredSettings();

        if (! empty($registeredClasses)) {
            return $registeredClasses;
        }

        // Fall back to scanning the settings directory
        $settingsPath = app_path('Settings');

        if (! is_dir($settingsPath)) {
            return [];
        }

        return collect(scandir($settingsPath))
            ->filter(fn ($file) => str_ends_with($file, 'Settings.php'))
            ->mapWithKeys(function ($file) {
                $className = str_replace('.php', '', $file);
                $group = Str::kebab(str_replace('Settings', '', $className));

                return [$group => "App\\Settings\\{$className}"];
            })
            ->toArray();
    }

    /**
     * Get group settings directly without using cache
     */
    protected function groupWithoutCache(string $group)
    {
        $settingsClasses = $this->getSettingsClassesWithoutCache();

        if (! isset($settingsClasses[$group])) {
            throw SettingsException::classNotFound($group);
        }

        return app($settingsClasses[$group]);
    }
}
