<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Carbon\Carbon;

/**
 * Class TemplateBot
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $rel_type
 * @property int|null $template_id
 * @property string|null $header_params
 * @property string|null $body_params
 * @property string|null $footer_params
 * @property string|null $filename
 * @property string|null $trigger
 * @property int $reply_type
 * @property int $is_bot_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $sending_count
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereBodyParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereFooterParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereHeaderParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereIsBotActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereRelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereReplyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereSendingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereTrigger($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateBot whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TemplateBot extends BaseModel
{
    use BelongsToTenant, TracksFeatureUsage;

    protected $casts = [
        'tenant_id' => 'int',
        'template_id' => 'int',
        'reply_type' => 'int',
        'is_bot_active' => 'int',
        'sending_count' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'rel_type',
        'template_id',
        'header_params',
        'body_params',
        'footer_params',
        'filename',
        'trigger',
        'reply_type',
        'is_bot_active',
        'sending_count',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFeatureSlug(): ?string
    {
        return 'template_bots';
    }

    public static function getTemplateBotsByRelType($relType, $message, $tenant_id, $replyType = null)
    {
        // Start with the query on TemplateBot model
        $query = TemplateBot::join('whatsapp_templates', 'template_bots.template_id', '=', 'whatsapp_templates.template_id')
            ->select('template_bots.id AS template_bot_id', 'template_bots.*', 'whatsapp_templates.*')
            ->where('template_bots.rel_type', $relType)
            ->where('template_bots.is_bot_active', 1)->where('template_bots.tenant_id', $tenant_id);

        // Filter by reply type (only for data filtering)
        if (! is_null($replyType)) {
            $query->where('reply_type', $replyType);
        }

        // Always apply message matching (regardless of reply type)
        if (! empty($message) && $replyType != 4) {
            $messageWords = explode(' ', $message);

            $query->where(function ($q) use ($message, $messageWords) {
                // 1. Exact full sentence match
                $q->orWhere('trigger', $message);

                // 2. Partial sentence match
                $q->orWhere('trigger', 'LIKE', '%'.$message.'%');

                // 3. Word-based match
                foreach ($messageWords as $word) {
                    $cleanWord = str_replace(["'", '"'], '', $word);
                    $q->orWhere('trigger', 'LIKE', '%'.$cleanWord.'%');
                }
            });
        }

        return $query->get()->toArray();
    }
}
