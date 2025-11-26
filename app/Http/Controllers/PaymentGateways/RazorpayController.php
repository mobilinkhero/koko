<?php

namespace App\Http\Controllers\PaymentGateways;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class RazorpayController extends Controller
{
    /**
     * Razorpay payment gateway instance
     *
     * @var \App\Services\PaymentGateways\RazorpayPaymentGateway
     */
    protected $gateway;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->gateway = app('billing.manager')->gateway('razorpay');
    }

    /**
     * Get Razorpay settings from database
     */
    private function getRazorpaySettings(): array
    {
        return get_batch_settings([
            'payment.razorpay_enabled',
            'payment.razorpay_key_id',
            'payment.razorpay_key_secret',
            'payment.razorpay_webhook_secret',
        ]);
    }

    /**
     * Get the key secret from database settings
     */
    private function getKeySecret(): ?string
    {
        $settings = $this->getRazorpaySettings();

        return $settings['payment.razorpay_key_secret'] ?? null;
    }

    /**
     * Get the key ID from database settings
     */
    private function getKeyId(): ?string
    {
        $settings = $this->getRazorpaySettings();

        return $settings['payment.razorpay_key_id'] ?? null;
    }

    /**
     * Show the checkout page for an invoice.
     *
     * @param  string  $invoiceId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request, string $subdomain, $invoiceId)
    {
        // Check if Razorpay is enabled
        $settings = $this->getRazorpaySettings();
        if (empty($settings['payment.razorpay_enabled'])) {
            return back()->with('error', 'Razorpay payment is not available.');
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
            // Get available credit
            $remainingCredit = 0;
            try {
                $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
                if ($balance && $balance->balance > 0) {
                    $remainingCredit = $balance->balance;
                }
            } catch (\Exception $creditException) {
                // Log the credit balance error but continue with payment process
                payment_log('Credit balance check failed, continuing without credit', 'warning', [
                    'error' => $creditException->getMessage(),
                    'invoice_id' => $invoiceId,
                ]);
                // Continue with zero credit
                $remainingCredit = 0;
            }

            // Create payment order with the credit applied
            $order = $this->gateway->createPaymentOrder($invoice, $remainingCredit);

            // Get the total amount including tax
            $total = $invoice->total();

            return view('payment-gateways.razorpay.checkout', [
                'invoice' => $invoice,
                'order' => $order,
                'keyId' => $this->getKeyId(),
                'remainingCredit' => $remainingCredit,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            payment_log('Razorpay checkout error', 'error', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Handle the Razorpay payment confirmation.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
    {
        // Different validation rules based on whether it's an authentication request
        if ($request->has('is_authentication') && $request->is_authentication) {
            $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'invoice_id' => 'required|string',
                'transaction_id' => 'required|string',
            ]);
        } else {
            $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'invoice_id' => 'required|string',
            ]);
        }

        // Check if Razorpay is enabled
        $settings = $this->getRazorpaySettings();
        if (empty($settings['payment.razorpay_enabled'])) {
            return response()->json([
                'success' => false,
                'message' => t('razorpay_payment_not_available'),
            ], 400);
        }

        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        try {
            // Verify payment signature
            $paymentId = $request->razorpay_payment_id;
            $orderId = $request->razorpay_order_id;
            $signature = $request->razorpay_signature;

            $generatedSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->getKeySecret());

            if (! hash_equals($generatedSignature, $signature)) {
                return response()->json([
                    'success' => false,
                    'message' => t('payment_verification_failed'),
                ], 400);
            }

            // Create transaction record
            $transaction = $invoice->createPendingTransaction($this->gateway, tenant_id());

            $transaction->update([
                'idempotency_key' => $paymentId,
                'metadata' => [
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_order_id' => $orderId,
                    'razorpay_signature' => $signature,
                    'verification_status' => 'verified',
                    'confirmed_at' => now()->toISOString(),
                ],
            ]);

            // Verify the transaction using the gateway
            $result = $this->gateway->verify($transaction);

            if ($result->isDone()) {
                PaymentMethod::where('tenant_id', tenant_id())->update(['is_default' => 0]);

                return response()->json([
                    'success' => true,
                    'redirect' => tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result->getMessage() ?? t('payment_not_successful'),
            ], 400);
        } catch (\Exception $e) {
            payment_log('Razorpay confirmation error', 'error', [
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
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function autoBillingData(Request $request)
    {
        $returnUrl = $request->input('return_url');

        try {
            $user = getUserByTenantId(tenant_id());
            $tenant = Tenant::find(tenant_id());

            return view('payment-gateways.razorpay.setup', [
                'keyId' => $this->gateway->getKeyId(),
                'tenantId' => $tenant->id,
                'userEmail' => $user->email,
                'userName' => $user->firstname.' '.$user->lastname,
                'returnUrl' => $returnUrl,
            ]);
        } catch (\Exception $e) {
            payment_log('Razorpay setup error', 'error', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Handle Razorpay webhooks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Handle Razorpay webhooks according to official documentation
     * https://razorpay.com/docs/webhooks/
     */
    public function webhook(Request $request)
    {
        try {
            // Start timing the webhook processing for performance monitoring
            $startTime = microtime(true);
            // Get webhook secret from settings
            $settings = $this->getRazorpaySettings();
            $webhookSecret = $settings['payment.razorpay_webhook_secret'] ?? null;

            if (empty($webhookSecret)) {
                return response()->json(['error' => 'Webhook secret not configured'], 400);
            }

            // Get the raw payload
            $payload = $request->getContent();
            $signature = $request->header('X-Razorpay-Signature');

            // Verify webhook signature according to Razorpay docs
            if (! $this->verifyRazorpayWebhookSignature($payload, $signature, $webhookSecret)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Parse the webhook payload
            $event = json_decode($payload, true);

            if (! $event || ! isset($event['event'])) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Handle different webhook events based on Razorpay documentation
            $handled = $this->processWebhookEvent($event);

            // Calculate processing time for performance monitoring
            $processingTime = round((microtime(true) - $startTime) * 1000);

            return response()->json(['status' => ($handled) ? 'success' : 'ignored']);

        } catch (\Exception $e) {

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify Razorpay webhook signature according to official docs
     * https://razorpay.com/docs/webhooks/validate-test/
     */
    protected function verifyRazorpayWebhookSignature(string $payload, ?string $signature, string $secret): bool
    {
        if (empty($signature)) {
            return false;
        }

        // Generate expected signature
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process webhook event based on event type
     */
    protected function processWebhookEvent(array $event): bool
    {
        $eventType = $event['event'];
        $eventId = $event['event_id'] ?? 'unknown';

        switch ($eventType) {
            // Payment events
            case 'payment.captured':
                return $this->handlePaymentCaptured($event['payload']['payment']['entity']);

            case 'payment.failed':
                return $this->handlePaymentFailed($event['payload']['payment']['entity']);

            case 'payment.authorized':
                return $this->handlePaymentAuthorized($event['payload']['payment']['entity']);

                // Order events
            case 'order.paid':
                return $this->handleOrderPaid($event['payload']['order']['entity']);

                // Refund events
            case 'refund.created':
                return $this->handleRefundCreated($event['payload']['refund']['entity']);

            case 'refund.processed':
                return $this->handleRefundProcessed($event['payload']['refund']['entity']);

                // Subscription events (if applicable)
            case 'subscription.charged':
                return $this->handleSubscriptionCharged($event['payload']['subscription']['entity'], $event['payload']['payment']['entity']);

            case 'subscription.completed':
                return $this->handleSubscriptionCompleted($event['payload']['subscription']['entity']);

            default:
                return false;
        }
    }

    /**
     * Handle payment captured webhook event
     * This is the main event that indicates successful payment
     */
    protected function handlePaymentCaptured(array $payment): bool
    {
        $paymentId = $payment['id'];
        $orderId = $payment['order_id'] ?? null;

        // Find transaction by payment ID or order ID
        $transaction = $this->findTransactionByPayment($paymentId, $orderId);

        if (! $transaction) {
            return false;
        }

        // Update transaction status to success
        $transactionMetadata = array_merge($transaction->metadata ?? [], [
            'razorpay_payment_id' => $paymentId,
            'captured_at' => now()->toISOString(),
            'razorpay_status' => 'captured',
            'webhook_processed' => true,
            'payment_method' => $payment['method'] ?? null,
            'bank' => $payment['bank'] ?? null,
            'captured_amount' => $payment['amount'] ?? null,
        ]);

        // Mark credit as processed if it was applied
        // Note: Credit deduction happens in createPaymentOrder, not here
        if (isset($transaction->metadata['credit_applied']) && $transaction->metadata['credit_applied'] > 0) {
            $transactionMetadata['credit_processed'] = true;
        }

        $transaction->update([
            'status' => 'success',
            'metadata' => $transactionMetadata,
        ]);

        // Mark invoice as paid if the transaction is successful
        $invoice = $transaction->invoice;
        if ($invoice && $invoice->status !== Invoice::STATUS_PAID) {
            // Get the typed invoice instance to ensure proper processing
            $typedInvoice = $invoice->mapType();

            // Use the appropriate invoice type for processing
            if ($typedInvoice->id === $invoice->id && get_class($typedInvoice) !== get_class($invoice)) {
                $typedInvoice->markAsPaid();
            } else {
                $invoice->markAsPaid();
            }

            payment_log('Invoice marked as paid successfully', 'info', [
                'invoice_id' => $invoice->id,
                'new_status' => $invoice->fresh()->status,
                'transaction_id' => $transaction->id,
            ]);
        }

        return true;
    }

    /**
     * Handle payment failed webhook event
     */
    protected function handlePaymentFailed(array $payment): bool
    {
        $paymentId = $payment['id'];
        $orderId = $payment['order_id'] ?? null;

        // Find transaction by payment ID or order ID
        $transaction = $this->findTransactionByPayment($paymentId, $orderId);

        if (! $transaction) {
            return false;
        }

        // Update transaction status to failed
        $transaction->update([
            'status' => 'failed',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'razorpay_payment_id' => $paymentId,
                'failed_at' => now()->toISOString(),
                'razorpay_status' => 'failed',
                'error_code' => $payment['error_code'] ?? null,
                'error_description' => $payment['error_description'] ?? 'Payment failed',
                'failure_reason' => $payment['error_reason'] ?? 'Unknown',
                'webhook_processed' => true,
            ]),
        ]);

        return true;
    }

    /**
     * Handle payment authorized webhook event
     * This occurs when payment is authorized but not yet captured
     */
    protected function handlePaymentAuthorized(array $payment): bool
    {
        $paymentId = $payment['id'];
        $orderId = $payment['order_id'] ?? null;

        // Find transaction by payment ID or order ID
        $transaction = $this->findTransactionByPayment($paymentId, $orderId);

        if (! $transaction) {
            payment_log('Transaction not found for authorized payment', 'warning', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
            ]);

            return false;
        }

        // Update transaction status to pending (authorized but not captured)
        $transaction->update([
            'status' => 'pending',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'razorpay_payment_id' => $paymentId,
                'authorized_at' => now()->toISOString(),
                'razorpay_status' => 'authorized',
                'webhook_processed' => true,
                'authorized_amount' => $payment['amount'] ?? null,
            ]),
        ]);

        return true;
    }

    /**
     * Handle order paid webhook event
     */
    protected function handleOrderPaid(array $order): bool
    {
        $orderId = $order['id'];

        // Find transaction by order ID
        $transaction = Transaction::where(function ($query) use ($orderId) {
            $query->where('metadata->razorpay_order_id', $orderId)
                ->orWhere('idempotency_key', $orderId);
        })->first();

        if (! $transaction) {
            return false;
        }

        // Update transaction with order details
        $transaction->update([
            'metadata' => array_merge($transaction->metadata ?? [], [
                'order_paid_at' => now()->toISOString(),
                'order_status' => $order['status'] ?? null,
                'amount_paid' => $order['amount_paid'] ?? null,
                'order_webhook_processed' => true,
            ]),
        ]);

        return true;
    }

    /**
     * Handle refund created webhook event
     */
    protected function handleRefundCreated(array $refund): bool
    {
        // Find the original transaction
        $paymentId = $refund['payment_id'] ?? null;
        if (! $paymentId) {
            return false;
        }

        $transaction = Transaction::where('metadata->razorpay_payment_id', $paymentId)->first();
        if (! $transaction) {
            return false;
        }

        // Calculate refund amount in base currency
        $refundAmountInCents = $refund['amount'] ?? 0;
        $refundAmount = $refundAmountInCents / 100; // Convert from paisa to rupees

        // Update transaction metadata with refund information
        $transaction->update([
            'metadata' => array_merge($transaction->metadata ?? [], [
                'refund_initiated_at' => now()->toISOString(),
                'razorpay_refund_id' => $refund['id'],
                'refund_amount' => $refundAmountInCents,
                'refund_amount_currency' => $refundAmount,
                'refund_status' => 'initiated',
                'refund_webhook_processed' => true,
            ]),
        ]);

        // Find the related invoice
        $invoice = $transaction->invoice;
        if ($invoice) {
            // Update invoice status to reflect partial or full refund
            $totalRefundAmount = $refundAmount;
            $existingRefunds = $transaction->metadata['total_refunded'] ?? 0;
            $newTotalRefunded = $existingRefunds + $refundAmount;

            // Update transaction with total refunded amount
            $transaction->update([
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'total_refunded' => $newTotalRefunded,
                ]),
            ]);

            // If fully refunded, update invoice status
            if ($newTotalRefunded >= ($transaction->amount / 100)) {
                $invoice->update(['status' => Invoice::STATUS_REFUNDED]);
            } else {
                $invoice->update(['status' => Invoice::STATUS_PARTIALLY_REFUNDED]);
            }

            // Add credit to tenant balance for the refund amount
            if ($invoice->tenant_id) {
                TenantCreditBalance::addCredit(
                    $invoice->tenant_id,
                    $invoice->currency_id,
                    $refundAmount,
                    "Refund for invoice #{$invoice->id} - Razorpay Refund ID: {$refund['id']}",
                    $invoice->id,
                    [
                        'source' => 'razorpay_refund',
                        'razorpay_refund_id' => $refund['id'],
                        'razorpay_payment_id' => $paymentId,
                        'transaction_id' => $transaction->id,
                        'refund_type' => $newTotalRefunded >= ($transaction->amount / 100) ? 'full' : 'partial',
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Handle refund processed webhook event
     */
    protected function handleRefundProcessed(array $refund): bool
    {
        // Find the original transaction
        $paymentId = $refund['payment_id'] ?? null;
        if (! $paymentId) {
            return false;
        }

        $transaction = Transaction::where('metadata->razorpay_payment_id', $paymentId)->first();
        if (! $transaction) {
            return false;
        }

        // Update transaction metadata with refund completion
        $transaction->update([
            'metadata' => array_merge($transaction->metadata ?? [], [
                'refund_processed_at' => now()->toISOString(),
                'refund_status' => $refund['status'] ?? 'processed',
                'refund_processed_webhook' => true,
            ]),
        ]);

        return true;
    }

    /**
     * Handle subscription charged webhook event
     * This handles recurring subscription payments via Razorpay Subscriptions API
     */
    protected function handleSubscriptionCharged(array $subscription, array $payment): bool
    {
        $razorpaySubscriptionId = $subscription['id'];
        $paymentId = $payment['id'];
        $amount = $payment['amount'] ?? 0;

        // Find subscription by Razorpay subscription ID stored in metadata
        $appSubscription = \App\Models\Subscription::where('metadata->razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if (! $appSubscription) {
            return false;
        }

        try {
            // Create a new renewal invoice for this subscription
            $renewalInvoice = \App\Models\Invoice\InvoiceRenewSubscription::create([
                'tenant_id' => $appSubscription->tenant_id,
                'subscription_id' => $appSubscription->id,
                'currency_id' => $appSubscription->plan->currency_id,
                'status' => Invoice::STATUS_NEW,
                'total' => $amount / 100, // Convert from paisa to rupees
                'subtotal' => $amount / 100,
                'due_date' => now()->addDays(7),
                'metadata' => [
                    'razorpay_subscription_id' => $razorpaySubscriptionId,
                    'razorpay_payment_id' => $paymentId,
                    'recurring_charge' => true,
                    'webhook_processed_at' => now()->toISOString(),
                ],
            ]);

            // Create transaction for this payment
            $transaction = $renewalInvoice->createPendingTransaction($this->gateway, $appSubscription->tenant_id);

            $transaction->update([
                'status' => 'success',
                'idempotency_key' => $paymentId,
                'metadata' => [
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_subscription_id' => $razorpaySubscriptionId,
                    'subscription_charge' => true,
                    'captured_at' => now()->toISOString(),
                    'razorpay_status' => $payment['status'] ?? 'captured',
                    'webhook_processed' => true,
                    'payment_method' => $payment['method'] ?? null,
                    'amount_charged' => $amount,
                ],
            ]);

            // Mark invoice as paid
            $renewalInvoice->markAsPaid();

            // Extend subscription period
            $currentEndDate = $appSubscription->current_period_ends_at;
            $plan = $appSubscription->plan;

            // Calculate new end date based on plan billing 0period
            $newEndDate = match ($plan->billing_period) {
                'monthly' => $currentEndDate->addMonth(),
                'yearly' => $currentEndDate->addYear(),
                default => $currentEndDate->addMonth(),
            };

            $appSubscription->update([
                'current_period_ends_at' => $newEndDate,
                'status' => \App\Models\Subscription::STATUS_ACTIVE,
                'metadata' => array_merge($appSubscription->metadata ?? [], [
                    'last_charged_at' => now()->toISOString(),
                    'last_charge_amount' => $amount / 100,
                    'last_razorpay_payment_id' => $paymentId,
                    'total_renewals' => ($appSubscription->metadata['total_renewals'] ?? 0) + 1,
                ]),
            ]);

            // Fire subscription renewed event for notifications
            event(new \App\Events\SubscriptionRenewed($appSubscription));

            payment_log('Subscription charged and renewed successfully', 'info', [
                'subscription_id' => $appSubscription->id,
                'invoice_id' => $renewalInvoice->id,
                'transaction_id' => $transaction->id,
                'new_end_date' => $newEndDate->toISOString(),
            ]);

            return true;

        } catch (\Exception $e) {
            payment_log('Error processing subscription charge', 'error', [
                'subscription_id' => $appSubscription->id ?? 'unknown',
                'razorpay_subscription_id' => $razorpaySubscriptionId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Handle subscription completed webhook event
     * This handles when a subscription reaches its end or is completed
     */
    protected function handleSubscriptionCompleted(array $subscription): bool
    {
        $razorpaySubscriptionId = $subscription['id'];

        // Find subscription by Razorpay subscription ID
        $appSubscription = \App\Models\Subscription::where('metadata->razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if (! $appSubscription) {
            payment_log('Subscription not found for completion', 'warning', [
                'razorpay_subscription_id' => $razorpaySubscriptionId,
            ]);

            return false;
        }

        try {
            // Update subscription status to ended
            $appSubscription->update([
                'status' => \App\Models\Subscription::STATUS_ENDED,
                'ended_at' => now(),
                'metadata' => array_merge($appSubscription->metadata ?? [], [
                    'razorpay_completed_at' => $subscription['completed_at'] ?? now()->toISOString(),
                    'completion_reason' => $subscription['status'] ?? 'completed',
                    'webhook_completed_at' => now()->toISOString(),
                ]),
            ]);

            // Log subscription completion
            \App\Models\SubscriptionLog::create([
                'subscription_id' => $appSubscription->id,
                'type' => 'subscription_completed',
                'data' => [
                    'razorpay_subscription_id' => $razorpaySubscriptionId,
                    'completion_status' => $subscription['status'] ?? 'completed',
                    'completed_at' => $subscription['completed_at'] ?? now()->toISOString(),
                    'webhook_source' => 'razorpay',
                ],
            ]);

            // Fire subscription cancelled event for notifications (closest equivalent to ended)
            event(new \App\Events\SubscriptionCancelled($appSubscription->id));

            payment_log('Subscription completed successfully', 'info', [
                'subscription_id' => $appSubscription->id,
                'razorpay_subscription_id' => $razorpaySubscriptionId,
                'status' => $appSubscription->status,
                'ended_at' => $appSubscription->ended_at->toISOString(),
            ]);

            return true;

        } catch (\Exception $e) {
            payment_log('Error processing subscription completion', 'error', [
                'subscription_id' => $appSubscription->id ?? 'unknown',
                'razorpay_subscription_id' => $razorpaySubscriptionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Reprocess a payment for an invoice - can be used to recover from issues
     * where webhook didn't properly mark the invoice as paid
     */
    public function reprocessPayment(Request $request, $invoiceId)
    {
        // Only allow admins to use this
        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user || $user->user_type !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $invoice = Invoice::findOrFail($invoiceId);

        // Check if the invoice already has a successful transaction
        $transaction = $invoice->transactions()->where('status', 'success')->first();

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'No successful transaction found for this invoice.',
            ]);
        }

        // Mark the invoice as paid if needed
        if ($invoice->status !== Invoice::STATUS_PAID) {
            // Get the typed invoice instance to ensure proper processing
            $typedInvoice = $invoice->mapType();

            // Use the appropriate invoice type for processing
            if ($typedInvoice->id === $invoice->id && get_class($typedInvoice) !== get_class($invoice)) {
                $typedInvoice->markAsPaid();
            } else {
                $invoice->markAsPaid();
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice has been marked as paid successfully.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice was already marked as paid.',
        ]);
    }

    /**
     * Helper method to find transaction by payment ID or order ID
     */
    protected function findTransactionByPayment(?string $paymentId, ?string $orderId): ?Transaction
    {
        if (! $paymentId && ! $orderId) {
            return null;
        }

        $query = Transaction::query();

        if ($paymentId) {
            $query->where(function ($q) use ($paymentId) {
                $q->where('idempotency_key', $paymentId)
                    ->orWhere('metadata->razorpay_payment_id', $paymentId);
            });
        }

        if ($orderId) {
            $query->orWhere(function ($q) use ($orderId) {
                $q->where('metadata->razorpay_order_id', $orderId)
                    ->orWhere('idempotency_key', $orderId);
            });
        }

        return $query->first();
    }

    /**
     * Display the payment authentication page for recurring payments.
     *
     * This page is shown when a customer needs to authenticate a recurring payment
     * as required by RBI regulations. The customer receives this link via email.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function authenticatePayment(Request $request, string $subdomain)
    {
        // Validate the request parameters
        $request->validate([
            'order' => 'required|string',
            'invoice' => 'required|string',
            'transaction' => 'required|string',
            'token' => 'required|string',
        ]);

        try {
            // Get the invoice and transaction
            $invoice = Invoice::where('id', $request->invoice)
                ->where('tenant_id', tenant_id())
                ->firstOrFail();

            $transaction = Transaction::where('id', $request->transaction)
                ->where('invoice_id', $request->invoice)
                ->firstOrFail();

            // Verify the token
            if (! isset($transaction->metadata['payment_link_token']) || $transaction->metadata['payment_link_token'] !== $request->token) {
                return back()->with('error', t('invalid_payment_link'));
            }

            // Check if the transaction has already been processed
            if ($transaction->status === 'success') {
                return redirect()->to(tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]))
                    ->with('success', t('payment_already_processed'));
            }

            // Get the order details from Razorpay
            $order = $this->gateway->getClient()->order->fetch($request->order);

            // Get available credit
            $remainingCredit = 0;
            try {
                $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
                if ($balance && $balance->balance > 0) {
                    $remainingCredit = $balance->balance;
                }
            } catch (\Exception $e) {
                // Continue with zero credit
                $remainingCredit = 0;
            }

            // Calculate the amount to pay
            $total = $invoice->total();
            if ($remainingCredit > 0) {
                $total = max(0, $total - $remainingCredit);
            }

            // Ensure minimum charge amount
            $total = max($total, $this->gateway->getMinimumChargeAmount($invoice->getCurrencyCode()));

            // Return the view with the authentication form
            return view('payment-gateways.razorpay.authenticate', [
                'invoice' => $invoice,
                'order' => $order,
                'keyId' => $this->gateway->getKeyId(),
                'remainingCredit' => $remainingCredit,
                'total' => $total,
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            payment_log('Razorpay payment authentication error', 'error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', t('payment_link_error').' '.$e->getMessage());
        }
    }
}
