<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Campaign
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $rel_type
 * @property string|null $template_id
 * @property Carbon|null $scheduled_send_time
 * @property bool $send_now
 * @property string|null $header_params
 * @property string|null $body_params
 * @property string|null $footer_params
 * @property bool $pause_campaign
 * @property bool $select_all
 * @property bool $is_sent
 * @property int|null $sending_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $filename
 * @property string|null $rel_data
 * @property Tenant $tenant
 * @property Collection|CampaignDetail[] $campaign_details
 * @property-read int|null $campaign_details_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereBodyParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereFooterParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereHeaderParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign wherePauseCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereRelData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereRelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereScheduledSendTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereSelectAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereSendNow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereSendingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereUpdatedAt($value)
 *
 * @property-read \App\Models\Tenant\WhatsappTemplate|null $whatsappTemplate
 *
 * @mixin \Eloquent
 */
class Campaign extends BaseModel
{
    use BelongsToTenant, TracksFeatureUsage;

    protected $casts = [
        'tenant_id' => 'int',
        'scheduled_send_time' => 'datetime',
        'send_now' => 'bool',
        'pause_campaign' => 'bool',
        'select_all' => 'bool',
        'is_sent' => 'bool',
        'sending_count' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'rel_type',
        'template_id',
        'scheduled_send_time',
        'send_now',
        'header_params',
        'body_params',
        'footer_params',
        'pause_campaign',
        'select_all',
        'is_sent',
        'sending_count',
        'filename',
        'rel_data',
        'updated_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function campaign_details()
    {
        return $this->hasMany(CampaignDetail::class);
    }

    public function whatsappTemplate()
    {
        return $this->belongsTo(WhatsappTemplate::class, 'template_id', 'template_id')
            ->where('whatsapp_templates.tenant_id', tenant_id());
    }

    public function getFeatureSlug(): ?string
    {
        return 'campaigns';
    }
}
