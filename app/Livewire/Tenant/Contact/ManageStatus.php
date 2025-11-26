<?php

namespace App\Livewire\Tenant\Contact;

use App\Models\Tenant\Status;
use App\Rules\PurifiedInput;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageStatus extends Component
{
    use WithPagination;

    public Status $status;

    public $showStatusModal = false;

    public $status_id = null;

    public $confirmingDeletion = false;

    protected $listeners = [
        'editStatus' => 'editStatus',
        'confirmDelete' => 'confirmDelete',
    ];

    public $tenant_id;

    public function mount()
    {
        if (! checkPermission('tenant.status.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->resetForm();
        $this->status = new Status;
        $this->tenant_id = tenant_id();
    }

    protected function rules()
    {
        return [
            'status.name' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('statuses', 'name')->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
                })
                    ->ignore($this->status->id),
                new PurifiedInput(t('sql_injection_error')),
            ],
            'status.color' => [
                'required',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                new PurifiedInput(t('sql_injection_error')),
            ],
        ];
    }

    public function createStatusPage()
    {
        $this->resetForm();
        $this->showStatusModal = true;
    }

    public function save()
    {
        if (checkPermission(['tenant.status.create', 'tenant.status.edit'])) {
            $this->validate();

            // Check if this is a new status being created (not an update)
            $isNew = ! $this->status->exists;

            if ($this->status->isDirty()) {
                $this->status->isdefault = 0;
                $this->status->tenant_id = tenant_id();

                $this->status->save();
                $this->showStatusModal = false;

                $message = $this->status->wasRecentlyCreated
                    ? t('status_save_successfully')
                    : t('status_update_successfully');

                $this->notify(['type' => 'success', 'message' => $message]);
                $this->dispatch('pg:eventRefresh-status-table');
            } else {
                $this->showStatusModal = false;
            }
        }
    }

    public function editStatus($statusId)
    {
        $status = Status::findOrFail($statusId);
        $this->status = $status;
        $this->resetValidation();
        $this->showStatusModal = true;
    }

    public function confirmDelete($statusId)
    {
        $this->status_id = $statusId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission(['tenant.status.delete'])) {
            $status = Status::find($this->status_id);

            if ($status) {
                $status->delete();
            }

            $this->confirmingDeletion = false;
            $this->resetForm();
            $this->status_id = null;
            $this->resetPage();

            $this->notify(['type' => 'success', 'message' => t('status_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-status-table');
        }
    }

    private function resetForm()
    {
        $this->resetExcept('status');
        $this->resetValidation();
        $this->status = new Status;
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-status-table');
    }

    public function render()
    {
        return view('livewire.tenant.contact.manage-status');
    }
}
