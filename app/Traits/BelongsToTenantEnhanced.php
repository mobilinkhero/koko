<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait BelongsToTenantEnhanced
{
    /**
     * Boot the trait to apply tenant scoping.
     */
    public static function bootBelongsToTenantEnhanced(): void
    {
        // Apply the tenant scope to all operations - simplified and more efficient
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Tenant::checkCurrent()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', Tenant::current()->id);
            }
        });

        // Automatically set the tenant_id on new models
        static::creating(function ($model) {
            if (! $model->isDirty('tenant_id') && Tenant::checkCurrent()) {
                $model->tenant_id = Tenant::current()->id;
            }
        });

        // Add protection against tenant_id modification with better error handling
        static::updating(function ($model) {
            if ($model->isDirty('tenant_id')) {
                $user = Auth::user();

                // Only allow tenant_id changes for super admins or during tenant migration
                if (! $user || (! $user->is_admin && ! app()->runningInConsole())) {
                    throw new \InvalidArgumentException('Unauthorized tenant assignment change');
                }
            }
        });

        // Optimized cache invalidation - only clear when needed
        static::saved(function ($model) {
            if ($model->wasRecentlyCreated || $model->isDirty(['tenant_id'])) {
                $model->clearTenantCache();
            }
        });

        static::deleted(function ($model) {
            $model->clearTenantCache();
        });
    }

    /**
     * Define the relationship to the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include records for a specific tenant.
     */
    public function scopeForTenant($query, $tenant)
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Clear tenant-specific cache for this model.
     * Optimized to use Spatie's tenant-aware caching patterns.
     */
    public function clearTenantCache(): void
    {
        if (! Tenant::checkCurrent()) {
            return;
        }

        $tenantId = Tenant::current()->id;
        $modelClass = strtolower(class_basename($this));

        // Clear model-specific cache keys
        $modelClass = strtolower(class_basename($this));

        Cache::forget("model_{$modelClass}_{$this->getKey()}");

        // Clear additional cache keys if needed
        Cache::forget("tenant_{$tenantId}_models_{$modelClass}");
        Cache::forget("tenant_{$tenantId}_model_{$modelClass}_{$this->getKey()}");
    }

    /**
     * Get a cached version of this model with better performance.
     * Uses Spatie's tenant context for automatic cache isolation.
     */
    public function getCached(string $suffix = '', int $ttl = 3600): mixed
    {
        if (! Tenant::checkCurrent()) {
            return $this;
        }

        $tenantId = Tenant::current()->id;
        $modelClass = strtolower(class_basename($this));
        $cacheKey = "model_{$modelClass}_{$this->getKey()}".($suffix ? "_{$suffix}" : '');

        return Cache::remember($cacheKey, now()->addMinutes(60), fn () => $this->fresh());
    }

    /**
     * Scope to bypass tenant filtering (for super admins or system operations).
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Check if current model belongs to the current tenant.
     */
    public function belongsToCurrentTenant(): bool
    {
        return Tenant::checkCurrent() && $this->tenant_id === Tenant::current()->id;
    }
}
