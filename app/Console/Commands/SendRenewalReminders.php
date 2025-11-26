<?php

namespace App\Console\Commands;

use App\Facades\Tenant;
use App\Models\Subscription;
use Carbon\Carbon;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class SendRenewalReminders extends Command
{
    use TenantAware;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-renewal-reminders {--tenant=*}';

    protected $tenant;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminder emails to customers with subscriptions about to expire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->tenant = Tenant::current();
        $this->info('Tenant ID: '.$this->tenant->id);
        $this->info('Tenant Name: '.$this->tenant->company_name);
        $this->info('Sending subscription renewal reminders...');

        //  Only process subscriptions for the current tenant
        $currentTenantId = $this->tenant->id;

        // Get number of days before expiration to send reminder
        $reminderDays = [7, 3, 1];
        Cache::remember("tenant_{$this->tenant->id}_reminder_mail", now()->addHours(23), function () use ($currentTenantId, $reminderDays) {
            // Process each reminder day
            $sentCount = 0;
            foreach ($reminderDays as $days) {

                // Calculate target date range for this specific day
                $targetDate = Carbon::now()->addDays($days)->startOfDay();
                $endDate = Carbon::now()->addDays($days)->endOfDay();
                $expiringSubscriptions = Subscription::where('tenant_id', $currentTenantId)
                    ->where(function ($query) use ($targetDate, $endDate) {
                        // Regular subscription expiring
                        $query->where('status', Subscription::STATUS_ACTIVE)
                            ->whereBetween('current_period_ends_at', [$targetDate, $endDate]);
                    })
                    ->orWhere(function ($query) use ($targetDate, $endDate, $currentTenantId) {
                        // Trial subscription ending
                        $query->where('tenant_id', $currentTenantId)
                            ->where('status', Subscription::STATUS_TRIAL)
                            ->whereBetween('trial_ends_at', [$targetDate, $endDate]);
                    })
                    ->with(['tenant', 'plan'])
                    ->get();

                $count = $expiringSubscriptions->count();
                $this->info("Found {$count} subscription(s) expiring in {$days} days.");

                // Send emails for this batch
                foreach ($expiringSubscriptions as $subscription) {
                    $user = getUserByTenantId($subscription->tenant_id);

                    try {
                        // Skip if customer or user doesn't exist
                        if (! $user) {
                            $this->warn("Skipping subscription #{$subscription->id} - no customer or user found.");

                            continue;
                        }

                        if (is_smtp_valid()) {
                            $content = render_email_template('subscription-expiring-soon', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
                            $subject = get_email_subject('subscription-expiring-soon', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);

                            Email::to($user->email)
                                ->subject($subject)
                                ->content($content)
                                ->send();

                            $sentCount++;
                        }

                        $this->info("Sent reminder for subscription #{$subscription->id} to {$user->email} ({$days} days before expiry)");
                    } catch (\Exception $e) {
                        $this->error("Failed to send reminder for subscription #{$subscription->id}: {$e->getMessage()}");

                        app_log('Failed to send subscription renewal reminder', 'error', $e, [
                            'subscription_id' => $subscription->id,
                            'days_before' => $days,
                        ]);

                        return false;
                    }
                }
            }

            //  Handle expired subscriptions (24 hours after expiration)
            $this->info('Checking for recently expired subscriptions (24 hours ago)...');

            // Calculate date range for subscriptions that expired 24 hours ago
            $expiredStartDate = Carbon::now()->subHours(25)->startOfHour(); // Add 1 hour buffer
            $expiredEndDate = Carbon::now()->subHours(23)->endOfHour();   // Add 1 hour buffer

            $expiredSubscriptions = Subscription::where('tenant_id', $currentTenantId)
                ->where(function ($query) use ($expiredStartDate, $expiredEndDate) {
                    // Regular subscriptions that expired
                    $query->where('status', Subscription::STATUS_ENDED)
                        ->whereBetween('current_period_ends_at', [$expiredStartDate, $expiredEndDate]);
                })
                ->orWhere(function ($query) use ($expiredStartDate, $expiredEndDate, $currentTenantId) {
                    // Trial subscriptions that expired
                    $query->where('tenant_id', $currentTenantId)
                        ->where('status', Subscription::STATUS_ENDED)
                        ->whereBetween('trial_ends_at', [$expiredStartDate, $expiredEndDate]);
                })
                ->with(['tenant', 'plan'])
                ->get();
            $expiredCount = $expiredSubscriptions->count();
            $this->info("Found {$expiredCount} subscription(s) that expired 24 hours ago.");

            // Send post-expiration emails
            foreach ($expiredSubscriptions as $subscription) {
                $user = getUserByTenantId($subscription->tenant_id);

                try {
                    // Skip if customer or user doesn't exist
                    if (! $user) {
                        $this->warn("Skipping expired subscription #{$subscription->id} - no customer or user found.");

                        continue;
                    }

                    if (is_smtp_valid()) {
                        $content = render_email_template('subscription-expired', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);
                        $subject = get_email_subject('subscription-expired', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id]);

                        Email::to($user->email)
                            ->subject($subject)
                            ->content($content)
                            ->send();

                        $sentCount++;
                    }

                    $this->info("Sent post-expiration notice for subscription #{$subscription->id} to {$user->email}");
                } catch (\Exception $e) {
                    $this->error("Failed to send post-expiration notice for subscription #{$subscription->id}: {$e->getMessage()}");

                    app_log('Failed to send subscription expired notification', 'error', $e, [
                        'subscription_id' => $subscription->id,
                    ]);

                    return false;
                }
            }

            $this->info("Successfully sent {$sentCount} renewal reminder email(s) in total.");

            return Command::SUCCESS;
        });

    }
}
