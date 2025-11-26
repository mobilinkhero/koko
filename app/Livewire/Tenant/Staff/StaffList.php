<?php

namespace App\Livewire\Tenant\Staff;

use App\Models\User;
use App\Services\FeatureService;
use Livewire\Component;

class StaffList extends Component
{
    public User $staff;

    public $staffId;

    public $confirmingDeletion = false;

    protected $featureLimitChecker;

    protected $listeners = [
        'editStaff' => 'editStaff',
        'confirmDelete' => 'confirmDelete',
        'viewStaff' => 'viewStaff',
    ];

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function mount()
    {
        if (! checkPermission('tenant.staff.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function createStaff()
    {
        $this->redirect(tenant_route('tenant.staff.save'));
    }

    public function viewStaff($staffId)
    {
        return redirect()->to(tenant_route('tenant.staff.details', ['staffId' => $staffId]));
    }

    public function editStaff($staffId)
    {
        $this->staff = User::findOrFail($staffId);

        return redirect()->to(tenant_route('tenant.staff.save', ['staffId' => $staffId]));
    }

    public function confirmDelete($staffId)
    {
        $this->staffId = $staffId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.staff.delete')) {
            $staff = User::findOrFail($this->staffId);
            if ($staff->id == auth()->id()) {
                $this->notify(['type' => 'warning', 'message' => t('cannot_delete_yourself')]);

                return;
            }
            $staff->delete();
            $this->notify(['type' => 'success', 'message' => t('staff_deleted_successfully')]);
            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-staff-table');
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('staff', User::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('staff');
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('staff', User::class);
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-staff-table');
    }

    public function render()
    {
        return view('livewire.tenant.staff.staff-list');
    }
}
