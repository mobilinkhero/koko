<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled
{
    use Dispatchable, SerializesModels;

    public $subscriptionId;

    public function __construct($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
    }
}
