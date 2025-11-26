<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use Carbon\Carbon;

/**
 * Class CampaignDetail
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $campaign_id
 * @property int|null $rel_id
 * @property string $rel_type
 * @property string|null $header_message
 * @property string|null $body_message
 * @property string|null $footer_message
 * @property int|null $status
 * @property string|null $response_message
 * @property string|null $whatsapp_id
 * @property string|null $message_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Campaign $campaign
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereBodyMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereFooterMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereHeaderMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereMessageStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereRelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereRelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereResponseMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignDetail whereWhatsappId($value)
 *
 * @mixin \Eloquent
 */
class CampaignDetail extends BaseModel
{
    protected $casts = [
        'tenant_id' => 'int',
        'campaign_id' => 'int',
        'rel_id' => 'int',
        'status' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'rel_id',
        'rel_type',
        'header_message',
        'body_message',
        'footer_message',
        'status',
        'response_message',
        'whatsapp_id',
        'message_status',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
