<?php

namespace Corbital\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getSettingsClasses()
 * @method static array all()
 * @method static mixed group(string $group)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool set(string $key, mixed $value)
 * @method static bool setBatch(string $group, array $settings)
 * @method static bool clearCache()
 * @method static bool refreshCache()
 * @method static \Corbital\Settings\TenantSettingsManager forTenant(string|int $tenantId)
 * @method static \Corbital\Settings\TenantSettingsManager forCurrentTenant()
 *
 * @see \Corbital\Settings\SettingsManager
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}
