<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Centralized Admin Cache Manager
 *
 * Provides a powerful, flexible, and event-driven cache management system
 * specifically designed for admin operations with tag-based organization.
 */
class AdminCacheManager
{
    protected AdminCacheTagRegistry $tagRegistry;

    protected array $config;

    protected string $cacheDriver;

    protected array $dependencies = [];

    public function __construct(AdminCacheTagRegistry $tagRegistry)
    {
        $this->tagRegistry = $tagRegistry;
        $this->config = config('admin-cache', []);

        // Use configured cache store or fall back to default
        $this->cacheDriver = $this->config['store'] ?? config('cache.default');

        $this->loadDependencies();
    }

    /**
     * Get the cache store instance
     */
    protected function getCacheStore()
    {
        if ($this->config['store']) {
            return Cache::store($this->config['store']);
        }

        return Cache::store();
    }

    /**
     * Store data in cache with tags
     */
    public function put(string $key, $value, array $tags = [], ?int $ttl = null): bool
    {
        try {
            $fullKey = $this->buildKey($key);
            $ttl = $ttl ?? $this->getDefaultTtl($tags);

            if ($this->supportsTags() && ! empty($tags)) {
                // Use method_exists to safely call tags method
                $store = $this->getCacheStore();
                if (method_exists($store, 'tags')) {
                    return $store->tags($this->prepareTags($tags))->put($fullKey, $value, $ttl);
                }
            }

            // Fallback for drivers that don't support tags
            $this->storeTagMapping($fullKey, $tags);

            return $this->getCacheStore()->put($fullKey, $value, $ttl);

        } catch (\Exception $e) {
            app_log('AdminCacheManager: Failed to store cache', 'error', $e, [
                'key' => $key,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Retrieve data from cache
     */
    public function get(string $key, $default = null)
    {
        try {
            $fullKey = $this->buildKey($key);
            $value = $this->getCacheStore()->get($fullKey, $default);

            // Track hit/miss for hit rate calculation
            if ($value !== $default) {
                $this->incrementHitCount();
            } else {
                $this->incrementMissCount();
            }

            return $value;
        } catch (\Exception $e) {
            app_log('AdminCacheManager: Failed to retrieve cache', 'error', $e, [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $default;
        }
    }

    /**
     * Remember data in cache with tags
     */
    public function remember(string $key, callable $callback, array $tags = [], ?int $ttl = null)
    {
        try {
            $fullKey = $this->buildKey($key);
            $ttl = $ttl ?? $this->getDefaultTtl($tags);

            // Check if value exists first for hit/miss tracking
            $existingValue = $this->getCacheStore()->get($fullKey);

            if ($existingValue !== null) {
                $this->incrementHitCount();

                return $existingValue;
            }

            // Value doesn't exist, so it's a miss - compute and store
            $this->incrementMissCount();
            $result = $callback();

            // Store the computed result
            $this->put($key, $result, $tags, $ttl);

            return $result;

        } catch (\Exception $e) {
            app_log('AdminCacheManager: Failed to remember cache', 'error', $e, [
                'key' => $key,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return $callback();
        }
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateTags(array $tags): bool
    {
        try {
            $tags = $this->prepareTags($tags);

            if ($this->supportsTags()) {
                Cache::tags($tags)->flush();
            } else {
                $this->flushByTagMapping($tags);
            }

            // Also clear dependent tags
            $this->clearDependentTags($tags);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Invalidate cache by a single tag
     */
    public function invalidateTag(string $tag): bool
    {
        return $this->invalidateTags([$tag]);
    }

    /**
     * Refresh specific cache by key and tags
     */
    public function refresh(string $key, ?callable $callback = null, array $tags = []): bool
    {
        try {
            $fullKey = $this->buildKey($key);

            // Remove existing cache
            if ($this->supportsTags() && ! empty($tags)) {
                Cache::tags($this->prepareTags($tags))->forget($fullKey);
            } else {
                Cache::forget($fullKey);
            }

            // Refresh with new data if callback provided
            if ($callback) {
                $this->remember($key, $callback, $tags);
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Warm up cache proactively
     */
    public function warm(string $tagOrKey, array $warmupData = []): bool
    {
        try {
            $strategy = $this->tagRegistry->getWarmupStrategy($tagOrKey);

            if ($strategy && method_exists($this, 'warm'.ucfirst($strategy))) {
                return $this->{'warm'.ucfirst($strategy)}($warmupData);
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear all admin cache
     */
    public function flush(): bool
    {
        try {
            $adminTags = $this->tagRegistry->getAllAdminTags();

            return $this->invalidateTags($adminTags);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add cache dependency (when tagA changes, also clear tagB)
     */
    public function addDependency(string $sourceTag, string $dependentTag): void
    {
        if (! isset($this->dependencies[$sourceTag])) {
            $this->dependencies[$sourceTag] = [];
        }

        if (! in_array($dependentTag, $this->dependencies[$sourceTag])) {
            $this->dependencies[$sourceTag][] = $dependentTag;
        }
    }

    /**
     * Get cache statistics for admin dashboard
     */
    public function getStatistics(): array
    {
        try {
            return [
                'driver' => $this->cacheDriver,
                'supports_tags' => $this->supportsTags(),
                'total_tags' => count($this->tagRegistry->getAllAdminTags()),
                'dependencies_count' => count($this->dependencies),
                'last_flush' => $this->get('admin.cache.last_flush'),
                'hit_rate' => $this->calculateHitRate(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle model events for automatic cache invalidation
     */
    public function handleModelEvent(string $modelClass, string $event, $model = null): void
    {
        try {
            $tags = $this->tagRegistry->getTagsForModel($modelClass, $event, $model);

            if (! empty($tags)) {
                $this->invalidateTags($tags);
            }
        } catch (\Exception $e) {
            app_log('AdminCacheManager: Failed to handle model event', 'error', $e, [
                'model' => $modelClass,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Manual trigger for cache operations
     */
    public function trigger(string $action, array $params = []): bool
    {
        try {
            switch ($action) {
                case 'refresh_navigation':
                    return $this->refreshNavigation($params);

                case 'refresh_dashboard':
                    return $this->refreshDashboard($params);

                case 'refresh_statistics':
                    return $this->refreshStatistics($params);

                case 'warm_critical':
                    return $this->warmCriticalCaches($params);

                default:
                    return false;
            }
        } catch (\Exception $e) {
            app_log('AdminCacheManager: Failed to execute trigger', 'warning', $e, [
                'action' => $action,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // Protected helper methods

    protected function buildKey(string $key): string
    {
        return 'admin.'.$key;
    }

    protected function prepareTags(array $tags): array
    {
        return array_map(function ($tag) {
            return strpos($tag, 'admin.') === 0 ? $tag : 'admin.'.$tag;
        }, $tags);
    }

    protected function supportsTags(): bool
    {
        return in_array($this->cacheDriver, ['redis', 'memcached']);
    }

    protected function getDefaultTtl(array $tags): int
    {
        return $this->tagRegistry->getTtlForTags($tags) ?? config('cache.ttl', 3600);
    }

    protected function clearDependentTags(array $clearedTags): void
    {
        foreach ($clearedTags as $tag) {
            if (isset($this->dependencies[$tag])) {
                $this->invalidateTags($this->dependencies[$tag]);
            }
        }
    }

    protected function storeTagMapping(string $key, array $tags): void
    {
        if (! empty($tags)) {
            foreach ($tags as $tag) {
                $tagKeys = Cache::get("tag_mapping.{$tag}", []);
                $tagKeys[] = $key;
                Cache::put("tag_mapping.{$tag}", array_unique($tagKeys), 86400);
            }
        }
    }

    protected function flushByTagMapping(array $tags): void
    {
        foreach ($tags as $tag) {
            $keys = Cache::get("tag_mapping.{$tag}", []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget("tag_mapping.{$tag}");
        }
    }

    protected function loadDependencies(): void
    {
        $this->dependencies = config('admin-cache.dependencies', [
            'admin.plans' => ['admin.navigation', 'admin.dashboard'],
            'admin.users' => ['admin.dashboard', 'admin.statistics'],
            'admin.tenants' => ['admin.dashboard', 'admin.statistics'],
            'admin.settings' => ['admin.navigation'],
        ]);
    }

    protected function calculateHitRate(): float
    {
        try {
            // Get hit and miss counts from cache using proper keys
            $hitKey = $this->buildKey('cache_hits');
            $missKey = $this->buildKey('cache_misses');

            $hits = $this->getCacheStore()->get($hitKey, 0);
            $misses = $this->getCacheStore()->get($missKey, 0);

            $total = $hits + $misses;

            if ($total === 0) {
                return 0.0;
            }

            return round(($hits / $total) * 100, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // Specific refresh methods

    protected function refreshNavigation(array $params = []): bool
    {
        return $this->invalidateTags(['admin.navigation']);
    }

    protected function refreshDashboard(array $params = []): bool
    {
        return $this->invalidateTags(['admin.dashboard']);
    }

    protected function refreshStatistics(array $params = []): bool
    {
        return $this->invalidateTags(['admin.statistics']);
    }

    protected function warmCriticalCaches(array $params = []): bool
    {
        // Implement critical cache warming
        return true;
    }

    /**
     * Get comprehensive cache statistics
     */
    public function getCacheStatistics(): array
    {
        try {
            return [
                'driver' => $this->cacheDriver,
                'total_keys' => $this->countTotalKeys(),
                'total_size' => $this->calculateTotalSize(),
                'hit_rate' => $this->calculateHitRate(),
                'last_cleared' => $this->getLastClearTime(),
                'uptime' => $this->getCacheUptime(),
                'tags' => $this->getTagStatistics(),
                'health' => $this->performHealthCheck(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to retrieve statistics',
                'driver' => $this->cacheDriver,
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    /**
     * Count total cache keys
     */
    protected function countTotalKeys(): int
    {
        try {
            // Build the correct prefix pattern to match buildKey() method
            // buildKey() returns 'admin.' + key, so we need cache_prefix + 'admin.'
            $prefix = config('cache.prefix').'admin.';

            switch ($this->cacheDriver) {
                case 'database':
                    return $this->countDatabaseKeys($prefix);
                case 'redis':
                    return $this->countRedisKeys($prefix);
                case 'file':
                    return $this->countFileKeys($prefix);
                case 'array':
                    return $this->countArrayKeys($prefix);
                default:
                    return $this->countGenericKeys($prefix);
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count keys in database cache
     */
    protected function countDatabaseKeys(string $prefix): int
    {
        try {
            $count = DB::table('cache')
                ->where('key', 'like', $prefix.'%')
                ->count();

            return $count;
        } catch (\Exception $e) {
            app_log('AdminCache: Failed to count database keys', 'error', $e);

            return 0;
        }
    }

    /**
     * Count keys in Redis cache
     */
    protected function countRedisKeys(string $prefix): int
    {
        try {
            $store = $this->getCacheStore();
            if (method_exists($store, 'getRedis')) {
                $redis = $store->getRedis();

                return count($redis->keys($prefix.'*'));
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count keys in file cache
     */
    protected function countFileKeys(string $prefix): int
    {
        try {
            $path = config('cache.stores.file.path', storage_path('framework/cache/data'));
            if (! is_dir($path)) {
                return 0;
            }

            $files = glob($path.'/*');
            $count = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $content = file_get_contents($file);
                    // Check if the cached key contains our prefix
                    if (strpos($content, $prefix) !== false) {
                        $count++;
                    }
                }
            }

            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count keys in array cache
     */
    protected function countArrayKeys(string $prefix): int
    {
        try {
            // Array cache doesn't have a reliable way to count keys
            // This is just an estimation for testing purposes
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Generic key counting fallback
     */
    protected function countGenericKeys(string $prefix): int
    {
        // For drivers we don't have specific implementation
        return 0;
    }

    /**
     * Count database cache keys for specific tag
     */
    protected function countDatabaseKeysForTag(string $prefix, string $tag): int
    {
        try {
            // For database cache, we need to check our tag mapping
            $tagMappingKey = $prefix.'.tag_mapping.'.$tag;
            $keys = $this->getCacheStore()->get($tagMappingKey, []);

            if (empty($keys)) {
                return 0;
            }

            // Count how many of these keys actually exist
            $existingCount = 0;
            foreach ($keys as $key) {
                if (DB::table('cache')->where('key', $key)->exists()) {
                    $existingCount++;
                }
            }

            return $existingCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count Redis cache keys for specific tag
     */
    protected function countRedisKeysForTag(string $prefix, string $tag): int
    {
        try {
            // For Redis, we can use the tag mapping or pattern matching
            $tagMappingKey = $prefix.'.tag_mapping.'.$tag;
            $keys = $this->getCacheStore()->get($tagMappingKey, []);

            if (empty($keys)) {
                return 0;
            }

            // Count existing keys
            $existingCount = 0;
            foreach ($keys as $key) {
                if ($this->getCacheStore()->has($key)) {
                    $existingCount++;
                }
            }

            return $existingCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count file cache keys for specific tag
     */
    protected function countFileKeysForTag(string $prefix, string $tag): int
    {
        try {
            // For file cache, we use tag mapping
            $tagMappingKey = $prefix.'.tag_mapping.'.$tag;
            $keys = $this->getCacheStore()->get($tagMappingKey, []);

            if (empty($keys)) {
                return 0;
            }

            // Count existing keys
            $existingCount = 0;
            foreach ($keys as $key) {
                if ($this->getCacheStore()->has($key)) {
                    $existingCount++;
                }
            }

            return $existingCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count array cache keys for specific tag
     */
    protected function countArrayKeysForTag(string $prefix, string $tag): int
    {
        try {
            // For array cache, we use tag mapping
            $tagMappingKey = $prefix.'.tag_mapping.'.$tag;
            $keys = $this->getCacheStore()->get($tagMappingKey, []);

            if (empty($keys)) {
                return 0;
            }

            // Count existing keys
            $existingCount = 0;
            foreach ($keys as $key) {
                if ($this->getCacheStore()->has($key)) {
                    $existingCount++;
                }
            }

            return $existingCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count keys for tag using generic method
     */
    protected function countGenericKeysForTag(string $prefix, string $tag): int
    {
        try {
            // Generic fallback using tag mapping
            $tagMappingKey = $prefix.'.tag_mapping.'.$tag;
            $keys = $this->getCacheStore()->get($tagMappingKey, []);

            return count($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate total cache size
     */
    protected function calculateTotalSize(): string
    {
        try {
            $prefix = config('cache.prefix').config('admin-cache.prefix', 'admin');

            switch ($this->cacheDriver) {
                case 'database':
                    return $this->calculateDatabaseSize($prefix);
                case 'redis':
                    return $this->calculateRedisSize($prefix);
                case 'file':
                    return $this->calculateFileSize($prefix);
                case 'array':
                    return $this->calculateArraySize($prefix);
                default:
                    return 'N/A';
            }
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get last cache clear time
     */
    protected function getLastClearTime(): string
    {
        return Cache::get('admin:last_cleared', 'Never');
    }

    /**
     * Get cache uptime
     */
    protected function getCacheUptime(): string
    {
        $startTime = Cache::get('admin:start_time');
        if (! $startTime) {
            Cache::put('admin:start_time', now(), 86400 * 30); // 30 days

            return 'Just started';
        }

        return now()->diffForHumans($startTime);
    }

    /**
     * Get statistics for all tags
     */
    protected function getTagStatistics(): array
    {
        $tags = $this->tagRegistry->getAllTags();
        $stats = [];

        foreach ($tags as $tag => $config) {
            $stats[$tag] = [
                'keys' => $this->countKeysForTag($tag),
                'size' => 'N/A', // Can be enhanced
                'ttl' => $config['ttl'] ?? $this->config['default_ttl'] ?? 3600,
            ];
        }

        return $stats;
    }

    /**
     * Count keys for a specific tag
     */
    protected function countKeysForTag(string $tag): int
    {
        try {
            $prefix = config('cache.prefix').config('admin-cache.prefix', 'admin');

            switch ($this->cacheDriver) {
                case 'database':
                    return $this->countDatabaseKeysForTag($prefix, $tag);
                case 'redis':
                    return $this->countRedisKeysForTag($prefix, $tag);
                case 'file':
                    return $this->countFileKeysForTag($prefix, $tag);
                case 'array':
                    return $this->countArrayKeysForTag($prefix, $tag);
                default:
                    return $this->countGenericKeysForTag($prefix, $tag);
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Perform basic health check
     */
    protected function performHealthCheck(): array
    {
        try {
            // Test basic cache operations
            $testKey = 'admin:health_check';
            $testValue = 'test_'.time();

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $retrieved === $testValue ? 'healthy' : 'degraded',
                'write' => true,
                'read' => $retrieved === $testValue,
                'delete' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'write' => false,
                'read' => false,
                'delete' => false,
            ];
        }
    }

    /**
     * Calculate database cache size
     */
    protected function calculateDatabaseSize(string $prefix): string
    {
        try {
            $result = DB::table('cache')
                ->where('key', 'like', $prefix.'%')
                ->selectRaw('SUM(LENGTH(value)) as total_size')
                ->first();

            $sizeBytes = $result->total_size ?? 0;

            return $this->formatBytes($sizeBytes);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Calculate Redis cache size
     */
    protected function calculateRedisSize(string $prefix): string
    {
        try {
            $store = $this->getCacheStore();
            if (! method_exists($store, 'connection')) {
                return 'N/A';
            }

            $redis = $store->connection();
            $keys = $redis->keys($prefix.'*');

            $totalSize = 0;
            foreach ($keys as $key) {
                try {
                    $totalSize += $redis->memory('usage', $key) ?? 0;
                } catch (\Exception $e) {
                    // Skip if memory command not available
                    $totalSize += strlen($redis->get($key) ?? '');
                }
            }

            return $this->formatBytes($totalSize);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Calculate file cache size
     */
    protected function calculateFileSize(string $prefix): string
    {
        try {
            $cachePath = storage_path('framework/cache/data');
            if (! is_dir($cachePath)) {
                return '0 B';
            }

            $totalSize = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cachePath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }
            }

            return $this->formatBytes($totalSize);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Calculate array cache size (in-memory)
     */
    protected function calculateArraySize(string $prefix): string
    {
        try {
            // For array cache, we can estimate size by checking stored data
            $store = $this->getCacheStore();

            // Since we can't directly access array cache memory,
            // we'll estimate based on our tag mappings
            $tagMappings = $this->getCacheStore()->get($prefix.'.tag_mapping.*', []);
            $estimatedKeys = 0;

            if (is_array($tagMappings)) {
                foreach ($tagMappings as $keys) {
                    if (is_array($keys)) {
                        $estimatedKeys += count($keys);
                    }
                }
            }

            // Estimate 1KB per key (rough estimation)
            $estimatedSize = $estimatedKeys * 1024;

            return $this->formatBytes($estimatedSize);
        } catch (\Exception $e) {
            return 'Estimated';
        }
    }

    /**
     * Format bytes into human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = floor(log($bytes, 1024));
        $size = round($bytes / pow(1024, $unitIndex), 2);

        return $size.' '.$units[$unitIndex];
    }

    /**
     * Increment cache hit count
     */
    protected function incrementHitCount(): void
    {
        try {
            $hitKey = $this->buildKey('cache_hits');
            $current = $this->getCacheStore()->get($hitKey, 0);
            $this->getCacheStore()->put($hitKey, $current + 1, 86400 * 30); // 30 days
        } catch (\Exception $e) {
            // Silently fail to avoid breaking cache operations
        }
    }

    /**
     * Increment cache miss count
     */
    protected function incrementMissCount(): void
    {
        try {
            $missKey = $this->buildKey('cache_misses');
            $current = $this->getCacheStore()->get($missKey, 0);
            $this->getCacheStore()->put($missKey, $current + 1, 86400 * 30); // 30 days
        } catch (\Exception $e) {
            // Silently fail to avoid breaking cache operations
        }
    }

    /**
     * Reset hit rate statistics
     */
    public function resetHitRateStats(): void
    {
        try {
            $hitKey = $this->buildKey('cache_hits');
            $missKey = $this->buildKey('cache_misses');

            $this->getCacheStore()->forget($hitKey);
            $this->getCacheStore()->forget($missKey);
        } catch (\Exception $e) {
        }
    }
}
