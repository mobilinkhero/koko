<?php

namespace App\Services\Billing;

use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use Closure;
use Exception;
use Illuminate\Support\Collection;

/**
 * Billing Manager Service
 *
 * Central registry and manager for payment gateways in the WhatsApp SaaS
 * multi-tenant application. Provides gateway registration, resolution,
 * and management capabilities for all payment processing services.
 *
 * Key Features:
 * - Payment gateway registration and resolution
 * - Gateway instance management and caching
 * - Active gateway filtering and selection
 * - Auto-billing gateway identification
 * - Gateway validation and interface enforcement
 * - Multi-tenant billing support
 *
 * The BillingManager acts as a factory and registry for payment gateways,
 * enabling the application to support multiple payment methods while
 * maintaining a consistent interface for billing operations.
 *
 * Supported Gateway Types:
 * - Stripe (online credit card processing)
 * - Offline (manual bank transfers, checks)
 * - PayPal (future implementation)
 * - Custom gateway implementations
 *
 * Usage Example:
 * ```php
 * $billingManager = app(BillingManager::class);
 *
 * // Register a gateway
 * $billingManager->register('stripe', function() {
 *     return new StripePaymentGateway($config);
 * });
 *
 * // Get active gateways
 * $activeGateways = $billingManager->getActiveGateways();
 *
 * // Get a specific gateway
 * $stripe = $billingManager->gateway('stripe');
 * ```
 *
 * @author WhatsApp SaaS Team
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 * @see PaymentGatewayInterface For gateway contract requirements
 * @see StripePaymentGateway For online payment implementation
 * @see OfflinePaymentGateway For manual payment implementation
 */
class BillingManager
{
    /**
     * The registered payment gateway factories.
     *
     * Maps gateway names to their factory closures. Each closure
     * returns a configured payment gateway instance when called.
     * Supports lazy loading for better performance.
     *
     * @var array<string, \Closure> Gateway name to factory mapping
     */
    protected $gateways = [];

    /**
     * The resolved gateway instances cache.
     *
     * Stores instantiated payment gateways to avoid recreating
     * them on subsequent requests. Improves performance and
     * maintains gateway state across operations.
     *
     * @var array<string, PaymentGatewayInterface> Gateway instances cache
     */
    protected $instances = [];

    /**
     * Register a payment gateway factory.
     *
     * Registers a closure that creates a payment gateway instance when called.
     * The gateway is not instantiated immediately, allowing for lazy loading
     * and better resource management.
     *
     * @param  string  $name  The unique gateway identifier
     * @param  \Closure  $callback  Factory closure that returns gateway instance
     *
     * @example
     * ```php
     * $billingManager->register('stripe', function() {
     *     return new StripePaymentGateway(
     *         config('services.stripe_key'),
     *         config('services.stripe_secret')
     *     );
     * });
     *
     * $billingManager->register('offline', function() {
     *     return new OfflinePaymentGateway(
     *         setting('offline_payment_instructions')
     *     );
     * });
     * ```
     *
     * @see gateway() For gateway resolution
     */
    public function register(string $name, Closure $callback): void
    {
        $this->gateways[$name] = $callback;
    }

    /**
     * Get a payment gateway instance.
     *
     * Resolves and returns a payment gateway instance by name. If the gateway
     * has been resolved before, returns the cached instance. Otherwise,
     * calls the factory closure to create a new instance.
     *
     * @param  string  $name  The gateway identifier
     * @return PaymentGatewayInterface The resolved gateway instance
     *
     * @throws \Exception If gateway is not registered or invalid
     *
     * @example
     * ```php
     * try {
     *     $stripe = $billingManager->gateway('stripe');
     *     $checkoutUrl = $stripe->getCheckoutUrl($invoice);
     * } catch (Exception $e) {
     *     // Handle gateway not found or misconfigured
     * }
     * ```
     *
     * @see register() For gateway registration
     * @see PaymentGatewayInterface For required gateway methods
     */
    public function gateway(string $name): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$name])) {
            throw new Exception("Payment gateway [{$name}] is not registered.");
        }

        // Return the instance if already resolved
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Resolve the gateway
        $gateway = call_user_func($this->gateways[$name]);

        // Ensure the gateway implements the correct interface
        if (! ($gateway instanceof PaymentGatewayInterface)) {
            throw new Exception("Payment gateway [{$name}] must implement PaymentGatewayInterface.");
        }

        // Store the instance
        $this->instances[$name] = $gateway;

        return $gateway;
    }

    /**
     * Get all registered gateway names.
     *
     * Returns an array of all gateway identifiers that have been
     * registered with the billing manager, regardless of their
     * active status or configuration.
     *
     * @return array<string> List of registered gateway names
     *
     * @example
     * ```php
     * $gateways = $billingManager->getRegisteredGateways();
     * // Returns: ['stripe', 'offline', 'paypal']
     * ```
     *
     * @see hasGateway() For checking specific gateway existence
     */
    public function getRegisteredGateways(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Get all active payment gateways.
     *
     * Filters and returns only the payment gateways that are currently
     * active and available for processing payments. Each gateway's
     * isActive() method is called to determine status.
     *
     * @return \Illuminate\Support\Collection<string, PaymentGatewayInterface> Active gateways collection
     *
     * @throws \Exception If gateway resolution fails
     *
     * @example
     * ```php
     * $activeGateways = $billingManager->getActiveGateways();
     * foreach ($activeGateways as $name => $gateway) {
     *     echo "Gateway: {$name} - {$gateway->getName()}";
     * }
     * ```
     *
     * @see hasActiveGateways() For checking availability
     * @see PaymentGatewayInterface::isActive() For gateway status
     */
    public function getActiveGateways(): Collection
    {
        $gateways = new Collection;

        foreach ($this->getRegisteredGateways() as $name) {
            $gateway = $this->gateway($name);

            if ($gateway->isActive()) {
                $gateways->put($name, $gateway);
            }
        }

        return $gateways;
    }

    /**
     * Get all active payment gateways that support auto billing.
     *
     * Returns only the active gateways that can automatically charge
     * customers for recurring subscriptions. Useful for subscription
     * management and automated billing operations.
     *
     * @return \Illuminate\Support\Collection<string, PaymentGatewayInterface> Auto-billing gateways
     *
     * @example
     * ```php
     * $autoBillingGateways = $billingManager->getAutoBillingGateways();
     * if ($autoBillingGateways->isNotEmpty()) {
     *     // Process recurring subscription charges
     * }
     * ```
     *
     * @see hasAutoBillingGateways() For availability check
     * @see PaymentGatewayInterface::supportsAutoBilling() For gateway capability
     */
    public function getAutoBillingGateways(): Collection
    {
        return $this->getActiveGateways()->filter(function ($gateway) {
            return $gateway->supportsAutoBilling();
        });
    }

    /**
     * Determine if the specified gateway exists.
     *
     * Checks whether a payment gateway with the given name has been
     * registered with the billing manager. Does not check if the
     * gateway is active or properly configured.
     *
     * @param  string  $name  The gateway identifier to check
     * @return bool True if gateway is registered, false otherwise
     *
     * @example
     * ```php
     * if ($billingManager->hasGateway('stripe')) {
     *     $stripe = $billingManager->gateway('stripe');
     * }
     * ```
     *
     * @see getRegisteredGateways() For all registered gateways
     */
    public function hasGateway(string $name): bool
    {
        return isset($this->gateways[$name]);
    }

    /**
     * Determine if any active payment gateways are available.
     *
     * Checks if there are any payment gateways currently active
     * and ready to process payments. Useful for determining if
     * payment processing is available in the application.
     *
     * @return bool True if active gateways exist, false otherwise
     *
     * @example
     * ```php
     * if ($billingManager->hasActiveGateways()) {
     *     // Show payment options to customer
     * } else {
     *     // Display payment unavailable message
     * }
     * ```
     *
     * @see getActiveGateways() For retrieving active gateways
     */
    public function hasActiveGateways(): bool
    {
        return $this->getActiveGateways()->isNotEmpty();
    }

    /**
     * Determine if any active auto-billing gateways are available.
     *
     * Checks if there are any payment gateways that support automatic
     * billing for subscriptions. Essential for recurring payment
     * processing and subscription management.
     *
     * @return bool True if auto-billing gateways exist, false otherwise
     *
     * @example
     * ```php
     * if ($billingManager->hasAutoBillingGateways()) {
     *     // Enable subscription auto-renewal
     * } else {
     *     // Require manual payment renewal
     * }
     * ```
     *
     * @see getAutoBillingGateways() For retrieving auto-billing gateways
     */
    public function hasAutoBillingGateways(): bool
    {
        return $this->getAutoBillingGateways()->isNotEmpty();
    }
}
