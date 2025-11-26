<?php

namespace Corbital\Installer\Http\Middleware;

use Closure;
use Corbital\Installer\Installer;
use Illuminate\Http\Request;

class RedirectIfInstalled
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
        if ($this->installer->isAppInstalled()) {
            return redirect('/');
        }

        return $next($request);
    }
}
