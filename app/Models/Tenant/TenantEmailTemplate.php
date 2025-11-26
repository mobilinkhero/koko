<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Corbital\LaravelEmails\Models\EmailLayout;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $subject
 * @property string|null $content
 * @property array<array-key, mixed>|null $merge_fields_groups
 * @property bool $is_active
 * @property bool $is_system
 * @property string|null $category
 * @property string|null $type
 * @property int|null $layout_id
 * @property bool $use_layout
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereLayoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereMergeFieldsGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantEmailTemplate whereUseLayout($value)
 *
 * @property-read EmailLayout|null $layout
 *
 * @mixin \Eloquent
 */
class TenantEmailTemplate extends BaseModel
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'subject',
        'content',
        'variables',
        'merge_fields_groups',
        'is_active',
        'is_system',
        'category',
        'type',
        'layout_id',
        'use_layout',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'merge_fields_groups' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'use_layout' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function layout()
    {
        return $this->belongsTo(EmailLayout::class, 'layout_id');
    }
}
