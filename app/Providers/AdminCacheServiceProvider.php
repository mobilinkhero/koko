<?php

namespace App\Providers;

use App\Observers\CacheInvalidationObserver;
use App\Services\Cache\AdminCacheManager;
use App\Services\Cache\AdminCacheTagRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Admin Cache Service Provider
 *
 * Registers the centralized admin cache management system,
 * including the cache manager, tag registry, and observers.
 */
class AdminCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register the tag registry as singleton
        $this->app->singleton(AdminCacheTagRegistry::class, function ($app) {
            return new AdminCacheTagRegistry;
        });

        // Register the cache manager as singleton
        $this->app->singleton(AdminCacheManager::class, function ($app) {
            return new AdminCacheManager(
                $app->make(AdminCacheTagRegistry::class)
            );
        });

        // Register alias for easier access
        $this->app->alias(AdminCacheManager::class, 'admin.cache');
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/admin-cache.php' => config_path('admin-cache.php'),
        ], 'admin-cache-config');

        // Register model observers for automatic cache invalidation
        if (config('admin-cache.auto_invalidation', true)) {
            $this->registerModelObservers();
        }

        // Warm up critical caches if enabled
        if (config('admin-cache.warmup.on_boot', false)) {
            $this->warmupCriticalCaches();
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\AdminCacheCommand::class,
                \App\Console\Commands\AdminCacheStatsCommand::class,
                \App\Console\Commands\AdminCacheClearCommand::class,
                \App\Console\Commands\AdminCacheWarmCommand::class,
            ]);
        }
    }

    /**
     * Register model observers for cache invalidation
     */
    protected function registerModelObservers(): void
    {
        $models = [
            \App\Models\User::class,
            \App\Models\Plan::class,
            \App\Models\Tenant::class,
            \Spatie\Permission\Models\Role::class,
            \Spatie\Permission\Models\Permission::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(CacheInvalidationObserver::class);
            }
        }
    }

    /**
     * Warm up critical caches on application boot
     */
    protected function warmupCriticalCaches(): void
    {
        if (config('admin-cache.warmup.enabled', true)) {
            // Use queue for warming if specified
            $queue = config('admin-cache.warmup.queue');

            if ($queue) {
                // Dispatch warmup job to queue
                // WarmupAdminCacheJob::dispatch()->onQueue($queue);
            } else {
                // Warm up immediately (be careful in production)
                try {
                    $cacheManager = $this->app->make(AdminCacheManager::class);
                    $cacheManager->warm('critical');
                } catch (\Exception $e) {
                    // Log error but don't fail the application boot
                    app_log('Failed to warm up admin cache on boot', 'error', $e, ['error' => $e->getMessage()]);
                }
            }
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            AdminCacheManager::class,
            AdminCacheTagRegistry::class,
            'admin.cache',
        ];
    }
}
