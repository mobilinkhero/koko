<?php

namespace App\Livewire\Admin\Role;

use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleCreator extends Component
{
    public Role $role;

    public $roleId;

    public $name = '';

    public $selectedPermissions = [];

    public $permissionGroups = [];

    protected function rules()
    {
        return [
            'name' => ['required', 'string', new PurifiedInput(t('sql_injection_error')), 'max:50', Rule::unique('roles', 'name')
                ->ignore($this->role->id)
                ->where(function ($query) {
                    return $query->where('tenant_id', null);
                })],
            'selectedPermissions' => 'nullable|array|min:1',
        ];
    }

    protected $messages = [
        'name.required' => 'Role name is required.',
        'name.unique' => 'Role name has already been taken.',
        'selectedPermissions.min' => 'At least one permission must be granted. ',
    ];

    public function mount()
    {
        if (! Auth::user()->is_admin) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->role = $this->roleId ? Role::findOrFail($this->roleId) : new Role;
        $this->roleId = $roleId ?? request()->route('roleId');

        if ($this->roleId) {
            $this->name = $this->role->name;
            $this->selectedPermissions = $this->role->permissions->pluck('id')->toArray();
        }

        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $permissions = Permission::where('guard_name', 'web')
            ->where(function ($query) {
                $query->where('scope', 'admin')
                    ->orWhere('name', 'like', 'admin.%');
            })
            ->orderBy('name')
            ->get();

        $this->permissionGroups = $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return isset($parts[1]) ? $parts[1] : 'general';
        })->toArray();
    }

    public function save()
    {
        if (checkPermission(['admin.roles.create'.'admin.roles.edit'])) {
            $this->validate();

            try {
                // Update the role
                $this->role->name = $this->name;
                $this->role->save();

                // Sync permissions
                if (! empty($this->selectedPermissions)) {
                    $permissionNames = Permission::whereIn('id', $this->selectedPermissions)
                        ->pluck('name')
                        ->toArray();
                    $this->role->syncPermissions($permissionNames);
                } else {
                    // Uncheck all permissions
                    $this->role->syncPermissions([]);
                }

                // This forces Spatie to reload permissions for all users
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $this->notify([
                    'type' => 'success',
                    'message' => $this->role->wasRecentlyCreated
                        ? t('role_save_successfully')
                        : t('role_update_successfully'),
                ], true);

                return redirect()->route('admin.roles.list');
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
        $this->redirect(route('admin.roles.list'), navigate: true);
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-role-assignee-table-pkhhln-table');
    }

    public function render()
    {
        return view('livewire.admin.role.role-creator');
    }
}
