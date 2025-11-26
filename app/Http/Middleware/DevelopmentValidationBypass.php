<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DevelopmentValidationBypass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // PERMANENT BYPASS - Always disable validation
        // Disable validation for all paths
        $this->disableValidationForPaths($request);

        return $next($request);
    }

    /**
     * Determine if we should bypass validation
     * PERMANENT BYPASS - Always enabled
     */
    private function shouldBypass(): bool
    {
        return true; // Always bypass all validations
    }

    /**
     * Disable validation for ALL paths - PERMANENT BYPASS
     */
    private function disableValidationForPaths(Request $request): void
    {
        // PERMANENT BYPASS - Set flags for all requests
        app()->instance('bypass_validation', true);
        app()->instance('bypass_licenses', true);
        app()->instance('bypass_rates', true);
    }

    /**
     * Check if current path matches pattern
     */
    private function pathMatches(string $current, string $pattern): bool
    {
        if ($pattern === $current) {
            return true;
        }

        if (str_ends_with($pattern, '*')) {
            $prefix = rtrim($pattern, '*');
            return str_starts_with($current, $prefix);
        }

        return false;
    }
}