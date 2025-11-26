<?php

namespace App\Models;

use App\Contracts\Team;
use App\Events\Tenant\TenantCreated;
use App\Events\Tenant\TenantDeleted;
use App\Events\Tenant\TenantStatusChanged;
use App\Events\Tenant\TenantUpdated;
use App\Traits\HasSubscription;
use Corbital\Settings\Models\TenantSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

/**
 * @property int $id
 * @property string|null $company_name
 * @property string $domain Custom domain if available
 * @property string $subdomain Subdomain for tenant access
 * @property string|null $stripe_customer_id
 * @property string $status
 * @property array<array-key, mixed>|null $custom_colors Tenant UI customization colors
 * @property string|null $timezone
 * @property bool|null $has_custom_domain
 * @property array<array-key, mixed>|null $features_config Tenant-specific feature configuration
 * @property string|null $address
 * @property int|null $country_id
 * @property string|null $payment_method
 * @property array<array-key, mixed>|null $payment_details
 * @property string|null $billing_name
 * @property string|null $billing_email
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_zip_code
 * @property string|null $billing_country
 * @property string|null $billing_phone
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Subscription $activeSubscription
 * @property-read \App\Models\User|null $adminUser
 * @property-read string $formatted_status
 * @property-read string $status_badge
 * @property-read string $url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentMethod> $paymentMethods
 * @property-read int|null $payment_methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TenantSetting> $settings
 * @property-read int|null $settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tickets\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 *
 * @method static \Spatie\Multitenancy\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Database\Factories\TenantFactory factory($count = null, $state = [])
 * @method static \Spatie\Multitenancy\TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBillingZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCustomColors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereFeaturesConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereHasCustomDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant wherePaymentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStripeCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereSubdomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Tenant extends BaseTenant implements Team
{
    use HasFactory, HasSubscription;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_name',
        'domain',
        'subdomain',
        'status',
        'custom_colors',
        'timezone',
        'has_custom_domain',
        'features_config',
        'address',
        'country_id',
        'payment_method',
        'payment_details',
        'billing_name',
        'billing_email',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip_code',
        'billing_country',
        'billing_phone',
        'expires_at',
        'stripe_customer_id',
        'deleted_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'deleted_date' => 'datetime',
        'custom_colors' => 'json',
        'features_config' => 'json',
        'has_custom_domain' => 'boolean',
        'payment_details' => 'json',
        'address' => 'string',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TenantCreated::class,
        'deleted' => TenantDeleted::class,
        'updated' => TenantUpdated::class,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            do_action('tenant.before_create', $tenant);
        });

        static::created(function ($tenant) {
            do_action('tenant.after_create', $tenant);
        });

        static::updating(function ($tenant) {
            do_action('tenant.before_update', $tenant);

            $changedAttributes = $tenant->getDirty();

            // Check for status change
            if (isset($changedAttributes['status'])) {
                $oldStatus = $tenant->getOriginal('status');
                $newStatus = $changedAttributes['status'];

                if ($oldStatus === 'active' && $newStatus !== 'active') {
                    do_action('tenant.before_deactivate', $tenant);
                } elseif ($oldStatus !== 'active' && $newStatus === 'active') {
                    do_action('tenant.before_activate', $tenant);
                }

                event(new TenantStatusChanged(
                    $tenant,
                    $tenant->getOriginal('status'),
                    $changedAttributes['status']
                ));
            }
        });

        static::updated(function ($tenant) {
            do_action('tenant.after_update', $tenant);

            // Check for status change
            if ($tenant->wasChanged('status')) {
                $oldStatus = $tenant->getOriginal('status');
                $newStatus = $tenant->status;

                if ($oldStatus === 'active' && $newStatus !== 'active') {
                    do_action('tenant.after_deactivate', $tenant);
                } elseif ($oldStatus !== 'active' && $newStatus === 'active') {
                    do_action('tenant.after_activate', $tenant);
                }

                do_action('tenant.status_changed', $tenant, ['old_status' => $oldStatus, 'new_status' => $newStatus]);
            }
        });

        static::deleting(function ($tenant) {
            do_action('tenant.before_delete', $tenant);
        });

        static::deleted(function ($tenant) {
            do_action('tenant.after_delete', $tenant);
        });

    }

    public function getFillable()
    {
        // Apply filter to allow modules to add fillable fields
        $fillable = apply_filters('post_fillable', $this->fillable);

        return array_unique($fillable);
    }

    /**
     * Get the tenant's active subscription.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where(function ($query) {
                $query->whereNull('current_period_ends_at')
                    ->orWhere('current_period_ends_at', '>', now());
            })
            ->latest()
            ->withDefault();
    }

    /**
     * Get all subscriptions for the tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the tenant's settings.
     */
    public function settings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    // In your Tenant model
    public function adminUser()
    {
        return $this->hasOne(User::class)->where('is_admin', 1);
    }

    /**
     * Get the tickets where this user is the client.
     */
    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Tickets\Models\Ticket::class, 'tenant_id');
    }

    /**
     * Get the current tenant with proper type handling.
     */
    public static function current(): ?static
    {
        $tenant = BaseTenant::current();

        if (! $tenant) {
            return null;
        }

        // Use request-level caching to prevent duplicate queries
        if ($tenant && get_class($tenant) === BaseTenant::class) {
            try {
                $tenantId = $tenant->getKey();
                // Use the cache service to avoid repeated lookups
                $cachedTenant = app(\App\Services\TenantCacheService::class)->remember($tenantId);
                if ($cachedTenant) {
                    return $cachedTenant;
                }

                return static::find($tenantId);
            } catch (\Exception $e) {
                app_log('Error getting cached tenant', 'error', $e);

                return static::find($tenant->getKey());
            }
        }

        return $tenant;
    }

    /**
     * Check if the tenant is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the tenant is marked for deletion.
     */
    public function isMarkedForDeletion(): bool
    {
        // Directly query the database to avoid attribute loading issues
        if (! $this->exists) {
            return false;
        }

        $result = static::where('id', $this->id)->whereNotNull('deleted_date')->exists();

        return $result;
    }

    /**
     * Check if the tenant data has been permanently deleted.
     * This checks if the tenant is marked for deletion and subscription has expired.
     *
     * Business Logic: Tenants should have access until their subscription period ends,
     * even if marked for deletion or subscription is cancelled, as long as the period is paid for.
     */
    public function isDataDeleted(): bool
    {
        if (! $this->isMarkedForDeletion()) {
            return false;
        }

        // Check if subscription period is still valid (regardless of cancellation)
        // This ensures tenants keep access for the period they paid for
        $hasValidSubscriptionPeriod = $this->subscriptions()
            ->where(function ($query) {
                $query->whereNull('current_period_ends_at') // Lifetime subscriptions
                    ->orWhere('current_period_ends_at', '>', now()); // Or not yet expired
            })
            ->exists();

        // Only block access if no valid subscription period exists
        return ! $hasValidSubscriptionPeriod;
    }

    /**
     * Get the full URL for this tenant.
     */
    public function getUrlAttribute(): string
    {
        if ($this->has_custom_domain && $this->domain) {
            return 'https://'.$this->domain;
        }

        // In path-based mode, return the path
        return url($this->subdomain);
    }

    /**
     * Get the formatted status of the tenant.
     */
    public function getFormattedStatusAttribute(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }

        return $this->status;
    }

    /**
     * Get tenant's status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        $status = $this->getFormattedStatusAttribute();

        $classes = match ($status) {
            'active' => 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300',
            'deactive' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300',
            'suspended' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300',
            'expired' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
            default => 'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-300',
        };

        return '<span class="px-2 py-1 text-xs font-medium rounded-full '.$classes.'">'.ucfirst($status).'</span>';
    }

    /**
     * Get the domain for this tenant.
     */
    public function getDomainAttribute(): string
    {
        return $this->attributes['domain'] ?? "{$this->subdomain}.".config('app.domain', 'whatsmark.com');
    }

    // Required for Spatie Team interface
    public function getTeamId()
    {
        return $this->id;
    }

    /**
     * Get auto billing data for the customer.
     */
    public function getAutoBillingData(?string $type = null)
    {
        $defaultPaymentMethod = $this->defaultPaymentMethod();

        if (! $defaultPaymentMethod) {
            return null;
        }

        if ($type && $defaultPaymentMethod->type !== $type) {
            // Try to find a payment method of the requested type
            $paymentMethod = $this->paymentMethods()->where('type', $type)->first();

            if (! $paymentMethod) {
                return null;
            }

            return $paymentMethod;
        }

        return $defaultPaymentMethod;
    }

    /**
     * Get the Stripe account country for the billable entity.
     *
     * @return string|null
     */
    public function stripeAccount()
    {
        return null; // Use default Stripe account
    }

    /**
     * Check if the tenant has a default payment method.
     * This method provides compatibility if Cashier method is missing.
     */
    public function hasDefaultPaymentMethod(): bool
    {
        try {
            // Try to use Cashier's method first
            if (method_exists($this, 'defaultPaymentMethod')) {
                return ! is_null($this->defaultPaymentMethod());
            }

            // Fallback: Check if stripe_id exists and has payment methods
            if (empty($this->stripe_customer_id)) {
                return false;
            }

            $paymentMethods = $this->paymentMethods();

            return $paymentMethods->isNotEmpty();
        } catch (\Exception $e) {
            payment_log('Error checking default payment method', 'warning', [
                'tenant_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function defaultPaymentMethod()
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    /**
     * Get the payment methods for the customer.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the Stripe SDK client configured with the tenant's API key.
     *
     * @return \Stripe\StripeClient
     */
    public function stripe()
    {
        $settings = get_batch_settings(['payment.stripe_secret']);
        $stripe = new \Stripe\StripeClient($settings['payment.stripe_secret']);

        return $stripe;
    }

    public function getCreatedAtAttribute($value)
    {
        $timezone = $this->getTimezone();

        return \Carbon\Carbon::parse($value)->setTimezone($timezone);
    }

    public function getUpdatedAtAttribute($value)
    {
        $timezone = $this->getTimezone();

        return \Carbon\Carbon::parse($value)->setTimezone($timezone);
    }

    public function getTimezone()
    {
        if (Tenant::checkCurrent()) {
            $systemSettings = tenant_settings_by_group('system');

            return $systemSettings['timezone'] ?? config('app.timezone');
        } else {
            $systemSettings = get_batch_settings(['system.timezone']);

            return $systemSettings['system.timezone'] ?? config('app.timezone');
        }
    }
}
