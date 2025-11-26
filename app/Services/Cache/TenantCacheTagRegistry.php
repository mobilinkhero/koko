<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tenant Cache Tag Registry
 *
 * Manages cache tags for tenant-isolated cache keys.
 * Provides tag-based cache invalidation even for drivers that don't natively support tags.
 */
class TenantCacheTagRegistry
{
    protected string $tenantId;

    protected string $registryPrefix;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $configPrefix = config('tenant-cache.prefix', 'tenant');
        $this->registryPrefix = "{$configPrefix}_{$tenantId}_tag_registry_";
    }

    /**
     * Register tags for a cache key
     */
    public function registerKeyTags(string $key, array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        // Store key-to-tags mapping
        $keyTagsKey = $this->registryPrefix.'key_tags_'.md5($key);
        Cache::put($keyTagsKey, $tags, now()->addDays(30));

        // Store tag-to-keys mapping for each tag
        foreach ($tags as $tag) {
            $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
            $existingKeys = Cache::get($tagKeysKey, []);

            if (! in_array($key, $existingKeys)) {
                $existingKeys[] = $key;
                Cache::put($tagKeysKey, $existingKeys, now()->addDays(30));
            }
        }

        // Update tag usage statistics
        $this->updateTagStats($tags);
    }

    /**
     * Unregister a cache key from all tags
     */
    public function unregisterKey(string $key): void
    {
        // Get tags for this key
        $keyTagsKey = $this->registryPrefix.'key_tags_'.md5($key);
        $tags = Cache::get($keyTagsKey, []);

        // Remove key from each tag's key list
        foreach ($tags as $tag) {
            $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
            $existingKeys = Cache::get($tagKeysKey, []);

            $updatedKeys = array_filter($existingKeys, fn ($k) => $k !== $key);

            if (empty($updatedKeys)) {
                Cache::forget($tagKeysKey);
            } else {
                Cache::put($tagKeysKey, $updatedKeys, now()->addDays(30));
            }
        }

        // Remove key-to-tags mapping
        Cache::forget($keyTagsKey);
    }

    /**
     * Get all cache keys associated with given tags
     */
    public function getKeysByTags(array $tags): array
    {
        $allKeys = [];

        foreach ($tags as $tag) {
            $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
            $keys = Cache::get($tagKeysKey, []);
            $allKeys = array_merge($allKeys, $keys);
        }

        return array_unique($allKeys);
    }

    /**
     * Get tags for a specific cache key
     */
    public function getTagsForKey(string $key): array
    {
        $keyTagsKey = $this->registryPrefix.'key_tags_'.md5($key);

        return Cache::get($keyTagsKey, []);
    }

    /**
     * Get all tags used by this tenant
     */
    public function getAllTags(): array
    {
        $pattern = $this->registryPrefix.'tag_keys_*';
        $tags = [];

        switch (config('cache.default')) {
            case 'database':
                $results = DB::table('cache')
                    ->where('key', 'like', config('cache.prefix').$pattern)
                    ->pluck('key');

                foreach ($results as $key) {
                    $tagHash = str_replace(config('cache.prefix').$this->registryPrefix.'tag_keys_', '', $key);
                    $tags[] = $this->getTagFromHash($tagHash);
                }
                break;

            case 'redis':
                try {
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    $keys = $redis->keys(config('cache.prefix').$pattern);

                    foreach ($keys as $key) {
                        $tagHash = str_replace(config('cache.prefix').$this->registryPrefix.'tag_keys_', '', $key);
                        $tags[] = $this->getTagFromHash($tagHash);
                    }
                } catch (\Exception $e) {
                    // Fallback to maintaining tags list for Redis issues
                    $allTagsKey = $this->registryPrefix.'all_tags';
                    $tags = Cache::get($allTagsKey, []);
                }
                break;

            default:
                // For file and other drivers, we'll maintain a separate tags list
                $allTagsKey = $this->registryPrefix.'all_tags';
                $tags = Cache::get($allTagsKey, []);
                break;
        }

        return array_filter(array_unique($tags));
    }

    /**
     * Get tag distribution statistics
     */
    public function getTagDistribution(): array
    {
        $distribution = [];
        $allTags = $this->getAllTags();

        foreach ($allTags as $tag) {
            $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
            $keys = Cache::get($tagKeysKey, []);
            $distribution[$tag] = count($keys);
        }

        // Sort by usage count descending
        arsort($distribution);

        return $distribution;
    }

    /**
     * Get tag usage statistics
     */
    public function getTagStats(): array
    {
        $statsKey = $this->registryPrefix.'tag_stats';

        return Cache::get($statsKey, []);
    }

    /**
     * Clean up orphaned tag registrations
     */
    public function cleanup(): array
    {
        $cleaned = [
            'orphaned_keys' => 0,
            'empty_tags' => 0,
            'invalid_entries' => 0,
        ];

        $allTags = $this->getAllTags();

        foreach ($allTags as $tag) {
            $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
            $keys = Cache::get($tagKeysKey, []);
            $validKeys = [];

            // Check if each key still exists in cache
            foreach ($keys as $key) {
                $tenantKey = "tenant_{$this->tenantId}_{$key}";
                if (Cache::has($tenantKey)) {
                    $validKeys[] = $key;
                } else {
                    $cleaned['orphaned_keys']++;
                }
            }

            // Update or remove tag entry
            if (empty($validKeys)) {
                Cache::forget($tagKeysKey);
                $cleaned['empty_tags']++;
            } else {
                Cache::put($tagKeysKey, $validKeys, now()->addDays(30));
            }
        }

        return $cleaned;
    }

    /**
     * Clear all tag registrations for this tenant
     */
    public function clear(): void
    {
        $pattern = $this->registryPrefix.'*';

        switch (config('cache.default')) {
            case 'database':
                DB::table('cache')
                    ->where('key', 'like', config('cache.prefix').$pattern)
                    ->delete();
                break;

            case 'redis':
                try {
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    $keys = $redis->keys(config('cache.prefix').$pattern);
                    if (! empty($keys)) {
                        $redis->del($keys);
                    }
                } catch (\Exception $e) {
                    // Fallback to individual removal
                    $allTags = $this->getAllTags();
                    foreach ($allTags as $tag) {
                        $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
                        Cache::forget($tagKeysKey);
                    }
                }
                break;

            default:
                // Clear all tag-related cache entries
                $allTags = $this->getAllTags();
                foreach ($allTags as $tag) {
                    $tagKeysKey = $this->registryPrefix.'tag_keys_'.md5($tag);
                    Cache::forget($tagKeysKey);
                }

                // Clear registry metadata
                Cache::forget($this->registryPrefix.'all_tags');
                Cache::forget($this->registryPrefix.'tag_stats');
                break;
        }
    }

    /**
     * Get all cache keys for this tenant (across all tags)
     */
    public function getAllKeys(): array
    {
        $allKeys = [];
        $allTags = $this->getAllTags();

        foreach ($allTags as $tag) {
            $tagKeys = $this->getKeysByTags([$tag]);
            $allKeys = array_merge($allKeys, $tagKeys);
        }

        return array_unique($allKeys);
    }

    /**
     * Update tag usage statistics
     */
    protected function updateTagStats(array $tags): void
    {
        $statsKey = $this->registryPrefix.'tag_stats';
        $stats = Cache::get($statsKey, []);

        foreach ($tags as $tag) {
            $stats[$tag] = ($stats[$tag] ?? 0) + 1;
            $stats[$tag.'_last_used'] = now()->toDateTimeString();
        }

        Cache::put($statsKey, $stats, now()->addDays(30));

        // Maintain a list of all tags for non-key-scanning cache drivers
        $allTagsKey = $this->registryPrefix.'all_tags';
        $allTags = Cache::get($allTagsKey, []);
        $allTags = array_unique(array_merge($allTags, $tags));
        Cache::put($allTagsKey, $allTags, now()->addDays(30));
    }

    /**
     * Reverse lookup tag from hash (for display purposes)
     */
    protected function getTagFromHash(string $hash): string
    {
        // This is a limitation - we can't reverse MD5 hashes
        // In practice, we'd maintain a reverse lookup table
        $reverseKey = $this->registryPrefix.'tag_reverse_'.$hash;

        return Cache::get($reverseKey, "unknown_tag_{$hash}");
    }

    /**
     * Store reverse lookup for tag hash
     */
    protected function storeTagReverse(string $tag): void
    {
        $hash = md5($tag);
        $reverseKey = $this->registryPrefix.'tag_reverse_'.$hash;
        Cache::put($reverseKey, $tag, now()->addDays(30));
    }
}
