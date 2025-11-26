<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * TenantSettingsCache Service
 *
 * Provides high-performance caching layer for tenant-specific configuration
 * settings in multi-tenant WhatsApp SaaS applications. Optimizes database
 * access patterns and reduces query overhead for frequently accessed settings.
 *
 * Key Features:
 * - Group-based settings organization and retrieval
 * - Intelligent caching with TTL management
 * - Bulk settings loading to prevent N+1 queries
 * - Tenant context awareness with explicit override support
 * - Selective cache invalidation by group or tenant
 *
 * Settings Architecture:
 * - Settings are organized by groups (e.g., 'whatsapp', 'notifications', 'billing')
 * - Each group can contain multiple key-value pairs
 * - Cache keys include tenant ID and group for isolation
 * - Default values provide fallback when settings don't exist
 *
 * Performance Benefits:
 * - Single query loads all settings in a group
 * - In-memory caching prevents repeated database access
 * - Explicit tenant ID parameter reduces context lookups
 * - Error handling prevents cascade failures
 *
 * Usage Examples:
 * ```php
 * // Get specific setting with default
 * $apiKey = TenantSettingsCache::get('whatsapp', 'api_key', 'default_key');
 *
 * // Get all settings in a group
 * $whatsappSettings = TenantSettingsCache::get('whatsapp');
 *
 * // Use explicit tenant ID to avoid context lookups
 * $setting = TenantSettingsCache::get('billing', 'currency', 'USD', $tenantId);
 *
 * // Clear cache after settings update
 * TenantSettingsCache::forget($tenantId, 'whatsapp');
 * ```
 *
 * @see \App\Services\TenantSettingsService
 * @see \App\Services\TenantCache
 *
 * @version 1.0.0
 */
class TenantSettingsCache
{
    /**
     * Retrieve tenant settings with intelligent caching
     *
     * Fetches tenant-specific configuration settings with group-based organization
     * and intelligent caching. Supports both individual setting retrieval and
     * bulk group loading to optimize database queries.
     *
     * @param  string  $group  The settings group name (e.g., 'whatsapp', 'billing')
     * @param  string|null  $key  Specific setting key, null to get all group settings
     * @param  mixed  $default  Default value returned if setting doesn't exist
     * @param  int|null  $explicitTenantId  Optional tenant ID to avoid context lookups
     * @return mixed Setting value, settings array, or default value
     *
     * @throws \Exception When database query fails
     *
     * @example
     * ```php
     * // Get specific WhatsApp setting
     * $apiKey = TenantSettingsCache::get('whatsapp', 'api_key', 'default');
     *
     * // Get all notification settings
     * $settings = TenantSettingsCache::get('notifications');
     *
     * // Use explicit tenant ID for performance
     * $currency = TenantSettingsCache::get('billing', 'currency', 'USD', 123);
     * ```
     *
     * @see \App\Services\TenantCache::getCurrentTenantId()
     */
    public static function get(string $group, ?string $key = null, $default = null, ?int $explicitTenantId = null)
    {
        // Use explicit tenant ID if provided, otherwise check if tenant is active
        if ($explicitTenantId === null && ! \Spatie\Multitenancy\Models\Tenant::checkCurrent()) {
            return $default;
        }

        // Use the explicitly provided tenant ID or get it from the context
        // This helps reduce redundant calls to current_tenant()
        $tenantId = $explicitTenantId ?? tenant_id();
        $cacheKey = "tenant_settings_{$tenantId}_{$group}";

        $settings = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tenantId, $group) {
            try {
                // Get all settings for this group in a single query
                return DB::table('tenant_settings')
                    ->where('tenant_id', $tenantId)
                    ->where('group', $group)
                    ->select(['key', 'value'])
                    ->get()
                    ->keyBy('key')
                    ->map(function ($item) {
                        return $item->value;
                    })
                    ->toArray();
            } catch (\Exception $e) {
                app_log("Error fetching tenant settings for tenant $tenantId: ".$e->getMessage(), 'error', $e);

                return [];
            }
        });

        if ($key === null) {
            return $settings;
        }

        return $settings[$key] ?? $default;
    }

    /**
     * Clear cached settings for a tenant
     *
     * Removes cached settings data for a tenant, optionally targeting
     * specific groups or clearing all cached settings. Should be called
     * after settings modifications to ensure cache consistency.
     *
     * @param  int  $tenantId  The tenant identifier
     * @param  string|null  $group  Optional group name to clear specific group cache
     * @return void
     *
     * @example
     * ```php
     * // Clear specific group cache
     * TenantSettingsCache::forget(123, 'whatsapp');
     *
     * // Clear all settings cache for tenant
     * TenantSettingsCache::forget(123);
     *
     * // After settings update
     * $setting->update(['value' => $newValue]);
     * TenantSettingsCache::forget($tenantId, $setting->group);
     * ```
     *
     * @see get()
     */
    public static function forget(int $tenantId, ?string $group = null)
    {
        if ($group) {
            Cache::forget("tenant_settings_{$tenantId}_{$group}");
        } else {
            // Clear all tenant settings cache patterns
            $cacheKeys = Cache::getStore()->many(["tenant_settings_{$tenantId}_*"]);
            foreach ($cacheKeys as $key => $value) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Get tenant language settings with proper cache isolation
     */
    public static function getLanguageSettings(?int $explicitTenantId = null): array
    {
        $tenantId = $explicitTenantId ?? tenant_id();
        $cacheKey = "tenant_language_settings_{$tenantId}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($tenantId) {
            try {
                return DB::table('tenant_settings')
                    ->where('tenant_id', $tenantId)
                    ->where('group', 'language')
                    ->select(['key', 'value'])
                    ->get()
                    ->keyBy('key')
                    ->map(function ($item) {
                        return $item->value;
                    })
                    ->toArray();
            } catch (\Exception $e) {
                app_log("Error fetching tenant language settings for tenant $tenantId: ".$e->getMessage(), 'error', $e);

                return [];
            }
        });
    }

    /**
     * Clear tenant language cache
     */
    public static function clearLanguageCache(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant_id();

        // Clear tenant language settings
        Cache::forget("tenant_language_settings_{$tenantId}");

        // Clear tenant-specific translation caches
        $patterns = [
            "tenant_languages_{$tenantId}_*",
            "translations_{$tenantId}_*",
            "lang_json_{$tenantId}_*",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
