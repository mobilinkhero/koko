<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreCouponRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Handle authorization in middleware or gates
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('coupons', 'code'),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'percentage' && $value > 100) {
                        $fail('Percentage discount cannot exceed 100%.');
                    }
                },
            ],
            'usage_limit' => 'nullable|integer|min:1|max:1000000',
            'usage_limit_per_customer' => 'nullable|integer|min:1|max:100',
            'starts_at' => [
                'nullable',
                'date',
                'after_or_equal:now'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:starts_at'
            ],
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'fixed_amount' && $value && $value < $this->value) {
                        $fail('Maximum discount cannot be less than the coupon value for fixed amount coupons.');
                    }
                },
            ],
            'applicable_plans' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    // Validate that plans exist in database
                    if (is_array($value) && !empty($value)) {
                        $existingPlans = DB::table('plans')
                            ->whereIn('id', $value)
                            ->count();
                        
                        if ($existingPlans !== count($value)) {
                            $fail('One or more specified plans do not exist.');
                        }
                    }
                }
            ],
            'applicable_billing_periods' => [
                'nullable',
                'array',
                'max:2' // Only monthly and yearly
            ],
            'applicable_billing_periods.*' => 'in:monthly,yearly',
            'first_payment_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'code.alpha_dash' => 'Coupon code may only contain letters, numbers, dashes and underscores.',
            'code.unique' => 'This coupon code is already taken.',
            'expires_at.after' => 'Expiry date must be after the start date.',
            'starts_at.after_or_equal' => 'Start date cannot be in the past.',
            'applicable_plans.array' => 'Applicable plans must be a valid array.',
            'applicable_plans.*.exists' => 'One or more specified plans do not exist.',
        ];
    }

    protected function prepareForValidation()
    {
        // Convert code to uppercase and trim whitespace
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }

        // Ensure dates are in proper format
        $this->merge([
            'starts_at' => $this->starts_at ? date('Y-m-d H:i:s', strtotime($this->starts_at)) : null,
            'expires_at' => $this->expires_at ? date('Y-m-d H:i:s', strtotime($this->expires_at)) : null,
        ]);
    }

    /**
     * Handle after validation to ensure logical consistency
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure at least one of the conditions is specified if applicable_plans is provided
            if ($this->has('applicable_plans') && $this->applicable_plans !== null && empty($this->applicable_plans)) {
                $validator->errors()->add('applicable_plans', 'At least one plan must be selected when specifying applicable plans.');
            }

            // Validate coupon logical rules
            if ($this->type === 'percentage' && $this->value > 100) {
                $validator->errors()->add('value', 'Percentage discount cannot exceed 100%.');
            }

            // Ensure expiry date is in the future if specified
            if ($this->expires_at && strtotime($this->expires_at) <= time()) {
                $validator->errors()->add('expires_at', 'Expiry date must be in the future.');
            }
        });
    }
}