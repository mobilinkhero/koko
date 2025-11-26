<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class FeatureUsage
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $subscription_id
 * @property string $feature_slug
 * @property int $used
 * @property Carbon|null $reset_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $last_reset_at
 * @property Subscription $subscription
 * @property Tenant $tenant
 * @property int $limit_value
 * @property \Illuminate\Support\Carbon|null $period_start
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereFeatureSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereLastResetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereLimitValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereResetDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsage whereUsed($value)
 *
 * @mixin \Eloquent
 */
class FeatureUsage extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'subscription_id' => 'int',
        'feature_slug' => 'string',
        'limit_value' => 'int',
        'used' => 'int',
        'reset_date' => 'datetime',
        'period_start' => 'datetime',
        'last_reset_at' => 'datetime',
    ];

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'feature_slug',
        'limit_value',
        'used',
        'reset_date',
        'period_start',
        'last_reset_at',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Increment the usage counter for this feature.
     *
     * @param  int  $quantity  Amount to increment by
     * @return bool True if incremented, false if limit reached
     */
    public function incrementUsageCount(int $quantity = 1): bool
    {
        // For unlimited features or if there's quota available
        if ($this->limit_value === -1 || $this->used + $quantity <= $this->limit_value || $this->limit_value === 0) {
            $this->used += $quantity;
            $this->save();

            // Log high usage (80% or more) for limited features
            if ($this->limit_value > 0 && $this->used >= ($this->limit_value * 0.8)) {
                $percentage = round(($this->used / $this->limit_value) * 100);
            }

            return true;
        }

        return false;
    }

    /**
     * Reset the usage counter for this feature.
     */
    public function resetUsage(): bool
    {
        try {
            // Store previous usage for reference
            $previousUsage = $this->used;

            // Reset counters
            $this->used = 0;
            $this->last_reset_at = Carbon::now();

            // Update period tracking
            $this->period_start = Carbon::now();

            // Calculate new reset date based on the current one
            // This preserves the billing cycle pattern
            $newResetDate = $this->reset_date->copy();
            while ($newResetDate <= Carbon::now()) {
                // Default to monthly if we can't determine the interval
                $newResetDate = $newResetDate->addMonth();
            }

            $this->reset_date = $newResetDate;
            $this->save();

            return true;
        } catch (\Exception $e) {
            app_log('Error resetting feature usage', 'error', $e, [
                'feature_usage_id' => $this->id,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Static method to increment usage for a specific tenant, feature and subscription.
     */
    public static function incrementUsage(int $tenantId, string $featureSlug, int $subscriptionId, int $quantity = 1): bool
    {
        $featureUsage = self::where('tenant_id', $tenantId)
            ->where('feature_slug', $featureSlug)
            ->where('subscription_id', $subscriptionId)
            ->first();

        if (! $featureUsage) {
            return false;
        }

        return $featureUsage->incrementUsageCount($quantity);
    }
}
