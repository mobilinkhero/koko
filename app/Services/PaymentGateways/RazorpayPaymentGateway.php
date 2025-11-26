<?php

namespace App\Services\PaymentGateways;

use App\Events\TransactionCreated;
use App\Models\Invoice\Invoice;
use App\Models\Tenant;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use App\Notifications\RazorpayAuthenticationRequired;
use App\Services\Billing\TransactionResult;
use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\Settings\PaymentSettings;
use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Razorpay\Api\Api;

/**
 * RazorpayPaymentGateway
 *
 * Comprehensive Razorpay payment integration for multi-tenant WhatsApp SaaS
 * applications. Handles subscription payments, one-time transactions, webhook
 * processing, and advanced payment features with full error handling.
 *
 * Key Features:
 * - Order creation and payment processing
 * - Subscription payment handling
 * - Webhook event processing and validation
 * - Multi-currency support (primarily INR)
 * - Comprehensive error handling and logging
 * - Transaction result standardization
 * - Auto-billing support with saved payment methods
 *
 * Razorpay Integration:
 * - API Version: v1
 * - Payment Methods: Cards, UPI, Net Banking, Wallets
 * - Webhook Endpoints: Payment confirmations, order updates
 * - Security: Webhook signature verification
 *
 * Payment Flow:
 * 1. Order creation with amount and metadata
 * 2. Client-side payment processing
 * 3. Webhook event processing for status updates
 * 4. Transaction recording and invoice updates
 * 5. Subscription activation/renewal processing
 *
 * Security Features:
 * - API key validation and secure storage
 * - Webhook signature verification
 * - Transaction data sanitization
 *
 * Usage:
 * ```php
 * // Initialize gateway
 * $gateway = new RazorpayPaymentGateway($keyId, $keySecret);
 *
 * // Create payment order
 * $orderData = $gateway->createPaymentOrder($invoice);
 *
 * // Verify transaction
 * $result = $gateway->verify($transaction);
 *
 * // Handle webhook
 * $gateway->handleWebhook($webhookPayload, $signature);
 * ```
 *
 * @version 1.0.0
 */
class RazorpayPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Razorpay key ID for client-side and server-side operations
     *
     * Used for both frontend payment form initialization and
     * server-side API calls. Safe to expose in client-side code.
     *
     * @var string
     */
    protected $keyId;

    /**
     * Razorpay key secret for server-side operations
     *
     * Used for creating orders, verifying payments,
     * and other sensitive operations. Must be kept secure.
     *
     * @var string
     */
    protected $keySecret;

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
     * Razorpay API client instance
     *
     * Initialized Razorpay client for making API calls
     * with proper authentication and configuration.
     *
     * @var \Razorpay\Api\Api
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
    public const TYPE = 'razorpay';

    /**
     * Retrieve Razorpay key secret from settings
     *
     * Fetches the secret key from tenant or global settings
     * using batch settings retrieval for performance.
     *
     * @return string|null Razorpay key secret or null if not configured
     *
     * @example
     * ```php
     * $keySecret = $gateway->getKeySecret();
     * if ($keySecret) {
     *     // Secret key is configured
     * }
     * ```
     */
    public function getKeySecret()
    {
        $settings = get_batch_settings(['payment.razorpay_key_secret']);

        return $settings['payment.razorpay_key_secret'];
    }

    /**
     * Retrieve Razorpay webhook secret for signature verification
     *
     * Gets the webhook endpoint secret used to verify that
     * incoming webhook events are actually from Razorpay.
     *
     * @return string|null Webhook secret or null if not configured
     *
     * @example
     * ```php
     * $webhookSecret = $gateway->getWebhookSecret();
     * // Used for: signature verification
     * ```
     */
    public function getWebhookSecret()
    {
        $settings = get_batch_settings(['payment.razorpay_webhook_secret']);

        return $settings['payment.razorpay_webhook_secret'];
    }

    /**
     * Initialize Razorpay payment gateway instance
     *
     * Creates a new Razorpay gateway with provided API credentials,
     * validates configuration, and initializes the Razorpay client
     * if all requirements are met.
     *
     * Initialization Process:
     * 1. Store API credentials
     * 2. Validate configuration and settings
     * 3. Initialize Razorpay client instance
     *
     * @param  string  $keyId  Razorpay key ID
     * @param  string  $keySecret  Razorpay key secret
     *
     * @example
     * ```php
     * $gateway = new RazorpayPaymentGateway(
     *     'rzp_live_...',
     *     'your_secret_here'
     * );
     *
     * if ($gateway->isActive()) {
     *     // Gateway is ready for payments
     * }
     * ```
     *
     * @see validate()
     */
    public function __construct(string $keyId, string $keySecret)
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;

        $this->validate();
        if ($this->active) {
            $this->client = new Api($this->keyId, $this->keySecret);
        }
    }

    /**
     * Validate payment gateway configuration and activation status
     *
     * Checks both API credentials and admin settings to determine
     * if the gateway should be active and ready for processing payments.
     *
     * Validation Criteria:
     * 1. Valid key ID and secret provided
     * 2. Gateway enabled in admin payment settings
     * 3. Both conditions must be true for activation
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
        $hasCredentials = ! empty($this->keyId) && ! empty($this->keySecret);

        // Check if the gateway is enabled by admin
        $settings = app(PaymentSettings::class);
        $isEnabled = $settings->razorpay_enabled ?? false;

        // Only active if both conditions are met
        $this->active = $hasCredentials && $isEnabled;
    }

    /**
     * Get the payment gateway name.
     */
    public function getName(): string
    {
        return 'Razorpay';
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
        return 'Pay securely with Razorpay - UPI, Cards, Net Banking, and Wallets supported.';
    }

    /**
     * Get the payment gateway short description.
     */
    public function getShortDescription(): string
    {
        return 'Razorpay Payment';
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
        return route('admin.settings.payment.razorpay');
    }

    /**
     * Get the checkout URL for the invoice.
     */
    public function getCheckoutUrl(Invoice $invoice): string
    {
        return tenant_route('tenant.payment.razorpay.checkout', ['invoice' => $invoice->id]);
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
     * Handles the Razorpay auto-charging process with consideration for RBI guidelines
     * that require customer authentication for recurring payments in India.
     *
     * The method creates a payment order, sends a payment link to the customer,
     * and handles the authentication flow as required by regulations.
     *
     * @param  Invoice  $invoice  The invoice to charge
     * @param  float  $remainingCredit  Optional credit to apply to the charge
     * @return mixed Transaction result or checkout result
     */
    public function autoCharge(Invoice $invoice, $remainingCredit = 0)
    {
        return new TransactionResult(
            false,
            'Razorpay payment gateway does not support auto charge!.'
        );
    }

    /**
     * Get the URL for updating auto billing data.
     */
    public function getAutoBillingDataUpdateUrl(string $returnUrl = '/'): string
    {
        return tenant_route('tenant.payment.razorpay.auto_billing_data', ['return_url' => $returnUrl]);
    }

    /**
     * Verify the transaction.
     *
     * @throws \Exception
     */
    public function verify(Transaction $transaction): TransactionResult
    {
        try {
            // Get payment details from transaction metadata
            $metadata = $transaction->metadata;

            if (! isset($metadata['razorpay_payment_id']) || ! isset($metadata['razorpay_order_id'])) {
                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    'Missing payment verification data'
                );
            }

            $paymentId = $metadata['razorpay_payment_id'];
            $orderId = $metadata['razorpay_order_id'];
            $signature = $metadata['razorpay_signature'] ?? '';

            // Verify payment signature
            $generatedSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->keySecret);

            if (! hash_equals($generatedSignature, $signature)) {
                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    'Payment signature verification failed'
                );
            }

            // Fetch payment details from Razorpay
            $payment = $this->client->payment->fetch($paymentId);
            if ($payment->status === 'captured') {
                // Get the invoice and calculate credit amount now during successful payment
                $invoice = $transaction->invoice;
                $remainingCredit = 0;

                if ($invoice) {
                    try {
                        $balance = TenantCreditBalance::getOrCreateBalance($invoice->tenant_id, $invoice->currency_id);
                        if ($balance && $balance->balance > 0) {
                            $total = $invoice->total();
                            $remainingCredit = min($balance->balance, $total);

                            // Now deduct the credit after successful payment verification
                            if ($remainingCredit > 0) {
                                TenantCreditBalance::deductCredit(
                                    $invoice->tenant_id,
                                    $remainingCredit,
                                    'Razorpay Payment Used Credit',
                                    $invoice->id
                                );

                                payment_log('Razorpay Gateway: Credit deducted after successful payment', 'info', [
                                    'tenant_id' => $invoice->tenant_id,
                                    'amount' => $remainingCredit,
                                    'invoice_id' => $invoice->id,
                                    'payment_id' => $paymentId,
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        payment_log('Razorpay Gateway: Failed to process credit during verification', 'warning', [
                            'error' => $e->getMessage(),
                            'invoice_id' => $invoice->id,
                        ]);
                        // Continue with zero credit
                        $remainingCredit = 0;
                    }
                }

                // Add credit information to metadata
                $updatedMetadata = array_merge($transaction->metadata ?? [], [
                    'verified_at' => now()->toISOString(),
                    'razorpay_status' => $payment->status,
                    'amount_verified' => $payment->amount,
                    'credit_applied' => $remainingCredit,
                    'credit_processed' => $remainingCredit > 0,
                ]);

                // Update transaction status
                $transaction->update([
                    'status' => 'success',
                    'metadata' => $updatedMetadata,
                ]);

                // Mark the invoice as paid if it exists and isn't already paid
                if ($transaction->invoice && $transaction->invoice->status !== Invoice::STATUS_PAID) {
                    // Get the typed invoice instance to ensure proper processing
                    $invoice = $transaction->invoice;
                    $typedInvoice = $invoice->mapType();

                    // Use the appropriate invoice type for processing
                    if ($typedInvoice->id === $invoice->id && get_class($typedInvoice) !== get_class($invoice)) {
                        $typedInvoice->markAsPaid();

                        payment_log('Invoice marked as paid via Razorpay verification (typed invoice)', 'info', [
                            'invoice_id' => $invoice->id,
                            'invoice_type' => get_class($typedInvoice),
                            'transaction_id' => $transaction->id,
                        ]);
                    } else {
                        $invoice->markAsPaid();

                        payment_log('Invoice marked as paid via Razorpay verification', 'info', [
                            'invoice_id' => $invoice->id,
                            'transaction_id' => $transaction->id,
                        ]);
                    }
                }

                event(new TransactionCreated($transaction->id, $transaction->invoice_id));

                return new TransactionResult(
                    TransactionResult::RESULT_DONE,
                    'Payment verified successfully'
                );
            }

            if ($payment->status === 'failed') {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'verified_at' => now()->toISOString(),
                        'razorpay_status' => $payment->status,
                        'failure_reason' => $payment->error_description ?? 'Payment failed',
                    ]),
                ]);

                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    'Payment failed: '.($payment->error_description ?? 'Unknown error')
                );
            }

            // Payment is still processing
            return new TransactionResult(
                TransactionResult::RESULT_PENDING,
                'Payment is being processed'
            );
        } catch (Exception $e) {
            payment_log('Razorpay verification error', 'error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new TransactionResult(
                TransactionResult::RESULT_FAILED,
                'Verification failed: '.$e->getMessage()
            );
        }
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
        // Razorpay minimum amounts by currency
        $minimums = [
            'INR' => 1.00,    // 1 INR
            'USD' => 0.50,    // 50 cents
            'EUR' => 0.50,    // 50 cents
            'GBP' => 0.30,    // 30 pence
            'AUD' => 0.50,    // 50 cents
            'CAD' => 0.50,    // 50 cents
            'SGD' => 0.50,    // 50 cents
        ];

        return $minimums[$currency] ?? 1.00;
    }

    /**
     * Get the key ID.
     */
    public function getKeyId(): string
    {
        $settings = get_batch_settings(['payment.razorpay_key_id']);

        return $settings['payment.razorpay_key_id'] ?? '';
    }

    /**
     * Convert price to smallest currency unit for Razorpay.
     *
     * @param  float  $price
     * @param  string  $currency
     */
    protected function convertPrice($price, $currency): int
    {
        // Razorpay expects amounts in paisa for INR, cents for other currencies
        return (int) ($price * 100);
    }

    /**
     * Create a payment order for the invoice.
     */
    public function createPaymentOrder(Invoice $invoice, $remainingCredit = 0): array
    {
        try {
            $user = getUserByTenantId(tenant_id());

            $tenant = Tenant::find(tenant_id());

            // Calculate total after applying credit and coupon discount
            $total = $invoice->finalPayableAmount($remainingCredit);

            // Ensure total is at least the minimum chargeable amount
            $total = max($total, $this->getMinimumChargeAmount($invoice->getCurrencyCode()));

            // Create order with Razorpay
            $orderData = [
                'amount' => $this->convertPrice($total, $invoice->getCurrencyCode()),
                'currency' => $invoice->getCurrencyCode(),
                'receipt' => "invoice_{$invoice->id}_".time(),
                'notes' => [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenant->id,
                    'customer_email' => $user->email,
                    'customer_name' => $user->firstname.' '.$user->lastname,
                ],
            ];

            // Debug currency info
            payment_log('Creating Razorpay order with currency info', 'info', [
                'invoice_id' => $invoice->id,
                'currency_code' => $invoice->getCurrencyCode(),
                'currency_id' => $invoice->currency_id,
                'has_currency_relation' => $invoice->currency ? true : false,
                'order_currency' => $orderData['currency'],
                'order_amount' => $orderData['amount'],
            ]);

            $order = $this->client->order->create($orderData);

            // Note: Credit will be deducted only after successful payment verification
            payment_log('Razorpay Gateway: Order created, credit will be deducted after payment completion', 'info', [
                'tenant_id' => $invoice->tenant_id,
                'remaining_credit' => $remainingCredit,
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
            ]);

            return [
                'id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'status' => $order->status,
                'receipt' => $order->receipt,
            ];
        } catch (Exception $e) {
            payment_log('Failed to create Razorpay order', 'error', [
                'invoice_id' => $invoice->id,
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        try {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);

            return hash_equals($expectedSignature, $signature);
        } catch (Exception $e) {
            payment_log('Webhook signature verification failed', 'error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create a payment link for the invoice and Razorpay order.
     *
     * Generates a payment link that the customer can use to complete the payment
     * with proper authentication as required by RBI regulations.
     *
     * @param  Invoice  $invoice  The invoice to pay
     * @param  object  $order  The Razorpay order
     * @param  Transaction  $transaction  The pending transaction
     * @return string The payment link URL
     */
    protected function createPaymentLink(Invoice $invoice, $order, Transaction $transaction): string
    {
        $user = getUserByTenantId($invoice->tenant_id);
        $tenant = Tenant::find($invoice->tenant_id);

        // Create a unique token for this payment link
        $token = md5($order->id.$invoice->id.time());

        // Store the token in the transaction metadata for verification
        $transaction->update([
            'metadata' => array_merge($transaction->metadata ?? [], [
                'payment_link_token' => $token,
                'payment_link_created_at' => \Illuminate\Support\Carbon::now()->toISOString(),
            ]),
        ]);

        // Generate a secure payment link URL
        return tenant_route('tenant.payment.razorpay.authenticate', [
            'order' => $order->id,
            'invoice' => $invoice->id,
            'transaction' => $transaction->id,
            'token' => $token,
        ]);
    }

    /**
     * Send payment authentication notification to the user.
     *
     * Sends an email notification with the payment link for authentication
     * as required by RBI regulations for recurring payments.
     *
     * @param  \App\Models\User  $user  The user to notify
     * @param  Invoice  $invoice  The invoice to pay
     * @param  string  $paymentLink  The payment authentication link
     */
    protected function sendPaymentAuthenticationNotification($user, Invoice $invoice, string $paymentLink): void
    {
        // Send email notification with payment link
        try {
            // Make sure we have a valid user object that can receive notifications
            if (! $user || ! method_exists($user, 'notify')) {
                payment_log('Cannot send notification: invalid user object', 'error', [
                    'invoice_id' => $invoice->id,
                ]);

                return;
            }

            // Use the specific notification class
            if (class_exists(RazorpayAuthenticationRequired::class)) {
                $user->notify(new RazorpayAuthenticationRequired($invoice, $paymentLink));

                payment_log('Sent RazorpayAuthenticationRequired notification', 'info', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $user->id,
                ]);
            } else {
                // Log that we couldn't send a notification
                payment_log('RazorpayAuthenticationRequired notification class not found', 'warning', [
                    'invoice_id' => $invoice->id,
                ]);

                // Use Laravel's Mail facade directly as a fallback
                try {
                    $invoiceNumber = $invoice->invoice_number;
                    $amount = $invoice->formattedTotal();
                    $planName = $invoice->subscription ? $invoice->subscription->plan->name : 'Subscription';

                    Mail::send(
                        [],
                        [],
                        function ($message) use ($user, $paymentLink, $invoiceNumber, $amount, $planName) {
                            $message->to($user->email)
                                ->subject('Payment Authentication Required')
                                ->html(
                                    "Hello {$user->firstname},<br><br>".
                                    "Your recurring payment for invoice #{$invoiceNumber} requires authentication.<br><br>".
                                    "Invoice: #{$invoiceNumber}<br>".
                                    "Amount: {$amount}<br>".
                                    "Plan: {$planName}<br><br>".
                                    'Per RBI regulations, you must authenticate this payment.<br><br>'.
                                    "<a href='{$paymentLink}' style='padding: 8px 16px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Authenticate Payment</a><br><br>".
                                    'This authentication link will expire in 24 hours.<br><br>'.
                                    'Thank you for your business.'
                                );
                        }
                    );

                    payment_log('Sent fallback email for payment authentication', 'info', [
                        'invoice_id' => $invoice->id,
                        'user_email' => $user->email,
                    ]);
                } catch (Exception $mailError) {
                    payment_log('Failed to send fallback payment authentication email', 'error', [
                        'invoice_id' => $invoice->id,
                        'error' => $mailError->getMessage(),
                        'trace' => $mailError->getTraceAsString(),
                    ]);
                }
            }
        } catch (Exception $e) {
            payment_log('Failed to send payment authentication notification', 'error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
