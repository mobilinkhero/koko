<?php

namespace App\Livewire\Tenant\Role;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class TenantRoleList extends Component
{
    public $confirmingDeletion;

    public $roleId;

    protected $listeners = [
        'editRole' => 'editRole',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! Auth::user()->is_admin && Auth::user()->user_type === 'tenant') {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function editRole($roleId)
    {
        return redirect()->to(tenant_route('tenant.roles.save', ['roleId' => $roleId]));
    }

    public function confirmDelete($roleId)
    {
        $this->roleId = $roleId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.role.delete')) {
            Role::findOrFail($this->roleId)->delete();
            $this->confirmingDeletion = false;
            $this->notify(['type' => 'success', 'message' => t('role_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-tenant-role-table-iuvydh-table');
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-tenant-role-table-iuvydh-table');
    }

    public function render()
    {
        return view('livewire.tenant.role.tenant-role-list');
    }
}
