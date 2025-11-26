<?php

namespace Modules\ApiWebhookManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ApiWebhookManager\Traits\HasWebhooks;

class ApiWebhookManagerServiceProvider extends ServiceProvider
{
    use HasWebhooks;

    /**
     * The module name.
     *
     * @var string
     */
    protected $moduleName = 'ApiWebhookManager';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(base_path('Modules/'.$this->moduleName.'/Database/Migrations'));
        // Routes are now handled by RouteServiceProvider
        $this->manageMenuItem();

        add_action('model.booted', function ($class) {
            $this->registerWebhookHooks($class);
        });
    }

    private function registerWebhookHooks($class)
    {
        // Add hooks to models that should have webhook functionality
        $models = [
            'App\Models\Tenant\Status',
            'App\Models\Tenant\Source',
            'App\Models\Tenant\Contact',
        ];

        if (in_array($class, $models)) {
            foreach ($models as $modelClass) {
                $this->addWebhookToModel($modelClass);
            }
        }
    }

    private function addWebhookToModel($model)
    {
        $model::created(function ($instance) {
            $this->triggerWebhook('created', $instance);
        });

        $model::updated(function ($instance) {
            $this->triggerWebhook('updated', $instance, $instance->getOriginal());
        });

        $model::deleted(function ($instance) {
            $this->triggerWebhook('deleted', $instance);
        });
    }

    private function manageMenuItem()
    {
        add_filter('tenant_settings_navigation', function ($menu) {
            $customItems = [
                'api' => [
                    'label' => 'api_management',
                    'route' => 'tenant.api-management.settings.view',
                    'icon' => 'heroicon-m-arrows-up-down',
                    'condition' => 'module_exists("ApiWebhookManager") && module_enabled("ApiWebhookManager")',
                    'feature_required' => 'enable_api',
                ],
                'webhook' => [
                    'label' => 'webhook_management',
                    'route' => 'tenant.webhook.settings.view',
                    'icon' => 'carbon-webhook',
                    'condition' => 'module_exists("ApiWebhookManager") && module_enabled("ApiWebhookManager")',
                ],
            ];

            return array_merge($menu, $customItems);
        });

        add_filter('admin_system_settings_navigation', function ($menu) {
            $customItems = [
                'api' => [
                    'label' => 'api_management',
                    'route' => 'admin.api.settings.view',
                    'icon' => 'heroicon-m-arrows-up-down',
                    'condition' => 'module_exists("ApiWebhookManager") && module_enabled("ApiWebhookManager")',
                    'feature_required' => 'enable_api',
                ],
            ];

            return array_merge($menu, $customItems);
        });

        add_filter('admin_settings_navigation_route', function ($menu) {

            $menu['api'] = 'Modules\\ApiWebhookManager\\Livewire\\Admin\\Settings\\System\\ApiSettings';

            return $menu;
        });
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
            base_path('Modules/'.$this->moduleName.'/Config/config.php'), $this->moduleName
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

        $this->loadViewsFrom(array_merge([$sourcePath], [
            $viewPath,
        ]), $this->moduleName);
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
