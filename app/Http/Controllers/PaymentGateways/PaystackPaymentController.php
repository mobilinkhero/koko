<?php

namespace App\Http\Controllers\PaymentGateways;

use App\Events\TransactionCreated;
use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use App\Services\Billing\TransactionResult;
use App\Services\PaymentGateways\PaystackPaymentGateway;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PaystackPaymentController
 *
 * Handles Paystack payment processing for tenant invoices in the WhatsApp SaaS
 * multi-tenant application. Manages the complete payment flow from checkout
 * initialization to webhook processing and payment verification.
 *
 * Key Responsibilities:
 * - Invoice payment checkout page rendering
 * - Payment initialization with Paystack API
 * - Payment callback handling and verification
 * - Webhook event processing for payment updates
 * - Error handling and user feedback
 *
 * Payment Flow:
 * 1. User visits checkout page for an invoice
 * 2. Payment is initialized with Paystack
 * 3. User completes payment on Paystack popup
 * 4. Paystack redirects to callback URL
 * 5. Payment is verified and transaction recorded
 * 6. Webhook confirms payment status
 *
 * Security Features:
 * - Tenant ownership verification for invoices
 * - Payment amount verification
 * - Webhook signature validation
 * - CSRF protection (except webhooks)
 *
 * @version 1.0.0
 */
class PaystackPaymentController extends Controller
{
    /**
     * The Paystack payment gateway instance
     *
     * @var PaystackPaymentGateway
     */
    protected $gateway;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->gateway = app('billing.manager')->gateway('paystack');
    }

    /**
     * Display the Paystack checkout page for an invoice.
     *
     * @param  mixed  $invoiceId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request, string $subdomain, $invoiceId)
    {
        // Find invoice by ID and ensure it belongs to current tenant
        $invoice = Invoice::with(['tenant.adminUser', 'currency'])
            ->where('id', $invoiceId)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        // Check if invoice is already paid
        if ($invoice->isPaid()) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('invoice_already_paid'),
            ], true);

            return redirect()->to(tenant_route('tenant.invoices.show', ['id' => $invoice->id]));
        }

        /** @var PaystackPaymentGateway $gateway */
        $gateway = $this->gateway;

        if (! $gateway->isActive()) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('paystack_payment_not_available'),
            ], true);

            return redirect()->to(tenant_route('tenant.invoices.show', ['id' => $invoice->id]));
        }

        // Calculate remaining credit and final amount
        $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
        $remainingCredit = 0;
        if ($balance->balance != 0) {
            $remainingCredit = $balance->balance;
        }
        $finalAmount = $invoice->finalPayableAmount($remainingCredit);

        // Check minimum amount requirement
        $currency = $invoice->currency->code ?? 'NGN';
        $minimumAmount = $gateway->getMinimumChargeAmount($currency);

        if ($finalAmount > 0 && $finalAmount < $minimumAmount) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('minimum_payment_amount_error', ['amount' => $minimumAmount, 'currency' => $currency]),
            ], true);

            return redirect()->to(tenant_route('tenant.invoices.show', ['id' => $invoice->id]));
        }

        return view('payment-gateways.paystack.checkout', [
            'invoice' => $invoice,
            'gateway' => $gateway,
            'publicKey' => $gateway->getPublicKey(),
            'remainingCredit' => $remainingCredit,
            'finalAmount' => $finalAmount,
            'currencySymbol' => $invoice->currency->symbol ?? '₦',
            'currency' => $currency,
            'minimumAmount' => $minimumAmount,
        ]);
    }

    /**
     * Process the Paystack payment for an invoice.
     *
     * @param  mixed  $invoiceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request, string $subdomain, $invoiceId)
    {

        // Find invoice by ID and ensure it belongs to current tenant
        $invoice = Invoice::with(['tenant.adminUser', 'currency'])
            ->where('id', $invoiceId)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        try {
            $gateway = $this->gateway;

            // Calculate credit and final amount
            $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
            $remainingCredit = ($balance->balance > 0) ? min($balance->balance, $invoice->total()) : 0;
            $finalAmount = $invoice->finalPayableAmount($remainingCredit);

            // Check minimum amount requirement
            $currency = $invoice->currency->code ?? 'NGN';
            $minimumAmount = $gateway->getMinimumChargeAmount($currency);

            if ($finalAmount > 0 && $finalAmount < $minimumAmount) {
                return response()->json([
                    'success' => false,
                    'message' => t('minimum_payment_amount_error', ['amount' => $minimumAmount, 'currency' => $currency]),
                    'error' => t('minimum_payment_amount_error', ['amount' => $minimumAmount, 'currency' => $currency]),
                ], 422);
            }

            // Create pending transaction using the correct method signature
            $transaction = $invoice->createPendingTransaction($gateway, tenant_id());

            // Initialize payment with Paystack
            $result = $gateway->initializePayment($invoice, [
                'transaction_id' => $transaction->id,
                'remaining_credit' => $remainingCredit,
                'final_amount' => $finalAmount,
            ]);

            if ($result['status'] === true && isset($result['data'])) {
                // Fire transaction created event
                event(new TransactionCreated($transaction->id, $invoice->id));

                return response()->json([
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? (t('payment_initialization_failed')),
                'error' => $result['message'] ?? (t('payment_initialization_failed')),
            ], 422);

        } catch (Exception $e) {

            // Update transaction status to failed if it was created
            if (isset($transaction) && $transaction instanceof Transaction) {
                $transaction->update([
                    'status' => Transaction::STATUS_FAILED,
                    'error' => $e->getMessage(),
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'failed_at' => now()->toISOString(),
                        'failure_reason' => 'Payment initialization failed',
                        'exception_message' => $e->getMessage(),
                    ]),
                ]);

                // Fire transaction failed event
                event(new \App\Events\TransactionFailed($transaction));
            }

            Log::error('Paystack payment initialization failed', [
                'invoice_id' => $invoice->id,
                'transaction_id' => isset($transaction) ? $transaction->id : null,
                'error' => $e->getMessage(),
                'tenant_id' => tenant_id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => t('payment_initialization_failed_try_again'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment callback from Paystack.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $reference = $request->get('reference');
        $trxref = $request->get('trxref');

        if (! $reference && ! $trxref) {
            return redirect()->route('tenant.dashboard')
                ->with('error', t('invalid_payment_reference'));
        }

        // Use whichever reference is available
        $paymentReference = $reference ?: $trxref;

        try {
            /** @var PaystackPaymentGateway $gateway */
            $gateway = $this->gateway;

            // Verify the payment
            $paymentData = $gateway->verifyPayment($paymentReference);

            if ($paymentData['status'] === true && $paymentData['data']['status'] === 'success') {
                // Extract invoice ID from metadata
                $invoiceId = $paymentData['data']['metadata']['invoice_id'] ?? null;
                $transactionId = $paymentData['data']['metadata']['transaction_id'] ?? null;

                if (! $invoiceId) {
                    return redirect()->route('tenant.dashboard')
                        ->with('error', t('payment_verification_failed_invalid_invoice'));
                }

                $invoice = Invoice::find($invoiceId);
                if (! $invoice || $invoice->tenant_id !== tenant_id()) {
                    return redirect()->route('tenant.dashboard')
                        ->with('error', t('invoice_not_found'));
                }

                // Find the existing transaction
                $transaction = null;
                if ($transactionId) {
                    $transaction = Transaction::find($transactionId);
                }

                // If no transaction found by ID, find by idempotency_key
                if (! $transaction) {
                    $transaction = Transaction::where('idempotency_key', $paymentReference)
                        ->where('invoice_id', $invoice->id)
                        ->first();
                }

                if (! $transaction) {
                    return redirect()->to(tenant_route('tenant.dashboard'))
                        ->with('error', t('transaction_not_found'));
                }

                // Check if transaction is already processed AND invoice is paid
                if ($transaction->status === Transaction::STATUS_SUCCESS && $invoice->isPaid()) {
                    session()->flash('notification', [
                        'type' => 'success',
                        'message' => t('payment_already_processed'),
                    ], true);

                    return redirect()->to(tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]));
                }

                // Process payment using database transaction for consistency
                DB::transaction(function () use ($invoice, $transaction, $paymentData) {
                    // Update transaction to successful (moved inside transaction)
                    $transaction->update([
                        'status' => Transaction::STATUS_SUCCESS,
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'paystack_response' => $paymentData['data'],
                            'completed_at' => now()->toISOString(),
                        ]),
                    ]);

                    // Apply any remaining credit
                    $remainingCredit = $paymentData['data']['metadata']['remaining_credit'] ?? 0;
                    if ($remainingCredit > 0) {
                        TenantCreditBalance::deductCredit(
                            tenant_id(),
                            $remainingCredit,
                            "Applied to invoice #{$invoice->invoice_number}",
                            $invoice->id
                        );
                    }

                    // Mark invoice as paid using the proper method
                    try {
                        Log::info('PaystackController: About to call handleTransactionResult', [
                            'invoice_id' => $invoice->id,
                            'transaction_id' => $transaction->id,
                            'transaction_status' => $transaction->status,
                            'invoice_status_before' => $invoice->status,
                        ]);

                        $invoice->handleTransactionResult($transaction, new TransactionResult(
                            TransactionResult::RESULT_DONE,
                            'Payment successful via Paystack'
                        ));

                        // Refresh invoice to get updated status
                        $invoice->refresh();

                        Log::info('PaystackController: handleTransactionResult completed', [
                            'invoice_id' => $invoice->id,
                            'invoice_status_after' => $invoice->status,
                            'invoice_paid_at' => $invoice->paid_at,
                        ]);

                    } catch (Exception $e) {
                        Log::error('PaystackController: Error in handleTransactionResult', [
                            'invoice_id' => $invoice->id,
                            'transaction_id' => $transaction->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        // Fall back to manual marking if handleTransactionResult fails
                        $invoice->markAsPaid();
                        Log::info('PaystackController: Fallback markAsPaid called');
                    }
                });

                session()->flash('notification', [
                    'type' => 'success',
                    'message' => t('payment_successful_invoice_paid'),
                ], true);

                return redirect()->to(tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]));

            } else {
                // Payment failed - find and update transaction
                $invoiceId = $paymentData['data']['metadata']['invoice_id'] ?? null;
                if ($invoiceId) {
                    $transaction = Transaction::where('idempotency_key', $paymentReference)
                        ->where('invoice_id', $invoiceId)
                        ->first();

                    if ($transaction) {
                        $transaction->update([
                            'status' => Transaction::STATUS_FAILED,
                            'error' => $paymentData['data']['gateway_response'] ?? 'Payment failed',
                            'metadata' => array_merge($transaction->metadata ?? [], [
                                'paystack_response' => $paymentData['data'],
                                'failed_at' => now()->toISOString(),
                            ]),
                        ]);
                    }
                }

                $message = $paymentData['data']['gateway_response'] ?? (t('payment_not_successful'));

                session()->flash('notification', [
                    'type' => 'error',
                    'message' => (t('payment_failed')).': '.$message,
                ], true);

                return redirect()->to(tenant_route('tenant.dashboard'));
            }

        } catch (Exception $e) {
            Log::error('Paystack payment callback error', [
                'reference' => $paymentReference,
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            session()->flash('notification', [
                'type' => 'error',
                'message' => t('payment_verification_failed_contact_support'),
                'error' => t('payment_verification_failed_contact_support'),
            ], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }
}
