<?php

namespace App\Facades;

use App\Events\Cache\TenantCacheHit;
use App\Events\Cache\TenantCacheMiss;
use App\Events\Cache\TenantCacheWrite;
use App\Services\Cache\TenantCacheManager;
use Illuminate\Support\Facades\Facade;

/**
 * Tenant Cache Facade
 *
 * Unified tenant cache interface that automatically handles:
 * - Tenant isolation
 * - Event tracking (hit/miss/write)
 * - Tag-based organization
 * - Health monitoring
 * - Performance analytics
 *
 * @method static mixed get(string $key, $default = null)
 * @method static bool put(string $key, $value, int $ttl = 3600, array $tags = [])
 * @method static mixed remember(string $key, int $ttl, callable $callback, array $tags = [])
 * @method static bool forget(string $key)
 * @method static bool flush()
 * @method static bool clearByTag(string|array $tags)
 * @method static array warm(array $warmingData)
 * @method static array getStatistics()
 * @method static array getHealthAssessment()
 * @method static bool has(string $key)
 *
 * @see TenantCacheManager
 */
class TenantCache extends Facade
{
    /**
     * Current tenant cache manager instance
     */
    protected static ?TenantCacheManager $manager = null;

    /**
     * Current tenant ID
     */
    protected static ?string $currentTenantId = null;

    /**
     * Get the current tenant cache manager
     */
    protected static function getManager(): TenantCacheManager
    {
        $tenantId = static::getCurrentTenantId();

        if (! static::$manager || static::$currentTenantId !== $tenantId) {
            static::$manager = new TenantCacheManager($tenantId);
            static::$currentTenantId = $tenantId;
        }

        return static::$manager;
    }

    /**
     * Get current tenant ID
     */
    protected static function getCurrentTenantId(): string
    {
        if (function_exists('tenant_id')) {
            return tenant_id() ?: 'default';
        }

        // Try to get from global helper
        try {
            if (function_exists('tenant') && ($tenant = \tenant())) {
                return $tenant->id;
            }
        } catch (\Exception $e) {
            // Ignore tenant detection errors
        }

        return 'default';
    }

    /**
     * Cache data with automatic event tracking
     */
    public static function get(string $key, $default = null)
    {
        $manager = static::getManager();
        $tenantId = static::$currentTenantId;
        $value = $manager->get($key, $default);

        // Fire appropriate event
        if ($value !== $default) {
            event(new TenantCacheHit($tenantId, $key));
        } else {
            event(new TenantCacheMiss($tenantId, $key));
        }

        return $value;
    }

    /**
     * Store data in cache with automatic event tracking
     */
    public static function put(string $key, $value, int $ttl = 3600, array $tags = []): bool
    {
        $manager = static::getManager();
        $tenantId = static::$currentTenantId;
        $result = $manager->put($key, $value, $ttl, $tags);

        if ($result) {
            event(new TenantCacheWrite($tenantId, $key, $tags));
        }

        return $result;
    }

    /**
     * Remember data with automatic event tracking
     */
    public static function remember(string $key, int $ttl, callable $callback, array $tags = [])
    {
        $manager = static::getManager();
        $tenantId = static::$currentTenantId;

        // Check if key exists first
        if ($manager->has($key)) {
            $value = $manager->get($key);
            event(new TenantCacheHit($tenantId, $key));

            return $value;
        }

        // Generate value and cache it
        $value = $callback();
        $manager->put($key, $value, $ttl, $tags);

        event(new TenantCacheMiss($tenantId, $key));
        event(new TenantCacheWrite($tenantId, $key, $tags));

        return $value;
    }

    /**
     * Forget a cache key
     */
    public static function forget(string $key): bool
    {
        return static::getManager()->forget($key);
    }

    /**
     * Clear all tenant cache
     */
    public static function flush(): bool
    {
        return static::getManager()->flush();
    }

    /**
     * Clear cache by tags
     */
    public static function clearByTag(string|array $tags): bool
    {
        $tags = is_array($tags) ? $tags : [$tags];
        static::getManager()->invalidateByTags($tags);

        return true;
    }

    /**
     * Warm cache with predefined data
     */
    public static function warm(array $warmingData): array
    {
        return static::getManager()->warm($warmingData);
    }

    /**
     * Get cache statistics
     */
    public static function getStatistics(): array
    {
        return static::getManager()->getStatistics();
    }

    /**
     * Get health assessment
     */
    public static function getHealthAssessment(): array
    {
        return static::getManager()->getHealthAssessment();
    }

    /**
     * Check if key exists
     */
    public static function has(string $key): bool
    {
        return static::getManager()->has($key);
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tenant.cache';
    }

    /**
     * Helper method for easy integration - Laravel Cache::remember style
     */
    public static function rememberForever(string $key, callable $callback, array $tags = [])
    {
        return static::remember($key, 86400 * 30, $callback, $tags); // 30 days
    }

    /**
     * Helper method for tags - clear multiple tags at once
     */
    public static function clearTags(array $tags): bool
    {
        return static::clearByTag($tags);
    }

    /**
     * Helper method for optimization - warm common tenant data
     */
    public static function warmCommonData(): array
    {
        return static::warm([
            [
                'key' => 'tenant.settings',
                'callback' => function () {
                    // Load tenant settings
                    return ['theme' => 'default', 'timezone' => 'UTC'];
                },
                'ttl' => 3600,
                'tags' => ['settings', 'tenant'],
            ],
            [
                'key' => 'tenant.permissions',
                'callback' => function () {
                    // Load user permissions
                    return ['dashboard' => true, 'reports' => true];
                },
                'ttl' => 1800,
                'tags' => ['permissions', 'tenant'],
            ],
        ]);
    }
}
