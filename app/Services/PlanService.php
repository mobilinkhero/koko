<?php

namespace App\Services;

use App\Facades\AdminCache;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Plan Service
 *
 * Manages subscription plans and their associated features for the multi-tenant SaaS application.
 * This service handles plan creation, modification, feature synchronization, and plan lifecycle management.
 *
 * Key Responsibilities:
 * - Plan CRUD operations (Create, Read, Update, Delete)
 * - Feature association and synchronization
 * - Plan validation and constraint enforcement
 * - Feature limit management per plan
 * - Plan availability and activation controls
 *
 * Features Managed:
 * - WhatsApp status/broadcast limits per plan
 * - Contact storage quotas
 * - Message sending limits
 * - Campaign creation allowances
 * - Template usage quotas
 * - API access levels
 * - Advanced feature toggles
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Models\Plan
 * @see \App\Models\Feature
 * @see \App\Models\PlanFeature
 * @see \App\Services\FeatureService
 *
 * @example
 * ```php
 * // Create a new plan with features
 * $planService = app(PlanService::class);
 * $planData = ['name' => 'Pro Plan', 'price' => 29.99, 'billing_cycle' => 'monthly'];
 * $features = [1 => 1000, 2 => 5000, 3 => -1]; // feature_id => limit (-1 = unlimited)
 * $plan = $planService->createPlan($planData, $features);
 *
 * // Get all active plans
 * $activePlans = $planService->getAllPlans(true);
 *
 * // Update plan features
 * $newFeatures = [1 => 2000, 2 => 10000];
 * $planService->updatePlan($planId, $planData, $newFeatures);
 * ```
 */
class PlanService
{
    /**
     * Get all plans with their features.
     *
     * Retrieves all subscription plans from the database with their associated features.
     * Can optionally filter to return only active plans.
     *
     * @param  bool  $activeOnly  Whether to return only active plans (default: false)
     * @return Collection<int, Plan> Collection of Plan models with loaded features
     *
     * @example
     * ```php
     * // Get all plans including inactive ones
     * $allPlans = $this->planService->getAllPlans();
     *
     * // Get only active plans for customer selection
     * $activePlans = $this->planService->getAllPlans(true);
     * ```
     *
     * @see Plan::with()
     */
    public function getAllPlans(bool $activeOnly = false): Collection
    {
        $cacheKey = $activeOnly ? 'plans_active' : 'plans_all';

        return AdminCache::remember($cacheKey, function () use ($activeOnly) {
            $query = Plan::with('features');

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            return $query->orderBy('price')->get();
        }, ['plans']);
    }

    /**
     * Get a plan by ID with its features.
     *
     * Retrieves a specific plan along with all its associated features and limits.
     * Returns null if the plan is not found.
     *
     * @param  int  $id  The plan identifier
     * @return Plan|null The plan with features, or null if not found
     *
     * @example
     * ```php
     * $plan = $this->planService->getPlan(1);
     * if ($plan) {
     *     foreach ($plan->features as $feature) {
     *         echo "{$feature->name}: {$feature->pivot->value}";
     *     }
     * }
     * ```
     *
     * @see Plan::with()
     */
    public function getPlan(int $id): ?Plan
    {
        return Plan::with('features')->find($id);
    }

    /**
     * Create a new plan with features.
     *
     * Creates a new subscription plan with the specified data and associated features.
     * All operations are wrapped in a database transaction for consistency.
     *
     * @param  array  $planData  Plan attributes (name, price, billing_cycle, etc.)
     * @param  array  $features  Key-value pairs of feature_id => limit value
     * @return Plan The created plan with features
     *
     * @throws Exception When plan creation or feature association fails
     *
     * @example
     * ```php
     * $planData = [
     *     'name' => 'Premium Plan',
     *     'price' => 49.99,
     *     'billing_cycle' => 'monthly',
     *     'description' => 'Full-featured plan',
     *     'is_active' => true
     * ];
     *
     * $features = [
     *     1 => 5000,  // Status limit: 5000
     *     2 => 10000, // Contact limit: 10000
     *     3 => -1,    // Campaigns: unlimited
     *     4 => 50     // Templates: 50
     * ];
     *
     * $plan = $this->planService->createPlan($planData, $features);
     * ```
     *
     * @see syncPlanFeatures()
     * @see Plan::create()
     */
    public function createPlan(array $planData, array $features = []): Plan
    {
        try {
            DB::beginTransaction();

            $plan = Plan::create($planData);

            // Add features
            if (! empty($features)) {
                $this->syncPlanFeatures($plan, $features);
            }

            // Trigger comprehensive cache invalidation using centralized cache manager
            $this->handlePlanCacheInvalidation('created', $plan);

            DB::commit();

            return $plan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing plan and its features.
     *
     * Updates plan data and replaces all associated features with new ones.
     * All operations are wrapped in a database transaction for consistency.
     *
     * @param  int  $planId  The plan identifier to update
     * @param  array  $planData  Updated plan attributes
     * @param  array  $features  New feature configuration (replaces existing)
     * @return Plan The updated plan with fresh feature data
     *
     * @throws Exception When plan update or feature synchronization fails
     *
     * @example
     * ```php
     * $planData = ['name' => 'Updated Plan Name', 'price' => 39.99];
     * $features = [1 => 3000, 2 => 8000]; // New limits
     *
     * $updatedPlan = $this->planService->updatePlan(1, $planData, $features);
     * ```
     *
     * @see syncPlanFeatures()
     * @see Plan::findOrFail()
     */
    public function updatePlan(int $planId, array $planData, array $features = []): Plan
    {
        try {
            DB::beginTransaction();

            $plan = Plan::findOrFail($planId);
            $originalData = $plan->toArray();
            $plan->update($planData);

            // Update features if provided
            if (! empty($features)) {
                // Remove existing features
                PlanFeature::where('plan_id', $plan->id)->delete();

                // Add new features
                $this->syncPlanFeatures($plan, $features);
            }

            // Intelligent cache invalidation based on changes
            $this->handlePlanUpdateCacheInvalidation($originalData, $plan->fresh()->toArray());

            DB::commit();

            return $plan->fresh(['features']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Synchronize plan features with proper name assignment.
     *
     * Associates features with a plan by creating PlanFeature records.
     * Preloads feature models for performance and includes proper metadata.
     *
     * @param  Plan  $plan  The plan to associate features with
     * @param  array  $features  Array of feature_id => limit_value pairs
     *
     * @example
     * ```php
     * $features = [
     *     1 => 1000,  // WhatsApp Status: 1000 per month
     *     2 => 5000,  // Contacts: 5000 total
     *     3 => -1     // Campaigns: unlimited
     * ];
     * $this->syncPlanFeatures($plan, $features);
     * ```
     *
     * @see Feature::whereIn()
     * @see PlanFeature::create()
     */
    protected function syncPlanFeatures(Plan $plan, array $features): void
    {
        // Preload all features in a single query for performance
        $featureModels = Feature::whereIn('id', array_keys($features))->get()->keyBy('id');

        foreach ($features as $featureId => $value) {
            // Get feature from our preloaded collection
            $feature = $featureModels->get($featureId);

            if (! $feature) {
                continue; // Skip invalid feature IDs
            }

            PlanFeature::create([
                'plan_id' => $plan->id,
                'feature_id' => $featureId,
                'value' => empty($value) ? 0 : $value,
                'name' => $feature->name,
                'slug' => $feature->slug, // Add the required slug field
            ]);
        }
    }

    /**
     * Delete a plan and all associated features.
     *
     * Removes a plan from the system after validating that it has no active subscriptions.
     * Cascade deletion automatically removes associated PlanFeature records.
     *
     * @param  int  $planId  The plan identifier to delete
     * @return bool True if deletion successful
     *
     * @throws Exception When plan has active subscriptions or deletion fails
     *
     * @example
     * ```php
     * try {
     *     $success = $this->planService->deletePlan($oldPlanId);
     *     if ($success) {
     *         // Plan deleted successfully
     *     }
     * } catch (Exception $e) {
     *     // Handle error (likely has active subscriptions)
     *     logger()->error('Plan deletion failed: ' . $e->getMessage());
     * }
     * ```
     *
     * @see Plan::findOrFail()
     * @see Plan::subscriptions()
     */
    public function deletePlan(int $planId): bool
    {
        try {
            DB::beginTransaction();

            $plan = Plan::findOrFail($planId);

            // Check if the plan has any active subscriptions
            if ($plan->subscriptions()->exists()) {
                throw new Exception('Cannot delete a plan with active subscriptions');
            }

            // Cascade delete will handle plan_features
            $result = $plan->delete();

            // Trigger cache invalidation for plan deletion
            $this->handlePlanCacheInvalidation('deleted', $plan);

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all available features.
     *
     * Retrieves all feature definitions available in the system, ordered by display priority.
     * Used for plan configuration and feature management interfaces.
     *
     * @return Collection<int, Feature> Collection of all available features
     *
     * @example
     * ```php
     * $features = $this->planService->getAllFeatures();
     * foreach ($features as $feature) {
     *     echo "Feature: {$feature->name} ({$feature->slug})";
     *     echo "Type: {$feature->type}";
     *     echo "Default: {$feature->default_value}";
     * }
     * ```
     *
     * @see Feature::orderBy()
     */
    public function getAllFeatures(): Collection
    {
        return Feature::orderBy('display_order')->get();
    }

    /**
     * Handle comprehensive cache invalidation for plan operations
     */
    private function handlePlanCacheInvalidation(string $operation, Plan $plan): void
    {
        // Always invalidate plan caches
        AdminCache::invalidateTag('plans');

        // Conditional invalidation based on plan properties and operation
        if ($plan->is_active) {
            AdminCache::invalidateTag('navigation');
            AdminCache::invalidateTag('frontend');
        }

        if ($plan->featured) {
            AdminCache::invalidateTag('featured_plans');
        }

        // Clear dashboard stats for significant changes
        if (in_array($operation, ['created', 'deleted'])) {
            AdminCache::invalidateTag('admin_dashboard');
        }

        // Clear pricing caches if plan affects pricing
        if ($plan->is_active) {
            AdminCache::invalidateTag('pricing');
        }
    }

    /**
     * Handle intelligent cache invalidation based on plan update changes
     */
    private function handlePlanUpdateCacheInvalidation(array $originalData, array $newData): void
    {
        $changedFields = [];

        foreach ($newData as $key => $value) {
            if (isset($originalData[$key]) && $originalData[$key] !== $value) {
                $changedFields[] = $key;
            }
        }

        // Always invalidate basic plan caches
        AdminCache::invalidateTag('plans');

        // Selective invalidation based on changed fields
        if (in_array('is_active', $changedFields)) {
            AdminCache::invalidateTag('navigation'); // Sidebar counts might change
            AdminCache::invalidateTag('admin_dashboard'); // Dashboard stats
            AdminCache::invalidateTag('frontend'); // Public pricing page
        }

        if (in_array('featured', $changedFields)) {
            AdminCache::invalidateTag('featured_plans');
            AdminCache::invalidateTag('frontend');
        }

        if (in_array('price', $changedFields) || in_array('yearly_price', $changedFields)) {
            AdminCache::invalidateTag('pricing');
            AdminCache::invalidateTag('admin_dashboard');
            AdminCache::invalidateTag('frontend');
        }

        if (in_array('name', $changedFields) || in_array('description', $changedFields)) {
            AdminCache::invalidateTag('navigation');
            AdminCache::invalidateTag('frontend');
        }
    }

    /**
     * Get tags that should be invalidated for specific field changes
     */
    private function getInvalidatedTagsForFields(array $fields): array
    {
        $tagMap = [
            'is_active' => ['navigation', 'admin_dashboard', 'frontend'],
            'featured' => ['featured_plans', 'frontend'],
            'price' => ['pricing', 'admin_dashboard', 'frontend'],
            'yearly_price' => ['pricing', 'admin_dashboard', 'frontend'],
            'name' => ['navigation', 'frontend'],
            'description' => ['navigation', 'frontend'],
        ];

        $tags = ['plans']; // Always include plans

        foreach ($fields as $field) {
            if (isset($tagMap[$field])) {
                $tags = array_merge($tags, $tagMap[$field]);
            }
        }

        return array_unique($tags);
    }
}
