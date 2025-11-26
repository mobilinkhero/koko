<?php

namespace Corbital\ModuleManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateModuleBackendRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        do_action('app.middleware.validate_module', $request);

        return $next($request);
    }
}
