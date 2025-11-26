<?php

namespace App\Models;

use Corbital\LaravelEmails\Models\EmailLayout;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $subject
 * @property string|null $content
 * @property array<array-key, mixed>|null $variables
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
 *
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
 * @property-read EmailLayout|null $layout
 *
 * @mixin \Eloquent
 */
class EmailTemplate extends BaseModel
{
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

    public function layout()
    {
        return $this->belongsTo(EmailLayout::class, 'layout_id');
    }
}
