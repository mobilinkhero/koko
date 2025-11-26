<?php

namespace App\Console\Commands;

use App\Facades\Tenant;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionManager;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class ProcessSubscriptionRenewals extends Command
{
    use TenantAware;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-renewals {--tenant=*}';

    protected $tenant;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process subscription renewals, including ending expired subscriptions and creating renewal invoices';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionManager $subscriptionManager)
    {
        $this->tenant = Tenant::current();
        $this->info('Tenant ID: '.$this->tenant->id);
        $this->info('Tenant Name: '.$this->tenant->company_name);
        $this->info('Starting subscription renewal processing...');
        Cache::remember("tenant_{$this->tenant->id}_subscriptions_subscription", 3600, function () use ($subscriptionManager) {
            $this->tenant = Tenant::current();
            try {
                // First end expired subscriptions
                $endedCount = $subscriptionManager->endExpiredSubscriptions()->count();
                $this->info("Ended {$endedCount} expired subscriptions");

                // Check if tenant is marked for deletion
                if ($this->tenant->deleted_date) {
                    $this->info("Tenant {$this->tenant->id} is marked for deletion. Skipping renewal invoice creation.");

                    // Don't create renewal invoices for deleted tenants
                    // Let the cleanup command handle the data deletion when appropriate
                    return Command::SUCCESS;
                }

                // First, find eligible subscriptions
                $eligibleSubscriptions = Subscription::where('current_period_ends_at', '<', now())
                    ->where('is_recurring', true)
                    ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_ENDED]) // Assuming you don't want paused ones
                    ->where('tenant_id', $this->tenant->id)
                    ->get();

                $this->info("Found {$eligibleSubscriptions->count()} subscriptions eligible for renewal");

                // Then create renewal invoices for them
                $renewalCount = 0;
                $renewedSubscriptions = [];

                foreach ($eligibleSubscriptions as $subscription) {
                    if ($subscription->is_recurring == 1) {
                        $invoice = $subscription->createRenewInvoice();

                        if ($invoice) {
                            $renewalCount++;
                            $renewedSubscriptions[] = [
                                'subscription' => $subscription,
                                'invoice' => $invoice,
                            ];
                        }
                    }
                }

                $this->info("Created {$renewalCount} renewal invoices for expired subscriptions");

                // Send email notifications for each subscription with a renewal invoice
                $emailCount = 0;
                foreach ($renewedSubscriptions as $data) {
                    $subscription = $data['subscription'];
                    $invoice = $data['invoice'];

                    // Get the tenant's user
                    $user = $subscription->tenant_id;
                    $user = getUserByTenantId($user);

                    try {
                        if ($user && is_smtp_valid()) {
                            // Render email template
                            $content = render_email_template('subscription-renewal-invoice', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id, 'invoiceId' => $invoice->id, 'userId' => $user->id]);
                            $subject = get_email_subject('subscription-renewal-invoice', ['tenantId' => $user->tenant_id, 'subscriptionId' => $subscription->id, 'planId' => $subscription->plan_id, 'invoiceId' => $invoice->id, 'userId' => $user->id]);

                            // Send email
                            Email::to($user->email)
                                ->subject($subject)
                                ->content($content)
                                ->send();

                            $emailCount++;
                        }
                    } catch (\Exception $e) {
                        return false;
                    }
                }

                $this->info("Sent {$emailCount} renewal notification emails");

                $chargedCount = $subscriptionManager->autoChargeRenewInvoices($this->tenant->id);
                $this->info("Auto-charged {$chargedCount} renewal invoices");

                $this->info('Subscription renewal processing completed successfully');

                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error('Error during subscription renewal processing: '.$e->getMessage());

                app_log('Subscription renewal processing failed', 'error', $e);

                return Command::FAILURE;
            }
        });
    }
}
