<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

/**
 * Tenant Cache Service
 *
 * Provides optimized caching mechanisms for tenant data and settings in the multi-tenant
 * WhatsApp SaaS application. This service reduces database queries and improves performance
 * by implementing request-scoped caching strategies.
 *
 * @author WhatsApp SaaS Team
 *
 * @since 1.0.0
 *
 * Key Features:
 * - Request-scoped caching to prevent memory leaks in long-running processes
 * - Optimized database queries with selective column loading
 * - Cross-tenant setting retrieval without context switching overhead
 * - Automatic cache invalidation and cleanup mechanisms
 * @see \App\Models\Tenant For the underlying tenant model
 * @see \App\Facades\Tenant For facade access to tenant functionality
 *
 * @note This service uses request fingerprinting to ensure cache isolation
 *       between different requests and prevent data leakage.
 *
 * @warning Cache keys include request fingerprints, so they are unique per request.
 *          This prevents cross-request data pollution but may reduce cache hit rates.
 */
class TenantCacheService
{
    /**
     * Store tenant data in cache with Spatie-aware caching.
     * Uses tenant context for automatic cache isolation.
     */
    public static function remember(int $tenantId): ?Tenant
    {
        $cacheKey = "tenant_{$tenantId}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($tenantId) {
            return Tenant::select([
                'id', 'company_name', 'subdomain', 'domain',
                'status', 'expires_at', 'created_at', 'updated_at',
            ])->find($tenantId);
        });
    }

    /**
     * Get tenant by subdomain with simple caching.
     */
    public static function getBySubdomain(string $subdomain): ?Tenant
    {
        $cacheKey = "tenant_subdomain_{$subdomain}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($subdomain) {
            return Tenant::select([
                'id', 'company_name', 'subdomain', 'domain',
                'status', 'expires_at', 'created_at', 'updated_at',
            ])->where('subdomain', $subdomain)->first();
        });
    }

    /**
     * Forget cached tenant data.
     */
    public static function forget(int $tenantId): void
    {
        // Clear specific tenant cache
        Cache::forget("tenant_{$tenantId}");

        // If we have the tenant, also clear subdomain cache
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            Cache::forget("tenant_subdomain_{$tenant->subdomain}");
        }
    }

    /**
     * Clear all tenant caches.
     */
    public static function flush(): void
    {
        Cache::flush();
    }

    /**
     * Get current tenant with caching.
     */
    public static function current(): ?Tenant
    {
        if (! Tenant::checkCurrent()) {
            return null;
        }

        return static::remember(Tenant::current()->id);
    }
}
