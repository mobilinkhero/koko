<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Invoice\Invoice;
use App\Models\Subscription;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Validate a coupon code for a specific context
     */
    public function validateCoupon(
        string $code,
        int $tenantId,
        float $amount,
        int $planId,
        string $billingPeriod,
        bool $isFirstPayment = false
    ): CouponValidationResult {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon) {
            return CouponValidationResult::invalid('Coupon code not found.');
        }

        if (! $coupon->isValid()) {
            if (! $coupon->is_active) {
                return CouponValidationResult::invalid('This coupon is no longer active.');
            }
            if ($coupon->isExpired()) {
                return CouponValidationResult::invalid('This coupon has expired.');
            }
            if ($coupon->isUsageLimitReached()) {
                return CouponValidationResult::invalid('This coupon has reached its usage limit.');
            }
            if (! $coupon->hasStarted()) {
                return CouponValidationResult::invalid('This coupon is not yet active.');
            }
        }

        if (! $coupon->canBeUsedByTenant($tenantId)) {
            return CouponValidationResult::invalid('You have reached the usage limit for this coupon.');
        }

        if (! $coupon->isApplicableToPlan($planId)) {
            return CouponValidationResult::invalid('This coupon is not applicable to the selected plan.');
        }

        if (! $coupon->isApplicableToBillingPeriod($billingPeriod)) {
            return CouponValidationResult::invalid('This coupon is not applicable to the selected billing period.');
        }

        if ($coupon->first_payment_only && ! $isFirstPayment) {
            return CouponValidationResult::invalid('This coupon is only valid for first payments.');
        }

        if (! $coupon->meetsMinimumAmount($amount)) {
            $minAmount = '$'.number_format($coupon->minimum_amount, 2);

            return CouponValidationResult::invalid("Minimum amount of {$minAmount} required for this coupon.");
        }

        $discountAmount = $coupon->calculateDiscount($amount);

        return CouponValidationResult::valid($coupon, $discountAmount);
    }

    /**
     * Apply coupon to an invoice
     */
    public function applyCouponToInvoice(Invoice $invoice, string $couponCode): bool
    {
        $plan = $invoice->subscription?->plan;
        if (! $plan) {
            return false;
        }

        $subtotal = $invoice->subTotal();
        $billingPeriod = $plan->billing_period ?? 'monthly';
        $isFirstPayment = $this->isFirstPaymentForTenant($invoice->tenant_id);

        $result = $this->validateCoupon(
            $couponCode,
            $invoice->tenant_id,
            $subtotal,
            $plan->id,
            $billingPeriod,
            $isFirstPayment
        );

        if (! $result->isValid) {
            return false;
        }

        // Remove existing coupon if any
        $this->removeCouponFromInvoice($invoice);

        // Apply new coupon using the enhanced method
        $invoice->applyCoupon($result->coupon);

        // Record usage
        $this->recordUsage($result->coupon, $invoice->tenant_id, $invoice, $invoice->subscription);

        return true;
    }

    /**
     * Remove coupon from invoice
     */
    public function removeCouponFromInvoice(Invoice $invoice): bool
    {
        if (! $invoice->coupon_id) {
            return false;
        }

        // Get the coupon before removing it
        $coupon = Coupon::find($invoice->coupon_id);

        // Remove usage record
        CouponUsage::where('invoice_id', $invoice->id)->delete();

        // Decrement usage count if coupon exists
        if ($coupon) {
            $coupon->decrementUsage();
        }

        // Clear coupon from invoice using the enhanced method
        $invoice->removeCoupon();

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(Coupon $coupon, float $amount): float
    {
        return $coupon->calculateDiscount($amount);
    }

    /**
     * Record coupon usage
     */
    public function recordUsage(
        Coupon $coupon,
        int $tenantId,
        ?Invoice $invoice = null,
        ?Subscription $subscription = null
    ): CouponUsage {
        $discountAmount = 0;
        if ($invoice) {
            $discountAmount = $invoice->coupon_discount;
        }

        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'tenant_id' => $tenantId,
            'invoice_id' => $invoice?->id,
            'subscription_id' => $subscription?->id,
            'discount_amount' => $discountAmount,
            'metadata' => [
                'applied_at' => now()->toISOString(),
                'invoice_total' => $invoice?->total(),
                'plan_name' => $subscription?->plan?->name,
            ],
        ]);

        // Increment coupon usage count
        $coupon->incrementUsage();

        return $usage;
    }

    /**
     * Get tenant usage count for a coupon
     */
    public function getTenantUsageCount(int $couponId, int $tenantId): int
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->where('tenant_id', $tenantId)
            ->count();
    }

    /**
     * Get coupon statistics
     */
    public function getCouponStats(int $couponId): array
    {
        $coupon = Coupon::with('usages')->find($couponId);

        if (! $coupon) {
            return [];
        }

        $totalUsages = $coupon->usages->count();
        $totalDiscount = $coupon->usages->sum('discount_amount');
        $uniqueTenants = $coupon->usages->unique('tenant_id')->count();
        $averageDiscount = $totalUsages > 0 ? $totalDiscount / $totalUsages : 0;

        return [
            'total_usages' => $totalUsages,
            'total_discount_given' => $totalDiscount,
            'unique_customers' => $uniqueTenants,
            'average_discount' => $averageDiscount,
            'remaining_usages' => $coupon->getRemainingUsage(),
            'conversion_rate' => 0, // Calculate based on your needs
        ];
    }

    /**
     * Generate unique coupon code
     */
    public function generateUniqueCode(string $prefix = ''): string
    {
        do {
            $code = $prefix.strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if this is first payment for tenant
     */
    private function isFirstPaymentForTenant(int $tenantId): bool
    {
        return ! Invoice::where('tenant_id', $tenantId)
            ->where('status', Invoice::STATUS_PAID)
            ->exists();
    }
}
