<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class PusherNotification
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property int $isread
 * @property bool $isread_inline
 * @property Carbon $date
 * @property string $description
 * @property int $fromuserid
 * @property int $fromclientid
 * @property string $from_fullname
 * @property int $touserid
 * @property int|null $fromcompany
 * @property string|null $link
 * @property string|null $additional_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereAdditionalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereFromFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereFromclientid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereFromcompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereFromuserid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereIsread($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereIsreadInline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereTouserid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PusherNotification whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PusherNotification extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'isread' => 'int',
        'isread_inline' => 'bool',
        'date' => 'datetime',
        'fromuserid' => 'int',
        'fromclientid' => 'int',
        'touserid' => 'int',
        'fromcompany' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'isread',
        'isread_inline',
        'date',
        'description',
        'fromuserid',
        'fromclientid',
        'from_fullname',
        'touserid',
        'fromcompany',
        'link',
        'additional_data',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
