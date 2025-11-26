<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Coupon extends BaseModel
{
    use HasFactory;

    const TYPE_PERCENTAGE = 'percentage';

    const TYPE_FIXED_AMOUNT = 'fixed_amount';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'usage_limit',
        'usage_count',
        'usage_limit_per_customer',
        'starts_at',
        'expires_at',
        'minimum_amount',
        'maximum_discount',
        'applicable_plans',
        'applicable_billing_periods',
        'first_payment_only',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_plans' => 'array',
        'applicable_billing_periods' => 'array',
        'first_payment_only' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Validation Methods
     */
    public function isValid(): bool
    {
        return $this->is_active &&
               ! $this->isExpired() &&
               ! $this->isUsageLimitReached() &&
               $this->hasStarted();
    }

    public function hasStarted(): bool
    {
        return is_null($this->starts_at) || Carbon::now()->gte($this->starts_at);
    }

    public function isExpired(): bool
    {
        return ! is_null($this->expires_at) && Carbon::now()->gt($this->expires_at);
    }

    public function isUsageLimitReached(): bool
    {
        return ! is_null($this->usage_limit) && $this->usage_count >= $this->usage_limit;
    }

    public function canBeUsedByTenant(int $tenantId): bool
    {
        if (is_null($this->usage_limit_per_customer)) {
            return true;
        }

        $tenantUsageCount = $this->usages()->where('tenant_id', $tenantId)->count();

        return $tenantUsageCount < $this->usage_limit_per_customer;
    }

    public function isApplicableToPlan(int $planId): bool
    {
        if (is_null($this->applicable_plans) || empty($this->applicable_plans)) {
            return true;
        }

        return in_array($planId, $this->applicable_plans);
    }

    public function isApplicableToBillingPeriod(string $period): bool
    {
        if (is_null($this->applicable_billing_periods) || empty($this->applicable_billing_periods)) {
            return true;
        }

        return in_array($period, $this->applicable_billing_periods);
    }

    public function meetsMinimumAmount(float $amount): bool
    {
        return is_null($this->minimum_amount) || $amount >= $this->minimum_amount;
    }

    /**
     * Calculation Methods
     */
    public function calculateDiscount(float $amount): float
    {
        if (! $this->meetsMinimumAmount($amount)) {
            return 0;
        }

        $discount = 0;

        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = $amount * ($this->value / 100);
        } else {
            $discount = $this->value;
        }

        // Apply maximum discount limit if set
        if (! is_null($this->maximum_discount) && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        // Ensure discount doesn't exceed the amount
        return min($discount, $amount);
    }

    public function getFormattedDiscount(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return number_format($this->value, $this->value == intval($this->value) ? 0 : 2).'%';
        }

        return '$'.number_format($this->value, 2);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_PERCENTAGE => 'Percentage',
            self::TYPE_FIXED_AMOUNT => 'Fixed Amount',
            default => 'Unknown'
        };
    }

    /**
     * Usage Methods
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }

    public function getRemainingUsage(): ?int
    {
        if (is_null($this->usage_limit)) {
            return null; // Unlimited
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    public function getTenantUsageCount(int $tenantId): int
    {
        return $this->usages()->where('tenant_id', $tenantId)->count();
    }

    public function getTenantRemainingUsage(int $tenantId): ?int
    {
        if (is_null($this->usage_limit_per_customer)) {
            return null; // Unlimited for this tenant
        }

        $used = $this->getTenantUsageCount($tenantId);

        return max(0, $this->usage_limit_per_customer - $used);
    }
}
