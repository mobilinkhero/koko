<?php

declare(strict_types=1);

namespace Modules\Tickets\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tickets\Database\Factories\DepartmentTranslationFactory;

/**
 * @property int $id
 * @property int $department_id
 * @property string $locale
 * @property string $name
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \Modules\Tickets\Models\Department $department
 *
 * @method static \Modules\Tickets\Database\Factories\DepartmentTranslationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation forDepartment(int $departmentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation forLocale(string $locale)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DepartmentTranslation extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'department_translations';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'department_id',
        'locale',
        'name',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'department_id' => 'integer',
        'locale' => 'string',
        'name' => 'string',
    ];

    /**
     * Get the department that owns this translation.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope a query to only include translations for a specific locale.
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope a query to only include translations for a specific department.
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DepartmentTranslationFactory
    {
        return DepartmentTranslationFactory::new();
    }
}
