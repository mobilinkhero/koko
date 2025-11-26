<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Contact
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $company
 * @property string $type
 * @property string|null $description
 * @property int|null $country_id
 * @property string|null $zip
 * @property string|null $city
 * @property string|null $state
 * @property string|null $address
 * @property int|null $assigned_id
 * @property int $status_id
 * @property int $source_id
 * @property string|null $email
 * @property string|null $website
 * @property string|null $phone
 * @property bool|null $is_enabled
 * @property int $addedfrom
 * @property Carbon|null $dateassigned
 * @property Carbon|null $last_status_change
 * @property string|null $default_language
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array $group_id
 * @property Source $source
 * @property Status $status
 * @property Tenant $tenant
 * @property Collection|ContactNote[] $contact_notes
 * @property-read int|null $contact_notes_count
 * @property-read mixed $country_name
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAddedfrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAssignedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereDateassigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereDefaultLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLastStatusChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereZip($value)
 *
 * @mixin \Eloquent
 */
class Contact extends BaseModel
{
    use BelongsToTenant, TracksFeatureUsage;

    protected $casts = [
        'tenant_id' => 'int',
        'country_id' => 'int',
        'is_enabled' => 'bool',
        'addedfrom' => 'int',
        'dateassigned' => 'datetime',
        'last_status_change' => 'datetime',
        'group_id' => 'array',
        'custom_fields_data' => 'array',
    ];

    protected $fillable = [
        'firstname',
        'lastname',
        'company',
        'type',
        'description',
        'country_id',
        'zip',
        'city',
        'state',
        'address',
        'assigned_id',
        'status_id',
        'source_id',
        'group_id',
        'email',
        'website',
        'phone',
        'is_enabled',
        'addedfrom',
        'dateassigned',
        'last_status_change',
        'tenant_id',
        'custom_fields_data',
        'created_at',
        'updated_at',
    ];

    /**
     * Create a new instance of the model with the correct table name
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($subdomain = tenant_subdomain()) {
            $this->setTable($subdomain.'_contacts');
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contact) {
            do_action('contact.before_create', $contact);
        });

        static::created(function ($contact) {
            do_action('contact.after_create', $contact);
        });

        static::updating(function ($contact) {
            do_action('contact.before_update', $contact);
        });

        static::updated(function ($contact) {
            do_action('contact.after_update', $contact);
        });

        static::deleting(function ($contact) {
            do_action('contact.before_delete', $contact);
        });

        static::deleted(function ($contact) {
            do_action('contact.after_delete', $contact);
        });

        do_action('model.booted', static::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function contact_notes()
    {
        return $this->hasMany(ContactNote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_id');
    }

    public function getCountryNameAttribute()
    {
        $country = collect(get_country_list())->firstWhere('id', (string) $this->country_id);

        return $country['short_name'] ?? null;
    }

    public function getFeatureSlug(): ?string
    {
        return 'contacts';
    }

    public static function fromTenant(string $subdomain)
    {
        return (new static)->setTable($subdomain.'_contacts');
    }

    public function getGroupIds(): array
    {
        $groups = $this->group_id;

        if (is_null($groups)) {
            return [];
        }

        if (is_array($groups)) {
            return array_map('intval', $groups);
        }

        // Fallback for any remaining string values
        if (is_string($groups)) {
            $decoded = json_decode($groups, true);

            return is_array($decoded) ? array_map('intval', $decoded) : [];
        }

        return [];
    }

    public function setGroupIds(array $groupIds): void
    {
        $cleanIds = array_values(array_unique(array_map('intval', array_filter($groupIds, 'is_numeric'))));
        $this->group_id = $cleanIds;
    }

    // Scope to filter by group
    public function scopeInGroup($query, $groupId)
    {
        return $query->whereJsonContains('group_id', (int) $groupId);
    }

    // Scope to filter by multiple groups (OR condition)
    public function scopeInAnyGroup($query, array $groupIds)
    {
        return $query->where(function ($q) use ($groupIds) {
            foreach ($groupIds as $groupId) {
                $q->orWhereJsonContains('group_id', (int) $groupId);
            }
        });
    }

    // Scope to filter by multiple groups (AND condition)
    public function scopeInAllGroups($query, array $groupIds)
    {
        foreach ($groupIds as $groupId) {
            $query->whereJsonContains('group_id', (int) $groupId);
        }

        return $query;
    }

    // Helper method to check if contact belongs to a group
    public function belongsToGroup($groupId): bool
    {
        return in_array((int) $groupId, $this->getGroupIds());
    }

    // Helper method to add a group
    public function addToGroup($groupId): void
    {
        $groups = $this->getGroupIds();
        if (! in_array((int) $groupId, $groups)) {
            $groups[] = (int) $groupId;
            $this->setGroupIds($groups);
            $this->save();
        }
    }

    // Helper method to remove from group
    public function removeFromGroup($groupId): void
    {
        $groups = $this->getGroupIds();
        $groups = array_filter($groups, fn ($id) => $id != (int) $groupId);
        $this->setGroupIds($groups);
        $this->save();
    }

    // Helper method to set groups (replace all)
    public function setGroups(array $groupIds): void
    {
        $this->setGroupIds($groupIds);
        $this->save();
    }

    // Get groups relationship
    public function groups()
    {
        $groupIds = $this->getGroupIds();

        if (empty($groupIds)) {
            return collect([]);
        }

        return Group::whereIn('id', $groupIds)->get();
    }

    public function assignedGroups()
    {
        return $this->groups();
    }

    /**
     * Get custom fields for this tenant
     */
    public function getAvailableCustomFields(): Collection
    {
        return CustomField::where('tenant_id', $this->tenant_id)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get custom field value
     */
    public function getCustomFieldValue(string $fieldName, $default = null)
    {
        if (! $this->custom_fields_data || ! is_array($this->custom_fields_data)) {
            return $default;
        }

        return $this->custom_fields_data[$fieldName] ?? $default;
    }

    /**
     * Set custom field value
     */
    public function setCustomFieldValue(string $fieldName, $value): void
    {
        $customFieldsData = $this->custom_fields_data ?? [];
        $customFieldsData[$fieldName] = $value;
        $this->custom_fields_data = $customFieldsData;
    }

    /**
     * Set multiple custom field values
     */
    public function setCustomFieldValues(array $values): void
    {
        $customFieldsData = $this->custom_fields_data ?? [];

        foreach ($values as $fieldName => $value) {
            $customFieldsData[$fieldName] = $value;
        }

        $this->custom_fields_data = $customFieldsData;
    }

    /**
     * Get all custom field values with their definitions
     */
    public function getCustomFieldsData()
    {
        $customFields = $this->getAvailableCustomFields();
        $customFieldsData = $this->custom_fields_data ?? [];

        return $customFields->map(function ($field) use ($customFieldsData) {
            return [
                'field' => $field,
                'value' => $customFieldsData[$field->field_name] ?? $field->default_value,
                'display_value' => $this->getCustomFieldDisplayValue($field, $customFieldsData[$field->field_name] ?? $field->default_value),
            ];
        });
    }

    /**
     * Get display value for custom field (useful for dropdowns)
     */
    public function getCustomFieldDisplayValue(CustomField $field, $value): string
    {
        if (is_null($value) || $value === '') {
            return '';
        }

        switch ($field->field_type) {
            case 'dropdown':
                return (string) $value;

            case 'date':
                if ($value) {
                    try {
                        return \Carbon\Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return (string) $value;
                    }
                }

                return '';

            default:
                return (string) $value;
        }
    }

    /**
     * Validate custom fields data
     */
    public function validateCustomFields(?array $customFieldsData = null): array
    {
        $customFieldsData = $customFieldsData ?? $this->custom_fields_data ?? [];
        $errors = [];

        $customFields = $this->getAvailableCustomFields();

        foreach ($customFields as $field) {
            $value = $customFieldsData[$field->field_name] ?? null;

            if (! $field->validateValue($value)) {
                $errors[$field->field_name] = "The {$field->field_label} field is invalid.";
            }
        }

        return $errors;
    }

    /**
     * Get validation rules for all custom fields
     */
    public function getCustomFieldValidationRules(): array
    {
        $rules = [];
        $customFields = $this->getAvailableCustomFields();

        foreach ($customFields as $field) {
            $rules["custom_fields.{$field->field_name}"] = $field->getValidationRules();
        }

        return $rules;
    }

    /**
     * Scope to filter by custom field value
     */
    public function scopeWhereCustomField($query, string $fieldName, $value)
    {
        return $query->whereJsonContains('custom_fields_data->'.$fieldName, $value);
    }

    /**
     * Scope to filter by custom field existence
     */
    public function scopeWhereHasCustomField($query, string $fieldName)
    {
        return $query->whereJsonContains('custom_fields_data', [$fieldName => true])
            ->orWhereNotNull("custom_fields_data->{$fieldName}");
    }

    /**
     * Search contacts by custom field values
     */
    public function scopeSearchCustomFields($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->whereRaw("JSON_SEARCH(custom_fields_data, 'one', ?) IS NOT NULL", ["%{$searchTerm}%"]);
        });
    }
}
