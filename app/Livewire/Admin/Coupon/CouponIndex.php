<?php

namespace App\Livewire\Admin\Coupon;

use App\Models\Coupon;
use Livewire\Component;

class CouponIndex extends Component
{
    public $search = '';

    public $editingCouponId = null;

    public $deletingCouponId = null;

    public $confirmingDeletion = false;

    protected $listeners = [
        'toggleStatus',
        'confirmDelete' => 'confirmDelete',
        'deleteCoupon',
        'couponSaved' => '$refresh',
        'powerGridComponentRefreshed' => '$refresh',
    ];

    public function mount()
    {
        // Initialize component
    }

    public function toggleStatus($id)
    {

        $coupon = Coupon::find($id);

        if ($coupon) {
            $coupon->is_active = ! $coupon->is_active;
            $coupon->save();

            $statusMessage = $coupon->is_active
                ? 'Coupon activated successfully'
                : 'Coupon deactivated successfully';

            $this->dispatch('notify', [
                'message' => $statusMessage,
                'type' => 'success',
            ]);

            $this->dispatch('pg:eventRefresh-coupon-table');
        }
    }

    public function confirmDelete($id)
    {
        $this->deletingCouponId = $id;
        $this->confirmingDeletion = true;
    }

    public function deleteCoupon()
    {
        if ($this->deletingCouponId) {
            $coupon = Coupon::find($this->deletingCouponId);

            if ($coupon) {
                $coupon->delete();
                $this->confirmingDeletion = false;

                $this->dispatch('notify', [
                    'message' => 'Coupon deleted successfully',
                    'type' => 'success',
                ]);
                $this->dispatch('pg:eventRefresh-coupon-table');

                $this->deletingCouponId = null;
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.coupon.coupon-index');
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-coupon-table');
    }
}
