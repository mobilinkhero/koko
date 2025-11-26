<?php

namespace App\Multitenancy;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class PathTenantFinder extends TenantFinder
{
    /**
     * Reserved paths that should not be treated as tenant subdomains.
     */
    private const RESERVED_PATHS = [
        'admin', 'api', 'login', 'register', 'password', 'install',
        'validate', 'debug', 'assets', 'storage', 'health', 'telescope',
    ];

    /**
     * Find tenant for the given request with optimized caching and validation.
     */
    public function findForRequest(Request $request): ?Tenant
    {
        $subdomain = $this->extractSubdomainFromPath($request);

        if (! $subdomain || $this->isReservedPath($subdomain)) {
            return null;
        }

        return $this->findTenantBySubdomain($subdomain);
    }

    /**
     * Extract subdomain from the request path.
     */
    private function extractSubdomainFromPath(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        if (empty($path) || $path === '/') {
            return null;
        }

        $segments = explode('/', $path);
        $subdomain = $segments[0] ?? null;

        // Validate subdomain format
        if (! $subdomain || ! $this->isValidSubdomain($subdomain)) {
            return null;
        }

        return $subdomain;
    }

    /**
     * Check if the given path is reserved.
     */
    private function isReservedPath(string $path): bool
    {
        return in_array(strtolower($path), self::RESERVED_PATHS, true);
    }

    /**
     * Validate subdomain format.
     */
    private function isValidSubdomain(string $subdomain): bool
    {
        // Basic subdomain validation
        return preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?$/i', $subdomain) === 1;
    }

    /**
     * Find tenant by subdomain with efficient caching.
     */
    private function findTenantBySubdomain(string $subdomain): ?Tenant
    {
        $cacheKey = "tenant_lookup_{$subdomain}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($subdomain) {
            $tenant = Tenant::select([
                'id', 'company_name', 'subdomain', 'domain',
                'status', 'expires_at', 'created_at', 'updated_at',
            ])
                ->where('subdomain', $subdomain)
                ->first();

            return $tenant;
        });
    }
}
