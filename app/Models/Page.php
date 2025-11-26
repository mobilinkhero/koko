<?php

namespace App\Models;

use App\Observers\PageObserver;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property bool|null $show_in_menu
 * @property bool|null $status
 * @property int|null $parent_id
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Page> $children
 * @property-read int|null $children_count
 * @property-read Page|null $parent
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page inMenu()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereShowInMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Page extends BaseModel
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'show_in_menu',
        'status',
        'parent_id',
        'order',
    ];

    protected $casts = [
        'show_in_menu' => 'boolean',
        'status' => 'boolean',
        'order' => 'integer',
    ];

    public static function observed(): array
    {
        return [
            PageObserver::class,
        ];
    }

    /**
     * Get the parent page
     */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages
     */
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    /**
     * Get active pages
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get menu items
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Generate a unique slug
     */
    public static function generateUniqueSlug($title, $id = null)
    {
        $slug = \Str::slug($title);
        $count = 1;

        while (static::where('slug', $slug)
            ->when($id, fn ($query) => $query->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = \Str::slug($title).'-'.$count++;
        }

        return $slug;
    }

    /**
     * Get full path
     */
    public function getFullPath()
    {
        // Use a static cache to avoid multiple database queries
        static $pathCache = [];

        $cacheKey = $this->id;
        if (isset($pathCache[$cacheKey])) {
            return $pathCache[$cacheKey];
        }

        $segments = collect([]);
        $currentPage = $this;

        // Build path from current page up through parents
        while ($currentPage !== null) {
            $segments->prepend($currentPage->slug);

            // Check if parent relationship is already loaded to avoid lazy loading
            if ($currentPage->relationLoaded('parent')) {
                $currentPage = $currentPage->parent;
            } elseif ($currentPage->parent_id) {
                // Load parent without triggering lazy loading warning
                $currentPage = static::select(['id', 'slug', 'parent_id'])
                    ->find($currentPage->parent_id);
            } else {
                $currentPage = null;
            }
        }

        $path = $segments->join('/');
        $pathCache[$cacheKey] = $path;

        return $path;
    }

    /**
     * Get nested array of pages for navigation
     */
    public static function getMenuTree()
    {
        return static::active()
            ->inMenu()
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->active()->inMenu()->orderBy('order');
            }])
            ->orderBy('order')
            ->get();
    }

    /**
     * Get full path for a page with all required parent data to avoid lazy loading
     */
    public function getFullPathWithParents()
    {
        $path = $this->slug;
        $currentParentId = $this->parent_id;

        while ($currentParentId) {
            $parent = static::select(['id', 'slug', 'parent_id'])
                ->find($currentParentId);

            if ($parent) {
                $path = $parent->slug.'/'.$path;
                $currentParentId = $parent->parent_id;
            } else {
                break;
            }
        }

        return $path;
    }
}
