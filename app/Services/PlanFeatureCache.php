<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Plan Feature Cache Service
 *
 * Provides high-performance caching for plan-feature relationships to eliminate N+1 queries
 * and optimize subscription plan data retrieval. This service maintains tenant-aware caches
 * of plan features, their relationships, and feature definitions for rapid access.
 *
 * Key Features:
 * - **N+1 Query Prevention**: Loads all plan-feature relationships in a single batch
 * - **Tenant-Aware Caching**: Separate cache per tenant to prevent data leakage
 * - **In-Memory + Persistent**: Combines static cache with Laravel's cache system
 * - **Relationship Management**: Maintains feature metadata with plan associations
 * - **Performance Optimization**: 1-hour cache TTL with lazy loading
 * - **Error Resilience**: Graceful fallback on cache failures
 *
 * Cache Structure:
 * - **plans**: Collection of active plans indexed by ID
 * - **features**: Collection of all features indexed by ID
 * - **planFeatures**: Plan-feature relationships grouped by plan_id
 *
 * Use Cases:
 * - Subscription plan selection pages
 * - Feature comparison tables
 * - Plan upgrade/downgrade flows
 * - Feature availability checks
 * - Admin plan management interfaces
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Models\Plan
 * @see \App\Models\Feature
 * @see \App\Models\PlanFeature
 * @see \App\Services\TenantCache
 *
 * @example
 * ```php
 * // Get features for a specific plan
 * $features = PlanFeatureCache::getPlanFeatures($planId);
 * foreach ($features as $planFeature) {
 *     echo "{$planFeature->feature->name}: {$planFeature->value}";
 * }
 *
 * // Get all plans with their features for comparison
 * $plansWithFeatures = PlanFeatureCache::getAllPlansWithFeatures();
 * foreach ($plansWithFeatures as $plan) {
 *     echo "Plan: {$plan->name}";
 *     foreach ($plan->planFeatures as $feature) {
 *         echo "- {$feature->feature->name}: {$feature->value}";
 *     }
 * }
 *
 * // Clear cache after plan changes
 * PlanFeatureCache::clearCache();
 * ```
 */
class PlanFeatureCache
{
    /**
     * In-memory cache for the current request
     */
    protected static array $cache = [];

    /**
     * Flag to track if plan features have been loaded
     */
    protected static bool $loaded = false;

    /**
     * Cache key for plan features
     *
     * Generates tenant-aware cache keys to ensure proper data isolation
     * between different tenants in the multi-tenant application.
     *
     * @param  string  $type  The cache type identifier
     * @return string Tenant-specific cache key
     */
    protected static function getCacheKey(string $type): string
    {
        // Use our admin helper function
        $isAdminContext = is_admin_context();

        // Only use tenant_id when not in admin context
        $tenantId = $isAdminContext ? 'admin' : tenant_id();

        return "plan_features_{$type}_{$tenantId}";
    }

    /**
     * Load all plan features into cache
     *
     * Performs efficient batch loading of all plan-feature relationships, avoiding N+1 queries
     * by fetching plans, features, and their relationships in separate optimized queries.
     * Results are cached for 1 hour to balance performance with data freshness.
     *
     *
     * @example
     * ```php
     * // Manually trigger cache loading (usually automatic)
     * PlanFeatureCache::loadPlanFeatures();
     *
     * // Subsequent calls will use cached data
     * $features = PlanFeatureCache::getPlanFeatures($planId);
     * ```
     *
     * @see Plan::where()
     * @see Feature::orderBy()
     * @see PlanFeature::whereIn()
     */
    public static function loadPlanFeatures(): void
    {
        // Quick return if already loaded in this request
        if (static::$loaded) {
            return;
        }

        // Use our admin helper function
        $isAdminContext = is_admin_context();

        // Only use tenant_id when not in admin context
        $tenantId = $isAdminContext ? 'admin' : tenant_id();
        $cacheKey = static::getCacheKey('all');

        // Define lock variables
        $lockKey = "lock_{$cacheKey}";
        $lockTimeout = 10; // seconds

        // Try to get data from cache first without lock
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            static::$cache = $cachedData;
            static::$loaded = true;

            return;
        }

        // Use lock for cache generation to prevent multiple processes from
        // generating the same cache simultaneously under high load
        static::$cache = Cache::lock($lockKey, $lockTimeout)->block(5, function () use ($cacheKey, $isAdminContext) {
            // Double-check if cache was populated while waiting for lock
            $cachedData = Cache::get($cacheKey);
            if ($cachedData !== null) {
                return $cachedData;
            }

            // Generate cache data
            $result = static::generateCacheData($isAdminContext);

            // Store in cache for 1 hour
            Cache::put($cacheKey, $result, now()->addHours(1));

            return $result;
        });

        static::$loaded = true;
    }

    /**
     * Generate cache data for plan features
     *
     * Extracted as a separate method for better maintainability and testing
     *
     * @param  bool  $isAdminContext  Whether we're in admin context
     * @return array Cache data structure
     */
    protected static function generateCacheData(bool $isAdminContext): array
    {
        try {
            // Customize query based on context
            if ($isAdminContext) {
                // For admin context, we can be more selective about columns
                $plans = Plan::with(['planFeatures:id,plan_id,feature_id,value,name,slug'])
                    ->select(['id', 'name', 'slug', 'description', 'price', 'billing_period', 'is_active', 'is_free', 'featured', 'trial_days', 'color'])
                    ->get();

            } else {
                // Standard query for tenant context
                $plans = Plan::with('planFeatures')->where('is_active', true)->get();
            }

            $features = Feature::orderBy('display_order', 'asc')
                ->get(['id', 'name', 'slug', 'display_order', 'type', 'description', 'default'])
                ->keyBy('id');

            $result = [
                'plans' => $plans->keyBy('id'),
                'features' => $features,
                'planFeatures' => $plans->pluck('planFeatures', 'id'), // Optional: planId => features
            ];

            return $result;
        } catch (\Exception $e) {
            app_log('Error loading plan features', 'error', $e, [
                'error' => $e->getMessage(),
            ]);

            return [
                'plans' => collect([]),
                'features' => collect([]),
                'planFeatures' => collect([]),
            ];
        }
    }

    /**
     * Get features for a specific plan
     *
     * Retrieves all features associated with a specific plan, including feature metadata
     * and the plan-specific values/limits. Features are returned with their full definitions.
     *
     * @param  int  $planId  The plan identifier
     * @return Collection Collection of PlanFeature models with attached Feature models
     *
     * @example
     * ```php
     * $features = PlanFeatureCache::getPlanFeatures($planId);
     *
     * foreach ($features as $planFeature) {
     *     $feature = $planFeature->feature;
     *     echo "Feature: {$feature->name}";
     *     echo "Slug: {$feature->slug}";
     *     echo "Limit: {$planFeature->value}";
     *     echo "Type: {$feature->type}";
     * }
     *
     * // Check if plan has specific feature
     * $hasFeature = $features->contains(function ($pf) {
     *     return $pf->feature->slug === 'whatsapp_status';
     * });
     * ```
     *
     * @see loadPlanFeatures()
     */
    public static function getPlanFeatures(int $planId): Collection
    {
        static::loadPlanFeatures();

        $planFeatures = static::$cache['planFeatures'] ?? collect([]);
        $features = static::$cache['features'] ?? collect([]);

        $result = collect([]);

        if (isset($planFeatures[$planId])) {
            $result = $planFeatures[$planId]->map(function ($planFeature) use ($features) {
                $feature = $features[$planFeature->feature_id] ?? null;
                $planFeature->feature = $feature;

                return $planFeature;
            });
        }

        return $result;
    }

    /**
     * Get all plans with their features
     *
     * Retrieves all active plans with their complete feature sets, ideal for building
     * plan comparison tables, pricing pages, and administrative interfaces.
     *
     * @return Collection Collection of Plan models with planFeatures relationship loaded
     *
     * @example
     * ```php
     * $plansWithFeatures = PlanFeatureCache::getAllPlansWithFeatures();
     *
     * // Build pricing comparison table
     * foreach ($plansWithFeatures as $plan) {
     *     echo "<div class='plan'>";
     *     echo "<h3>{$plan->name} - \${$plan->price}/month</h3>";
     *     echo "<ul>";
     *
     *     foreach ($plan->planFeatures as $planFeature) {
     *         $feature = $planFeature->feature;
     *         $limit = $planFeature->value == -1 ? 'Unlimited' : $planFeature->value;
     *         echo "<li>{$feature->name}: {$limit}</li>";
     *     }
     *
     *     echo "</ul>";
     *     echo "</div>";
     * }
     *
     * // Filter plans by feature availability
     * $plansWithUnlimitedContacts = $plansWithFeatures->filter(function ($plan) {
     *     return $plan->planFeatures->contains(function ($pf) {
     *         return $pf->feature->slug === 'contacts' && $pf->value == -1;
     *     });
     * });
     * ```
     *
     * @see loadPlanFeatures()
     * @see getPlanFeatures()
     */
    public static function getAllPlansWithFeatures(): Collection
    {
        static::loadPlanFeatures();

        $plans = static::$cache['plans'] ?? collect([]);
        $planFeatures = static::$cache['planFeatures'] ?? collect([]);
        $features = static::$cache['features'] ?? collect([]);

        return $plans->map(function ($plan) use ($planFeatures, $features) {
            $featuresForPlan = $planFeatures[$plan->id] ?? collect([]);
            $plan->planFeatures = $featuresForPlan->map(function ($planFeature) use ($features) {
                $feature = $features[$planFeature->feature_id] ?? null;
                $planFeature->feature = $feature;

                return $planFeature;
            });
            $plan->planFeatures->push([
                'name' => 'Whatsapp Webhook',
                'slug' => 'whatsapp_webhook',
                'value' => '-1',
            ]);

            $plan = apply_filters('before_rendar_pricing_plan_list', $plan);

            return $plan;
        });
    }

    /**
     * Clear the plan features cache
     *
     * Removes all cached plan-feature data for the current tenant from both memory
     * and persistent cache. Should be called when plan features are modified.
     *
     *
     * @example
     * ```php
     * // After updating plan features
     * $plan = Plan::find(1);
     * $plan->features()->sync([
     *     1 => ['value' => 5000],  // Updated contact limit
     *     2 => ['value' => 1000],  // Updated status limit
     * ]);
     *
     * // Clear cache to reload fresh data
     * PlanFeatureCache::clearCache();
     *
     * // Next access will load updated features
     * $freshFeatures = PlanFeatureCache::getPlanFeatures(1);
     * ```
     *
     * @see Cache::forget()
     * @see TenantCache::getCurrentTenantId()
     */
    public static function clearCache(): void
    {
        try {
            // Use our admin helper function
            $isAdminContext = is_admin_context();

            // Only use tenant_id when not in admin context
            $tenantId = $isAdminContext ? 'admin' : tenant_id();
            $cacheKey = static::getCacheKey('all');

            Cache::forget($cacheKey);
            static::$cache = [];
            static::$loaded = false;
            static::$featuresCache = null; // Also clear the static features cache

        } catch (\Exception $e) {
            app_log('Failed to clear plan feature cache', 'error', $e, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all features
     *
     * Retrieves all features from cache, avoiding repeated database queries.
     *
     * @return Collection Collection of Feature models
     */
    /**
     * Static request-level cache for features to avoid redundant processing
     */
    protected static ?Collection $featuresCache = null;

    /**
     * Get all features
     *
     * Retrieves all features from cache, avoiding repeated database queries.
     * Uses a request-level static cache for additional performance.
     *
     * @return Collection Collection of Feature models
     */
    public static function getFeatures(): Collection
    {
        // Use static request-level cache if available
        if (static::$featuresCache !== null) {
            return static::$featuresCache;
        }

        // Try to load from memory cache first
        if (! empty(static::$cache['features'] ?? [])) {
            static::$featuresCache = static::$cache['features'];

            return static::$featuresCache;
        }

        // Load from persistent cache or database
        static::loadPlanFeatures();
        static::$featuresCache = static::$cache['features'] ?? collect([]);

        return static::$featuresCache;
    }
}
