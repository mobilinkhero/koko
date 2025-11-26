<?php

declare(strict_types=1);

namespace Modules\Tickets\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tickets\Database\Factories\DepartmentFactory;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $status
 * @property int|null $assigned_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $translated_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\DepartmentTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read User|null $users
 *
 * @method static Builder<static>|Department active()
 * @method static \Modules\Tickets\Database\Factories\DepartmentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Department inactive()
 * @method static Builder<static>|Department newModelQuery()
 * @method static Builder<static>|Department newQuery()
 * @method static Builder<static>|Department query()
 * @method static Builder<static>|Department whereAssignedId($value)
 * @method static Builder<static>|Department whereCreatedAt($value)
 * @method static Builder<static>|Department whereDescription($value)
 * @method static Builder<static>|Department whereId($value)
 * @method static Builder<static>|Department whereName($value)
 * @method static Builder<static>|Department whereStatus($value)
 * @method static Builder<static>|Department whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Department extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'assignee_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'assignee_id' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DepartmentFactory
    {
        return DepartmentFactory::new();
    }

    /**
     * Get tickets assigned to this department
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get translations for this department
     */
    public function translations(): HasMany
    {
        return $this->hasMany(DepartmentTranslation::class);
    }

    /**
     * Scope to get only active departments
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get only inactive departments
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    /**
     * Get translated name for current locale
     */
    public function getTranslatedNameAttribute(): string
    {
        $locale = app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->name ?? $this->name;
    }

    /**
     * Get a collection of admin users assigned to this department
     */
    public function assignedUsers()
    {
        $assigneeIds = is_array($this->assignee_id) ? $this->assignee_id : [];

        return User::whereIn('id', $assigneeIds)
            ->where('user_type', 'admin')
            ->get();
    }
}
