<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class EmailTemplate
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $subject
 * @property string $slug
 * @property array|null $merge_fields_groups
 * @property string $message
 * @property int $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 * @property string|null $description
 * @property string|null $content
 * @property string|null $variables
 * @property int $is_system
 * @property string|null $category
 * @property string|null $type
 * @property int|null $layout_id
 * @property int $use_layout
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereLayoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereMergeFieldsGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereUseLayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereVariables($value)
 *
 * @mixin \Eloquent
 */
class EmailTemplate extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'merge_fields_groups' => 'json',
        'is_active' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'subject',
        'slug',
        'merge_fields_groups',
        'message',
        'is_active',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
