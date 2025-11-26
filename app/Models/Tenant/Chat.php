<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class Chat
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $receiver_id
 * @property string|null $last_message
 * @property Carbon|null $last_msg_time
 * @property string|null $wa_no
 * @property string|null $wa_no_id
 * @property Carbon $time_sent
 * @property string|null $type
 * @property string|null $type_id
 * @property string|null $agent
 * @property bool $is_ai_chat
 * @property array|null $ai_message_json
 * @property bool|null $is_bots_stoped
 * @property Carbon|null $bot_stoped_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tenant\ChatMessage> $messages
 * @property-read int|null $messages_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat query()
 *
 * @mixin \Eloquent
 */
class Chat extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'last_msg_time' => 'datetime',
        'time_sent' => 'datetime',
        'is_ai_chat' => 'bool',
        'ai_message_json' => 'json',
        'is_bots_stoped' => 'bool',
        'bot_stoped_time' => 'datetime',
    ];

    protected $fillable = [
        'tenant_id',
        'interaction_id',
        'name',
        'receiver_id',
        'last_message',
        'last_msg_time',
        'wa_no',
        'wa_no_id',
        'time_sent',
        'type',
        'type_id',
        'agent',
        'is_ai_chat',
        'ai_message_json',
        'is_bots_stoped',
        'bot_stoped_time',
        'created_at',
        'updated_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function fromTenant(string $subdomain)
    {
        return (new static)->setTable($subdomain.'_chats');
    }

    public function messages()
    {
        $subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);

        return $this->hasMany(ChatMessage::class, 'interaction_id')
            ->from($subdomain.'_chat_messages')
            ->where('tenant_id', $this->tenant_id);
    }
}
