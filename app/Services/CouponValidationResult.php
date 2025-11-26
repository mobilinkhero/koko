<?php

namespace App\Services;

use App\Models\Coupon;

class CouponValidationResult
{
    public function __construct(
        public bool $isValid,
        public ?Coupon $coupon = null,
        public string $errorMessage = '',
        public float $discountAmount = 0
    ) {}

    public static function valid(Coupon $coupon, float $discountAmount): self
    {
        return new self(
            isValid: true,
            coupon: $coupon,
            discountAmount: $discountAmount
        );
    }

    public static function invalid(string $message): self
    {
        return new self(
            isValid: false,
            errorMessage: $message
        );
    }

    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'coupon' => $this->coupon,
            'error_message' => $this->errorMessage,
            'discount_amount' => $this->discountAmount,
        ];
    }
}
