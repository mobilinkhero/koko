<?php

namespace App\Providers;

use App\Events\PaymentGatewayRegistration;
use App\Services\Billing\BillingManager;
use App\Services\PaymentGateways\OfflinePaymentGateway;
use App\Services\PaymentGateways\PayPalPaymentGateway;
use App\Services\PaymentGateways\PaystackPaymentGateway;
use App\Services\PaymentGateways\RazorpayPaymentGateway;
use App\Services\PaymentGateways\StripePaymentGateway;
use App\Services\Subscription\SubscriptionManager;
use App\Settings\PaymentSettings;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind payment gateway classes
        $this->app->singleton(StripePaymentGateway::class, function ($app) {
            $settings = $app->make(PaymentSettings::class);

            return new StripePaymentGateway(
                $settings->stripe_key,
                $settings->stripe_secret
            );
        });

        $this->app->singleton(RazorpayPaymentGateway::class, function ($app) {
            $settings = $app->make(PaymentSettings::class);

            return new RazorpayPaymentGateway(
                $settings->razorpay_key_id,
                $settings->razorpay_key_secret
            );
        });

        $this->app->singleton(PayPalPaymentGateway::class, function ($app) {
            return new PayPalPaymentGateway(
                $app->make(PaymentSettings::class)
            );
        });

        $this->app->singleton(PaystackPaymentGateway::class, function ($app) {
            $settings = $app->make(PaymentSettings::class);

            return new PaystackPaymentGateway(
                $settings->paystack_public_key,
                $settings->paystack_secret_key
            );
        });

        $this->app->singleton(OfflinePaymentGateway::class, function ($app) {
            $settings = $app->make(PaymentSettings::class);
            $instructions = $settings->offline_instructions ?? 'Please make payment to our bank account and provide your invoice number in the payment details.';

            return new OfflinePaymentGateway($instructions);
        });

        // Register Billing Manager
        $this->app->singleton('billing.manager', function ($app) {
            $manager = new BillingManager;

            // Get PaymentSettings
            $settings = $app->make(PaymentSettings::class);

            // Register Razorpay only if enabled and configured
            if ($settings->razorpay_enabled && ! empty($settings->razorpay_key_id) && ! empty($settings->razorpay_key_secret)) {
                $manager->register('razorpay', function () use ($app) {
                    return $app->make(RazorpayPaymentGateway::class);
                });
            }

            // Register PayPal only if enabled and configured
            if ($settings->paypal_enabled && ! empty($settings->paypal_client_id) && ! empty($settings->paypal_client_secret)) {
                $manager->register('paypal', function () use ($app) {
                    return $app->make(PayPalPaymentGateway::class);
                });
            }

            // Register Paystack only if enabled and configured
            if ($settings->paystack_enabled && ! empty($settings->paystack_public_key) && ! empty($settings->paystack_secret_key)) {
                $manager->register('paystack', function () use ($app) {
                    return $app->make(PaystackPaymentGateway::class);
                });
            }

            // Register Stripe only if enabled and configured
            if ($settings->stripe_enabled && ! empty($settings->stripe_key) && ! empty($settings->stripe_secret)) {
                $manager->register('stripe', function () use ($app) {
                    return $app->make(StripePaymentGateway::class);
                });
            }

            // Register Offline Payment
            $manager->register('offline', function () use ($app) {
                return $app->make(OfflinePaymentGateway::class);
            });

            // Dispatch event for modules to register their payment gateways
            event(new PaymentGatewayRegistration($manager));

            return $manager;
        });

        // Register Subscription Manager
        $this->app->singleton(SubscriptionManager::class, function ($app) {
            return new SubscriptionManager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
