<?php

namespace App\Http\Controllers\PaymentGateways;

use App\Events\TransactionCreated;
use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\TenantCreditBalance;
use App\Services\Billing\TransactionResult;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfflinePaymentController extends Controller
{
    /**
     * The offline payment gateway instance.
     *
     * @var \App\Services\PaymentGateways\OfflinePaymentGateway
     */
    protected $gateway;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->gateway = app('billing.manager')->gateway('offline');
    }

    /**
     * Show the checkout page for an invoice.
     *
     * @param  mixed  $invoiceId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function checkout(Request $request, string $subdomain, $invoiceId)
    {
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

        // Ensure taxes are applied to the invoice
        if ($invoice->taxes()->count() === 0) {
            $invoice->applyTaxes();
        }

        $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
        $remainingCredit = 0;
        if ($balance->balance != 0) {
            $remainingCredit = $balance->balance;
        }

        return view('payment-gateways.offline.checkout', [
            'invoice' => $invoice,
            'paymentInstruction' => $this->gateway->getPaymentInstruction(),
            'remainingCredit' => $remainingCredit ?? 0,
        ]);
    }

    /**
     * Process the offline payment notification.
     *
     * @param  string  $invoiceId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request, string $subdomain, $invoiceId)
    {
        $request->validate([
            'payment_reference' => 'required|string',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payment_details' => 'nullable|string',
        ]);

        $invoice = Invoice::where('id', $invoiceId)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->firstOrFail();

        try {
            // Process the invoice payment
            $invoice->checkout($this->gateway, function ($invoice, $transaction) use ($request) {
                try {
                    // Store payment details
                    $transaction->metadata = [
                        'payment_reference' => $request->payment_reference,
                        'payment_date' => $request->payment_date,
                        'payment_method' => $request->payment_method,
                        'payment_details' => $request->payment_details,
                    ];
                    $transaction->save();
                    // Send transaction notification email
                    // Dispatch event instead of directly sending email
                    event(new TransactionCreated($transaction->id, $invoice->id));
                    // For offline payments, we always return pending status
                    $transactionResult = new TransactionResult(TransactionResult::RESULT_PENDING);

                    return $transactionResult;
                } catch (\Exception $e) {
                    payment_log('Offline payment error', 'error', [
                        'tenant_id' => tenant_id(),
                        'error' => $e->getMessage(),
                    ]);

                    $transactionResult = new TransactionResult(
                        TransactionResult::RESULT_FAILED,
                        $e->getMessage()
                    );

                    return $transactionResult;
                }
            });

            session()->flash('notification', [
                'type' => 'info',
                'message' => t('payment_submit_notification'),
            ]);

            return redirect()->to(tenant_route('tenant.subscription.pending'));
        } catch (\Exception $e) {
            payment_log('Offline payment process error', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->to(tenant_route('tenant.checkout.resume', ['invoice' => $invoice->id]))
                ->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Display a listing of offline payment transactions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Only admin users can view transaction list
        if (! Auth::user()->is_admin) {
            abort(403);
        }

        // Get transactions of type 'offline'
        $transactions = \App\Models\Transaction::where('type', 'offline')
            ->with(['invoice', 'invoice.tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.transactions.index', compact('transactions'));
    }
}
