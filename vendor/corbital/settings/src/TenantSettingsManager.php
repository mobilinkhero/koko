<?php

namespace Corbital\Settings;

use Corbital\Settings\Events\SettingUpdated;
use Corbital\Settings\Exceptions\SettingsException;
use Corbital\Settings\Repositories\TenantSettingsRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TenantSettingsManager extends SettingsManager
{
    /**
     * The tenant ID.
     */
    protected string|int $tenantId;

    /**
     * The tenant settings repository.
     */
    protected ?TenantSettingsRepository $repository = null;

    /**
     * Lock timeout in seconds.
     */
    protected int $lockTimeout = 10;

    /**
     * Lock retry delay in milliseconds.
     */
    protected int $lockRetryDelay = 100;

    /**
     * Create a new tenant settings manager instance.
     */
    public function __construct(string|int $tenantId)
    {
        $this->app = app();

        // Use Spatie's cache configuration with fallbacks to our enhanced settings
        $cacheConfig = config('settings.cache', []);
        $this->cacheTtl = $cacheConfig['ttl'] ?? config('settings.cache_ttl', 3600);
        $this->cachePrefix = $cacheConfig['prefix'] ?? config('settings.cache_prefix', 'settings_');

        $this->tenantId = $tenantId;
        $this->lockTimeout = config('settings.lock_timeout', 10);
        $this->lockRetryDelay = config('settings.lock_retry_delay', 100);
    }

    /**
     * Get the tenant settings repository.
     */
    protected function getRepository(): TenantSettingsRepository
    {
        if ($this->repository === null) {
            $this->repository = app(TenantSettingsRepository::class);
        }

        return $this->repository;
    }

    /**
     * Get the tenant-specific cache key with enhanced security.
     */
    protected function getCacheKey(string $key): string
    {
        // Use hash to prevent key length issues and add security
        $tenantHash = hash('xxh3', (string) $this->tenantId);
        $keyHash = hash('xxh3', $key);

        return "{$this->cachePrefix}tn_{$tenantHash}_{$keyHash}";
    }

    /**
     * Get the lock key for cache operations.
     */
    protected function getLockKey(string $key): string
    {
        return "lock:settings:tenant_{$this->tenantId}:{$key}";
    }

    /**
     * Acquire a distributed lock.
     */
    protected function acquireLock(string $key): bool
    {
        $lockKey = $this->getLockKey($key);
        $lockValue = uniqid();

        try {
            if (class_exists('Illuminate\Support\Facades\Redis') && config('cache.default') === 'redis') {
                return Redis::set($lockKey, $lockValue, 'EX', $this->lockTimeout, 'NX');
            }

            // Fallback to cache-based locking
            return Cache::add($lockKey, $lockValue, $this->lockTimeout);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Release a distributed lock.
     */
    protected function releaseLock(string $key): bool
    {
        $lockKey = $this->getLockKey($key);

        try {
            if (class_exists('Illuminate\Support\Facades\Redis') && config('cache.default') === 'redis') {
                return Redis::del($lockKey) > 0;
            }

            // Fallback to cache-based locking
            return Cache::forget($lockKey);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Get a setting with lock protection.
     */
    protected function getWithLock(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->getCacheKey($key);

        // Try to get from cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Acquire lock for cache regeneration
        $lockKey = "get_{$key}";
        if (! $this->acquireLock($lockKey)) {
            // If we can't acquire lock, try to get from cache again (might be populated by another process)
            return Cache::get($cacheKey, $default);
        }

        try {
            // Double-check cache after acquiring lock
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Generate value and cache it
            [$group, $setting] = explode('.', $key);
            $value = $this->getDirectFromRepository($group, $setting, $default);

            Cache::put($cacheKey, $value, $this->cacheTtl);

            return $value;
        } finally {
            $this->releaseLock($lockKey);
        }
    }

    /**
     * Get a setting directly from repository without caching.
     */
    protected function getDirectFromRepository(string $group, string $key, mixed $default = null): mixed
    {
        $value = $this->getRepository()->get($this->tenantId, $group, $key);

        return $value ?? $default;
    }

    /**
     * Get a setting directly from the repository.
     */
    protected function getFromRepository(string $group, string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->getCacheKey("{$group}.{$key}");

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($group, $key, $default) {
            $value = $this->getRepository()->get($this->tenantId, $group, $key);

            return $value ?? $default;
        });
    }

    /**
     * Set a setting directly in the repository.
     */
    protected function setInRepository(string $group, string $key, mixed $value): bool
    {
        $oldValue = $this->getDirectFromRepository($group, $key);
        $result = $this->getRepository()->set($this->tenantId, $group, $key, $value);

        // Clear cache
        Cache::forget($this->getCacheKey("{$group}.{$key}"));
        Cache::forget($this->getCacheKey($group));

        // Dispatch event
        if ($oldValue !== $value) {
            event(new SettingUpdated($group, $key, $value, $oldValue, $this->tenantId));
        }

        return $result;
    }

    /**
     * Flush all settings for this tenant.
     */
    public function flush(): bool
    {
        $lockKey = 'flush_all';

        if (! $this->acquireLock($lockKey)) {
            throw new SettingsException('Could not acquire lock for flush operation');
        }

        try {
            $result = $this->getRepository()->flush($this->tenantId);

            if ($result) {
                $this->clearCache();
            }

            return $result;
        } finally {
            $this->releaseLock($lockKey);
        }
    }

    /**
     * Set multiple settings in bulk.
     */
    public function setBulk(array $settings): bool
    {
        if (empty($settings)) {
            return true;
        }

        $lockKey = 'bulk_set_'.md5(serialize(array_keys($settings)));

        if (! $this->acquireLock($lockKey)) {
            throw new SettingsException('Could not acquire lock for bulk operation');
        }

        try {
            $success = $this->getRepository()->setBulk($this->tenantId, $settings);

            if ($success) {
                // Clear affected cache entries
                foreach ($settings as $key => $value) {
                    if (str_contains($key, '.')) {
                        [$group, $setting] = explode('.', $key);
                        Cache::forget($this->getCacheKey("{$group}.{$setting}"));
                        Cache::forget($this->getCacheKey($group));
                    }
                }
            }

            return $success;
        } finally {
            $this->releaseLock($lockKey);
        }
    }

    /**
     * Get multiple settings in bulk.
     */
    public function getBulk(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $lockKey = 'bulk_get_'.md5(serialize($keys));

        if (! $this->acquireLock($lockKey)) {
            // Fallback to individual gets if lock acquisition fails
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $this->get($key);
            }

            return $result;
        }

        try {
            return $this->getRepository()->getBulk($this->tenantId, $keys);
        } finally {
            $this->releaseLock($lockKey);
        }
    }

    /**
     * Delete multiple settings in bulk.
     */
    public function deleteBulk(array $keys): bool
    {
        if (empty($keys)) {
            return true;
        }

        $lockKey = 'bulk_delete_'.md5(serialize($keys));

        if (! $this->acquireLock($lockKey)) {
            throw new SettingsException('Could not acquire lock for bulk operation');
        }

        try {
            $success = $this->getRepository()->deleteBulk($this->tenantId, $keys);

            if ($success) {
                // Clear affected cache entries
                foreach ($keys as $key) {
                    if (str_contains($key, '.')) {
                        [$group, $setting] = explode('.', $key);
                        Cache::forget($this->getCacheKey("{$group}.{$setting}"));
                        Cache::forget($this->getCacheKey($group));
                    }
                }
            }

            return $success;
        } finally {
            $this->releaseLock($lockKey);
        }
    }

    /**
     * Warm cache with common settings.
     */
    public function warmCache(array $keys = []): bool
    {
        if (empty($keys)) {
            // Default common settings to warm
            $keys = config('settings.warm_cache_keys', [
                'app.name',
                'app.timezone',
                'app.locale',
                'app.currency',
            ]);
        }

        $lockKey = 'cache_warm';

        if (! $this->acquireLock($lockKey)) {
            return false;
        }

        try {
            $settings = $this->getBulk($keys);

            // Cache each setting individually for faster access
            foreach ($settings as $key => $value) {
                $cacheKey = $this->getCacheKey($key);
                Cache::put($cacheKey, $value, $this->cacheTtl);
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        } finally {
            $this->releaseLock($lockKey);
        }
    }

    /**
     * Preload common settings for performance.
     */
    public function preloadCommon(): void
    {
        $this->warmCache();
    }

    /**
     * Get a setting with enhanced security and performance.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            if (! str_contains($key, '.')) {
                throw SettingsException::invalidKeyFormat($key);
            }

            // Use lock-protected get for critical operations
            return $this->getWithLock($key, $default);

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
     */
    public function set(string $key, mixed $value): bool
    {
        if (! str_contains($key, '.')) {
            throw SettingsException::invalidKeyFormat($key);
        }

        [$group, $setting] = explode('.', $key);

        // First check if we have a settings class for this group
        $settingsClasses = $this->getSettingsClasses();

        if (! isset($settingsClasses[$group])) {
            // For tenant settings, store directly in repository
            return $this->setInRepository($group, $setting, $value);
        }

        $settings = $this->group($group);

        if (! property_exists($settings, $setting)) {
            throw SettingsException::propertyNotFound($group, $setting);
        }

        $oldValue = $settings->$setting;
        $settings->$setting = $value;
        $settings->save();

        // Clear the specific setting cache
        Cache::forget($this->getCacheKey("{$group}.{$setting}"));
        Cache::forget($this->getCacheKey($group));

        // Dispatch event
        if ($oldValue !== $value) {
            // Modified to match SettingUpdated constructor params
            event(new SettingUpdated($group, $setting, $value, $oldValue, $this->tenantId));
        }

        return true;
    }

    /**
     * Clear all settings cache for this tenant.
     */
    public function clearCache(): bool
    {
        try {
            // Clear the settings classes cache
            Cache::forget($this->getCacheKey('classes'));

            // Clear individual setting caches
            foreach ($this->getSettingsClasses() as $group => $class) {
                try {
                    $settings = app($class);
                    Cache::forget($this->getCacheKey($group));

                    foreach (get_object_vars($settings) as $key => $value) {
                        if (! str_starts_with($key, '_')) {
                            Cache::forget($this->getCacheKey("{$group}.{$key}"));
                        }
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Also clear repository-based settings if available
            try {
                $allSettings = $this->getRepository()->getAll($this->tenantId);

                foreach ($allSettings as $group => $settings) {
                    Cache::forget($this->getCacheKey($group));

                    foreach ($settings as $key => $value) {
                        Cache::forget($this->getCacheKey("{$group}.{$key}"));
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
