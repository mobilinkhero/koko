<?php

namespace Corbital\Installer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isAppInstalled()
 * @method static string installedFileLocation()
 * @method static bool isInstalling()
 * @method static bool requiresInstallation()
 * @method static bool markAsInstalled()
 * @method static array|null getInstallationInfo()
 * @method static bool reset()
 *
 * @see \Corbital\Installer\Installer
 */
class Installer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'installer';
    }
}
