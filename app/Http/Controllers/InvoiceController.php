<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\Invoice\Invoice;
use App\Models\TenantCreditBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Display a listing of the invoices.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (! checkPermission('tenant.invoices.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        return view('invoices.index');
    }

    /**
     * Display the specified invoice.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show(string $subdomain, $id)
    {
        if (! checkPermission('tenant.invoices.view')) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $query = Invoice::with('tenant');

        $invoice = $query->where('id', $id)
            ->where('tenant_id', tenant_id())
            ->firstOrFail();

        $creditTransactions = CreditTransaction::where('invoice_id', $id)->where('type', 'debit')->get();

        $tenant = $invoice->tenant ?? current_tenant();

        return view('invoices.show', compact('invoice', 'tenant', 'creditTransactions'));
    }

    public function showAdminInvoice($id)
    {
        if (! checkPermission('admin.invoices.view')) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->to(route('admin.dashboard'));
        }

        $creditTransactions = CreditTransaction::where('invoice_id', $id)->where('type', 'debit')->get();

        $query = Invoice::with('tenant');
        $invoice = $query->findOrFail($id);
        $tenant = $invoice->tenant ?? current_tenant();

        return view('invoices.show', compact('invoice', 'tenant', 'creditTransactions'));
    }

    /**
     * Download the specified invoice as PDF.
     *
     * @param  string  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $subdomain, $id)
    {
        if (checkPermission('tenant.invoices.view')) {
            $query = Invoice::query();

            $invoice = $query->where('tenant_id', tenant_id())
                ->where('id', $id)
                ->firstOrFail();

            $pdfPath = $invoice->savePdf();
            $filename = empty($invoice->invoice_number) ? formateInvoiceNumber($id, $invoice->invoice_number).'.pdf' : $invoice->invoice_number.'.pdf';

            return Response::download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } else {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function downloadInvoicePdf($id)
    {
        if (checkPermission('admin.invoices.view')) {
            $query = Invoice::query();

            $invoice = $query->findOrFail($id);
            $pdfPath = $invoice->savePdf();
            $filename = empty($invoice->invoice_number) ? formateInvoiceNumber($id, $invoice->invoice_number).'.pdf' : $invoice->invoice_number.'.pdf';

            return Response::download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } else {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }
    }

    /**
     * Resume the checkout process for an unpaid invoice.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function resumeCheckout(string $subdomain, $id)
    {
        $invoice = Invoice::where('id', $id)
            ->where('tenant_id', tenant_id())
            ->where('status', Invoice::STATUS_NEW)
            ->first();

        if (! $invoice) {
            return redirect()->to(tenant_route('tenant.subscriptions'));
        }

        // If the invoice is free, bypass payment
        if ($invoice->isFree()) {
            $invoice->bypassPayment();

            return redirect()->to(tenant_route('tenant.subscription.thank-you', ['invoice' => $invoice->id]))
                ->with('success', t('subscription_activate_successfully'));
        }

        // Get available payment gateways
        $billingManager = app('billing.manager');
        $availableGateways = $billingManager->getActiveGateways();

        // If no payment gateways available, show error
        if ($availableGateways->isEmpty()) {
            return redirect()->to(tenant_route('tenant.dashboard'))
                ->with('error', t('no_payment_method_available'));
        }

        // If only one gateway available, redirect directly to it
        if ($availableGateways->count() === 1) {
            $gateway = $availableGateways->first();

            return redirect()->to($gateway->getCheckoutUrl($invoice));
        }

        $balance = TenantCreditBalance::getOrCreateBalance(tenant_id(), $invoice->currency_id);
        $remainingCredit = 0;
        if ($balance->balance != 0) {
            $remainingCredit = $balance->balance;
        }

        // Show payment method selection
        return view('invoices.checkout', [
            'invoice' => $invoice,
            'availableGateways' => $availableGateways,
            'remainingCredit' => $remainingCredit,
        ]);
    }

    /**
     * Show the invoice PDF in the browser.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function showPdf(string $subdomain, $id)
    {
        if (checkPermission('tenant.invoices.view')) {
            $query = Invoice::query();

            $invoice = $query->where('tenant_id', tenant_id())
                ->where('id', $id)
                ->firstOrFail();

            $tenant = $invoice->tenant ?? current_tenant();
            $pdf = $invoice->exportToPdf();

            $filename = empty($invoice->invoice_number) ? formateInvoiceNumber($id, $invoice->invoice_number) : $invoice->invoice_number;

            return Response::make($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
            ]);
        } else {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function showInvoicePdf($id)
    {
        if (checkPermission('admin.invoices.view')) {
            $query = Invoice::query();

            $invoice = $query->findOrFail($id);

            $tenant = $invoice->tenant;
            $pdf = $invoice->exportToPdf();

            $filename = empty($invoice->invoice_number) ? formateInvoiceNumber($id, $invoice->invoice_number) : $invoice->invoice_number;

            return Response::make($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
            ]);
        } else {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }
    }

    /**
     * Process payment for an invoice.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pay(Request $request, string $subdomain, $id)
    {
        // Instead of processing payment directly, redirect to checkout page
        return redirect()->to(tenant_route('tenant.checkout.resume', ['id' => $id]));
    }
}
