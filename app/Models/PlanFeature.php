<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property int|null $feature_id
 * @property string|null $name Feature display name
 * @property string|null $slug URL-friendly feature name
 * @property string|null $description Feature description
 * @property string $value Feature value or limit
 * @property int|null $resettable_period Period after which usage resets
 * @property string|null $resettable_interval Interval for reset (day, month, year)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Feature|null $feature
 * @property-read string|int|bool $formatted_value
 * @property-read \App\Models\Plan $plan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereResettableInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereResettablePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanFeature whereValue($value)
 *
 * @mixin \Eloquent
 */
class PlanFeature extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'plan_id',
        'feature_id',
        'name',
        'slug',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
    ];

    protected $casts = [
        'plan_id' => 'integer',
        'feature_id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
    ];

    /**
     * Get the plan that owns the feature.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the feature.
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Get the formatted value.
     *
     * @return string|int|bool
     */
    public function getFormattedValueAttribute()
    {
        if (! $this->feature) {
            return $this->value;
        }

        if ($this->feature->type === 'boolean') {
            return (bool) $this->value;
        }

        if ($this->feature->type === 'limit') {
            if ($this->value == -1) {
                return 'Unlimited';
            }

            return (int) $this->value;
        }

        return $this->value;
    }
}
