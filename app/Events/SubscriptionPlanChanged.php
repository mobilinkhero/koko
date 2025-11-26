<?php

namespace App\Events;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionPlanChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The subscription instance.
     *
     * @var \App\Models\Subscription
     */
    public $subscription;

    /**
     * The new plan instance.
     *
     * @var \App\Models\Plan
     */
    public $plan;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription, Plan $plan)
    {
        $this->subscription = $subscription;
        $this->plan = $plan;
    }
}
