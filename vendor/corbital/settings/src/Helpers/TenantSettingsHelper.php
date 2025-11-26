<?php

use Corbital\Settings\Facades\Settings;

if (! function_exists('tenant_settings')) {
    /**
     * Get or set tenant settings.
     *
     * @param  string|int|null  $tenantId  The tenant ID
     * @param  string|null  $key  The settings key
     * @param  mixed|null  $value  The settings value
     * @param  mixed|null  $default  The default value
     * @return mixed
     */
    function tenant_settings($tenantId = null, $key = null, $value = null, $default = null)
    {
        // If no tenant ID provided, use the current tenant
        $manager = $tenantId ? app('settings')->forTenant($tenantId) : app('settings')->forCurrentTenant();

        return (is_null($key))
            ? $manager->all()
            : (! is_null($value) ? $manager->set($key, $value) : $manager->get($key, $default));
    }
}

if (! function_exists('get_tenant_setting')) {
    /**
     * Get a specific tenant setting.
     *
     * @param  string|int  $tenantId  The tenant ID
     * @param  string  $key  The settings key
     * @param  mixed|null  $default  The default value
     */
    function get_tenant_setting(string|int $tenantId, string $key, mixed $default = null): mixed
    {
        return Settings::forTenant($tenantId)->get($key, $default);
    }
}

if (! function_exists('set_tenant_setting')) {
    /**
     * Set a specific tenant setting.
     *
     * @param  string|int  $tenantId  The tenant ID
     * @param  string  $key  The settings key
     * @param  mixed  $value  The settings value
     */
    function set_tenant_setting(string|int $tenantId, string $key, mixed $value): bool
    {
        return app('settings')->forTenant($tenantId)->set($key, $value);
    }
}

function get_current_tenant_setting(string $key, mixed $default = null): mixed
{
    return app('settings')->forCurrentTenant()->get($key, $default);
}

if (! function_exists('set_current_tenant_setting')) {
    /**
     * Set a specific setting for the current tenant.
     *
     * @param  string  $key  The settings key
     * @param  mixed  $value  The settings value
     */
    function set_current_tenant_setting(string $key, mixed $value): bool
    {
        return app('settings')->forCurrentTenant()->set($key, $value);
    }
}
