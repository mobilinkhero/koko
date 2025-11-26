<?php

namespace App\Providers;

use App\Services\FeatureService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FeatureService::class);

        // Make sure helper file exists before requiring it
        $helperPath = app_path('Helpers/FeatureHelper.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the @hasFeature blade directive
        Blade::directive('hasFeature', function ($expression) {
            return "<?php if (feature($expression)): ?>";
        });

        Blade::directive('endhasFeature', function () {
            return '<?php endif; ?>';
        });
    }
}
