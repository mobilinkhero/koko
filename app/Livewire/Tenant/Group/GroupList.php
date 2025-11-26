<?php

namespace App\Livewire\Tenant\Group;

use App\Models\Tenant\Group;
use App\Rules\PurifiedInput;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class GroupList extends Component
{
    use WithPagination;

    public Group $group;

    public $showGroupModal = false;

    public $confirmingDeletion = false;

    public $group_id = null;

    public $tenant_id;

    protected $listeners = [
        'editGroup' => 'editGroup',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.group.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->resetForm();
        $this->group = new Group;
        $this->tenant_id = tenant_id();
    }

    protected function rules()
    {
        return [
            'group.name' => [
                'required',
                Rule::unique('groups', 'name')->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
                })
                    ->ignore($this->group->id), // For update case
                new PurifiedInput(t('sql_injection_error')),
                'max:150',
            ],
        ];
    }

    public function createGroupPage()
    {
        $this->resetForm();
        $this->showGroupModal = true;
    }

    public function save()
    {
        if (checkPermission(['tenant.group.create', 'tenant.group.create'])) {

            $this->validate();

            $isNew = ! $this->group->exists;

            if ($this->group->isDirty()) {
                $this->group->tenant_id = tenant_id();
                $this->group->save();

                $this->showGroupModal = false;

                $message = $this->group->wasRecentlyCreated
                    ? t('group_saved_successfully')
                    : t('group_update_successfully');

                $this->notify(['type' => 'success', 'message' => $message]);
                $this->dispatch('pg:eventRefresh-groups-table-q5rszw-table');
            } else {
                $this->showGroupModal = false;
            }
        }
    }

    public function editGroup($groupId)
    {
        $group = Group::findOrFail($groupId);
        $this->group = $group;
        $this->resetValidation();
        $this->showGroupModal = true;
    }

    public function confirmDelete($groupId)
    {
        $this->group_id = $groupId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.group.delete')) {

            $group = Group::find($this->group_id);

            if ($group) {
                $group->delete();
            }

            $this->confirmingDeletion = false;
            $this->resetForm();
            $this->group_id = null;
            $this->resetPage();

            $this->notify(['type' => 'success', 'message' => t('group_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-groups-table-q5rszw-table');
        }
    }

    private function resetForm()
    {
        $this->resetExcept('group');
        $this->resetValidation();
        $this->group = new Group;
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-groups-table-q5rszw-table');
    }

    public function render()
    {
        return view('livewire.tenant.group.group-list');
    }
}
