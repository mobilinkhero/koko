<?php

namespace App\Repositories;

use App\Models\Feature;
use App\Models\FeatureLimit;
use App\Services\FeatureCache;

class FeatureLimitRepository extends BaseRepository
{
    /**
     * Get the model class name.
     */
    protected function getModelClass(): string
    {
        return FeatureLimit::class;
    }

    /**
     * Check if a tenant has access to a feature based on their subscription plan.
     */
    public function hasFeatureAccess(int $tenantId, int $planId, string $featureSlug, bool $requireActive = true): bool
    {
        return FeatureCache::hasFeatureAccess($tenantId, $planId, $featureSlug, $requireActive);
    }

    /**
     * Get the limit for a specific feature in a plan.
     */
    public function getFeatureLimit(int $tenantId, int $planId, string $featureSlug): ?int
    {
        return FeatureCache::getFeatureLimit($tenantId, $planId, $featureSlug);
    }

    /**
     * Create or update a custom feature limit for a tenant.
     */
    public function setCustomLimit(int $tenantId, string $featureSlug, ?int $limit, ?string $expiresAt = null): void
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (! $feature) {
            return;
        }

        // Create or update the custom limit
        FeatureLimit::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'feature_id' => $feature->id,
            ],
            [
                'custom_limit' => $limit,
                'expires_at' => $expiresAt ? now()->parse($expiresAt) : null,
            ]
        );

        // Clear the cache since we modified the limits
        FeatureCache::clearCache($tenantId);
    }

    /**
     * Remove a custom feature limit for a tenant.
     */
    public function removeCustomLimit(int $tenantId, string $featureSlug): void
    {
        $feature = Feature::where('slug', $featureSlug)->first();

        if (! $feature) {
            return;
        }

        FeatureLimit::where('tenant_id', $tenantId)
            ->where('feature_id', $feature->id)
            ->delete();

        // Clear the cache since we modified the limits
        FeatureCache::clearCache($tenantId);
    }
}
