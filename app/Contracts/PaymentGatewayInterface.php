<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\Tenant;

interface PaymentGatewayInterface
{
    /**
     * Check if the payment gateway is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Get the payment gateway name.
     */
    public function getName(): string;

    /**
     * Get the payment gateway identifier.
     */
    public function getIdentifier(): string;

    /**
     * Get the payment gateway description.
     */
    public function getDescription(): string;

    /**
     * Create a checkout session.
     */
    public function createCheckout(Tenant $tenant, Plan $plan, string $billingCycle, string $successUrl, string $cancelUrl): array;

    /**
     * Verify a checkout session completion.
     */
    public function verifyCheckout(string $sessionId): array;

    /**
     * Process a webhook event.
     */
    public function processWebhook(array $payload): bool;

    /**
     * Get payment method details for display.
     */
    public function getPaymentMethodDetails(): array;
}
