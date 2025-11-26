<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CustomField
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $field_name
 * @property string $field_label
 * @property string $field_type
 * @property array|null $field_options
 * @property string|null $placeholder
 * @property string|null $description
 * @property bool $is_required
 * @property string|null $default_value
 * @property int $display_order
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property Tenant $tenant
 */
class CustomField extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'placeholder',
        'description',
        'is_required',
        'default_value',
        'display_order',
        'is_active',
        'show_on_table',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'field_options' => 'array',
        'is_required' => 'boolean',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'show_on_table' => 'boolean',
    ];

    /**
     * The available field types
     */
    public const FIELD_TYPES = [
        'text' => 'Text Field',
        'textarea' => 'Text Area',
        'number' => 'Number Field',
        'date' => 'Date Field',
        'dropdown' => 'Dropdown',
        'checkbox' => 'Checkbox',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customField) {
            if (empty($customField->display_order)) {
                $customField->display_order = static::where('tenant_id', $customField->tenant_id)->max('display_order') + 1;
            }

            do_action('custom_field.before_create', $customField);
        });

        static::created(function ($customField) {
            do_action('custom_field.after_create', $customField);
        });

        static::updating(function ($customField) {
            do_action('custom_field.before_update', $customField);
        });

        static::updated(function ($customField) {
            do_action('custom_field.after_update', $customField);
        });

        static::deleting(function ($customField) {
            do_action('custom_field.before_delete', $customField);
        });

        static::deleted(function ($customField) {
            do_action('custom_field.after_delete', $customField);
        });
    }

    /**
     * Get the tenant that owns the custom field
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to only active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Get field type label
     */
    public function getFieldTypeLabelAttribute(): string
    {
        return self::FIELD_TYPES[$this->field_type] ?? $this->field_type;
    }

    /**
     * Check if field is dropdown type
     */
    public function isDropdown(): bool
    {
        return $this->field_type === 'dropdown';
    }

    /**
     * Check if field is required
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * Get dropdown options as array
     */
    public function getDropdownOptions(): array
    {
        if (! $this->isDropdown() || ! $this->field_options) {
            return [];
        }

        return $this->field_options;
    }

    /**
     * Set dropdown options
     */
    public function setDropdownOptions(array $options): void
    {
        $this->field_options = array_values(array_filter($options));
    }

    /**
     * Validate field value
     */
    public function validateValue($value): bool
    {
        // Required field validation
        if ($this->is_required && (is_null($value) || $value === '')) {
            return false;
        }

        // Type-specific validation
        switch ($this->field_type) {
            case 'number':
                return is_null($value) || $value === '' || is_numeric($value);

            case 'date':
                if (is_null($value) || $value === '') {
                    return true;
                }

                return strtotime($value) !== false;

            case 'dropdown':
                if (is_null($value) || $value === '') {
                    return true;
                }

                return in_array($value, $this->getDropdownOptions());

            default:
                return true;
        }
    }

    /**
     * Get validation rules for this field
     */
    public function getValidationRules(): array
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($this->field_type) {
            case 'number':
                $rules[] = 'numeric';
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'dropdown':
                if (! empty($this->field_options)) {
                    $rules[] = 'in:'.implode(',', $this->field_options);
                }
                break;

            case 'text':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;

            case 'textarea':
                $rules[] = 'string';
                $rules[] = 'max:2000';
                break;
        }

        return $rules;
    }
}
