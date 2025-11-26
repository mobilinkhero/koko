<?php

namespace App\Models;

use App\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends BaseModel
{
    protected $fillable = [
        'coupon_id',
        'tenant_id',
        'invoice_id',
        'subscription_id',
        'discount_amount',
        'metadata',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Scopes
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForCoupon($query, int $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }

    /**
     * Accessor
     */
    public function getFormattedDiscountAttribute(): string
    {
        return '$'.number_format($this->discount_amount, 2);
    }
}
