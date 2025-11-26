<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Traits\BelongsToTenant;

/**
 * Class ModelHasRole
 *
 * @property int $role_id
 * @property string $model_type
 * @property int $model_id
 * @property Role $role
 * @property int|null $tenant_id
 * @property-read \App\Models\Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelHasRole whereTenantId($value)
 *
 * @mixin \Eloquent
 */
class ModelHasRole extends BaseModel
{
    use BelongsToTenant;

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'role_id' => 'int',
        'model_id' => 'int',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
