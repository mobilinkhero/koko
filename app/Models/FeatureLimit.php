<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property int $feature_id
 * @property int $tenant_id
 * @property string $custom_limit
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property-read \App\Models\Feature $feature
 * @property-read \App\Models\Plan $plan
 * @property-read \App\Models\Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereCustomLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureLimit whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FeatureLimit extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'feature_id',
        'tenant_id',
        'custom_limit',
        'reason',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_limit' => 'string',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the plan that owns the feature limit.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the feature that owns the feature limit.
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Get the tenant that owns the feature limit.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include active feature limits.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to only include expired feature limits.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Check if the feature limit is active.
     */
    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at > now();
    }

    /**
     * Check if the feature limit has expired.
     */
    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at <= now();
    }

    /**
     * Get the limit as an integer.
     */
    public function getLimitValue(): int
    {
        return (int) $this->custom_limit;
    }
}
