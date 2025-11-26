<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class TenantSetting
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 * @property string $group
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantSetting whereValue($value)
 *
 * @mixin \Eloquent
 */
class TenantSetting extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
