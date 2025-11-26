<?php

namespace App\Http\Controllers\PaymentGateways;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\Subscription;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    /**
     * The PayPal payment gateway instance
     */
    protected $gateway;

    /**
     * PayPalController constructor
     */
    public function __construct()
    {
        $this->gateway = app('billing.manager')->gateway('paypal');
    }

    /**
     * Get PayPal settings from database using your existing pattern
     */
    private function getPayPalSettings(): array
    {
        return get_batch_settings([
            'payment.paypal_enabled',
            'payment.paypal_mode',
            'payment.paypal_client_id',
            'payment.paypal_client_secret',
            'payment.paypal_webhook_id',
        ]);
    }

    /**
     * Check if a PayPal order is still valid and can be completed
     */
    public function checkPayPalOrderStatus(string $orderId): ?array
    {
        try {
            $provider = $this->getPayPalClient();
            $orderDetails = $provider->showOrderDetails($orderId);

            return [
                'id' => $orderDetails['id'] ?? null,
                'status' => $orderDetails['status'] ?? null,
                'is_valid' => in_array($orderDetails['status'] ?? '', ['CREATED', 'SAVED', 'APPROVED']),
                'is_expired' => in_array($orderDetails['status'] ?? '', ['VOIDED', 'EXPIRED']),
                'details' => $orderDetails,
            ];
        } catch (\Exception $e) {
            return [
                'id' => $orderId,
                'status' => 'ERROR',
                'is_valid' => false,
                'is_expired' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get PayPal client with dynamic configuration using Laravel PayPal package
     */
    private function getPayPalClient(): PayPalClient
    {
        // Get configuration from config file but override with dynamic settings
        $config = config('paypal');
        $settings = $this->getPayPalSettings();

        // Override with dynamic settings from admin panel
        $config['mode'] = $settings['payment.paypal_mode'] ?? 'sandbox';
        $config['sandbox']['client_id'] = $settings['payment.paypal_client_id'];
        $config['sandbox']['client_secret'] = $settings['payment.paypal_client_secret'];
        $config['live']['client_id'] = $settings['payment.paypal_client_id'];
        $config['live']['client_secret'] = $settings['payment.paypal_client_secret'];
        $config['notify_url'] = route('webhooks.paypal');

        $provider = new PayPalClient($config);
        $provider->setApiCredentials($config);

        // Let the package handle token management
        $accessToken = $provider->getAccessToken();

        if (isset($accessToken['error'])) {
            Log::error('PayPal access token error', $accessToken);
            throw new \Exception('Failed to get PayPal access token');
        }

        return $provider;
    }

    /**
     * Handle pending PayPal transaction - either resume or retry
     *
     * @param  int  $invoiceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePendingTransaction(Request $request, string $subdomain, $invoiceId)
    {
        $invoice = Invoice::query()
            ->where('id', $invoiceId)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        // Check for existing pending PayPal transaction
        $pendingTransaction = $invoice->transactions()
            ->where('type', 'paypal')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($pendingTransaction && $pendingTransaction->idempotency_key) {
            // Check if the PayPal order is still valid
            $orderStatus = $this->checkPayPalOrderStatus($pendingTransaction->idempotency_key);

            if ($orderStatus && $orderStatus['is_valid']) {
                // Resume existing order
                return response()->json([
                    'success' => true,
                    'action' => 'resume',
                    'id' => $pendingTransaction->idempotency_key,
                    'message' => 'Resuming existing PayPal payment',
                ]);
            } else {
                // Mark old transaction as failed and create new one
                $pendingTransaction->update([
                    'status' => 'failed',
                    'error' => 'PayPal order expired or invalid',
                ]);
            }
        }

        // Create new PayPal order (same logic as process method)
        return $this->process($request, $subdomain, $invoiceId);
    }

    /**
     * Handle PayPal checkout process
     *
     * @param  mixed  $invoiceId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request, string $subdomain, $invoiceId)
    {
        // Check if PayPal is enabled
        $settings = $this->getPayPalSettings();
        if (empty($settings['payment.paypal_enabled'])) {
            return back()->with('error', 'PayPal payment is not available.');
        }

        $invoice = Invoice::with('taxes')
            ->where('id', $invoiceId)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        // If the invoice is free, bypass payment
        if ($invoice->isFree()) {
            $invoice->bypassPayment();

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('subscription_activate_message'),
            ]);

            return redirect()->to(tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]));
        }

        // For subscription invoices, treat them as one-time payments
        // Your application will handle the recurring billing like Stripe
        $isSubscriptionInvoice = $invoice->type === 'new_subscription';

        if ($isSubscriptionInvoice) {
            // Log that this is a subscription payment being processed as one-time (like Stripe)
            payment_log('Processing subscription invoice as one-time PayPal payment (like Stripe)', 'info', [
                'invoice_id' => $invoiceId,
                'tenant_id' => tenant_id(),
                'plan_id' => $invoice->subscription?->plan_id ?? $invoice->plan_id,
            ]);
        }

        // Continue with one-time payment flow
        // Ensure taxes are applied to the invoice
        if ($invoice->taxes()->count() === 0) {
            $invoice->applyTaxes();
        }

        try {
            // Get available credit balance
            $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
            $remainingCredit = 0;
            if ($balance && $balance->balance > 0) {
                $remainingCredit = $balance->balance;
            }

            return view('payment-gateways.paypal.checkout', [
                'invoice' => $invoice,
                'remainingCredit' => $remainingCredit,
                'total' => $invoice->total(),
                'settings' => $settings,
                'isSubscription' => false,
            ]);

        } catch (\Exception $e) {
            payment_log('PayPal checkout error', 'error', [
                'invoice_id' => $invoiceId,
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to process PayPal payment. Please try again.');
        }
    }

    /**
     * Process PayPal payment (create order)
     */
    public function process(Request $request, string $subdomain, $invoiceId)
    {
        try {
            $invoice = Invoice::query()
                ->where('id', $invoiceId)
                ->where('tenant_id', tenant_id())
                ->where('status', Invoice::STATUS_NEW)
                ->firstOrFail();

            // Get available credit balance (like Stripe implementation)
            $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
            $remainingCredit = 0;
            if ($balance && $balance->balance > 0) {
                $remainingCredit = $balance->balance;
            }

            // Calculate amount after applying credit and coupon discount
            $total = $invoice->total();
            $creditApplied = $remainingCredit > 0 ? min($remainingCredit, $total) : 0;
            $finalAmount = $invoice->finalPayableAmount($remainingCredit);

            // Minimum amount validation (1 USD minimum like most payment processors)
            $minimumAmount = 1.00;
            if ($finalAmount > 0 && $finalAmount < $minimumAmount) {
                return response()->json([
                    'success' => false,
                    'error' => 'Minimum payment amount is $'.number_format($minimumAmount, 2).' USD. Your amount after credit: $'.number_format($finalAmount, 2),
                ], 400);
            }

            // If amount is zero after credit, handle as free payment
            if ($finalAmount <= 0) {
                // Deduct the credit used
                if ($creditApplied > 0) {
                    TenantCreditBalance::deductCredit($invoice->tenant_id, $creditApplied, 'Credit used for invoice payment', $invoice->id);
                }

                // Mark invoice as paid with credit
                $invoice->bypassPayment();

                payment_log('Invoice paid entirely with credit', 'info', [
                    'invoice_id' => $invoice->id,
                    'credit_applied' => $creditApplied,
                    'tenant_id' => tenant_id(),
                ]);

                return response()->json([
                    'success' => true,
                    'paid_with_credit' => true,
                    'redirect' => tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]),
                ]);
            }

            $provider = $this->getPayPalClient();

            // Create order data with final amount
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $invoice->currency->code ?? 'USD',
                            'value' => number_format($finalAmount, 2, '.', ''),
                        ],
                        'description' => "Payment for Invoice #{$invoice->id}",
                        'invoice_id' => (string) $invoice->id,
                    ],
                ],
                'application_context' => [
                    'return_url' => tenant_route('tenant.payment.paypal.capture', ['invoice' => $invoice->id]),
                    'cancel_url' => tenant_route('tenant.invoices.show', ['id' => $invoice->id]),
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ];

            $response = $provider->createOrder($orderData);

            if (isset($response['id'])) {
                // Note: Credit will be deducted only after successful payment capture

                // Create pending transaction for tracking
                $transaction = $invoice->createPendingTransaction($this->gateway, $invoice->tenant_id);
                $transaction->update([
                    'idempotency_key' => $response['id'], // Use PayPal order ID as idempotency key
                    'metadata' => [
                        'paypal_order_id' => $response['id'],
                        'paypal_status' => $response['status'] ?? 'CREATED',
                        'created_at' => now()->toISOString(),
                        'charge_type' => 'manual',
                        'original_amount' => $total,
                        'credit_applied' => $creditApplied,
                        'final_amount' => $finalAmount,
                    ],
                ]);

                return response()->json([
                    'success' => true,
                    'id' => $response['id'],
                ]);
            }

            payment_log('PayPal order creation failed', 'error', ['response' => $response]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create PayPal order',
            ], 400);

        } catch (\Exception $e) {
            payment_log('PayPal process error', 'error', [
                'invoice_id' => $invoiceId,
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment processing failed',
            ], 500);
        }
    }

    /**
     * Capture PayPal payment
     */
    public function capture(Request $request, string $subdomain, $invoiceId)
    {
        try {
            $invoice = Invoice::query()
                ->where('id', $invoiceId)
                ->where('tenant_id', tenant_id())
                ->where('status', Invoice::STATUS_NEW)
                ->firstOrFail();

            $orderId = $request->get('token');
            if (! $orderId) {
                throw new \Exception('Missing PayPal order ID');
            }

            $provider = $this->getPayPalClient();
            $response = $provider->capturePaymentOrder($orderId);

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                // Use database transaction for consistency
                DB::transaction(function () use ($invoice, $orderId, $response) {
                    // Find existing transaction by PayPal order ID or create new one
                    $transaction = Transaction::where('idempotency_key', $orderId)
                        ->where('invoice_id', $invoice->id)
                        ->first();

                    if (! $transaction) {
                        // Create new transaction
                        $transaction = $invoice->createPendingTransaction($this->gateway, $invoice->tenant_id);
                        $transaction->update([
                            'idempotency_key' => $orderId,
                        ]);
                    }

                    // Handle credit deduction after successful payment capture
                    $remainingCredit = 0;
                    try {
                        $balance = TenantCreditBalance::getOrCreateBalance($invoice->tenant_id, $invoice->currency_id);
                        if ($balance && $balance->balance > 0) {
                            $total = $invoice->total();
                            $remainingCredit = min($balance->balance, $total);

                            // Deduct credit after successful payment
                            if ($remainingCredit > 0) {
                                TenantCreditBalance::deductCredit(
                                    $invoice->tenant_id,
                                    $remainingCredit,
                                    'PayPal Payment Used Credit',
                                    $invoice->id
                                );

                                payment_log('PayPal: Credit deducted after successful payment', 'info', [
                                    'tenant_id' => $invoice->tenant_id,
                                    'amount' => $remainingCredit,
                                    'invoice_id' => $invoice->id,
                                    'paypal_order_id' => $orderId,
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        payment_log('PayPal: Failed to process credit during capture', 'warning', [
                            'error' => $e->getMessage(),
                            'invoice_id' => $invoice->id,
                        ]);
                        // Continue with zero credit
                        $remainingCredit = 0;
                    }

                    // Update transaction with PayPal payment details
                    $transaction->update([
                        'status' => 'success',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'paypal_order_id' => $orderId,
                            'paypal_capture_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                            'paypal_status' => $response['status'] ?? 'unknown',
                            'amount_received' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0,
                            'currency' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? 'USD',
                            'captured_at' => now()->toISOString(),
                            'charge_type' => 'manual',
                            'credit_applied' => $remainingCredit,
                            'credit_processed' => $remainingCredit > 0,
                        ]),
                    ]);

                    // Mark invoice as paid using the proper transaction result
                    $invoice->handleTransactionResult($transaction, new \App\Services\Billing\TransactionResult(
                        \App\Services\Billing\TransactionResult::RESULT_DONE,
                        t('payment_successful')
                    ));
                });

                session()->flash('notification', [
                    'type' => 'success',
                    'message' => t('payment_successful'),
                ]);

                return redirect()->to(tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]));
            }

            payment_log('PayPal capture failed', 'error', ['response' => $response]);

            session()->flash('notification', [
                'type' => 'error',
                'message' => t('payment_failed'),
            ]);

            return redirect()->to(tenant_route('tenant.invoices.show', ['id' => $invoice->id]));

        } catch (\Exception $e) {
            payment_log('PayPal capture error', 'error', [
                'invoice_id' => $invoiceId,
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            session()->flash('notification', [
                'type' => 'error',
                'message' => t('payment_error_occurred'),
            ]);

            return redirect()->to(tenant_route('tenant.invoices.show', ['id' => $invoice->id]));
        }
    }

    /**
     * Verify PayPal subscription status using Laravel PayPal package helper
     */
    public function verifySubscriptionStatus($subscriptionId)
    {
        try {
            $provider = $this->getPayPalClient();

            // Use the package's helper method to show subscription details
            $subscription = $provider->showSubscriptionDetails($subscriptionId);

            if (isset($subscription['error']) || ! isset($subscription['id'])) {
                Log::error('PayPal subscription verification failed', $subscription);

                return null;
            }

            return [
                'id' => $subscription['id'],
                'status' => $subscription['status'],
                'billing_info' => $subscription['billing_info'] ?? null,
                'start_time' => $subscription['start_time'] ?? null,
                'next_billing_time' => $subscription['billing_info']['next_billing_time'] ?? null,
                'plan_id' => $subscription['plan_id'] ?? null,
                'subscriber' => $subscription['subscriber'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('PayPal subscription verification error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Cancel PayPal subscription using Laravel PayPal package helper
     */
    public function cancelSubscription($subscriptionId, $reason = 'User requested cancellation')
    {
        try {
            $provider = $this->getPayPalClient();

            // Use the package's helper method to cancel subscription
            $response = $provider->cancelSubscription($subscriptionId, $reason);

            if (isset($response['error'])) {
                Log::error('PayPal subscription cancellation failed', $response);

                return false;
            }

            Log::info('PayPal subscription cancelled', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('PayPal subscription cancellation error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Suspend PayPal subscription using Laravel PayPal package helper
     */
    public function suspendSubscription($subscriptionId, $reason = 'Administrative suspension')
    {
        try {
            $provider = $this->getPayPalClient();

            // Use the package's helper method to suspend subscription
            $response = $provider->suspendSubscription($subscriptionId, $reason);

            if (isset($response['error'])) {
                Log::error('PayPal subscription suspension failed', $response);

                return false;
            }

            Log::info('PayPal subscription suspended', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('PayPal subscription suspension error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Activate suspended PayPal subscription using Laravel PayPal package helper
     */
    public function activateSubscription($subscriptionId, $reason = 'Reactivation requested')
    {
        try {
            $provider = $this->getPayPalClient();

            // Use the package's helper method to activate subscription
            $response = $provider->activateSubscription($subscriptionId, $reason);

            if (isset($response['error'])) {
                Log::error('PayPal subscription activation failed', $response);

                return false;
            }

            Log::info('PayPal subscription activated', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('PayPal subscription activation error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Handle subscription success callback
     */
    public function subscriptionSuccess(Request $request)
    {
        try {
            $subscriptionId = $request->query('subscription_id');
            $tenantId = $request->query('tenant_id');

            if (! $subscriptionId || ! $tenantId) {
                return redirect()->route('home')->with('error', 'Invalid subscription parameters');
            }

            // Find the local subscription
            $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $subscription) {
                return redirect()->route('home')->with('error', 'Subscription not found');
            }

            // Verify subscription with PayPal using helper method
            $subscriptionDetails = $this->verifySubscriptionStatus($subscriptionId);

            if ($subscriptionDetails && isset($subscriptionDetails['status']) && $subscriptionDetails['status'] === 'ACTIVE') {
                // Update subscription status
                $subscription->update([
                    'status' => 'active',
                    'trial_ends_at' => null,
                    'current_period_start' => Carbon::now(),
                ]);

                Log::info('PayPal subscription activated via success callback', [
                    'subscription_id' => $subscriptionId,
                    'tenant_id' => $tenantId,
                    'paypal_status' => $subscriptionDetails['status'],
                ]);

                return redirect()->route('tenant.dashboard')->with('success', 'Subscription activated successfully!');
            }

            return redirect()->route('home')->with('error', 'Subscription activation failed');

        } catch (\Exception $e) {
            Log::error('PayPal subscription success error: '.$e->getMessage());

            return redirect()->route('home')->with('error', 'An error occurred during subscription activation');
        }
    }

    /**
     * Handle subscription cancellation callback
     */
    public function subscriptionCancel(Request $request)
    {
        $tenantId = $request->query('tenant_id');

        payment_log('PayPal subscription cancelled by user', 'info', [
            'tenant_id' => $tenantId,
        ]);

        return redirect()->route('home')->with('info', 'Subscription setup was cancelled');
    }

    /**
     * Enhanced webhook handler for PayPal events
     */
    public function handleWebhook(Request $request)
    {
        try {
            if (! $this->verifyWebhookSignature($request)) {
                payment_log('Invalid PayPal webhook signature', 'error');

                return response('Invalid signature', 400);
            }

            $payload = $request->all();
            $eventType = $payload['event_type'] ?? 'unknown';

            payment_log('Received PayPal webhook', 'info', [
                'event_type' => $eventType,
                'resource_id' => $payload['resource']['id'] ?? 'unknown',
            ]);

            switch ($eventType) {
                // One-time payment events (subscription renewals handled by your application)
                case 'CHECKOUT.ORDER.APPROVED':
                    $this->handleOrderApproved($payload['resource']);
                    break;

                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handleCaptureCompleted($payload['resource']);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.FAILED':
                    $this->handleCaptureFailed($payload['resource']);
                    break;

                    // Legacy subscription events (for existing subscriptions only)
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                case 'BILLING.SUBSCRIPTION.RENEWED':
                case 'PAYMENT.SALE.COMPLETED':
                    payment_log('Legacy PayPal subscription event received (no action taken)', 'info', [
                        'event_type' => $eventType,
                        'note' => 'Subscription billing now handled by application like Stripe',
                    ]);
                    break;

                default:
                    payment_log('Unhandled PayPal webhook event', 'info', ['event_type' => $eventType]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            payment_log('PayPal webhook error', 'error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response('Error processing webhook', 500);
        }
    }

    /**
     * Handle subscription activated webhook
     *
     * @deprecated No longer used - subscription billing now handled by application like Stripe
     */
    private function handleSubscriptionActivated($resource)
    {
        $subscriptionId = $resource['id'];

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'current_period_start' => Carbon::now(),
            ]);

            Log::info('PayPal subscription activated via webhook', [
                'subscription_id' => $subscriptionId,
                'local_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Handle subscription cancelled webhook
     *
     * @deprecated No longer used - subscription billing now handled by application like Stripe
     */
    private function handleSubscriptionCancelled($resource)
    {
        $subscriptionId = $resource['id'];

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'ends_at' => Carbon::now(),
            ]);

            Log::info('PayPal subscription cancelled via webhook', [
                'subscription_id' => $subscriptionId,
                'local_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Handle subscription suspended webhook
     *
     * @deprecated No longer used - subscription billing now handled by application like Stripe
     */
    private function handleSubscriptionSuspended($resource)
    {
        $subscriptionId = $resource['id'];

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'past_due',
            ]);

            Log::info('PayPal subscription suspended via webhook', [
                'subscription_id' => $subscriptionId,
                'local_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Handle payment failed webhook
     *
     * @deprecated No longer used - subscription billing now handled by application like Stripe
     */
    private function handlePaymentFailed($resource)
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? $resource['id'];

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'past_due',
            ]);

            Log::warning('PayPal payment failed via webhook', [
                'subscription_id' => $subscriptionId,
                'local_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Handle payment completed webhook
     *
     * @deprecated No longer used - subscription billing now handled by application like Stripe
     */
    private function handlePaymentCompleted($resource)
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? $resource['subscription_id'] ?? null;

        if (! $subscriptionId) {
            Log::warning('PayPal payment completed webhook missing subscription ID');

            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $plan = $subscription->plan;

            // Calculate next billing period
            $nextBillingDate = $plan->billing_period === 'yearly'
                ? Carbon::now()->addYears($plan->interval ?? 1)
                : Carbon::now()->addMonths($plan->interval ?? 1);

            $subscription->update([
                'status' => 'active',
                'current_period_start' => Carbon::now(),
                'current_period_end' => $nextBillingDate,
            ]);

            Log::info('PayPal payment completed via webhook', [
                'subscription_id' => $subscriptionId,
                'local_id' => $subscription->id,
                'next_billing' => $nextBillingDate,
            ]);
        }
    }

    /**
     * Handle order approved webhook (when user approves payment on PayPal)
     */
    private function handleOrderApproved(array $resource): void
    {
        $orderId = $resource['id'] ?? null;

        if (! $orderId) {
            payment_log('PayPal order approved webhook missing order ID', 'warning');

            return;
        }

        // Find the pending transaction with this order ID
        $transaction = Transaction::where('idempotency_key', $orderId)
            ->where('status', 'pending')
            ->first();

        if ($transaction) {
            payment_log('PayPal order approved via webhook', 'info', [
                'order_id' => $orderId,
                'transaction_id' => $transaction->id,
            ]);

            // Note: Don't mark as success yet - wait for capture completion
            // Just log that the order was approved
        }
    }

    /**
     * Handle payment capture completed webhook (final success)
     */
    private function handleCaptureCompleted(array $resource): void
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ??
                   $resource['invoice_id'] ?? null;
        $captureId = $resource['id'] ?? null;

        if (! $orderId && ! $captureId) {
            Log::warning('PayPal capture completed webhook missing order/capture ID');

            return;
        }

        // Find the pending transaction
        $transaction = Transaction::where('idempotency_key', $orderId)
            ->where('status', 'pending')
            ->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'success',
                'metadata' => json_encode(array_merge(
                    json_decode($transaction->metadata ?? '{}', true),
                    [
                        'capture_id' => $captureId,
                        'captured_at' => now()->toISOString(),
                        'paypal_status' => 'COMPLETED',
                    ]
                )),
            ]);

            // Mark invoice as paid
            $transaction->invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            payment_log('PayPal payment capture completed via webhook', 'info', [
                'order_id' => $orderId,
                'capture_id' => $captureId,
                'transaction_id' => $transaction->id,
                'invoice_id' => $transaction->invoice_id,
            ]);
        }
    }

    /**
     * Handle payment capture failed webhook
     */
    private function handleCaptureFailed(array $resource): void
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ??
                   $resource['invoice_id'] ?? null;
        $captureId = $resource['id'] ?? null;
        $reason = $resource['status_details']['reason'] ?? 'Unknown';

        if (! $orderId && ! $captureId) {
            Log::warning('PayPal capture failed webhook missing order/capture ID');

            return;
        }

        // Find the pending transaction
        $transaction = Transaction::where('idempotency_key', $orderId)
            ->where('status', 'pending')
            ->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'error' => "PayPal capture failed: {$reason}",
                'metadata' => json_encode(array_merge(
                    json_decode($transaction->metadata ?? '{}', true),
                    [
                        'capture_id' => $captureId,
                        'failed_at' => now()->toISOString(),
                        'failure_reason' => $reason,
                        'paypal_status' => 'FAILED',
                    ]
                )),
            ]);

            Log::warning('PayPal payment capture failed via webhook', [
                'order_id' => $orderId,
                'capture_id' => $captureId,
                'reason' => $reason,
                'transaction_id' => $transaction->id,
                'invoice_id' => $transaction->invoice_id,
            ]);
        }
    }

    /**
     * Verify PayPal webhook signature using package methods
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        try {
            $settings = $this->getPayPalSettings();
            $webhookId = $settings['payment.paypal_webhook_id'] ?? '';

            // If no webhook ID is configured, skip verification in development
            if (empty($webhookId)) {
                Log::warning('PayPal webhook ID not configured, skipping signature verification');

                return app()->environment('local', 'development');
            }

            $provider = $this->getPayPalClient();

            // Use PayPal package's webhook verification
            $verificationData = [
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_id' => $request->header('PAYPAL-CERT-ID'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $webhookId,
                'webhook_event' => $request->getContent(),
            ];

            $response = $provider->verifyWebHook($verificationData);

            return $response['verification_status'] === 'SUCCESS';

        } catch (\Exception $e) {
            Log::error('PayPal webhook signature verification failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get the approval URL from PayPal response links
     */
    private function getApprovalUrl(array $links): ?string
    {
        foreach ($links as $link) {
            if (isset($link['rel']) && $link['rel'] === 'approve') {
                return $link['href'];
            }
        }

        return null;
    }
}
