<?php

namespace App\Traits;

use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;

trait HasSubscription
{
    /**
     * Subscribe tenant to a plan.
     */
    public function subscribeToPlan(Plan $plan, array $options = []): Subscription
    {
        $trialDays = $options['trial_days'] ?? $plan->trial_days;
        $trialEndsAt = null;

        if ($trialDays > 0) {
            $trialEndsAt = Carbon::now()->addDays($trialDays);
        }

        // Cancel any existing subscriptions
        $this->cancelCurrentSubscription();

        // Create new subscription
        return $this->subscriptions()->create([
            'plan_id' => $plan->id,
            'name' => $plan->name,
            'stripe_id' => $options['stripe_id'] ?? null,
            'stripe_status' => $options['stripe_status'] ?? 'active',
            'stripe_price' => $options['stripe_price'] ?? null,
            'quantity' => $options['quantity'] ?? 1,
            'trial_ends_at' => $trialEndsAt,
            'billing_period' => $options['billing_period'] ?? $plan->interval,
            'price' => $options['price'] ?? $plan->price,
            'payment_method_id' => $options['payment_method_id'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);
    }

    /**
     * Cancel the current subscription.
     *
     * @return $this
     */
    public function cancelCurrentSubscription(bool $immediately = false): self
    {
        $subscription = $this->activeSubscription;

        if ($subscription->exists && ! $subscription->canceled()) {
            // If immediately, set ends_at to now, otherwise to the end of the current period
            $endsAt = $immediately ? Carbon::now() : $subscription->ends_at;

            $subscription->update([
                'ends_at' => $endsAt,
            ]);
        }

        return $this;
    }

    /**
     * Determine if the tenant has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        $subscription = $this->activeSubscription;

        return $subscription->exists && ($subscription->isActive() && ! $subscription->ended());
    }

    /**
     * Determine if the tenant is on trial.
     */
    public function onTrial(): bool
    {
        $subscription = $this->activeSubscription;

        return $subscription->exists && $subscription->onTrial();
    }

    /**
     * Get the current plan of the tenant.
     */
    public function getCurrentPlan(): ?Plan
    {
        $subscription = $this->activeSubscription;

        if (! $subscription->exists) {
            return null;
        }

        return $subscription->plan;
    }

    /**
     * Check if tenant can use a specific feature.
     */
    public function canUseFeature(string $feature): bool
    {
        $plan = $this->getCurrentPlan();

        if (! $plan) {
            return false;
        }

        return $plan->hasFeature($feature);
    }

    /**
     * Get usage limit for a specific feature.
     */
    public function getFeatureLimit(string $feature): ?int
    {
        $plan = $this->getCurrentPlan();

        if (! $plan) {
            return 0;
        }

        return $plan->getFeatureValue($feature);
    }
}
