<?php

namespace Corbital\Installer\Providers;

use Corbital\Installer\Http\Middleware\CanInstall;
use Corbital\Installer\Http\Middleware\RedirectIfInstalled;
use Corbital\Installer\Http\Middleware\RedirectIfNotInstalled;
use Corbital\Installer\Installer;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/installer.php',
            'installer'
        );

        // Register the main class
        $this->app->singleton('installer', function ($app) {
            return new Installer;
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register the middleware
        $this->registerMiddleware();

        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/installer.php' => $this->app->configPath('installer.php'),
            ], 'installer-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/installer'),
            ], 'installer-views');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'installer-migrations');

            // Publish models
            $this->publishes([
                __DIR__.'/../Models/User.php' => $this->app->path('Models/User.php'),
                // Add any other user-related models here
            ], ['installer-models', 'force']);

        }
    }

    /**
     * Register middleware in Laravel 11.
     */
    protected function registerMiddleware(): void
    {
        // Register named middleware aliases
        $this->app['router']->aliasMiddleware('redirectIfInstalled', RedirectIfInstalled::class);
        $this->app['router']->aliasMiddleware('redirectIfNotInstalled', RedirectIfNotInstalled::class);
        $this->app['router']->aliasMiddleware('canInstall', CanInstall::class);

        // Register global middleware for Laravel 11
        $this->app->singleton('middleware.global', function () {
            return [
                // Add RedirectIfNotInstalled to run globally, which will check if app is not installed
                // and redirect to /install if necessary
                RedirectIfNotInstalled::class,
            ];
        });

        // Note: In Laravel 11, you can't modify middleware groups from a service provider
        // Users will need to add the middleware in their bootstrap/app.php file
    }
}
