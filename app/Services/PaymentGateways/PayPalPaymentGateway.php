<?php

namespace App\Services\PaymentGateways;

use App\Events\TransactionCreated;
use App\Models\Invoice\Invoice;
use App\Models\Tenant;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use App\Services\Billing\TransactionResult;
use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\Settings\PaymentSettings;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPalPaymentGateway
 *
 * Comprehensive PayPal payment integration for multi-tenant WhatsApp SaaS
 * applications. Handles subscription payments, one-time transactions, webhook
 * processing, and advanced payment features with full error handling.
 *
 * Key Features:
 * - Order creation and payment processing
 * - Subscription payment handling
 * - Webhook event processing and validation
 * - Multi-currency support
 * - Comprehensive error handling and logging
 * - Transaction result standardization
 */
class PayPalPaymentGateway implements PaymentGatewayInterface
{
    /**
     * @var \App\Settings\PaymentSettings
     */
    protected $settings;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Constructor
     */
    public function __construct(PaymentSettings $settings)
    {
        $this->settings = $settings;
        $this->baseUrl = $this->settings->paypal_mode === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /**
     * Get the name of the payment gateway.
     */
    public function getName(): string
    {
        return 'PayPal';
    }

    /**
     * Get the type identifier for the payment gateway.
     */
    public function getType(): string
    {
        return 'paypal';
    }

    /**
     * Get the description of the payment gateway.
     */
    public function getDescription(): string
    {
        return t('pay_securely_with_paypal');
    }

    /**
     * Get a short description of the payment gateway.
     */
    public function getShortDescription(): string
    {
        return t('paypal_payment');
    }

    /**
     * Check if the payment gateway is active.
     */
    public function isActive(): bool
    {
        if (! $this->settings->paypal_enabled) {
            return false;
        }

        if (empty($this->settings->paypal_client_id) || empty($this->settings->paypal_client_secret)) {
            return false;
        }

        return true;
    }

    /**
     * Get the checkout URL for the payment gateway.
     */
    public function getCheckoutUrl(Invoice $invoice): string
    {
        return tenant_route('tenant.payment.paypal.checkout', ['invoice' => $invoice->id]);
    }

    /**
     * Create a PayPal payment order with credit management.
     */
    public function createPaymentOrder(Invoice $invoice, float $remainingCredit = 0): ?array
    {
        try {
            $this->getAccessToken();

            // Calculate amount after applying credit (like Stripe implementation)
            $total = $invoice->total();
            $creditApplied = $remainingCredit > 0 ? min($remainingCredit, $total) : 0;
            $finalAmount = $invoice->finalPayableAmount($remainingCredit);

            // Note: Credit will be deducted only after successful payment verification

            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $invoice->id,
                        'custom_id' => $invoice->invoice_number,
                        'description' => $invoice->description ?? t('invoice_payment'),
                        'amount' => [
                            'currency_code' => $invoice->currency,
                            'value' => number_format($finalAmount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'brand_name' => $this->settings->paypal_brand_name ?: 'WhatsMarks',
                    'landing_page' => 'BILLING',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => tenant_route('tenant.payments.paypal.success'),
                    'cancel_url' => tenant_route('tenant.payments.paypal.cancel'),
                ],
            ];

            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders", $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                payment_log('PayPal order created with credit management', 'info', [
                    'order_id' => $responseData['id'],
                    'original_amount' => $total,
                    'credit_applied' => $creditApplied,
                    'final_amount' => $finalAmount,
                ]);

                return $responseData;
            } else {
                payment_log('PayPal order creation failed: '.$response->body(), 'error', $response->json());

                return null;
            }
        } catch (Exception $e) {
            payment_log('PayPal order creation exception: '.$e->getMessage(), 'error');

            return null;
        }
    }

    /**
     * Verify a transaction status.
     */
    public function verify(Transaction $transaction): TransactionResult
    {
        try {
            $this->getAccessToken();

            $orderId = $transaction->order_id ?? null;
            $captureId = $transaction->transaction_id ?? null;

            if (! $orderId && ! $captureId) {
                return new TransactionResult(false, 'Invalid PayPal transaction data');
            }

            // If we have a captureId, check its status
            if ($captureId) {
                $response = Http::withToken($this->accessToken)
                    ->get("{$this->baseUrl}/v2/payments/captures/{$captureId}");

                if ($response->successful()) {
                    $responseData = $response->json();
                    $status = $responseData['status'] ?? '';

                    if ($status === 'COMPLETED') {
                        return new TransactionResult(true, t('payment_successful'));
                    } else {
                        payment_log('PayPal capture not completed: '.$status, 'warning', $responseData);

                        return new TransactionResult(false, t('payment_not_completed'));
                    }
                }
            }

            // If no capture ID or capture check failed, check the order
            if ($orderId) {
                $response = Http::withToken($this->accessToken)
                    ->get("{$this->baseUrl}/v2/checkout/orders/{$orderId}");

                if ($response->successful()) {
                    $responseData = $response->json();
                    $status = $responseData['status'] ?? '';

                    if ($status === 'COMPLETED') {
                        return new TransactionResult(true, t('payment_successful'));
                    } else {
                        payment_log('PayPal order not completed: '.$status, 'warning', $responseData);

                        return new TransactionResult(false, t('payment_not_completed'));
                    }
                }
            }

            payment_log('PayPal verification failed for transaction: '.$transaction->id, 'error');

            return new TransactionResult(false, t('payment_verification_failed'));

        } catch (Exception $e) {
            payment_log('PayPal verification exception: '.$e->getMessage(), 'error');

            return new TransactionResult(false, t('payment_verification_failed').': '.$e->getMessage());
        }
    }

    /**
     * Process and verify payment data from checkout.
     */
    public function processPayment(array $data, Invoice $invoice): TransactionResult
    {
        try {
            $this->getAccessToken();
            $orderId = $data['order_id'] ?? null;

            if (! $orderId) {
                return new TransactionResult(TransactionResult::RESULT_FAILED, 'Invalid PayPal order ID');
            }

            // Capture the payment
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if (! $response->successful()) {
                payment_log('PayPal capture failed: '.$response->body(), 'error', $response->json());

                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    t('payment_failed_please_try_again')
                );
            }

            $responseData = $response->json();
            $captureId = $responseData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
            $captureStatus = $responseData['purchase_units'][0]['payments']['captures'][0]['status'] ?? null;

            if ($captureStatus !== 'COMPLETED') {
                payment_log('PayPal capture not completed: '.$captureStatus, 'warning', $responseData);

                return new TransactionResult(
                    TransactionResult::RESULT_FAILED,
                    t('payment_not_completed')
                );
            }

            // Create transaction record
            $transaction = Transaction::create([
                'tenant_id' => tenant_id(),
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'currency' => $invoice->currency,
                'status' => 'completed',
                'payment_method' => 'paypal',
                'transaction_id' => $captureId,
                'order_id' => $orderId,
                'data' => $responseData,
            ]);

            // Update invoice status
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'paypal',
                'transaction_id' => $captureId,
            ]);

            // Update credit balance if needed
            if ($invoice->credits > 0) {
                $tenant = Tenant::find(tenant_id());
                TenantCreditBalance::addCredits($tenant, $invoice->credits, 'purchase', $invoice->id);
            }

            // Trigger transaction created event
            event(new TransactionCreated($transaction->id, $invoice->id));

            payment_log('PayPal payment completed: '.$captureId, 'info', $responseData);

            return new TransactionResult(
                TransactionResult::RESULT_DONE,
                t('payment_successful')
            );
        } catch (Exception $e) {
            payment_log('PayPal verification exception: '.$e->getMessage(), 'error');

            return new TransactionResult(
                TransactionResult::RESULT_FAILED,
                t('payment_verification_failed').': '.$e->getMessage()
            );
        }
    }

    /**
     * Check if the gateway supports auto-billing.
     */
    public function supportsAutoBilling(): bool
    {
        return false;
    }

    /**
     * Auto-charge a customer based on their saved payment method.
     */
    public function autoCharge(Invoice $invoice): TransactionResult
    {
        // For PayPal, auto-charging requires subscription setup
        // This implementation assumes subscriptions are managed by PayPal
        return new TransactionResult(
            false,
            t('auto_charging_not_supported_for_paypal')
        );
    }

    /**
     * Check if transactions from this gateway require manual review.
     */
    public function allowManualReviewingOfTransaction(): bool
    {
        return false;
    }

    /**
     * Get the administrative settings URL.
     */
    public function getSettingsUrl(): string
    {
        return route('admin.settings.payment.paypal');
    }

    /**
     * Get the URL for updating auto-billing data.
     */
    public function getAutoBillingDataUpdateUrl(string $returnUrl = '/'): string
    {
        // For PayPal, you might need to implement an actual update URL based on your requirements
        return tenant_route('tenant.payments.paypal.auto_billing_data', ['return_url' => $returnUrl]);
    }

    /**
     * Get the minimum charge amount for the payment gateway.
     *
     * @param  string  $currency
     */
    public function getMinimumChargeAmount($currency): float
    {
        // PayPal minimum amounts by currency
        $minimums = [
            'USD' => 0.50,
            'EUR' => 0.50,
            'GBP' => 0.50,
            'CAD' => 0.50,
            'AUD' => 0.50,
            'INR' => 5.00,
        ];

        return $minimums[$currency] ?? 0.50;
    }

    /**
     * Get PayPal access token.
     */
    protected function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $clientId = $this->settings->paypal_client_id;
        $clientSecret = $this->settings->paypal_client_secret;

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->successful()) {
            $this->accessToken = $response->json()['access_token'];

            return $this->accessToken;
        }

        throw new Exception('Failed to get PayPal access token: '.$response->body());
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, array $headers): bool
    {
        // PayPal webhook verification
        $webhookId = $this->settings->paypal_webhook_id;
        if (empty($webhookId)) {
            return false;
        }

        try {
            $this->getAccessToken();

            $verificationData = [
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($payload, true),
            ];

            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", $verificationData);

            if ($response->successful()) {
                $result = $response->json();

                return $result['verification_status'] === 'SUCCESS';
            }

            return false;
        } catch (Exception $e) {
            payment_log('PayPal webhook signature verification failed: '.$e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Process PayPal webhook event.
     */
    public function processWebhookEvent(array $payload): void
    {
        $eventType = $payload['event_type'] ?? '';
        $resourceType = $payload['resource_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        payment_log('Processing PayPal webhook: '.$eventType, 'info', $payload);

        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePaymentCompleted($resource);
                break;

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.DECLINED':
                $this->handlePaymentFailed($resource);
                break;

            case 'CHECKOUT.ORDER.COMPLETED':
                $this->handleOrderCompleted($resource);
                break;

            default:
                payment_log('Unhandled PayPal webhook event: '.$eventType, 'info');
                break;
        }
    }

    /**
     * Handle payment completed webhook event.
     */
    protected function handlePaymentCompleted(array $resource): void
    {
        $captureId = $resource['id'] ?? null;
        $customId = $resource['custom_id'] ?? null;

        if (! $captureId || ! $customId) {
            payment_log('Invalid PayPal payment completed webhook', 'error', $resource);

            return;
        }

        // Find invoice by custom_id (invoice number)
        $invoice = Invoice::where('invoice_number', $customId)->first();

        if (! $invoice) {
            payment_log('PayPal webhook: Invoice not found for '.$customId, 'error');

            return;
        }

        // Check if payment already processed
        $existingTransaction = Transaction::where('transaction_id', $captureId)->first();
        if ($existingTransaction) {
            payment_log('PayPal webhook: Transaction already processed '.$captureId, 'info');

            return;
        }

        // Create transaction
        $transaction = Transaction::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'amount' => $resource['amount']['value'] ?? $invoice->total,
            'currency' => $resource['amount']['currency_code'] ?? $invoice->currency,
            'status' => 'completed',
            'payment_method' => 'paypal',
            'transaction_id' => $captureId,
            'data' => $resource,
        ]);

        // Update invoice
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'paypal',
            'transaction_id' => $captureId,
        ]);

        // Update credit balance if needed
        if ($invoice->credits > 0) {
            $tenant = Tenant::find($invoice->tenant_id);
            TenantCreditBalance::addCredits($tenant, $invoice->credits, 'purchase', $invoice->id);
        }

        // Trigger event
        event(new TransactionCreated($transaction->id, $invoice->id));

        payment_log('PayPal webhook: Payment completed for invoice #'.$invoice->invoice_number, 'info');
    }

    /**
     * Handle payment failed webhook event.
     */
    protected function handlePaymentFailed(array $resource): void
    {
        $captureId = $resource['id'] ?? null;
        $customId = $resource['custom_id'] ?? null;

        if (! $captureId || ! $customId) {
            payment_log('Invalid PayPal payment failed webhook', 'error', $resource);

            return;
        }

        // Find invoice by custom_id (invoice number)
        $invoice = Invoice::where('invoice_number', $customId)->first();

        if (! $invoice) {
            payment_log('PayPal webhook: Invoice not found for '.$customId, 'error');

            return;
        }

        // Create failed transaction record
        Transaction::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'amount' => $resource['amount']['value'] ?? $invoice->total,
            'currency' => $resource['amount']['currency_code'] ?? $invoice->currency,
            'status' => 'failed',
            'payment_method' => 'paypal',
            'transaction_id' => $captureId,
            'data' => $resource,
        ]);

        payment_log('PayPal webhook: Payment failed for invoice #'.$invoice->invoice_number, 'warning', $resource);
    }

    /**
     * Handle order completed webhook event.
     */
    protected function handleOrderCompleted(array $resource): void
    {
        $orderId = $resource['id'] ?? null;
        $purchaseUnits = $resource['purchase_units'] ?? [];

        if (! $orderId || empty($purchaseUnits)) {
            payment_log('Invalid PayPal order completed webhook', 'error', $resource);

            return;
        }

        foreach ($purchaseUnits as $unit) {
            $customId = $unit['custom_id'] ?? null;
            $referenceId = $unit['reference_id'] ?? null;

            if (! $customId && ! $referenceId) {
                continue;
            }

            // Try to find invoice by reference_id (invoice ID) or custom_id (invoice number)
            $invoice = null;
            if ($referenceId) {
                $invoice = Invoice::find($referenceId);
            }

            if (! $invoice && $customId) {
                $invoice = Invoice::where('invoice_number', $customId)->first();
            }

            if (! $invoice) {
                payment_log('PayPal webhook: Invoice not found for order '.$orderId, 'error');

                continue;
            }

            // Check invoice status
            if ($invoice->status === 'paid') {
                payment_log('PayPal webhook: Invoice already paid '.$invoice->invoice_number, 'info');

                continue;
            }

            // Order completed doesn't necessarily mean payment captured
            // Just log the event for reference
            payment_log('PayPal webhook: Order completed for invoice #'.$invoice->invoice_number, 'info', $resource);
        }
    }
}
