<?php

namespace Corbital\Installer\Http\Middleware;

use Closure;
use Corbital\Installer\Installer;
use Illuminate\Http\Request;

class CanInstall
{
    /**
     * The installer instance.
     */
    protected Installer $installer;

    /**
     * Create a new middleware instance.
     */
    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If the application is already installed, redirect to the home page
        // unless the user is on the finished page
        if (
            $this->installer->isAppInstalled() && ! $request->is(config('installer.routes.prefix').'/finished')
        ) {
            return redirect('/');
        }

        return $next($request);
    }
}
