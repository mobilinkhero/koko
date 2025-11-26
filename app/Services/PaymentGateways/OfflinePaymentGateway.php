<?php

namespace App\Services\PaymentGateways;

use App\Models\Invoice\Invoice;
use App\Models\Transaction;
use App\Services\Billing\TransactionResult;
use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use Exception;

/**
 * Offline Payment Gateway Service
 *
 * Handles manual payment processing for the WhatsApp SaaS multi-tenant application.
 * This gateway enables tenants to accept payments through bank transfers, checks,
 * cash payments, or other offline payment methods.
 *
 * Key Features:
 * - Manual payment instruction management
 * - Administrative approval workflow
 * - Bank transfer support
 * - Transaction verification
 * - Multi-tenant configuration
 * - Admin settings integration
 *
 * The offline payment gateway is designed for businesses that need to accept
 * payments through traditional methods or when online payment gateways are
 * not available or preferred.
 *
 * Usage Example:
 * ```php
 * $offlineGateway = new OfflinePaymentGateway('Bank transfer to Account: 123456789');
 * if ($offlineGateway->isActive()) {
 *     $checkoutUrl = $offlineGateway->getCheckoutUrl($invoice);
 *     // Redirect customer to checkout page
 * }
 * ```
 *
 * @author WhatsApp SaaS Team
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 * @see PaymentGatewayInterface For payment gateway contract
 * @see StripePaymentGateway For online payment alternative
 * @see BillingManager For gateway registration
 */
class OfflinePaymentGateway implements PaymentGatewayInterface
{
    /**
     * The payment instruction text displayed to customers.
     *
     * Contains detailed instructions for making offline payments,
     * including bank account details, reference requirements,
     * and any special conditions.
     *
     * @var string Payment instruction text
     */
    protected $paymentInstruction;

    /**
     * Indicates whether the payment gateway is currently active.
     *
     * Determined by both payment instruction availability
     * and admin configuration settings.
     *
     * @var bool Gateway active status
     */
    protected $active = false;

    /**
     * The unique payment gateway type identifier.
     *
     * Used for gateway registration and routing purposes.
     *
     * @var string Gateway type constant
     */
    public const TYPE = 'offline';

    /**
     * Create a new offline payment gateway instance.
     *
     * Initializes the gateway with payment instructions and validates
     * the configuration to determine if the gateway should be active.
     *
     * @param  string  $paymentInstruction  The payment instruction text for customers
     *
     * @example
     * ```php
     * $gateway = new OfflinePaymentGateway(
     *     'Bank Transfer: Account 123456789, Bank XYZ, Reference: Invoice #{invoice_id}'
     * );
     * ```
     */
    public function __construct(string $paymentInstruction)
    {
        $this->paymentInstruction = $paymentInstruction;

        $this->validate();
    }

    /**
     * Validate the payment gateway configuration.
     *
     * Checks both the payment instruction availability and admin settings
     * to determine if the gateway should be active. The gateway is only
     * active when both conditions are met:
     * 1. Payment instruction is provided
     * 2. Admin has enabled offline payments
     *
     *
     * @throws \InvalidArgumentException If settings service is not available
     *
     * @example
     * ```php
     * $gateway->validate();
     * if ($gateway->isActive()) {
     *     // Gateway is ready for use
     * }
     * ```
     *
     * @see \App\Settings\PaymentSettings For admin configuration
     */
    public function validate(): void
    {
        // Check if payment instruction is available
        $hasPaymentInstruction = ! empty($this->getPaymentInstruction());

        // Check if the gateway is enabled by admin
        $settings = app(\App\Settings\PaymentSettings::class);
        $isEnabled = $settings->offline_enabled ?? false;

        // Only active if both conditions are met
        $this->active = $hasPaymentInstruction && $isEnabled;
    }

    /**
     * Get the payment gateway name.
     *
     * Returns the human-readable name of the payment gateway
     * that is displayed in the admin interface and customer forms.
     *
     * @return string The gateway display name
     *
     * @example
     * ```php
     * echo $gateway->getName(); // "Offline Payment"
     * ```
     */
    public function getName(): string
    {
        return 'Offline Payment';
    }

    /**
     * Get the payment gateway type identifier.
     *
     * Returns the unique type identifier used for gateway
     * registration, routing, and configuration purposes.
     *
     * @return string The gateway type constant
     *
     * @example
     * ```php
     * echo $gateway->getType(); // "offline"
     * ```
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * Get the payment gateway description.
     *
     * Returns a detailed description of the payment gateway
     * explaining its purpose and payment methods supported.
     *
     * @return string The gateway description
     *
     * @example
     * ```php
     * echo $gateway->getDescription();
     * // "Pay via bank transfer or other offline methods."
     * ```
     */
    public function getDescription(): string
    {
        return 'Pay via bank transfer or other offline methods.';
    }

    /**
     * Get the payment gateway short description.
     *
     * Returns a concise description suitable for buttons,
     * dropdown menus, and compact UI elements.
     *
     * @return string The gateway short description
     *
     * @example
     * ```php
     * echo $gateway->getShortDescription();
     * // "Bank transfer / Manual payment"
     * ```
     */
    public function getShortDescription(): string
    {
        return 'Bank transfer / Manual payment';
    }

    /**
     * Determine if the payment gateway is active.
     *
     * Checks if the gateway is properly configured and enabled
     * for processing payments. Returns the cached active status
     * determined during validation.
     *
     * @return bool True if gateway is active, false otherwise
     *
     * @example
     * ```php
     * if ($gateway->isActive()) {
     *     $url = $gateway->getCheckoutUrl($invoice);
     *     // Process payment
     * }
     * ```
     *
     * @see validate() For active status determination logic
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Get the payment gateway settings URL.
     *
     * Returns the admin panel URL where administrators can
     * configure offline payment settings, including payment
     * instructions and enable/disable status.
     *
     * @return string The admin settings URL
     *
     * @example
     * ```php
     * $settingsUrl = $gateway->getSettingsUrl();
     * // Returns: route('admin.settings.payment.offline')
     * ```
     *
     * @see \App\Http\Controllers\Admin\PaymentSettingsController
     */
    public function getSettingsUrl(): string
    {
        return route('admin.settings.payment.offline');
    }

    /**
     * Get the checkout URL for the invoice.
     *
     * Generates the tenant-specific checkout URL where customers
     * can view payment instructions and complete offline payments.
     * The URL includes the invoice ID for payment tracking.
     *
     * @param  Invoice  $invoice  The invoice to generate checkout URL for
     * @return string The tenant checkout URL
     *
     * @example
     * ```php
     * $checkoutUrl = $gateway->getCheckoutUrl($invoice);
     * // Returns: https://tenant.domain.com/payment/offline/checkout/123
     * ```
     *
     * @see \App\Http\Controllers\Tenant\OfflinePaymentController
     */
    public function getCheckoutUrl(Invoice $invoice): string
    {
        return tenant_route('tenant.payment.offline.checkout', ['invoice' => $invoice->id]);
    }

    /**
     * Determine if the payment gateway supports auto billing.
     *
     * Offline payments inherently require manual intervention
     * and cannot be automatically charged, so this always returns false.
     *
     * @return bool Always returns false for offline payments
     *
     * @example
     * ```php
     * if ($gateway->supportsAutoBilling()) {
     *     // This will never execute for offline gateway
     * }
     * ```
     */
    public function supportsAutoBilling(): bool
    {
        return false;
    }

    /**
     * Auto charge the invoice.
     *
     * Offline payments cannot be automatically charged, so this method
     * always throws an exception. Use manual verification instead.
     *
     * @param  Invoice  $invoice  The invoice to charge
     * @return mixed Never returns, always throws exception
     *
     * @throws \Exception Always throws exception for offline payments
     *
     * @example
     * ```php
     * try {
     *     $gateway->autoCharge($invoice);
     * } catch (Exception $e) {
     *     // Handle unsupported operation
     * }
     * ```
     *
     * @see verify() For manual payment verification
     */
    public function autoCharge(Invoice $invoice)
    {
        throw new Exception('Offline payment gateway does not support auto charge!');
    }

    /**
     * Get the URL for updating auto billing data.
     *
     * Since offline payments don't support auto billing, this method
     * always throws an exception. Auto billing requires online payment methods.
     *
     * @param  string  $returnUrl  The URL to return to after update (unused)
     * @return string Never returns, always throws exception
     *
     * @throws \Exception Always throws exception for offline payments
     *
     * @example
     * ```php
     * try {
     *     $url = $gateway->getAutoBillingDataUpdateUrl('/dashboard');
     * } catch (Exception $e) {
     *     // Handle unsupported operation
     * }
     * ```
     */
    public function getAutoBillingDataUpdateUrl(string $returnUrl = '/'): string
    {
        throw new Exception('Offline payment gateway does not support auto charge.');
    }

    /**
     * Verify the transaction.
     *
     * For offline payments, verification is a manual process that requires
     * administrative review. All transactions remain in pending status
     * until an administrator verifies the payment and approves it.
     *
     * @param  Transaction  $transaction  The transaction to verify
     * @return TransactionResult Always returns pending status
     *
     * @example
     * ```php
     * $result = $gateway->verify($transaction);
     * if ($result->isPending()) {
     *     // Transaction awaits admin approval
     * }
     * ```
     *
     * @see TransactionResult::RESULT_PENDING For status constants
     * @see allowManualReviewingOfTransaction() For admin review capability
     */
    public function verify(Transaction $transaction): TransactionResult
    {
        // Offline payments always remain pending until admin approves
        return new TransactionResult(TransactionResult::RESULT_PENDING);
    }

    /**
     * Determine if the payment gateway allows manual reviewing of transactions.
     *
     * Offline payments require manual review by administrators to verify
     * that payment has been received through the specified offline method.
     * This enables the admin transaction review interface.
     *
     * @return bool Always returns true for offline payments
     *
     * @example
     * ```php
     * if ($gateway->allowManualReviewingOfTransaction()) {
     *     // Enable admin review interface
     * }
     * ```
     *
     * @see \App\Http\Controllers\Admin\TransactionController For admin review
     */
    public function allowManualReviewingOfTransaction(): bool
    {
        return true;
    }

    /**
     * Get the minimum charge amount for the given currency.
     *
     * Offline payments don't have technical minimum amounts since they
     * rely on manual processing. Returns 0 to allow any amount.
     * Business rules for minimum amounts should be handled elsewhere.
     *
     * @param  string  $currency  The currency code (e.g., 'USD', 'EUR')
     * @return float Always returns 0 for offline payments
     *
     * @example
     * ```php
     * $minimum = $gateway->getMinimumChargeAmount('USD');
     * // Returns: 0
     * ```
     */
    public function getMinimumChargeAmount($currency)
    {
        return 0;
    }

    /**
     * Get the payment instruction text.
     *
     * Returns the payment instructions that customers see during checkout.
     * If no custom instruction is provided, returns a default instruction
     * with basic bank transfer guidance.
     *
     * @return string The payment instruction text
     *
     * @example
     * ```php
     * $instructions = $gateway->getPaymentInstruction();
     * echo $instructions;
     * // Custom or default payment instructions
     * ```
     *
     * @see __construct() For setting custom instructions
     */
    public function getPaymentInstruction(): string
    {
        if ($this->paymentInstruction) {
            return $this->paymentInstruction;
        }

        return 'Please make payment to our bank account and provide your invoice number in the payment details.';
    }
}
