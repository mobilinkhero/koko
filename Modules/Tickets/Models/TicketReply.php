<?php

declare(strict_types=1);

namespace Modules\Tickets\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tickets\Database\Factories\TicketReplyFactory;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int|null $user_id
 * @property string $user_type
 * @property array<array-key, mixed>|null $attachments
 * @property bool $viewed
 * @property int|null $send_notification
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $reply_from
 * @property-read \Modules\Tickets\Models\Ticket $ticket
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply byUserType(string $userType)
 * @method static \Modules\Tickets\Database\Factories\TicketReplyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketReply whereViewed($value)
 *
 * @mixin \Eloquent
 */
class TicketReply extends BaseModel
{
    use HasFactory;

    // Time window in which replies can be deleted (in minutes)
    const DELETION_WINDOW = 10;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'user_type',
        'attachments',
        'viewed',
        'content',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attachments' => 'array',
        'viewed' => 'boolean',
    ];

    /**
     * Get the ticket that owns the reply.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user that owns the reply.
     * This could be either a tenant user or system admin
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    /**
     * Scope to filter by user type.
     */
    public function scopeByUserType($query, string $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope to filter unread replies.
     */
    public function scopeUnread($query)
    {
        return $query->where('viewed', false);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TicketReplyFactory
    {
        return TicketReplyFactory::new();
    }

    /**
     * Check if the reply is from a tenant user
     */
    public function isTenantReply(): bool
    {
        return $this->user_type === 'tenant';
    }

    /**
     * Check if the reply is from a system admin
     */
    public function isAdminReply(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if the reply is from the system
     */
    public function isSystemReply(): bool
    {
        return $this->user_type === 'system';
    }

    /**
     * Get the formatted name of who made the reply
     */
    public function getReplyFromAttribute(): string
    {
        if ($this->isSystemReply()) {
            return 'System';
        }

        if (! $this->user) {
            return 'Unknown';
        }

        $name = $this->user->firstname.' '.$this->user->lastname;

        if ($this->isAdminReply()) {
            return $name.' (Admin)';
        }

        if ($this->isTenantReply()) {
            $role = $this->user->is_admin ? 'Tenant Admin' : 'Tenant Staff';

            return $name.' ('.$role.')';
        }

        return $name;
    }

    /**
     * Check if the reply can be deleted by the given user
     */
    public function canBeDeletedBy(User $user): bool
    {
        // If user is not admin or doesn't have admin role, they can't delete anything
        if (! $user->is_admin || $user->user_type !== 'admin') {
            return false;
        }

        // Only admin replies can be deleted
        if ($this->user_type !== 'admin') {
            return false;
        }

        // Check if we're still within the deletion window
        $createdAt = $this->created_at;
        $now = now();
        $minutesSinceCreation = $createdAt->diffInMinutes($now);

        return $minutesSinceCreation <= self::DELETION_WINDOW;
    }
}
