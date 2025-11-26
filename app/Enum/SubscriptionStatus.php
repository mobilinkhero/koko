<?php

namespace App\Enum;

enum SubscriptionStatus: string
{
    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case DEACTIVE = 'Deactive';
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';
    case TRIAL = 'trial';
    case PAST_DUE = 'past_due';
    case PAUSED = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
            self::DEACTIVE => 'Deactive',
            self::PENDING => 'Pending',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
            self::CANCELED => 'Canceled',
            self::TRIAL => 'Trial',
            self::PAST_DUE => 'Past Due',
            self::PAUSED => 'Paused',
        };
    }

    /**
     * Get user-friendly message for subscription status.
     */
    public function message(): string
    {
        return match ($this) {
            self::INACTIVE => 'Your subscription is currently inactive.',
            self::DEACTIVE => 'Your subscription is currently deactive.',
            self::ACTIVE => 'Your subscription is active.',
            self::PENDING => 'Your subscription is pending approval.',
            self::REJECTED => 'Your subscription request was rejected.',
            self::EXPIRED => 'Your subscription has expired.',
            self::CANCELED => 'Your subscription has been canceled.',
            self::TRIAL => 'You are currently on a trial subscription.',
            self::PAST_DUE => 'Your payment is past due. Please update your payment information.',
            self::PAUSED => 'Your subscription is paused.',
        };
    }

    /**
     * Check if allowed transitions for a status.
     */
    public function canTransitionTo(self $newStatus): bool
    {
        // Define allowed transitions for each status using the string value
        $allowedTransitions = [
            // From INACTIVE, can go to PENDING, TRIAL or ACTIVE
            'inactive' => ['pending', 'trial', 'active'],

            'deactive' => ['pending', 'trial', 'active'],

            // From PENDING, can go to ACTIVE, REJECTED or INACTIVE
            'pending' => ['active', 'rejected', 'inactive'],

            // From ACTIVE, can go to PAST_DUE, EXPIRED, CANCELED or INACTIVE
            'active' => ['past_due', 'expired', 'canceled', 'inactive'],

            // From REJECTED, can only go to PENDING or INACTIVE
            'rejected' => ['pending', 'inactive'],

            // From EXPIRED, can go to PENDING, ACTIVE or INACTIVE
            'expired' => ['pending', 'active', 'inactive'],

            // From CANCELED, can go to PENDING or INACTIVE
            'canceled' => ['pending', 'inactive'],

            // From TRIAL, can go to ACTIVE, EXPIRED or INACTIVE
            'trial' => ['active', 'expired', 'inactive'],

            // From PAST_DUE, can go to ACTIVE, EXPIRED, CANCELED or INACTIVE
            'past_due' => ['active', 'expired', 'canceled', 'inactive'],

            // From PAUSED, can go to ACTIVE or INACTIVE
            'paused' => ['active', 'inactive'],
        ];

        return in_array($newStatus->value, $allowedTransitions[$this->value]);
    }
}
