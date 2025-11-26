<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $firstname User first name
 * @property string $lastname User last name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property int|null $tenant_id Tenant ID
 * @property bool $is_admin Whether user is a super admin
 * @property int|null $role_id
 * @property string|null $avatar User profile image
 * @property string|null $phone User phone number
 * @property string|null $default_language User default language
 * @property int|null $country_id
 * @property string|null $address
 * @property string $user_type User Type
 * @property int $active Whether user is active
 * @property bool $send_welcome_mail Whether send welcome mail.
 * @property \Illuminate\Support\Carbon|null $last_login_at Last successful login
 * @property \Illuminate\Support\Carbon|null $last_password_change Last password changed
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\Ticket> $assignedTickets
 * @property-read int|null $assigned_tickets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\Department> $departments
 * @property-read int|null $departments_count
 * @property-read string $name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDefaultLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastPasswordChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSendWelcomeMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use BelongsToTenant;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use TracksFeatureUsage;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'tenant_id',
        'is_admin',
        'avatar',
        'phone',
        'country_id',
        'address',
        'last_password_change',
        'send_welcome_mail',
        'active',
        'user_type',
        'last_login_at',
        'role_id',
        'default_language',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'tenant_id' => 'int',
        'email_verified_at' => 'datetime',
        'is_admin' => 'bool',
        'send_welcome_mail' => 'bool',
        'status' => 'bool',
        'last_login_at' => 'datetime',
        'last_password_change' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the current team ID for permission checks.
     * This method is required by Spatie's permission package when using teams feature.
     */
    public function getPermissionTeamId(): ?int
    {
        // For super admin/admin users, return null (global permissions)
        if ($this->user_type === 'admin' || $this->is_admin) {
            return null;
        }

        // For tenant users, return their tenant_id
        return $this->tenant_id;
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * Get the tickets created by this tenant user
     */
    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Tickets\Models\Ticket::class, 'tenant_staff_id');
    }

    /**
     * Get the tickets assigned to this admin user
     */
    public function assignedTickets(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \Modules\Tickets\Models\Ticket::class,
            \Modules\Tickets\Models\TicketAssignment::class,
            'user_id',
            'id',
            'id',
            'ticket_id'
        );
    }

    /**
     * Get the departments this user is assigned to.
     */
    public function departments()
    {
        return \Modules\Tickets\Models\Department::whereJsonContains('assignee_id', $this->id);
    }

    /**
     * Get the feature slug for tracking user count.
     * Only tenant staff members count towards the staff limit.
     */
    public function getFeatureSlug(): ?string
    {
        // Only track tenant staff members, not admins or system users
        if ($this->tenant_id && ! $this->is_admin) {
            return 'staff';
        }

        return null;
    }

    public function getCreatedAtAttribute($value)
    {
        $timezone = $this->getTimezone();

        return \Carbon\Carbon::parse($value)->setTimezone($timezone);
    }

    public function getUpdatedAtAttribute($value)
    {
        $timezone = $this->getTimezone();

        return \Carbon\Carbon::parse($value)->setTimezone($timezone);
    }

    public function getTimezone()
    {
        if (Tenant::checkCurrent()) {
            $systemSettings = tenant_settings_by_group('system');

            return $systemSettings['timezone'] ?? config('app.timezone');
        } else {
            $systemSettings = get_batch_settings(['system.timezone']);

            return $systemSettings['system.timezone'] ?? config('app.timezone');
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            do_action('user.before_create', $user);
        });

        static::created(function ($user) {
            do_action('user.after_create', $user);
        });

        static::updating(function ($user) {
            do_action('user.before_update', $user);

            // Check for status change
            if ($user->isDirty('active')) {
                $oldStatus = $user->getOriginal('active');
                $newStatus = $user->active;

                if ($oldStatus && ! $newStatus) {
                    do_action('user.before_deactivate', $user);
                } elseif (! $oldStatus && $newStatus) {
                    do_action('user.before_activate', $user);
                }
            }
        });

        static::updated(function ($user) {
            do_action('user.after_update', $user);

            // Check for status change
            if ($user->wasChanged('active')) {
                $oldStatus = $user->getOriginal('active');
                $newStatus = $user->active;

                if ($oldStatus && ! $newStatus) {
                    do_action('user.after_deactivate', $user);
                } elseif (! $oldStatus && $newStatus) {
                    do_action('user.after_activate', $user);
                }
            }

            // Check for password change
            if ($user->wasChanged('password')) {
                do_action('user.password_changed', $user);
            }

            // Check for role change
            if ($user->wasChanged('role_id')) {
                do_action('user.role_changed', $user);
            }
        });

        static::deleting(function ($user) {
            do_action('user.before_delete', $user);
        });

        static::deleted(function ($user) {
            do_action('user.after_delete', $user);
        });
    }
}
