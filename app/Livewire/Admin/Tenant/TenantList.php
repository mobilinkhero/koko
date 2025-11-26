<?php

namespace App\Livewire\Admin\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantDeletionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TenantList extends Component
{
    public Tenant $tenant;

    public User $user;

    public $tenantId;

    public $confirmingDeletion = false;

    protected $listeners = [
        'editTenant' => 'editTenant',
        'confirmDelete' => 'confirmDelete',
        'viewTenant' => 'viewTenant',
        'confirmTenantRegistration' => 'confirmTenantRegistration',
        'restoreTenant' => 'restoreTenant',
    ];

    public function mount()
    {
        if (! checkPermission('admin.tenants.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function createTenant()
    {
        $this->redirect(tenant_route('admin.tenants.save'));
    }

    public function editTenant($tenantId)
    {
        $this->tenant = Tenant::findOrFail($tenantId);
        $this->redirect(route('admin.tenants.save', ['tenantId' => $tenantId]));
    }

    public function viewTenant($tenantId)
    {
        $this->redirect(route('admin.tenants.view', ['tenantId' => $tenantId]));
    }

    public function confirmDelete($tenantId)
    {
        $this->tenantId = $tenantId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('admin.tenants.delete')) {
            $tenant = Tenant::withoutGlobalScopes()->findOrFail($this->tenantId);

            // Check if trying to delete the current user's own tenant (if applicable)
            $adminUser = $tenant->adminUser;
            if ($adminUser && $adminUser->id == Auth::id()) {
                $this->notify(['type' => 'warning', 'message' => t('cannot_delete_your_own_tenant')]);

                return;
            }

            try {
                $deletionService = app(TenantDeletionService::class);
                $success = $deletionService->markTenantForDeletion($tenant);

                if ($success) {
                    $this->notify(['type' => 'success', 'message' => t('tenant_marked_for_deletion_successfully')]);
                } else {
                    $this->notify(['type' => 'danger', 'message' => t('tenant_deletion_failed')]);
                }
            } catch (\Exception $e) {
                $this->notify(['type' => 'danger', 'message' => t('tenant_deletion_failed')]);
            }

            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-tenant-table');
        }
    }

    public function confirmTenantRegistration($tenantId)
    {
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->notify(['type' => 'danger', 'message' => t('tenant_not_found')]);

            return;
        }

        // Update email_verified_at on the related user
        $user = User::where('tenant_id', $tenant->id)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
        }

        $this->notify(['type' => 'success', 'message' => t('tenant_verified_successfully')]);
        $this->dispatch('pg:eventRefresh-tenant-table');
    }

    public function restoreTenant($tenantId)
    {
        if (! checkPermission('admin.tenants.delete')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')]);

            return;
        }

        $tenant = Tenant::withoutGlobalScopes()->find($tenantId);

        if (! $tenant) {
            $this->notify(['type' => 'danger', 'message' => t('tenant_not_found')]);

            return;
        }

        try {
            $deletionService = app(TenantDeletionService::class);
            $success = $deletionService->restoreTenant($tenant);

            if ($success) {
                $this->notify(['type' => 'success', 'message' => t('tenant_restored_successfully')]);
            } else {
                $this->notify(['type' => 'danger', 'message' => t('tenant_restore_failed')]);
            }
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => t('tenant_restore_failed')]);
        }

        $this->dispatch('pg:eventRefresh-tenant-table');
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-tenant-table');
    }

    public function render()
    {
        return view('livewire.admin.tenant.tenant-list');
    }
}
