<?php

namespace App\Livewire\Tenant\Role;

use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantRoleCreator extends Component
{
    public $name = '';

    public Role $role;

    public $roleId;

    public $selectedPermissions = [];

    public $selectedPermissionNames = [];

    public $permissionGroups = [];

    public function mount()
    {
        if (! Auth::user()->is_admin && Auth::user()->user_type === 'tenant') {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->role = $this->roleId ? Role::findOrFail($this->roleId) : new Role;
        $this->roleId = $roleId ?? request()->route('roleId');

        if ($this->roleId) {
            $this->name = $this->role->name;
            $this->selectedPermissions = $this->role->permissions->pluck('id')->toArray();

            $this->selectedPermissionNames = $this->role->permissions->pluck('name')->toArray();
        }

        if (! tenant_check()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Tenant context is required',
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        // Get tenant permissions only (scope = tenant or name like tenant.%)
        $permissions = Permission::where('guard_name', 'web')
            ->where(function ($query) {
                $query->where('scope', 'tenant')
                    ->orWhere('name', 'like', 'tenant.%');
            })
            ->orderBy('name')
            ->get();

        // Group permissions by the second part of the name (after tenant.)
        $this->permissionGroups = $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return isset($parts[1]) ? $parts[1] : 'general';
        })->toArray();
    }

    protected function rules()
    {
        return [
            'name' => ['required', new PurifiedInput(t('sql_injection_error')), Rule::unique('roles', 'name')
                ->ignore($this->role->id)
                ->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
                }), 'max:50'],
            'selectedPermissions' => 'nullable|array|min:1',
        ];
    }

    protected $messages = [
        'name.required' => 'Role name is required.',
        'name.unique' => 'Role name has already been taken.',
        'selectedPermissions.min' => 'At least one permission must be granted. ',
    ];

    public function save()
    {
        if (checkPermission(['tenant.role.create', 'tenant.role.edit'])) {
            if (! tenant_check()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Tenant context is required',
                ]);

                return redirect()->to(tenant_route('tenant.dashboard'));
            }

            $this->validate();
            try {
                $this->role->name = $this->name;
                $this->role->tenant_id = tenant_id();
                $this->role->save();

                $permissionNames = Permission::whereIn('id', $this->selectedPermissions)
                    ->pluck('name')
                    ->toArray();

                // Sync permissions by name
                $this->role->syncPermissions($permissionNames);

                $this->notify([
                    'type' => 'success',
                    'message' => $this->role->wasRecentlyCreated
                        ? t('role_save_successfully')
                        : t('role_update_successfully'),
                ], true);

                return redirect()->to(tenant_route('tenant.roles.list'));
            } catch (\Exception $e) {
                app_log('Failed to save role: '.$e->getMessage(), 'error', $e, [
                    'role_id' => $this->role->id ?? null,
                    'selectedPermissions' => $this->selectedPermissions,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('role_save_failed')]);
            }
        }
    }

    public function cancel()
    {
        $this->resetValidation();

        return redirect()->to(tenant_route('tenant.roles.list'));
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-tenant-role-assignee-table-x5kjnl-table');
    }

    public function render()
    {
        return view('livewire.tenant.role.tenant-role-creator');
    }
}
