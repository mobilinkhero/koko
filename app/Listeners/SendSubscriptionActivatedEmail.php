<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use Corbital\LaravelEmails\Facades\Email;

class SendSubscriptionActivatedEmail
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionActivated $event)
    {
        try {
            $subscription = $event->subscription;
            $tenantId = $subscription->tenant_id;

            // Get user by tenant ID
            $user = getUserByTenantId($tenantId);

            if (! $user) {
                app_log('User not found for tenant ID', 'error', null, [
                    'tenant_id' => $tenantId,
                ]);

                return;
            }

            $content = render_email_template('subscription-activated', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
            $subject = get_email_subject('subscription-activated', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
            if (is_smtp_valid()) {
                Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            app_log('Failed to send subscription activated email', 'error', $e, [
                'subscription_id' => $event->subscription->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
