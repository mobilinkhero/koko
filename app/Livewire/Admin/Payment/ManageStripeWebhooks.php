<?php

namespace App\Livewire\Admin\Payment;

use App\Models\PaymentWebhook;
use App\Services\StripeWebhookService;
use Livewire\Component;

class ManageStripeWebhooks extends Component
{
    public $stripeWebhooks = [];

    public $localWebhooks = [];

    public $isLoading = false;

    public $isConfiguring = false;

    public $customUrl = '';

    public $selectedEvents = [];

    public $allEvents = [
        'checkout.session.completed' => 'Checkout Session Completed',
        'customer.deleted' => 'Customer Deleted',
        'customer.subscription.created' => 'Subscription Created',
        'customer.subscription.deleted' => 'Subscription Deleted',
        'customer.subscription.updated' => 'Subscription Updated',
        'invoice.payment_action_required' => 'Payment Action Required',
        'invoice.payment_failed' => 'Payment Failed',
        'invoice.payment_succeeded' => 'Payment Succeeded',
        'payment_intent.payment_failed' => 'Payment Intent Failed',
        'payment_intent.succeeded' => 'Payment Intent Succeeded',
        'subscription_schedule.canceled' => 'Subscription Schedule Canceled',
        'subscription_schedule.created' => 'Subscription Schedule Created',
        'subscription_schedule.updated' => 'Subscription Schedule Updated',
        'customer.updated' => 'Customer updated',
        'payment_method.attached' => 'Update default payment method',
        'payment_method.detached' => 'Remove default payment method',
    ];

    public $showDeleteModal = false;

    public $webhookToDelete = null;

    public $configurationResult = null;

    // New property to track if webhook details card is expanded
    public $showWebhookDetails = false;

    // New property to store selected webhook for details
    public $selectedWebhook = null;

    protected $webhookService;

    /**
     * Create a new component instance.
     */
    public function boot(StripeWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Mount the component.
     */
    public function mount()
    {
        $this->customUrl = route('webhook.stripe');
        $this->loadWebhooks();

        // Load current event selections from the latest webhook
        $latestWebhook = PaymentWebhook::forProvider('stripe')
            ->active()
            ->latest()
            ->first();

        $this->selectedEvents = array_keys($this->allEvents);
        // if ($latestWebhook) {
        //     $this->selectedEvents = $latestWebhook->getEventsArray();
        // } else {
        //     // Default selections
        //     $this->selectedEvents = array_keys($this->allEvents);
        // }
    }

    /**
     * Load webhooks from Stripe and the local database.
     */
    public function loadWebhooks()
    {
        $this->isLoading = true;

        try {
            // Get webhooks from Stripe
            $result = $this->webhookService->listWebhooks();
            if ($result['success']) {
                $settings = get_batch_settings(['payment.stripe_webhook_id']);
                $webhookId = $settings['payment.stripe_webhook_id'];
                if (! empty($webhookId)) {
                    $webhookData = $this->webhookService->getWebhookDetails($webhookId);
                    if ($webhookData['success']) {
                        $this->stripeWebhooks = $webhookData['webhook'];
                    }
                }
            }

            // Get webhooks from database
            $this->localWebhooks = PaymentWebhook::forProvider('stripe')->get();
        } catch (\Exception $e) {
            payment_log('Failed to load stripe webhooks', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_load_webhooks').$e->getMessage(),
            ]);

        }

        $this->isLoading = false;
    }

    /**
     * Show webhook details
     *
     * @param  string  $webhookId
     */
    public function viewWebhookDetails($webhookId)
    {
        try {
            $result = $this->webhookService->getWebhookDetails($webhookId);

            if ($result['success']) {
                $this->selectedWebhook = $result['webhook'];
                $this->showWebhookDetails = true;
            } else {
                $this->notify([
                    'type' => 'danger',
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            payment_log('Failed to get stripe webhook details', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            $this->notify([
                'type' => 'danger',
                'message' => t('get_webhook_details').$e->getMessage(),
            ]);
        }
    }

    /**
     * Configure a new webhook.
     */
    public function configureWebhook()
    {
        $this->isConfiguring = true;

        try {
            // Validate at least one event is selected
            if (empty($this->selectedEvents)) {
                throw new \Exception('You must select at least one event to listen for.');
            }

            // Configure the webhook
            $result = $this->webhookService->ensureWebhooksAreConfigured(
                $this->customUrl,
                $this->selectedEvents
            );

            $this->configurationResult = $result;

            if ($result['success']) {
                $this->notify([
                    'type' => 'success',
                    'message' => $result['message'],
                ]);

                // Refresh the webhook lists
                $this->loadWebhooks();
            } else {
                $this->notify([
                    'type' => 'danger',
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            payment_log('Failed to configure stripe webhooks', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_configure_webhook').$e->getMessage(),
            ]);
        }

        $this->isConfiguring = false;
    }

    /**
     * Confirm webhook deletion.
     *
     * @param  string  $webhookId
     */
    public function confirmDeleteWebhook($webhookId)
    {
        $this->webhookToDelete = $webhookId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a webhook.
     */
    public function deleteWebhook()
    {
        if (! $this->webhookToDelete) {
            return;
        }

        try {
            $result = $this->webhookService->deleteWebhook($this->webhookToDelete);

            if ($result['success']) {
                $this->notify([
                    'type' => 'success',
                    'message' => $result['message'],
                ]);

                // Refresh the webhook lists
                $this->loadWebhooks();
            } else {
                $this->notify([
                    'type' => 'danger',
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            payment_log('Failed to delete stripe webhook', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_delete_webhook').$e->getMessage(),
            ]);
        }

        $this->webhookToDelete = null;
        $this->showDeleteModal = false;
    }

    /**
     * Cancel delete confirmation.
     */
    public function cancelDelete()
    {
        $this->webhookToDelete = null;
        $this->showDeleteModal = false;
    }

    /**
     * Select or deselect all events.
     *
     * @param  bool  $selected
     */
    public function selectAllEvents($selected)
    {
        $this->selectedEvents = $selected ? array_keys($this->allEvents) : [];
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.admin.payment.manage-stripe-webhooks');
    }
}
