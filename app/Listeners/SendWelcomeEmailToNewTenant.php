<?php

namespace App\Listeners;

use App\Events\NewRegistered;
use App\Models\User;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmailToNewTenant
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NewRegistered $event)
    {
        $user = $event->user;

        $user = getUserByTenantId($user->tenant_id);
        // Only send the welcome email if this is a tenant
        if ($user->user_type === 'tenant' && get_setting('tenant.isEnableWelcomeEmail')) {
            try {
                $content = render_email_template('tenant-welcome-mail', ['tenantId' => $user->tenant_id]);
                $subject = get_email_subject('tenant-welcome-mail', ['tenantId' => $user->tenant_id]);

                if (is_smtp_valid()) {
                    Email::to($user->email)
                        ->subject($subject)
                        ->content($content)
                        ->send();
                }

                $this->sendNewTenantNotificationToAdmins($user);
            } catch (\Exception $e) {
                return false;
            }
        }

    }

    /**
     * Send notification to admin users about new tenant registration
     */
    protected function sendNewTenantNotificationToAdmins($tenantUser)
    {
        try {
            // Find all admin users
            $adminUsers = User::where('user_type', 'admin')
                ->where('is_admin', true)
                ->get();

            if ($adminUsers->isEmpty()) {
                return;
            }

            foreach ($adminUsers as $adminUser) {
                $adminContent = render_email_template('new-tenant-reminder-email-to-admin', ['tenantId' => $tenantUser->tenant_id, 'userId' => $adminUser->id]);
                $subject = get_email_subject('new-tenant-reminder-email-to-admin', ['tenantId' => $tenantUser->tenant_id, 'userId' => $adminUser->id]);

                if (is_smtp_valid()) {
                    Email::to($adminUser->email)
                        ->subject($subject)
                        ->content($adminContent)
                        ->send();
                }
            }
        } catch (\Exception $e) {
            app_log('Failed to send admin notifications about new tenant', 'error', $e, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
