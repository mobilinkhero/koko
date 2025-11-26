<?php

namespace App\Repositories;

use App\Enum\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;

class SubscriptionRepository extends BaseRepository
{
    /**
     * Get the model class name.
     */
    protected function getModelClass(): string
    {
        return Subscription::class;
    }

    /**
     * Check if a previous trial exists for a tenant.
     */
    public function previousTrialExists(int $tenantId): bool
    {
        try {
            // Note: tenant_id and status columns should be indexed
            return Subscription::where('tenant_id', $tenantId)
                ->whereIn('status', [
                    Subscription::STATUS_TRIAL,
                    Subscription::STATUS_ENDED,
                    Subscription::STATUS_CANCELLED,
                    Subscription::STATUS_ACTIVE,
                ])
                ->exists();
        } catch (\Exception $e) {
            app_log('Error checking previous trial existence', 'error', $e);

            return false; // Default to false on error
        }
    }

    /**
     * Create a trial subscription for a tenant.
     *
     * @return \App\Models\Subscription
     */
    public function createTrial(int $tenantId, int $planId, int $trialDays)
    {
        // Check if the tenant already has a trial (either active or expired)
        $existingTrial = Subscription::where('tenant_id', $tenantId)
            ->whereIn('status', [
                Subscription::STATUS_TRIAL,
                Subscription::STATUS_ENDED,
                Subscription::STATUS_CANCELLED,
                Subscription::STATUS_ACTIVE,
            ])
            ->latest()
            ->first();

        if ($existingTrial) {
            // Tenant has already used a trial, don't allow another trial
            session()->flash('notification', [
                'type' => 'warning',
                'message' => t('you_have_already_used_a_trial'),
            ]);

            return $existingTrial; // Return existing subscription instead of null
        }

        // If no existing trial, allow the creation of a new trial
        $trialStartDate = get_super_admin_current_time();
        $trialEndDate = $trialStartDate->copy()->addDays($trialDays);

        $subscription = Subscription::updateOrCreate(
            ['tenant_id' => $tenantId], // Condition to check if record exists
            [
                'plan_id' => $planId,
                'status' => Subscription::STATUS_TRIAL,
                'trial_starts_at' => $trialStartDate,
                'trial_ends_at' => $trialEndDate,
            ]
        );

        $plan = $subscription->plan;

        return $subscription->createInitInvoice($plan);
    }

    /**
     * Get the trial subscription for a tenant.
     *
     * @return \App\Models\Subscription|null
     */
    public function getTrialSubscription(int $tenantId)
    {
        return Subscription::where('tenant_id', $tenantId)
            ->where('status', Subscription::STATUS_TRIAL)
            ->latest()
            ->first();
    }

    /**
     * Get active subscription for a tenant.
     *
     * @return \App\Models\Subscription|null
     */
    public function getActiveSubscription(int $tenantId)
    {
        try {
            // Use the subscription cache service instead of querying the database
            return \App\Services\SubscriptionCache::getActiveSubscription($tenantId);
        } catch (\Exception $e) {
            app_log('Error getting active subscription', 'error', $e);

            // Fallback to direct database query if caching fails
            return $this->query()
                ->select([
                    'id', 'tenant_id', 'plan_id', 'status',
                    'ended_at', 'trial_starts_at', 'trial_ends_at', 'current_period_ends_at',
                ])
                ->where('tenant_id', $tenantId)
                ->whereIn('status', [
                    Subscription::STATUS_ACTIVE,
                    Subscription::STATUS_TRIAL,
                    Subscription::STATUS_PAUSED,
                    Subscription::STATUS_CANCELLED,
                ])
                ->with(['plan:id,name,price'])
                ->latest()
                ->first();
        }
    }

    /**
     * Deactivate a subscription.
     */
    public function deactivate(int $id, array $additionalData = []): Subscription
    {
        return $this->updateStatus($id, SubscriptionStatus::INACTIVE, $additionalData);
    }

    /**
     * Reject a subscription.
     */
    public function reject(int $id, ?string $reason = null): Subscription
    {
        $additionalData = [];
        if ($reason) {
            $additionalData['rejection_reason'] = $reason;
        }

        return $this->updateStatus($id, SubscriptionStatus::REJECTED, $additionalData);
    }

    /**
     * Cancel a subscription.
     *
     * @param  bool  $immediate  Whether to cancel immediately or at period end
     */
    public function cancel(int $id, ?string $reason = null, bool $immediate = false): Subscription
    {
        $subscription = $this->findOrFail($id);
        $additionalData = [];

        if ($reason) {
            $additionalData['cancellation_reason'] = $reason;
        }

        if ($immediate) {
            $additionalData['canceled_at'] = now();

            return $this->updateStatus($id, SubscriptionStatus::CANCELED, $additionalData);
        } else {
            // Schedule cancellation at end of current period
            $additionalData['scheduled_cancellation_date'] = $subscription->ends_at;
            $subscription->fill($additionalData);
            $subscription->save();

            // We don't change status yet, it will happen when the subscription ends
            return $subscription;
        }
    }

    /**
     * Mark a subscription as expired.
     */
    public function expire(int $id): Subscription
    {
        return $this->updateStatus($id, SubscriptionStatus::EXPIRED, [
            'expired_at' => now(),
        ]);
    }

    /**
     * Mark a subscription as past due.
     *
     * @param  int  $attemptCount  Number of payment attempts
     */
    public function markAsPastDue(int $id, int $attemptCount = 1): Subscription
    {
        return $this->updateStatus($id, SubscriptionStatus::PAST_DUE, [
            'payment_attempt_count' => $attemptCount,
            'last_payment_attempt_at' => now(),
        ]);
    }

    /**
     * Check if a subscription is active.
     */
    public function isActive(int $id): bool
    {
        $subscription = $this->findOrFail($id);

        return $subscription->status === SubscriptionStatus::ACTIVE;
    }

    /**
     * Check if a subscription is in trial.
     */
    public function isInTrial(int $id): bool
    {
        $subscription = $this->findOrFail($id);

        return $subscription->status === SubscriptionStatus::TRIAL;
    }

    /**
     * Check if a subscription is pending.
     */
    public function isPending(int $id): bool
    {
        $subscription = $this->findOrFail($id);

        return $subscription->status === SubscriptionStatus::PENDING;
    }

    /**
     * Check if a subscription has expired.
     */
    public function isExpired(int $id): bool
    {
        $subscription = $this->findOrFail($id);

        return $subscription->status === SubscriptionStatus::EXPIRED;
    }

    /**
     * Check if a subscription is canceled.
     */
    public function isCanceled(int $id): bool
    {
        $subscription = $this->findOrFail($id);

        return $subscription->status === SubscriptionStatus::CANCELED;
    }

    /**
     * Check if a subscription is past due.
     */
    public function isPastDue(int $id): bool
    {
        $subscription = $this->findOrFail($id);

        return $subscription->status === SubscriptionStatus::PAST_DUE;
    }

    /**
     * Update subscription status with validation.
     *
     * @param  array  $additionalData  Additional data to update
     *
     * @throws Exception
     */
    public function updateStatus(int $id, SubscriptionStatus $newStatus, array $additionalData = []): Subscription
    {
        $subscription = $this->findOrFail($id);
        $oldStatus = $subscription->status;

        // Skip if status is already the same
        if ($oldStatus === $newStatus) {
            return $subscription;
        }

        // Validate status transition
        if (! $oldStatus->canTransitionTo($newStatus)) {
            throw new Exception(
                "Cannot change subscription status from {$oldStatus->label()} to {$newStatus->label()}"
            );
        }

        // Begin transaction to ensure database consistency
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // If we're activating a subscription, deactivate all other active subscriptions for this tenant
            if ($newStatus === SubscriptionStatus::ACTIVE && $subscription->tenant_id) {
                $this->deactivateOtherSubscriptions($subscription->tenant_id, $id);
            }

            // Update the subscription
            $subscription->status = $newStatus;

            // Apply any additional updates
            foreach ($additionalData as $key => $value) {
                $subscription->{$key} = $value;
            }

            // If status is changing to ACTIVE from PENDING, update the purchase_date to now
            if ($oldStatus === SubscriptionStatus::PENDING && $newStatus === SubscriptionStatus::ACTIVE) {
                $subscription->purchase_date = now();
            }

            $subscription->save();

            // Commit transaction
            \Illuminate\Support\Facades\DB::commit();

            return $subscription;
        } catch (\Exception $e) {
            // Rollback on error
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deactivate all other active subscriptions for a tenant.
     *
     * @param  int  $exceptSubscriptionId  The subscription ID to exclude from deactivation
     */
    protected function deactivateOtherSubscriptions(int $tenantId, int $exceptSubscriptionId): void
    {
        $activeSubscriptions = $this->query()
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $exceptSubscriptionId)
            ->whereIn('status', [
                SubscriptionStatus::ACTIVE->value,
                SubscriptionStatus::TRIAL->value,
                SubscriptionStatus::PAST_DUE->value,
            ])
            ->get();

        foreach ($activeSubscriptions as $activeSubscription) {
            // Get the old status for the event
            $oldStatus = $activeSubscription->status;

            // Set to inactive without calling updateStatus to prevent nested transactions
            $activeSubscription->status = SubscriptionStatus::INACTIVE;
            $activeSubscription->save();

        }
    }

    /**
     * Activate a subscription.
     * This will automatically deactivate any other active subscriptions for the same tenant.
     */
    public function activate(int $id, array $additionalData = []): Subscription
    {
        return $this->updateStatus($id, SubscriptionStatus::ACTIVE, $additionalData);
    }

    /**
     * Start a trial for a subscription.
     * This will automatically deactivate any other active subscriptions for the same tenant.
     */
    public function startTrial(int $id, Carbon $trialEndsAt): Subscription
    {
        $subscription = $this->findOrFail($id);

        // Since trial is also considered "active", we need to deactivate other active subscriptions
        if ($subscription->tenant_id) {
            $this->deactivateOtherSubscriptions($subscription->tenant_id, $id);
        }

        return $this->updateStatus($id, SubscriptionStatus::TRIAL, [
            'trial_ends_at' => $trialEndsAt,
        ]);
    }

    /**
     * Check if a tenant has any active subscription.
     */
    public function hasActiveSubscription(int $tenantId): bool
    {
        return $this->query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_TRIAL,
            ])
            ->exists();
    }

    public function hasAnySubscription($tenantId): bool
    {
        try {
            // Use the subscription cache service instead of querying the database
            return \App\Services\SubscriptionCache::hasAnySubscription($tenantId);
        } catch (\Exception $e) {
            app_log('Error checking subscription existence', 'error', $e);

            // Fallback to direct database query if caching fails
            return $this->query()
                ->where('tenant_id', $tenantId)
                ->exists();
        }
    }

    /**
     * Create a new subscription.
     *
     * @param  array  $data  Subscription data
     * @param  bool  $activateImmediately  Whether to activate the subscription immediately
     */
    public function create(array $data, bool $activateImmediately = false): Subscription
    {
        // Begin transaction
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Create the subscription
            $subscription = $this->query()->create($data);

            // If immediate activation is requested, activate and handle deactivating others
            if ($activateImmediately && isset($data['tenant_id'])) {
                $this->deactivateOtherSubscriptions($data['tenant_id'], $subscription->id);
                $subscription->status = SubscriptionStatus::ACTIVE;
                $subscription->save();
            }

            \Illuminate\Support\Facades\DB::commit();

            return $subscription;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }
}
