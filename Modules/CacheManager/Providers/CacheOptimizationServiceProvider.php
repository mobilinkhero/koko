<?php

namespace Modules\CacheManager\Providers;

use App\Facades\AdminCache;
use Illuminate\Support\ServiceProvider;

class CacheOptimizationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            $this->getConfigFile() => config_path('cachemanager-cache-optimization.php'),
        ], 'config');

        $this->scheduleOptimizationCheck();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigFile(), 'cachemanager-cache-optimization');

        $this->registerCommands();
    }

    /**
     * Schedule cache optimization job if enabled in config
     */
    protected function scheduleOptimizationCheck(): void
    {
        add_action('after_scheduling_tasks_registered', function ($schedule, $timezone = null) {
            $enabled = config('cachemanager-cache-optimization.job_schedule.enabled');
            $settings = get_batch_settings(['system.timezone']);
            $timezone = $settings['system.timezone'] ?? config('app.timezone');

            if ($enabled) {
                $schedule->command('cachemanager:optimize-cache --queue')
                    ->everyMinute()
                    ->timezone($timezone)
                    ->withoutOverlapping();
            }
        }, 10, 2);

        add_filter('check_optimize_cache_status', function ($status) {
            return AdminCache::get('optimize_cache_status');
        });
    }

    /**
     * Register cache optimization commands
     */
    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            \Modules\CacheManager\Console\Commands\CacheOptimizationCommand::class,
        ]);
    }

    /**
     * Get configuration file path
     */
    protected function getConfigFile(): string
    {
        return __DIR__.
            DIRECTORY_SEPARATOR.'..'.
            DIRECTORY_SEPARATOR.'Config'.
            DIRECTORY_SEPARATOR.'cache-optimization.php';
    }
}
