<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentWebhook;
use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class WebhookSettingsController extends Controller
{
    protected $stripeWebhookService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StripeWebhookService $stripeWebhookService)
    {
        $this->stripeWebhookService = $stripeWebhookService;
    }

    /**
     * Display the webhook settings page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Get webhook configurations
        $stripeWebhook = PaymentWebhook::forProvider('stripe')->active()->first();

        return view('admin.settings.webhooks', [
            'stripeWebhook' => $stripeWebhook,
        ]);
    }

    /**
     * Configure webhooks via console command.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function configure(Request $request)
    {
        $provider = $request->input('provider', 'stripe');

        try {
            $exitCode = Artisan::call('stripe:webhooks', [
                '--url' => $request->input('endpoint_url'),
                '--events' => $request->input('events', []),
            ]);

            if ($exitCode === 0) {
                return redirect()->back()->with('success', t('webhooks_configured_successfully'));
            } else {
                return redirect()->back()->with('error', t('failed_configure_webhooks'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', t('error_configuring_webhooks').$e->getMessage());
        }
    }

    /**
     * Delete a webhook.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, string $provider, string $webhookId)
    {
        if ($provider === 'stripe') {
            $result = $this->stripeWebhookService->deleteWebhook($webhookId);

            if ($result['success']) {
                return redirect()->back()->with('success', t('webhook_deleted_successfully'));
            } else {
                return redirect()->back()->with('error', t('failed_to_delete_webhook').($result['message'] ?? 'Unknown error'));
            }
        }

        return redirect()->back()->with('error', t('unsupported_payment_provider').$provider);
    }

    /**
     * List all webhooks from a provider.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function list(Request $request, string $provider)
    {
        if ($provider === 'stripe') {
            $result = $this->stripeWebhookService->listWebhooks();

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return view('admin.settings.webhooks.list', [
                    'webhooks' => $result['webhooks'],
                    'provider' => $provider,
                ]);
            } else {
                return redirect()->back()->with('error', t('failed_to_list_webhooks ').($result['message'] ?? 'Unknown error'));
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => t('unsupported_payment_provider')]);
        }

        return redirect()->back()->with('error', t('unsupported_payment_provider').$provider);
    }

    /**
     * Get webhook details.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function show(Request $request, string $provider, string $webhookId)
    {
        if ($provider === 'stripe') {
            $result = $this->stripeWebhookService->getWebhookDetails($webhookId);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return view('admin.settings.webhooks.show', [
                    'webhook' => $result['webhook'],
                    'provider' => $provider,
                ]);
            } else {
                return redirect()->back()->with('error', t('failed_to_get_webhook_details ').($result['message'] ?? 'Unknown error'));
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => t('unsupported_payment_provider')]);
        }

        return redirect()->back()->with('error', t('unsupported_payment_provider').$provider);
    }
}
