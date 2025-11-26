<?php

namespace App\Livewire;

use App\Models\Subscription;
use App\Services\Subscription\SubscriptionManager as SubscriptionService;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionManager extends Component
{
    use WithPagination;

    /**
     * Show active subscriptions only.
     *
     * @var bool
     */
    public $activeOnly = true;

    /**
     * The subscription being cancelled.
     *
     * @var string|null
     */
    public $cancellingSubscription = null;

    /**
     * The subscription being modified (recurring toggle).
     *
     * @var string|null
     */
    public $modifyingSubscription = null;

    /**
     * Boolean toggle for recurring.
     *
     * @var bool
     */
    public $isRecurring = true;

    /**
     * Confirmation modal open state.
     *
     * @var bool
     */
    public $showConfirmModal = false;

    /**
     * Confirmation modal message.
     *
     * @var string
     */
    public $confirmMessage = '';

    /**
     * Confirmation modal action.
     *
     * @var string
     */
    public $confirmAction = '';

    /**
     * Event listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refreshSubscriptions' => '$refresh',
    ];

    /**
     * Toggle active only filter.
     *
     * @return void
     */
    public function toggleActiveOnly()
    {
        $this->activeOnly = ! $this->activeOnly;
        $this->resetPage();
    }

    /**
     * Show the cancel confirmation modal.
     *
     * @return void
     */
    public function confirmCancel(string $subscriptionId)
    {
        $this->cancellingSubscription = $subscriptionId;
        $this->confirmMessage = t('cancel_subscription_desc');
        $this->confirmAction = 'cancelSubscription';
        $this->showConfirmModal = true;
    }

    /**
     * Show the recurring toggle confirmation modal.
     *
     * @return void
     */
    public function confirmToggleRecurring(string $subscriptionId, string $isRecurring)
    {
        $this->modifyingSubscription = $subscriptionId;
        $this->isRecurring = $isRecurring === 'true';

        $action = $this->isRecurring ? 'enable' : 'disable';
        $this->confirmMessage = "Are you sure you want to {$action} auto-renewal for this subscription?";
        $this->confirmAction = 'toggleRecurring';
        $this->showConfirmModal = true;
    }

    /**
     * Close the confirmation modal.
     *
     * @return void
     */
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->cancellingSubscription = null;
        $this->modifyingSubscription = null;
        $this->confirmMessage = '';
        $this->confirmAction = '';
    }

    /**
     * Cancel a subscription.
     *
     * @return void
     */
    public function cancelSubscription()
    {
        try {
            $subscriptionManager = app(SubscriptionService::class);
            $subscriptionManager->cancelSubscription(
                $this->cancellingSubscription,
                tenant_id()
            );

            session()->flash('success', t('subscription_cancelled_successfully'));
        } catch (\Exception $e) {

            payment_log('Failed to cancel subscription', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', t('failed_cancel_subscription ').$e->getMessage());
        }

        $this->closeConfirmModal();
    }

    /**
     * Toggle recurring billing for a subscription.
     *
     * @return void
     */
    public function toggleRecurring()
    {
        try {
            $subscriptionManager = app(SubscriptionService::class);
            $subscriptionManager->toggleRecurring(
                $this->modifyingSubscription,
                tenant_id(),
                $this->isRecurring
            );

            $action = $this->isRecurring ? 'enabled' : 'disabled';
            session()->flash('success', "Auto-renewal {$action} successfully.");
        } catch (\Exception $e) {
            payment_log('Failed to modify subscription', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', t('failed_to_modify_subscription').$e->getMessage());
        }

        $this->closeConfirmModal();
    }

    /**
     * Get the subscriptions.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSubscriptions()
    {
        $query = Subscription::query()
            ->with([
                'plan',
                'invoices' => function ($query) {
                    $query->latest();
                },
            ])
            ->where('tenant_id', tenant_id());

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.subscription-manager', [
            'subscriptions' => $this->getSubscriptions(),
        ]);
    }
}
