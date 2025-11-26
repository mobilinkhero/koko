<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/GeneralHelper.php');
        require_once app_path('Helpers/TenantHelper.php');
        require_once app_path('Helpers/WhatsappHelper.php');
        require_once app_path('Helpers/TicketHelper.php');
        require_once app_path('Helpers/AdminHelper.php');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
