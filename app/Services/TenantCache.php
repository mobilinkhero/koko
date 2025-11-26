<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

/**
 * TenantCache Service
 *
 * Provides lightweight tenant context resolution and caching mechanisms
 * to optimize tenant identification without heavy model instantiation.
 * Serves as a performance optimization layer for multi-tenant architecture.
 *
 * Key Features:
 * - Fast tenant ID resolution with multiple fallback strategies
 * - Subdomain-based tenant lookup with request-specific caching
 * - Session-based tenant context persistence
 * - Integration with Spatie multitenancy package
 * - Request fingerprinting for cache uniqueness
 *
 * Resolution Priority:
 * 1. Spatie BaseTenant::current() - Primary multitenancy context
 * 2. Session storage - Cross-request tenant persistence
 * 3. Subdomain lookup - Route-based tenant identification
 *
 * Performance Benefits:
 * - Avoids full Tenant model instantiation when only ID is needed
 * - Reduces database queries through intelligent caching
 * - Request-scoped cache prevents duplicate subdomain lookups
 * - Session fallback reduces database load for authenticated users
 *
 * Usage Examples:
 * ```php
 * // Get current tenant ID efficiently
 * $tenantId = TenantCache::getCurrentTenantId();
 *
 * // Check if tenant context is active
 * if (TenantCache::isTenantActive()) {
 *     // Proceed with tenant-specific operations
 * }
 * ```
 *
 * @see \App\Models\Tenant
 * @see \Spatie\Multitenancy\Models\Tenant
 * @see \App\Services\TenantCacheService
 *
 * @version 1.0.0
 */
class TenantCache
{
    /**
     * Retrieve current tenant ID with optimized lookup strategies
     *
     * Implements a multi-layered approach to determine the current tenant ID
     * without instantiating heavy model objects. Uses caching and session
     * storage to minimize database queries.
     *
     * Resolution Strategy:
     * 1. Check Spatie multitenancy current tenant
     * 2. Fallback to session-stored tenant ID
     * 3. Resolve from subdomain via cached database lookup
     *
     * @return int|null Tenant ID if found, null if no tenant context
     *
     * @example
     * ```php
     * $tenantId = TenantCache::getCurrentTenantId();
     *
     * if ($tenantId) {
     *     // Use tenant ID for database queries
     *     $contacts = Contact::where('tenant_id', $tenantId)->get();
     * } else {
     *     // Handle no tenant context
     *     abort(404, 'Tenant not found');
     * }
     * ```
     *
     * @see \Spatie\Multitenancy\Models\Tenant::current()
     */
    public static function getCurrentTenantId(): ?int
    {
        // First try to get from Spatie's base mechanism
        $baseTenant = BaseTenant::current();
        if ($baseTenant !== null) {
            return $baseTenant->getKey();
        }

        // Next try from session
        if (session()->has('current_tenant_id')) {
            return session('current_tenant_id');
        }

        if (! empty(Auth::user()->tenant_id)) {
            return Auth::user()->tenant_id;
        }

        // Last resort: look up from subdomain
        $subdomain = request()->route('subdomain');
        if ($subdomain) {
            // Use tenant cache with a request-level fingerprint
            $cacheKey = "tenant_subdomain_{$subdomain}_".request()->fingerprint();

            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($subdomain) {
                return Tenant::where('subdomain', $subdomain)->value('id');
            });
        }

        return null;
    }

    /**
     * Check if tenant context is currently active
     *
     * Determines if the application is currently operating within a tenant
     * context by checking both Spatie multitenancy state and session storage.
     * Useful for middleware and context-aware operations.
     *
     * @return bool True if tenant context is active, false otherwise
     *
     * @example
     * ```php
     * // In middleware or controller
     * if (!TenantCache::isTenantActive()) {
     *     return redirect()->route('tenant.select');
     * }
     *
     * // In service methods
     * if (TenantCache::isTenantActive()) {
     *     // Apply tenant-specific filters
     *     $query->where('tenant_id', TenantCache::getCurrentTenantId());
     * }
     * ```
     *
     * @see getCurrentTenantId()
     * @see \Spatie\Multitenancy\Models\Tenant::current()
     */
    public static function isTenantActive(): bool
    {
        return BaseTenant::current() !== null || session()->has('current_tenant_id');
    }
}
