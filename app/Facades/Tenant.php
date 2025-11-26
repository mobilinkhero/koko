<?php

namespace App\Facades;

use App\Models\Tenant as TenantModel;
use Illuminate\Support\Facades\Facade;

/**
 * Tenant Facade
 *
 * Provides access to tenant-related functionality in the multi-tenant WhatsApp SaaS application.
 * This facade simplifies interaction with the current tenant context and tenant management operations.
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Models\Tenant
 * @see \App\Providers\TenantServiceProvider
 * @see \Spatie\Multitenancy\Models\Tenant
 *
 * @method static TenantModel|null current() Get the current active tenant instance with caching
 * @method static bool checkCurrent() Check if there is a current tenant set in the application context
 *
 * // Static methods available from the underlying Tenant model:
 * @method static TenantModel|null find(int $id) Find a tenant by ID
 * @method static TenantModel|null findOrFail(int $id) Find a tenant by ID or throw an exception
 * @method static \Illuminate\Database\Eloquent\Collection<int, TenantModel>|TenantModel[] all() Get all tenants
 * @method static TenantModel create(array $attributes) Create a new tenant
 * @method static \Illuminate\Database\Eloquent\Builder<TenantModel> where(string $column, mixed $operator = null, mixed $value = null) Query tenants with where clause
 * @method static \Illuminate\Database\Eloquent\Builder<TenantModel> whereStatus(string $status) Query tenants by status
 * @method static \Illuminate\Database\Eloquent\Builder<TenantModel> whereSubdomain(string $subdomain) Query tenants by subdomain
 * @method static \Illuminate\Database\Eloquent\Builder<TenantModel> whereDomain(string $domain) Query tenants by custom domain
 * @method static void forgetCurrent() Clear the current tenant from application context
 * @method static void makeCurrent(TenantModel $tenant) Set a specific tenant as the current active tenant
 *
 * // Instance methods available on tenant objects (when using current()):
 * The current() method returns a TenantModel instance with the following capabilities:
 *
 * - **Identification & URLs:**
 *   - ->id: Primary key of the tenant
 *   - ->subdomain: Tenant's subdomain (e.g., 'demo' for demo.app.com)
 *   - ->domain: Custom domain if configured
 *   - ->url: Full URL to access the tenant (custom domain or subdomain-based)
 *   - ->company_name: Display name of the tenant organization
 *
 * - **Status & Lifecycle:**
 *   - ->status: Current tenant status ('active', 'suspended', 'deactive', etc.)
 *   - ->formatted_status: Formatted status considering expiration
 *   - ->status_badge: HTML badge for displaying status with appropriate styling
 *   - ->expires_at: When the tenant expires (if applicable)
 *   - ->isExpired(): Check if tenant has expired
 *   - ->created_at: When tenant was created
 *   - ->updated_at: Last modification timestamp
 *
 * - **Subscription Management:**
 *   - ->activeSubscription: Current active subscription relationship
 *   - ->subscriptions: All subscription history
 *   - ->hasDefaultPaymentMethod(): Check if payment method is configured
 *   - ->defaultPaymentMethod(): Get default payment method
 *   - ->paymentMethods: Collection of payment methods
 *
 * - **Configuration:**
 *   - ->timezone: Tenant's timezone setting
 *   - ->custom_colors: UI customization colors (JSON)
 *   - ->features_config: Feature toggles and limits (JSON)
 *   - ->has_custom_domain: Whether tenant uses custom domain
 *   - ->settings(): Tenant-specific settings relationship
 *
 * - **Billing & Payments:**
 *   - ->stripe_customer_id: Stripe customer identifier
 *   - ->billing_*: Billing address and contact information
 *   - ->payment_method: Preferred payment method type
 *   - ->payment_details: Payment configuration (JSON)
 *
 * - **Team Integration:**
 *   - ->adminUser: Primary admin user for the tenant
 *   - ->getTeamId(): Get team identifier for authorization
 *
 * - **Multi-tenancy Operations:**
 *   - ->makeCurrent(): Set this tenant as the active context
 *   - ->execute($callback): Execute code in this tenant's context
 *   - ->settings()->get(): Retrieve tenant-specific settings
 *
 * @example Basic Usage:
 * ```php
 * // Check if tenant context is available
 * if (Tenant::checkCurrent()) {
 *     $tenant = Tenant::current();
 *     echo "Current tenant: " . $tenant->company_name;
 *     echo "Status: " . $tenant->formatted_status;
 *     echo "URL: " . $tenant->url;
 * }
 * ```
 * @example Tenant Information:
 * ```php
 * $tenant = Tenant::current();
 * if ($tenant) {
 *     // Basic info
 *     $name = $tenant->company_name;
 *     $subdomain = $tenant->subdomain;
 *     $isExpired = $tenant->isExpired();
 *
 *     // Status checking
 *     if ($tenant->status === 'active' && !$tenant->isExpired()) {
 *         // Tenant is fully operational
 *     }
 *
 *     // Subscription info
 *     $subscription = $tenant->activeSubscription;
 *     if ($subscription->exists) {
 *         $plan = $subscription->plan->name;
 *     }
 * }
 * ```
 * @example Querying Tenants (Admin Context):
 * ```php
 * // Find specific tenant
 * $tenant = Tenant::whereSubdomain('demo')->first();
 *
 * // Get active tenants
 * $activeTenants = Tenant::whereStatus('active')->get();
 *
 * // Check expired tenants
 * $expiredTenants = Tenant::where('expires_at', '<', now())->get();
 * ```
 *
 * @note This facade is designed for path-based multi-tenancy where tenants are accessed
 *       via URL paths like /tenant-subdomain/dashboard rather than subdomains.
 * @note The facade automatically handles caching through TenantCacheService to optimize
 *       performance and reduce database queries for frequently accessed tenant data.
 *
 * @warning Always check if a tenant exists before accessing its properties:
 *          `if (Tenant::checkCurrent()) { ... }` or `if ($tenant = Tenant::current()) { ... }`
 *
 * @see \App\Services\TenantCacheService For tenant caching implementation
 * @see \App\Services\TenantManager For advanced tenant context switching
 * @see \App\Models\Subscription For subscription management
 * @see \App\Http\Middleware\EnsureTenantExists For tenant resolution middleware
 */
class Tenant extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tenant';
    }
}
