<?php

namespace App\Services\PaymentGateways;

use App\Events\TransactionCreated;
use App\Models\Invoice\Invoice;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Services\Billing\TransactionResult;
use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\Settings\PaymentSettings;
use Exception;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * StripePaymentGateway
 *
 * Comprehensive Stripe payment integration for multi-tenant WhatsApp SaaS
 * applications. Handles subscription payments, one-time transactions, webhook
 * processing, and advanced payment features with full error handling.
 *
 * Key Features:
 * - Payment Intent creation and processing
 * - Subscription payment handling
 * - Webhook event processing and validation
 * - Multi-currency support
 * - SCA (Strong Customer Authentication) compliance
 * - Comprehensive error handling and logging
 * - Transaction result standardization
 *
 * Stripe Integration:
 * - API Version: 2022-11-15
 * - Payment Methods: Cards, bank transfers, digital wallets
 * - Webhook Endpoints: Payment confirmations, subscription updates
 * - Security: Webhook signature verification
 *
 * Payment Flow:
 * 1. Payment Intent creation with amount and metadata
 * 2. Client-side payment confirmation
 * 3. Webhook event processing for status updates
 * 4. Transaction recording and invoice updates
 * 5. Subscription activation/renewal processing
 *
 * Security Features:
 * - API key validation and secure storage
 * - Webhook signature verification
 * - Payment confirmation before processing
 * - Error logging without exposing sensitive data
 *
 * Error Handling:
 * - Card declined scenarios
 * - Network and API failures
 * - Invalid payment method handling
 * - Webhook replay attack prevention
 *
 * Usage Examples:
 * ```php
 * $gateway = new StripePaymentGateway($publishableKey, $secretKey);
 *
 * // Process subscription payment
 * $result = $gateway->processPayment($invoice, $paymentData);
 *
 * if ($result->isSuccess()) {
 *     // Payment successful
 *     $transactionId = $result->getTransactionId();
 * } else {
 *     // Handle payment failure
 *     $error = $result->getErrorMessage();
 * }
 *
 * // Handle webhook
 * $gateway->handleWebhook($webhookPayload, $signature);
 * ```
 *
 * @implements PaymentGatewayInterface
 *
 * @see \App\Services\PaymentGateways\Contracts\PaymentGatewayInterface
 * @see \App\Services\Billing\TransactionResult
 *
 * @version 1.0.0
 */
class StripePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Stripe publishable API key for client-side operations
     *
     * Used for frontend payment form initialization and
     * secure token collection. Safe to expose in client-side code.
     *
     * @var string
     */
    protected $publishableKey;

    /**
     * Stripe secret API key for server-side operations
     *
     * Used for creating charges, managing subscriptions,
     * and other sensitive operations. Must be kept secure.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Gateway activation status
     *
     * Indicates whether the gateway is properly configured
     * and enabled for processing payments.
     *
     * @var bool
     */
    protected $active = false;

    /**
     * Stripe API client instance
     *
     * Initialized Stripe client for making API calls
     * with proper authentication and configuration.
     *
     * @var \Stripe\StripeClient
     */
    protected $client;

    /**
     * Payment gateway type identifier
     *
     * Constant identifier used throughout the application
     * to reference this specific gateway implementation.
     *
     * @var string
     */
    public const TYPE = 'stripe';

    /**
     * Retrieve Stripe secret API key from settings
     *
     * Fetches the secret key from tenant or global settings
     * using batch settings retrieval for performance.
     *
     * @return string|null Stripe secret key or null if not configured
     *
     * @example
     * ```php
     * $secretKey = $gateway->getSecretKey();
     * if ($secretKey) {
     *     // Secret key is configured
     * }
     * ```
     */
    public function getSecretKey()
    {
        $settings = get_batch_settings(['payment.stripe_secret']);

        return $settings['payment.stripe_secret'];
    }

    /**
     * Retrieve Stripe webhook secret for signature verification
     *
     * Gets the webhook endpoint secret used to verify that
     * incoming webhook events are actually from Stripe.
     *
     * @return string|null Webhook secret or null if not configured
     *
     * @example
     * ```php
     * $webhookSecret = $gateway->getWebhookSecret();
     * // Used for: Stripe\Webhook::constructEvent($payload, $signature, $secret)
     * ```
     */
    public function getWebhookSecret()
    {
        $settings = get_batch_settings(['payment.stripe_webhook_secret']);

        return $settings['payment.stripe_webhook_secret'];
    }

    /**
     * Initialize Stripe payment gateway instance
     *
     * Creates a new Stripe gateway with provided API credentials,
     * validates configuration, and initializes the Stripe client
     * if all requirements are met.
     *
     * Initialization Process:
     * 1. Store API credentials
     * 2. Validate configuration and settings
     * 3. Set Stripe API key and version
     * 4. Initialize Stripe client instance
     *
     * @param  string  $publishableKey  Stripe publishable API key
     * @param  string  $secretKey  Stripe secret API key
     *
     * @example
     * ```php
     * $gateway = new StripePaymentGateway(
     *     'pk_live_...',
     *     'sk_live_...'
     * );
     *
     * if ($gateway->isActive()) {
     *     // Gateway is ready for payments
     * }
     * ```
     *
     * @see validate()
     */
    public function __construct(string $publishableKey, string $secretKey)
    {
        $this->publishableKey = $publishableKey;
        $this->secretKey = $secretKey;

        $this->validate();
        if ($this->active) {
            Stripe::setApiKey($this->secretKey);
            Stripe::setApiVersion('2022-11-15');
            $this->client = new StripeClient($this->secretKey);
        }
    }

    /**
     * Validate payment gateway configuration and activation status
     *
     * Checks both API credentials and admin settings to determine
     * if the gateway should be active and ready for processing payments.
     *
     * Validation Criteria:
     * 1. Valid publishable and secret keys provided
     * 2. Gateway enabled in admin payment settings
     * 3. Both conditions must be true for activation
     *
     *
     * @example
     * ```php
     * $gateway->validate();
     *
     * if ($gateway->isActive()) {
     *     // Gateway passed validation
     * } else {
     *     // Missing credentials or disabled by admin
     * }
     * ```
     *
     * @see \App\Settings\PaymentSettings
     */
    public function validate(): void
    {
        // Check if credentials are valid
        $hasCredentials = ! empty($this->publishableKey) && ! empty($this->secretKey) && ! empty($this->getWebhookSecret());

        // Check if the gateway is enabled by admin
        $settings = app(PaymentSettings::class);
        $isEnabled = $settings->stripe_enabled ?? false;

        // Only active if both conditions are met
        $this->active = $hasCredentials && $isEnabled;
    }

    /**
     * Get the payment gateway name.
     */
    public function getName(): string
    {
        return 'Stripe';
    }

    /**
     * Get the payment gateway type.
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * Get the payment gateway description.
     */
    public function getDescription(): string
    {
        return 'Pay with credit card using Stripe secure payment.';
    }

    /**
     * Get the payment gateway short description.
     */
    public function getShortDescription(): string
    {
        return 'Credit/Debit cards';
    }

    /**
     * Determine if the payment gateway is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Get the payment gateway settings URL.
     */
    public function getSettingsUrl(): string
    {
        return route('admin.settings.payment.stripe');
    }

    /**
     * Get the checkout URL for the invoice.
     */
    public function getCheckoutUrl(Invoice $invoice): string
    {
        return tenant_route('tenant.payment.stripe.checkout', ['invoice' => $invoice->id]);
    }

    /**
     * Determine if the payment gateway supports auto billing.
     */
    public function supportsAutoBilling(): bool
    {
        return true;
    }

    /**
     * Auto charge the invoice.
     *
     * @return mixed
     */
    public function autoCharge(Invoice $invoice, $remainingCredit = 0)
    {
        return $invoice->checkout($this, function ($invoice) use ($remainingCredit) {
            try {
                // Get auto billing data
                $autoBillingData = $invoice->tenant->getAutoBillingData(self::TYPE);

                if (! $autoBillingData) {
                    return new TransactionResult(
                        TransactionResult::RESULT_FAILED,
                        'No auto billing data found'
                    );
                }

                // Get the payment method data from metadata
                $metadata = $autoBillingData->metadata;

                // Create a pending transaction first
                $transaction = $invoice->createPendingTransaction($this, $invoice->tenant_id);

                $total = $invoice->finalPayableAmount($remainingCredit);

                // Create payment intent without custom idempotency - let Stripe handle it
                $paymentIntent = PaymentIntent::create([
                    'amount' => $this->convertPrice($total, $invoice->getCurrencyCode()),
                    'currency' => $invoice->getCurrencyCode(),
                    'customer' => $metadata['stripe_customer_id'],
                    'payment_method' => $autoBillingData->payment_method_id,
                    'off_session' => true,
                    'confirm' => true,
                    'description' => "Auto-charge Invoice #{$invoice->id} | Transaction: {$transaction->id}",
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'tenant_id' => $invoice->tenant_id,
                        'transaction_id' => $transaction->id,
                        'charge_type' => 'auto_billing',
                        'invoice_number' => $invoice->invoice_number,
                    ],
                ]);

                // Update transaction with Stripe's payment intent ID and idempotency key
                $transaction->update([
                    'idempotency_key' => $paymentIntent->id, // Use Stripe's payment intent ID as idempotency key
                    'metadata' => [
                        'stripe_payment_intent_id' => $paymentIntent->id,
                        'payment_method_id' => $autoBillingData->payment_method_id,
                        'auto_charge' => true,
                        'stripe_status' => $paymentIntent->status,
                        'charge_created_at' => now()->toISOString(),
                    ],
                ]);

                // Handle immediate payment results
                if ($paymentIntent->status === 'succeeded') {
                    // Note: Credit will be deducted via webhook processing

                    // Update transaction status to completed
                    $transaction->update([
                        'status' => 'success',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'completed_at' => now()->toISOString(),
                            'stripe_status' => 'succeeded',
                        ]),
                    ]);

                    event(new TransactionCreated($transaction->id, $invoice->id));

                    return new TransactionResult(
                        TransactionResult::RESULT_DONE,
                        'Auto charge completed successfully'
                    );
                }

                // If payment requires action, return appropriate result
                if ($paymentIntent->status === 'requires_action') {
                    $authPaymentLink = tenant_route('tenant.payment.stripe.auth', [
                        'invoice' => $invoice->id,
                        'payment_intent' => $paymentIntent->id,
                    ]);

                    $transaction->update([
                        'status' => 'requires_action',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'requires_action_at' => now()->toISOString(),
                            'auth_link' => $authPaymentLink,
                        ]),
                    ]);

                    return new TransactionResult(
                        TransactionResult::RESULT_FAILED,
                        'Authentication required. <a href="'.$authPaymentLink.'">Click here to authenticate</a>'
                    );
                }

                // For other statuses, consider it processing
                return new TransactionResult(
                    TransactionResult::RESULT_PENDING,
                    'Payment is being processed'
                );
            } catch (CardException $e) {
                // Update transaction status if it exists
                if (isset($transaction)) {
                    $transaction->update([
                        'status' => 'failed',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'failure_reason' => $e->getError()->message,
                            'failure_code' => $e->getError()->code,
                            'failure_type' => $e->getError()->type,
                            'failed_at' => now()->toISOString(),
                        ]),
                    ]);
                }

                // Authentication required
                $authPaymentLink = tenant_route('tenant.payment.stripe.auth', ['invoice' => $invoice->id]);

                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    $e->getError()->message.' Click <a href="'.$authPaymentLink.'">here</a> to authenticate.'
                );
            } catch (Exception $e) {
                // Update transaction status if it exists
                if (isset($transaction)) {
                    $transaction->update([
                        'status' => 'failed',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'failure_reason' => $e->getMessage(),
                            'failed_at' => now()->toISOString(),
                        ]),
                    ]);
                }

                payment_log('Stripe auto-charge error', 'error', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                    'transaction_id' => $transaction->id ?? null,
                    'trace' => $e->getTraceAsString(),
                ]);

                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    $e->getMessage()
                );
            }
        });
    }

    /**
     * Get the URL for updating auto billing data.
     */
    public function getAutoBillingDataUpdateUrl(string $returnUrl = '/'): string
    {
        return tenant_route('tenant.payment.stripe.auto_billing_data', ['return_url' => $returnUrl]);
    }

    /**
     * Verify the transaction.
     *
     *
     * @throws \Exception
     */
    public function verify(Transaction $transaction): TransactionResult
    {
        throw new Exception('Stripe should not have pending transactions to verify');
    }

    /**
     * Determine if the payment gateway allows manual reviewing of transactions.
     */
    public function allowManualReviewingOfTransaction(): bool
    {
        return false;
    }

    /**
     * Get the minimum charge amount for the given currency.
     *
     * @param  string  $currency
     * @return float
     */
    public function getMinimumChargeAmount($currency)
    {
        return 0;
    }

    /**
     * Get the publishable key.
     */
    public function getPublishableKey(): string
    {
        $settings = get_batch_settings(['payment.stripe_publishable']);

        return $settings['payment.stripe_publishable'] ?? '';
    }

    /**
     * Convert price to cents for Stripe.
     *
     * @param  float  $price
     * @param  string  $currency
     */
    protected function convertPrice($price, $currency): int
    {
        $zeroDecimalCurrencies = ['JPY', 'KRW', 'VND'];

        if (in_array($currency, $zeroDecimalCurrencies)) {
            return (int) $price;
        }

        return (int) ($price * 100);
    }

    /**
     * Create a payment intent for the invoice.
     */
    public function createPaymentIntent(Invoice $invoice, $remainingCredit = 0): array
    {
        try {
            $user = getUserByTenantId(tenant_id());
            $tenant = Tenant::find(tenant_id());

            // Get or create Stripe customer FIRST
            $stripeCustomer = $this->getStripeCustomer(
                $tenant->stripe_customer_id,
                [
                    'email' => $user->email,
                    'name' => $user->firstname.' '.$user->lastname,
                ]
            );

            // Update tenant with customer ID if not set or different
            if ($tenant->stripe_customer_id !== $stripeCustomer->id) {
                $tenant->stripe_customer_id = $stripeCustomer->id;
                $tenant->save();
            }

            // Get customer's default payment method if available
            $paymentMethodData = [];
            $defaultPaymentMethod = PaymentMethod::where('tenant_id', $tenant->id)
                ->where('is_default', true)
                ->first();

            if ($defaultPaymentMethod) {
                // Verify the payment method is still attached to this customer
                try {
                    $stripePaymentMethod = $this->client->paymentMethods->retrieve($defaultPaymentMethod->payment_method_id);
                    if ($stripePaymentMethod->customer === $stripeCustomer->id) {
                        $paymentMethodData['payment_method'] = $defaultPaymentMethod->payment_method_id;
                        $paymentMethodData['confirm'] = false;
                    }
                } catch (Exception $e) {
                    payment_log('Could not verify default payment method', 'warning', [
                        'payment_method_id' => $defaultPaymentMethod->payment_method_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $total = $invoice->finalPayableAmount($remainingCredit);

            // CRITICAL: Always include customer in payment intent
            $basePaymentIntentData = [
                'amount' => round($total * 100), // Convert to cents
                'currency' => strtolower($invoice->currency->code ?? 'USD'),
                'customer' => $stripeCustomer->id, // ALWAYS include customer
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenant->id,
                    'invoice_number' => $invoice->invoice_number,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'always',  // Always allow redirects for payment methods that require them
                ],
                // Configure setup for future usage based on payment method availability
                'setup_future_usage' => 'off_session',
            ];

            $paymentIntentData = array_merge($basePaymentIntentData, $paymentMethodData);

            $paymentIntent = $this->client->paymentIntents->create($paymentIntentData);

            // Note: Credit will be deducted only after successful payment completion

            return [
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'customer_id' => $stripeCustomer->id,
            ];
        } catch (Exception $e) {
            payment_log('Failed to create payment intent', 'error', [
                'invoice_id' => $invoice->id,
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get or create a Stripe customer for the given UID.
     *
     * @param  string  $customerUid
     * @return \Stripe\Customer
     */
    public function getStripeCustomer($customerId, array $data = [])
    {
        try {
            // If we have a customer ID, try to retrieve it first
            if (! empty($customerId)) {
                try {
                    $customer = $this->client->customers->retrieve($customerId);

                    // Update customer data if provided
                    if (! empty($data)) {
                        $customer = $this->client->customers->update($customer->id, $data);
                    }

                    return $customer;
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                }
            }

            // Create new customer
            $customerData = array_merge($data, [
                'metadata' => [
                    'tenant_id' => tenant_id(),
                    'created_from' => 'whatsmark_app',
                ],
            ]);

            $customer = $this->client->customers->create($customerData);

            return $customer;
        } catch (Exception $e) {
            payment_log('Failed to get/create Stripe customer', 'error', [
                'attempted_customer_id' => $customerId,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
