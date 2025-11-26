<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Source
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 * @property Collection|Contact[] $contacts
 * @property-read int|null $contacts_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Source extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($source) {
            do_action('source.before_create', $source);
        });

        static::created(function ($source) {
            do_action('source.after_create', $source);
        });

        static::updating(function ($source) {
            do_action('source.before_update', $source);
        });

        static::updated(function ($source) {
            do_action('source.after_update', $source);
        });

        static::deleting(function ($source) {
            do_action('source.before_delete', $source);
        });

        static::deleted(function ($source) {
            do_action('source.after_delete', $source);
        });

        do_action('model.booted', static::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
