<?php

namespace Corbital\LaravelEmails\Models;

use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailLayout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'header',
        'footer',
        'master_template',
        'variables',
        'is_default',
        'is_system',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'json',
        'is_default' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($layout) {
            // Auto-generate slug from name if not provided
            if (empty($layout->slug) && ! empty($layout->name)) {
                $layout->slug = Str::slug($layout->name);
            }
        });

        static::saving(function ($layout) {
            // If this layout is set as default, unset all other defaults
            if ($layout->is_default) {
                static::where('id', '!=', $layout->id)->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the templates that use this layout.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class, 'layout_id');
    }

    /**
     * Scope a query to only include default layouts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include active layouts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system layouts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Get the default layout.
     *
     * @return \Corbital\LaravelEmails\Models\EmailLayout|null
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first() ?? static::first();
    }

    /**
     * Render the complete layout with content.
     *
     * @param  string  $content
     * @param  array  $variables
     * @return string
     */
    public function render($content, $variables = [])
    {
        // Start with the master template
        $template = $this->master_template;

        // Replace {HEADER} placeholder with header content
        if (strpos($template, '{HEADER}') !== false) {
            $template = str_replace('{HEADER}', $this->header ?? '', $template);
        }

        // Replace {FOOTER} placeholder with footer content
        if (strpos($template, '{FOOTER}') !== false) {
            $template = str_replace('{FOOTER}', $this->footer ?? '', $template);
        }

        // Replace {CONTENT} placeholder with the actual email content
        if (strpos($template, '{CONTENT}') !== false) {
            $template = str_replace('{CONTENT}', $content, $template);
        }

        $mergeFieldsService = app(MergeFieldsService::class);
        $template = $mergeFieldsService->parseTemplates(['other-group'], $template, $variables);

        // Process variables using the same engine as email templates
        $template = $this->processVariables($template, $variables);

        return $template;
    }

    /**
     * Process template variables.
     *
     * @param  string  $content
     * @param  array  $variables
     * @return string
     */
    protected function processVariables($content, $variables = [])
    {
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $content = str_replace('{{'.$key.'}}', $value ?? '', $content);
            }
        }

        return $content;
    }
}
