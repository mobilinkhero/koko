<?php

namespace App\Http\Middleware;

use App\Models\Invoice\Invoice;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\SubscriptionCache;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || auth()->user()->user_type !== 'tenant') {
            return $next($request);
        }

        $tenantId = tenant_id();

        $cache_status = apply_filters('check_optimize_cache_status', false);

        if ($cache_status) {
            $request->session()->flash('notification', [
                'type' => 'danger',
                'message' => $cache_status,
            ]);
        }

        // Skip check for subscription-related routes
        if ($this->isSubscriptionRelatedRoute($request)) {
            return $next($request);
        }

        $status = $this->getOptimizedStatus($tenantId);
        // **NEW: Check for unpaid invoices first (highest priority)**
        if ($this->hasUnpaidInvoice($tenantId) && ! $status['has_failed_transaction'] && ! $status['has_pending_transaction']) {
            if (! $this->isPaymentProcessRoute($request)) {
                $request->session()->flash('notification', [
                    'type' => 'warning',
                    'message' => 'Please complete your payment process to access features.',
                ]);

                return redirect()->to(tenant_route('tenant.subscription'));
            }
        }

        // Handle pending transactions
        if ($status['has_pending_transaction'] && ! $status['has_failed_transaction']) {
            if (! $this->isPendingRelatedRoute($request)) {
                $request->session()->flash('notification', [
                    'type' => 'warning',
                    'message' => 'Your payment is being processed. Please wait for administrator approval.',
                ]);

                return redirect()->to(tenant_route('tenant.subscription.pending'));
            }
        }

        // Handle failed transactions
        if ($status['has_failed_transaction'] && ! $status['has_pending_transaction']) {
            if (! $this->isPaymentRelatedRoute($request)) {
                $request->session()->flash('notification', [
                    'type' => 'danger',
                    'message' => 'Your payment was rejected. Please select a plan and try again.',
                ]);

                return redirect()->to(tenant_route('tenant.subscriptions'));
            }
        }

        // Handle invalid subscriptions
        if (! $status['has_valid_subscription']) {
            $message = $status['subscription_message'] ?? 'Your subscription has expired. Please renew a subscription to continue.';
            session()->flash('notification', [
                'type' => 'warning',
                'message' => $message,
            ]);

            return redirect()->to(tenant_route('tenant.subscriptions'));
        }

        return $next($request);
    }

    /**
     * **NEW: Check if tenant has unpaid invoices that need to be completed**
     */
    protected function hasUnpaidInvoice($tenantId): bool
    {
        return Invoice::where('tenant_id', $tenantId)
            ->where('status', Invoice::STATUS_NEW)
            ->whereHas('subscription', function ($query) {
                $query->where('status', Subscription::STATUS_NEW);
            })
            ->exists();
    }

    /**
     * **NEW: Check if the current route is allowed during payment process**
     */
    protected function isPaymentProcessRoute(Request $request): bool
    {
        $currentRoute = $request->route()->getName();

        $allowedRoutes = [
            // Subscription management
            'tenant.subscription',
            'tenant.subscriptions',
            'tenant.subscription.pending',
            'tenant.subscription.thank-you',

            // Billing and invoices
            'tenant.billing',
            'tenant.billing.details',
            'tenant.invoices',
            'tenant.invoices.show',
            'tenant.invoices.download',

            // Payment process
            'tenant.checkout.process',
            'tenant.checkout.resume',
            'tenant.payment.offline.checkout',
            'tenant.payment.offline.process',
            'tenant.payment.stripe.checkout',
            'tenant.payment.stripe.process',

            // Profile (user might need to update billing info)
            'tenant.profile',
            'tenant.profile.update',

            // Auth routes (logout, etc.)
            'tenant.logout',
            'logout',

            // API routes for payment processing
            'tenant.api.invoices',
            'tenant.api.billing',
        ];

        // Check exact matches
        if (in_array($currentRoute, $allowedRoutes)) {
            return true;
        }

        // Check pattern matches
        $allowedPatterns = [
            'tenant.checkout.',
            'tenant.payment.',
            'tenant.billing.',
            'tenant.subscription.',
            'tenant.invoices.',
            'tenant.api.payment.',
            'tenant.webhooks.', // For payment webhooks
            'tenant.coupon.', // For coupon validation during checkout
        ];

        foreach ($allowedPatterns as $pattern) {
            if (str_starts_with($currentRoute, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get optimized subscription and transaction status with minimal queries
     */
    protected function getOptimizedStatus($tenantId): array
    {
        $now = Carbon::now();

        // Single comprehensive query to get all needed data
        $result = DB::select("
    SELECT
        s.id as subscription_id,
        s.status as subscription_status,
        s.trial_ends_at,
        s.current_period_ends_at,
        s.created_at as subscription_created_at,
        t.status as latest_transaction_status,
        t.created_at as transaction_created_at,
        p.name as plan_name
    FROM subscriptions s
    LEFT JOIN plans p ON s.plan_id = p.id
    LEFT JOIN invoices i ON s.id = i.subscription_id
    LEFT JOIN transactions t ON t.id = (
        SELECT t2.id
        FROM transactions t2
        WHERE t2.invoice_id = i.id
        ORDER BY t2.created_at DESC
        LIMIT 1
    )
    WHERE s.tenant_id = ?
    AND s.status IN ('trial', 'active', 'paused', 'new', 'ended','cancelled')
    ORDER BY s.created_at DESC
", [$tenantId]);

        if (empty($result)) {
            return [
                'has_valid_subscription' => false,
                'has_pending_transaction' => false,
                'has_failed_transaction' => false,
                'subscription_message' => 'No subscription found. Please select a plan to continue.',
            ];
        }

        $hasValidSubscription = false;
        $hasPendingTransaction = false;
        $hasFailedTransaction = false;
        $expiredSubscriptionIds = [];
        $subscriptionMessage = null;

        // Process results
        foreach ($result as $row) {
            $subscriptionStatus = $row->subscription_status;
            $trialEndsAt = $row->trial_ends_at ? Carbon::parse($row->trial_ends_at) : null;
            $currentPeriodEndsAt = $row->current_period_ends_at ? Carbon::parse($row->current_period_ends_at) : null;

            // Check if subscription is expired
            $isExpired = $this->isSubscriptionExpiredFromData($subscriptionStatus, $trialEndsAt, $currentPeriodEndsAt, $now);

            if ($isExpired && in_array($subscriptionStatus, ['trial', 'active'])) {
                $expiredSubscriptionIds[] = $row->subscription_id;

                continue;
            }

            // Check if subscription is valid
            if ($this->isValidSubscriptionFromData($subscriptionStatus, $trialEndsAt, $currentPeriodEndsAt, $now)) {
                $hasValidSubscription = true;
            }

            // Check transaction status (only for subscriptions that might have pending payments)
            if (in_array($subscriptionStatus, ['new', 'ended']) && $row->latest_transaction_status) {
                if ($row->latest_transaction_status === Transaction::STATUS_PENDING) {
                    $hasPendingTransaction = true;
                } elseif ($row->latest_transaction_status === Transaction::STATUS_FAILED) {
                    $hasFailedTransaction = true;
                }
            }
        }

        // Update expired subscriptions in batch if any found
        if (! empty($expiredSubscriptionIds)) {
            $this->batchUpdateExpiredSubscriptions($expiredSubscriptionIds, $now, $tenantId);
        }

        // Set appropriate message
        if (! $hasValidSubscription) {
            if ($hasPendingTransaction && ! $hasFailedTransaction) {
                $subscriptionMessage = 'Your payment is being processed. Please wait for confirmation.';
            } elseif ($hasFailedTransaction) {
                $subscriptionMessage = 'Your recent payment was rejected. Please select a plan and try again.';
            } else {
                $subscriptionMessage = 'Your subscription has expired. Please renew a subscription to continue.';
            }
        }

        return [
            'has_valid_subscription' => $hasValidSubscription,
            'has_pending_transaction' => $hasPendingTransaction,
            'has_failed_transaction' => $hasFailedTransaction && ! $hasPendingTransaction,
            'subscription_message' => $subscriptionMessage,
        ];
    }

    /**
     * Check if subscription is expired based on raw data
     */
    protected function isSubscriptionExpiredFromData(string $status, ?Carbon $trialEndsAt, ?Carbon $currentPeriodEndsAt, Carbon $now): bool
    {
        if ($status === 'trial' && $trialEndsAt && $trialEndsAt->lt($now)) {
            return true;
        }

        if ($status === 'active' && $currentPeriodEndsAt && $currentPeriodEndsAt->lt($now)) {
            return true;
        }

        return false;
    }

    /**
     * Check if subscription is valid based on raw data
     */
    protected function isValidSubscriptionFromData(string $status, ?Carbon $trialEndsAt, ?Carbon $currentPeriodEndsAt, Carbon $now): bool
    {
        switch ($status) {
            case 'trial':
                return ! $trialEndsAt || $trialEndsAt->gte($now);

            case 'active':
                return ! $currentPeriodEndsAt || $currentPeriodEndsAt->gte($now);

            case 'new':
                return true;

            case 'paused':
                return ! $currentPeriodEndsAt || $currentPeriodEndsAt->gte($now);

            case 'cancelled':
                return ! $currentPeriodEndsAt || $currentPeriodEndsAt->gte($now);

            default:
                return false;
        }
    }

    /**
     * Batch update expired subscriptions
     */
    protected function batchUpdateExpiredSubscriptions(array $subscriptionIds, Carbon $now, $tenantId): void
    {
        try {
            DB::table('subscriptions')
                ->whereIn('id', $subscriptionIds)
                ->update([
                    'status' => Subscription::STATUS_ENDED,
                    'ended_at' => $now,
                    'updated_at' => $now,
                ]);

            SubscriptionCache::clearCache($tenantId);
        } catch (\Exception $e) {
            app_log('Failed to batch update expired subscriptions', 'error', $e, [
                'subscription_ids' => $subscriptionIds,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if the current route is pending-subscription-related
     */
    protected function isPendingRelatedRoute(Request $request): bool
    {
        $currentRoute = $request->route()->getName();

        $allowedRoutes = [
            'tenant.subscription.pending',
            'tenant.profile',
            'tenant.billing',
        ];

        return in_array($currentRoute, $allowedRoutes);
    }

    /**
     * Check if the current route is payment-related (allowed for failed transactions)
     */
    protected function isPaymentRelatedRoute(Request $request): bool
    {
        $currentRoute = $request->route()->getName();

        $allowedPatterns = [
            'tenant.subscription',
            'tenant.billing',
            'tenant.invoices',
            'tenant.payment',
            'tenant.checkout',
            'tenant.profile',
        ];

        foreach ($allowedPatterns as $pattern) {
            if ($currentRoute === $pattern || str_starts_with($currentRoute, $pattern.'.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current route is subscription-related (always allowed)
     */
    protected function isSubscriptionRelatedRoute(Request $request): bool
    {
        $currentRoute = $request->route()->getName();

        $allowedPatterns = [
            'tenant.profile',
            'tenant.checkout',
            'tenant.payment',
            'tenant.billing',
            'tenant.subscription',
            'tenant.subscriptions',
            'tenant.invoices',
        ];

        foreach ($allowedPatterns as $pattern) {
            if ($currentRoute === $pattern || str_starts_with($currentRoute, $pattern.'.')) {
                return true;
            }
        }

        return false;
    }
}
