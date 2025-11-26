<?php

namespace App\Services;

use App\Facades\TenantCache;
use App\Models\Feature;
use App\Models\FeatureLimit;
use App\Models\PlanFeature;
use Illuminate\Support\Facades\Cache;

class FeatureCache
{
    /**
     * In-memory cache storage for feature data
     *
     * @var array<int, array>
     */
    protected static array $cache = [];

    /**
     * Generate cache key for tenant feature data
     */
    protected static function getCacheKey(int $tenantId, int $planId): string
    {
        return "tenant_features:{$tenantId}:plan:{$planId}";
    }

    /**
     * Load and cache all feature data for a tenant and plan
     */
    protected static function loadFeatures(int $tenantId, int $planId): void
    {
        $cacheKey = static::getCacheKey($tenantId, $planId);

        if (! isset(static::$cache[$cacheKey])) {
            static::$cache[$cacheKey] = TenantCache::remember($cacheKey, 3600, function () use ($tenantId, $planId) {
                // Load all features
                $features = Feature::all()->keyBy('slug');

                // Load all plan features in one query
                $planFeatures = PlanFeature::where('plan_id', $planId)
                    ->get()
                    ->keyBy('feature_id');

                // Load all custom feature limits in one query
                $customLimits = FeatureLimit::where('tenant_id', $tenantId)
                    ->active()
                    ->get()
                    ->keyBy('feature_id');

                $featureData = [];
                foreach ($features as $slug => $feature) {
                    $featureData[$slug] = [
                        'id' => $feature->id,
                        'slug' => $feature->slug,
                        'plan_limit' => isset($planFeatures[$feature->id])
                            ? (int) $planFeatures[$feature->id]->value
                            : 0,
                        'custom_limit' => isset($customLimits[$feature->id])
                            ? (int) $customLimits[$feature->id]->custom_limit
                            : null,
                    ];
                }

                return $featureData;
            });
        }
    }

    /**
     * Get feature limit for a tenant
     */
    public static function getFeatureLimit(int $tenantId, int $planId, string $featureSlug): ?int
    {
        static::loadFeatures($tenantId, $planId);

        $cacheKey = static::getCacheKey($tenantId, $planId);
        $featureData = static::$cache[$cacheKey][$featureSlug] ?? null;
        if (! $featureData) {
            return 0;
        }

        // Return custom limit if set, otherwise plan limit
        return $featureData['custom_limit'] ?? $featureData['plan_limit'];
    }

    /**
     * Check if tenant has access to a feature
     */
    public static function hasFeatureAccess(int $tenantId, int $planId, string $featureSlug, bool $requireActive = true): bool
    {
        // Check default features first
        $defaultFeatures = config('app.tenant_default_feature', []);
        if (in_array($featureSlug, $defaultFeatures)) {
            return true;
        }

        $limit = static::getFeatureLimit($tenantId, $planId, $featureSlug);

        return ! ($requireActive && $limit === 0);
    }

    /**
     * Clear cache for a tenant
     */
    public static function clearCache(int $tenantId, ?int $planId = null): void
    {
        if ($planId) {
            $cacheKey = static::getCacheKey($tenantId, $planId);
            unset(static::$cache[$cacheKey]);
            Cache::forget($cacheKey);
        } else {
            // Clear all plans for tenant
            foreach (static::$cache as $key => $value) {
                if (str_starts_with($key, "tenant_features:{$tenantId}:")) {
                    unset(static::$cache[$key]);
                    Cache::forget($key);
                }
            }
        }
    }
}
