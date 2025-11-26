<?php

namespace Corbital\ModuleManager\Facades;

use Illuminate\Support\Facades\Facade;

class ModuleEvents extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'module-events';
    }
}
