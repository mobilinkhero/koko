<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $description
 * @property int|null $tenant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 * @property Collection|ModelHasRole[] $model_has_roles
 * @property Collection|Permission[] $permissions
 * @property-read int|null $model_has_roles_count
 * @property-read int|null $permissions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Role extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
    ];

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function model_has_roles()
    {
        return $this->hasMany(ModelHasRole::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }
}
