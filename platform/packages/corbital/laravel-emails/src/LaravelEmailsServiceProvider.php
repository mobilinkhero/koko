<?php

namespace Corbital\LaravelEmails;

use Corbital\LaravelEmails\Services\CustomEmailService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LaravelEmailsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Load routes - custom templates route only
        $this->loadRoutesFrom(__DIR__.'/../routes/templates.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-emails');

        // Register Blade Components
        $this->loadViewComponentsAs('laravel-emails', [
            // Register any view components here
        ]);

        // Register Blade Component aliases
        Blade::component('laravel-emails::components.email-navigation', 'laravel-emails-navigation');

        // Load migrations for templates only - only database/migrations has the migrations now
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish configurations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-emails.php' => config_path('laravel-emails.php'),
            ], 'laravel-emails-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-emails'),
            ], 'laravel-emails-views');

            // Publish migrations for templates only
            $this->publishes([
                __DIR__.'/../database/migrations/templates' => database_path('migrations'),
            ], 'laravel-emails-migrations');

            // Register and publish the seeders
            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'laravel-emails-seeders');

            $this->commands([
                \Corbital\LaravelEmails\Commands\SeedEmailTemplatesCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-emails.php', 'laravel-emails');

        // Register the main service without settings dependency
        $this->app->singleton('laravel-emails', function ($app) {
            return new CustomEmailService;
        });

        // Register the facade
        $this->app->bind('email', function ($app) {
            return $app->make('laravel-emails');
        });
    }
}
