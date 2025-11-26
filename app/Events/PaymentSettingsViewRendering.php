<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PaymentSettingsViewRendering
{
    use Dispatchable;

    public array $paymentGateways = [];

    public function addPaymentGateway(string $html): void
    {
        $this->paymentGateways[] = $html;
    }
}
