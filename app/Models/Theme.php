<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $folder
 * @property bool $active
 * @property string|null $version
 * @property string|null $theme_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereThemeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereVersion($value)
 *
 * @mixin \Eloquent
 */
class Theme extends BaseModel
{
    use HasFactory;

    protected $table = 'themes'; // Specify the table name (optional if follows naming convention)

    protected $fillable = [
        'name',
        'folder',
        'active',
        'version',
        'theme_url',
        'payload',
        'theme_html',
        'theme_css',
        'core',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
