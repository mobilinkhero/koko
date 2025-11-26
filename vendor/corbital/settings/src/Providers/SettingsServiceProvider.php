<?php

namespace Corbital\Settings\Providers;

use Corbital\Settings\Commands\BackupSettingsCommand;
use Corbital\Settings\Commands\ClearSettingsCacheCommand;
use Corbital\Settings\Commands\ExportSettingsCommand;
use Corbital\Settings\Commands\ImportSettingsCommand;
use Corbital\Settings\Commands\RefreshSettingsCacheCommand;
use Corbital\Settings\Commands\RestoreSettingsCommand;
use Corbital\Settings\Events\SettingCreated;
use Corbital\Settings\Events\SettingDeleted;
use Corbital\Settings\Events\SettingUpdated;
use Corbital\Settings\Listeners\ClearSettingsCache;
use Corbital\Settings\SettingsManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__.'/../../config/settings.php',
            'settings'
        );

        // Register the Settings Manager singleton
        $this->app->singleton('settings', function ($app) {
            return new SettingsManager($app);
        });

        // Register the settings facade
        $this->app->alias('settings', SettingsManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('tenant.settings', \Corbital\Settings\Http\Middleware\ScopeTenantSettings::class);

        // Publish config file
        $this->publishes([
            __DIR__.'/../../config/settings.php' => config_path('settings.php'),
        ], 'corbital-settings-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'corbital-settings-migrations');

        // Publish all assets
        $this->publishes([
            __DIR__.'/../../config/settings.php' => config_path('settings.php'),
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'corbital-settings');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearSettingsCacheCommand::class,
                RefreshSettingsCacheCommand::class,
                ExportSettingsCommand::class,
                ImportSettingsCommand::class,
                BackupSettingsCommand::class,
                RestoreSettingsCommand::class,
            ]);
        }

        // Register event listeners
        Event::listen(SettingCreated::class, ClearSettingsCache::class);
        Event::listen(SettingUpdated::class, ClearSettingsCache::class);
        Event::listen(SettingDeleted::class, ClearSettingsCache::class);

        // Load helper file
        $this->loadHelpers();
    }

    /**
     * Load helper files.
     */
    protected function loadHelpers(): void
    {
        require_once __DIR__.'/../Helpers/SettingsHelper.php';

        // Check if tenant functionality is enabled, then load tenant helpers
        if (config('settings.enable_tenant_support', true)) {
            if (file_exists(__DIR__.'/../Helpers/TenantSettingsHelper.php')) {
                require_once __DIR__.'/../Helpers/TenantSettingsHelper.php';
            }
        }
    }
}
