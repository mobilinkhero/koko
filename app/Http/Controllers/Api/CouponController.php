<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCouponRequest;
use App\Models\Invoice\Invoice;
use App\Models\Tenant;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Validate a coupon code for the current tenant
     */
    public function validate(ValidateCouponRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Convert code to uppercase
        $code = strtoupper($validated['code']);

        // Get invoice details if an invoice_id is provided
        $invoice = null;
        if (isset($validated['invoice_id'])) {
            $invoice = Invoice::with(['subscription.plan'])->find($validated['invoice_id']);
        }

        $result = $this->couponService->validateCoupon(
            $code,
            tenant_id(),
            $invoice ? $invoice->subTotal() : ($validated['amount'] ?? 0),
            $invoice ? $invoice->subscription?->plan_id : ($validated['plan_id'] ?? null),
            $invoice ? $invoice->subscription?->plan?->billing_period : ($validated['billing_period'] ?? 'monthly'),
            $validated['is_first_payment'] ?? $this->isFirstPaymentForTenant(tenant_id())
        );

        return response()->json([
            'success' => $result->isValid,
            'discount_amount' => $result->discountAmount,
            'formatted_discount' => $result->discountAmount > 0 ? $this->formatDiscountAmount($result->discountAmount) : null,
            'message' => $result->isValid ? 'Coupon is valid' : $result->errorMessage,
            'coupon' => $result->isValid ? [
                'code' => $result->coupon->code,
                'name' => $result->coupon->name,
                'type' => $result->coupon->type,
                'formatted_discount' => $result->coupon->getFormattedDiscount(),
            ] : null,
        ]);
    }

    /**
     * Apply a coupon to an invoice
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        $success = $this->couponService->applyCouponToInvoice($invoice, $request->code);

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon to invoice',
            ], 400);
        }

        // Get the updated invoice with the new totals
        $invoice = $invoice->fresh();

        $discountAmount = $invoice->getCouponDiscount();

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'discount_amount' => $discountAmount,
            'formatted_discount' => $invoice->formatAmount($discountAmount),
            'total' => $invoice->total(),
            'formatted_total' => $invoice->formattedTotal(),
            'coupon_code' => $invoice->coupon_code,
        ]);
    }

    /**
     * Remove a coupon from an invoice
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        if (! $invoice->hasCoupon()) {
            return response()->json([
                'success' => false,
                'message' => 'No coupon applied to this invoice',
            ], 400);
        }

        $this->couponService->removeCouponFromInvoice($invoice);
        $invoice = $invoice->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully',
            'total' => $invoice->total(),
            'formatted_total' => $invoice->formattedTotal(),
        ]);
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

    /**
     * Format a discount amount for API response
     */
    private function formatDiscountAmount(float $amount): string
    {
        // Use the default currency since we might not have an Invoice instance
        // This is just for API response, the actual display will use Invoice::formatAmount
        $currency = \App\Models\Currency::getDefault();

        if ($currency) {
            return $currency->format($amount);
        }

        // Fallback to basic formatting if no default currency is set
        return '$'.number_format($amount, 2);
    }
}
