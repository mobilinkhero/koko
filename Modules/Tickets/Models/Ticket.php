<?php

declare(strict_types=1);

namespace Modules\Tickets\Models;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tickets\Database\Factories\TicketFactory;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int|null $tenant_staff_id
 * @property string $subject
 * @property int $department_id
 * @property array|null $assignee_id
 * @property string $priority
 * @property string $status
 * @property string $ticket_id
 * @property bool $admin_viewed
 * @property bool $tenant_viewed
 * @property array<array-key, mixed>|null $attachments
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $assignedUser
 * @property-read \Modules\Tickets\Models\Department $department
 * @property-read string $ticket_number
 * @property-read string $title
 * @property-read \Modules\Tickets\Models\TicketReply|null $latestReply
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\TicketReply> $replies
 * @property-read int|null $replies_count
 * @property-read Tenant|null $tenant
 * @property-read User|null $tenantStaff
 *
 * @method static Builder<static>|Ticket answered()
 * @method static Builder<static>|Ticket byPriority(string $priority)
 * @method static Builder<static>|Ticket byStatus(string $status)
 * @method static Builder<static>|Ticket closed()
 * @method static \Modules\Tickets\Database\Factories\TicketFactory factory($count = null, $state = [])
 * @method static Builder<static>|Ticket highPriority()
 * @method static Builder<static>|Ticket newModelQuery()
 * @method static Builder<static>|Ticket newQuery()
 * @method static Builder<static>|Ticket open()
 * @method static Builder<static>|Ticket pending()
 * @method static Builder<static>|Ticket query()
 * @method static Builder<static>|Ticket whereAdminViewed($value)
 * @method static Builder<static>|Ticket whereAssigneeId($value)
 * @method static Builder<static>|Ticket whereAttachments($value)
 * @method static Builder<static>|Ticket whereBody($value)
 * @method static Builder<static>|Ticket whereClosedAt($value)
 * @method static Builder<static>|Ticket whereCreatedAt($value)
 * @method static Builder<static>|Ticket whereDepartmentId($value)
 * @method static Builder<static>|Ticket whereId($value)
 * @method static Builder<static>|Ticket wherePriority($value)
 * @method static Builder<static>|Ticket whereStatus($value)
 * @method static Builder<static>|Ticket whereSubject($value)
 * @method static Builder<static>|Ticket whereTenantId($value)
 * @method static Builder<static>|Ticket whereTenantStaffId($value)
 * @method static Builder<static>|Ticket whereTenantViewed($value)
 * @method static Builder<static>|Ticket whereTicketId($value)
 * @method static Builder<static>|Ticket whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Ticket extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'tenant_staff_id',
        'assignee_id',
        'subject',
        'department_id',
        'priority',
        'status',
        'ticket_id',
        'admin_viewed',
        'tenant_viewed',
        'body',
        'attachments',
        'ticket_number',
        'title',
        'last_reply_at',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attachments' => 'array',
        'assignee_id' => 'array',
        'admin_viewed' => 'boolean',
        'tenant_viewed' => 'boolean',
        'closed_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    /**
     * Get the department that owns the ticket.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the tenant that owns the ticket.
     * The tenant is the main client/company in the system.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get the staff member (either admin or regular staff) from the tenant organization
     * who created this ticket. All tenant users are stored in users table with user_type='tenant'
     */
    public function tenantStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_staff_id')
            ->where('user_type', 'tenant');
    }

    /**
     * Get the admin user assigned to handle this ticket
     *
     * @deprecated Use assignedUsers() instead
     */
    public function assignedUser(): BelongsTo
    {
        // For backwards compatibility, return the first assignee
        $firstAssigneeId = $this->assignee_id[0] ?? null;

        return $this->belongsTo(User::class, 'id')
            ->where('user_type', 'admin')
            ->where('id', $firstAssigneeId);
    }

    /**
     * Get the replies for the ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    /**
     * Get the latest reply for the ticket.
     */
    public function latestReply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class, 'id', 'ticket_id')->latest();
    }

    /**
     * Get the title attribute (alias for subject).
     */
    public function getTitleAttribute(): string
    {
        return $this->subject;
    }

    /**
     * Generate a unique ticket ID.
     */
    public static function generateTicketId(): string
    {
        // Get the latest ticket_id from the database
        $latest = self::orderByDesc('id')->first();

        $nextNumber = 1;

        if ($latest && preg_match('/TKT-(\d+)/', $latest->ticket_id, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return 'TKT-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the ticket number
     */
    public function getTicketNumberAttribute(): string
    {
        // Return ticket_number if it exists, fallback to ticket_id
        return $this->attributes['ticket_number'] ?? $this->ticket_id;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Open the ticket.
     */
    public function open(): bool
    {
        return $this->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    /**
     * Close the ticket.
     */
    public function close(): bool
    {
        return $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include answered tickets.
     */
    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('status', 'answered');
    }

    /**
     * Scope a query to only include closed tickets.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope a query to only include high priority tickets.
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Get the admin users assigned to handle this ticket
     */
    public function assignedUsers()
    {
        $ids = $this->assignee_id;
        if (empty($ids)) {
            return $this->hasMany(User::class, 'id')->whereNull('id');
        }

        // Ensure we have a flat array of IDs
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?? [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));

        return $this->hasMany(User::class, 'id')
            ->whereIn('id', $ids)
            ->where('user_type', 'admin');
    }

    public function getAssigneeIdAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value ?? [];
    }

    public function setAssigneeIdAttribute($value)
    {
        $this->attributes['assignee_id'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TicketFactory::new();
    }
}
