<?php

namespace App\Http\Controllers\Admin;

use App\Events\PaymentApproved;
use App\Events\PaymentRejected;
use App\Facades\AdminCache;
use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of pending transactions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $pendingTransactions = Transaction::with(['invoice', 'invoice.tenant'])
            ->where('type', 'offline')
            ->where('status', Transaction::STATUS_PENDING)
            ->latest()
            ->paginate(10);

        return view('admin.transactions.index', [
            'transactions' => $pendingTransactions,
        ]);
    }

    /**
     * Show transaction details.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $transaction = Transaction::with(['invoice', 'invoice.tenant', 'invoice.items', 'invoice.currency'])
            ->findOrFail($id);

        $balance = TenantCreditBalance::getOrCreateBalance($transaction->invoice->tenant_id, $transaction->invoice->currency_id);
        $remainingCredit = 0;
        if ($balance->balance != 0) {
            $remainingCredit = $balance->balance;
        }

        $creditTransactions = CreditTransaction::where('invoice_id', $transaction->invoice->id)->where('type', 'debit')->get();

        return view('admin.transactions.show', [
            'transaction' => $transaction,
            'user' => getUserByTenantId($transaction['invoice']->tenant_id),
            'remainingCredit' => $remainingCredit,
            'creditTransactions' => $creditTransactions,
        ]);
    }

    /**
     * Approve an offline payment transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, $id)
    {
        $credit = $request->credit_used;
        $transaction = Transaction::findOrFail($id);

        // Make sure it's an offline payment transaction
        if ($transaction->type !== 'offline') {
            return back()->with('error', t('this_transaction_not_offline_payment'));
        }

        // Make sure it's pending
        if (! $transaction->isPending()) {
            return back()->with('error', t('this_transaction_not_pending'));
        }

        try {
            // Mark transaction as successful
            $transaction->markAsSuccessful();

            // Mark invoice as paid
            $transaction->invoice->markAsPaid();

            if ($credit > 0) {
                TenantCreditBalance::deductCredit($transaction->invoice->tenant_id, $credit, 'Offline Payment Used Credit', $transaction->invoice->id);
            }

            // Process after-payment actions like plan upgrades or downgrades
            $transaction->invoice->afterPaymentProcessed($transaction);

            // Send payment approval email
            event(new PaymentApproved($transaction));
            AdminCache::invalidateTags(['admin.dashboard', 'admin.statistics']);

            return back()->with('success', t('payment_approved_and_activated'));
        } catch (\Exception $e) {
            payment_log('Offline payment approval error', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    /**
     * Reject an offline payment transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $transaction = Transaction::findOrFail($id);

        // Make sure it's an offline payment transaction
        if ($transaction->type !== 'offline') {
            return back()->with('error', t('this_transaction_not_offline_payment'));
        }

        // Make sure it's pending
        if (! $transaction->isPending()) {
            return back()->with('error', t('this_transaction_not_pending'));
        }

        try {
            // Mark transaction as failed
            $transaction->markAsFailed($request->reason);

            event(new PaymentRejected($transaction, $request->reason));

            return back()->with('error', t('payment_rejected_message'));
        } catch (\Exception $e) {
            payment_log('Offline payment rejection error', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', t('an_error_occurred').$e->getMessage());
        }
    }
}
