<?php

namespace App\Livewire\Tenant\TenantSubscription;

use App\Enum\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Repositories\SubscriptionRepository;
use Livewire\Component;

class BillingDetails extends Component
{
    public $plan_id;

    protected $queryString = ['plan_id'];

    public $enabledGateways = [];

    public $offlinePaymentMethod;

    public $paymentDate;

    public $submitted_at;

    public $payment_method;

    public $payment_id;

    public $paymentDetails = [];

    // Stripe specific properties
    public $isProcessing = false;

    public $stripeClientSecret;

    public $stripeCheckoutUrl;

    public $errorMessage;

    // Shared properties
    public $plan;

    public $tenant;

    public function mount()
    {
        $subscriptionRepository = app(SubscriptionRepository::class);
        $this->plan_id = request()->query('plan_id');
        $this->plan = Plan::find($this->plan_id);
        $this->tenant = current_tenant();
        $tenantId = $this->tenant->id;

        // Handle invalid plan
        if (! $this->plan) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('invalid_plan_id'),
            ]);

            return redirect()->to(tenant_route('tenant.subscription'));
        }

        // Check for any pending subscription first
        $pendingSubscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', SubscriptionStatus::PENDING)
            ->latest()
            ->first();

        if ($pendingSubscription) {
            session()->flash('notification', [
                'type' => 'warning',
                'message' => t('already_pending_subscription'),
            ]);

            return redirect()->to(tenant_route('tenant.subscription.pending'));
        }

        // Continue with the rest of your existing logic...
        $isFreePlan = $this->plan->price == 0;

        // to check for existing trial or subscription
        if ($isFreePlan && $subscriptionRepository->previousTrialExists($tenantId)) {
            session()->flash('notification', [
                'type' => 'warning',
                'message' => t('you_already_used_the_free_plan'),
            ]);

            return redirect()->to(tenant_route('tenant.subscription'));
        }

        // create a trial
        if ($isFreePlan) {
            $trialDays = $this->plan->trial_days ?? 14; // fallback to 14 if not defined
            $subscriptionRepository->createTrial($tenantId, $this->plan_id, $trialDays);

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('free_trial_subscription_activate'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $paymentSettingsKeys = [
            'payment.stripe_enabled',
            'payment.offline_enabled',
            'payment.razorpay_enabled',
            'payment.paystack_enabled',
            'payment.stripe_webhook_id',
        ];

        // Allow modules to add additional payment settings keys
        $paymentSettingsKeys = apply_filters('billing_details.payment_settings_keys', $paymentSettingsKeys);

        $paymentSettings = get_batch_settings($paymentSettingsKeys);

        // Load enabled gateways
        $this->enabledGateways = [
            'stripe' => ($paymentSettings['payment.stripe_enabled'] && ! empty($paymentSettings['payment.stripe_webhook_secret'])) ? true : false,
            'offline' => $paymentSettings['payment.offline_enabled'] ?? false,
            'razorpay' => $paymentSettings['payment.razorpay_enabled'] ?? false,
            'paystack' => $paymentSettings['payment.paystack_enabled'] ?? false,
        ];

        // Allow modules to add additional gateways
        $this->enabledGateways = apply_filters('billing_details.enabled_gateways', $this->enabledGateways, $paymentSettings);

        $this->paymentDate = now()->format('Y-m-d');
    }

    public function createStripeCheckout()
    {
        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            // Get the billing manager
            $billingManager = app('billing.manager');
            $stripeGateway = $billingManager->gateway('stripe');

            if (! $stripeGateway) {
                throw new \Exception('Stripe gateway not available.');
            }

            // Define success and cancel URLs
            $successUrl = tenant_route('tenant.checkout.success', [
                'plan' => $this->plan->id,
                'provider' => 'stripe',
            ]);

            $cancelUrl = tenant_route('tenant.checkout.cancel', [
                'plan' => $this->plan->id,
            ]);

            // Create checkout session - either subscription or one-time payment
            if (method_exists($stripeGateway, 'createSubscriptionCheckout')) {
                // For subscription-based payments
                $checkout = $stripeGateway->createSubscriptionCheckout(
                    $this->tenant,
                    $this->plan,
                    $this->plan->billing_period,
                    $successUrl,
                    $cancelUrl
                );

            } else {
                // Fall back to regular checkout if subscription method doesn't exist
                $checkout = $stripeGateway->createCheckout(
                    $this->tenant,
                    $this->plan,
                    $this->plan->billing_period,
                    $successUrl,
                    $cancelUrl
                );
            }

            // Save the checkout URL and redirect the user
            $this->stripeCheckoutUrl = $checkout['url'];
            $this->dispatch('redirectToStripe', ['url' => $this->stripeCheckoutUrl]);

        } catch (\Exception $e) {

            payment_log('Error creating Stripe checkout session: ', 'error', [
                'tenant_id' => tenant_id(),
                'plan_id' => $this->plan_id,
                'billing_period' => $this->plan->billing_period,
                'error' => $e->getMessage(),
            ]);

            $this->errorMessage = t('error_while_setting_payment ').$e->getMessage();
            $this->isProcessing = false;
        }
    }

    public function createRazorpayCheckout()
    {
        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            payment_log('BillingDetails: Starting Razorpay checkout creation');

            // Get the billing manager
            $billingManager = app('billing.manager');
            $razorpayGateway = $billingManager->gateway('razorpay');

            if (! $razorpayGateway) {
                throw new \Exception('Razorpay gateway not available.');
            }

            payment_log('BillingDetails: Billing manager retrieved successfully');

            // Create subscription using the proper flow
            $subscriptionManager = app(\App\Services\Subscription\SubscriptionManager::class);
            $subscription = $subscriptionManager->createSubscription(tenant_id(), $this->plan_id);

            payment_log('BillingDetails: Subscription created with ID: '.$subscription->id);

            // Get the unpaid invoice
            $invoice = $subscription->getUnpaidInitInvoice();

            if (! $invoice) {
                throw new \Exception('Failed to create invoice for subscription');
            }

            payment_log('BillingDetails: Invoice retrieved with ID: '.$invoice->id);

            // Get checkout URL using the actual invoice
            $checkoutUrl = $razorpayGateway->getCheckoutUrl($invoice);

            payment_log('BillingDetails: Checkout URL generated: '.$checkoutUrl);

            $this->dispatch('redirectToRazorpay', ['url' => $checkoutUrl]);

        } catch (\Exception $e) {
            payment_log('Error creating Razorpay checkout session: '.$e->getMessage(), 'error', [
                'tenant_id' => tenant_id(),
                'plan_id' => $this->plan_id,
                'billing_period' => $this->plan->billing_period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->errorMessage = t('error_while_setting_payment ').$e->getMessage();
            $this->isProcessing = false;
        }
    }

    public function processOfflinePayment()
    {
        $this->validate([
            'offlinePaymentMethod' => 'required|string',
            'payment_id' => 'required|string',
        ]);

        $this->paymentDetails = [
            'offlinePaymentMethod' => $this->offlinePaymentMethod,
        ];

        // Set purchase date
        $purchaseDate = get_super_admin_current_time();

        // Calculate ends_at based on billing cycle
        $endsAt = null;
        if ($this->plan->billing_period === 'monthly') {
            $endsAt = $purchaseDate->copy()->addDays(30);
        } elseif ($this->plan->billing_period === 'yearly') {
            $endsAt = $purchaseDate->copy()->addDays(365);
        }

        // Create subscription with pending status
        $subscription = $this->tenant->subscriptions()->create([
            'plan_id' => $this->plan_id,
            'price' => $this->plan->price,
            'status' => SubscriptionStatus::PENDING,
            'payment_method' => $this->payment_method,
            'payment_id' => $this->payment_id,
            'payment_details' => $this->paymentDetails,
            'billing_cycle' => $this->plan->billing_period,
            'purchase_date' => $purchaseDate,
            'ends_at' => $endsAt,
        ]);

        $this->notify([
            'type' => 'success',
            'message' => t('subscription_request_submit ').$this->payment_id.t('pending_approval_from_team'),
        ]);

        return redirect()->to(tenant_route('tenant.subscription.pending'));
    }

    public function getOfflinePaymentMethodsProperty()
    {
        return [
            'bank_transfer' => 'Bank Transfer',
            'cash_transfer' => 'Cash Transfer',
            'cheque_payment' => 'Cheque Payment',
        ];
    }

    public function render()
    {
        return view('livewire.tenant.tenant-subscription.billing-details', [
            'plan' => $this->plan,
            'interval' => $this->plan->billing_period === 'yearly' ? 'year' : 'month',
        ]);
    }
}
