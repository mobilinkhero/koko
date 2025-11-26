<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentWebhook;
use App\Rules\PurifiedInput;
use App\Settings\PaymentSettings;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function showOfflineSettings(PaymentSettings $settings)
    {
        if (! checkPermission('admin.payment_settings.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }

        return view('admin.settings.payment.offline', [
            'settings' => $settings,
        ]);
    }

    public function updateOfflineSettings(Request $request)
    {
        if (checkPermission('admin.payment_settings.edit')) {
            $request->validate([
                'offline_description' => ['nullable', 'string', 'max:500', new PurifiedInput(t('sql_injection_error'))],
                'offline_instructions' => ['nullable', 'string', 'max:1000', new PurifiedInput(t('sql_injection_error'))],
            ]);

            set_settings_batch('payment', [
                'offline_description' => $request->offline_description ?? '',
                'offline_instructions' => $request->offline_instructions ?? '',
                'offline_enabled' => ($request->offline_enabled) ? true : false,
            ]);

            if ($request->offline_enabled) {
                set_setting('payment.default_gateway', 'offline');
            }

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('settings_saved_successfully'),
            ]);

            return redirect()->to(route('admin.payment-settings'));
        }
    }

    /**
     * Show Stripe payment settings.
     *
     * @return \Illuminate\View\View
     */
    public function showStripeSettings(PaymentSettings $settings)
    {
        if (! checkPermission('admin.payment_settings.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }

        return view('admin.settings.payment.stripe', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update Stripe payment settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStripeSettings(Request $request)
    {
        if (checkPermission('admin.payment_settings.edit')) {
            $request->validate([
                'stripe_enabled' => ['string', 'in:on,off'],
                'stripe_key' => [
                    'required_if:stripe_enabled,on',
                    'string',
                    'max:255',
                    'regex:/^pk_(test|live)_[A-Za-z0-9]+$/',
                    new PurifiedInput(t('sql_injection_error')),
                ],
                'stripe_secret' => [
                    'required_if:stripe_enabled,on',
                    'string',
                    'max:255',
                    'regex:/^sk_(test|live)_[A-Za-z0-9]+$/',
                    new PurifiedInput(t('sql_injection_error')),
                ],

            ], [
                'stripe_key.regex' => t('invalid_stripe_publishable_key'),
                'stripe_secret.regex' => t('invalid_stripe_secret_key'),
            ]);

            set_settings_batch('payment', [
                'stripe_enabled' => ($request->stripe_enabled) ? true : false,
                'stripe_key' => $request->stripe_key ?? '',
                'stripe_secret' => $request->stripe_secret ?? '',
            ]);

            if (! $request->stripe_enabled) {
                PaymentWebhook::where('provider', 'stripe')->delete();
            }

            if ($request->stripe_enabled) {
                set_setting('payment.default_gateway', 'stripe');
            }

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('settings_saved_successfully'),
            ]);

            return redirect()->to(route('admin.payment-settings'));
        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => t('access_denied_note'),
        ]);

        return redirect()->to(route('admin.dashboard'));
    }

    /**
     * Show Razorpay payment settings.
     *
     * @return \Illuminate\View\View
     */
    public function showRazorpaySettings(PaymentSettings $settings)
    {
        if (! checkPermission('admin.payment_settings.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }

        return view('admin.settings.payment.razorpay', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update Razorpay payment settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRazorpaySettings(Request $request)
    {
        if (checkPermission('admin.payment_settings.edit')) {
            $request->validate([
                'razorpay_enabled' => ['string', 'in:on,off'],
                'razorpay_key_id' => [
                    'required_if:razorpay_enabled,on',
                    'string',
                    'max:255',
                    'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/',
                    new PurifiedInput(t('sql_injection_error')),
                ],
                'razorpay_key_secret' => [
                    'required_if:razorpay_enabled,on',
                    'string',
                    'max:255',
                    new PurifiedInput(t('sql_injection_error')),
                ],
                'razorpay_webhook_secret' => [
                    'nullable',
                    'string',
                    'max:255',
                    new PurifiedInput(t('sql_injection_error')),
                ],
            ], [
                'razorpay_key_id.regex' => t('invalid_razorpay_key_id'),
            ]);

            set_settings_batch('payment', [
                'razorpay_enabled' => ($request->razorpay_enabled) ? true : false,
                'razorpay_key_id' => $request->razorpay_key_id ?? '',
                'razorpay_key_secret' => $request->razorpay_key_secret ?? '',
                'razorpay_webhook_secret' => $request->razorpay_webhook_secret ?? '',
            ]);

            if ($request->razorpay_enabled) {
                set_setting('payment.default_gateway', 'razorpay');
            }

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('settings_saved_successfully'),
            ]);

            return redirect()->to(route('admin.payment-settings'));
        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => t('access_denied_note'),
        ]);

        return redirect()->to(route('admin.dashboard'));
    }

    /**
     * Show PayPal payment settings.
     *
     * @return \Illuminate\View\View
     */
    public function showPayPalSettings(PaymentSettings $settings)
    {
        if (! checkPermission('admin.payment_settings.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }

        return view('admin.settings.payment.paypal', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update PayPal payment settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePayPalSettings(Request $request)
    {
        if (checkPermission('admin.payment_settings.edit')) {
            $request->validate([
                'paypal_enabled' => ['string', 'in:on,off'],
                'paypal_mode' => ['required', 'string', 'in:sandbox,live'],
                'paypal_client_id' => [
                    'required_if:paypal_enabled,on',
                    'string',
                    'max:255',
                    new PurifiedInput(t('sql_injection_error')),
                ],
                'paypal_client_secret' => [
                    'required_if:paypal_enabled,on',
                    'string',
                    'max:255',
                    new PurifiedInput(t('sql_injection_error')),
                ],
            ]);

            set_settings_batch('payment', [
                'paypal_enabled' => $request->has('paypal_enabled'),
                'paypal_mode' => $request->paypal_mode ?? 'sandbox',
                'paypal_client_id' => $request->paypal_client_id ?? '',
                'paypal_client_secret' => $request->paypal_client_secret ?? '',
            ]);

            if ($request->paypal_enabled) {
                set_setting('payment.default_gateway', 'paypal');
            }

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('settings_saved_successfully'),
            ]);

            return redirect()->to(route('admin.payment-settings'));
        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => t('access_denied_note'),
        ]);

        return redirect()->to(route('admin.dashboard'));
    }

    /**
     * Show Paystack payment settings.
     *
     * @return \Illuminate\View\View
     */
    public function showPaystackSettings(PaymentSettings $settings)
    {
        if (! checkPermission('admin.payment_settings.view')) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }

        return view('admin.settings.payment.paystack', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update Paystack payment settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePaystackSettings(Request $request)
    {
        if (checkPermission('admin.payment_settings.edit')) {
            $request->validate([
                'paystack_enabled' => ['string', 'in:on,off'],
                'paystack_public_key' => [
                    'required_if:paystack_enabled,on',
                    'string',
                    'max:255',
                    'regex:/^pk_(test|live)_[A-Za-z0-9]+$/',
                    new PurifiedInput(t('sql_injection_error')),
                ],
                'paystack_secret_key' => [
                    'required_if:paystack_enabled,on',
                    'string',
                    'max:255',
                    'regex:/^sk_(test|live)_[A-Za-z0-9]+$/',
                    new PurifiedInput(t('sql_injection_error')),
                ],
            ]);

            set_settings_batch('payment', [
                'paystack_enabled' => $request->has('paystack_enabled'),
                'paystack_public_key' => $request->paystack_public_key ?? '',
                'paystack_secret_key' => $request->paystack_secret_key ?? '',
            ]);

            if ($request->paystack_enabled) {
                set_setting('payment.default_gateway', 'paystack');
            }

            session()->flash('notification', [
                'type' => 'success',
                'message' => t('settings_saved_successfully'),
            ]);

            return redirect()->to(route('admin.payment-settings'));
        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => t('access_denied_note'),
        ]);

        return redirect()->to(route('admin.dashboard'));
    }
}
