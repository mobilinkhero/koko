<?php

namespace App\Listeners;

use App\Events\SubscriptionCreated;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionCreatedEmail
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SubscriptionCreated $event)
    {

        try {
            $subscription = $event->subscription;

            $user = getUserByTenantId($subscription->tenant_id);

            $content = render_email_template('subscription-created', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
            $subject = get_email_subject('subscription-created', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
            if (is_smtp_valid()) {
                Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
