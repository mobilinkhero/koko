<?php

namespace App\Services;

use App\Models\Tenant;
use Corbital\Settings\Models\TenantSetting;
use Illuminate\Support\Facades\Cache;

/**
 * TenantSettingsService
 *
 * Comprehensive tenant-specific settings management service for multi-tenant
 * WhatsApp SaaS applications. Provides full CRUD operations with intelligent
 * caching, group-based organization, and runtime configuration management.
 *
 * Key Features:
 * - Individual and bulk settings management
 * - Group-based settings organization
 * - Multi-layer caching (individual, group, tenant-wide)
 * - Runtime configuration synchronization
 * - Automatic cache invalidation
 * - Tenant context awareness
 *
 * Settings Architecture:
 * - Settings are organized by groups (whatsapp, billing, notifications, etc.)
 * - Each setting has a key-value structure within its group
 * - Cache keys are hierarchically structured for efficient invalidation
 * - Runtime config is synchronized for immediate access
 *
 * Caching Strategy:
 * - Individual setting cache: tenant_{id}_setting_{group}_{key}
 * - Group cache: tenant_{id}_settings_group_{group}
 * - Tenant-wide cache: tenant_{id}_settings
 * - Cache TTL: 30 minutes for optimal performance
 *
 * Common Setting Groups:
 * - 'whatsapp': WhatsApp API configuration, tokens, webhook settings
 * - 'billing': Payment gateway settings, currency, tax rates
 * - 'notifications': Email templates, SMS settings, alert preferences
 * - 'features': Feature flags, limits, quota configurations
 * - 'ui': Theme settings, branding, customization options
 *
 * Usage Examples:
 * ```php
 * $settingsService = new TenantSettingsService();
 *
 * // Get individual setting
 * $apiKey = $settingsService->get('whatsapp', 'api_key', 'default_key');
 *
 * // Get all settings in a group
 * $whatsappSettings = $settingsService->getGroup('whatsapp');
 *
 * // Set single setting
 * $settingsService->set('billing', 'currency', 'USD');
 *
 * // Set multiple settings at once
 * $settingsService->setMany('whatsapp', [
 *     'api_key' => 'new_key',
 *     'webhook_url' => 'https://app.example.com/webhook'
 * ]);
 *
 * // Delete setting
 * $settingsService->delete('notifications', 'deprecated_setting');
 *
 * // Clear all cache
 * $settingsService->clearCache();
 * ```
 *
 * @see \App\Services\TenantSettingsCache
 * @see \Corbital\Settings\Models\TenantSetting
 * @see \App\Models\Tenant
 *
 * @version 1.0.0
 */
class TenantSettingsService
{
    /**
     * Get a setting value for the current tenant with simple caching.
     */
    public function get(string $group, string $key, $default = null)
    {
        if (! Tenant::checkCurrent()) {
            return $default;
        }

        $tenant = Tenant::current();
        $cacheKey = "tenant_{$tenant->id}_setting_{$group}_{$key}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($tenant, $group, $key, $default) {
            $setting = TenantSetting::where('tenant_id', $tenant->id)
                ->where('group', $group)
                ->where('key', $key)
                ->value('value');

            return $setting ?? $default;
        });
    }

    /**
     * Get all settings in a group with simple caching.
     */
    public function getGroup(string $group): array
    {
        if (! Tenant::checkCurrent()) {
            return [];
        }

        $tenant = Tenant::current();
        $cacheKey = "tenant_{$tenant->id}_settings_group_{$group}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($tenant, $group) {
            return TenantSetting::where('tenant_id', $tenant->id)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Set a setting value for the current tenant.
     */
    public function set(string $group, string $key, $value): bool
    {
        if (! Tenant::checkCurrent()) {
            return false;
        }

        $tenant = Tenant::current();

        TenantSetting::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );

        // Clear specific cache keys
        Cache::forget("tenant_{$tenant->id}_setting_{$group}_{$key}");
        Cache::forget("tenant_{$tenant->id}_settings_group_{$group}");

        // Update in-memory config
        config(["tenant.{$group}.{$key}" => $value]);

        return true;
    }

    /**
     * Set multiple settings at once for the current tenant.
     */
    public function setMany(string $group, array $settings): bool
    {
        if (! Tenant::checkCurrent()) {
            return false;
        }

        $tenant = Tenant::current();

        foreach ($settings as $key => $value) {
            TenantSetting::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'group' => $group,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );

            // Clear individual cache
            Cache::forget("tenant_{$tenant->id}_setting_{$group}_{$key}");

            // Update in-memory config
            config(["tenant.{$group}.{$key}" => $value]);
        }

        // Clear group cache
        Cache::forget("tenant_{$tenant->id}_settings_group_{$group}");

        return true;
    }

    /**
     * Delete a setting for the current tenant.
     */
    public function delete(string $group, string $key): bool
    {
        if (! Tenant::checkCurrent()) {
            return false;
        }

        $tenant = Tenant::current();

        $deleted = TenantSetting::where('tenant_id', $tenant->id)
            ->where('group', $group)
            ->where('key', $key)
            ->delete();

        // Clear specific cache keys
        Cache::forget("tenant_{$tenant->id}_setting_{$group}_{$key}");
        Cache::forget("tenant_{$tenant->id}_settings_group_{$group}");

        // Clear from config
        config()->forget("tenant.{$group}.{$key}");

        return $deleted > 0;
    }

    /**
     * Clear all tenant settings cache.
     */
    public function clearCache(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? (Tenant::checkCurrent() ? Tenant::current()->id : null);

        if (! $tenantId) {
            return;
        }

        // Since we can't use tags with database driver, we'll need to clear all cache
        // This is less efficient but works with any cache driver
        Cache::flush();
    }
}
