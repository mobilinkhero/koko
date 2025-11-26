<?php

namespace App\Services;

use App\Models\Tenant\FeatureUsage;
use App\Repositories\FeatureLimitRepository;
use App\Repositories\FeatureUsageRepository;
use App\Repositories\SubscriptionRepository;
use Carbon\Carbon;

/**
 * Feature Service
 *
 * Manages feature access control, usage tracking, and limit enforcement for multi-tenant SaaS application.
 * This service is responsible for determining whether tenants can access specific features based on their
 * subscription plan, tracking feature usage, and enforcing usage limits.
 *
 * Key Responsibilities:
 * - Feature access validation based on subscription plans
 * - Usage tracking and limit enforcement
 * - Quota management and monitoring
 * - Feature availability checks
 * - Usage statistics and reporting
 *
 * Features Managed:
 * - WhatsApp status/broadcast limits
 * - Contact storage limits
 * - Message sending quotas
 * - Campaign creation limits
 * - Template usage tracking
 * - API access controls
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Repositories\FeatureLimitRepository
 * @see \App\Repositories\FeatureUsageRepository
 * @see \App\Repositories\SubscriptionRepository
 * @see \App\Models\Tenant\FeatureUsage
 *
 * @example
 * ```php
 * // Check if tenant can create more WhatsApp status
 * $featureService = app(FeatureService::class);
 * if ($featureService->hasAccess('status')) {
 *     if (!$featureService->hasReachedLimit('status', Status::class)) {
 *         // Tenant can create status
 *         $featureService->recordUsage('status');
 *     }
 * }
 *
 * // Get current usage and limits
 * $usage = $featureService->getCurrentUsage('contacts');
 * $limit = $featureService->getLimit('contacts');
 * $remaining = $limit - $usage;
 * ```
 */
class FeatureService
{
    /**
     * Feature limit repository instance
     *
     * @var FeatureLimitRepository
     */
    protected $featureLimitRepository;

    /**
     * Subscription repository instance
     *
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * Feature usage repository instance
     *
     * @var FeatureUsageRepository
     */
    protected $featureUsageRepository;

    /**
     * Create a new service instance.
     *
     * @param  FeatureLimitRepository  $featureLimitRepository  Repository for feature limits
     * @param  SubscriptionRepository  $subscriptionRepository  Repository for subscriptions
     * @param  FeatureUsageRepository  $featureUsageRepository  Repository for feature usage
     */
    public function __construct(
        FeatureLimitRepository $featureLimitRepository,
        SubscriptionRepository $subscriptionRepository,
        FeatureUsageRepository $featureUsageRepository
    ) {
        $this->featureLimitRepository = $featureLimitRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->featureUsageRepository = $featureUsageRepository;
    }

    /**
     * Check if a tenant has access to a specific feature.
     *
     * Validates whether the current tenant's subscription plan includes access to the specified feature.
     * This method checks subscription status, expiration, and feature availability in the plan.
     *
     * @param  string  $featureSlug  The feature slug to check (e.g., 'status', 'contacts', 'campaigns')
     * @param  bool  $requireActive  Whether to require an active subscription (default: true)
     * @return bool True if tenant has access, false otherwise
     *
     * @example
     * ```php
     * // Check if tenant can access WhatsApp status feature
     * if ($this->featureService->hasAccess('status')) {
     *     // Feature is available in current plan
     * }
     *
     * // Check access without requiring active subscription
     * $hasAccess = $this->featureService->hasAccess('premium_feature', false);
     * ```
     *
     * @see FeatureLimitRepository::hasFeatureAccess()
     * @see SubscriptionRepository::getActiveSubscription()
     */
    public function hasAccess(string $featureSlug, bool $requireActive = true): bool
    {
        // Skip check if no tenant is active (e.g., admin routes)
        if (! current_tenant()) {
            return true;
        }

        $tenant = current_tenant();
        if (! $tenant) {
            return false;
        }

        $subscription = $this->subscriptionRepository->getActiveSubscription(tenant_id());

        // If no active subscription, deny access
        if (! $subscription) {
            return false;
        }

        // Check for paused subscription with expired period
        if ($subscription->isPause() && $subscription->current_period_ends_at < now()) {
            return false;
        }
        // Check for canselled subscription with expired period
        if ($subscription->isCancelled() && $subscription->current_period_ends_at < now()) {
            return false;
        }

        // Check if feature is available in the plan
        return $this->featureLimitRepository->hasFeatureAccess(
            $tenant->id,
            $subscription->plan_id,
            $featureSlug,
            $requireActive
        );
    }

    /**
     * Get the limit for a specific feature.
     *
     * Returns the maximum allowed usage for a feature based on the tenant's current subscription plan.
     * Returns null if no subscription is found or feature is not available.
     *
     * @param  string  $featureSlug  The feature slug to get limit for
     * @return int|null The feature limit, or null if not available/unlimited
     *
     * @example
     * ```php
     * $statusLimit = $this->featureService->getLimit('status');
     * if ($statusLimit === null) {
     *     // Unlimited or feature not available
     * } else {
     *     echo "You can create up to {$statusLimit} status messages";
     * }
     * ```
     *
     * @see FeatureLimitRepository::getFeatureLimit()
     */
    public function getLimit(string $featureSlug): ?int
    {
        if (! current_tenant()) {
            return null;
        }

        $subscription = $this->subscriptionRepository->getActiveSubscription(tenant_id());
        if (! $subscription) {
            return null;
        }

        return $this->featureLimitRepository->getFeatureLimit(
            tenant_id(),
            $subscription->plan_id,
            $featureSlug
        );
    }

    /**
     * Check if a tenant has reached their limit for a specific feature.
     * It will also initiate usage tracking so limit checks are properly recorded.
     *
     * This method performs comprehensive limit checking by counting existing records
     * and comparing against the plan's feature limits. It can optionally track usage
     * without incrementing the counter.
     *
     * @param  string  $featureSlug  The feature slug (e.g. 'status', 'contacts')
     * @param  string|null  $modelClass  The model class to count (e.g. Status::class)
     * @param  array  $additionalConditions  Additional query conditions
     * @param  bool  $trackWithoutIncrement  Check limit but don't increment usage
     * @return bool True if limit is reached, false otherwise
     *
     * @example
     * ```php
     * // Check if tenant has reached status creation limit
     * if ($this->featureService->hasReachedLimit('status', Status::class)) {
     *     throw new FeatureLimitExceededException('Status creation limit reached');
     * }
     *
     * // Check with additional conditions
     * $conditions = ['status' => 'active'];
     * $limitReached = $this->featureService->hasReachedLimit(
     *     'active_campaigns',
     *     Campaign::class,
     *     $conditions
     * );
     * ```
     *
     * @throws \Exception When feature tracking fails
     *
     * @see FeatureLimitRepository::getFeatureLimit()
     * @see FeatureUsageRepository::getCurrentUsage()
     */
    public function hasReachedLimit(
        string $featureSlug,
        ?string $modelClass = null,
        array $additionalConditions = [],
        bool $trackWithoutIncrement = true,
        $tenant_id = null,
    ): bool {
        if (empty($tenant_id) && ! tenant_check()) {
            return false; // No tenant, no limit
        }

        $tenantId = $tenant_id ?? tenant_id();
        $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);

        if (! $subscription) {
            return true; // No subscription, reached limit
        }

        // Check for paused subscription with expired period
        if ($subscription->isPause() && $subscription->current_period_ends_at < now()) {
            return true; // Treat as reached limit if paused and period expired
        }

        // Get the limit for this feature
        $limit = $this->featureLimitRepository->getFeatureLimit(
            $tenantId,
            $subscription->plan_id,
            $featureSlug
        );
        // If limit is -1, it means unlimited
        if ($limit === -1) {
            // Still set up usage tracking for reporting even if unlimited
            $this->initializeFeatureUsageTracking($tenantId, $featureSlug, $subscription->id, $limit);

            return false;
        }

        // If limit is 0, feature is disabled
        if ($limit === 0) {
            return true;
        }

        // First check for tracked usage in the database
        $featureUsage = $this->initializeFeatureUsageTracking($tenantId, $featureSlug, $subscription->id, $limit);
        if ($featureUsage) {
            // If it's unlimited or within limit, it's not reached
            return $featureUsage->used >= $limit;
        }

        // If no model class provided, just check the limit value
        if (! $modelClass) {
            return false;
        }

        if (method_exists($modelClass, 'fromTenant')) {
            $tenant_id = tenant_id();
            $tenant_subdomain = tenant_subdomain_by_tenant_id($tenant_id);
            // Count current usage from the model
            $query = $modelClass::fromTenant($tenant_subdomain)->where('tenant_id', $tenantId);
        } else {
            // Count current usage from the model
            $query = $modelClass::where('tenant_id', $tenantId);
        }

        // Add any additional conditions
        foreach ($additionalConditions as $column => $value) {
            $query->where($column, $value);
        }

        $currentUsage = $query->count();

        // Track this count in our tracking system
        if ($trackWithoutIncrement) {
            $this->syncUsageCount($tenantId, $featureSlug, $subscription->id, $currentUsage);
        }

        return $currentUsage >= $limit;
    }

    /**
     * Initialize feature usage tracking.
     *
     * Creates or retrieves a feature usage record for tracking feature consumption.
     * Handles subscription changes and billing cycle resets automatically.
     *
     * @param  int  $tenantId  The tenant identifier
     * @param  string  $featureSlug  The feature slug to track
     * @param  int  $subscriptionId  The subscription identifier
     * @param  int|null  $limitValue  The feature limit value (-1 for unlimited)
     * @return FeatureUsage|null The feature usage record or null on failure
     *
     * @see calculateResetDate()
     */
    protected function initializeFeatureUsageTracking(
        int $tenantId,
        string $featureSlug,
        int $subscriptionId,
        ?int $limitValue
    ): ?FeatureUsage {
        // Get or create the feature usage record
        $featureUsage = $this->featureUsageRepository->getTenantFeatureUsage(
            $tenantId,
            $featureSlug,
            $subscriptionId
        );

        if (! $featureUsage) {
            // Create a new usage record with no reset date
            $featureUsage = FeatureUsage::create([
                'tenant_id' => $tenantId,
                'subscription_id' => $subscriptionId,
                'feature_slug' => $featureSlug,
                'limit_value' => $limitValue ?? -1, // -1 indicates unlimited
                'used' => 0,
                'reset_date' => null,
                'period_start' => Carbon::now(),
                'last_reset_at' => null,
            ]);
        } else {
            // Update the limit value if it has changed
            if ($featureUsage->limit_value !== $limitValue) {
                $featureUsage->limit_value = $limitValue ?? -1;
                $featureUsage->save();
            }

            // Check if we need to reset based on the reset date
            if ($featureUsage->reset_date && $featureUsage->reset_date <= Carbon::now()) {
                $this->resetUsage($featureUsage->tenant_id);
            }
        }

        return $featureUsage;
    }

    /**
     * Sync the usage count to match the actual count in the database.
     * This is used to ensure our tracking system stays in sync with reality.
     *
     * Compares tracked usage with actual database records and updates the tracking
     * to maintain accuracy. Logs high usage warnings when approaching limits.
     *
     * @param  int  $tenantId  The tenant identifier
     * @param  string  $featureSlug  The feature slug to sync
     * @param  int  $subscriptionId  The subscription identifier
     * @param  int  $currentCount  The actual current count from database
     * @return bool True if sync successful, false otherwise
     *
     * @example
     * ```php
     * // Sync WhatsApp status count with tracking
     * $actualCount = Status::where('tenant_id', $tenantId)->count();
     * $this->featureService->syncUsageCount($tenantId, 'status', $subscriptionId, $actualCount);
     * ```
     *
     * @see logHighUsage()
     */
    public function syncUsageCount(int $tenantId, string $featureSlug, int $subscriptionId, int $currentCount): bool
    {
        try {
            $featureUsage = $this->featureUsageRepository->getTenantFeatureUsage(
                $tenantId,
                $featureSlug,
                $subscriptionId
            );

            if (! $featureUsage) {
                // If no usage record exists, initialize it with the current count
                $limit = $this->featureLimitRepository->getFeatureLimit(
                    $tenantId,
                    $subscriptionId,
                    $featureSlug
                );

                $featureUsage = FeatureUsage::create([
                    'tenant_id' => $tenantId,
                    'subscription_id' => $subscriptionId,
                    'feature_slug' => $featureSlug,
                    'limit_value' => $limit ?? -1,
                    'used' => $currentCount,
                    'reset_date' => null,
                    'period_start' => Carbon::now(),
                    'last_reset_at' => null,
                ]);

                return true;
            }

            // Only update if the count has changed
            if ($featureUsage->used !== $currentCount) {
                $featureUsage->used = $currentCount;
                $featureUsage->save();

                // Log high usage (80% or more) for limited features
                if (
                    $featureUsage->limit_value > 0 && $featureUsage->used >= ($featureUsage->limit_value * 0.8)
                ) {
                    $this->logHighUsage($featureUsage);
                }
            }

            return true;
        } catch (\Exception $e) {
            app_log('Error syncing feature usage count', 'error', $e, [
                'tenant_id' => $tenantId,
                'feature_slug' => $featureSlug,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Track usage for a specific feature.
     *
     * Records feature usage by incrementing the usage counter. This method should be
     * called whenever a feature is used to maintain accurate usage tracking.
     *
     * @param  string  $featureSlug  The feature slug to track usage for
     * @param  int  $quantity  The quantity to add to usage (default: 1)
     * @return bool True if tracking successful, false otherwise
     *
     * @example
     * ```php
     * // Track single status creation
     * $this->featureService->trackUsage('status');
     *
     * // Track bulk contact import
     * $this->featureService->trackUsage('contacts', 150);
     * ```
     *
     * @see hasReachedLimit()
     * @see getCurrentUsage()
     */
    public function trackUsage(string $featureSlug, int $quantity = 1, $tenant_id = null): bool
    {
        if (empty($tenant_id) && ! tenant_check()) {
            return false;
        }

        $tenantId = $tenant_id ?? tenant_id();

        try {
            $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);
            if (! $subscription) {
                return false;
            }

            // Clean up old subscription records first
            $this->cleanupOldFeatureUsageRecords($tenantId, $subscription->id);

            // Get the model class for the feature to sync actual count
            $modelClass = $this->getModelClassForFeature($featureSlug);

            // Create or update usage record
            $featureUsage = FeatureUsage::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'subscription_id' => $subscription->id,
                    'feature_slug' => $featureSlug,
                ],
                [
                    'used' => 0,
                    'limit_value' => $this->getLimit($featureSlug) ?? -1,
                    'reset_date' => null,
                    'period_start' => Carbon::now(),
                    'last_reset_at' => null,
                ]
            );

            if ($modelClass && class_exists($modelClass)) {
                // Sync with actual count from database
                if (method_exists($modelClass, 'fromTenant')) {
                    $tenant_subdomain = tenant_subdomain_by_tenant_id($tenantId);
                    $actualCount = $modelClass::fromTenant($tenant_subdomain)
                        ->where('tenant_id', $tenantId)
                        ->count();
                } else {
                    $actualCount = $modelClass::where('tenant_id', $tenantId)->count();
                }

                // Update usage to match actual count
                $featureUsage->used = $actualCount;
            } else {
                // If no model class found, just increment
                $featureUsage->used += $quantity;
            }

            return $featureUsage->save();
        } catch (\Exception $e) {
            app_log('Error tracking feature usage', 'error', $e, [
                'tenant_id' => $tenantId,
                'feature_slug' => $featureSlug,
                'quantity' => $quantity,
            ]);

            return false;
        }
    }

    /**
     * Reset usage for a tenant, optionally for a specific subscription.
     *
     * Resets feature usage counters to zero. This is typically called during billing
     * cycle transitions or subscription changes.
     *
     * @param  int  $tenantId  The tenant identifier
     * @param  int|null  $subscriptionId  Optional subscription ID to exclude from reset
     * @return bool True if reset successful, false otherwise
     *
     * @example
     * ```php
     * // Reset all usage for tenant
     * $this->featureService->resetUsage($tenantId);
     *
     * // Reset usage except for current subscription
     * $this->featureService->resetUsage($tenantId, $currentSubscriptionId);
     * ```
     */
    public function resetUsage(int $tenantId, ?int $subscriptionId = null, ?string $featureSlug = null): bool
    {
        // This method should only be used when creating a new subscription
        // It should not be used for periodic resets as per business requirements
        try {
            $query = FeatureUsage::where('tenant_id', $tenantId);

            if ($subscriptionId) {
                // Only reset features for the new subscription
                $query->where('subscription_id', $subscriptionId);
            }

            if ($featureSlug) {
                $query->where('feature_slug', $featureSlug);
            }

            // Initialize usage to 0 for new subscription
            return (bool) $query->update([
                'used' => 0,
                'reset_date' => null,  // We don't use reset dates anymore
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the remaining limit for a feature.
     *
     * Calculates how much quota remains for a specific feature based on current usage
     * and the plan's limits. Returns null for unlimited features.
     *
     * @param  string  $featureSlug  The feature slug to check
     * @param  string|null  $modelClass  Optional model class for accurate counting
     * @param  array  $additionalConditions  Additional query conditions
     * @return int|null Null for unlimited, otherwise remaining count
     *
     * @example
     * ```php
     * $remaining = $this->featureService->getRemainingLimit('status');
     * if ($remaining === null) {
     *     echo "Unlimited status creation";
     * } elseif ($remaining > 0) {
     *     echo "You can create {$remaining} more status messages";
     * } else {
     *     echo "Status creation limit reached";
     * }
     * ```
     *
     * @see getCurrentUsage()
     * @see getLimit()
     */
    public function getRemainingLimit(string $featureSlug, ?string $modelClass = null, array $additionalConditions = []): ?int
    {
        if (! tenant_check()) {
            return null;
        }

        $tenantId = tenant_id();
        $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);

        if (! $subscription) {
            return 0;
        }

        // Get the limit for this feature
        $limit = $this->featureLimitRepository->getFeatureLimit(
            $tenantId,
            $subscription->plan_id,
            $featureSlug
        );

        // If limit is -1, it means unlimited
        if ($limit === -1) {
            return null;
        }

        // If limit is 0, feature is disabled
        if ($limit === 0) {
            return 0;
        }
        // First check if we have a tracked usage for this feature
        $featureUsage = $this->featureUsageRepository->getTenantFeatureUsage(
            $tenantId,
            $featureSlug,
            $subscription->id
        );

        if ($featureUsage) {
            // Check if we need to reset based on the reset date
            if ($featureUsage->reset_date && $featureUsage->reset_date <= Carbon::now()) {
                $this->resetUsage($featureUsage->tenant_id, null, $featureUsage->feature_slug);

                return $limit; // After reset, all quota available
            }

            return max(0, $limit - $featureUsage->used);
        }

        // If no tracked usage and no model class provided, just return the limit
        if (! $modelClass) {
            return $limit;
        }

        if (method_exists($modelClass, 'fromTenant')) {
            $tenant_id = tenant_id();
            $tenant_subdomain = tenant_subdomain_by_tenant_id($tenant_id);
            // Count current usage from the model
            $query = $modelClass::fromTenant($tenant_subdomain)->where('tenant_id', $tenantId);
        } else {
            // Count current usage from the model
            $query = $modelClass::where('tenant_id', $tenantId);
        }

        // Add any additional conditions
        foreach ($additionalConditions as $column => $value) {
            $query->where($column, $value);
        }

        $currentUsage = $query->count();

        // Track this count in our tracking system for future queries
        $this->syncUsageCount($tenantId, $featureSlug, $subscription->id, $currentUsage);

        return max(0, $limit - $currentUsage);
    }

    /**
     * Get the current usage for a feature.
     *
     * Returns the current usage count for a specific feature, checking both tracked
     * usage and actual database records for accuracy.
     *
     * @param  string  $featureSlug  The feature slug to check usage for
     * @param  string|null  $modelClass  Optional model class for direct counting
     * @param  array  $additionalConditions  Additional query conditions
     * @return int Current usage count
     *
     * @example
     * ```php
     * $currentUsage = $this->featureService->getCurrentUsage('contacts');
     * $limit = $this->featureService->getLimit('contacts');
     * $percentage = ($currentUsage / $limit) * 100;
     * ```
     *
     * @see getRemainingLimit()
     * @see trackUsage()
     */
    public function getCurrentUsage(string $featureSlug, ?string $modelClass = null, array $additionalConditions = []): int
    {
        if (! tenant_check()) {
            return 0;
        }

        $tenantId = tenant_id();
        $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);

        if (! $subscription) {
            return 0;
        }

        // First check if we have a tracked usage for this feature
        $featureUsage = $this->featureUsageRepository->getTenantFeatureUsage(
            $tenantId,
            $featureSlug,
            $subscription->id
        );

        if ($featureUsage) {
            return $featureUsage->used;
        }

        // If no tracked usage and no model class provided, return 0
        if (! $modelClass) {
            return 0;
        }

        // Count current usage from the model
        $query = $modelClass::where('tenant_id', $tenantId);

        // Add any additional conditions
        foreach ($additionalConditions as $column => $value) {
            $query->where($column, $value);
        }

        $currentUsage = $query->count();

        // Track this count in our tracking system for future queries
        $this->syncUsageCount($tenantId, $featureSlug, $subscription->id, $currentUsage);

        return $currentUsage;
    }

    /**
     * Explicitly increment usage of a model feature.
     * Use this when you create a new record and want to increment usage right away.
     *
     * Combines current usage retrieval with usage tracking to ensure accurate
     * counting when new records are created.
     *
     * @param  string  $featureSlug  The feature slug to increment
     * @param  string|null  $modelClass  Optional model class for counting
     * @param  array  $additionalConditions  Additional query conditions
     * @return bool True if increment successful, false otherwise
     *
     * @example
     * ```php
     * // After creating a new campaign
     * $campaign = Campaign::create($data);
     * $this->featureService->incrementModelFeatureUsage('campaigns', Campaign::class);
     * ```
     *
     * @see trackUsage()
     * @see getCurrentUsage()
     */
    public function incrementModelFeatureUsage(string $featureSlug, ?string $modelClass = null, array $additionalConditions = []): bool
    {
        // First get current count
        $currentUsage = $this->getCurrentUsage($featureSlug, $modelClass, $additionalConditions);

        // Then increment by 1
        return $this->trackUsage($featureSlug, 1);
    }

    /**
     * Log high usage for monitoring and notifications.
     *
     * Records when feature usage reaches 80% or higher of the limit for monitoring
     * and potential notification purposes.
     *
     * @param  FeatureUsage  $featureUsage  The feature usage record
     *
     * @see syncUsageCount()
     */
    protected function logHighUsage(FeatureUsage $featureUsage): void
    {
        $percentage = round(($featureUsage->used / $featureUsage->limit_value) * 100);
    }

    /**
     * Sync the usage count for a model-based feature.
     * This ensures the feature usage record reflects the actual count.
     *
     * Counts actual database records and syncs with the usage tracking system
     * to maintain accuracy between tracked and actual usage.
     *
     * @param  string  $featureSlug  The feature slug to sync
     * @param  string  $modelClass  The model class to count
     * @param  array  $additionalConditions  Additional query conditions
     * @return bool True if sync successful, false otherwise
     *
     * @example
     * ```php
     * // Sync contact count after bulk operations
     * $this->featureService->syncModelCount('contacts', Contact::class);
     *
     * // Sync active campaigns only
     * $this->featureService->syncModelCount(
     *     'active_campaigns',
     *     Campaign::class,
     *     ['status' => 'active']
     * );
     * ```
     *
     * @see syncUsageCount()
     * @see getCurrentUsage()
     */
    public function syncModelCount(string $featureSlug, string $modelClass, array $additionalConditions = []): bool
    {
        if (! tenant_check()) {
            return false;
        }

        $tenantId = tenant_id();

        try {
            $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);

            if (! $subscription) {
                return false;
            }

            // Count current records with proper tenant handling
            if (method_exists($modelClass, 'fromTenant')) {
                $tenant_subdomain = tenant_subdomain_by_tenant_id($tenantId);
                $query = $modelClass::fromTenant($tenant_subdomain)->where('tenant_id', $tenantId);
            } else {
                $query = $modelClass::where('tenant_id', $tenantId);
            }

            // Add any additional conditions
            foreach ($additionalConditions as $column => $value) {
                $query->where($column, $value);
            }

            $actualCount = $query->count();
            app_log($actualCount, 'info', null, [
                'tenant_id' => $tenantId,
            ]);
            // Sync with feature usage record
            $synced = $this->syncUsageCount($tenantId, $featureSlug, $subscription->id, $actualCount);

            return $synced;
        } catch (\Exception $e) {
            app_log('Error syncing model count for feature', 'error', $e, [
                'tenant_id' => $tenantId,
                'feature_slug' => $featureSlug,
                'model_class' => $modelClass,
            ]);

            return false;
        }
    }

    /**
     * Get model class for a feature slug
     */
    protected function getModelClassForFeature(string $featureSlug): ?string
    {
        $modelMappings = [
            'canned_replies' => \App\Models\Tenant\CannedReply::class,
            'ai_prompts' => \App\Models\Tenant\AiPrompt::class,
            'contacts' => \App\Models\Tenant\Contact::class,
            'campaigns' => \App\Models\Tenant\Campaign::class,
            'message_bots' => \App\Models\Tenant\MessageBot::class,
            'template_bots' => \App\Models\Tenant\TemplateBot::class,
            'staff' => \App\Models\User::class,
            'conversation' => \App\Models\Tenant\ChatMessage::class,
            'bot_flow' => \App\Models\Tenant\BotFlow::class,
        ];

        $mappings = apply_filters('before_get_model_class_for_feature', $modelMappings);

        return $mappings[$featureSlug] ?? null;
    }

    /**
     * Clean up old feature usage records for previous subscription
     */
    public function cleanupOldFeatureUsageRecords(int $tenantId, int $newSubscriptionId): bool
    {
        try {
            // Delete feature usage records from old subscriptions
            $deletedCount = FeatureUsage::where('tenant_id', $tenantId)
                ->where('subscription_id', '!=', $newSubscriptionId)
                ->delete();

            return true;
        } catch (\Exception $e) {
            app_log('Error cleaning up old feature usage records', 'error', $e, [
                'tenant_id' => $tenantId,
                'new_subscription_id' => $newSubscriptionId,
            ]);

            return false;
        }
    }

    /**
     * Sync all feature counts for a tenant after plan change
     */
    public function syncAllFeatureCounts(int $tenantId): bool
    {
        try {
            $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);

            if (! $subscription) {
                app_log('No active subscription found for tenant', 'warning', null, [
                    'tenant_id' => $tenantId,
                ]);

                return false;
            }

            // Get feature mappings from config
            $featureModelMappings = config('features.feature_model_mappings', []);

            if (empty($featureModelMappings)) {
                app_log('No feature model mappings found in config', 'warning', null, [
                    'tenant_id' => $tenantId,
                ]);

                return false;
            }

            $syncedFeatures = [];
            foreach ($featureModelMappings as $featureSlug => $modelClass) {
                // Check if the model class exists
                if (! class_exists($modelClass)) {
                    app_log("Model class does not exist: {$modelClass}", 'warning', null, [
                        'tenant_id' => $tenantId,
                        'feature_slug' => $featureSlug,
                    ]);

                    continue;
                }

                // Check if the feature exists in the new plan
                if ($this->hasAccess($featureSlug, false)) {
                    $syncResult = $this->syncModelCount($featureSlug, $modelClass);
                    $syncedFeatures[$featureSlug] = $syncResult;
                }
            }

            // Clean up old feature usage records
            $this->cleanupOldFeatureUsageRecords($tenantId, $subscription->id);

            return true;
        } catch (\Exception $e) {
            app_log('Error syncing all feature counts', 'error', $e, [
                'tenant_id' => $tenantId,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Sync a specific feature count for a tenant
     */
    public function syncSpecificFeatureCount(int $tenantId, string $featureSlug): bool
    {
        try {
            $modelClass = $this->getModelClassForFeature($featureSlug);

            if (! $modelClass) {
                app_log("No model class found for feature: {$featureSlug}", 'warning', null, [
                    'tenant_id' => $tenantId,
                    'feature_slug' => $featureSlug,
                ]);

                return false;
            }

            if (! class_exists($modelClass)) {
                app_log("Model class does not exist: {$modelClass}", 'warning', null, [
                    'tenant_id' => $tenantId,
                    'feature_slug' => $featureSlug,
                ]);

                return false;
            }

            return $this->syncModelCount($featureSlug, $modelClass);
        } catch (\Exception $e) {
            app_log('Error syncing specific feature count', 'error', $e, [
                'tenant_id' => $tenantId,
                'feature_slug' => $featureSlug,
            ]);

            return false;
        }
    }

    /**
     * Get all available features from config
     */
    public function getAvailableFeatures(): array
    {
        return array_keys(config('features.feature_model_mappings', []));
    }

    /**
     * Check if a conversation session is active for a specific contact within 24 hours
     *
     * @param  int  $contactId  The contact ID
     * @param  int|null  $tenantId  The tenant ID (optional, will use current tenant if null)
     * @param  string|null  $tenantSubdomain  The tenant subdomain (optional, will resolve if null)
     * @return bool True if session is active, false otherwise
     */
    public function isConversationSessionActive($contactIdOrChatId, $tenantId = null, $tenantSubdomain = null, $type = null): bool
    {
        // Ensure we have proper tenant context
        $tenantId = $tenantId ?? tenant_id();
        $tenantSubdomain = $tenantSubdomain ?? tenant_subdomain_by_tenant_id($tenantId);

        if (! $tenantId || ! $tenantSubdomain) {
            return false;
        }

        try {
            $cutoffTime = \Carbon\Carbon::now()->subHours(24);

            $query = \App\Models\Tenant\Chat::fromTenant($tenantSubdomain)
                ->where('tenant_id', $tenantId)
                ->where('last_msg_time', '>', $cutoffTime);

            if ($type === 'guest') {
                // For guests, use the chat ID directly
                $query->where('id', $contactIdOrChatId)->where('type', 'guest');
            } else {
                // For leads and customers, use type_id
                $query->where('type_id', $contactIdOrChatId)
                    ->whereIn('type', ['lead', 'customer']);
            }

            $activeChat = $query->first();

            return $activeChat !== null;
        } catch (\Exception $e) {
            app_log('ERROR: Session check failed', 'error', $e, [
                'contact_or_chat_id' => $contactIdOrChatId,
                'type' => $type,
            ]);

            return false;
        }
    }

    /**
     * Check conversation limit before starting new conversation
     *
     * @param  int  $contactId  The contact ID to check
     * @param  int|null  $tenantId  The tenant ID (optional)
     * @param  string|null  $tenantSubdomain  The tenant subdomain (optional)
     * @return bool True if limit reached, false if can start conversation
     */
    public function checkConversationLimit($contactIdOrChatId, $tenantId = null, $tenantSubdomain = null, $type = null): bool
    {
        // Ensure we have proper tenant context
        $tenantId = $tenantId ?? tenant_id();
        $tenantSubdomain = $tenantSubdomain ?? tenant_subdomain_by_tenant_id($tenantId);

        if (! $tenantId || ! $tenantSubdomain) {
            return true; // Fail safe
        }

        // STEP 1: Check if there's an active session within 24 hours
        $hasActiveSession = $this->isConversationSessionActive($contactIdOrChatId, $tenantId, $tenantSubdomain, $type);

        if ($hasActiveSession) {
            return false; // Can send message
        }

        // STEP 2: No active session - this would be a NEW conversation
        // Check if limit is reached
        $limitReached = $this->hasReachedLimit('conversations');

        return $limitReached;
    }

    /**
     * Track new conversation usage (only call for NEW conversations)
     *
     * @param  int  $contactId  The contact ID
     * @param  int|null  $tenantId  The tenant ID (optional)
     * @param  string|null  $tenantSubdomain  The tenant subdomain (optional)
     * @return bool True if tracking successful
     */
    public function trackNewConversation($contactIdOrChatId, $tenantId = null, $tenantSubdomain = null, $type = null): bool
    {
        // Ensure we have proper tenant context
        $tenantId = $tenantId ?? tenant_id();
        $tenantSubdomain = $tenantSubdomain ?? tenant_subdomain_by_tenant_id($tenantId);

        if (! $tenantId || ! $tenantSubdomain) {
            return false;
        }

        // STEP 1: Check if there's already an active session
        $hasActiveSession = $this->isConversationSessionActive($contactIdOrChatId, $tenantId, $tenantSubdomain, $type);

        if ($hasActiveSession) {
            return true; // Success but didn't track
        }

        // STEP 2: No active session - track as new conversation
        $tracked = $this->trackUsage('conversations');

        return $tracked;
    }

    /**
     * Force initialize feature usage tracking for conversations
     * This ensures the feature_usages record is created with correct limits
     */
    public function forceInitializeConversationTracking(): bool
    {
        if (! tenant_check()) {
            return false;
        }

        $tenantId = tenant_id();
        $subscription = $this->subscriptionRepository->getActiveSubscription($tenantId);

        if (! $subscription) {
            return false;
        }

        // Get the correct limit from plan
        $limit = $this->featureLimitRepository->getFeatureLimit(
            $tenantId,
            $subscription->plan_id,
            'conversations'
        );

        // Force create/update the feature usage record
        $featureUsage = $this->initializeFeatureUsageTracking($tenantId, 'conversations', $subscription->id, $limit);

        if ($featureUsage) {
            return true;
        }

        return false;
    }

    /**
     * Synchronize conversation usage with existing active chats
     * This method counts currently active conversations and updates the usage counter
     *
     * @param  int|null  $tenantId  The tenant ID (optional, will use current tenant if null)
     * @param  string|null  $tenantSubdomain  The tenant subdomain (optional, will resolve if null)
     * @return array Synchronization result with details
     */
    public function syncConversationUsage($tenantId = null, $tenantSubdomain = null): array
    {
        // Ensure we have proper tenant context
        $tenantId = $tenantId ?? tenant_id();
        $tenantSubdomain = $tenantSubdomain ?? tenant_subdomain_by_tenant_id($tenantId);

        if (! $tenantId || ! $tenantSubdomain) {
            return [
                'success' => false,
                'error' => 'No tenant context available',
            ];
        }

        try {
            // Force initialize conversation tracking first
            $this->forceInitializeConversationTracking();

            $cutoffTime = \Carbon\Carbon::now()->subHours(24);

            // Count unique active conversations by type and identifier
            $activeConversations = \App\Models\Tenant\Chat::fromTenant($tenantSubdomain)
                ->where('tenant_id', $tenantId)
                ->where('last_msg_time', '>', $cutoffTime)
                ->whereIn('type', ['lead', 'customer', 'guest'])
                ->select('type', 'type_id', 'id', 'last_msg_time', 'name', 'receiver_id')
                ->get()
                ->groupBy(function ($chat) {
                    // Group by conversation identifier
                    if ($chat->type === 'guest') {
                        return 'guest_'.$chat->id;  // Use chat ID for guests
                    } else {
                        return $chat->type.'_'.$chat->type_id;  // Use type_id for leads/customers
                    }
                })
                ->map(function ($group) {
                    // For each group, get the most recent chat record
                    return $group->sortByDesc('last_msg_time')->first();
                });

            $activeCount = $activeConversations->count();
            $currentUsage = $this->getCurrentUsage('conversations');

            // Update the usage counter to match active conversations
            $updated = \App\Models\Tenant\FeatureUsage::where('tenant_id', $tenantId)
                ->where('feature_slug', 'conversations')
                ->update(['used' => $activeCount]);

            $result = [
                'success' => true,
                'tenant_id' => $tenantId,
                'sync_time' => now()->toDateTimeString(),
                'cutoff_time' => $cutoffTime->toDateTimeString(),
                'before_sync' => [
                    'usage' => $currentUsage,
                    'limit' => $this->getLimit('conversations'),
                ],
                'after_sync' => [
                    'usage' => $activeCount,
                    'limit' => $this->getLimit('conversations'),
                ],
                'active_conversations_found' => $activeCount,
                'database_records_updated' => $updated,
                'conversations_detail' => $activeConversations->map(function ($chat) {
                    return [
                        'id' => $chat->id,
                        'type' => $chat->type,
                        'type_id' => $chat->type_id,
                        'name' => $chat->name,
                        'receiver_id' => $chat->receiver_id,
                        'last_msg_time' => $chat->last_msg_time,
                        'hours_ago' => $chat->last_msg_time ? now()->diffInHours($chat->last_msg_time) : null,
                    ];
                })->values(),
            ];

            return $result;
        } catch (\Exception $e) {
            app_log('Conversation usage sync failed', 'error', $e, [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ];
        }
    }

    /**
     * Cleanup and fix existing chat records for conversation tracking
     * This method ensures all chats have proper last_msg_time values
     *
     * @param  int|null  $tenantId  The tenant ID (optional, will use current tenant if null)
     * @param  string|null  $tenantSubdomain  The tenant subdomain (optional, will resolve if null)
     * @return array Cleanup result with details
     */
    public function cleanupChatRecords($tenantId = null, $tenantSubdomain = null): array
    {
        // Ensure we have proper tenant context
        $tenantId = $tenantId ?? tenant_id();
        $tenantSubdomain = $tenantSubdomain ?? tenant_subdomain_by_tenant_id($tenantId);

        if (! $tenantId || ! $tenantSubdomain) {
            return [
                'success' => false,
                'error' => 'No tenant context available',
            ];
        }

        try {
            // Find chats with missing or invalid last_msg_time
            $chatsWithoutLastMsgTime = \App\Models\Tenant\Chat::fromTenant($tenantSubdomain)
                ->where('tenant_id', $tenantId)
                ->whereIn('type', ['lead', 'customer', 'guest'])
                ->where(function ($query) {
                    $query->whereNull('last_msg_time')
                        ->orWhere('last_msg_time', '0000-00-00 00:00:00')
                        ->orWhere('last_msg_time', '');
                })
                ->get();

            $fixedCount = 0;

            foreach ($chatsWithoutLastMsgTime as $chat) {
                // Try to get the last message time from chat_messages
                try {
                    $lastMessage = \App\Models\Tenant\ChatMessage::fromTenant($tenantSubdomain)
                        ->where('interaction_id', $chat->id)
                        ->where('tenant_id', $tenantId)
                        ->orderBy('time_sent', 'desc')
                        ->first();

                    $newLastMsgTime = null;

                    if ($lastMessage && $lastMessage->time_sent) {
                        $newLastMsgTime = $lastMessage->time_sent;
                    } elseif ($chat->time_sent) {
                        $newLastMsgTime = $chat->time_sent;
                    } elseif ($chat->created_at) {
                        $newLastMsgTime = $chat->created_at;
                    } else {
                        // Default to creation time if nothing else available
                        $newLastMsgTime = now()->subDays(2); // Make it older than 24h to not count as active
                    }

                    // Update the chat record
                    $chat->update(['last_msg_time' => $newLastMsgTime]);
                    $fixedCount++;
                } catch (\Exception $e) {
                    app_log('Failed to fix chat record', 'error', $e, [
                        'chat_id' => $chat->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $result = [
                'success' => true,
                'tenant_id' => $tenantId,
                'cleanup_time' => now()->toDateTimeString(),
                'chats_found_without_last_msg_time' => $chatsWithoutLastMsgTime->count(),
                'chats_fixed' => $fixedCount,
                'details' => $chatsWithoutLastMsgTime->map(function ($chat) {
                    return [
                        'id' => $chat->id,
                        'type' => $chat->type,
                        'type_id' => $chat->type_id,
                        'name' => $chat->name,
                        'old_last_msg_time' => $chat->last_msg_time,
                        'time_sent' => $chat->time_sent,
                        'created_at' => $chat->created_at,
                    ];
                }),
            ];

            return $result;
        } catch (\Exception $e) {
            app_log('Chat records cleanup failed', 'error', $e, [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ];
        }
    }

    /**
     * Full conversation system synchronization
     * Combines cleanup and sync operations
     *
     * @param  int|null  $tenantId  The tenant ID (optional, will use current tenant if null)
     * @param  string|null  $tenantSubdomain  The tenant subdomain (optional, will resolve if null)
     * @return array Full sync result
     */
    public function fullConversationSync($tenantId = null, $tenantSubdomain = null): array
    {
        // Ensure we have proper tenant context
        $tenantId = $tenantId ?? tenant_id();
        $tenantSubdomain = $tenantSubdomain ?? tenant_subdomain_by_tenant_id($tenantId);

        // Step 1: Initialize conversation tracking
        $initialized = $this->forceInitializeConversationTracking();

        // Step 2: Cleanup chat records
        $cleanupResult = $this->cleanupChatRecords($tenantId, $tenantSubdomain);

        // Step 3: Sync conversation usage
        $syncResult = $this->syncConversationUsage($tenantId, $tenantSubdomain);

        $result = [
            'success' => $cleanupResult['success'] && $syncResult['success'],
            'tenant_id' => $tenantId,
            'full_sync_time' => now()->toDateTimeString(),
            'step_1_initialization' => [
                'completed' => $initialized,
                'description' => 'Initialize conversation tracking system',
            ],
            'step_2_cleanup' => $cleanupResult,
            'step_3_sync' => $syncResult,
            'final_state' => [
                'conversation_usage' => $this->getCurrentUsage('conversations'),
                'conversation_limit' => $this->getLimit('conversations'),
                'limit_reached' => $this->hasReachedLimit('conversations'),
            ],
        ];

        return $result;
    }
}
