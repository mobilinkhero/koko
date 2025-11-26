<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpgraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The subscription instance.
     *
     * @var \App\Models\Subscription
     */
    public $subscription;

    /**
     * The previous plan ID.
     *
     * @var int
     */
    public $previousPlanId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription, int $previousPlanId)
    {
        $this->subscription = $subscription;
        $this->previousPlanId = $previousPlanId;
    }
}
