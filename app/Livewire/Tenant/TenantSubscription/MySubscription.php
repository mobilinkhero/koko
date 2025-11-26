<?php

namespace App\Livewire\Tenant\TenantSubscription;

use App\Models\Plan;
use App\Models\Subscription;
use App\Repositories\PlanRepository;
use App\Repositories\SubscriptionRepository;
use Livewire\Component;

class MySubscription extends Component
{
    public $plansFeatures;

    public $billingPeriod = 'monthly';

    public $currentPlanId = null;

    public $filteredPlans = []; // Added property to store plans

    public $showBillingToggle = false;

    public $defaultBillingPeriod = 'monthly';

    public function mount()
    {
        if (! tenant_on_active_plan()) {
            // If tenant is already on an active plan, redirect to the dashboard
            return redirect()->to(tenant_route('tenant.subscriptions'));
        }
        // Use our new TenantCache service to get the tenant ID reliably
        $tenantId = tenant_id();

        // Preload subscription data into cache first to avoid separate queries later
        \App\Services\SubscriptionCache::loadSubscriptions($tenantId);

        // Check if there's a pending subscription using the cache
        if (\App\Services\SubscriptionCache::hasPendingSubscription($tenantId)) {
            return redirect()->to(tenant_route('tenant.subscription.pending'));
        }

        // Load current subscription info from cache
        $subscription = \App\Services\SubscriptionCache::getActiveSubscription($tenantId);

        if ($subscription && $subscription->plan_id) {
            $this->currentPlanId = $subscription->plan_id;
            $this->billingPeriod = strtolower(optional($subscription->plan)->billing_period ?? 'monthly');
        }

        // Determine if billing toggle should be shown
        $this->showBillingToggle = Plan::hasBothBillingPeriods();
        $this->defaultBillingPeriod = Plan::getDefaultBillingPeriod();

        // Pre-load filtered plans to avoid calling it in render
        $this->loadFilteredPlans();
    }

    /**
     * Load filtered plans using the optimized repository method
     */
    protected function loadFilteredPlans()
    {
        $planRepo = app(PlanRepository::class);
        $subscriptionRepo = app(SubscriptionRepository::class);
        $tenantId = tenant_id();

        $hasAnySubscription = $subscriptionRepo->hasAnySubscription($tenantId);
        if ($hasAnySubscription) {
            $this->filteredPlans = $planRepo->getAvailablePlansOptimized(true);
        } else {
            $this->filteredPlans = $planRepo->getAvailablePlansOptimized(false);
        }
    }

    /**
     * Handle free plan subscription
     */
    public function startFreeTrial($planId)
    {
        $subscriptionRepo = app(SubscriptionRepository::class);
        $plan = Plan::find($planId);
        $tenant = current_tenant();

        if (! $plan || $plan->price > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => t('invalid_plan_selected'),
            ]);

            return;
        }

        // Check if tenant already had a subscription
        $hasHadAnySubscription = $subscriptionRepo->hasAnySubscription($tenant->id);
        if ($hasHadAnySubscription) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => t('you_already_used_the_free_plan'),
            ]);

            return;
        }

        $subscriptionRepo->createTrial($tenant->id, $plan->id, $plan->trial_days);

        return redirect()->to(tenant_route('tenant.dashboard'));
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'You are now on a free trial for '.($plan->trial_days ?? 14).' days.',
        ]);

        return redirect()->to(tenant_route('tenant.dashboard'));
    }

    /**
     * We don't need this method anymore as we're pre-loading plans in mount()
     */
    public function getFilteredPlans()
    {
        return $this->filteredPlans;
    }

    /**
     * Return the pre-loaded filtered plans
     */
    public function getFilteredPlansProperty()
    {
        return $this->filteredPlans;
    }

    /**
     * Get the featured plan from our pre-loaded filtered plans
     */
    public function getFeaturedProperty()
    {
        // Find the featured plan from the pre-loaded plans
        foreach ($this->filteredPlans as $plan) {
            if ($plan->featured) {
                return $plan;
            }
        }

        // If no featured plan found, return the first plan
        return ! empty($this->filteredPlans) ? $this->filteredPlans[0] : null;
    }

    public function render()
    {
        return view('livewire.tenant.tenant-subscription.my-subscription');
    }
}
