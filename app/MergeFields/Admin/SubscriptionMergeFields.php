<?php

namespace App\MergeFields\Admin;

use App\Models\Subscription;

class SubscriptionMergeFields
{
    public function name(): string
    {
        return 'subscription-group';
    }

    public function templates(): array
    {
        return [
            'subscription-renewal-success',
            'subscription-renewal-failed',
            'subscription-created',
            'subscription-activated',
            'subscription-renewal-reminder',
            'subscription-expiring-soon',
            'subscription-cancelled',
            'subscription-expired',
        ];
    }

    public function build(): array
    {
        return [
            ['name' => 'Subscription Status',         'key' => '{subscription_status}'],
            ['name' => 'Subscription Plan Name',      'key' => '{subscription_plan_name}'],
            ['name' => 'Subscription Tenant Name',    'key' => '{subscription_tenant_name}'],
            ['name' => 'Recurring',                   'key' => '{subscription_is_recurring}'],
            ['name' => 'Current Period Ends At',      'key' => '{subscription_period_ends_at}'],
            ['name' => 'Trial Starts At',             'key' => '{subscription_trial_starts_at}'],
            ['name' => 'Trial Ends At',               'key' => '{subscription_trial_ends_at}'],
            ['name' => 'Cancelled At',                'key' => '{subscription_cancelled_at}'],
            ['name' => 'Cancellation Reason',         'key' => '{subscription_cancellation_reason}'],
            ['name' => 'Payment Attempt Count',       'key' => '{subscription_payment_attempt_count}'],
            ['name' => 'Last Payment Attempt At',     'key' => '{subscription_last_payment_attempt_at}'],
            ['name' => 'Ended At',                    'key' => '{subscription_ended_at}'],
            ['name' => 'Terminated At',               'key' => '{subscription_terminated_at}'],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['subscriptionId'])) {
            return [];
        }

        $subscription = Subscription::with(['tenant', 'plan'])->findOrFail($context['subscriptionId']);

        return [
            '{subscription_status}' => $subscription->status ?? '',
            '{subscription_plan_name}' => $subscription->plan->name ?? '',
            '{subscription_tenant_name}' => $subscription->tenant->company_name ?? '',
            '{subscription_is_recurring}' => $subscription->is_recurring ? 'Yes' : 'No',
            '{subscription_period_ends_at}' => optional($subscription->current_period_ends_at)->toDateTimeString(),
            '{subscription_trial_starts_at}' => optional($subscription->trial_starts_at)->toDateTimeString(),
            '{subscription_trial_ends_at}' => optional($subscription->trial_ends_at)->toDateTimeString(),
            '{subscription_cancelled_at}' => optional($subscription->cancelled_at)->toDateTimeString(),
            '{subscription_cancellation_reason}' => $subscription->cancellation_reason ?? '',
            '{subscription_payment_attempt_count}' => $subscription->payment_attempt_count,
            '{subscription_last_payment_attempt_at}' => optional($subscription->last_payment_attempt_at)->toDateTimeString(),
            '{subscription_ended_at}' => optional($subscription->ended_at)->toDateTimeString(),
            '{subscription_terminated_at}' => optional($subscription->terminated_at)->toDateTimeString(),
        ];
    }
}
