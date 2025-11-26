<?php

namespace Corbital\ModuleManager\Providers;

use Corbital\ModuleManager\Console\Commands\ModuleActivateCommand;
use Corbital\ModuleManager\Console\Commands\ModuleDeactivateCommand;
use Corbital\ModuleManager\Console\Commands\ModuleListCommand;
use Corbital\ModuleManager\Console\Commands\ModuleMakeCommand;
use Corbital\ModuleManager\Console\Commands\ModuleRemoveCommand;
use Corbital\ModuleManager\Http\Livewire\ModuleList;
use Corbital\ModuleManager\Http\Livewire\ModuleUpdate;
use Corbital\ModuleManager\Services\ModuleEventManager;
use Corbital\ModuleManager\Services\ModuleHooksService;
use Corbital\ModuleManager\Services\ModuleManager;
use Corbital\ModuleManager\Services\SemVerParser;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ModuleManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register our config file
        $this->mergeConfigFrom(
            __DIR__.'/../../config/module.php',
            'modules'
        );

        // Register the ModuleManager as a singleton
        $this->app->singleton('module.manager', function ($app) {
            return new ModuleManager($app);
        });

        // Register the module events service
        $this->app->singleton('module-events', function ($app) {
            return new ModuleEventManager;
        });

        // Register the module hooks service
        $this->app->singleton('module.hooks', function ($app) {
            return new ModuleHooksService;
        });

        // Register the semantic versioning parser
        $this->app->singleton('semver-parser', function ($app) {
            return new SemVerParser;
        });

        // Register helpers
        $this->registerHelpers();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/module.php' => config_path('modules.php'),
        ], 'modules-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'modules-migrations');

        // Publish assets
        $this->publishes([
            __DIR__.'/../../resources/js/' => public_path('vendor/modules/js'),
            __DIR__.'/../../resources/css/' => public_path('vendor/modules/css'),
        ], 'modules-assets');

        // Publish views
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/modules'),
        ], 'modules-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'modules');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register Livewire components
        if (class_exists(Livewire::class)) {
            Livewire::component('module-list', ModuleList::class);
            Livewire::component('module-update', ModuleUpdate::class);

            // Register commands
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleListCommand::class,
                ModuleActivateCommand::class,
                ModuleDeactivateCommand::class,
                ModuleRemoveCommand::class,
                ModuleMakeCommand::class,
            ]);
        }

        // Register default hooks
        $this->registerDefaultHooks();
    }

    /**
     * Register default module hooks
     */
    protected function registerDefaultHooks()
    {
        \Corbital\ModuleManager\Hooks\DefaultModuleHooks::register();
    }

    /**
     * Register helpers.
     */
    protected function registerHelpers(): void
    {
        // Include helpers file if it exists
        if (file_exists($file = __DIR__.'/../Support/helpers.php')) {
            require $file;
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'module.manager',
            'module-events',
            'module.hooks',
            'semver-parser',
        ];
    }
}
