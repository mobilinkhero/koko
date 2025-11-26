<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionManager;
use App\Services\SubscriptionCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class SubscriptionController extends Controller
{
    /**
     * The subscription manager instance.
     *
     * @var \App\Services\Subscription\SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Process the checkout and create a subscription.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
            // Add any other validation rules needed for checkout
        ]);

        // Get the plan to check its price
        $plan = Plan::findOrFail($request->plan_id);

        // Validate PayPal minimum amount requirement
        if ($request->payment_method === 'paypal' && $plan->price <= 1) {
            return redirect()->back()->with('error', t('paypal_minimum_amount_error'));
        }

        try {
            // Create subscription
            $subscription = $this->subscriptionManager->createSubscription(
                tenant_id(),
                $request->plan_id
            );

            // Get the invoice for the subscription
            $invoice = $subscription->getUnpaidInitInvoice() ?? $subscription->getUnpaidRenewInvoice();

            if (! $invoice) {
                payment_log('Failed to get unpaid init invoice', 'error', [
                    'tenant_id' => tenant_id(),
                    'subscription_id' => $subscription->id,
                ]);

                return redirect()->to(tenant_route('tenant.subscriptions'))->with('error', t('failed_to_create_invoice'));
            }

            // Determine which payment gateway to use
            $gateway = app('billing.manager')->gateway($request->payment_method);

            // Get checkout URL
            $checkoutUrl = $gateway->getCheckoutUrl($invoice);

            // Redirect to payment gateway checkout
            return redirect()->to($checkoutUrl);
        } catch (\Exception $e) {
            payment_log('Failed to create subscription', 'error', [
                'tenant_id' => tenant_id(),
                'invoice_id' => $invoice->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', t('failed_create_subscription ').$e->getMessage());
        }
    }

    /**
     * Show the thank you page after successful payment.
     *
     * @param  string  $invoiceId
     * @return \Illuminate\View\View
     */
    public function thankYou(string $subdomain, $invoiceId)
    {
        SubscriptionCache::clearCache(tenant_id());
        $invoice = \App\Models\Invoice\Invoice::where('id', $invoiceId)
            ->where('tenant_id', tenant_id())
            ->firstOrFail();

        return View::make('subscriptions.thankyou', compact('invoice'));
    }

    /**
     * Display a listing of the user's subscriptions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (checkPermission('tenant.subscription.view')) {
            return View::make('subscriptions.index');
        } else {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    /**
     * Display the specified subscription.
     *
     * @param  string  $iid
     * @return \Illuminate\View\View
     */
    public function show(string $subdomain, int $id)
    {
        if (! checkPermission('tenant.subscription.view')) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $query = Subscription::with(['plan', 'tenant', 'invoices', 'subscriptionLogs']);

        $subscription = $query->where('tenant_id', tenant_id())
            ->where('id', $id)
            ->firstOrFail();

        if (isset($subscription->plan->features) && $subscription->plan->features instanceof \Illuminate\Support\Collection) {
            $subscription->plan->features->push((object) [
                'name' => 'Whatsapp Webhook',
                'slug' => 'whatsapp_webhook',
                'value' => '-1',
            ]);
        }

        $subscription = apply_filters('before_rendar_subscription_list', $subscription);

        return View::make('subscriptions.show', compact('subscription'));
    }

    public function showAdminSubscription(int $id)
    {
        if (! checkPermission('admin.subscription.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'mssage' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }

        $query = Subscription::with(['plan', 'tenant', 'invoices', 'subscriptionLogs']);
        $subscription = $query->findOrFail($id);

        return View::make('subscriptions.show', compact('subscription'));
    }

    /**
     * Cancel a subscription.
     *
     * @param  string  $uid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, string $subdomain, $id)
    {
        try {
            $this->subscriptionManager->cancelSubscription(
                $id,
                tenant_id()
            );

            return Redirect::back()->with('success', t('subscription_cancelled'));
        } catch (\Exception $e) {
            payment_log('Failed to cancel subscription', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return Redirect::back()->with('error', t('failed_cancel_subscription').$e->getMessage());
        }
    }

    /**
     * Toggle recurring billing for a subscription.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleRecurring(Request $request, string $subdomain, $id)
    {
        $enable = $request->input('enable', true);

        try {
            $this->subscriptionManager->toggleRecurring(
                $id,
                tenant_id(),
                $enable
            );

            $status = $enable ? 'enabled' : 'disabled';

            return Redirect::back()->with('success', "Auto-renewal has been {$status} successfully.");
        } catch (\Exception $e) {
            payment_log('Failed to update subscription', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return Redirect::back()->with('error', t('failed_update_subscription').$e->getMessage());
        }
    }

    /**
     * Show the form for upgrading a subscription.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function upgradeForm(string $subdomain, $id)
    {
        $subscription = $this->subscriptionManager->getSubscription(
            $id,
            tenant_id()
        );

        if (! $subscription || (! $subscription->isActive() && ! $subscription->isPause())) {
            abort(404);
        }

        // Get only plans with higher price (upgrades)
        $plans = Plan::with('features')
            ->where('is_active', true)
            ->where('is_free', '0')
            ->where('price', '>', $subscription->plan->price)
            ->where('currency_id', $subscription->plan->currency_id)
            ->get();

        return View::make('subscriptions.upgrade', compact('subscription', 'plans'));
    }

    /**
     * Process the subscription upgrade.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upgrade(Request $request, string $subdomain, $id)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'override_existing' => 'boolean',
        ]);

        try {
            // Fetch the subscription to verify it exists
            $subscription = $this->subscriptionManager->getSubscription(
                $id,
                tenant_id()
            );

            if (! $subscription || ! $subscription->isActive() && ! $subscription->isPause()) {
                return Redirect::back()->with('error', t('cannot_upgrade_subscription'));
            }

            // Get the target plan to verify it's valid
            $newPlan = Plan::findOrFail($request->input('plan_id'));

            // Verify it's actually an upgrade
            if ($newPlan->price <= $subscription->plan->price) {
                return Redirect::back()->with('error', t('the_selected_plan_not_upgrade'));
            }

            $invoice = $this->subscriptionManager->upgradePlan(
                $id,
                $request->input('plan_id'),
                tenant_id(),
                $request->boolean('override_existing')
            );

            SubscriptionCache::clearCache($invoice->tenant_id);

            // Fix: Pass the invoice UID directly as a route parameter, not as an array
            return redirect()->to(tenant_route('tenant.checkout.resume', ['id' => $invoice->id]))
                ->with('success', t('complete_payment_to_upgrade'));
        } catch (\Exception $e) {
            payment_log('Upgrade error', 'error', [
                'tenant_id' => tenant_id(),
                'subscription_id' => $id,
                'plan_id' => $request->input('plan_id'),
                'error' => $e->getMessage(),
            ]);

            return Redirect::back()->with('error', t('failed_upgrade_plan').$e->getMessage());
        }
    }

    /**
     * Show the form for downgrading a subscription.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function downgradeForm(string $subdomain, $id)
    {
        $subscription = $this->subscriptionManager->getSubscription(
            $id,
            tenant_id()
        );

        if (! $subscription || ! $subscription->isActive() && ! $subscription->isPause()) {
            abort(404);
        }

        // Get only plans with lower price (downgrades)
        $plans = Plan::with('features')
            ->where('is_active', true)
            ->where('is_free', '0')
            ->where('price', '<', $subscription->plan->price)
            ->where('currency_id', $subscription->plan->currency_id)
            ->get();

        return View::make('subscriptions.downgrade', compact('subscription', 'plans'));
    }

    /**
     * Process the subscription downgrade.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downgrade(Request $request, string $subdomain, $id)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'override_existing' => 'boolean',
        ]);

        try {
            // Fetch the subscription to verify it exists
            $subscription = $this->subscriptionManager->getSubscription(
                $id,
                tenant_id()
            );

            if (! $subscription || ! $subscription->isActive() && ! $subscription->isPause()) {
                return Redirect::back()->with('error', t('cannot_downgrade'));
            }

            // Get the target plan to verify it's valid
            $newPlan = Plan::findOrFail($request->input('plan_id'));

            $invoice = $this->subscriptionManager->downgradePlan(
                $id,
                $request->input('plan_id'),
                tenant_id(),
                $request->boolean('override_existing')
            );

            SubscriptionCache::clearCache($invoice->tenant_id);

            if ($invoice->status != \App\Models\Invoice\Invoice::STATUS_PAID) {
                return redirect()->to(tenant_route('tenant.checkout.resume', ['id' => $invoice->id]))
                    ->with('success', t('complete_payment_to_upgrade'));
            }

            // Redirect to subscription show page since downgrade is automatically processed
            return redirect()->to(tenant_route('tenant.subscriptions.show', ['id' => $id]))
                ->with('success', t('subscription_downgraded'));
        } catch (\Exception $e) {
            payment_log('Downgrade error', 'error', [
                'tenant_id' => tenant_id(),
                'subscription_id' => $id,
                'plan_id' => $request->input('plan_id'),
                'error' => $e->getMessage(),
            ]);

            return Redirect::back()->with('error', t('failed_downgrade_plan').$e->getMessage());
        }
    }

    /**
     * Cancel a pending plan change invoice.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelPendingPlanChange(Request $request, string $subdomain, $id)
    {
        $subscription = $this->subscriptionManager->getSubscription(
            $id,
            tenant_id()
        );

        if (! $subscription || ! $subscription->isActive()) {
            abort(404);
        }

        try {
            $pendingInvoice = $subscription->getUnpaidChangePlanInvoice();

            if (! $pendingInvoice) {
                return Redirect::back()->with('error', t('no_pending_change_plan'));
            }

            // Cancel the pending invoice
            $pendingInvoice->status = \App\Models\Invoice\Invoice::STATUS_CANCELLED;
            $pendingInvoice->cancelled_at = now();
            $pendingInvoice->save();

            // Add log entry for cancellation
            $subscription->addLog('plan_change_cancelled', [
                'invoice_id' => $pendingInvoice->id,
                'reason' => 'Cancelled by user',
            ]);

            return Redirect::back()->with('success', t('cancel_subscription'));
        } catch (\Exception $e) {
            payment_log('Failed to cancel pending plan change', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return Redirect::back()->with('error', t('failed_cancel_plan').$e->getMessage());
        }
    }

    public function publicPlans()
    {
        return redirect()->to(tenant_route('tenant.subscription'));
    }
}
