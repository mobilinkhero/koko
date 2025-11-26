<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Facade;

/**
 * Tenant Cache Facade
 *
 * Provides a simple interface to use TenantCacheManager throughout the application
 *
 * @method static mixed remember(string $key, int $ttl, callable $callback, array $tags = [])
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool put(string $key, mixed $value, int $ttl = 3600, array $tags = [])
 * @method static bool forget(string $key)
 * @method static bool clearByTag(string|array $tags)
 * @method static bool flush()
 * @method static array getStatistics()
 * @method static array getHealthAssessment()
 */
class TenantCache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tenant.cache';
    }
}
