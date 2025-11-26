<?php

namespace App\Services\Subscription;

use App\Events\InvoicePaid;
use App\Models\Subscription;
use App\Models\TenantCreditBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Subscription Management Service
 *
 * Handles comprehensive subscription lifecycle management for the WhatsApp SaaS
 * multi-tenant platform. Manages subscription renewals, plan changes, billing
 * operations, and automated charging processes with tenant-aware isolation.
 *
 * Key Features:
 * - Automated subscription renewal processing
 * - Plan upgrade and downgrade handling
 * - Recurring billing management
 * - Invoice generation and auto-charging
 * - Subscription cancellation and recovery
 * - Tenant-specific subscription filtering
 * - Comprehensive error handling and logging
 *
 * Renewal Process:
 * 1. End expired subscriptions
 * 2. Create renewal invoices for recurring subscriptions
 * 3. Auto-charge renewal invoices where possible
 * 4. Handle failed payments with retry logic
 *
 * Plan Change Operations:
 * - Upgrade: Move to higher-tier plan with prorated billing
 * - Downgrade: Move to lower-tier plan with credit management
 * - Change: Switch between plans of similar value
 *
 * Usage Example:
 * ```php
 * $manager = new SubscriptionManager();
 *
 * // Upgrade customer subscription
 * $invoice = $manager->upgradePlan($subscriptionId, $newPlanId, $tenantId);
 *
 * // Get active subscriptions for tenant
 * $subscriptions = $manager->getActiveSubscriptions($tenantId);
 * ```
 *
 * @author WhatsApp SaaS Team
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 * @see Subscription For subscription model
 * @see Plan For subscription plans
 * @see Invoice For billing operations
 * @see BillingManager For payment processing
 */
class SubscriptionManager
{
    /**
     * End expired subscriptions for the current tenant.
     *
     * Identifies and terminates active subscriptions that have exceeded
     * their current period end date. Handles errors gracefully and logs
     * failures for administrative review.
     *
     * @return Collection Collection of subscriptions that were expired
     *
     * @example
     * ```php
     * $expiredSubscriptions = $manager->endExpiredSubscriptions();
     * foreach ($expiredSubscriptions as $subscription) {
     *     // Notify customer of expiration
     *     Mail::to($subscription->tenant->owner)
     *         ->send(new SubscriptionExpiredNotification($subscription));
     * }
     * ```
     */
    public function endExpiredSubscriptions(): Collection
    {
        $expiredSubscriptions = Subscription::active()
            ->where('tenant_id', tenant_id())
            ->where('current_period_ends_at', '<', Carbon::now())
            ->get();

        $endedCount = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $subscription->end();
                $endedCount++;
            } catch (\Exception $e) {
                payment_log("Failed to end expired subscription: {$subscription->id}", 'error', [
                    'tenant_id' => tenant_id(),
                    'error' => $e->getMessage(),
                ]);

            }
        }

        return $expiredSubscriptions;
    }

    /**
     * Create renewal invoices for subscriptions approaching expiry.
     *
     * Generates renewal invoices for ended recurring subscriptions that
     * are eligible for automatic renewal. Only processes subscriptions
     * that haven't been cancelled and are marked as recurring.
     *
     * @return Collection Collection of subscriptions processed for renewal
     *
     * @example
     * ```php
     * $renewalSubscriptions = $manager->createRenewInvoices();
     * $invoiceCount = $renewalSubscriptions->count();
     * ```
     */
    public function createRenewInvoices(): Collection
    {
        $expiringSubscriptions = Subscription::ended()
            ->where('is_recurring', true)
            ->where('tenant_id', tenant_id())
            ->whereNull('cancelled_at')
            ->get();

        $createdCount = 0;

        foreach ($expiringSubscriptions as $subscription) {
            try {
                $invoice = $subscription->checkAndCreateRenewInvoice();
                if ($invoice) {
                    $createdCount++;
                }
            } catch (\Exception $e) {
                payment_log("Failed to create renewal invoice for subscription: {$subscription->id}", 'error', [
                    'tenant_id' => tenant_id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $expiringSubscriptions;
    }

    /**
     * Automatically charge renewal invoices using stored payment methods.
     *
     * Processes unpaid renewal invoices for recurring subscriptions by
     * attempting automatic charges through configured payment gateways.
     * Only charges subscriptions with valid auto-billing data.
     *
     * @return int Number of successfully charged renewal invoices
     *
     * @example
     * ```php
     * $chargedCount = $manager->autoChargeRenewInvoices();
     * if ($chargedCount > 0) {
     * }
     * ```
     */
    public function autoChargeRenewInvoices($tenant_id): int
    {
        // Get subscriptions in billing period
        $subscriptions = Subscription::ended()
            ->where('is_recurring', true)
            ->where('tenant_id', $tenant_id)
            ->whereNull('cancelled_at')
            ->get();

        $chargedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Get unpaid renewal invoice
                $renewInvoice = $subscription->getUnpaidRenewInvoice();

                if (! $renewInvoice) {
                    continue;
                }

                // Get auto billing data
                $tenantAutoBillingData = $subscription->tenant->getAutoBillingData();

                if (! $tenantAutoBillingData) {
                    continue;
                }

                // Get auto billing gateway
                $gateway = app('billing.manager')->gateway($tenantAutoBillingData->type);

                if (! $gateway || ! $gateway->supportsAutoBilling()) {
                    continue;
                }

                $balance = TenantCreditBalance::getOrCreateBalance($renewInvoice->tenant_id, $renewInvoice->currency_id);
                $remainingCredit = 0;
                if ($balance->balance != 0) {
                    $remainingCredit = $balance->balance;
                }
                // Auto charge
                $gateway->autoCharge($renewInvoice, $remainingCredit);

                if ($renewInvoice->isPaid()) {
                    event(new InvoicePaid($renewInvoice));
                    $chargedCount++;
                }
            } catch (\Exception $e) {
                payment_log("Failed to auto-charge renewal invoice for subscription: {$subscription->id}", 'error', [
                    'tenant_id' => tenant_id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $chargedCount;
    }

    /**
     * Find active subscriptions for a specific tenant.
     *
     * Retrieves all currently active subscriptions for the specified tenant,
     * including associated plan information. Active subscriptions are those
     * that are not expired, cancelled, or ended.
     *
     * @param  int  $tenantId  The tenant ID to filter subscriptions
     * @return Collection Collection of active subscriptions with plans
     *
     * @example
     * ```php
     * $activeSubscriptions = $manager->getActiveSubscriptions($tenantId);
     * foreach ($activeSubscriptions as $subscription) {
     *     echo "Plan: {$subscription->plan->name}";
     *     echo "Expires: {$subscription->current_period_ends_at}";
     * }
     * ```
     */
    public function getActiveSubscriptions(int $tenantId): Collection
    {
        return Subscription::where('tenant_id', $tenantId)
            ->active()
            ->with('plan')
            ->get();
    }

    /**
     * Find all subscriptions for a specific tenant.
     *
     * Retrieves the complete subscription history for the specified tenant,
     * including active, expired, and cancelled subscriptions. Results are
     * ordered by creation date with most recent first.
     *
     * @param  int  $tenantId  The tenant ID to filter subscriptions
     * @return Collection Collection of all subscriptions with plans
     *
     * @example
     * ```php
     * $allSubscriptions = $manager->getAllSubscriptions($tenantId);
     * $subscriptionHistory = $allSubscriptions->map(function ($sub) {
     *     return [
     *         'plan' => $sub->plan->name,
     *         'status' => $sub->status,
     *         'created' => $sub->created_at->format('Y-m-d')
     *     ];
     * });
     * ```
     */
    public function getAllSubscriptions(int $tenantId): Collection
    {
        return Subscription::where('tenant_id', $tenantId)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a subscription by ID with optional tenant filtering.
     *
     * Retrieves a single subscription by its ID with optional tenant filtering
     * for security and data isolation. Includes associated plan and tenant
     * information for complete subscription context.
     *
     * @param  int|string  $id  The subscription ID to retrieve
     * @param  int|null  $tenantId  Optional tenant ID for filtering (security)
     * @return Subscription|null The subscription or null if not found
     *
     * @example
     * ```php
     * // Get subscription with tenant filtering (recommended for security)
     * $subscription = $manager->getSubscription($subscriptionId, $tenantId);
     *
     * // Get subscription without tenant filtering (admin use)
     * $subscription = $manager->getSubscription($subscriptionId);
     *
     * if ($subscription) {
     *     echo "Plan: {$subscription->plan->name}";
     *     echo "Tenant: {$subscription->tenant->name}";
     * }
     * ```
     */
    public function getSubscription(int|string $id, ?int $tenantId = null): ?Subscription
    {
        $subscriptionId = (int) $id;

        $query = Subscription::with('plan', 'tenant');

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->find($subscriptionId);
    }

    /**
     * Create a new subscription for a tenant.
     *
     * Creates a new subscription instance associating a tenant with a specific
     * subscription plan. Sets up the subscription with proper timezone handling
     * and initial configuration for the subscription lifecycle.
     *
     * @param  int  $tenant_id  The tenant ID to create subscription for
     * @param  int  $planId  The subscription plan ID to associate
     * @return Subscription The newly created subscription instance
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If tenant or plan not found
     *
     * @example
     * ```php
     * try {
     *     $subscription = $manager->createSubscription($tenantId, $planId);
     * } catch (ModelNotFoundException $e) {
     *     Log::error("Failed to create subscription: {$e->getMessage()}");
     * }
     * ```
     */
    public function createSubscription(int $tenant_id, int $planId): Subscription
    {
        $tenant = \App\Models\Tenant::findOrFail($tenant_id);
        $plan = \App\Models\Plan::findOrFail($planId);

        $subscription = Subscription::createNewSubscription($tenant, $plan);
        payment_log('SubscriptionManager: Subscription created successfully', 'info', [
            'subscription_id' => $subscription->id,
            'subscription_status' => $subscription->status,
        ]);

        return $subscription;
    }

    /**
     * Upgrade subscription plan to a higher tier.
     *
     * Processes subscription upgrades by validating the new plan is actually
     * a higher tier, calculating prorated billing, and creating upgrade invoices.
     * Includes comprehensive logging and error handling for upgrade operations.
     *
     * @param  string  $subscriptionId  The subscription ID to upgrade
     * @param  int  $newPlanId  The higher-tier plan ID to upgrade to
     * @param  int  $tenantId  The tenant ID for security filtering
     * @param  bool  $overrideExisting  Whether to override existing pending changes
     * @return \App\Models\Invoice\Invoice The upgrade invoice
     *
     * @throws \Exception If subscription not found or plan is not an upgrade
     *
     * @example
     * ```php
     * try {
     *     $invoice = $manager->upgradePlan($subscriptionId, $premiumPlanId, $tenantId);
     *
     *     // Notify customer of upgrade
     *     Mail::to($tenant->owner)->send(new PlanUpgradeNotification($invoice));
     *
     *     return $invoice;
     * } catch (Exception $e) {
     *     throw $e;
     * }
     * ```
     */
    public function upgradePlan(string $subscriptionId, int $newPlanId, int $tenantId, bool $overrideExisting = false)
    {
        try {
            $subscription = $this->getSubscription($subscriptionId, $tenantId);

            if (! $subscription) {
                throw new \Exception('Subscription not found.');
            }

            $newPlan = \App\Models\Plan::findOrFail($newPlanId);

            // Ensure the new plan is actually an upgrade (higher price)
            if ($newPlan->price <= $subscription->plan->price) {
                throw new \Exception('The selected plan is not an upgrade. Please choose a higher tier plan.');
            }

            $invoice = $subscription->upgradePlan($newPlan, $overrideExisting);

            // Ensure the invoice has the target plan ID set
            if ($invoice instanceof \App\Models\Invoice\InvoiceChangePlan) {
                $invoice->setPlanId($newPlanId);
            }

            return $invoice;
        } catch (\Exception $e) {
            payment_log('Error in upgradePlan', 'error', [
                'tenant_id' => tenant_id(),
                'subscription_id' => $subscriptionId,
                'new_plan_id' => $newPlanId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Downgrade subscription plan to a lower tier.
     *
     * Processes subscription downgrades by validating the new plan is actually
     * a lower tier, handling billing credits, and creating downgrade invoices.
     * Includes comprehensive logging and error handling for downgrade operations.
     *
     * @param  string  $subscriptionId  The subscription ID to downgrade
     * @param  int  $newPlanId  The lower-tier plan ID to downgrade to
     * @param  int  $tenantId  The tenant ID for security filtering
     * @param  bool  $overrideExisting  Whether to override existing pending changes
     * @return \App\Models\Invoice\Invoice The downgrade invoice (may have credit)
     *
     * @throws \Exception If subscription not found or plan is not a downgrade
     *
     * @example
     * ```php
     * try {
     *     $invoice = $manager->downgradePlan($subscriptionId, $basicPlanId, $tenantId);
     *
     *     // Handle credit from downgrade
     *     if ($invoice->amount < 0) {
     *         // Customer receives credit
     *         $creditAmount = abs($invoice->amount);
     *     }
     *
     *     return $invoice;
     * } catch (Exception $e) {
     *     Log::error("Downgrade failed", ['error' => $e->getMessage()]);
     *     throw $e;
     * }
     * ```
     */
    public function downgradePlan(string $subscriptionId, int $newPlanId, int $tenantId, bool $overrideExisting = false)
    {
        try {
            $subscription = $this->getSubscription($subscriptionId, $tenantId);

            if (! $subscription) {
                throw new \Exception('Subscription not found.');
            }

            $newPlan = \App\Models\Plan::findOrFail($newPlanId);

            // Ensure the new plan is actually a downgrade (lower price)
            if ($newPlan->price >= $subscription->plan->price) {
                throw new \Exception('The selected plan is not a downgrade. Please choose a lower tier plan.');
            }

            $invoice = $subscription->downgradePlan($newPlan, $overrideExisting);

            // Ensure the invoice has the target plan ID set
            if ($invoice instanceof \App\Models\Invoice\InvoiceChangePlan) {
                $invoice->setPlanId($newPlanId);
            }

            return $invoice;
        } catch (\Exception $e) {
            payment_log('Error in downgradePlan', 'error', [
                'tenant_id' => tenant_id(),
                'subscription_id' => $subscriptionId,
                'new_plan_id' => $newPlanId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a subscription.
     *
     * Cancels an active subscription by marking it as cancelled and updating
     * its status. The subscription may continue until the end of the current
     * billing period depending on the cancellation policy.
     *
     * @param  string  $subscriptionId  The subscription ID to cancel
     * @param  int  $tenantId  The tenant ID for security filtering
     * @return Subscription The cancelled subscription instance
     *
     * @throws \Exception If subscription not found or cancellation fails
     *
     * @example
     * ```php
     * try {
     *     $subscription = $manager->cancelSubscription($subscriptionId, $tenantId);
     *
     *     // Notify customer of cancellation
     *     Mail::to($tenant->owner)->send(new SubscriptionCancelledNotification($subscription));
     *
     *     return $subscription;
     * } catch (Exception $e) {
     *     Log::error("Cancellation failed: {$e->getMessage()}");
     *     throw $e;
     * }
     * ```
     */
    public function cancelSubscription(string $subscriptionId, int $tenantId): Subscription
    {
        $subscription = $this->getSubscription($subscriptionId, $tenantId);

        if (! $subscription) {
            throw new \Exception('Subscription not found.');
        }

        return $subscription->cancel();
    }

    /**
     * Toggle recurring billing for a subscription.
     *
     * Enables or disables automatic recurring billing for a subscription.
     * When enabled, the subscription will automatically renew at the end
     * of each billing period. When disabled, the subscription will end
     * after the current period expires.
     *
     * @param  string  $subscriptionId  The subscription ID to modify
     * @param  int  $tenantId  The tenant ID for security filtering
     * @param  bool  $enable  True to enable recurring, false to disable
     * @return Subscription The updated subscription instance
     *
     * @throws \Exception If subscription not found or toggle operation fails
     *
     * @example
     * ```php
     * // Enable auto-renewal
     * $subscription = $manager->toggleRecurring($subscriptionId, $tenantId, true);
     * echo "Auto-renewal enabled. Next billing: {$subscription->current_period_ends_at}";
     *
     * // Disable auto-renewal
     * $subscription = $manager->toggleRecurring($subscriptionId, $tenantId, false);
     * echo "Auto-renewal disabled. Subscription ends: {$subscription->current_period_ends_at}";
     * ```
     */
    public function toggleRecurring(string $subscriptionId, int $tenantId, bool $enable): Subscription
    {
        $subscription = $this->getSubscription($subscriptionId, $tenantId);

        if (! $subscription) {
            throw new \Exception('Subscription not found.');
        }

        if ($enable) {
            return $subscription->enableRecurring();
        } else {
            return $subscription->disableRecurring();
        }
    }
}
