<?php

namespace Corbital\LaravelEmails\Models;

use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'subject',
        'content',
        'html_template',
        'text_template',
        'variables',
        'is_active',
        'is_system',
        'category',
        'layout_id',
        'use_layout',
        'created_by',
        'updated_by',
        // Actual database fields
        'message',
        'merge_fields_groups',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'use_layout' => 'boolean',
        'merge_fields_groups' => 'array',
    ];

    public static function fromTable(string $table)
    {
        return (new static)->setTable($table);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($template) {
            // Auto-generate slug from name if not provided
            if (empty($template->slug) && ! empty($template->name)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Get the layout that this template uses.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(EmailLayout::class);
    }

    /**
     * This method was previously used for logs relationship but has been removed.
     */

    /**
     * Scope a query to only include active templates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system templates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to only include custom templates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Render the HTML content with the given data.
     *
     * @return string
     */
    public function renderContent(array $data = [])
    {
        // Get the template content - use message field for compatibility
        $content = $this->message ?? $this->html_template ?? $this->content ?? '';

        if (empty($content)) {
            return '';
        }

        $mergeFieldsService = app(MergeFieldsService::class);
        $content = $mergeFieldsService->parseTemplates($this->merge_fields_groups, $content, $data);

        // Apply layout if the template uses one
        if ($this->use_layout && ($this->layout || $this->getDefaultLayout())) {
            $layout = $this->layout ?? $this->getDefaultLayout();

            return $layout->render($content, $data);
        }

        return $content;
    }

    /**
     * Get the default layout.
     *
     * @return \Corbital\LaravelEmails\Models\EmailLayout|null
     */
    protected function getDefaultLayout()
    {
        return EmailLayout::getDefault();
    }

    /**
     * Render the subject with the given data.
     */
    public function renderSubject(array $data = []): string
    {
        if (empty($this->subject)) {
            return '';
        }

        $mergeFieldsService = app(MergeFieldsService::class);
        $content = $mergeFieldsService->parseTemplates($this->merge_fields_groups, $this->subject, $data);

        return $content;
    }

    /**
     * Render a template string with the given data.
     */
    protected function renderTemplate(string $template, array $data = []): string
    {
        // Add default variables
        $data = array_merge(config('laravel-emails.default_variables', []), $data);

        // Replace variables in the format {{variable_name}}
        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($data) {
            $key = trim($matches[1]);

            return $data[$key] ?? '';
        }, $template);
    }
}
