<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Traits\BelongsToTenant;

/**
 * Class ModelHasPermission
 *
 * @property int $permission_id
 * @property string $model_type
 * @property int $model_id
 * @property Permission $permission
 * @property int|null $tenant_id
 * @property-read \App\Models\Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasPermission whereTenantId($value)
 *
 * @mixin \Eloquent
 */
class ModelHasPermission extends BaseModel
{
    use BelongsToTenant;

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'int',
        'model_id' => 'int',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
