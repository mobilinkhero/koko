<?php

namespace Modules\CacheManager\Livewire\Admin\Settings\System;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\CacheManager\Services\AdminCacheService;

class CacheManagementSettings extends Component
{
    public array $cacheSizes = [];

    public array $cacheStatus = [];

    public string $loadingType = '';

    public ?bool $environment = false;

    public ?bool $production_mode = false;

    public bool $storageLinked = false;

    protected ?AdminCacheService $adminCacheService = null;

    protected function rules()
    {
        return [
            'environment' => ['nullable', 'boolean'],
            'production_mode' => ['nullable', 'boolean'],
        ];
    }

    public function mount()
    {
        // Create service instance directly to avoid dependency injection issues
        try {
            $this->adminCacheService = new AdminCacheService;
        } catch (\Exception $e) {
            $this->adminCacheService = null;
        }

        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);
            // Don't return here, just show the error - the component still needs to be functional
            // return redirect(route('admin.dashboard'));
        }

        // Use config values instead of env for better caching support
        $this->environment = config('app.debug');

        $this->production_mode = config('app.env') !== 'local';

        $publicStoragePath = public_path('storage');

        if (File::exists($publicStoragePath) && File::isDirectory($publicStoragePath)) {
            $this->storageLinked = true;
        }

        $this->calculateSizes();
        $this->loadCacheStatus();
    }

    public function loadCacheStatus(): void
    {
        if ($this->adminCacheService === null) {
            $this->cacheStatus = [
                'error' => 'Cache service not initialized',
                'debug' => 'Service is null in loadCacheStatus method',
            ];

            return;
        }

        try {
            $this->cacheStatus = $this->adminCacheService->getCacheStatus();
        } catch (\Exception $e) {
            $this->cacheStatus = [
                'error' => 'Failed to get cache status: '.$e->getMessage(),
                'debug' => 'Exception in getCacheStatus: '.$e->getTraceAsString(),
            ];
        }
    }

    public function calculateSizes(): void
    {
        $directories = [
            'framework' => storage_path('framework/cache'),
            'views' => storage_path('framework/views'),
            'config' => base_path('bootstrap/cache'),
            'routing' => base_path('bootstrap/cache'), // Route cache is stored here in Laravel 11
            'logs' => storage_path('logs'),
            'storage' => '',
        ];

        $this->cacheSizes = array_map(
            fn ($path) => $this->getDirectorySize($path),
            $directories
        );
    }

    public function clearCache(string $type): void
    {
        $this->loadingType = $type;

        // Reinitialize service if it's null (Livewire component state issue)
        if ($this->adminCacheService === null) {
            try {
                $this->adminCacheService = new AdminCacheService;
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
                'framework' => $this->adminCacheService->clearApplicationCache(),
                'views' => $this->adminCacheService->clearViewCache(),
                'config' => $this->adminCacheService->clearConfigCache(),
                'routing' => $this->adminCacheService->clearRouteCache(),
                'logs' => $this->adminCacheService->clearApplicationLogs(),
                'storage' => $this->linkStorage(),
                default => throw new \InvalidArgumentException("Invalid cache type: {$type}"),
            };

            // Update cache sizes after clearing
            if ($type !== 'storage') {
                $this->cacheSizes[$type] = $this->getDirectorySize($this->getDirectoryPath($type));

                $message = $result['message'] ?? (Str::headline($type).' '.t('cache_cleared_successfully'));
                $this->notify([
                    'type' => 'success',
                    'message' => $message,
                ]);
            }

            // Refresh cache status
            $this->loadCacheStatus();

        } catch (\Exception $e) {
            report($e);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_clear_cache').': '.$e->getMessage(),
            ]);
        }

        $this->loadingType = '';
    }

    private function getDirectoryPath(string $type): string
    {
        return match ($type) {
            'framework' => storage_path('framework/cache'),
            'views' => storage_path('framework/views'),
            'config' => base_path('bootstrap/cache'),
            'routing' => base_path('bootstrap/cache'), // Route cache is in bootstrap/cache
            'logs' => storage_path('logs'),
            'storage' => storage_path('app'),
            default => throw new \InvalidArgumentException("Invalid cache type: {$type}"),
        };
    }

    private function getDirectorySize(string $path): string
    {
        if (! is_dir($path)) {
            return '0 B';
        }

        try {
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }

            return $this->formatSizeUnits($size);
        } catch (\Exception $e) {
            report($e);

            return '0 B';
        }
    }

    private function clearRouteCache(): bool
    {
        try {
            // Clear Laravel 11 route cache files
            $routeCacheFiles = [
                base_path('bootstrap/cache/routes-v7.php'),
                base_path('bootstrap/cache/routes.php'), // Also check for older format
                base_path('bootstrap/cache/routes-tenant.php'), // Tenant routes cache file
            ];

            foreach ($routeCacheFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            // Also run the Artisan command to ensure complete cleanup
            Artisan::call('route:clear');

            return true;
        } catch (\Exception $e) {
            report($e);
            app_log('Failed to clear route cache', 'error', $e);

            return false;
        }
    }

    private function clearLogFiles(): void
    {
        $path = storage_path('logs');

        try {
            // Get all log files in the main logs directory (admin logs)
            $files = File::glob("{$path}/*.log");

            // Preserve WhatsApp logs and today's Laravel log
            $keepFiles = [
                'whatsapp.log',
                'laravel-'.now()->format('Y-m-d').'.log', // Keep today's main log file
                'payment-'.now()->format('Y-m-d').'.log',  // Keep today's payment log file
            ];

            foreach ($files as $file) {
                $fileName = basename($file);

                // Skip files in the tenant directory
                if (Str::contains($file, 'logs/tenant')) {
                    continue;
                }

                // Skip WhatsApp logs and specified log files
                if (! in_array($fileName, $keepFiles) && ! Str::startsWith($fileName, 'whats')) {
                    File::delete($file);
                }
            }
        } catch (\Exception $e) {
            report($e);
            throw $e;
        }
    }

    private function linkStorage(): void
    {
        $publicStoragePath = public_path('storage');

        if (File::exists($publicStoragePath)) {
            if (! is_link($publicStoragePath)) {
                if (File::isDirectory($publicStoragePath)) {
                    File::deleteDirectory($publicStoragePath);
                } elseif (File::isFile($publicStoragePath)) {
                    unlink($publicStoragePath);
                }
            }
        }

        Artisan::call('storage:link');

        $this->notify([
            'type' => 'success',
            'message' => t('storage_linked_successfully'),
        ]);
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

    public function toggleEnvironment()
    {
        try {
            $this->environment = ! $this->environment;
            $this->updateEnvVariable('APP_DEBUG', $this->environment ? 'true' : 'false');

            $this->notify([
                'type' => 'success',
                'message' => t('environment_updated'),
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_update_environment').': '.$e->getMessage(),
            ]);
        }
    }

    public function toggleEnableProductionMode()
    {

        try {
            $this->production_mode = ! $this->production_mode;
            $this->updateEnvVariable('APP_ENV', $this->production_mode ? 'production' : 'local');

            $this->notify([
                'type' => 'success',
                'message' => $this->production_mode
                    ? t('enable_production_mode_successfully')
                    : t('disable_production_mode_successfully'),
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_update_production_mode').': '.$e->getMessage(),
            ]);
        }
    }

    protected function updateEnvVariable(string $key, string $value): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            throw new \Exception('The .env file does not exist.');
        }

        if (! is_writable($path)) {
            throw new \Exception('The .env file is not writable.');
        }

        try {
            $content = file_get_contents($path);

            // Escape the key for regex
            $escapedKey = preg_quote($key, '/');

            // If the key exists, replace its value
            if (preg_match("/^{$escapedKey}=/m", $content)) {
                $content = preg_replace("/^{$escapedKey}=.*$/m", "{$key}={$value}", $content);
            } else {
                // If the key doesn't exist, add it
                $content .= PHP_EOL."{$key}={$value}";
            }

            file_put_contents($path, $content);

            // Clear config cache to apply changes
            Artisan::call('config:clear');

            // Update the current environment variable for this request
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        } catch (\Exception $e) {
            report($e);
            throw new \Exception('Failed to update environment variable: '.$e->getMessage());
        }
    }

    /**
     * Reinitialize the admin cache service
     * This is a workaround for Livewire serialization issues
     */
    public function reinitializeService(): void
    {
        try {
            $this->adminCacheService = new AdminCacheService;
            $this->loadCacheStatus();
            $this->notify([
                'type' => 'success',
                'message' => t('cache_service_reinitialized_success'),
            ]);
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_reinitialize_cache_service').$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('CacheManager::livewire.admin.settings.system.cache-management-settings');
    }
}
