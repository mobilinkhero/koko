<?php

namespace App\Livewire\Tenant\Staff;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

class StaffDetails extends Component
{
    public User $user;

    public function mount($staffId)
    {
        if (! checkPermission('tenant.staff.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant_id());

        if (! checkPermission('tenant.staff.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->user = User::findOrFail($staffId);

    }

    public function render()
    {
        return view('livewire.tenant.staff.staff-details');
    }
}
