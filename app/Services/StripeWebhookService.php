<?php

namespace App\Services;

use App\Models\PaymentWebhook;
use Corbital\Settings\Facades\Settings;
use Exception;

/**
 * Stripe Webhook Service
 *
 * Manages Stripe webhook configuration, registration, and lifecycle management for the multi-tenant SaaS application.
 * This service ensures proper webhook setup for payment processing events and maintains webhook integrity.
 *
 * Key Responsibilities:
 * - Webhook endpoint registration with Stripe
 * - Event subscription management
 * - Webhook validation and verification
 * - Automatic webhook updates and synchronization
 * - Error handling and logging for webhook operations
 * - Multi-tenant webhook configuration
 *
 * Supported Webhook Events:
 * - checkout.session.completed (successful payments)
 * - customer.subscription.* (subscription lifecycle)
 * - invoice.payment_* (payment processing)
 * - payment_intent.* (payment intent events)
 * - subscription_schedule.* (scheduled subscription changes)
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Models\PaymentWebhook
 * @see \Stripe\Webhook
 * @see https://stripe.com/docs/webhooks
 *
 * @example
 * ```php
 * // Configure default webhooks
 * $webhookService = app(StripeWebhookService::class);
 * $result = $webhookService->ensureWebhooksAreConfigured();
 *
 * if ($result['success']) {
 *     echo "Webhook configured: " . $result['webhook']->endpoint_url;
 * }
 *
 * // Configure custom webhook with specific events
 * $customEvents = ['checkout.session.completed', 'invoice.payment_succeeded'];
 * $result = $webhookService->ensureWebhooksAreConfigured(
 *     'https://myapp.com/webhooks/stripe',
 *     $customEvents
 * );
 * ```
 */
class StripeWebhookService
{
    /**
     * Default webhook events to register
     *
     * List of Stripe webhook events that are essential for proper payment processing
     * and subscription management in the multi-tenant application.
     *
     * @var array
     */
    protected const DEFAULT_EVENTS = [
        'checkout.session.completed',
        'customer.deleted',
        'customer.subscription.created',
        'customer.subscription.deleted',
        'customer.subscription.updated',
        'invoice.payment_action_required',
        'invoice.payment_failed',
        'invoice.payment_succeeded',
        'payment_intent.payment_failed',
        'payment_intent.succeeded',
        'subscription_schedule.canceled',
        'subscription_schedule.created',
        'subscription_schedule.updated',
    ];

    /**
     * Stripe API base URL
     *
     * @var string
     */
    protected $baseUrl = 'https://api.stripe.com/v1/';

    /**
     * Stripe secret key for API authentication
     *
     * @var string|null
     */
    protected $stripeSecret;

    /**
     * Create a new service instance.
     *
     * Initializes the Stripe webhook service without requiring immediate configuration.
     * The service will retrieve Stripe credentials when needed from application settings.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Set up or ensure webhooks are properly configured
     *
     * Configures Stripe webhooks for the application, either creating new ones or updating
     * existing configurations. Handles endpoint URL validation, event subscription, and
     * maintains webhook consistency across deployments.
     *
     * @param  string|null  $endpointUrl  Custom webhook URL or null to use default route
     * @param  array|null  $events  Events to listen for or null to use defaults
     * @return array Result array with success status, message, and webhook data
     *
     * @example
     * ```php
     * // Use default configuration
     * $result = $this->webhookService->ensureWebhooksAreConfigured();
     *
     * // Custom endpoint with specific events
     * $result = $this->webhookService->ensureWebhooksAreConfigured(
     *     'https://app.example.com/webhooks/stripe',
     *     ['checkout.session.completed', 'invoice.payment_succeeded']
     * );
     *
     * // Check result
     * if ($result['success']) {
     *     $webhook = $result['webhook'];
     *     echo "Webhook ID: {$webhook->stripe_webhook_id}";
     *     echo "Secret: {$webhook->webhook_secret}";
     * } else {
     *     echo "Error: {$result['message']}";
     * }
     * ```
     *
     * @throws Exception When Stripe API key is not configured
     *
     * @see createWebhook()
     * @see updateWebhook()
     * @see PaymentWebhook::forProvider()
     */
    public function ensureWebhooksAreConfigured(?string $endpointUrl = null, ?array $events = null): array
    {

        try {
            // Check if we have valid credentials

            $settings = get_batch_settings(['payment.stripe_secret']);
            if (empty($settings['payment.stripe_secret'])) {
                throw new Exception('Stripe API key is not configured.');
            }

            // Set the endpoint URL
            if (empty($endpointUrl)) {
                $endpointUrl = route('webhook.stripe');
            }

            // Set events to register
            $eventsToRegister = $events ?? self::DEFAULT_EVENTS;

            // Check if we already have a webhook registered
            $existingWebhook = PaymentWebhook::forProvider('stripe')
                ->active()
                ->first();

            if ($existingWebhook) {
                // Check if we need to update it
                if (
                    $existingWebhook->endpoint_url !== $endpointUrl || array_diff($eventsToRegister, $existingWebhook->getEventsArray()) || array_diff($existingWebhook->getEventsArray(), $eventsToRegister)
                ) {

                    // Update existing webhook in Stripe
                    return $this->updateWebhook($existingWebhook, $endpointUrl, $eventsToRegister);
                }

                // No changes needed
                return [
                    'success' => true,
                    'message' => t('Webhook_already_properly_configured'),
                    'webhook' => $existingWebhook,
                ];
            }

            // Create new webhook
            return $this->createWebhook($endpointUrl, $eventsToRegister);
        } catch (Exception $e) {
            payment_log('Failed to configure Stripe webhook', 'error', [
                'tenant_id' => tenant_id(),
                'endpoint' => $endpointUrl,
                'events' => $eventsToRegister ?? self::DEFAULT_EVENTS,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to configure webhook: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create a new webhook in Stripe and database
     */
    protected function createWebhook(string $endpointUrl, array $events): array
    {
        $settings = get_batch_settings(['payment.stripe_secret']);
        $stripe = new \Stripe\StripeClient($settings['payment.stripe_secret']);
        $webhookData = $stripe->webhookEndpoints->create([
            'enabled_events' => $events,
            'url' => $endpointUrl,
            'api_version' => '2023-10-16',
            'description' => 'Automatically created webhook for '.config('app.name'),
        ]);

        if (! $webhookData || ! isset($webhookData->id)) {
            throw new Exception('Failed to create Stripe webhook: '.($webhookData->error->message ?? 'Unknown error'));
        }

        // Save webhook to database
        $webhook = PaymentWebhook::create([
            'provider' => 'stripe',
            'webhook_id' => $webhookData->id,
            'endpoint_url' => $endpointUrl,
            'secret' => $webhookData->secret,
            'is_active' => true,
            'events' => $events,
            'metadata' => [
                'created_at' => now()->toIso8601String(),
                'api_version' => $webhookData['api_version'] ?? '2023-10-16',
            ],
            'last_pinged_at' => now(),
        ]);

        // Update webhook settings for easy access
        set_settings_batch('payment', [
            'stripe_webhook_id' => $webhookData->id,
            'stripe_webhook_secret' => $webhookData->secret,
        ]);

        return [
            'success' => true,
            'message' => t('webhook_created_successfully'),
            'webhook' => $webhook,
        ];
    }

    /**
     * Update an existing webhook
     */
    protected function updateWebhook(PaymentWebhook $webhook, string $endpointUrl, array $events): array
    {
        $settings = get_batch_settings(['payment.stripe_secret']);
        $stripe = new \Stripe\StripeClient($settings['payment.stripe_secret']);

        $webhookData = $stripe->webhookEndpoints->update(
            $webhook->webhook_id,
            [
                'enabled_events' => $events,
                'url' => $endpointUrl,
            ]
        );

        if (! $webhookData || ! isset($webhookData->id)) {
            throw new Exception('Failed to create Stripe webhook: '.($webhookData->error->message ?? 'Unknown error'));
        }

        // Update webhook in database
        $webhook->update([
            'endpoint_url' => $endpointUrl,
            'events' => $events,
            'metadata' => array_merge($webhook->metadata ?? [], [
                'updated_at' => now()->toIso8601String(),
            ]),
            'last_pinged_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => t('webhook_updated_successfully'),
            'webhook' => $webhook,
        ];
    }

    /**
     * Delete a webhook
     */
    public function deleteWebhook(string $webhookId): array
    {
        try {

            $settings = get_batch_settings(['payment.stripe_secret']);
            $stripe = new \Stripe\StripeClient($settings['payment.stripe_secret']);

            $deleted = $stripe->webhookEndpoints->delete($webhookId, []);

            if (! $deleted || ! isset($deleted->id)) {
                throw new Exception('Failed to delete Stripe webhook: '.($deleted->error->message ?? 'Unknown error'));
            }

            // Delete from database
            $webhook = PaymentWebhook::where('webhook_id', $webhookId)->first();
            if ($webhook) {
                $webhook->delete();
            }

            // Clear settings
            $settings = get_batch_settings(['payment.stripe_webhook_id', 'payment.stripe_webhook_secret']);
            if ($settings['payment.stripe_webhook_id'] === $webhookId) {
                set_setting('payment.stripe_webhook_id', '');
                set_setting('payment.stripe_webhook_secret', '');
            }

            return [
                'success' => true,
                'message' => t('webhook_deleted_successfully'),
            ];
        } catch (Exception $e) {
            payment_log('Failed to delete Stripe webhook:', 'error', [
                'tenant_id' => tenant_id(),
                'webhook_id' => $webhookId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => t('failed_to_delete_webhook ').$e->getMessage(),
            ];
        }
    }

    /**
     * List all webhooks from Stripe
     */
    public function listWebhooks(): array
    {
        try {
            $settings = get_batch_settings(['payment.stripe_secret']);
            $stripe = new \Stripe\StripeClient($settings['payment.stripe_secret']);

            $webhookData = $stripe->webhookEndpoints->all();

            if (! $webhookData || ! isset($webhookData->data)) {
                throw new Exception('Failed to list Stripe webhooks: '.($webhookData->error->message ?? 'Unknown error'));
            }

            $webhookList = array_map(function ($webhook) {
                return $webhook->toArray();
            }, $webhookData->data);

            return [
                'success' => true,
                'webhooks' => $webhookList,
            ];

        } catch (Exception $e) {
            payment_log('Failed to list Stripe webhooks:', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => t('failed_to_list_webhooks ').$e->getMessage(),
                'webhooks' => [],
            ];
        }
    }

    /**
     * Get all products from Stripe
     *
     * @param  bool  $active  Only get active products
     * @param  int  $limit  Number of products to fetch
     */
    public function getProducts(bool $active = true, int $limit = 100): array
    {
        try {

            $settings = get_batch_settings(['payment.stripe_secret']);
            $stripe_secret = $settings['payment.stripe_secret'] ?? null;

            // Check if we have valid credentials
            if (empty($stripe_secret)) {
                throw new Exception('Stripe API key is not configured.');
            }

            $stripe = new \Stripe\StripeClient($stripe_secret);

            $options = [
                'limit' => $limit,
                'expand' => ['data.default_price'],
            ];

            if ($active) {
                $options['active'] = true;
            }

            $products = $stripe->products->all($options);

            if (! $products || ! isset($products->data)) {
                throw new Exception('Failed to list Stripe products: '.($products->error->message ?? 'Unknown error'));
            }

            // Format data for easier consumption
            $formattedProducts = array_map(function ($product) {
                $price = null;
                $currency = null;
                $interval = null;
                $interval_count = null;

                if (isset($product->default_price) && $product->default_price) {
                    $price = $product->default_price->unit_amount / 100; // Convert from cents
                    $currency = $product->default_price->currency;

                    if (isset($product->default_price->recurring)) {
                        $interval = $product->default_price->recurring->interval; // 'month' or 'year'
                        $interval_count = $product->default_price->recurring->interval_count; // 'month' or 'year'
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'active' => $product->active,
                    'images' => $product->images,
                    'metadata' => $product->metadata,
                    'price' => $price,
                    'currency' => $currency,
                    'interval' => $interval,
                    'interval_count' => $interval_count,
                    'price_id' => $product->default_price->id ?? null,
                    'created' => date('Y-m-d H:i:s', $product->created),
                    'is_featured' => $product->metadata->featured ?? false,
                    'trial_days' => $product->metadata->trial_days ?? 0,
                ];
            }, $products->data);

            return [
                'success' => true,
                'products' => $formattedProducts,
            ];
        } catch (Exception $e) {
            payment_log('Failed to list Stripe products:', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => t('failed_to_list_products ').$e->getMessage(),
                'products' => [],
            ];
        }
    }

    /**
     * Get webhook details from Stripe
     */
    public function getWebhookDetails(string $webhookId): array
    {
        try {
            $settings = get_batch_settings(['payment.stripe_secret']);
            $stripe = new \Stripe\StripeClient($settings['payment.stripe_secret']);

            $webhookData = $stripe->webhookEndpoints->retrieve(
                $webhookId,
                []
            );

            if (! $webhookData || ! isset($webhookData->id)) {
                throw new Exception('Failed to get Stripe webhook details: '.($webhookData->error->message ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'webhook' => $webhookData->toArray(),
            ];
        } catch (Exception $e) {
            payment_log('Failed to get Stripe webhook details:', 'error', [
                'tenant_id' => tenant_id(),
                'webhook_id' => $webhookId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => t('failed_to_get_webhook_details ').$e->getMessage(),
            ];
        }
    }
}
