<?php

namespace App\Repositories;

use App\Models\Tenant\FeatureUsage;

class FeatureUsageRepository extends BaseRepository
{
    /**
     * Get the model class name.
     */
    protected function getModelClass(): string
    {
        return FeatureUsage::class;
    }

    /**
     * Get specific feature usage for a tenant.
     *
     * @return \App\Models\Tenant\FeatureUsage|null
     */
    public function getTenantFeatureUsage(int $tenantId, string $featureSlug, ?int $subscriptionId = null)
    {
        $query = $this->query()
            ->where('tenant_id', $tenantId)
            ->where('feature_slug', $featureSlug);

        if ($subscriptionId) {
            $query->where('subscription_id', $subscriptionId);
        }

        return $query->first();
    }

    /**
     * Increment usage for a tenant and feature.
     */
    public function incrementUsage(int $tenantId, string $featureSlug, int $subscriptionId, int $quantity = 1): bool
    {
        return FeatureUsage::incrementUsage($tenantId, $featureSlug, $subscriptionId, $quantity);
    }

    /**
     * Reset usage for a specific feature usage record.
     */
    public function resetUsage(int $id): bool
    {
        $featureUsage = $this->findOrFail($id);

        return $featureUsage->resetUsage();
    }
}
