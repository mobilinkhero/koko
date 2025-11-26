<?php

namespace App\Livewire\Tenant\CustomField;

use App\Models\Tenant\Contact;
use App\Models\Tenant\CustomField;
use Livewire\Component;

class CustomFieldList extends Component
{
    public $confirmingDeletion = false;

    public $field_id = null;

    protected $listeners = [
        'customFieldCreated' => 'refreshList',
        'customFieldUpdated' => 'refreshList',
        'refreshCustomFields' => 'refreshList',
        'moveUp' => 'moveUp',
        'moveDown' => 'moveDown',
        'confirmDelete' => 'confirmDelete',
        'refreshComponent' => '$refresh',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.custom_fields.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function toggleActive($fieldId)
    {
        if (! checkPermission('tenant.custom_fields.edit')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('custom_field_edit_permission_denied'),
            ]);

            return;
        }

        $field = CustomField::where('tenant_id', current_tenant()->id)
            ->findOrFail($fieldId);

        $field->update(['is_active' => ! $field->is_active]);

        $this->notify([
            'type' => 'success',
            'message' => t('custom_field_status_updated_successfully'),
        ]);
    }

    public function moveUp($fieldId)
    {
        $this->reorderField($fieldId, 'up');
    }

    public function moveDown($fieldId)
    {
        $this->reorderField($fieldId, 'down');
    }

    private function reorderField($fieldId, $direction)
    {
        if (! checkPermission('tenant.custom_fields.edit')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('custom_field_edit_permission_denied'),
            ]);

            return;
        }

        $tenant = current_tenant();
        $field = CustomField::where('tenant_id', $tenant->id)->findOrFail($fieldId);

        if ($direction === 'up') {
            $swapField = CustomField::where('tenant_id', $tenant->id)
                ->where('display_order', '<', $field->display_order)
                ->orderBy('display_order', 'desc')
                ->first();
        } else {
            $swapField = CustomField::where('tenant_id', $tenant->id)
                ->where('display_order', '>', $field->display_order)
                ->orderBy('display_order', 'asc')
                ->first();
        }

        if ($swapField) {
            $tempOrder = $field->display_order;
            $field->update(['display_order' => $swapField->display_order]);
            $swapField->update(['display_order' => $tempOrder]);

            $this->notify([
                'type' => 'success',
                'message' => t('custom_field_order_updated_successfully'),
            ]);

            // Refresh PowerGrid table
            $this->dispatch('pg:eventRefresh-custom-fields-table');
        }
    }

    public function confirmDelete($fieldId)
    {
        if (! checkPermission('tenant.custom_fields.delete')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return;
        }

        $this->field_id = $fieldId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (! checkPermission('tenant.custom_fields.delete')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return;
        }

        $field = CustomField::where('tenant_id', tenant_id())
            ->findOrFail($this->field_id);

            // Check if any contact is using this custom field
        $contacts = Contact::where('tenant_id', tenant_id())
            ->whereNotNull('custom_fields_data')
            ->whereRaw("JSON_EXTRACT(custom_fields_data, '$.\"".$field->field_name."\"') IS NOT NULL")
            ->exists();
        if ($contacts) {
            $this->notify([
                'type' => 'danger',
                'message' => t('custom_field_in_use_cannot_delete'),
            ]);
            $this->confirmingDeletion = false;
            $this->field_id = null;
            return;
        }

        $field->delete();

        $this->confirmingDeletion = false;

        $this->field_id = null;

        $this->notify([
            'type' => 'success',
            'message' => t('custom_field_delete_success'),
        ]);

        // Refresh PowerGrid table
        $this->dispatch('pg:eventRefresh-custom-fields-table');
    }

    public function refreshList()
    {
        // Refresh PowerGrid table
        $this->dispatch('pg:eventRefresh-custom-fields-table');
    }

    public function getCanCreateProperty()
    {
        return checkPermission('tenant.custom_fields.create');
    }

    public function getCanEditProperty()
    {
        return checkPermission('tenant.custom_fields.edit');
    }

    public function getCanDeleteProperty()
    {
        return checkPermission('tenant.custom_fields.delete');
    }

    public function render()
    {
        return view('livewire.tenant.custom-field.custom-field-list');
    }
}
