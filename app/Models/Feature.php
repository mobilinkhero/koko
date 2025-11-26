<?php

namespace App\Models;

use App\Models\Tenant\FeatureUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property string|null $icon
 * @property int $display_order
 * @property bool $default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeatureLimit> $limits
 * @property-read int|null $limits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Plan> $plans
 * @property-read int|null $plans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FeatureUsage> $usages
 * @property-read int|null $usages_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature boolean()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature limit()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feature whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Feature extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'icon',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feature) {
            do_action('feature.before_create', $feature);
        });

        static::created(function ($feature) {
            do_action('feature.after_create', $feature);
        });

        static::updating(function ($feature) {
            do_action('feature.before_update', $feature);
        });

        static::updated(function ($feature) {
            do_action('feature.after_update', $feature);
        });

        static::deleting(function ($feature) {
            do_action('feature.before_delete', $feature);
        });

        static::deleted(function ($feature) {
            do_action('feature.after_delete', $feature);
        });
    }

    /**
     * Get the plans for the feature.
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot('value', 'resettable_period', 'resettable_interval')
            ->withTimestamps();
    }

    /**
     * Get the feature usages for the feature.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(FeatureUsage::class, 'feature_slug', 'slug');
    }

    /**
     * Get the feature limits for the feature.
     */
    public function limits(): HasMany
    {
        return $this->hasMany(FeatureLimit::class);
    }

    /**
     * Scope a query to only include boolean-type features.
     */
    public function scopeBoolean($query)
    {
        return $query->where('type', 'boolean');
    }

    /**
     * Scope a query to only include limit-type features.
     */
    public function scopeLimit($query)
    {
        return $query->where('type', 'limit');
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Check if the feature is a boolean type.
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * Check if the feature is a limit type.
     */
    public function isLimit(): bool
    {
        return $this->type === 'limit';
    }
}
