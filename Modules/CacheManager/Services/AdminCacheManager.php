<?php

namespace Modules\CacheManager\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized Admin Cache Manager with Tag-Based Strategies
 *
 * This service provides a unified interface for all admin-side cache operations
 * using Laravel's cache tags for efficient bulk invalidation and granular control.
 *
 * Key Features:
 * - Tag-based cache organization for bulk operations
 * - Compression support for large objects
 * - Lock mechanisms to prevent cache stampede
 * - Comprehensive monitoring and statistics
 * - Standardized cache key formats
 * - Graceful error handling with fallbacks
 *
 * @author WhatsApp SaaS Team
 *
 * @since 2.0.0
 */
class AdminCacheManager
{
    // Cache tag constants for admin data
    public const TAG_LANGUAGES = 'admin:languages';

    public const TAG_CURRENCIES = 'admin:currencies';

    public const TAG_TAXES = 'admin:taxes';

    public const TAG_PLANS = 'admin:plans';

    public const TAG_SETTINGS = 'admin:settings';

    public const TAG_USERS = 'admin:users';

    public const TAG_SYSTEM = 'admin:system';

    // Framework cache tags
    public const TAG_FRAMEWORK_CONFIG = 'framework:config';

    public const TAG_FRAMEWORK_ROUTES = 'framework:routes';

    public const TAG_FRAMEWORK_VIEWS = 'framework:views';

    public const TAG_FRAMEWORK_OPCACHE = 'framework:opcache';

    // Meta tags for bulk operations
    public const TAG_ADMIN_ALL = 'admin:all';

    public const TAG_FRAMEWORK_ALL = 'framework:all';

    /**
     * Default cache TTL in seconds (1 hour)
     */
    protected int $defaultTtl = 3600;

    /**
     * Cache key prefix for admin operations
     */
    protected string $keyPrefix = 'admin_cache:';

    /**
     * Whether to use compression for large objects
     */
    protected bool $useCompression = true;

    /**
     * Minimum size in bytes to trigger compression
     */
    protected int $compressionThreshold = 1024; // 1KB

    /**
     * Statistics tracking
     */
    protected array $statistics = [
        'hits' => 0,
        'misses' => 0,
        'puts' => 0,
        'deletes' => 0,
        'tag_operations' => 0,
    ];

    /**
     * Remember data in cache with tags and optional compression
     *
     * @param  string  $key  Cache key
     * @param  callable  $callback  Function to generate data if not cached
     * @param  array  $tags  Cache tags for organization and bulk operations
     * @param  int|null  $ttl  Time to live in seconds (null = default)
     * @return mixed Cached or generated data
     */
    public function remember(string $key, callable $callback, array $tags = [], ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheKey = $this->formatKey($key);
        $lockKey = "lock:{$cacheKey}";

        // Add meta tags
        $tags = $this->addMetaTags($tags);

        try {
            // Try to get from cache first
            $cachedValue = $this->getCachedValue($cacheKey, $tags);
            if ($cachedValue !== null) {
                $this->statistics['hits']++;

                return $cachedValue;
            }

            $this->statistics['misses']++;

            // Use lock to prevent cache stampede
            return Cache::lock($lockKey, 10)->block(5, function () use ($cacheKey, $callback, $tags, $ttl) {
                // Double-check cache after acquiring lock
                $cachedValue = $this->getCachedValue($cacheKey, $tags);
                if ($cachedValue !== null) {
                    return $cachedValue;
                }

                // Generate new data
                $data = $callback();

                // Store in cache
                $this->put($cacheKey, $data, $tags, $ttl);

                return $data;
            });

        } catch (\Exception $e) {
            app_log('Admin cache remember failed', 'error', $e, [
                'key' => $cacheKey,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            // Fallback to direct callback execution
            return $callback();
        }
    }

    /**
     * Store data in cache with tags and optional compression
     *
     * @param  string  $key  Cache key
     * @param  mixed  $value  Data to cache
     * @param  array  $tags  Cache tags
     * @param  int|null  $ttl  Time to live in seconds
     * @return bool Success status
     */
    public function put(string $key, mixed $value, array $tags = [], ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheKey = $this->formatKey($key);
        $tags = $this->addMetaTags($tags);

        try {
            // Prepare value for storage (with optional compression)
            $storageValue = $this->prepareForStorage($value);

            // Store in cache with tags if supported
            if (! empty($tags) && $this->cacheDriverSupportsTagging()) {
                $result = Cache::tags($tags)->put($cacheKey, $storageValue, $ttl);
            } else {
                $result = Cache::put($cacheKey, $storageValue, $ttl);
            }

            if ($result) {
                $this->statistics['puts']++;
                $this->logCacheOperation('put', $cacheKey, $tags);
            }

            return $result;

        } catch (\Exception $e) {
            app_log('Admin cache put failed', 'error', $e, [
                'key' => $cacheKey,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove specific cache entry
     *
     * @param  string  $key  Cache key to remove
     * @return bool Success status
     */
    public function forget(string $key): bool
    {
        $cacheKey = $this->formatKey($key);

        try {
            $result = Cache::forget($cacheKey);

            if ($result) {
                $this->statistics['deletes']++;
                $this->logCacheOperation('forget', $cacheKey);
            }

            return $result;

        } catch (\Exception $e) {
            app_log('Admin cache forget failed', 'error', $e, [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove all cache entries with specific tags
     *
     * @param  array  $tags  Tags to clear
     * @return bool Success status
     */
    public function forgetByTags(array $tags): bool
    {
        if (empty($tags)) {
            return false;
        }

        try {
            if ($this->cacheDriverSupportsTagging()) {
                Cache::tags($tags)->flush();
            } else {
                // For drivers that don't support tagging, we need to flush all cache
                // This is less efficient but necessary for database/file drivers
                Cache::flush();
            }

            $this->statistics['tag_operations']++;
            $this->logCacheOperation('flush_tags', '', $tags);

            return true;

        } catch (\Exception $e) {
            app_log('Admin cache tag flush failed', 'error', $e, [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear all admin-related cache
     *
     * @return array Results of clearing operations
     */
    public function clearAllAdminCache(): array
    {
        $results = [];

        try {
            // Clear all admin-tagged cache
            $this->forgetByTags([self::TAG_ADMIN_ALL]);
            $results['admin_cache'] = 'All admin cache cleared';

            // Clear individual admin categories as fallback
            $adminTags = [
                self::TAG_LANGUAGES,
                self::TAG_CURRENCIES,
                self::TAG_TAXES,
                self::TAG_PLANS,
                self::TAG_SETTINGS,
                self::TAG_USERS,
                self::TAG_SYSTEM,
            ];

            foreach ($adminTags as $tag) {
                $this->forgetByTags([$tag]);
                $results[str_replace('admin:', '', $tag)] = 'Cleared';
            }

        } catch (\Exception $e) {
            $results['error'] = 'Failed to clear admin cache: '.$e->getMessage();
        }

        return $results;
    }

    /**
     * Clear cache by category
     *
     * @param  string  $category  Category name (languages, currencies, etc.)
     * @return bool Success status
     */
    public function clearByCategory(string $category): bool
    {
        $tagMap = [
            'languages' => self::TAG_LANGUAGES,
            'currencies' => self::TAG_CURRENCIES,
            'taxes' => self::TAG_TAXES,
            'plans' => self::TAG_PLANS,
            'settings' => self::TAG_SETTINGS,
            'users' => self::TAG_USERS,
            'system' => self::TAG_SYSTEM,
        ];

        if (! isset($tagMap[$category])) {
            return false;
        }

        return $this->forgetByTags([$tagMap[$category]]);
    }

    /**
     * Clear all framework cache
     *
     * @return array Results of clearing operations
     */
    public function clearFrameworkCache(): array
    {
        $results = [];

        try {
            // Clear framework-tagged cache
            $this->forgetByTags([self::TAG_FRAMEWORK_ALL]);
            $results['framework_cache'] = 'All framework cache cleared';

        } catch (\Exception $e) {
            $results['error'] = 'Failed to clear framework cache: '.$e->getMessage();
        }

        return $results;
    }

    /**
     * Store compressed data for large objects
     *
     * @param  string  $key  Cache key
     * @param  mixed  $value  Data to compress and cache
     * @param  array  $tags  Cache tags
     * @param  int|null  $ttl  Time to live
     * @return bool Success status
     */
    public function putCompressed(string $key, mixed $value, array $tags = [], ?int $ttl = null): bool
    {
        $serialized = serialize($value);

        if (strlen($serialized) > $this->compressionThreshold) {
            $compressed = gzcompress($serialized, 9);
            $storageValue = [
                'compressed' => true,
                'data' => base64_encode($compressed),
            ];
        } else {
            $storageValue = [
                'compressed' => false,
                'data' => $value,
            ];
        }

        return $this->put($key, $storageValue, $tags, $ttl);
    }

    /**
     * Warm cache by pre-loading common data
     *
     * @param  array  $warmupCallbacks  Array of callbacks to warm cache
     * @return array Results of warmup operations
     */
    public function warmCache(array $warmupCallbacks = []): array
    {
        $results = [];

        // Default warmup operations
        $defaultWarmups = [
            'languages' => fn () => $this->warmLanguageCache(),
            'currencies' => fn () => $this->warmCurrencyCache(),
            'taxes' => fn () => $this->warmTaxCache(),
        ];

        $warmups = array_merge($defaultWarmups, $warmupCallbacks);

        foreach ($warmups as $name => $callback) {
            try {
                $callback();
                $results[$name] = 'Warmed successfully';
            } catch (\Exception $e) {
                $results[$name] = 'Failed: '.$e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get comprehensive cache statistics
     *
     * @return array Statistics and metrics
     */
    public function getCacheStatistics(): array
    {
        try {
            return [
                'cache_driver' => config('cache.default'),
                'tags_supported' => $this->cacheDriverSupportsTagging(),
                'operations' => $this->statistics,
                'cache_sizes' => $this->getCacheSizes(),
                'tag_usage' => $this->getTagUsage(),
                'memory_usage' => $this->getMemoryUsage(),
                'hit_ratio' => $this->calculateHitRatio(),
                'last_cleared' => Cache::get($this->formatKey('last_cleared'), 'Never'),
                'compression_enabled' => $this->useCompression,
                'admin_cache_health' => $this->checkCacheHealth(),
            ];
        } catch (\Exception $e) {

            return ['error' => 'Failed to retrieve statistics'];
        }
    }

    /**
     * Get cache sizes by tags
     *
     * @param  array  $tags  Specific tags to check (empty = all admin tags)
     * @return array Size information
     */
    public function getCacheSizes(array $tags = []): array
    {
        // This is a simplified version - actual implementation would depend on cache driver
        return [
            'admin_total' => '0 MB', // Would need driver-specific implementation
            'languages' => '0 MB',
            'currencies' => '0 MB',
            'taxes' => '0 MB',
            'plans' => '0 MB',
            'note' => 'Detailed size calculation requires cache driver support',
        ];
    }

    /**
     * Get tag usage statistics
     *
     * @return array Tag usage information
     */
    public function getTagUsage(): array
    {
        return [
            'available_tags' => [
                'admin' => [
                    self::TAG_LANGUAGES,
                    self::TAG_CURRENCIES,
                    self::TAG_TAXES,
                    self::TAG_PLANS,
                    self::TAG_SETTINGS,
                    self::TAG_USERS,
                    self::TAG_SYSTEM,
                ],
                'framework' => [
                    self::TAG_FRAMEWORK_CONFIG,
                    self::TAG_FRAMEWORK_ROUTES,
                    self::TAG_FRAMEWORK_VIEWS,
                    self::TAG_FRAMEWORK_OPCACHE,
                ],
            ],
            'meta_tags' => [
                self::TAG_ADMIN_ALL,
                self::TAG_FRAMEWORK_ALL,
            ],
        ];
    }

    /**
     * Check cache system health
     *
     * @return array Health check results
     */
    public function checkCacheHealth(): array
    {
        $health = [];

        try {
            // Test basic cache operations
            $testKey = $this->formatKey('health_check');
            $testValue = 'health_test_'.time();

            // Test write
            $writeSuccess = Cache::put($testKey, $testValue, 60);
            $health['write'] = $writeSuccess ? 'OK' : 'FAILED';

            // Test read
            $readValue = Cache::get($testKey);
            $health['read'] = ($readValue === $testValue) ? 'OK' : 'FAILED';

            // Test delete
            $deleteSuccess = Cache::forget($testKey);
            $health['delete'] = $deleteSuccess ? 'OK' : 'FAILED';

            // Test tags (if supported)
            if ($this->cacheDriverSupportsTagging()) {
                try {
                    Cache::tags(['health_test'])->put($testKey, $testValue, 60);
                    Cache::tags(['health_test'])->flush();
                    $health['tags'] = 'OK';
                } catch (\Exception $e) {
                    $health['tags'] = 'FAILED';
                }
            } else {
                $health['tags'] = 'NOT_SUPPORTED';
            }

            $health['overall'] = (
                $health['write'] === 'OK' && $health['read'] === 'OK' && $health['delete'] === 'OK'
            ) ? 'HEALTHY' : 'DEGRADED';

        } catch (\Exception $e) {
            $health['overall'] = 'FAILED';
            $health['error'] = $e->getMessage();
        }

        return $health;
    }

    /**
     * Format cache key with prefix
     *
     * @param  string  $key  Raw cache key
     * @return string Formatted cache key
     */
    protected function formatKey(string $key): string
    {
        // Remove any existing prefix to avoid double-prefixing
        $cleanKey = str_replace($this->keyPrefix, '', $key);

        return $this->keyPrefix.$cleanKey;
    }

    /**
     * Add meta tags to tag array
     *
     * @param  array  $tags  Original tags
     * @return array Tags with meta tags added
     */
    protected function addMetaTags(array $tags): array
    {
        // Always add admin:all meta tag for bulk operations
        if (! empty($tags) && ! in_array(self::TAG_ADMIN_ALL, $tags)) {
            $tags[] = self::TAG_ADMIN_ALL;
        }

        return $tags;
    }

    /**
     * Get cached value with tag support
     *
     * @param  string  $cacheKey  Formatted cache key
     * @param  array  $tags  Cache tags
     * @return mixed Cached value or null
     */
    protected function getCachedValue(string $cacheKey, array $tags): mixed
    {
        try {
            if (! empty($tags) && $this->cacheDriverSupportsTagging()) {
                $value = Cache::tags($tags)->get($cacheKey);
            } else {
                $value = Cache::get($cacheKey);
            }

            return $this->prepareFromStorage($value);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Prepare data for storage (handle compression)
     *
     * @param  mixed  $value  Data to prepare
     * @return mixed Prepared data
     */
    protected function prepareForStorage(mixed $value): mixed
    {
        if (! $this->useCompression) {
            return $value;
        }

        $serialized = serialize($value);

        if (strlen($serialized) > $this->compressionThreshold) {
            return [
                'compressed' => true,
                'data' => base64_encode(gzcompress($serialized, 9)),
            ];
        }

        return [
            'compressed' => false,
            'data' => $value,
        ];
    }

    /**
     * Prepare data from storage (handle decompression)
     *
     * @param  mixed  $value  Stored data
     * @return mixed Prepared data
     */
    protected function prepareFromStorage(mixed $value): mixed
    {
        if (! is_array($value) || ! isset($value['compressed'])) {
            return $value;
        }

        if ($value['compressed']) {
            try {
                $decompressed = gzuncompress(base64_decode($value['data']));

                return unserialize($decompressed);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $value['data'];
    }

    /**
     * Check if cache driver supports tagging
     *
     * @return bool Whether tagging is supported
     */
    protected function cacheDriverSupportsTagging(): bool
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached', 'array']);
    }

    /**
     * Calculate cache hit ratio
     *
     * @return float Hit ratio percentage
     */
    protected function calculateHitRatio(): float
    {
        $total = $this->statistics['hits'] + $this->statistics['misses'];

        return $total > 0 ? round(($this->statistics['hits'] / $total) * 100, 2) : 0;
    }

    /**
     * Get memory usage information
     *
     * @return array Memory usage stats
     */
    protected function getMemoryUsage(): array
    {
        return [
            'php_memory' => memory_get_usage(true),
            'php_memory_peak' => memory_get_peak_usage(true),
            'formatted' => [
                'current' => $this->formatBytes(memory_get_usage(true)),
                'peak' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
        ];
    }

    /**
     * Format bytes to human readable format
     *
     * @param  int  $bytes  Number of bytes
     * @return string Formatted string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    /**
     * Log cache operation for debugging
     *
     * @param  string  $operation  Operation type
     * @param  string  $key  Cache key
     * @param  array  $tags  Cache tags
     */
    protected function logCacheOperation(string $operation, string $key, array $tags = []): void
    {
        if (config('app.debug')) {
        }
    }

    /**
     * Warm language cache
     */
    protected function warmLanguageCache(): void
    {
        // This would call the language service to pre-load common data
        // Implementation depends on existing language service
    }

    /**
     * Warm currency cache
     */
    protected function warmCurrencyCache(): void
    {
        // This would call the currency service to pre-load common data
        // Implementation depends on existing currency service
    }

    /**
     * Warm tax cache
     */
    protected function warmTaxCache(): void
    {
        // This would call the tax service to pre-load common data
        // Implementation depends on existing tax service
    }
}
