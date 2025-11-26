<?php

namespace App\Services\pusher;

use Pusher\Pusher;

/**
 * Pusher Real-Time Communication Service
 *
 * Manages real-time WebSocket communication for the WhatsApp SaaS multi-tenant
 * application using Pusher Channels. Provides secure, tenant-aware real-time
 * messaging capabilities for chat notifications, status updates, and live features.
 *
 * Key Features:
 * - Tenant-specific Pusher configuration
 * - Real-time message broadcasting
 * - Batch event processing
 * - Connection resilience and retry logic
 * - Presence channel authentication
 * - Channel information retrieval
 * - Connection health monitoring
 * - Error handling and graceful degradation
 *
 * Real-Time Use Cases:
 * - WhatsApp message notifications
 * - Chat conversation updates
 * - Campaign status broadcasts
 * - User presence indicators
 * - System status notifications
 * - Live dashboard updates
 *
 * The service automatically handles tenant context switching and maintains
 * separate Pusher configurations for each tenant, ensuring proper isolation
 * and security in the multi-tenant environment.
 *
 * Usage Example:
 * ```php
 * // Initialize for current tenant
 * $pusher = new PusherService();
 *
 * // Trigger real-time notification
 * $result = $pusher->trigger(
 *     'tenant-123-chat',
 *     'new-message',
 *     ['message' => 'Hello from WhatsApp!', 'sender' => 'Customer']
 * );
 *
 * // Batch multiple events
 * $events = [
 *     ['channel' => 'tenant-123-notifications', 'name' => 'status-update', 'data' => [...]],
 *     ['channel' => 'tenant-123-dashboard', 'name' => 'metrics-update', 'data' => [...]]
 * ];
 * $pusher->triggerBatch($events);
 * ```
 *
 * @author WhatsApp SaaS Team
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 * @see \Pusher\Pusher For underlying Pusher SDK
 * @see tenant_settings_by_group() For tenant configuration
 * @see \App\Http\Controllers\Tenant\ManageChat For chat integration
 */
class PusherService
{
    /**
     * The Pusher client instance.
     *
     * Null when Pusher is not properly configured or initialization fails.
     * Contains the authenticated Pusher client for the current tenant.
     *
     * @var \Pusher\Pusher|null Pusher client instance
     */
    protected ?Pusher $pusher = null;

    /**
     * Number of connection retry attempts.
     *
     * Tracks failed initialization attempts to prevent infinite retry loops
     * and implement exponential backoff for connection failures.
     *
     * @var int Connection retry counter
     */
    protected int $connectionRetries = 0;

    /**
     * Maximum allowed connection retry attempts.
     *
     * Prevents infinite retry loops when Pusher configuration is invalid
     * or Pusher services are unavailable.
     *
     * @var int Maximum retry attempts
     */
    protected const MAX_RETRIES = 3;

    /**
     * Create a new Pusher service instance.
     *
     * Initializes the Pusher client with tenant-specific configuration.
     * If no tenant ID is provided, uses the current tenant context.
     *
     * @param  int|string|null  $tenant_id  The tenant ID for configuration lookup
     *
     * @example
     * ```php
     * // Use current tenant
     * $pusher = new PusherService();
     *
     * // Use specific tenant
     * $pusher = new PusherService(123);
     * ```
     */
    public function __construct($tenant_id = null)
    {
        if (empty($tenant_id)) {
            $tenant_id = tenant_id();
        }
        $this->initializePusher($tenant_id);
    }

    /**
     * Initialize the Pusher client with improved error handling.
     *
     * Retrieves tenant-specific Pusher configuration and creates a new
     * Pusher client instance. Implements retry logic for connection failures
     * and validates all required configuration parameters.
     *
     * @param  int|string  $tenant_id  The tenant ID for configuration lookup
     *
     * @throws \Exception When Pusher initialization fails repeatedly
     *
     * @example
     * ```php
     * // Reinitialize with different tenant
     * $this->initializePusher(456);
     * ```
     *
     * @see tenant_settings_by_group() For configuration retrieval
     */
    protected function initializePusher($tenant_id): void
    {
        if ($this->connectionRetries >= self::MAX_RETRIES) {
            return;
        }

        try {
            $pusher_settings = tenant_settings_by_group('pusher', $tenant_id);
            // Get settings with appropriate fallbacks
            $appKey = $pusher_settings['app_key'];
            $appSecret = $pusher_settings['app_secret'];
            $appId = $pusher_settings['app_id'];
            $cluster = $pusher_settings['cluster'];

            // Validate required settings
            if (empty($appKey) || empty($appSecret) || empty($appId) || empty($cluster)) {
                $this->pusher = null;

                return;
            }

            // Initialize Pusher with better options
            $this->pusher = new Pusher(
                $appKey,
                $appSecret,
                $appId,
                [
                    'cluster' => $cluster,
                    'useTLS' => true,
                    'host' => "api-{$cluster}.pusher.com", // Explicitly set the host
                    'port' => 443,
                    'scheme' => 'https',
                    'encrypted' => true,
                    'timeout' => 30,
                    'debug' => config('app.debug', false),
                ]
            );

            $this->connectionRetries = 0;
        } catch (\Exception $e) {
            $this->pusher = null;
            $this->connectionRetries++;
        }
    }

    /**
     * Trigger an event on a specific channel.
     *
     * Broadcasts a real-time event to all subscribers of the specified channel.
     * Returns status information indicating success or failure of the operation.
     * Automatically handles connection issues with retry logic.
     *
     * @param  string  $channel  The channel name to broadcast to
     * @param  string  $event  The event name to trigger
     * @param  array  $data  The data payload to send with the event
     * @return array Status array with 'status' (bool) and 'message' (string)
     *
     * @example
     * ```php
     * // Trigger chat message notification
     * $result = $pusher->trigger(
     *     'tenant-123-chat-room-456',
     *     'new-message',
     *     [
     *         'message_id' => 789,
     *         'sender' => 'Customer',
     *         'content' => 'Hello!',
     *         'timestamp' => now()->toISOString()
     *     ]
     * );
     *
     * if ($result['status']) {
     *     Log::info('Message broadcast successful');
     * } else {
     *     Log::error('Broadcast failed: ' . $result['message']);
     * }
     * ```
     *
     * @see isPusherReady() For connection status checking
     */
    public function trigger(string $channel, string $event, array $data): array
    {
        if (! $this->isPusherReady()) {
            return ['status' => false, 'message' => 'Pusher initialization failed'];
        }

        try {
            // Always pass an array as the 4th parameter
            $this->pusher->trigger($channel, $event, $data, []);

            return ['status' => true, 'message' => 'Pusher connection test successful'];
        } catch (\Exception $e) {

            // Try to reinitialize on connection issues
            if (strpos($e->getMessage(), 'cURL error 28') !== false || strpos($e->getMessage(), 'Connection') !== false || strpos($e->getMessage(), 'Unable to parse URI') !== false) {
                $this->initializePusher(tenant_id());
            }

            return ['status' => false, 'message' => 'Pusher trigger failed: '.$e->getMessage()];
        }
    }

    /**
     * Trigger multiple events simultaneously.
     *
     * Efficiently broadcasts multiple events in a single API call to Pusher.
     * Useful for sending related notifications or updates that should arrive
     * together for better user experience and reduced API overhead.
     *
     * @param  array  $events  Array of event objects with 'channel', 'name', and 'data' keys
     * @return bool True if all events were sent successfully, false otherwise
     *
     * @example
     * ```php
     * $events = [
     *     [
     *         'channel' => 'tenant-123-notifications',
     *         'name' => 'campaign-started',
     *         'data' => ['campaign_id' => 456, 'status' => 'running']
     *     ],
     *     [
     *         'channel' => 'tenant-123-dashboard',
     *         'name' => 'metrics-update',
     *         'data' => ['active_campaigns' => 5, 'messages_sent' => 1200]
     *     ]
     * ];
     *
     * if ($pusher->triggerBatch($events)) {
     *     Log::info('Batch events sent successfully');
     * }
     * ```
     *
     * @see trigger() For single event broadcasting
     */
    public function triggerBatch(array $events): bool
    {
        try {
            if (! $this->isPusherReady()) {
                return false;
            }

            // Always pass an array as the 2nd parameter
            $this->pusher->triggerBatch($events, []);

            return true;
        } catch (\Exception $e) {

            return false;
        }
    }

    /**
     * Authenticate a user for presence channels.
     *
     * Generates authentication signature for users joining presence channels.
     * Presence channels allow tracking of who is currently subscribed and
     * enable features like "user is typing" indicators and online status.
     *
     * @param  string  $socketId  The client's socket ID from Pusher
     * @param  array  $channelData  User information for presence channel
     * @return string Authentication signature for the presence channel
     *
     * @throws \RuntimeException If Pusher is not properly initialized
     *
     * @example
     * ```php
     * // Authenticate user for chat presence
     * $auth = $pusher->authenticateUser(
     *     $request->input('socket_id'),
     *     [
     *         'user_id' => auth()->id(),
     *         'user_info' => [
     *             'name' => auth()->user()->name,
     *             'avatar' => auth()->user()->avatar_url
     *         ]
     *     ]
     * );
     *
     * return response($auth);
     * ```
     *
     * @see getChannelInfo() For channel statistics
     */
    public function authenticateUser(string $socketId, array $channelData): string
    {
        if (! $this->isPusherReady()) {
            throw new \RuntimeException('Pusher not initialized');
        }

        return $this->pusher->authenticateUser($socketId, $channelData);
    }

    /**
     * Get information about a specific channel.
     *
     * Retrieves channel statistics including subscriber count and presence
     * information. Useful for monitoring channel activity and implementing
     * features based on channel occupancy.
     *
     * @param  string  $channel  The channel name to get information for
     * @return mixed Channel information object from Pusher
     *
     * @throws \RuntimeException If Pusher is not properly initialized
     *
     * @example
     * ```php
     * $info = $pusher->getChannelInfo('tenant-123-chat-room-456');
     * $subscriberCount = $info->subscription_count ?? 0;
     *
     * if ($subscriberCount > 0) {
     *     // Channel has active subscribers
     * }
     * ```
     */
    public function getChannelInfo(string $channel)
    {
        if (! $this->isPusherReady()) {
            throw new \RuntimeException('Pusher not initialized');
        }

        return $this->pusher->getChannelInfo($channel);
    }

    /**
     * Check if the Pusher client is ready for use.
     *
     * Determines whether the Pusher client has been successfully initialized
     * and is available for broadcasting events. Should be called before
     * attempting to use Pusher functionality.
     *
     * @return bool True if Pusher is initialized and ready, false otherwise
     *
     * @example
     * ```php
     * if ($pusher->isPusherReady()) {
     *     $pusher->trigger($channel, $event, $data);
     * } else {
     *     // Fallback to alternative notification method
     *     Mail::send($notificationEmail);
     * }
     * ```
     */
    public function isPusherReady(): bool
    {
        return $this->pusher !== null;
    }

    /**
     * Test the Pusher connection and configuration.
     *
     * Performs a connection test by sending a test event to verify that
     * Pusher credentials are valid and the service is accessible. Useful
     * for configuration validation and health checks.
     *
     * @return array Test result with 'status' (bool), 'message' (string), and optional 'details'
     *
     * @example
     * ```php
     * $test = $pusher->testConnection();
     *
     * if ($test['status']) {
     *     echo "Pusher is working: " . $test['message'];
     * } else {
     *     echo "Pusher failed: " . $test['message'];
     *     if (isset($test['details'])) {
     *         Log::debug('Pusher test details', $test['details']);
     *     }
     * }
     * ```
     *
     * @see isPusherReady() For basic readiness check
     */
    public function testConnection(): array
    {
        if (! $this->isPusherReady()) {
            return [
                'status' => false,
                'message' => 'Pusher not initialized - please check your Pusher configuration',
            ];
        }

        try {
            $result = $this->pusher->trigger('test-channel', 'test-event', ['message' => 'Connection test'], []);

            if (isset($result['status']) && $result['status'] === 200) {
                return [
                    'status' => true,
                    'message' => 'Pusher connection test successful!',
                ];
            }

            return [
                'status' => false,
                'message' => 'Pusher connection test failed',
                'details' => $result,
            ];
        } catch (\Exception $e) {
            // If the error indicates missing or invalid configuration, provide a clearer message
            if (strpos($e->getMessage(), 'Unable to parse URI') !== false) {
                return [
                    'status' => false,
                    'message' => 'Pusher connection failed: Invalid configuration. Please check your Pusher key, secret, app ID, and cluster settings.',
                ];
            }

            return [
                'status' => false,
                'message' => 'Pusher test connection failed: '.$e->getMessage(),
            ];
        }
    }
}
