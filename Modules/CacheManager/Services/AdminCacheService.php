<?php

namespace Modules\CacheManager\Services;

use App\Facades\AdminCache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AdminCacheService
{
    /**
     * Get cache statistics for admin dashboard
     */
    public function getCacheStatistics(): array
    {
        // Get comprehensive statistics from centralized manager
        $centralizedStats = AdminCache::getCacheStatistics();

        // Add traditional cache file information (admin-specific only)
        $traditionalStats = [
            'view_cache_size' => $this->getDirectorySize(storage_path('framework/views')),
            'route_cache_exists' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'config_cache_exists' => file_exists(base_path('bootstrap/cache/config.php')),
            'log_files_count' => count(File::glob(storage_path('logs/*.log'))),
            'log_directory_size' => $this->getDirectorySize(storage_path('logs')),
        ];

        return array_merge($centralizedStats, $traditionalStats);
    }

    /**
     * Clear application cache (admin-wide, non-tenant specific)
     * Enhanced with centralized cache manager
     */
    public function clearApplicationCache(): array
    {
        $results = [];

        // Use centralized manager for admin cache clearing
        AdminCache::flush();
        $results['admin_cache'] = 'Admin cache cleared completely';

        // Clear Laravel application cache
        Artisan::call('cache:clear');
        $results['cache_clear'] = 'Application cache cleared';

        // Clear queue cache
        try {
            Artisan::call('queue:clear');
            $results['queue_clear'] = 'Queue cache cleared';
        } catch (\Exception $e) {
            $results['queue_clear'] = 'Queue clear failed: '.$e->getMessage();
        }

        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $results['opcache'] = 'OPCache cleared';
        }

        // Update timestamp via centralized manager
        AdminCache::put('last_cleared', now()->toDateTimeString(), ['system']);

        return $results;
    }

    /**
     * Clear configuration cache
     */
    public function clearConfigCache(): array
    {
        $results = [];

        Artisan::call('config:clear');
        $results['config_clear'] = 'Configuration cache cleared';

        // Clear config-related admin cache
        AdminCache::invalidateTag('config');

        return $results;
    }

    /**
     * Clear route cache
     */
    public function clearRouteCache(): array
    {
        $results = [];

        Artisan::call('route:clear');
        $results['route_clear'] = 'Route cache cleared';

        // Clear route-related admin cache (sidebar, navigation)
        AdminCache::invalidateTag('navigation');

        return $results;
    }

    /**
     * Clear view cache
     */
    public function clearViewCache(): array
    {
        $results = [];

        Artisan::call('view:clear');
        $results['view_clear'] = 'View cache cleared';

        // Clear view-related admin cache
        AdminCache::invalidateTag('views');

        return $results;
    }

    /**
     * Clear application logs (admin only)
     */
    public function clearApplicationLogs(): array
    {
        $results = [];
        $logPath = storage_path('logs');

        if (File::exists($logPath)) {
            $logFiles = File::glob($logPath.'/*.log');
            $clearedCount = 0;

            foreach ($logFiles as $logFile) {
                // Keep today's log, clear others
                if (! str_contains(basename($logFile), now()->format('Y-m-d'))) {
                    File::delete($logFile);
                    $clearedCount++;
                }
            }

            $results['logs_cleared'] = "Cleared {$clearedCount} log files";
        }

        return $results;
    }

    /**
     * Clear all admin-related cache
     */
    public function clearAllAdminCache(): array
    {
        $results = [];

        $results = array_merge($results, $this->clearApplicationCache());
        $results = array_merge($results, $this->clearConfigCache());
        $results = array_merge($results, $this->clearRouteCache());
        $results = array_merge($results, $this->clearViewCache());

        return $results;
    }

    /**
     * Optimize application (clear + recache)
     */
    public function optimizeApplication(): array
    {
        $results = [];

        // Clear all cache first
        $results = array_merge($results, $this->clearAllAdminCache());

        // Rebuild caches
        try {
            Artisan::call('config:cache');
            $results['config_cache'] = 'Configuration cached';
        } catch (\Exception $e) {
            $results['config_cache'] = 'Config cache failed: '.$e->getMessage();
        }

        try {
            Artisan::call('route:cache');
            $results['route_cache'] = 'Routes cached';
        } catch (\Exception $e) {
            $results['route_cache'] = 'Route cache failed: '.$e->getMessage();
        }

        try {
            Artisan::call('view:cache');
            $results['view_cache'] = 'Views cached';
        } catch (\Exception $e) {
            $results['view_cache'] = 'View cache failed: '.$e->getMessage();
        }

        // Warm admin cache with common tags
        AdminCache::warm('system');
        $results['admin_cache_warm'] = 'Admin cache warmed successfully';

        return $results;
    }

    /**
     * Get directory size in MB
     */
    private function getDirectorySize(string $directory): string
    {
        if (! File::exists($directory)) {
            return '0 MB';
        }

        $size = 0;
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return round($size / 1024 / 1024, 2).' MB';
    }

    /**
     * Get cache status information
     */
    public function getCacheStatus(): array
    {
        try {
            // Get AdminCache statistics using getCacheStatistics method
            $adminStats = AdminCache::getCacheStatistics();

            return [
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_driver' => config('queue.default'),
                'mail_driver' => config('mail.default'),
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'cache_stores' => array_keys(config('cache.stores', [])),
                'storage_linked' => is_link(public_path('storage')),
                'last_cleared' => $this->getLastClearTime(),

                // Enhanced AdminCache statistics
                'total_keys' => $adminStats['total_keys'] ?? 0,
                'total_size' => $adminStats['total_size'] ?? 'N/A',
                'hit_rate' => $adminStats['hit_rate'] ?? 'N/A',
                'cache_uptime' => $adminStats['uptime'] ?? 'Unknown',
                'cache_health' => $adminStats['health']['status'] ?? 'unknown',
            ];
        } catch (\Exception $e) {
            app_log('Failed to get cache status', 'error', $e);

            return [
                'error' => 'Failed to retrieve cache status: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get the last cache clear time from a marker file
     */
    private function getLastClearTime(): ?string
    {
        $markerFile = storage_path('framework/cache/admin_last_clear.marker');

        if (file_exists($markerFile)) {
            return date('Y-m-d H:i:s', filemtime($markerFile));
        }

        return null;
    }

    /**
     * Update the last clear time marker
     */
    private function updateLastClearTime(): void
    {
        $markerFile = storage_path('framework/cache/admin_last_clear.marker');
        $markerDir = dirname($markerFile);

        if (! File::exists($markerDir)) {
            File::makeDirectory($markerDir, 0755, true);
        }

        File::put($markerFile, now()->toDateTimeString());
    }
}
