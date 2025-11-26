<?php

namespace App\Listeners;

use App\Events\SubscriptionRenewed;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionRenewedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SubscriptionRenewed $event)
    {
        try {
            $subscription = $event->subscription;

            $user = getUserByTenantId($subscription->tenant_id);

            $content = render_email_template('subscription-renewal-reminder', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id]);
            $subject = get_email_subject('subscription-renewal-reminder', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id]);
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
