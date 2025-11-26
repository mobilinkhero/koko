<?php

/**
 * Get a tenant setting with cache
 *
 * @param  mixed  $default
 * @return mixed
 */
function get_tenant_setting_cached(string $group, ?string $key = null, $default = null)
{
    return \App\Services\TenantSettingsCache::get($group, $key, $default);
}

/**
 * Clear tenant settings cache
 *
 * @return void
 */
function clear_tenant_settings_cache(?int $tenantId = null, ?string $group = null)
{
    $tenantId = $tenantId ?? (tenant_check() ? tenant_id() : null);

    if ($tenantId) {
        \App\Services\TenantSettingsCache::forget($tenantId, $group);
    }
}
