<?php

use Corbital\ModuleManager\Models\Module;

if (! function_exists('_module_path')) {
    /**
     * Get the path to a module directory.
     *
     * @param  string  $name  Module name
     * @param  string  $path  Path within the module
     * @return string
     */
    function _module_path($name, $path = '')
    {
        $modulePath = rtrim(config('modules.directory', app_path('Modules')), '/').'/'.$name;

        return $path ? $modulePath.'/'.ltrim($path, '/') : $modulePath;
    }
}

if (! function_exists('module_asset')) {
    /**
     * Generate an asset path for a module.
     *
     * @param  string  $name  Module name
     * @param  string  $path  Path within the module's public directory
     * @return string
     */
    function module_asset($name, $path)
    {
        $assetPath = 'modules/'.strtolower($name).'/'.ltrim($path, '/');

        return asset($assetPath);
    }
}

if (! function_exists('module_config')) {
    /**
     * Get a module configuration value.
     *
     * @param  string  $name  Module name
     * @param  string  $key  Configuration key (dot notation supported)
     * @param  mixed  $default  Default value if key not found
     * @return mixed
     */
    function module_config($name, $key, $default = null)
    {
        $configKey = strtolower($name).'.'.$key;

        return config($configKey, $default);
    }
}

if (! function_exists('module_view')) {
    /**
     * Get the evaluated view contents for a module.
     *
     * @param  string  $name  Module name
     * @param  string  $view  View name within the module
     * @param  array  $data  View data
     * @param  array  $mergeData  Additional view data to merge
     * @return \Illuminate\View\View
     */
    function module_view($name, $view, $data = [], $mergeData = [])
    {
        $viewName = strtolower($name).'::'.$view;

        return view($viewName, $data, $mergeData);
    }
}

if (! function_exists('module_lang')) {
    /**
     * Get a module translation string.
     *
     * @param  string  $name  Module name
     * @param  string  $key  Translation key
     * @param  array  $replace  Replace parameters
     * @param  string|null  $locale  Locale to use
     * @return string
     */
    function module_lang($name, $key, $replace = [], $locale = null)
    {
        $langKey = strtolower($name).'::'.$key;

        return trans($langKey, $replace, $locale);
    }
}

if (! function_exists('module_exists')) {
    /**
     * Check if a module exists.
     *
     * @param  string  $name  Module name
     * @return bool
     */
    function module_exists($name)
    {
        return app('module.manager')->has($name);
    }
}

if (! function_exists('module_enabled')) {
    /**
     * Check if a module is enabled.
     *
     * @param  string  $name  Module name
     * @return bool
     */
    function module_enabled($name)
    {
        return app('module.manager')->isActive($name);
    }
}

if (! function_exists('module_disabled')) {
    /**
     * Check if a module is disabled.
     *
     * @param  string  $name  Module name
     * @return bool
     */
    function module_disabled($name)
    {
        return ! app('module.manager')->isActive($name);
    }
}

if (! function_exists('get_module')) {
    function get_module($item_id)
    {
        $module = Module::where('item_id', $item_id)->first();

        if (empty($module)) {
            return null;
        }

        $module->payload = json_decode($module->payload, true);

        return $module;
    }
}

if (! function_exists('is_minimum_version_requirement_met')) {
    function is_minimum_version_requirement_met($name)
    {
        $module = app('module.manager')->get($name);

        if (! isset($module['info']['requires_at'])) {
            return true;
        }

        $appVersion = config('installer.license_verification.current_version');
        $moduleRequiresAppVersion = $module['info']['requires_at'];

        if (version_compare($appVersion, $moduleRequiresAppVersion, '>=')) {
            return true;
        }

        return false;
    }
}
