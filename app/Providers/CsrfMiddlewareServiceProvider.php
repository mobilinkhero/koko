<?php

namespace App\Providers;

use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\ServiceProvider;

class CsrfMiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Middleware $middleware): void
    {
        $csrf_exclusions = apply_filters('csrf_exclusions', [
            'whatsapp/webhook',
            'admin/send-message',
            'webhooks/stripe',
            'webhooks/razorpay',
            'webhooks/paystack',
            'webhooks/paypal',
            'api/webhooks',
        ]);

        $middleware->validateCsrfTokens($csrf_exclusions);
    }
}
