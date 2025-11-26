<?php

namespace Corbital\Installer\Http\Middleware;

use Closure;
use Corbital\Installer\Classes\UpdateChecker;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateBackendRequest
{
    protected $updateChecker;

    public function __construct(UpdateChecker $updateChecker)
    {
        $this->updateChecker = $updateChecker;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Validate the request
        $result = $this->updateChecker->validateRequest();

        if (! $result) {
            return redirect()->route('validate');
        }

        do_action('app.middleware.redirect_if_not_installed', $request);

        return $next($request);
    }
}
