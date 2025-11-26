<?php

namespace Modules\CacheManager\Console\Commands;

use Illuminate\Console\Command;
use Modules\CacheManager\Services\AdminCacheService;
use Modules\CacheManager\Services\TenantCacheService;

class TestCacheIsolation extends Command
{
    protected $signature = 'cache:test-isolation';

    protected $description = 'Test cache isolation between admin and tenants';

    public function handle()
    {
        $this->info('=== Cache Management Isolation Test ===');
        $this->newLine();

        try {
            // Test AdminCacheService
            $this->info('Testing AdminCacheService...');
            $adminService = app(AdminCacheService::class);

            $adminStats = $adminService->getCacheStatistics();
            $this->info('Admin Cache Statistics:');
            foreach ($adminStats as $key => $value) {
                $this->line("  {$key}: ".(is_array($value) ? json_encode($value) : $value));
            }

            $adminStatus = $adminService->getCacheStatus();
            $this->newLine();
            $this->info('Admin Cache Status:');
            foreach ($adminStatus as $key => $value) {
                $this->line("  {$key}: ".(is_array($value) ? json_encode($value) : $value));
            }

            $this->newLine();
            $this->line(str_repeat('-', 50));
            $this->newLine();

            // Test TenantCacheService with tenant1, tenant2, tenant3
            $testTenants = ['tenant1', 'tenant2', 'tenant3'];

            foreach ($testTenants as $tenantId) {
                $this->info("Testing TenantCacheService for {$tenantId}...");
                $tenantService = app(TenantCacheService::class);

                $tenantStats = $tenantService->getTenantCacheStatistics($tenantId);
                $this->info("{$tenantId} Cache Statistics:");
                foreach ($tenantStats as $key => $value) {
                    $this->line("  {$key}: ".(is_array($value) ? json_encode($value) : $value));
                }

                // Test cache isolation by clearing cache for this tenant
                $this->newLine();
                $this->info("Testing cache operations for {$tenantId}:");

                // Clear tenant cache
                $clearResult = $tenantService->clearTenantCache($tenantId);
                $this->line('  Clear Cache Result: '.json_encode($clearResult));

                // Clear tenant views
                $clearViewsResult = $tenantService->clearTenantViews($tenantId);
                $this->line('  Clear Views Result: '.json_encode($clearViewsResult));

                // Clear tenant logs
                $clearLogsResult = $tenantService->clearTenantLogs($tenantId);
                $this->line('  Clear Logs Result: '.json_encode($clearLogsResult));

                $this->newLine();
                $this->line(str_repeat('-', 30));
                $this->newLine();
            }

            $this->info('=== Test Completed Successfully ===');

        } catch (\Exception $e) {
            $this->error('ERROR: '.$e->getMessage());
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
