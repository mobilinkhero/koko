<?php

namespace App\Services\PaymentGateways\Contracts;

use App\Models\Invoice\Invoice;
use App\Models\Transaction;
use App\Services\Billing\TransactionResult;

/**
 * Payment Gateway Interface Contract
 *
 * Defines the standardized contract that all payment gateway implementations
 * must follow in the WhatsApp SaaS multi-tenant billing system. Ensures
 * consistent behavior across different payment processing services.
 *
 * Key Responsibilities:
 * - Gateway identification and configuration
 * - Checkout URL generation
 * - Auto-billing capability management
 * - Transaction verification and processing
 * - Manual review support
 * - Currency-specific minimum amounts
 *
 * Implementation Requirements:
 * - All methods must be implemented by concrete gateway classes
 * - Gateway must handle tenant-specific configuration
 * - Error handling should be consistent across implementations
 * - Security considerations for payment processing
 *
 * Gateway Types Supported:
 * - Online payment gateways (Stripe, PayPal, etc.)
 * - Offline payment methods (bank transfer, checks)
 * - Cryptocurrency payments (future)
 * - Custom payment processors
 *
 * Usage Example:
 * ```php
 * class CustomPaymentGateway implements PaymentGatewayInterface
 * {
 *     public function getName(): string
 *     {
 *         return 'Custom Payment Processor';
 *     }
 *
 *     public function verify(Transaction $transaction): TransactionResult
 *     {
 *         // Implement transaction verification logic
 *         return new TransactionResult(TransactionResult::RESULT_DONE);
 *     }
 *
 *     // ... implement all other required methods
 * }
 * ```
 *
 * @author WhatsApp SaaS Team
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 * @see StripePaymentGateway For online payment implementation
 * @see OfflinePaymentGateway For manual payment implementation
 * @see BillingManager For gateway registration and management
 * @see TransactionResult For transaction status representation
 */
interface PaymentGatewayInterface
{
    /**
     * Get the human-readable payment gateway name.
     *
     * Returns the display name shown to administrators and customers
     * in the user interface. Should be descriptive and brand-appropriate.
     *
     * @return string The gateway display name
     *
     * @example
     * ```php
     * echo $gateway->getName(); // "Stripe Credit Card Processing"
     * ```
     */
    public function getName(): string;

    /**
     * Get the unique payment gateway type identifier.
     *
     * Returns a unique string identifier used for gateway registration,
     * routing, database storage, and configuration purposes. Should be
     * lowercase and URL-safe.
     *
     * @return string The gateway type identifier
     *
     * @example
     * ```php
     * echo $gateway->getType(); // "stripe"
     * ```
     */
    public function getType(): string;

    /**
     * Get the detailed payment gateway description.
     *
     * Returns a comprehensive description of the payment gateway,
     * its capabilities, and supported payment methods. Used in
     * admin interfaces and help documentation.
     *
     * @return string The gateway description
     *
     * @example
     * ```php
     * echo $gateway->getDescription();
     * // "Accept credit card payments securely through Stripe"
     * ```
     */
    public function getDescription(): string;

    /**
     * Get the concise payment gateway description.
     *
     * Returns a brief description suitable for buttons, dropdown
     * menus, and compact UI elements where space is limited.
     *
     * @return string The short gateway description
     *
     * @example
     * ```php
     * echo $gateway->getShortDescription(); // "Credit Card"
     * ```
     */
    public function getShortDescription(): string;

    /**
     * Determine if the payment gateway is currently active.
     *
     * Checks if the gateway is properly configured, enabled by
     * administrators, and ready to process payments. Should verify
     * all required configuration parameters.
     *
     * @return bool True if gateway is active and ready, false otherwise
     *
     * @example
     * ```php
     * if ($gateway->isActive()) {
     *     // Gateway is ready for payments
     *     $checkoutUrl = $gateway->getCheckoutUrl($invoice);
     * }
     * ```
     */
    public function isActive(): bool;

    /**
     * Get the administrative settings URL.
     *
     * Returns the URL where administrators can configure the payment
     * gateway settings, credentials, and preferences. Used in admin
     * navigation and gateway management interfaces.
     *
     * @return string The admin settings URL
     *
     * @example
     * ```php
     * $settingsUrl = $gateway->getSettingsUrl();
     * // Returns: "https://admin.example.com/settings/payment/stripe"
     * ```
     */
    public function getSettingsUrl(): string;

    /**
     * Generate the checkout URL for an invoice.
     *
     * Creates a tenant-specific URL where customers can complete
     * payment for the given invoice. Should include necessary
     * parameters for payment tracking and security.
     *
     * @param  Invoice  $invoice  The invoice to generate checkout URL for
     * @return string The customer checkout URL
     *
     * @example
     * ```php
     * $checkoutUrl = $gateway->getCheckoutUrl($invoice);
     * // Returns: "https://tenant.example.com/payment/stripe/checkout/123"
     *
     * return redirect($checkoutUrl);
     * ```
     */
    public function getCheckoutUrl(Invoice $invoice): string;

    /**
     * Determine if the gateway supports automatic billing.
     *
     * Indicates whether the payment gateway can automatically charge
     * customers for recurring subscriptions without manual intervention.
     * Critical for subscription-based business models.
     *
     * @return bool True if auto-billing is supported, false otherwise
     *
     * @example
     * ```php
     * if ($gateway->supportsAutoBilling()) {
     *     // Enable subscription auto-renewal
     *     $subscription->enable_auto_renewal = true;
     * }
     * ```
     */
    public function supportsAutoBilling(): bool;

    /**
     * Automatically charge an invoice.
     *
     * Processes automatic payment for the given invoice using stored
     * payment methods or recurring billing setup. Only available for
     * gateways that support auto-billing.
     *
     * @param  Invoice  $invoice  The invoice to automatically charge
     * @return mixed The charge result or response from payment processor
     *
     * @throws \Exception If auto-billing is not supported or fails
     *
     * @example
     * ```php
     * try {
     *     $result = $gateway->autoCharge($invoice);
     *     // Process successful auto-charge
     * } catch (Exception $e) {
     *     // Handle auto-charge failure
     *     Log::error('Auto-charge failed: ' . $e->getMessage());
     * }
     * ```
     */
    public function autoCharge(Invoice $invoice);

    /**
     * Get the URL for updating auto-billing payment data.
     *
     * Returns a URL where customers can update their stored payment
     * methods, billing addresses, or other auto-billing configuration.
     * Only relevant for gateways supporting auto-billing.
     *
     * @param  string  $returnUrl  URL to redirect to after updating payment data
     * @return string The payment data update URL
     *
     * @throws \Exception If auto-billing is not supported
     *
     * @example
     * ```php
     * $updateUrl = $gateway->getAutoBillingDataUpdateUrl(
     *     route('tenant.billing.dashboard')
     * );
     *
     * return redirect($updateUrl);
     * ```
     */
    public function getAutoBillingDataUpdateUrl(string $returnUrl = '/'): string;

    /**
     * Verify a transaction status.
     *
     * Checks the current status of a payment transaction with the
     * payment processor and returns a standardized result object.
     * Used for confirming payment completion and updating records.
     *
     * @param  Transaction  $transaction  The transaction to verify
     * @return TransactionResult The verification result with status and message
     *
     * @example
     * ```php
     * $result = $gateway->verify($transaction);
     *
     * if ($result->isDone()) {
     *     $invoice->markAsPaid();
     * } elseif ($result->isFailed()) {
     *     $transaction->markAsFailed($result->getMessage());
     * }
     * ```
     */
    public function verify(Transaction $transaction): TransactionResult;

    /**
     * Determine if manual transaction review is allowed.
     *
     * Indicates whether administrators can manually review and approve
     * transactions for this gateway. Typically true for offline payment
     * methods that require manual verification.
     *
     * @return bool True if manual review is supported, false otherwise
     *
     * @example
     * ```php
     * if ($gateway->allowManualReviewingOfTransaction()) {
     *     // Show admin review interface
     *     $adminReviewUrl = route('admin.transactions.review', $transaction);
     * }
     * ```
     */
    public function allowManualReviewingOfTransaction(): bool;

    /**
     * Get the minimum charge amount for a currency.
     *
     * Returns the minimum transaction amount supported by the payment
     * gateway for the specified currency. Used for validation and
     * preventing transactions below processor limits.
     *
     * @param  string  $currency  The currency code (e.g., 'USD', 'EUR')
     * @return float The minimum charge amount in the specified currency
     *
     * @example
     * ```php
     * $minimum = $gateway->getMinimumChargeAmount('USD');
     * if ($invoice->amount < $minimum) {
     *     throw new ValidationException("Minimum amount is $minimum USD");
     * }
     * ```
     */
    public function getMinimumChargeAmount($currency);
}
