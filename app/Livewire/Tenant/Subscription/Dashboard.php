<?php

namespace App\Livewire\Tenant\Subscription;

use App\Models\Subscription;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $subscription;

    public $transactions;

    public $upcomingInvoice;

    public $pastInvoices;

    public $isLoading = true;

    public $pauseUntilDate = null;

    public $showPauseModal = false;

    public $showResumeModal = false;

    public $cancelAtPeriodEnd = false;

    public $showCancelModal = false;

    /**
     * Settings loaded once to avoid multiple database calls
     */
    protected $paymentSettings;

    public function mount()
    {
        // Load all payment settings in a single batch call
        $this->paymentSettings = get_batch_settings([
            'payment.stripe_secret',
        ]);

        $this->loadSubscriptionData();
    }

    public function loadSubscriptionData()
    {
        $this->isLoading = true;

        // Get active subscription for the current tenant
        $this->subscription = Subscription::where('tenant_id', tenant_id())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($this->subscription) {
            // Get transactions from the transactions table
            $this->transactions = Transaction::where('invoice_id', function ($query) {
                $query->select('id')
                    ->from('invoices')
                    ->where('subscription_id', $this->subscription->id);
            })
                ->orderBy('created_at', 'desc')
                ->get();

            // Get upcoming invoice from Stripe if available
            if ($this->subscription->stripe_subscription_id) {
                $this->upcomingInvoice = $this->getUpcomingInvoice();
            }

            // Set cancel flag
            $this->cancelAtPeriodEnd = $this->subscription->cancel_at_period_end;
        }

        $this->isLoading = false;
    }

    protected function getUpcomingInvoice()
    {
        try {
            \Stripe\Stripe::setApiKey($this->paymentSettings['payment.stripe_secret'] ?? '');

            $upcoming = \Stripe\Invoice::upcoming([
                'subscription' => $this->subscription->stripe_subscription_id,
            ]);

            if ($upcoming) {
                return [
                    'amount' => $upcoming->amount_due / 100, // Convert from cents
                    'currency' => strtoupper($upcoming->currency),
                    'date' => Carbon::createFromTimestamp($upcoming->created)->format('M d, Y'),
                    'period_start' => Carbon::createFromTimestamp($upcoming->period_start)->format('M d, Y'),
                    'period_end' => Carbon::createFromTimestamp($upcoming->period_end)->format('M d, Y'),
                ];
            }
        } catch (\Exception $e) {
            // No upcoming invoice or error
        }

        return null;
    }

    public function openPauseModal()
    {
        // Set default pause date to 1 month from now
        $this->pauseUntilDate = Carbon::now()->addMonth()->format('Y-m-d');
        $this->showPauseModal = true;
    }

    public function pauseSubscription()
    {
        $this->validate([
            'pauseUntilDate' => 'required|date|after:today',
        ]);

        try {
            // Convert to Carbon date
            $resumeDate = Carbon::parse($this->pauseUntilDate);

            // Get Stripe gateway service
            $gateway = app(\App\Services\PaymentGateways\StripeGateway::class);

            // Pause subscription
            $result = $gateway->pauseSubscription($this->subscription, $resumeDate);

            if ($result) {
                $this->notify([
                    'type' => 'success',
                    'message' => t('subscription_has_been_paused ').$resumeDate->format('M d, Y'),
                ]);

                $this->loadSubscriptionData();
            } else {
                $this->notify([
                    'type' => 'error',
                    'message' => t('problem_pausing_your_subscription'),
                ]);
            }
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }

        $this->showPauseModal = false;
    }

    public function openResumeModal()
    {
        $this->showResumeModal = true;
    }

    public function resumeSubscription()
    {
        try {
            // Get Stripe gateway service
            $gateway = app(\App\Services\PaymentGateways\StripeGateway::class);

            // Resume subscription
            $result = $gateway->resumeSubscription($this->subscription);

            if ($result) {
                $this->notify([
                    'type' => 'success',
                    'message' => t('subscription_has_been_resumed'),
                ]);

                $this->loadSubscriptionData();
            } else {
                $this->notify([
                    'type' => 'error',
                    'message' => t('problem_resuming_your_subscription'),
                ]);
            }
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }

        $this->showResumeModal = false;
    }

    public function openCancelModal()
    {
        $this->showCancelModal = true;
    }

    public function cancelSubscription()
    {
        try {
            // Get Stripe gateway service
            $gateway = app(\App\Services\PaymentGateways\StripeGateway::class);

            // Cancel subscription at period end
            $result = $gateway->cancelSubscription($this->subscription, true);

            if ($result) {
                $this->notify([
                    'type' => 'success',
                    'message' => t('your_subscription_has_been_cancelled'),
                ]);

                $this->loadSubscriptionData();
            } else {
                $this->notify([
                    'type' => 'error',
                    'message' => t('problem_cancelling_your_subscription'),
                ]);
            }
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }

        $this->showCancelModal = false;
    }

    public function render()
    {
        return view('livewire.tenant.subscription.dashboard');
    }
}
