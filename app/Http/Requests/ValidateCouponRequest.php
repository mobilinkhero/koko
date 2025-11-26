<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Tenants can validate coupons
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50',
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'nullable|numeric|min:0',
            'plan_id' => 'nullable|exists:plans,id',
            'billing_period' => 'nullable|in:monthly,yearly',
            'is_first_payment' => 'boolean',
        ];
    }

    /**
     * No need for prepareForValidation - we'll handle the uppercase conversion in the controller
     */
}
