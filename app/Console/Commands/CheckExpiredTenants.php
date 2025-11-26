<?php

namespace App\Console\Commands;

use App\Events\Tenant\TenantStatusChanged;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckExpiredTenants extends Command
{
    protected $signature = 'tenants:check-expired';

    protected $description = 'Check for expired tenants and update their status';

    public function handle()
    {
        $expiredTenants = Tenant::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredTenants as $tenant) {
            $oldStatus = $tenant->status;
            $tenant->status = 'expired';
            $tenant->save();

            // Dispatch status changed event
            event(new TenantStatusChanged($tenant, $oldStatus, 'expired'));

            // Clear tenant cache
            Cache::forget("tenant:{$tenant->subdomain}");

            $count++;
        }

        $this->info("Updated status for {$count} expired tenants.");

        return Command::SUCCESS;
    }
}
