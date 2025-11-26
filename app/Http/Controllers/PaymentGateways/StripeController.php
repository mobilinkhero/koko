<?php

namespace App\Http\Controllers\PaymentGateways;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeController extends Controller
{
    /**
     * Show the checkout page for an invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $subdomain
     * @param  mixed  $invoiceId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    protected $gateway;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->gateway = app('billing.manager')->gateway('stripe');
    }

    /**
     * Get Stripe settings from database
     */
    private function getStripeSettings(): array
    {
        return get_batch_settings([
            'payment.stripe_enabled',
            'payment.stripe_key',
            'payment.stripe_secret',
            'payment.stripe_webhook_secret', // This should be added to your settings
        ]);
    }

    /**
     * Get the secret key from database settings
     */
    private function getSecretKey(): ?string
    {
        $settings = $this->getStripeSettings();

        return $settings['payment.stripe_secret'] ?? null;
    }

    /**
     * Get the publishable key from database settings
     */
    private function getPublishableKey(): ?string
    {
        $settings = $this->getStripeSettings();

        return $settings['payment.stripe_key'] ?? null;
    }

    /**
     * Show the checkout page for an invoice.
     *
     * @param  string  $invoiceUid
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request, string $subdomain, $invoiceId)
    {
        // Check if Stripe is enabled
        $settings = $this->getStripeSettings();
        if (empty($settings['payment.stripe_enabled'])) {
            return back()->with('error', 'Stripe payment is not available.');
        }

        $invoice = Invoice::where('id', $invoiceId)
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

        try {

            $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
            $remainingCredit = 0;
            if ($balance->balance != 0) {
                $remainingCredit = $balance->balance;
            }

            // Create payment intent
            $paymentIntent = $this->gateway->createPaymentIntent($invoice, $remainingCredit);

            return view('payment-gateways.stripe.checkout', [
                'invoice' => $invoice,
                'clientSecret' => $paymentIntent['client_secret'],
                'publishableKey' => $this->getPublishableKey(),
                'remainingCredit' => $remainingCredit,
            ]);
        } catch (\Exception $e) {
            payment_log('Stripe checkout error', 'error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Handle the Stripe payment confirmation.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'invoice_id' => 'required|string',
        ]);

        // Check if Stripe is enabled
        $settings = $this->getStripeSettings();
        if (empty($settings['payment.stripe_enabled'])) {
            return response()->json([
                'success' => false,
                'message' => t('stripe_payment_not_available'),
            ], 400);
        }

        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        try {
            // Set Stripe API key from database settings
            Stripe::setApiKey($this->getSecretKey());

            $user = getUserByTenantId(tenant_id());
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
            $tenant = Tenant::find(tenant_id());

            // Get or create Stripe customer
            $stripeCustomer = $this->gateway->getStripeCustomer(
                $tenant->stripe_customer_id,
                [
                    'email' => $user->email,
                    'name' => $user->firstname.' '.$user->lastname,
                ]
            );

            $tenant->stripe_customer_id = $stripeCustomer->id;
            $tenant->save();

            if ($paymentIntent->status === 'succeeded') {
                return response()->json([
                    'success' => true,
                    'redirect' => tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => t('payment_not_successful'),
            ], 400);
        } catch (\Exception $e) {
            payment_log('Stripe confirmation error', 'error', [
                'invoice_id' => $request->invoice_id,
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the auto billing data update page.
     *
     * @return \Illuminate\View\View
     */
    public function autoBillingData(Request $request)
    {
        $returnUrl = $request->input('return_url');

        try {
            $user = getUserByTenantId(tenant_id());
            $tenant = Tenant::find(tenant_id());
            // Get or create Stripe customer
            $stripeCustomer = $this->gateway->getStripeCustomer(
                $tenant->stripe_customer_id,
                [
                    'email' => $user->email,
                    'name' => $user->firstname.' '.$user->lastname,
                ]
            );

            return view('payment-gateways.stripe.setup', [
                'publishableKey' => $this->gateway->getPublishableKey(),
                'customerId' => $stripeCustomer->id,
                'returnUrl' => $returnUrl,
            ]);
        } catch (\Exception $e) {
            payment_log('Stripe setup error: ', 'error');

            return back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Validate webhook signature from Stripe
     *
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    private function validateWebhookSignature(Request $request): bool
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // Get webhook secret from settings
        $settings = $this->getStripeSettings();
        $webhookSecret = $settings['payment.stripe_webhook_secret'] ?? null;

        if (empty($webhookSecret)) {
            payment_log('Stripe webhook secret not configured', 'error', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return false;
        }

        try {
            // This will throw an exception if verification fails
            \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);

            return true;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            payment_log('Stripe webhook signature verification failed', 'error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);

            return false;
        }
    }

    /**
     * Enhanced webhook handler with better response handling
     */
    public function webhook(Request $request)
    {
        if (! $this->validateWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = $this->gateway->getWebhookSecret();

        try {
            // Set Stripe API key
            Stripe::setApiKey($this->gateway->getSecretKey());

            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            // Handle the event
            $handled = false;
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $handled = $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $handled = $this->handlePaymentIntentFailed($event->data->object);
                    break;

                case 'payment_method.attached':
                    $handled = $this->handlePaymentMethodAttached($event->data->object);
                    break;

                case 'customer.updated':
                    $handled = $this->handleCustomerUpdated($event->data->object);
                    break;

                default:
                    $handled = true; // Don't return error for unhandled events
            }

            if ($handled) {
                return response()->json([
                    'status' => 'success',
                    'event_id' => $event->id,
                    'message' => t('webhook_processed_successfully'),
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'event_id' => $event->id,
                    'message' => t('failed_to_process_webhook'),
                ], 500);
            }
        } catch (\UnexpectedValueException $e) {
            payment_log('Stripe webhook invalid payload', 'error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            payment_log('Stripe webhook signature verification failed', 'error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            payment_log('Stripe webhook processing error', 'error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Helper method to set default payment method in Stripe
     */
    private function setDefaultPaymentMethodInStripe(string $customerId, string $paymentMethodId): bool
    {
        try {
            $stripe = new StripeClient($this->gateway->getSecretKey());

            $res = $stripe->customers->update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            payment_log('Failed to set default payment method in Stripe', 'error', [
                'customer_id' => $customerId,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enhanced storePaymentMethod with better data handling
     */
    protected function storePaymentMethod($data, Tenant $tenant): PaymentMethod
    {
        // Get the payment method object from the data
        $paymentMethod = null;
        $stripeCustomerId = null;

        // Handle different input formats
        if (is_string($data['payment_method'])) {
            // If it's just the ID, retrieve the full payment method
            $stripe = new StripeClient($this->gateway->getSecretKey());
            $paymentMethod = $stripe->paymentMethods->retrieve($data['payment_method']);
        } else {
            $paymentMethod = $data['payment_method'];
        }

        $stripeCustomerId = $data['tenant']['id'];

        // Check if we already have this payment method
        $existingMethod = PaymentMethod::where('tenant_id', $tenant->id)
            ->where('payment_method_id', $paymentMethod->id)
            ->first();

        if ($existingMethod) {
            // If it exists but not default, and this is the first method, make it default
            if (! $existingMethod->is_default) {
                $totalMethods = PaymentMethod::where('tenant_id', $tenant->id)->count();
                if ($totalMethods == 1) {
                    $existingMethod->is_default = true;
                    $existingMethod->save();

                    // Set as default in Stripe
                    $this->setDefaultPaymentMethodInStripe($stripeCustomerId, $paymentMethod->id);
                }
            }

            return $existingMethod;
        }

        // Check if this should be the default (first payment method)
        $isFirstMethod = PaymentMethod::where('tenant_id', $tenant->id)->count() === 0;

        PaymentMethod::where('tenant_id', $tenant->id)->update([
            'is_default' => 0,
        ]);

        // Create new payment method
        $paymentMethodModel = PaymentMethod::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'payment_method_id' => $paymentMethod->id,
            ],
            [
                'type' => 'stripe',
                'is_default' => 1,
                'metadata' => [
                    'stripe_customer_id' => $stripeCustomerId,
                ],
            ]
        );

        // CRITICAL: Set as default in Stripe if this is the first method
        $this->setDefaultPaymentMethodInStripe($stripeCustomerId, $paymentMethod->id);

        return $paymentMethodModel;
    }

    /**
     * Handle payment intent succeeded event.
     * Handle a successful payment intent webhook
     *
     * @param  \Stripe\StripeObject  $paymentIntent
     */
    protected function handlePaymentIntentSucceeded($paymentIntent): bool
    {
        try {
            if (empty($paymentIntent->metadata->invoice_id)) {
                return true;
            }

            $invoice = Invoice::find($paymentIntent->metadata->invoice_id);
            if (! $invoice) {
                return true;
            }

            if ($invoice->isPaid()) {
                return true;
            }

            // Use database transaction for consistency
            DB::transaction(function () use ($invoice, $paymentIntent) {
                // Find existing transaction by Stripe payment intent ID (our idempotency key)
                $transaction = Transaction::where('idempotency_key', $paymentIntent->id)
                    ->where('invoice_id', $invoice->id)
                    ->first();

                if (! $transaction) {
                    // If no transaction found, create new one (shouldn't happen in auto charge)
                    $transaction = $invoice->createPendingTransaction($this->gateway, $invoice->tenant_id);
                    $transaction->update([
                        'idempotency_key' => $paymentIntent->id,
                    ]);
                }

                // Handle credit deduction after successful payment
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
                                'Stripe Payment Used Credit',
                                $invoice->id
                            );

                            payment_log('Stripe: Credit deducted after successful payment', 'info', [
                                'tenant_id' => $invoice->tenant_id,
                                'amount' => $remainingCredit,
                                'invoice_id' => $invoice->id,
                                'payment_intent_id' => $paymentIntent->id,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    payment_log('Stripe: Failed to process credit during webhook', 'warning', [
                        'error' => $e->getMessage(),
                        'invoice_id' => $invoice->id,
                    ]);
                    // Continue with zero credit
                    $remainingCredit = 0;
                }

                // Update transaction with final payment details
                $transaction->update([
                    'status' => 'success',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'stripe_payment_intent_id' => $paymentIntent->id ?? null,
                        'charge_type' => isset($paymentIntent->metadata) ? ($paymentIntent->metadata->charge_type ?? 'manual') : 'manual',
                        'webhook_processed_at' => now()->toISOString(),
                        'amount_received' => isset($paymentIntent->amount_received) ? ($paymentIntent->amount_received / 100) : 0,
                        'stripe_status' => $paymentIntent->status ?? 'unknown',
                        'payment_method_used' => $paymentIntent->payment_method ?? null,
                        'credit_applied' => $remainingCredit,
                        'credit_processed' => $remainingCredit > 0,
                    ]),
                ]);

                // Store payment method if this was an auto charge
                if (isset($paymentIntent->payment_method) && $paymentIntent->payment_method && isset($paymentIntent->metadata) && ! empty($paymentIntent->metadata->charge_type) && $paymentIntent->metadata->charge_type === 'auto_billing') {
                    $tenant = Tenant::find($invoice->tenant_id);
                    if ($tenant) {
                        try {
                            $this->storePaymentMethod([
                                'payment_method' => $paymentIntent->payment_method,
                                'tenant' => ['id' => isset($paymentIntent->customer) ? $paymentIntent->customer : null],
                            ], $tenant);
                        } catch (\Exception $e) {
                            payment_log('Could not store payment method from auto charge webhook', 'warning', [
                                'payment_method_id' => $paymentIntent->payment_method,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                // Mark invoice as paid
                $invoice->handleTransactionResult($transaction, new \App\Services\Billing\TransactionResult(
                    \App\Services\Billing\TransactionResult::RESULT_DONE,
                    t('payment_processed_via_webhook')
                ));
            });

            return true;
        } catch (\Exception $e) {
            payment_log('Failed to handle payment intent succeeded', 'error', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Handle payment intent failed event.
     * Handle a failed payment intent webhook
     *
     * @param  \Stripe\StripeObject  $paymentIntent
     */
    protected function handlePaymentIntentFailed($paymentIntent): bool
    {
        try {
            if (empty($paymentIntent->metadata->invoice_id)) {
                return true;
            }

            $invoice = Invoice::find(isset($paymentIntent->metadata) ? ($paymentIntent->metadata->invoice_id ?? null) : null);
            if (! $invoice || $invoice->isPaid()) {
                return true;
            }

            $errorMessage = isset($paymentIntent->last_payment_error) && isset($paymentIntent->last_payment_error->message)
                ? $paymentIntent->last_payment_error->message
                : 'Payment failed without specific error';

            payment_log('Payment failed for invoice', 'warning', [
                'payment_intent_id' => $paymentIntent->id ?? 'unknown',
                'invoice_id' => $invoice->id,
                'error' => $errorMessage,
                'decline_code' => isset($paymentIntent->last_payment_error) ? ($paymentIntent->last_payment_error->decline_code ?? null) : null,
            ]);

            // Optionally create failed transaction record
            $transaction = $invoice->createPendingTransaction($this->gateway, $invoice->tenant_id);
            $transaction->update([
                'status' => 'failed',
                'metadata' => [
                    'failure_reason' => $errorMessage,
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            payment_log('Failed to handle payment intent failed', 'error', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle payment method attached event
     */
    protected function handlePaymentMethodAttached($paymentMethod): bool
    {
        try {
            // Find tenant by stripe customer ID
            $tenant = Tenant::where('stripe_customer_id', $paymentMethod->customer)->first();
            if (! $tenant) {
                payment_log('Payment method attached to unknown customer', 'warning', [
                    'customer_id' => $paymentMethod->customer,
                ]);

                return true;
            }

            // Store or update payment method
            $this->storePaymentMethod([
                'payment_method' => $paymentMethod,
                'tenant' => ['id' => $paymentMethod->customer],
            ], $tenant);

            return true;
        } catch (\Exception $e) {
            payment_log('Failed to handle payment method attached', 'error', [
                'payment_method_id' => $paymentMethod->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle customer updated event
     */
    protected function handleCustomerUpdated($customer): bool
    {
        try {
            // Find tenant and update default payment method if changed
            $tenant = Tenant::where('stripe_customer_id', $customer->id)->first();
            if ($tenant && isset($customer->invoice_settings->default_payment_method)) {

                // Update local payment method records
                PaymentMethod::where('tenant_id', $tenant->id)->update(['is_default' => false]);
                PaymentMethod::where('tenant_id', $tenant->id)
                    ->where('payment_method_id', $customer->invoice_settings->default_payment_method)
                    ->update(['is_default' => true]);
            }

            return true;
        } catch (\Exception $e) {
            payment_log('Failed to handle customer updated', 'error', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
