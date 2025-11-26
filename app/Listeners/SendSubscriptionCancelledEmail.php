<?php

namespace App\Listeners;

use App\Events\SubscriptionCancelled;
use App\Models\Subscription;
use Corbital\LaravelEmails\Facades\Email;

class SendSubscriptionCancelledEmail
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(SubscriptionCancelled $event)
    {
        // Get the subscription
        $subscription = Subscription::find($event->subscriptionId);

        if (! $subscription) {
            app_log('Cannot send cancellation email: Subscription not found', 'warning', null, [
                'subscription_id' => $event->subscriptionId,
            ]);

            return;
        }

        $user = getUserByTenantId($subscription->tenant_id);

        // Check if the subscription has a customer and user
        if (! $user) {
            app_log('Cannot send cancellation email: User not found for tenant ID', 'warning', null, [
                'subscription_id' => $subscription->id,
            ]);

            return;
        }

        try {
            $content = render_email_template('subscription-cancelled', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
            $subject = get_email_subject('subscription-cancelled', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
            if (is_smtp_valid()) {
                Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }

        } catch (\Exception $e) {
            app_log('Failed to send subscription cancellation email', 'error', $e, [
                'subscription_id' => $subscription->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;

        }
    }
}
