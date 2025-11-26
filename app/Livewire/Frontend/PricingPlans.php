<?php

namespace App\Livewire\Frontend;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PricingPlans extends Component
{
    public $plansFeatures;

    public $selectedPlan = null;

    public $billing_period;

    public $billingCycle = 'monthly';

    public $pricingSettings = [];

    public $showBillingToggle = false;

    public $defaultBillingPeriod = 'monthly';

    public function mount()
    {
        $planRepo = app(PlanRepository::class);
        $this->pricingSettings = get_batch_settings([
            'theme.pricing_section_title',
            'theme.pricing_section_subtitle',
        ]);

        $this->plansFeatures = collect($planRepo->getPlansWithFeatures())
            ->where('is_active', true)
            ->sortBy([
                ['is_free', 'desc'],    // Free plans first (1 before 0)
                ['price', 'asc'],       // Highest price first
            ])
            ->values();

        // Determine if billing toggle should be shown
        $this->showBillingToggle = Plan::hasBothBillingPeriods();
        $this->defaultBillingPeriod = Plan::getDefaultBillingPeriod();
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = $planId;
        // Store the selected plan in session
        session()->put('selected_plan_id', $planId);
        $this->dispatch('planSelected', planId: $planId);
    }

    public function checkout()
    {
        if (! $this->selectedPlan) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('select_plan_continue'),
            ]);

            return;
        }

        $plan = Plan::findOrFail($this->selectedPlan);

        if (Auth::check()) {
            // User is logged in, redirect to checkout
            return redirect()->to(tenant_route('tenant.billing', ['plan_id' => $plan->id]));
        } else {
            // User is not logged in, redirect to register with plan
            return redirect()->route('register', ['plan_id' => $plan->id]);
        }
    }

    public function render()
    {
        return view('livewire.frontend.pricing-plans');
    }
}
