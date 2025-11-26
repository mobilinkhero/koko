<?php

namespace Modules\LogViewer\Providers;

use Illuminate\Support\ServiceProvider;

class LogViewerServiceProvider extends ServiceProvider
{
    /**
     * The module name.
     *
     * @var string
     */
    protected $moduleName = 'LogViewer';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Remove the conflicting loadViewsFrom line
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(base_path('Modules/'.$this->moduleName.'/Database/Migrations'));
        // Routes are now handled by RouteServiceProvider
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the RouteServiceProvider
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    protected function registerTranslations()
    {
        $langPath = resource_path('lang/modules/'.strtolower($this->moduleName));

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleName);
        } else {
            $this->loadTranslationsFrom(base_path('Modules/'.$this->moduleName.'/resources/lang'), $this->moduleName);
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            base_path('Modules/'.$this->moduleName.'/Config/config.php') => config_path($this->moduleName.'.php'),
        ], 'config');
        $this->mergeConfigFrom(
            base_path('Modules/'.$this->moduleName.'/Config/config.php'),
            $this->moduleName
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    protected function registerViews()
    {
        $viewPath = resource_path('views/modules/'.strtolower($this->moduleName));

        $sourcePath = base_path('Modules/'.$this->moduleName.'/resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        // Register views with both lowercase and original case to ensure compatibility
        $this->loadViewsFrom(array_merge([$sourcePath], [$viewPath]), $this->moduleName);
        $this->loadViewsFrom(array_merge([$sourcePath], [$viewPath]), strtolower($this->moduleName));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
