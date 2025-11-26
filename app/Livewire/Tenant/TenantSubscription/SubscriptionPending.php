<?php

namespace App\Livewire\Tenant\TenantSubscription;

use App\Facades\TenantCache;
use App\Models\Invoice\Invoice;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SubscriptionCache;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriptionPending extends Component
{
    public $subscription;

    public $transaction;

    public $currentSubscription; // To store active subscription info

    public $previousStatus = null;

    public $supportEmail;

    public function mount()
    {
        $this->checkSubscriptionStatus();

        // Load system settings in batch
        $systemSettings = get_batch_settings(['system.company_email']);
        $this->supportEmail = $systemSettings['system.company_email'] ?? '';

        if (empty($this->supportEmail)) {
            // If company email setting is empty, get the admin email
            $adminUser = User::where('user_type', 'admin')
                ->where('is_admin', 1)
                ->first();

            $this->supportEmail = $adminUser ? $adminUser->email : '';
        }
    }

    public function checkSubscriptionStatus()
    {
        $tenantId = Auth::user()->tenant_id;

        // Check for new subscription (first time subscription)
        $this->subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'new')
            ->orWhere('status', 'ended')
            ->with('plan')
            ->latest()
            ->first();

        $latestSubscription = Subscription::where('tenant_id', $tenantId)
            ->with('plan')
            ->latest()
            ->first();

        $currentStatus = $latestSubscription->status ?? null;
        SubscriptionCache::clearCache($tenantId);
        // Handle case when subscription becomes active/trial
        if (
            in_array($currentStatus, ['active', 'trial']) && $this->previousStatus !== $currentStatus
        ) {
            $this->previousStatus = $currentStatus;

            // Check if there are pending transactions for plan upgrades
            $hasPendingUpgrade = $this->checkForPendingUpgrade($tenantId);

            if ($hasPendingUpgrade) {
                // User has active subscription but pending upgrade payment
                $this->notify([
                    'message' => t('subscription_recorded_and_activated'),
                    'type' => 'info',
                ], true);

                return redirect()->to(tenant_route('tenant.dashboard'));
            } else {
                // Normal activation
                $this->notify([
                    'message' => t('subscription_activated'),
                    'type' => 'success',
                ], true);

                return redirect()->to(tenant_route('tenant.dashboard'));
            }
        }

        $this->previousStatus = $currentStatus;

        $this->currentSubscription = Subscription::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->with('plan')
            ->latest()
            ->first();
    }

    public function checkForPendingUpgrade($tenantId)
    {
        // Check if there's an active subscription with pending plan change invoices
        $activeSubscription = Subscription::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial', 'paused'])
            ->first();

        if (! $activeSubscription) {
            return false;
        }

        // Look for unpaid plan change invoices
        $pendingPlanChangeInvoice = Invoice::where('tenant_id', $tenantId)
            ->where('subscription_id', $activeSubscription->id)
            ->where('type', 'change_plan') // Assuming this is the type for plan changes
            ->whereIn('status', ['new', 'pending'])
            ->latest()
            ->first();

        if ($pendingPlanChangeInvoice) {
            // Check if there are pending transactions for this invoice
            $pendingTransaction = Transaction::where('invoice_id', $pendingPlanChangeInvoice->id)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();

            return $pendingTransaction;
        }

        return false;
    }

    public function checkTransactionStatus()
    {
        $tenantId = Auth::user()->tenant_id;

        // Clear cache to get fresh status
        TenantCache::forget("tenant_status_check_{$tenantId}");

        // Get the latest invoice for this tenant
        $invoice = Invoice::where('tenant_id', $tenantId)
            ->latest()
            ->first();

        if (! $invoice) {
            return;
        }

        // Check if there's a transaction for this invoice
        $transaction = Transaction::where('invoice_id', $invoice->id)
            ->latest()
            ->first();

        // If no transaction exists, it means payment is still being processed or created
        if (! $transaction) {
            // Stay on pending page - payment is being processed
            return;
        }

        // If transaction exists but status is null, it might be pending
        if ($transaction->status === null) {
            // Stay on pending page - transaction is pending
            return;
        }

        // Handle failed transactions
        if ($transaction->status === 'failed') {
            // Determine the appropriate redirect based on subscription status
            $activeSubscription = Subscription::where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'trial'])
                ->exists();

            if ($activeSubscription) {
                // User has active subscription, failed transaction might be for upgrade
                $this->notify([
                    'message' => t('payment_for_plan_upgrade'),
                    'type' => 'danger',
                ], true);
            } else {
                // User doesn't have active subscription, failed transaction for new subscription
                $this->notify([
                    'message' => t('payment_for_subscription_failed'),
                    'type' => 'danger',
                ], true);
            }

            return redirect()->to(tenant_route('tenant.subscriptions'));
        }

        // Handle successful transactions
        if ($transaction->status === 'success') {
            $this->notify([
                'message' => t('payment_processed_success'),
                'type' => 'success',
            ], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // For any other status (pending, processing, etc.), stay on pending page

    }

    public function reload()
    {
        $this->checkSubscriptionStatus();
        $this->checkTransactionStatus();
    }

    public function render()
    {
        return view('livewire.tenant.tenant-subscription.subscription-pending');
    }
}
