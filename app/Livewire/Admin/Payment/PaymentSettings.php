<?php

namespace App\Livewire\Admin\Payment;

use Livewire\Component;

class PaymentSettings extends Component
{
    public function mount()
    {
        if (! checkPermission('admin.payment_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function render()
    {
        return view('livewire.admin.payment.payment-settings');
    }
}
