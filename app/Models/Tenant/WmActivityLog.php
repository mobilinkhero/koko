<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class WmActivityLog
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string|null $phone_number_id
 * @property string|null $access_token
 * @property string|null $business_account_id
 * @property string $response_code
 * @property int|null $client_id
 * @property string|null $response_data
 * @property string|null $category
 * @property int|null $category_id
 * @property string|null $rel_type
 * @property int|null $rel_id
 * @property string|null $category_params
 * @property string|null $raw_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereBusinessAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereCategoryParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog wherePhoneNumberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereRawData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereRelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereRelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WmActivityLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class WmActivityLog extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'client_id' => 'int',
        'category_id' => 'int',
        'rel_id' => 'int',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $fillable = [
        'tenant_id',
        'phone_number_id',
        'access_token',
        'business_account_id',
        'response_code',
        'client_id',
        'response_data',
        'category',
        'category_id',
        'rel_type',
        'rel_id',
        'category_params',
        'raw_data',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
