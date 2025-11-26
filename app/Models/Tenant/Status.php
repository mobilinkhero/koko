<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Status
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $color
 * @property bool $isdefault
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 * @property Collection|Contact[] $contacts
 * @property-read int|null $contacts_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereIsdefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Status extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'isdefault' => 'bool',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'isdefault',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($status) {
            do_action('status.before_create', $status);
        });

        static::created(function ($status) {
            do_action('status.after_create', $status);
        });

        static::updating(function ($status) {
            do_action('status.before_update', $status);
        });

        static::updated(function ($status) {
            do_action('status.after_update', $status);
        });

        static::deleting(function ($status) {
            do_action('status.before_delete', $status);
        });

        static::deleted(function ($status) {
            do_action('status.after_delete', $status);
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
