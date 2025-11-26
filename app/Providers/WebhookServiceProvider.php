<?php

namespace App\Providers;

use App\Services\StripeWebhookService;
use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeWebhookService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\SetupStripeWebhooksCommand::class,
            ]);
        }

        // Register the webhook set up job to run in app initialization
        // But only if we're not in console mode (to avoid interfering with artisan commands)
        if (! $this->app->runningInConsole() && config('app.env') === 'production') {
            // Only try to set up webhooks automatically in production
            $this->app->booted(function () {
                // This will run after all service providers have booted
                // We use a callback to avoid slowing down application boot time
                // The schedule will run in background
                $this->scheduleWebhookSetup();
            });
        }
    }

    /**
     * Schedule webhook setup to run in background
     */
    protected function scheduleWebhookSetup(): void
    {
        // Add to queue with a delay to ensure app is fully booted
        // dispatch(function () {
        //     try {
        //         // Check if we have any webhook configured already
        //         $webhookExists = \App\Models\PaymentWebhook::forProvider('stripe')->active()->exists();

        //         // If no webhook is configured, try to set it up automatically
        //         if (!$webhookExists && get_setting('payment.stripe_enabled')) {
        //             app(StripeWebhookService::class)->ensureWebhooksAreConfigured();
        //         }
        //     } catch (\Exception $e) {
        //         // Just log the error, don't prevent application from running
        //         \Illuminate\Support\Facades\Log::warning(
        //             'Failed to automatically configure Stripe webhooks: ' . $e->getMessage()
        //         );
        //     }
        // })->delay(now()->addSeconds(10));
    }
}
