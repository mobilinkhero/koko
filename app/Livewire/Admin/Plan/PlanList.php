<?php

namespace App\Livewire\Admin\Plan;

use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;

class PlanList extends Component
{
    use WithPagination;

    // Set the theme for pagination (tailwind or bootstrap)
    protected $paginationTheme = 'tailwind';

    public $showActiveOnly = false;

    // Change the default sorting to price
    public $sortField = 'price';

    public $sortDirection = 'asc';

    // Current page for pagination
    public $page = 1;

    public $confirmingDeletion = false;

    public $planId;

    protected $planService;

    protected $listeners = [
        'confirmDelete' => 'confirmDelete',
        'planUpdated' => '$refresh',
    ];

    public function boot(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function mount()
    {
        if (! checkPermission('admin.plans.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return $this->redirect(route('admin.dashboard'), navigate: true);
        }

        // Clear the plan feature cache to ensure we have the latest data
        \App\Services\PlanFeatureCache::clearCache();
    }

    public function updatedShowActiveOnly()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDelete($id)
    {
        $this->planId = $id;
        $this->confirmingDeletion = true;
    }

    public function editPlan($planId)
    {
        return $this->redirect(route('admin.plans.create', ['planId' => $planId]), navigate: true);
    }

    public function updateStatus($planId, $newStatus)
    {
        try {
            $plan = Plan::findOrFail($planId);
            $plan->is_active = $newStatus;
            $plan->save();

            Artisan::call('cache:clear');
            $message = $newStatus
                ? t('plan_activated_successfully')
                : t('plan_deactivated_successfully');

            $this->notify(['type' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => t('failed_to_update_plan_status')]);
        }
    }

    public function delete()
    {
        if (checkPermission('admin.plans.delete')) {
            try {
                $this->planService->deletePlan($this->planId);
                Artisan::call('cache:clear');
                $this->confirmingDeletion = false;
                $this->notify(['type' => 'success', 'message' => t('plan_deleted_successfully')], true);

                return $this->redirect(route('admin.plans.list'), navigate: true);
            } catch (\Exception $e) {
                $this->confirmingDeletion = false;
                $this->notify([
                    'type' => 'danger',
                    'message' => t('failed_to_delete_plan').': '.$e->getMessage(),
                ]);
            }
        }
    }

    public function render()
    {
        // If we have active filter or custom sorting, use the database query
        // Otherwise, we can use our optimized cache for better performance
        if ($this->showActiveOnly || $this->sortField !== 'price' || $this->sortDirection !== 'asc') {
            // Use database query when filtering or custom sorting is needed
            $query = Plan::with([
                'planFeatures:id,plan_id,feature_id,name,slug,value',
                'planFeatures.feature:id,name,slug,type,display_order',
            ])->select(['id', 'name', 'slug', 'description', 'price', 'billing_period', 'is_active', 'is_free', 'featured', 'trial_days', 'color']);

            // Apply active filter
            if ($this->showActiveOnly) {
                $query->where('is_active', true);
            }

            // Handle sorting
            if ($this->sortField === 'price') {
                // Special sorting for price field - order by price ascending
                $plans = $query->orderBy('price', $this->sortDirection)
                    ->paginate(10);
            } else {
                // For other fields, use the normal sorting
                $plans = $query->orderBy($this->sortField, $this->sortDirection)
                    ->paginate(10);
            }

            // Append whatsapp_webhook feature to each plan's planFeatures collection
            foreach ($plans as $plan) {
                if (isset($plan->planFeatures) && is_iterable($plan->planFeatures)) {
                    $plan->planFeatures->push((object) [
                        'name' => 'Whatsapp Webhook',
                        'slug' => 'whatsapp_webhook',
                        'value' => '-1',
                    ]);
                }

                $plan = apply_filters('before_rendar_plan_list', $plan);
            }
        } else {
            // No search/filter/custom sort - use our optimized cache for better performance
            // Get all plans from cache
            $plansCollection = \App\Services\PlanFeatureCache::getAllPlansWithFeatures();

            // Convert to array for proper pagination (required for Livewire)
            $plansArray = $plansCollection->values()->all();

            foreach ($plansArray as $plan) {
                if (isset($plan->planFeatures) && is_iterable($plan->planFeatures)) {
                    $plan->planFeatures->push((object) [
                        'name' => 'Whatsapp Webhook',
                        'slug' => 'whatsapp_webhook',
                        'value' => '-1',
                    ]);
                }

                $plan = apply_filters('before_rendar_plan_list', $plan);
            }

            unset($plan);

            // Use Livewire's paginate() method on a collection
            $plans = new \Illuminate\Pagination\LengthAwarePaginator(
                collect($plansArray)->forPage($this->page, 10),
                count($plansArray),
                10,
                $this->page,
                ['path' => url()->current()]
            );
        }

        return view('livewire.admin.plan.plan-list', compact('plans'));
    }
}
