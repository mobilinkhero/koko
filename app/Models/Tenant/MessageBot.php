<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Carbon\Carbon;

/**
 * Class MessageBot
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $rel_type
 * @property string $reply_text
 * @property int $reply_type
 * @property string|null $trigger
 * @property string|null $bot_header
 * @property string|null $bot_footer
 * @property string|null $button1
 * @property string|null $button1_id
 * @property string|null $button2
 * @property string|null $button2_id
 * @property string|null $button3
 * @property string|null $button3_id
 * @property string|null $button_name
 * @property string|null $button_url
 * @property int $addedfrom
 * @property bool $is_bot_active
 * @property int $sending_count
 * @property string|null $filename
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereAddedfrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereBotFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereBotHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButton1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButton1Id($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButton2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButton2Id($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButton3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButton3Id($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButtonName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereButtonUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereIsBotActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereRelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereReplyText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereReplyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereSendingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereTrigger($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageBot whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MessageBot extends BaseModel
{
    use BelongsToTenant, TracksFeatureUsage;

    protected $casts = [
        'tenant_id' => 'int',
        'reply_type' => 'int',
        'addedfrom' => 'int',
        'is_bot_active' => 'bool',
        'sending_count' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'rel_type',
        'reply_text',
        'reply_type',
        'trigger',
        'bot_header',
        'bot_footer',
        'button1',
        'button1_id',
        'button2',
        'button2_id',
        'button3',
        'button3_id',
        'button_name',
        'button_url',
        'addedfrom',
        'is_bot_active',
        'sending_count',
        'filename',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFeatureSlug(): ?string
    {
        return 'message_bots';
    }

    public static function getMessageBotsByRelType($relType, $message, $tenant_id, $replyType = null)
    {
        $query = self::where('rel_type', $relType)
            ->where('is_bot_active', 1)->where('tenant_id', $tenant_id);

        if (! is_null($replyType)) {
            $query->where('reply_type', $replyType);
        }

        if (! empty($message) && $replyType != 4) {
            $cleanMessage = str_replace(["'", '"'], '', $message);
            $messageWords = explode(' ', $cleanMessage);

            $query->where(function ($q) use ($cleanMessage, $messageWords) {
                // 1. Exact full sentence match
                $q->orWhere('trigger', $cleanMessage);

                // 2. Partial sentence match
                $q->orWhere('trigger', 'LIKE', '%'.$cleanMessage.'%');

                // 3. Word-based match
                foreach ($messageWords as $word) {
                    $cleanWord = trim($word);
                    if (! empty($cleanWord)) {
                        $q->orWhere('trigger', 'LIKE', '%'.$cleanWord.'%');
                    }
                }
            });
        }

        return $query->get()->toArray();
    }
}
