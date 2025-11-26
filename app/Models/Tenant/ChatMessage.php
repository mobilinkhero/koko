<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class ChatMessage
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $interaction_id
 * @property string $sender_id
 * @property string|null $url
 * @property string $message
 * @property string|null $status
 * @property string|null $status_message
 * @property Carbon $time_sent
 * @property string|null $message_id
 * @property string|null $staff_id
 * @property string|null $type
 * @property bool $is_read
 * @property string|null $ref_message_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 * @property-read \App\Models\Tenant\Chat|null $chat
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage query()
 *
 * @mixin \Eloquent
 */
class ChatMessage extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'interaction_id' => 'int',
        'time_sent' => 'datetime',
        'is_read' => 'bool',
    ];

    protected $fillable = [
        'tenant_id',
        'interaction_id',
        'sender_id',
        'url',
        'message',
        'status',
        'status_message',
        'time_sent',
        'message_id',
        'staff_id',
        'type',
        'is_read',
        'ref_message_id',
        'updated_at',
        'created_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function fromTenant(string $subdomain)
    {
        return (new static)->setTable($subdomain.'_chat_messages');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'interaction_id');
    }
}
