<?php

namespace App\Models;

use App\Facades\AdminCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property numeric $price
 * @property string|null $stripe_product_id
 * @property numeric $yearly_price
 * @property int $yearly_discount
 * @property string $billing_period
 * @property int $trial_days
 * @property int $interval
 * @property bool $is_active
 * @property bool $is_free
 * @property bool $featured
 * @property string|null $color
 * @property int|null $currency_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlanFeature> $features
 * @property-read int|null $features_count
 * @property-read string $formatted_price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlanFeature> $planFeatures
 * @property-read int|null $plan_features_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereBillingPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereIsFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereStripeProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereTrialDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereYearlyDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereYearlyPrice($value)
 *
 * @mixin \Eloquent
 */
class Plan extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'monthly_price',
        'yearly_discount',
        'billing_period',
        'is_active',
        'is_free',
        'featured',
        'color',
        'trial_days',
        'interval',
        'is_default',
        'stripe_product_id',
        'stripe_price_id',
        'metadata',
        'currency_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'featured' => 'boolean',
        'is_default' => 'boolean',
        'trial_days' => 'integer',
        'interval' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set slug from name before validation
        static::creating(function ($plan) {
            do_action('plan.before_create', $plan);
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });

        // Clear admin cache when plans are modified
        static::created(function ($plan) {
            do_action('plan.after_create', $plan);
            AdminCache::invalidateTag('plans');
            AdminCache::invalidateTag('navigation'); // Clear sidebar menu cache
        });

        static::updating(function ($plan) {
            do_action('plan.before_update', $plan);

            // Check for status changes
            if ($plan->isDirty('is_active')) {
                $oldStatus = $plan->getOriginal('is_active');
                $newStatus = $plan->is_active;

                if ($oldStatus && ! $newStatus) {
                    do_action('plan.before_deactivate', $plan);
                } elseif (! $oldStatus && $newStatus) {
                    do_action('plan.before_activate', $plan);
                }
            }

            // Check for price changes
            if ($plan->isDirty('price') || $plan->isDirty('yearly_price')) {
                do_action('plan.price_changed', $plan);
            }
        });

        static::updated(function ($plan) {
            do_action('plan.after_update', $plan);

            // Check for status changes
            if ($plan->wasChanged('is_active')) {
                $oldStatus = $plan->getOriginal('is_active');
                $newStatus = $plan->is_active;

                if ($oldStatus && ! $newStatus) {
                    do_action('plan.after_deactivate', $plan);
                } elseif (! $oldStatus && $newStatus) {
                    do_action('plan.after_activate', $plan);
                }
            }

            AdminCache::invalidateTag('plans');
            AdminCache::invalidateTag('navigation'); // Clear sidebar menu cache
        });

        static::deleting(function ($plan) {
            do_action('plan.before_delete', $plan);
        });

        static::deleted(function ($plan) {
            do_action('plan.after_delete', $plan);
            AdminCache::invalidateTag('plans');
            AdminCache::invalidateTag('navigation'); // Clear sidebar menu cache
        });
    }

    /**
     * Get the plan features.
     */
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * Get the features with the associated values (alias for planFeatures()).
     *
     * @deprecated Use planFeatures() instead for consistency
     */
    public function features()
    {
        return $this->planFeatures();
    }

    /**
     * Get the currency that owns the plan.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get all subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include visible plans.
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Check if the plan has a specific feature.
     */
    public function hasFeature(string $featureSlug): bool
    {
        // If in admin context, use optimized approach with no tenant filtering
        if (is_admin_context()) {
            // If planFeatures is already loaded, use the collection to avoid a new query
            if ($this->relationLoaded('planFeatures')) {
                return $this->planFeatures->contains(function ($planFeature) use ($featureSlug) {
                    return $planFeature->feature && $planFeature->feature->slug === $featureSlug;
                });
            }

            // For admin context, no tenant filtering needed
            return $this->planFeatures()
                ->whereHas('feature', function ($query) use ($featureSlug) {
                    $query->where('slug', $featureSlug);
                })
                ->exists();
        }

        // Regular tenant context behavior
        // If planFeatures is already loaded, use the collection to avoid a new query
        if ($this->relationLoaded('planFeatures')) {
            return $this->planFeatures->contains(function ($planFeature) use ($featureSlug) {
                return $planFeature->feature && $planFeature->feature->slug === $featureSlug;
            });
        }

        // Otherwise, perform a query
        return $this->planFeatures()
            ->whereHas('feature', function ($query) use ($featureSlug) {
                $query->where('slug', $featureSlug);
            })
            ->exists();
    }

    /**
     * Get the value of a specific feature.
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function getFeatureValue(string $featureSlug, $default = null)
    {
        // If in admin context, use optimized approach with no tenant filtering
        if (is_admin_context()) {
            // If planFeatures is already loaded, use the collection to avoid a new query
            if ($this->relationLoaded('planFeatures')) {
                $planFeature = $this->planFeatures->first(function ($planFeature) use ($featureSlug) {
                    return $planFeature->feature && $planFeature->feature->slug === $featureSlug;
                });

                return $planFeature ? $planFeature->value : $default;
            }

            // For admin context, no tenant filtering needed
            $planFeature = $this->planFeatures()
                ->whereHas('feature', function ($query) use ($featureSlug) {
                    $query->where('slug', $featureSlug);
                })
                ->first();

            return $planFeature ? $planFeature->value : $default;
        }

        // Regular tenant context behavior
        // If planFeatures is already loaded, use the collection to avoid a new query
        if ($this->relationLoaded('planFeatures')) {
            $planFeature = $this->planFeatures->first(function ($planFeature) use ($featureSlug) {
                return $planFeature->feature && $planFeature->feature->slug === $featureSlug;
            });

            return $planFeature ? $planFeature->value : $default;
        }

        // Otherwise, perform a query
        $planFeature = $this->planFeatures()
            ->whereHas('feature', function ($query) use ($featureSlug) {
                $query->where('slug', $featureSlug);
            })
            ->first();

        return $planFeature ? $planFeature->value : $default;
    }

    /**
     * Check if plan is a premium plan (not free)
     */
    public function isPremium(): bool
    {
        return ! $this->is_free && $this->price > 0;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free) {
            return 'Free';
        }

        return '$'.number_format($this->price, 2).'/'.$this->billing_period;
    }

    /**
     * Check if plan has trial period.
     */
    public function hasTrial(): bool
    {
        return ! is_null($this->trial_period) && $this->trial_period > 0;
    }

    public function isFree(): bool
    {
        return $this->price == 0 || $this->price == 0.00 || $this->is_free == 1;
    }

    /**
     * Check if both monthly and yearly plans are available (active).
     * This determines whether to show the billing period toggle.
     * Free plans are considered billing-period agnostic and don't affect toggle logic.
     */
    public static function hasBothBillingPeriods(): bool
    {
        $activePlans = static::where('is_active', true)->get();

        // Separate free and paid plans
        $freePlans = $activePlans->where('is_free', true);
        $paidPlans = $activePlans->where('is_free', false);

        // Check paid plans only for billing periods
        $hasMonthlyPaid = $paidPlans->where('billing_period', 'monthly')->isNotEmpty();
        $hasYearlyPaid = $paidPlans->where('billing_period', 'yearly')->isNotEmpty();

        // Show toggle only if there are both monthly and yearly PAID plans
        return $hasMonthlyPaid && $hasYearlyPaid;
    }

    /**
     * Check if only monthly plans are available (active).
     * Free plans are considered billing-period agnostic.
     */
    public static function hasOnlyMonthlyPlans(): bool
    {
        $activePlans = static::where('is_active', true)->get();

        // Separate free and paid plans
        $paidPlans = $activePlans->where('is_free', false);

        $hasMonthlyPaid = $paidPlans->where('billing_period', 'monthly')->isNotEmpty();
        $hasYearlyPaid = $paidPlans->where('billing_period', 'yearly')->isNotEmpty();

        return $hasMonthlyPaid && ! $hasYearlyPaid;
    }

    /**
     * Check if only yearly plans are available (active).
     * Free plans are considered billing-period agnostic.
     */
    public static function hasOnlyYearlyPlans(): bool
    {
        $activePlans = static::where('is_active', true)->get();

        // Separate free and paid plans
        $paidPlans = $activePlans->where('is_free', false);

        $hasMonthlyPaid = $paidPlans->where('billing_period', 'monthly')->isNotEmpty();
        $hasYearlyPaid = $paidPlans->where('billing_period', 'yearly')->isNotEmpty();

        return ! $hasMonthlyPaid && $hasYearlyPaid;
    }

    /**
     * Get the default billing period based on available active plans.
     */
    public static function getDefaultBillingPeriod(): string
    {
        if (static::hasOnlyYearlyPlans()) {
            return 'yearly';
        }

        return 'monthly'; // Default to monthly if both available or only monthly
    }
}
