<?php

namespace App\Livewire\Tenant\Contact;

use App\Models\Tenant\Source;
use App\Rules\PurifiedInput;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageSource extends Component
{
    use WithPagination;

    public Source $source;

    public $showSourceModal = false;

    public $confirmingDeletion = false;

    public $source_id = null;

    public $tenant_id;

    protected $listeners = [
        'editSource' => 'editSource',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.source.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->resetForm();
        $this->source = new Source;
        $this->tenant_id = tenant_id();
    }

    protected function rules()
    {
        return [
            'source.name' => [
                'required',
                Rule::unique('sources', 'name')->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
                })
                    ->ignore($this->source->id), // For update case
                new PurifiedInput(t('sql_injection_error')),
                'max:150',
            ],
        ];
    }

    public function createSourcePage()
    {
        $this->resetForm();
        $this->showSourceModal = true;
    }

    public function save()
    {
        if (checkPermission(['tenant.source.create', 'tenant.source.create'])) {

            $this->validate();

            $isNew = ! $this->source->exists;

            if ($this->source->isDirty()) {
                $this->source->tenant_id = tenant_id();
                $this->source->save();

                $this->showSourceModal = false;

                $message = $this->source->wasRecentlyCreated
                    ? t('source_saved_successfully')
                    : t('source_update_successfully');

                $this->notify(['type' => 'success', 'message' => $message]);
                $this->dispatch('pg:eventRefresh-source-table-3kfsxg-table');
            } else {
                $this->showSourceModal = false;
            }
        }
    }

    public function editSource($sourceId)
    {
        $source = Source::findOrFail($sourceId);
        $this->source = $source;
        $this->resetValidation();
        $this->showSourceModal = true;
    }

    public function confirmDelete($sourceId)
    {
        $this->source_id = $sourceId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.source.delete')) {

            $source = Source::find($this->source_id);

            if ($source) {
                $source->delete();
            }

            $this->confirmingDeletion = false;
            $this->resetForm();
            $this->source_id = null;
            $this->resetPage();

            $this->notify(['type' => 'success', 'message' => t('source_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-source-table-3kfsxg-table');
        }
    }

    private function resetForm()
    {
        $this->resetExcept('source');
        $this->resetValidation();
        $this->source = new Source;
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-source-table-3kfsxg-table');
    }

    public function render()
    {
        return view('livewire.tenant.contact.manage-source');
    }
}
