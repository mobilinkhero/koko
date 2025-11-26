<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-aware cache service that enforces tenant isolation
 */
class TenantCacheStore
{
    private string $tenantId;

    private string $prefix;

    public function __construct(?string $tenantId = null)
    {
        $this->tenantId = $tenantId ?: $this->getCurrentTenantId();
        $this->prefix = "tenant_{$this->tenantId}_";
    }

    /**
     * Get a value from cache with tenant isolation
     */
    public function get(string $key, $default = null)
    {
        $tenantKey = $this->getTenantKey($key);

        return Cache::get($tenantKey, $default);
    }

    /**
     * Store a value in cache with tenant isolation
     */
    public function put(string $key, $value, $ttl = null): bool
    {
        $tenantKey = $this->getTenantKey($key);

        return Cache::put($tenantKey, $value, $ttl);
    }

    /**
     * Check if key exists in tenant cache
     */
    public function has(string $key): bool
    {
        $tenantKey = $this->getTenantKey($key);

        return Cache::has($tenantKey);
    }

    /**
     * Remove a key from tenant cache
     */
    public function forget(string $key): bool
    {
        $tenantKey = $this->getTenantKey($key);

        return Cache::forget($tenantKey);
    }

    /**
     * Clear all cache for current tenant
     */
    public function flush(): bool
    {
        // This would need driver-specific implementation
        // For now, we'll implement for database driver
        if (config('cache.default') === 'database') {
            $fullPrefix = config('cache.prefix').$this->prefix;

            return DB::table('cache')
                ->where('key', 'like', $fullPrefix.'%')
                ->delete() > 0;
        }

        return false;
    }

    /**
     * Get tenant-specific cache key
     */
    private function getTenantKey(string $key): string
    {
        // If key already has tenant prefix, return as-is
        if (str_starts_with($key, $this->prefix)) {
            return $key;
        }

        return $this->prefix.$key;
    }

    /**
     * Get current tenant ID from context
     */
    private function getCurrentTenantId(): string
    {
        // Try to get tenant ID from various sources
        if (function_exists('tenant_id') && tenant_id()) {
            return (string) tenant_id();
        }

        // Fallback to session or request
        if (session()->has('tenant_id')) {
            return (string) session('tenant_id');
        }

        // Default fallback
        return 'default';
    }

    /**
     * Get tenant ID for this cache instance
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Validate that a key belongs to current tenant
     */
    public function validateTenantAccess(string $key): bool
    {
        $fullPrefix = config('cache.prefix').$this->prefix;

        return str_starts_with($key, $fullPrefix);
    }

    /**
     * Get all keys for current tenant
     */
    public function getAllKeys(): array
    {
        if (config('cache.default') === 'database') {
            $fullPrefix = config('cache.prefix').$this->prefix;

            return DB::table('cache')
                ->where('key', 'like', $fullPrefix.'%')
                ->pluck('key')
                ->map(function ($key) use ($fullPrefix) {
                    // Remove the full prefix to get the original key
                    return str_replace($fullPrefix, '', $key);
                })
                ->toArray();
        }

        return [];
    }

    /**
     * Get cache statistics for current tenant
     */
    public function getStats(): array
    {
        $tenantCacheService = app(\Modules\CacheManager\Services\TenantCacheService::class);
        $statistics = $tenantCacheService->getTenantCacheStatistics($this->tenantId);

        return [
            'tenant_id' => $this->tenantId,
            'total_keys' => $statistics['total_keys'] ?? 0,
            'total_size' => $statistics['total_size'] ?? '0 B',
            'hit_rate' => $statistics['hit_rate'] ?? 0,
        ];
    }
}
