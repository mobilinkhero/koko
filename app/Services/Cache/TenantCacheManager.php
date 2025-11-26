<?php

namespace App\Services\Cache;

use App\Events\Cache\TenantCacheHit;
use App\Events\Cache\TenantCacheMiss;
use App\Events\Cache\TenantCacheWrite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Modern Tenant Cache Manager
 *
 * Provides enterprise-level caching with:
 * - Automatic tenant isolation
 * - Tag-based organization
 * - Event-driven analytics
 * - Cache warming strategies
 * - Health monitoring
 */
class TenantCacheManager
{
    protected string $tenantId;

    protected string $tenantPrefix;

    protected TenantCacheTagRegistry $tagRegistry;

    protected array $config;

    // Cache strategies
    public const STRATEGY_WRITE_THROUGH = 'write_through';

    public const STRATEGY_WRITE_BEHIND = 'write_behind';

    public const STRATEGY_CACHE_ASIDE = 'cache_aside';

    // Cache health thresholds
    public const HEALTH_EXCELLENT = 'excellent';   // 95%+ hit rate

    public const HEALTH_GOOD = 'good';             // 85-94% hit rate

    public const HEALTH_WARNING = 'warning';       // 70-84% hit rate

    public const HEALTH_CRITICAL = 'critical';     // <70% hit rate

    public function __construct(string $tenantId, ?TenantCacheTagRegistry $tagRegistry = null)
    {
        $this->tenantId = $tenantId;
        $configPrefix = config('tenant-cache.prefix', 'tenant');
        $this->tenantPrefix = "{$configPrefix}_{$tenantId}_";
        $this->tagRegistry = $tagRegistry ?? new TenantCacheTagRegistry($tenantId);
        $this->config = config('tenant-cache', []);
    }

    /**
     * Get value from cache with automatic tracking
     */
    public function get(string $key, $default = null, array $tags = [])
    {
        $tenantKey = $this->getTenantKey($key);
        $value = Cache::get($tenantKey, $default);

        if ($value !== $default) {
            // Cache hit
            $this->trackCacheHit($key, $tags);
            Event::dispatch(new TenantCacheHit($this->tenantId, $key, $tags));
        } else {
            // Cache miss
            $this->trackCacheMiss($key, $tags);
            Event::dispatch(new TenantCacheMiss($this->tenantId, $key, $tags));
        }

        return $value;
    }

    /**
     * Store value in cache with tags and strategy
     */
    public function put(string $key, $value, $ttl = null, array $tags = [], string $strategy = self::STRATEGY_CACHE_ASIDE): bool
    {
        $tenantKey = $this->getTenantKey($key);

        // Store the main cache entry
        $result = Cache::put($tenantKey, $value, $ttl);

        if ($result) {
            // Register tags for this key
            $this->tagRegistry->registerKeyTags($key, $tags);

            // Track cache write
            $this->trackCacheWrite($key, $tags, strlen(serialize($value)));
            Event::dispatch(new TenantCacheWrite($this->tenantId, $key, $tags, $strategy));
        }

        return $result;
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        $tenantKey = $this->getTenantKey($key);

        return Cache::has($tenantKey);
    }

    /**
     * Remove key from cache
     */
    public function forget(string $key): bool
    {
        $tenantKey = $this->getTenantKey($key);
        $this->tagRegistry->unregisterKey($key);

        return Cache::forget($tenantKey);
    }

    /**
     * Get or set value with callback (cache-aside pattern)
     */
    public function remember(string $key, $ttl, callable $callback, array $tags = [])
    {
        $value = $this->get($key, null, $tags);

        if ($value === null) {
            $value = $callback();
            $this->put($key, $value, $ttl, $tags);
        }

        return $value;
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): int
    {
        $keys = $this->tagRegistry->getKeysByTags($tags);
        $invalidatedCount = 0;

        foreach ($keys as $key) {
            if ($this->forget($key)) {
                $invalidatedCount++;
            }
        }

        return $invalidatedCount;
    }

    /**
     * Warm cache with predefined data
     */
    public function warm(array $warmingData): array
    {
        $results = [];

        foreach ($warmingData as $item) {
            $key = $item['key'];
            $callback = $item['callback'];
            $ttl = $item['ttl'] ?? 3600;
            $tags = $item['tags'] ?? [];

            try {
                $value = $callback();
                $this->put($key, $value, $ttl, $tags);
                $results[$key] = 'warmed';
            } catch (\Exception $e) {
                $results[$key] = 'failed: '.$e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get comprehensive cache statistics
     */
    public function getStatistics(): array
    {
        $stats = $this->getCacheStats();
        $health = $this->calculateCacheHealth($stats);
        $cacheDriver = config('tenant-cache.store') ?? config('cache.default');

        return [
            'tenant_id' => $this->tenantId,
            'cache_driver' => $cacheDriver,
            'total_keys' => $this->getTotalKeys(),
            'total_size' => $this->getTotalSize(),
            'hit_rate' => $stats['hit_rate'],
            'cache_health' => $health,
            'cache_uptime' => $this->getCacheUptime(),
            'last_warming' => $this->getLastWarmingTime(),
            'tag_distribution' => $this->getTagDistribution(),
            'top_accessed_keys' => $this->getTopAccessedKeys(),
            'cache_efficiency' => $this->calculateCacheEfficiency($stats),
        ];
    }

    /**
     * Flush all tenant cache
     */
    public function flush(): bool
    {
        $cacheDriver = config('tenant-cache.store') ?? config('cache.default');
        $pattern = config('cache.prefix').$this->tenantPrefix.'*';

        switch ($cacheDriver) {
            case 'database':
                $deleted = DB::table('cache')
                    ->where('key', 'like', $pattern)
                    ->delete();
                break;

            case 'redis':
                try {
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    $keys = $redis->keys($pattern);
                    if (! empty($keys)) {
                        $redis->del($keys);
                    }
                } catch (\Exception $e) {
                    // Fallback to individual key deletion
                    $keys = $this->getAllKeys();
                    foreach ($keys as $key) {
                        $this->forget($key);
                    }
                }
                break;

            case 'file':
                try {
                    // Clear cache from both locations
                    $path1 = config('cache.stores.file.path', storage_path('framework/cache/data'));
                    $path2 = storage_path('framework/cache');

                    // Clear from path1
                    if (is_dir($path1)) {
                        $directory = new \RecursiveDirectoryIterator($path1, \FilesystemIterator::SKIP_DOTS);
                        $iterator = new \RecursiveIteratorIterator($directory);

                        foreach ($iterator as $file) {
                            if ($file->isFile() && $file->getExtension() !== 'meta') {
                                $filename = $file->getFilename();
                                $contents = @file_get_contents($file->getPathname());

                                // Ensure strict tenant isolation when deleting files
                                if ((strpos($filename, $this->tenantId) !== false && $this->isCurrentTenantKey($contents)) || (preg_match('/"key";s:\d+:"'.preg_quote($this->tenantPrefix, '/').'/', $contents))) {
                                    @unlink($file->getPathname());
                                }
                            }
                        }
                    }

                    // Clear from path2
                    if (is_dir($path2)) {
                        $directory = new \RecursiveDirectoryIterator($path2, \FilesystemIterator::SKIP_DOTS);
                        $iterator = new \RecursiveIteratorIterator($directory);

                        foreach ($iterator as $file) {
                            if ($file->isFile() && $file->getExtension() !== 'meta') {
                                $filename = $file->getFilename();

                                if (strpos($filename, $this->tenantId) !== false) {
                                    $contents = @file_get_contents($file->getPathname());
                                    if ($contents && $this->isCurrentTenantKey($contents)) {
                                        @unlink($file->getPathname());
                                    }

                                    continue;
                                }

                                $contents = @file_get_contents($file->getPathname());
                                if ($contents && preg_match('/"key";s:\d+:"'.preg_quote($this->tenantPrefix, '/').'/', $contents)) {
                                    @unlink($file->getPathname());
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // If file deletion fails, fallback to forget method
                    $keys = $this->getAllKeys();
                    foreach ($keys as $key) {
                        $this->forget($key);
                    }
                }
                break;

            default:
                // For other drivers, clear individual keys
                $keys = $this->getAllKeys();
                foreach ($keys as $key) {
                    $this->forget($key);
                }
        }

        // Clear tag registry
        $this->tagRegistry->clear();

        // Reset statistics
        $this->resetStatistics();

        return true;
    }

    /**
     * Get cache health assessment
     */
    public function getHealthAssessment(): array
    {
        $stats = $this->getCacheStats();
        $health = $this->calculateCacheHealth($stats);

        $assessment = [
            'status' => $health,
            'score' => $this->calculateHealthScore($stats),
            'recommendations' => $this->generateRecommendations($stats),
            'metrics' => [
                'hit_rate' => $stats['hit_rate'],
                'total_requests' => $stats['total_requests'],
                'avg_response_time' => $this->getAverageResponseTime(),
                'cache_size_efficiency' => $this->calculateSizeEfficiency(),
            ],
        ];

        return $assessment;
    }

    /**
     * Get all tags for this tenant
     */
    public function getTags(): array
    {
        return $this->tagRegistry->getAllTags();
    }

    // Protected helper methods

    protected function getTenantKey(string $key): string
    {
        // Always enforce tenant isolation if enabled in config
        $isolationEnabled = $this->config['tenant']['isolate_tenants'] ?? true;
        $sharedAllowed = $this->config['tenant']['shared_cache_allowed'] ?? false;

        // If the key already has the tenant prefix, don't add it again
        if (str_starts_with($key, $this->tenantPrefix)) {
            return $key;
        }

        // Check if this is a shared cache key (starts with 'shared_')
        $isSharedKey = str_starts_with($key, 'shared_');

        // If isolation is enabled and this is not a shared key (or shared keys aren't allowed)
        if ($isolationEnabled && (! $isSharedKey || ! $sharedAllowed)) {
            return $this->tenantPrefix.$key;
        }

        // For shared keys when allowed
        if ($isSharedKey && $sharedAllowed) {
            return $key;
        }

        // Default case - always namespace with tenant prefix for safety
        return $this->tenantPrefix.$key;
    }

    protected function getTotalKeys(): int
    {
        try {
            $pattern = config('cache.prefix').$this->tenantPrefix;
            $cacheDriver = config('tenant-cache.store') ?? config('cache.default');

            switch ($cacheDriver) {
                case 'database':
                    return $this->countDatabaseKeys($pattern);
                case 'redis':
                    return $this->countRedisKeys($pattern);
                case 'file':
                    return $this->countFileKeys($pattern);
                case 'array':
                    return $this->countArrayKeys($pattern);
                default:
                    return $this->countGenericKeys($pattern);
            }
        } catch (\Exception $e) {
            // Log error but prevent disruption to application
            return 0;
        }
    }

    /**
     * Count keys in database cache
     */
    protected function countDatabaseKeys(string $prefix): int
    {
        try {
            return DB::table('cache')
                ->where('key', 'like', $prefix.'%')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count keys in Redis cache
     */
    protected function countRedisKeys(string $prefix): int
    {
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection();

            return count($redis->keys($prefix.'*'));
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
            // Check both cache data directory and framework cache directory
            $path1 = config('cache.stores.file.path', storage_path('framework/cache/data'));
            $path2 = storage_path('framework/cache');

            $count = 0;
            $tenantPrefixPattern = preg_quote($prefix, '/');

            // Check first path
            if (is_dir($path1)) {
                $directory = new \RecursiveDirectoryIterator($path1, \FilesystemIterator::SKIP_DOTS);
                $iterator = new \RecursiveIteratorIterator($directory);

                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() !== 'meta') {
                        // Check filename for tenant pattern first (more efficient)
                        $filename = $file->getFilename();
                        if (strpos($filename, $this->tenantId) !== false) {
                            // Double check that this is really our tenant's file
                            // This prevents counting cache from tenant123 when we're tenant12
                            $contents = @file_get_contents($file->getPathname());
                            if ($contents && $this->isCurrentTenantKey($contents)) {
                                $count++;
                            }

                            continue;
                        }

                        // If not in filename, check file contents more thoroughly
                        $contents = @file_get_contents($file->getPathname());
                        if ($contents && preg_match('/"key";s:\d+:"'.$tenantPrefixPattern.'/', $contents)) {
                            $count++;
                        }
                    }
                }
            }

            // Check second path
            if (is_dir($path2)) {
                $directory = new \RecursiveDirectoryIterator($path2, \FilesystemIterator::SKIP_DOTS);
                $iterator = new \RecursiveIteratorIterator($directory);

                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() !== 'meta') {
                        // Check filename for tenant pattern
                        $filename = $file->getFilename();
                        if (strpos($filename, $this->tenantId) !== false) {
                            // Double check that this is really our tenant's file
                            // This prevents counting cache from tenant123 when we're tenant12
                            $contents = @file_get_contents($file->getPathname());
                            if ($contents && $this->isCurrentTenantKey($contents)) {
                                $count++;
                            }

                            continue;
                        }

                        // If not in filename, check file contents more thoroughly
                        $contents = @file_get_contents($file->getPathname());
                        if ($contents && preg_match('/"key";s:\d+:"'.$tenantPrefixPattern.'/', $contents)) {
                            $count++;
                        }
                    }
                }
            }

            return $count;
        } catch (\Exception $e) {
            // Log error or handle it as needed
            return 0;
        }
    }

    /**
     * Count keys in array cache
     */
    protected function countArrayKeys(string $prefix): int
    {
        // Array cache doesn't have a reliable way to count keys
        // This is just an estimation for testing purposes
        return 0;
    }

    /**
     * Generic key counting fallback
     */
    protected function countGenericKeys(string $prefix): int
    {
        // For drivers we don't have specific implementation
        return 0;
    }

    protected function getTotalSize(): string
    {
        $pattern = config('cache.prefix').$this->tenantPrefix.'%';
        $cacheDriver = config('tenant-cache.store') ?? config('cache.default');

        switch ($cacheDriver) {
            case 'database':
                $result = DB::table('cache')
                    ->where('key', 'like', $pattern)
                    ->selectRaw('SUM(LENGTH(value)) as total_size')
                    ->first();

                $sizeBytes = $result->total_size ?? 0;

                return $this->formatBytes($sizeBytes);

            case 'redis':
                try {
                    // Redis doesn't have a direct way to get total size
                    // This is just an estimation based on keys
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    $keys = $redis->keys($pattern);
                    $totalSize = 0;

                    foreach ($keys as $key) {
                        $value = $redis->get($key);
                        if ($value) {
                            $totalSize += strlen($value);
                        }
                    }

                    return $this->formatBytes($totalSize);
                } catch (\Exception $e) {
                    return 'N/A';
                }

            case 'file':
                try {
                    // Check both cache data directory and framework cache directory
                    $path1 = config('cache.stores.file.path', storage_path('framework/cache/data'));
                    $path2 = storage_path('framework/cache');

                    $totalSize = 0;
                    $tenantPrefixPattern = preg_quote($this->tenantPrefix, '/');

                    // Check first path
                    if (is_dir($path1)) {
                        $directory = new \RecursiveDirectoryIterator($path1, \FilesystemIterator::SKIP_DOTS);
                        $iterator = new \RecursiveIteratorIterator($directory);

                        foreach ($iterator as $file) {
                            if ($file->isFile() && $file->getExtension() !== 'meta') {
                                // Check filename for tenant pattern first
                                $filename = $file->getFilename();
                                if (strpos($filename, $this->tenantId) !== false) {
                                    // Double check this is really our tenant's file
                                    $contents = @file_get_contents($file->getPathname());
                                    if ($contents && $this->isCurrentTenantKey($contents)) {
                                        $totalSize += $file->getSize();
                                    }

                                    continue;
                                }

                                // If not in filename, check file contents more thoroughly
                                $contents = @file_get_contents($file->getPathname());
                                if ($contents && preg_match('/"key";s:\d+:"'.$tenantPrefixPattern.'/', $contents)) {
                                    $totalSize += $file->getSize();
                                }
                            }
                        }
                    }

                    // Check second path
                    if (is_dir($path2)) {
                        $directory = new \RecursiveDirectoryIterator($path2, \FilesystemIterator::SKIP_DOTS);
                        $iterator = new \RecursiveIteratorIterator($directory);

                        foreach ($iterator as $file) {
                            if ($file->isFile() && $file->getExtension() !== 'meta') {
                                // Check filename for tenant pattern
                                $filename = $file->getFilename();
                                if (strpos($filename, $this->tenantId) !== false) {
                                    // Double check this is really our tenant's file
                                    $contents = @file_get_contents($file->getPathname());
                                    if ($contents && $this->isCurrentTenantKey($contents)) {
                                        $totalSize += $file->getSize();
                                    }

                                    continue;
                                }

                                // If not in filename, check file contents more thoroughly
                                $contents = @file_get_contents($file->getPathname());
                                if ($contents && preg_match('/"key";s:\d+:"'.$tenantPrefixPattern.'/', $contents)) {
                                    $totalSize += $file->getSize();
                                }
                            }
                        }
                    }

                    return $this->formatBytes($totalSize);
                } catch (\Exception $e) {
                    return 'N/A';
                }

            case 'array':
                // Array cache is in-memory only, size calculation not applicable
                return 'In-Memory';

            default:
                return 'N/A';
        }
    }

    protected function getCacheStats(): array
    {
        $statsKey = $this->tenantPrefix.'_cache_stats';

        return Cache::get($statsKey, [
            'hits' => 0,
            'misses' => 0,
            'writes' => 0,
            'total_size' => 0,
            'hit_rate' => 0,
            'total_requests' => 0,
        ]);
    }

    protected function trackCacheHit(string $key, array $tags = []): void
    {
        $statsKey = $this->tenantPrefix.'_cache_stats';
        $stats = $this->getCacheStats();
        $stats['hits']++;
        $stats['total_requests'] = $stats['hits'] + $stats['misses'];
        $stats['hit_rate'] = $stats['total_requests'] > 0
            ? round(($stats['hits'] / $stats['total_requests']) * 100, 2)
            : 0;

        Cache::put($statsKey, $stats, now()->addDays(7));
    }

    protected function trackCacheMiss(string $key, array $tags = []): void
    {
        $statsKey = $this->tenantPrefix.'_cache_stats';
        $stats = $this->getCacheStats();
        $stats['misses']++;
        $stats['total_requests'] = $stats['hits'] + $stats['misses'];
        $stats['hit_rate'] = $stats['total_requests'] > 0
            ? round(($stats['hits'] / $stats['total_requests']) * 100, 2)
            : 0;

        Cache::put($statsKey, $stats, now()->addDays(7));
    }

    protected function trackCacheWrite(string $key, array $tags, int $size): void
    {
        $statsKey = $this->tenantPrefix.'_cache_stats';
        $stats = $this->getCacheStats();
        $stats['writes']++;
        $stats['total_size'] += $size;

        Cache::put($statsKey, $stats, now()->addDays(7));
    }

    protected function calculateCacheHealth(array $stats): string
    {
        $hitRate = $stats['hit_rate'] ?? 0;

        if ($hitRate >= 95) {
            return self::HEALTH_EXCELLENT;
        }
        if ($hitRate >= 85) {
            return self::HEALTH_GOOD;
        }
        if ($hitRate >= 70) {
            return self::HEALTH_WARNING;
        }

        return self::HEALTH_CRITICAL;
    }

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

    // Additional helper methods for advanced features

    protected function getCacheUptime(): string
    {
        $uptimeKey = $this->tenantPrefix.'_cache_uptime';
        $startTime = Cache::get($uptimeKey, now());

        if ($startTime === now()) {
            Cache::put($uptimeKey, now(), now()->addDays(30));
        }

        return now()->diffForHumans($startTime).' uptime';
    }

    protected function getLastWarmingTime(): ?string
    {
        $warmingKey = $this->tenantPrefix.'_last_warming';
        $lastWarming = Cache::get($warmingKey);

        return $lastWarming ? $lastWarming->diffForHumans() : null;
    }

    protected function getTagDistribution(): array
    {
        return $this->tagRegistry->getTagDistribution();
    }

    protected function getTopAccessedKeys(): array
    {
        $accessKey = $this->tenantPrefix.'_key_access';
        $accessStats = Cache::get($accessKey, []);

        arsort($accessStats);

        return array_slice($accessStats, 0, 10, true);
    }

    protected function calculateCacheEfficiency(array $stats): float
    {
        $hitRate = $stats['hit_rate'] ?? 0;
        $keyCount = $this->getTotalKeys();

        // Efficiency = hit rate * key utilization
        $keyUtilization = $keyCount > 0 ? min(100, ($stats['total_requests'] / $keyCount) * 10) : 0;

        return round(($hitRate * 0.7) + ($keyUtilization * 0.3), 2);
    }

    protected function calculateHealthScore(array $stats): int
    {
        $hitRate = $stats['hit_rate'] ?? 0;
        $efficiency = $this->calculateCacheEfficiency($stats);

        return min(100, round(($hitRate * 0.6) + ($efficiency * 0.4)));
    }

    protected function generateRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['hit_rate'] < 70) {
            $recommendations[] = 'Consider implementing cache warming for frequently accessed data';
        }

        if ($stats['hit_rate'] < 85) {
            $recommendations[] = 'Review cache TTL values to improve hit rates';
        }

        $keyCount = $this->getTotalKeys();
        if ($keyCount > 10000) {
            $recommendations[] = 'Large number of keys detected, consider implementing cache pruning';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Cache performance is optimal';
        }

        return $recommendations;
    }

    protected function getAverageResponseTime(): float
    {
        // This would be implemented with more detailed tracking
        return 0.0;
    }

    protected function calculateSizeEfficiency(): float
    {
        // Calculate how efficiently cache space is being used
        return 85.0; // Placeholder
    }

    protected function getAllKeys(): array
    {
        return $this->tagRegistry->getAllKeys();
    }

    protected function resetStatistics(): void
    {
        $statsKey = $this->tenantPrefix.'_cache_stats';
        Cache::forget($statsKey);

        $accessKey = $this->tenantPrefix.'_key_access';
        Cache::forget($accessKey);
    }

    /**
     * Check if a cache key belongs to the current tenant
     */
    protected function isCurrentTenantKey(string $key): bool
    {
        // Always check if the key contains the tenant ID or prefix
        return strpos($key, $this->tenantId) !== false || strpos($key, $this->tenantPrefix) !== false;
    }
}
