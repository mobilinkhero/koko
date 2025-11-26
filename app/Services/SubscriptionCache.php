<?php

namespace App\Services;

use App\Facades\TenantCache;
use App\Models\Subscription;

/**
 * SubscriptionCache Service
 *
 * Provides comprehensive caching mechanisms for tenant subscription data to optimize
 * database performance and reduce query overhead in multi-tenant WhatsApp SaaS applications.
 *
 * Key Features:
 * - In-memory request-level caching to prevent duplicate queries
 * - Redis/cache layer integration for cross-request persistence
 * - Subscription status-based indexing for fast lookups
 * - Active subscription resolution with fallback priority
 * - Automatic cache invalidation and management
 *
 * Cache Structure:
 * - all: Complete collection of tenant subscriptions
 * - by_status: Subscriptions grouped by status (active, trial, pending, etc.)
 * - active: Primary active subscription with status priority
 * - pending: First pending subscription
 * - latest: Most recently created subscription
 *
 * Usage Examples:
 * ```php
 * // Get active subscription for tenant
 * $subscription = SubscriptionCache::getActiveSubscription($tenantId);
 *
 * // Check for pending subscriptions
 * if (SubscriptionCache::hasPendingSubscription($tenantId)) {
 *     // Handle pending subscription logic
 * }
 *
 * // Clear cache after subscription changes
 * SubscriptionCache::clearCache($tenantId);
 * ```
 *
 * @see \App\Models\Subscription
 * @see \App\Services\PlanService
 *
 * @version 1.0.0
 */
class SubscriptionCache
{
    /**
     * In-memory cache storage for subscription data
     *
     * Stores subscription data indexed by tenant ID to prevent
     * duplicate database queries within the same request lifecycle.
     *
     * Structure: [tenant_id => subscription_data_array]
     *
     * @var array<int, array>
     */
    protected static array $cache = [];

    /**
     * Loading state tracker for subscription data
     *
     * Prevents multiple loading attempts and ensures efficient
     * cache initialization per tenant.
     */
    protected static bool $loaded = false;

    /**
     * Generate cache key for tenant subscription data
     *
     * Creates a unique cache key for storing tenant-specific subscription
     * data in the application cache layer (Redis/file).
     *
     * @param  int  $tenantId  The tenant identifier
     * @return string Formatted cache key for tenant subscriptions
     *
     * @example
     * ```php
     * $key = SubscriptionCache::getCacheKey(123);
     * // Returns: "tenant_subscriptions_123"
     * ```
     */
    protected static function getCacheKey(int $tenantId): string
    {
        return "tenant_subscriptions_{$tenantId}";
    }

    /**
     * Load and cache tenant subscription data
     *
     * Loads all subscription data for a tenant from the database and caches it
     * both in memory and in the application cache layer. Creates indexed arrays
     * for efficient lookups by status and priority.
     *
     * Subscription Priority for Active Resolution:
     * 1. active - Currently active paid subscription
     * 2. trial - Active trial subscription
     * 3. paused - Temporarily paused subscription
     *
     * @param  int  $tenantId  The tenant identifier to load subscriptions for
     *
     * @throws \Exception When database query fails
     *
     * @example
     * ```php
     * // Load subscriptions for tenant 123
     * SubscriptionCache::loadSubscriptions(123);
     *
     * // Subsequent calls will use cached data
     * SubscriptionCache::loadSubscriptions(123); // No DB query
     * ```
     *
     * @see \App\Models\Subscription
     */
    public static function loadSubscriptions(int $tenantId): void
    {
        if (isset(static::$cache[$tenantId])) {
            return;
        }

        $cacheKey = static::getCacheKey($tenantId);

        static::$cache[$tenantId] = TenantCache::remember($cacheKey, 3600, function () use ($tenantId) { // 1 hour
            try {
                // Get all subscriptions for this tenant with essential data
                $subscriptions = Subscription::where('tenant_id', $tenantId)
                    ->select([
                        'id', 'tenant_id', 'plan_id', 'status', 'ended_at', 'trial_starts_at', 'trial_ends_at',
                        'created_at', 'updated_at', 'current_period_ends_at',
                    ])
                    ->with(['plan:id,name,price,is_free,billing_period,trial_days'])
                    ->get();

                // Index by status for quick lookup
                $result = [
                    'all' => $subscriptions,
                    'by_status' => $subscriptions->groupBy('status'),
                    'active' => $subscriptions->firstWhere('status', 'active') ?? $subscriptions->firstWhere('status', 'trial') ?? $subscriptions->firstWhere('status', 'paused'),
                    'pending' => $subscriptions->firstWhere('status', 'pending'),
                    'latest' => $subscriptions->sortByDesc('created_at')->first(),
                ];

                return $result;
            } catch (\Exception $e) {
                app_log('Error loading tenant subscriptions', 'error', $e, [
                    'error' => $e->getMessage(),
                ]);

                return [
                    'all' => collect([]),
                    'by_status' => collect([]),
                    'active' => null,
                    'pending' => null,
                    'latest' => null,
                ];
            }
        });
    }

    /**
     * Retrieve the active subscription for a tenant
     *
     * Returns the currently active subscription based on priority:
     * active > trial > paused. Automatically loads subscription data
     * if not already cached.
     *
     * @param  int  $tenantId  The tenant identifier
     * @return \App\Models\Subscription|null Active subscription or null if none found
     *
     * @example
     * ```php
     * $subscription = SubscriptionCache::getActiveSubscription(123);
     *
     * if ($subscription) {
     *     $planName = $subscription->plan->name;
     *     $status = $subscription->status;
     *     $expiresAt = $subscription->ends_at;
     * }
     * ```
     *
     * @see \App\Models\Subscription
     * @see loadSubscriptions()
     */
    public static function getActiveSubscription(int $tenantId)
    {
        static::loadSubscriptions($tenantId);

        return static::$cache[$tenantId]['active'] ?? null;
    }

    /**
     * Check if tenant has pending subscription
     *
     * Determines if the tenant has any subscriptions in pending status,
     * typically indicating payment processing or manual approval needed.
     *
     * @param  int  $tenantId  The tenant identifier
     * @return bool True if pending subscription exists, false otherwise
     *
     * @example
     * ```php
     * if (SubscriptionCache::hasPendingSubscription(123)) {
     *     // Show pending payment message
     *     $message = 'Your subscription is being processed...';
     * }
     * ```
     *
     * @see loadSubscriptions()
     */
    public static function hasPendingSubscription(int $tenantId): bool
    {
        static::loadSubscriptions($tenantId);

        return static::$cache[$tenantId]['pending'] !== null;
    }

    /**
     * Check if tenant has any subscription records
     *
     * Determines if the tenant has ever had any subscription,
     * regardless of status (active, expired, cancelled, etc.).
     *
     * @param  int  $tenantId  The tenant identifier
     * @return bool True if any subscription exists, false otherwise
     *
     * @example
     * ```php
     * if (!SubscriptionCache::hasAnySubscription(123)) {
     *     // Show subscription signup prompt
     *     return redirect()->route('plans.select');
     * }
     * ```
     *
     * @see loadSubscriptions()
     */
    public static function hasAnySubscription(int $tenantId): bool
    {
        static::loadSubscriptions($tenantId);

        return count(static::$cache[$tenantId]['all'] ?? []) > 0;
    }

    /**
     * Clear subscription cache for a tenant
     *
     * Removes both in-memory and persistent cache entries for a tenant's
     * subscription data. Should be called after subscription changes
     * (create, update, delete, status changes).
     *
     * @param  int  $tenantId  The tenant identifier
     *
     * @example
     * ```php
     * // After subscription update
     * $subscription->update(['status' => 'active']);
     * SubscriptionCache::clearCache($subscription->tenant_id);
     *
     * // After subscription creation
     * $newSubscription = Subscription::create($data);
     * SubscriptionCache::clearCache($newSubscription->tenant_id);
     * ```
     *
     * @see loadSubscriptions()
     */
    public static function clearCache(int $tenantId): void
    {
        try {
            $cacheKey = static::getCacheKey($tenantId);

            TenantCache::forget($cacheKey);
            unset(static::$cache[$tenantId]);

        } catch (\Exception $e) {
            app_log('Error clearing subscription cache', 'error', $e, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
