<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Traits\BelongsToTenant;

/**
 * Class RoleHasPermission
 *
 * @property int $permission_id
 * @property int $role_id
 * @property Permission $permission
 * @property Role $role
 * @property-read \App\Models\Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleHasPermission forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleHasPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleHasPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleHasPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleHasPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleHasPermission whereRoleId($value)
 *
 * @mixin \Eloquent
 */
class RoleHasPermission extends BaseModel
{
    use BelongsToTenant;

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'int',
        'role_id' => 'int',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
