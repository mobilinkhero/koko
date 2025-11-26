<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantDeletionService;
use Illuminate\Console\Command;

class CleanupDeletedTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:cleanup-deleted {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tenant data for tenants marked for deletion whose subscriptions have ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDebug = $this->option('debug');

        if ($isDebug) {
            $this->info('Starting cleanup of deleted tenants...');
        }

        try {
            $deletionService = new TenantDeletionService;
            $count = 0;

            // Find tenants marked for deletion
            $deletedTenants = Tenant::whereNotNull('deleted_date')->get();

            foreach ($deletedTenants as $tenant) {
                try {
                    // Check if tenant has any active subscriptions
                    $hasActiveSubscription = $tenant->subscriptions()
                        ->where(function ($query) {
                            $query->whereNull('current_period_ends_at')
                                ->orWhere('current_period_ends_at', '>', now());
                        })
                        ->exists();

                    // Only delete if no active subscriptions
                    if (! $hasActiveSubscription) {
                        if ($isDebug) {
                            $this->info("Processing tenant ID: {$tenant->id} - Subscription expired, deleting data");
                        }

                        $deletionService->deleteAllTenantData($tenant);
                        $count++;

                        if ($isDebug) {
                            $this->info("Successfully deleted data for tenant {$tenant->id}");
                        }
                    } else {
                        if ($isDebug) {
                            $this->info("Skipping tenant ID: {$tenant->id} - Still has active subscription");
                        }
                    }
                } catch (\Exception $e) {
                    if ($isDebug) {
                        $this->error("Failed to delete data for tenant {$tenant->id}: {$e->getMessage()}");
                    }
                    app_log('Tenant cleanup failed', 'error', $e, [
                        'tenant_id' => $tenant->id,
                    ]);

                    continue;
                }
            }

            if ($isDebug) {
                $this->info("Completed. Processed {$count} expired deleted tenants.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            if ($isDebug) {
                $this->error('Cleanup failed: '.$e->getMessage());
            }
            app_log('Tenant cleanup command failed', 'error', $e);

            return Command::FAILURE;
        }
    }
}
