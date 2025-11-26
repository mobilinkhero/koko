<?php

namespace App\Livewire\Admin\Role;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleList extends Component
{
    public $confirmingDeletion;

    public $roleId;

    protected $listeners = [
        'editRole' => 'editRole',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! Auth::user()->is_admin) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function editRole($roleId)
    {
        return to_route('admin.roles.save', ['roleId' => $roleId]);
    }

    public function confirmDelete($roleId)
    {
        $this->roleId = $roleId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('admin.roles.delete')) {
            Role::findOrFail($this->roleId)->delete();
            $this->confirmingDeletion = false;
            $this->notify(['type' => 'success', 'message' => t('role_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-role-table-7crbpz-table');
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-role-table-7crbpz-table');
    }

    public function render()
    {
        return view('livewire.admin.role.role-list');
    }
}
