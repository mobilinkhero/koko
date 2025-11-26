<?php

namespace App\Livewire\Tenant\CustomField;

use App\Models\Tenant\CustomField;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CustomFieldCreator extends Component
{
    public $customFieldId = null;

    public $field_name = '';

    public $field_label = '';

    public $field_type = 'text';

    public $placeholder = '';

    public $description = '';

    public $is_required = false;

    public $default_value = '';

    public $is_active = true;

    public $show_on_table = false;

    // For dropdown options
    public $field_options = [];

    public $newOption = '';

    public $isEditMode = false;

    protected $listeners = [
        'editCustomField' => 'loadCustomField',
    ];

    public function mount($customFieldId = null)
    {
        if ($customFieldId) {
            if (! checkPermission('tenant.custom_fields.edit')) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('access_denied_note'),
                ]);

                return redirect()->to(tenant_route('tenant.custom-fields.list'));
            }
            $this->loadCustomField($customFieldId);
        } else {
            if (! checkPermission('tenant.custom_fields.create')) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('access_denied_note'),
                ]);

                return redirect()->to(tenant_route('tenant.custom-fields.list'));
            }
        }
    }

    public function loadCustomField($customFieldId)
    {
        $field = CustomField::where('tenant_id', current_tenant()->id)
            ->findOrFail($customFieldId);

        $this->customFieldId = $field->id;
        $this->field_name = $field->field_name;
        $this->field_label = $field->field_label;
        $this->field_type = $field->field_type;
        $this->placeholder = $field->placeholder ?? '';
        $this->description = $field->description ?? '';
        $this->is_required = $field->is_required;
        $this->is_active = $field->is_active;
        $this->show_on_table = $field->show_on_table ?? false;
        $this->field_options = $field->field_options ?? [];

        // Handle default value for checkbox type
        if ($field->field_type === 'checkbox' && is_array($field->default_value)) {
            // Convert the stored default values back to an associative array
            $this->default_value = collect($field->default_value)
                ->mapWithKeys(fn ($option) => [$option => true])
                ->toArray();
        } else {
            $this->default_value = $field->default_value ?? '';
        }

        $this->isEditMode = true;
    }

    public function rules()
    {
        $tenant = current_tenant();

        return [
            'field_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('custom_fields', 'field_name')
                    ->where('tenant_id', $tenant->id)
                    ->ignore($this->customFieldId),
            ],
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,date,dropdown,checkbox',
            'placeholder' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_required' => 'boolean',
            'default_value' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'show_on_table' => 'boolean',
            'field_options' => 'array|min:0',
            'field_options.*' => 'string|max:255',
        ];
    }

    public function validationAttributes()
    {
        return [
            'field_name' => 'field name',
            'field_label' => 'field label',
            'field_type' => 'field type',
            'field_options' => 'dropdown options',
        ];
    }

    public function messages()
    {
        return [
            'field_name.regex' => 'The field name may only contain lowercase letters, numbers, and underscores.',
            'field_name.unique' => 'A custom field with this name already exists.',
            'field_options.min' => 'Dropdown fields must have at least one option.',
        ];
    }

    public function updatedFieldName()
    {
        // Auto-generate field name from label if not manually edited
        if (! $this->isEditMode && empty($this->field_name)) {
            $this->field_name = $this->generateFieldName($this->field_label);
        }
    }

    public function updatedFieldLabel()
    {
        // Auto-generate field name from label if not manually edited
        if (! $this->isEditMode && empty($this->field_name)) {
            $this->field_name = $this->generateFieldName($this->field_label);
        }
    }

    public function updatedFieldType()
    {
        // Clear dropdown options when changing away from dropdown
        if ($this->field_type !== 'dropdown') {
            $this->field_options = [];
        }

        // Clear default value when changing field type
        $this->default_value = '';
    }

    private function generateFieldName($label)
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim($label)));
    }

    public function addOption()
    {
        $this->validate([
            'newOption' => 'required|string|max:255',
        ]);

        if (! in_array($this->newOption, $this->field_options)) {
            $this->field_options[] = $this->newOption;
            $this->newOption = '';
        } else {
            $this->addError('newOption', 'This option already exists.');
        }
    }

    public function removeOption($index)
    {
        if (isset($this->field_options[$index])) {
            unset($this->field_options[$index]);
            $this->field_options = array_values($this->field_options); // Re-index array
        }
    }

    public function moveOptionUp($index)
    {
        if ($index > 0 && isset($this->field_options[$index])) {
            $temp = $this->field_options[$index];
            $this->field_options[$index] = $this->field_options[$index - 1];
            $this->field_options[$index - 1] = $temp;
        }
    }

    public function moveOptionDown($index)
    {
        if ($index < count($this->field_options) - 1 && isset($this->field_options[$index])) {
            $temp = $this->field_options[$index];
            $this->field_options[$index] = $this->field_options[$index + 1];
            $this->field_options[$index + 1] = $temp;
        }
    }

    public function save()
    {
        // Check permissions
        if ($this->customFieldId) {
            if (! checkPermission('tenant.custom_fields.edit')) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('access_denied_note'),
                ]);

                return;
            }
        } else {
            if (! checkPermission('tenant.custom_fields.create')) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('access_denied_note'),
                ]);

                return;
            }
        }

        // Additional validation for dropdown and checkbox fields
        if ($this->field_type === 'dropdown' || $this->field_type === 'checkbox') {
            $this->validate([
                'field_options' => 'required|array|min:1',
            ]);
        }

        $this->validate();

        $tenant = current_tenant();

        // Process default value for checkbox type
        $processedDefaultValue = $this->default_value;
        if ($this->field_type === 'checkbox' && is_array($this->default_value)) {
            // Convert the checkbox array into a list of selected options
            $processedDefaultValue = collect($this->default_value)
                ->filter(fn ($value, $key) => $value)
                ->keys()
                ->toArray();
        }

        $data = [
            'tenant_id' => $tenant->id,
            'field_name' => $this->field_name,
            'field_label' => $this->field_label,
            'field_type' => $this->field_type,
            'placeholder' => $this->placeholder ?: null,
            'description' => $this->description ?: null,
            'is_required' => $this->is_required,
            'default_value' => $processedDefaultValue ?: null,
            'is_active' => $this->is_active,
            'show_on_table' => $this->show_on_table,
            'field_options' => in_array($this->field_type, ['dropdown', 'checkbox']) ? $this->field_options : null,
        ];

        if ($this->isEditMode && $this->customFieldId) {
            $field = CustomField::where('tenant_id', $tenant->id)
                ->findOrFail($this->customFieldId);

            $field->update($data);

            $this->notify([
                'type' => 'success',
                'message' => t('custom_field_updated_successfully'),
            ]);

            $this->dispatch('customFieldUpdated');
        } else {
            CustomField::create($data);

            $this->notify([
                'type' => 'success',
                'message' => t('custom_field_created_successfully'),
            ]);

            $this->dispatch('customFieldCreated');
            $this->reset();
        }

        Cache::forget('contacts_table_custom_fields'.$tenant->id);

        // Redirect to list
        return redirect()->to(tenant_route('tenant.custom-fields.list'));
    }

    public function cancel()
    {
        return redirect()->to(tenant_route('tenant.custom-fields.list'));
    }

    public function resetForm()
    {
        $this->reset([
            'field_name',
            'field_label',
            'field_type',
            'placeholder',
            'description',
            'is_required',
            'default_value',
            'field_options',
            'newOption',
            'show_on_table',
        ]);

        $this->field_type = 'text';
        $this->is_active = true;
        $this->isEditMode = false;
    }

    public function getFieldTypesProperty()
    {
        return CustomField::FIELD_TYPES;
    }

    public function render()
    {
        return view('livewire.tenant.custom-field.custom-field-creator');
    }
}
