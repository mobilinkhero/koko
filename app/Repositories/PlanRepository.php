<?php

namespace App\Repositories;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PlanRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * Get the model class name.
     */
    protected function getModelClass(): string
    {
        return Plan::class;
    }

    /**
     * Get active plans.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePlans()
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();
    }

    /**
     * Get plans with features.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlansWithFeatures()
    {
        // Use the dedicated PlanFeatureCache service which returns a Collection
        return \App\Services\PlanFeatureCache::getAllPlansWithFeatures();
    }

    /**
     * Get a plan by ID with its features.
     */
    public function findById(int $planId): ?Plan
    {
        $cacheKey = 'plan_with_features_'.$planId;

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($planId) {
            return Plan::with([
                'planFeatures:id,plan_id,feature_id,name,slug,value',
                'planFeatures.feature:id,name,slug,type,display_order',
            ])->find($planId);
        });
    }

    /**
     * Get all features.
     */
    public function getAllFeatures(): Collection
    {
        return Cache::remember('all_features', now()->addHours(1), function () {
            return Feature::orderBy('display_order')->get();
        });
    }

    /**
     * Get available plans with features - optimized for the available plans page
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailablePlansOptimized(bool $excludeFreePlans = false)
    {
        // Use our admin helper function
        $isAdminContext = is_admin_context();

        // Only use tenant_id when not in admin context
        $tenantId = $isAdminContext ? 'admin' : tenant_id();
        $cacheKey = 'available_plans_optimized_'.$tenantId.($excludeFreePlans ? '_paid_only' : '_all');

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($excludeFreePlans) {
            logger()->info('Fetching optimized available plans from DB...');

            try {
                // First, get all features to avoid multiple queries
                $features = Feature::select('id', 'name', 'slug', 'display_order', 'type')
                    ->orderBy('display_order', 'asc')
                    ->get()
                    ->keyBy('id');

                // Then get all plans - avoid listing specific columns to prevent errors
                $query = Plan::query()
                    ->where('is_active', true);

                if ($excludeFreePlans) {
                    $query->where('price', '>', 0);
                }

                $plans = $query->with('planFeatures:id,plan_id,feature_id,name,value') //
                    ->orderBy('price')
                    ->get();

                // Get all plan features for these plans in one query
                $planFeatures = $plans->pluck('planFeatures', 'id');

                // Attach features to plans
                foreach ($plans as $plan) {
                    $featuresForPlan = $planFeatures->get($plan->id, collect([]));

                    // Add feature data directly
                    $formattedFeatures = $featuresForPlan->map(function ($planFeature) use ($features) {
                        $feature = $features->get($planFeature->feature_id);
                        $planFeature->feature = $feature;

                        return $planFeature;
                    });

                    $plan->planFeatures = $formattedFeatures;
                }

                return $plans;
            } catch (\Exception $e) {
                logger()->error('Error fetching optimized plans: '.$e->getMessage());

                // Fallback to simpler query if there's an error
                return $this->getActivePlans();
            }
        });
    }
}
