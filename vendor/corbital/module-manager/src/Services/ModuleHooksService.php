<?php

namespace Corbital\ModuleManager\Services;

use Corbital\ModuleManager\Classes\ModuleInstall;
use Corbital\ModuleManager\Facades\ModuleEvents;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModuleHooksService
{
    /**
     * Fire hooks for module lifecycle events
     */
    public function fireModuleHook(string $hook, string $moduleName, array $data = [])
    {
        $moduleManager = app('module.manager');
        $module = $moduleManager->get($moduleName);

        // Prepare hook data
        $hookData = array_merge([
            'module_name' => $moduleName,
            'timestamp' => now()->toDateTimeString(),
            'item_id' => $module['info']['license_product_id'] ?? null,
            'version' => $module['info']['version'] ?? null,
        ], $data);

        // Fire the hook using ModuleEvents
        return ModuleEvents::trigger("module.{$hook}", $hookData);
    }

    /**
     * Validate module activation request
     */
    public function validateModuleActivation(string $moduleName)
    {
        $result = $this->fireModuleHook('before_activate', $moduleName);

        return $result;
    }

    /**
     * Validate module activation request
     */
    public function processAfterActivation(string $moduleName)
    {
        $result = $this->fireModuleHook('after_activate', $moduleName);

        return $result;
    }

    /**
     * Validate module activation request
     */
    public function processAfterDeActivation(string $moduleName)
    {
        $result = $this->fireModuleHook('after_deactivate', $moduleName);

        return $result;
    }

    public function processAfterRemoval(string $moduleName)
    {
        $result = $this->fireModuleHook('after_remove', $moduleName);

        return $result;
    }

    /**
     * Validate Envato purchase code for module activation
     */
    public function validateEnvatoPurchase(string $moduleName, string $username, string $purchaseCode)
    {
        // Store the validation attempt
        $this->logValidationAttempt($moduleName, $username, $purchaseCode);

        // Fire hook to allow custom validation
        $result = $this->fireModuleHook('envato_validation', $moduleName, [
            'username' => $username,
            'purchase_code' => $purchaseCode,
        ]);

        return $result;
    }

    /**
     * Log validation attempt for auditing
     */
    protected function logValidationAttempt(string $moduleName, string $username, string $purchaseCode)
    {
        try {
            DB::table('module_validation_logs')->insert([
                'module_name' => $moduleName,
                'username' => $username,
                'purchase_code' => substr($purchaseCode, 0, 8).'***', // Partial code for security
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => Auth::id(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the validation
            app_log("Failed to log validation attempt for module {$moduleName}", 'warning', $e);
        }
    }

    /**
     * Check if module requires Envato validation
     */
    public function requiresEnvatoValidation(string $moduleName)
    {
        // Core modules don't require validation
        $moduleManager = app('module.manager');
        $module = $moduleManager->get($moduleName);

        if (! $module) {
            return false;
        }

        // Check if it's a core module
        if (isset($module['info']['type']) && ($module['info']['type'] === 'core' || $module['info']['type'] === 'custom')) {
            return false;
        }

        // Check if validation is disabled for this module
        if (isset($module['info']['skv']) && $module['info']['skv']) {
            return false;
        }

        $moduleInstall = new ModuleInstall;
        $item_id = $module['info']['license_product_id'] ?? null;

        return $moduleInstall->requiresInstallation($item_id);
    }

    /**
     * Get all available module hooks
     */
    public function getAvailableHooks()
    {
        return [
            'before_activate' => 'Fired before module activation',
            'after_activate' => 'Fired after module activation',
            'before_deactivate' => 'Fired before module deactivation',
            'after_deactivate' => 'Fired after module deactivation',
            'before_install' => 'Fired before module installation',
            'after_install' => 'Fired after module installation',
            'before_uninstall' => 'Fired before module uninstallation',
            'after_uninstall' => 'Fired after module uninstallation',
            'envato_validation' => 'Fired during Envato purchase validation',
            'module_uploaded' => 'Fired when a module is uploaded',
            'module_validated' => 'Fired when a module passes validation',
        ];
    }
}
