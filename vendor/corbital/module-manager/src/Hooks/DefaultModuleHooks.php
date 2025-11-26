<?php

namespace Corbital\ModuleManager\Hooks;

use App\Services\LanguageService;
use Corbital\ModuleManager\Classes\ModuleInstall;
use Corbital\ModuleManager\Classes\ModuleUpdateChecker;
use Corbital\ModuleManager\Facades\ModuleEvents;
use Corbital\ModuleManager\Models\Module;
use Illuminate\Support\Facades\Artisan;

/**
 * Example hooks implementation for module lifecycle events
 */
class DefaultModuleHooks
{
    /**
     * Register default hooks
     */
    public static function register()
    {
        // Register Envato validation hook
        ModuleEvents::listen('module.envato_validation', [self::class, 'validateEnvatoPurchase'], 10);

        // Register general module activation validation
        ModuleEvents::listen('module.before_activate', [self::class, 'beforeModuleActivation'], 10);

        // Register post-activation hook
        ModuleEvents::listen('module.after_activate', [self::class, 'afterModuleActivation'], 10);

        // Register pre-deactivation hook
        ModuleEvents::listen('module.after_deactivate', [self::class, 'afterModuleDeactivation'], 10);

        ModuleEvents::listen('module.after_remove', [self::class, 'afterModuleRemoval'], 10);
    }

    /**
     * Validate Envato purchase code
     */
    public static function validateEnvatoPurchase($data)
    {
        $username = $data['username'] ?? '';
        $purchaseCode = $data['purchase_code'] ?? '';
        $moduleName = $data['module_name'] ?? '';
        $item_id = $data['item_id'] ?? null;

        // If validation is disabled, allow activation
        if (! config('modules.hooks.envato_validation.enabled', true)) {
            return $data;
        }

        $response = self::isValidEnvatoPurchase($username, $purchaseCode, $moduleName, $item_id);

        return $response;
    }

    /**
     * Validate Envato purchase code (example implementation)
     */
    protected static function isValidEnvatoPurchase($username, $purchaseCode, $moduleName, $item_id)
    {
        // Example API call structure (implement this based on Envato API documentation)
        try {
            // Get the domain for validation
            $moduleRegister = new ModuleUpdateChecker;
            $response = $moduleRegister->validateEnvatoPurchase($username, $purchaseCode, $moduleName, $item_id);

            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'License registration failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Before module activation hook
     */
    public static function beforeModuleActivation($data)
    {
        $moduleInstall = new ModuleInstall;

        if (session('module_license_data')) {
            $module_update_checker = new ModuleUpdateChecker;
            $module_update_checker->installVersion(session('module_license_data'), $data);
        }

        $moduleInstall->markAsInstalled($data);
    }

    /**
     * After module activation hook
     */
    public static function afterModuleActivation($data)
    {
        Module::where('item_id', $data['item_id'])->update(['active' => true]);

        $languageService = new LanguageService;
        $languageService->syncModulesToMasterFile('admin');
        $languageService->syncModulesToMasterFile('tenant');

        Artisan::call('cache:clear');
    }

    /**
     * Before module deactivation hook
     */
    public static function afterModuleDeactivation($data)
    {
        $moduleInstall = new ModuleInstall;

        // Mark the module as deactivated
        Module::where('item_id', $data['item_id'])->update(['active' => false]);

        // Perform any additional cleanup if needed
        $moduleInstall->reset($data['item_id']);
    }

    public static function afterModuleRemoval($data)
    {
        $moduleInstall = new ModuleInstall;
        // Mark the module as removed
        Module::where('item_id', $data['item_id'])->delete();
        // Perform any additional cleanup if needed
        $moduleInstall->reset($data['item_id']);
    }
}
