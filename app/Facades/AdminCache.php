<?php

namespace App\Facades;

use App\Services\Cache\AdminCacheManager;
use Illuminate\Support\Facades\Facade;

/**
 * Admin Cache Facade
 *
 * Provides easy access to the AdminCacheManager through a static interface.
 *
 * @method static bool put(string $key, $value, array $tags = [], ?int $ttl = null)
 * @method static mixed get(string $key, $default = null)
 * @method static mixed remember(string $key, callable $callback, array $tags = [], ?int $ttl = null)
 * @method static bool invalidateTags(array $tags)
 * @method static bool refresh(string $key, callable $callback = null, array $tags = [])
 * @method static bool warm(string $tagOrKey, array $warmupData = [])
 * @method static bool flush()
 * @method static void addDependency(string $sourceTag, string $dependentTag)
 * @method static array getStatistics()
 * @method static array getCacheStatistics()
 * @method static void handleModelEvent(string $modelClass, string $event, $model = null)
 * @method static bool trigger(string $action, array $params = [])
 *
 * @see AdminCacheManager
 */
class AdminCache extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return AdminCacheManager::class;
    }
}
