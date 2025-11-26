<?php

namespace App\Models\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'user_id',
        'tenant_id',
        'rate_limits',
        'monthly_quota',
        'usage_current_month',
        'expires_at',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'abilities' => 'array',
        'rate_limits' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'monthly_quota' => 'integer',
        'usage_current_month' => 'integer',
    ];

    protected $hidden = ['token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if token has specific ability
     */
    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities) || in_array($ability, $abilities);
    }

    /**
     * Generate a new API token with secure hashing
     */
    public static function generate(array $data): array
    {
        // Fire before create hook
        do_action('api_token.before_create', $data);

        $plainToken = 'apitk_'.Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        $token = static::create([
            ...$data,
            'token' => $hashedToken,
        ]);

        // Fire after create hook
        do_action('api_token.after_create', $token);

        return [
            'token' => $token,
            'plain_text_token' => $plainToken,
        ];
    }

    /**
     * Check if token can make request (active, not expired, under quota)
     */
    public function canMakeRequest(): bool
    {
        return $this->is_active &&
               (! $this->expires_at || $this->expires_at > Carbon::now()) &&
               $this->usage_current_month < $this->monthly_quota;
    }

    /**
     * Check if tenant's plan allows API access
     */
    public function tenantHasApiAccess(): bool
    {
        $tenant = $this->tenant;
        if (! $tenant || ! $tenant->activeSubscription) {
            return false;
        }

        return $tenant->activeSubscription->plan->hasFeature('api_access');
    }

    /**
     * Get plan-based quota limit
     */
    public function getPlanQuotaLimit(): int
    {
        $tenant = $this->tenant;
        if (! $tenant || ! $tenant->activeSubscription) {
            return 0;
        }

        return (int) $tenant->activeSubscription->plan->getFeatureValue('api_quota', 1000);
    }

    /**
     * Reset monthly usage (called by scheduler)
     */
    public static function resetMonthlyUsage(): void
    {
        static::query()->update(['usage_current_month' => 0]);
    }

    /**
     * Track API usage
     */
    public function trackUsage(): void
    {
        // Fire before use hook
        do_action('api_token.before_use', $this);

        $this->increment('usage_current_month');
        $this->update(['last_used_at' => Carbon::now()]);

        // Fire after use hook
        do_action('api_token.after_use', $this);

        // Check if quota exceeded
        if ($this->monthly_quota > 0 && $this->usage_current_month >= $this->monthly_quota) {
            do_action('api_token.quota_exceeded', $this);
        }
    }
}
