<?php

namespace Corbital\ModuleManager\Support;

abstract class Module
{
    /**
     * Register event listeners for this module.
     *
     * @return void
     */
    public function registerHooks()
    {
        // Override this method in your module to register hooks
    }

    /**
     * Called when the module is activated.
     *
     * @return void
     */
    public function activate()
    {
        // Override this method in your module when needed
    }

    /**
     * Called after the module has been activated.
     *
     * @return void
     */
    public function activated()
    {
        // Override this method in your module if needed
    }

    /**
     * Called when the module is deactivated.
     *
     * @return void
     */
    public function deactivate()
    {
        // Override this method in your module if needed
    }

    /**
     * Called after the module has been deactivated.
     *
     * @return void
     */
    public function deactivated()
    {
        // Override this method in your module if needed
    }

    /**
     * Get an instance of the module class.
     *
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();

        return new $className;
    }
}
