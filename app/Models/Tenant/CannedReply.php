<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Carbon\Carbon;

/**
 * Class CannedReply
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $title
 * @property string $description
 * @property bool $is_public
 * @property int $added_from
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereAddedFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CannedReply whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CannedReply extends BaseModel
{
    use BelongsToTenant,TracksFeatureUsage;

    protected $casts = [
        'tenant_id' => 'int',
        'is_public' => 'bool',
        'added_from' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'is_public',
        'added_from',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFeatureSlug(): ?string
    {
        return 'canned_replies';
    }
}
