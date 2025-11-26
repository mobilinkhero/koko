<?php

namespace App\Services;

use App\Models\PaymentWebhook;
use Exception;
use Illuminate\Support\Str;

/**
 * Razorpay Webhook Service
 *
 * Manages Razorpay webhook configuration for manual setup only.
 * This service provides webhook configuration information and validates webhook settings.
 *
 * Key Responsibilities:
 * - Provide webhook setup instructions for manual configuration
 * - Webhook validation and verification
 * - Generate webhook secrets for security
 * - Store webhook configuration locally
 *
 * Supported Webhook Events:
 * - payment.captured (successful payments)
 * - payment.failed (failed payments)
 * - order.paid (order completion)
 * - payment.authorized (authorized payments)
 * - subscription.charged (recurring payments)
 * - subscription.completed (subscription ended)
 * - refund.created (refund initiated)
 * - refund.processed (refund completed)
 *
 * Note: Razorpay webhook API is only available for Partner accounts.
 * Regular merchant accounts must configure webhooks manually through the Razorpay Dashboard.
 *
 * @author WhatsApp SaaS Team
 *
 * @since 1.0.0
 * @see \App\Models\PaymentWebhook
 * @see https://razorpay.com/docs/webhooks/
 */
class RazorpayWebhookService
{
    /**
     * Recommended webhook events for Razorpay integration
     *
     * @var array
     */
    protected const RECOMMENDED_EVENTS = [
        'payment.captured',
        'payment.failed',
        'order.paid',
        'payment.authorized',
        'subscription.charged',
        'subscription.completed',
        'refund.created',
        'refund.processed',
    ];

    /**
     * Create a new service instance.
     */
    public function __construct() {}

    /**
     * Get webhook setup information for manual configuration (replaces ensureWebhooksAreConfigured)
     */
    public function ensureWebhooksAreConfigured(?string $endpointUrl = null, ?array $events = null): array
    {
        $endpointUrl = $endpointUrl ?: route('webhook.razorpay');
        $events = $events ?: self::RECOMMENDED_EVENTS;

        return $this->createLocalWebhookRecord($endpointUrl, $events);
    }

    /**
     * Get webhook setup information for manual configuration
     */
    public function getWebhookSetupInfo(?string $endpointUrl = null, ?array $events = null): array
    {
        $endpointUrl = $endpointUrl ?: route('webhook.razorpay');
        $events = $events ?: self::RECOMMENDED_EVENTS;

        // Generate a secure webhook secret
        $webhookSecret = Str::random(32);

        return [
            'endpoint_url' => $endpointUrl,
            'webhook_secret' => $webhookSecret,
            'events' => $events,
            'setup_instructions' => [
                'step_1' => 'Login to your Razorpay Dashboard',
                'step_2' => 'Navigate to Settings > Webhooks',
                'step_3' => 'Click "Create Webhook"',
                'step_4' => 'Enter webhook URL: '.$endpointUrl,
                'step_5' => 'Enter webhook secret: '.$webhookSecret,
                'step_6' => 'Select events: '.implode(', ', $events),
                'step_7' => 'Save the webhook configuration',
                'step_8' => 'Update your application settings with the webhook secret',
            ],
        ];
    }

    /**
     * Create a local webhook record for manual setup
     */
    public function createLocalWebhookRecord(string $endpointUrl, array $events, ?string $webhookSecret = null): array
    {
        try {
            $webhookSecret = $webhookSecret ?: Str::random(32);
            $webhookId = 'manual_'.Str::random(16);

            // Check if webhook already exists
            $existingWebhook = PaymentWebhook::forProvider('razorpay')->first();

            if ($existingWebhook) {
                // Update existing webhook
                $existingWebhook->update([
                    'endpoint_url' => $endpointUrl,
                    'events' => $events,
                    'secret' => $webhookSecret,
                    'metadata' => array_merge($existingWebhook->metadata ?? [], [
                        'updated_at' => now()->toISOString(),
                        'setup_instructions' => $this->getWebhookSetupInfo($endpointUrl, $events)['setup_instructions'],
                    ]),
                ]);

                $webhook = $existingWebhook;
                $message = 'Webhook configuration updated. Please update your Razorpay Dashboard.';
            } else {
                // Create new webhook record
                $webhook = PaymentWebhook::create([
                    'provider' => 'razorpay',
                    'webhook_id' => $webhookId,
                    'endpoint_url' => $endpointUrl,
                    'secret' => $webhookSecret,
                    'is_active' => false, // Will be activated when properly configured
                    'events' => $events,
                    'metadata' => [
                        'setup_type' => 'manual',
                        'created_at' => now()->toISOString(),
                        'requires_manual_setup' => true,
                        'setup_instructions' => $this->getWebhookSetupInfo($endpointUrl, $events)['setup_instructions'],
                    ],
                ]);

                $message = 'Webhook record created. Please configure manually in Razorpay Dashboard.';
            }

            // Store webhook secret in settings for easy access
            set_settings_batch('payment', [
                'razorpay_webhook_secret' => $webhookSecret,
            ]);

            payment_log('Razorpay webhook record created/updated for manual setup', 'info', [
                'webhook_id' => $webhook->webhook_id,
                'endpoint_url' => $endpointUrl,
                'events' => $events,
            ]);

            return [
                'success' => true,
                'message' => $message,
                'webhook' => $webhook,
                'setup_info' => $this->getWebhookSetupInfo($endpointUrl, $events),
            ];

        } catch (Exception $e) {
            payment_log('Failed to create manual Razorpay webhook record', 'error', [
                'endpoint' => $endpointUrl,
                'events' => $events,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create webhook record: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get existing webhook configuration
     */
    public function getExistingWebhook(): ?PaymentWebhook
    {
        return PaymentWebhook::forProvider('razorpay')->first();
    }

    /**
     * Validate webhook configuration
     */
    public function validateWebhookConfiguration(): array
    {
        $webhook = $this->getExistingWebhook();
        $settings = get_batch_settings(['payment.razorpay_webhook_secret']);

        if (! $webhook) {
            return [
                'configured' => false,
                'message' => 'No webhook configuration found. Please create a webhook setup.',
            ];
        }

        if (empty($settings['payment.razorpay_webhook_secret'])) {
            return [
                'configured' => false,
                'message' => 'Webhook secret not configured in application settings.',
            ];
        }

        return [
            'configured' => true,
            'webhook' => $webhook,
            'message' => 'Webhook configuration appears to be set up.',
        ];
    }

    /**
     * Get recommended events for webhook configuration
     */
    public function getRecommendedEvents(): array
    {
        return [
            'payment.captured' => 'Payment Successfully Captured',
            'payment.failed' => 'Payment Failed',
            'payment.authorized' => 'Payment Authorized (not captured)',
            'order.paid' => 'Order Fully Paid',
            'subscription.charged' => 'Subscription Charged (Recurring)',
            'subscription.completed' => 'Subscription Completed/Ended',
            'refund.created' => 'Refund Initiated',
            'refund.processed' => 'Refund Processed',
        ];
    }

    /**
     * Create a manual webhook record
     *
     * This is the only supported method for creating webhooks as Razorpay's webhook API
     * is not available for regular merchant accounts.
     */
    public function createManualWebhook(string $endpointUrl, array $events): array
    {
        // Create a local webhook record for manual setup
        return $this->createLocalWebhookRecord($endpointUrl, $events);
    }

    /**
     * List webhooks
     */
    public function listWebhooks(): array
    {
        return [
            'success' => false,
            'message' => 'Webhook API not available for regular Razorpay accounts. Please configure manually.',
            'webhooks' => [],
            'requires_manual_setup' => true,
        ];
    }

    /**
     * Get webhook details
     */
    public function getWebhookDetails(string $webhookId): array
    {
        return [
            'success' => false,
            'message' => 'Webhook API not available for regular Razorpay accounts. Please configure manually.',
            'requires_manual_setup' => true,
        ];
    }

    /**
     * Delete webhook configuration
     */
    public function deleteWebhook(string $webhookId): array
    {
        try {
            $webhook = PaymentWebhook::where('provider', 'razorpay')
                ->where('webhook_id', $webhookId)
                ->first();

            if ($webhook) {
                $webhook->delete();
            }

            // Clear webhook secret from settings
            set_settings_batch('payment', [
                'razorpay_webhook_secret' => null,
            ]);

            payment_log('Razorpay webhook configuration deleted', 'info', [
                'webhook_id' => $webhookId,
            ]);

            return [
                'success' => true,
                'message' => 'Webhook configuration deleted. Please also remove the webhook from your Razorpay Dashboard.',
            ];

        } catch (Exception $e) {
            payment_log('Failed to delete Razorpay webhook configuration', 'error', [
                'webhook_id' => $webhookId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete webhook configuration: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature from Razorpay
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $secret = null): bool
    {
        try {
            // Get webhook secret from settings if not provided
            $webhookSecret = $secret ?: get_setting('payment.razorpay_webhook_secret');

            if (empty($webhookSecret)) {
                payment_log('Razorpay webhook verification failed: No webhook secret found', 'error');

                return false;
            }

            // Generate expected signature
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

            // Compare with provided signature
            $isValid = hash_equals($expectedSignature, $signature);

            if (! $isValid) {
                payment_log('Razorpay webhook signature verification failed', 'error');
            }

            return $isValid;
        } catch (Exception $e) {
            payment_log('Razorpay webhook verification error: '.$e->getMessage(), 'error');

            return false;
        }
    }
}
