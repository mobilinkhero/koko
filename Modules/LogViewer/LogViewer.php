<?php

namespace Modules\LogViewer;

use Corbital\ModuleManager\Support\Module;

class LogViewer extends Module
{
    /**
     * Register event listeners for this module.
     *
     * @return void
     */
    public function registerHooks()
    {
        // Register event hooks here
    }

    /**
     * Called when the module is activated.
     *
     * @return void
     */
    public function activate()
    {
        parent::activate();
        // Code to run when the module is activated
    }

    /**
     * Called when the module is deactivated.
     *
     * @return void
     */
    public function deactivate()
    {
        // Code to run when the module is deactivated
    }

    /**
     * Called after the module has been activated.
     *
     * @return void
     */
    public function activated()
    {
        // Code to run after the module is activated
    }

    /**
     * Called after the module has been deactivated.
     *
     * @return void
     */
    public function deactivated()
    {
        // Code to run after the module is deactivated
    }
}
