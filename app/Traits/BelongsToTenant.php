<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot the trait to apply tenant scoping.
     */
    public static function bootBelongsToTenant(): void
    {
        // Apply the tenant scope to all operations
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Only apply the tenant scope if we're in a tenant context and the query doesn't already join with the tenant
            if (Tenant::checkCurrent() && ! $builder->getQuery()->joins) {
                // Check if we already have a where clause for tenant_id to avoid duplicating it
                $alreadyScoped = collect($builder->getQuery()->wheres)
                    ->contains(function ($where) {
                        return isset($where['column']) ? $where['column'] === 'tenant_id' && (isset($where['value']) && $where['value'] == Tenant::current()->id) : null;
                    });

                if (! $alreadyScoped) {
                    $builder->where('tenant_id', Tenant::current()->id);
                }
            }
        });

        // Automatically set the tenant_id on new models
        static::creating(function ($model) {
            if (! $model->isDirty('tenant_id') && Tenant::checkCurrent()) {
                $model->tenant_id = Tenant::current()->id;
            }
        });

        // Add protection against tenant_id modification
        static::updating(function ($model) {
            if ($model->isDirty('tenant_id')) {
                $user = Auth::user();

                // Only allow tenant_id changes for super admins
                if (! $user || ! $user->is_admin) {
                    throw new \Exception('Cannot change tenant assignment');
                }
            }
        });
    }

    /**
     * Define the relationship to the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include records for a specific tenant.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|Tenant  $tenant
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTenant($query, $tenant)
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query->where('tenant_id', $tenantId);
    }
}

/*
    * This trait is used to ensure that all models that use it are scoped to the current tenant.
    * It automatically sets the tenant_id on creation and prevents modification of tenant_id
    * unless the user is a super admin.
    *
    * Usage:
    * - Include this trait in your model class.
    * - Ensure that your model has a tenant_id column.
    *
    * Example:
    * class YourModel extends Model
    * {
    *     use BelongsToTenant;
    *
    *     // Other model code...
    * }
*/
