<?php

namespace App\Events;

use App\Services\Billing\BillingManager;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentGatewayRegistration
{
    use Dispatchable;

    public function __construct(
        public BillingManager $billingManager
    ) {}
}
