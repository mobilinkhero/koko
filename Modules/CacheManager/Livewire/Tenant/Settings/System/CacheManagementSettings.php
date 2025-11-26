<?php

namespace Modules\CacheManager\Livewire\Tenant\Settings\System;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\CacheManager\Services\TenantCacheService;

class CacheManagementSettings extends Component
{
    public array $cacheSizes = [];

    public array $cacheStatus = [];

    public string $loadingType = '';

    public ?bool $enable_wp_log = false;

    protected ?TenantCacheService $tenantCacheService = null;

    public function mount()
    {
        // Create service instance directly to avoid dependency injection issues
        try {
            $this->tenantCacheService = new TenantCacheService;
        } catch (\Exception $e) {
            app_log('Failed to create TenantCacheService: ', 'error', $e, ['error' => $e->getMessage()]);
            $this->tenantCacheService = null;
        }

        $settings = get_tenant_setting_by_tenant_id('whatsapp', 'logging', '', tenant_id());
        $logging = json_decode($settings);

        $this->enable_wp_log = filter_var($logging->enabled ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->calculateSizes();
        $this->loadCacheStatus();
    }

    public function loadCacheStatus(): void
    {
        if ($this->tenantCacheService === null) {
            $this->cacheStatus = [
                'error' => 'Cache service not initialized',
                'debug' => 'Service is null in loadCacheStatus method',
            ];

            return;
        }

        try {
            $this->cacheStatus = $this->tenantCacheService->getCacheStatus(tenant_id());
        } catch (\Exception $e) {
            $this->cacheStatus = [
                'error' => 'Failed to get cache status: '.$e->getMessage(),
                'debug' => 'Exception in getCacheStatus: '.$e->getTraceAsString(),
            ];
        }
    }

    public function calculateSizes(): void
    {
        // Calculate tenant-specific cache sizes
        $cacheTypes = ['framework', 'views', 'config', 'routing', 'logs'];

        $this->cacheSizes = [];
        foreach ($cacheTypes as $type) {
            $this->cacheSizes[$type] = $this->getTenantCacheSize($type);
        }
    }

    public function clearCache(string $type): void
    {
        $this->loadingType = $type;
        $tenantId = tenant_id();

        // Reinitialize service if it's null (Livewire component state issue)
        if ($this->tenantCacheService === null) {
            try {
                $this->tenantCacheService = new TenantCacheService;
            } catch (\Exception $e) {
                $this->loadingType = '';
                $this->notify([
                    'type' => 'danger',
                    'message' => t('cache_service_not_initialized'),
                ]);

                return;
            }
        }

        try {
            $result = match ($type) {
                'framework' => $this->tenantCacheService->clearTenantCache($tenantId),
                'views' => $this->tenantCacheService->clearTenantViews($tenantId),
                'config' => $this->clearTenantConfig($tenantId),
                'routing' => $this->clearTenantRouting($tenantId),
                'logs' => $this->tenantCacheService->clearTenantLogs($tenantId),
                default => throw new \InvalidArgumentException("Invalid cache type: {$type}"),
            };

            // Update only the cleared cache size using tenant-specific calculation
            $this->cacheSizes[$type] = $this->getTenantCacheSize($type);

            $this->loadingType = '';

            // Refresh cache status
            $this->loadCacheStatus();

            $message = $result['message'] ?? (Str::headline($type).' '.t('cache_cleared_successfully'));
            $this->notify([
                'type' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            $this->loadingType = '';
            report($e);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_clear_cache').': '.$e->getMessage(),
            ]);
        }
    }

    private function getDirectoryPath(string $type): string
    {
        $tenantId = tenant_id();

        return match ($type) {
            'framework' => storage_path('framework/cache'), // Framework cache (filtered for tenant)
            'views' => storage_path('framework/views'),     // Views cache (filtered for tenant)
            'config' => storage_path('framework/cache'),    // Config cache (tenant-specific keys)
            'routing' => storage_path('framework/cache'),   // Route cache (tenant-specific keys)
            'logs' => storage_path("logs/tenant/{$tenantId}"),
            default => throw new \InvalidArgumentException("Invalid cache type: {$type}"),
        };
    }

    private function getDirectorySize(string $path): string
    {
        if (! is_dir($path)) {
            return '0 B';
        }

        try {
            $tenantId = tenant_id();
            $size = 0;

            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $filename = $file->getBasename();

                    // Only count tenant-specific files
                    if ($this->isTenantSpecificFile($filename, $tenantId)) {
                        $size += $file->getSize();
                    }
                }
            }

            return $this->formatSizeUnits($size);
        } catch (\Exception $e) {
            report($e);

            return '0 B';
        }
    }

    /**
     * Check if a file is tenant-specific based on filename patterns
     */
    private function isTenantSpecificFile(string $filename, string $tenantId): bool
    {
        // Patterns that indicate tenant-specific files
        $tenantPatterns = [
            "tenant_{$tenantId}_",
            "_{$tenantId}_",
            "tenant{$tenantId}",
            "{$tenantId}_tenant_",
        ];

        foreach ($tenantPatterns as $pattern) {
            if (strpos($filename, $pattern) !== false) {
                return true;
            }
        }

        // For framework views, check if it contains tenant ID
        if (strpos($filename, $tenantId) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get tenant-specific cache size for a given cache type
     */
    private function getTenantCacheSize(string $type): string
    {
        $tenantId = tenant_id();

        return match ($type) {
            'framework' => $this->getTenantFrameworkCacheSize($tenantId),
            'views' => $this->getTenantViewsCacheSize($tenantId),
            'config' => $this->getTenantConfigCacheSize($tenantId),
            'routing' => $this->getTenantRouteCacheSize($tenantId),
            'logs' => $this->getDirectorySize(storage_path("logs/tenant/{$tenantId}")),
            default => '0 B',
        };
    }

    /**
     * Get size of tenant-specific framework cache files
     */
    private function getTenantFrameworkCacheSize(string $tenantId): string
    {
        try {
            // For framework cache, we want to show the actual tenant cache data size
            // This represents the main cache entries for this tenant
            if ($this->tenantCacheService !== null) {
                $manager = new \App\Services\Cache\TenantCacheManager($tenantId);
                $stats = $manager->getStatistics();

                // The total_size from stats is already formatted as a string
                $cacheSize = $stats['total_size'] ?? '0 B';
                if ($cacheSize !== '0 B' && $cacheSize !== 'N/A') {
                    return $cacheSize;
                }
            }

            // Fallback: check for tenant-specific framework cache files on disk
            $frameworkCachePath = storage_path('framework/cache');
            if (is_dir($frameworkCachePath)) {
                $totalSize = 0;
                $files = glob($frameworkCachePath.'/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $filename = basename($file);
                        if ($this->isTenantSpecificFile($filename, $tenantId)) {
                            $totalSize += filesize($file);
                        }
                    }
                }

                if ($totalSize > 0) {
                    return $this->formatSizeUnits($totalSize);
                }
            }

            return '0 B';
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    /**
     * Get size of tenant-specific compiled views
     */
    private function getTenantViewsCacheSize(string $tenantId): string
    {
        $viewsPath = storage_path('framework/views');
        if (! is_dir($viewsPath)) {
            return '0 B';
        }

        try {
            $size = 0;
            $files = glob($viewsPath.'/*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    $filename = basename($file);
                    if ($this->isTenantSpecificFile($filename, $tenantId)) {
                        $size += filesize($file);
                    }
                }
            }

            return $this->formatSizeUnits($size);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    /**
     * Get size of tenant-specific config cache
     */
    private function getTenantConfigCacheSize(string $tenantId): string
    {
        try {
            $size = 0;

            // Count tenant-specific config cache keys
            $configKeys = [
                "config_tenant_{$tenantId}",
                "tenant_{$tenantId}_config",
                "tenant_settings_{$tenantId}",
                "tenant_config_{$tenantId}",
            ];

            foreach ($configKeys as $key) {
                if (Cache::has($key)) {
                    // Estimate size (actual size calculation would require serialization)
                    $size += 1024; // Rough estimate per config entry
                }
            }

            return $this->formatSizeUnits($size);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    /**
     * Get size of tenant-specific route cache
     */
    private function getTenantRouteCacheSize(string $tenantId): string
    {
        try {
            $size = 0;

            // Check for route-related cache in tenant cache
            if ($this->tenantCacheService !== null) {
                $manager = new \App\Services\Cache\TenantCacheManager($tenantId);
                $stats = $manager->getStatistics();

                // Get total keys to estimate route cache
                $totalKeys = $stats['total_keys'] ?? 0;

                // If tenant has cache keys, estimate route cache as a small percentage
                if ($totalKeys > 0) {
                    // Rough estimate: route cache is about 5-10% of total cache
                    $routeEstimate = max(512, $totalKeys * 100); // At least 512 bytes, or 100 bytes per key
                    $size += min($routeEstimate, 8192); // Cap at 8KB for routes
                }
            }

            // Also count tenant-specific route cache keys in global cache
            $routeKeys = [
                "routes_tenant_{$tenantId}",
                "tenant_{$tenantId}_routes",
                "tenant_navigation_{$tenantId}",
                "tenant_menu_{$tenantId}",
            ];

            foreach ($routeKeys as $key) {
                if (Cache::has($key)) {
                    $size += 1024; // 1KB estimate per route entry
                }
            }

            return $this->formatSizeUnits($size);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    private function clearLogFiles(): void
    {
        $tenantId = tenant_id();
        if (! $tenantId) {
            return;
        }

        // Tenant-specific log directory
        $tenantLogPath = storage_path("logs/tenant/{$tenantId}");

        if (File::exists($tenantLogPath)) {
            try {
                // Get all log files in the tenant's log directory
                $files = File::glob("{$tenantLogPath}/*.log");

                // Preserve WhatsApp logs and today's Laravel log
                $keepFiles = [
                    'whatsapp.log',
                    'laravel-'.now()->format('Y-m-d').'.log', // Keep today's main log file
                    'payment-'.now()->format('Y-m-d').'.log',  // Keep today's payment log file
                ];

                foreach ($files as $file) {
                    $fileName = basename($file);
                    // Skip WhatsApp logs and specified log files
                    if (! in_array($fileName, $keepFiles) && ! Str::startsWith($fileName, 'whats')) {
                        File::delete($file);
                    }
                }
            } catch (\Exception $e) {
                report($e);
                app_log('Failed to clear tenant log files', 'error', $e, [], $tenantId);
            }
        }
    }

    private function formatSizeUnits(int $bytes): string
    {
        return match (true) {
            $bytes >= 1_073_741_824 => number_format($bytes / 1_073_741_824, 2).' GB',
            $bytes >= 1_048_576 => number_format($bytes / 1_048_576, 2).' MB',
            $bytes >= 1_024 => number_format($bytes / 1_024, 2).' KB',
            $bytes > 1 => "{$bytes} bytes",
            $bytes === 1 => '1 byte',
            default => '0 B',
        };
    }

    public function toggleEnableWpLog()
    {
        try {

            $settings = get_tenant_setting_by_tenant_id('whatsapp', 'logging', '', tenant_id());

            $logging = is_string($settings)
                ? json_decode($settings, true)
                : (array) $settings;

            $this->enable_wp_log = ! ($logging['enabled'] ?? false);

            $logging['enabled'] = $this->enable_wp_log;

            save_tenant_setting('whatsapp', 'logging', json_encode($logging));

            $this->notify([
                'type' => 'success',
                'message' => t('whatsapp_log_updated'),
            ]);
        } catch (\Exception $e) {
            report($e);

            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_update_whatsapp_log_setting').': '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Clear tenant-specific config cache
     */
    private function clearTenantConfig(string $tenantId): array
    {
        try {
            // Clear tenant-specific config cache keys
            $configKeys = [
                "config_tenant_{$tenantId}",
                "tenant_{$tenantId}_config",
                "tenant_settings_{$tenantId}",
                "tenant_config_{$tenantId}",
            ];

            $cleared = 0;
            foreach ($configKeys as $key) {
                if (Cache::forget($key)) {
                    $cleared++;
                }
            }

            // Also clear via TenantCache facade
            if ($this->tenantCacheService !== null) {
                $manager = new \App\Services\Cache\TenantCacheManager($tenantId);
                $manager->invalidateByTags(['config', 'settings']);
            }

            return [
                'message' => "Cleared {$cleared} tenant config cache entries for tenant {$tenantId}",
                'keys_cleared' => $cleared,
            ];
        } catch (\Exception $e) {
            return [
                'message' => 'Failed to clear tenant config cache: '.$e->getMessage(),
                'keys_cleared' => 0,
            ];
        }
    }

    /**
     * Clear tenant-specific routing cache (limited scope)
     */
    private function clearTenantRouting(string $tenantId): array
    {
        try {
            // Clear tenant-specific route cache keys
            $routeKeys = [
                "routes_tenant_{$tenantId}",
                "tenant_{$tenantId}_routes",
                "tenant_navigation_{$tenantId}",
                "tenant_menu_{$tenantId}",
            ];

            $cleared = 0;
            foreach ($routeKeys as $key) {
                if (Cache::forget($key)) {
                    $cleared++;
                }
            }

            // Also clear via TenantCache facade
            if ($this->tenantCacheService !== null) {
                $manager = new \App\Services\Cache\TenantCacheManager($tenantId);
                $manager->invalidateByTags(['routes', 'navigation', 'menu']);
            }

            return [
                'message' => $cleared > 0
                    ? "Cleared {$cleared} tenant route cache entries for tenant {$tenantId}"
                    : "No tenant-specific route cache found for tenant {$tenantId}",
                'keys_cleared' => $cleared,
            ];
        } catch (\Exception $e) {
            return [
                'message' => 'Failed to clear tenant route cache: '.$e->getMessage(),
                'keys_cleared' => 0,
            ];
        }
    }

    public function render()
    {
        return view('CacheManager::livewire.tenant.settings.system.cache-management-settings');
    }
}
