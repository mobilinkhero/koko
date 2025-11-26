<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Webhook Management
 *
 * APIs for handling WhatsApp webhook events and verification
 */
class WebhookController extends Controller
{
    /**
     * WhatsApp Webhook Handler
     *
     * Handle incoming WhatsApp webhook events from Meta.
     *
     * @unauthenticated
     *
     * @urlParam provider string required WhatsApp provider identifier. Example: meta
     *
     * @bodyParam object object Webhook payload from Meta.
     * @bodyParam object.entry array Array of webhook entries.
     * @bodyParam object.entry.*.id string Webhook entry ID.
     * @bodyParam object.entry.*.changes array Array of changes in the entry.
     * @bodyParam object.entry.*.changes.*.value object The change value containing event data.
     * @bodyParam object.entry.*.changes.*.field string The field that changed (messages, message_deliveries, etc.).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Webhook processed successfully",
     *   "data": {
     *     "processed_events": 2,
     *     "event_types": ["message_received", "message_delivered"],
     *     "processing_time_ms": 45
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Invalid webhook payload"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Webhook signature verification failed"
     * }
     */
    public function whatsapp(Request $request, string $provider): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Verify webhook signature (in production)
            if (! $this->verifyWebhookSignature($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook signature verification failed',
                ], 401);
            }

            $payload = $request->all();

            if (! isset($payload['object']) || $payload['object'] !== 'whatsapp_business_account') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook payload',
                ], 400);
            }

            $processedEvents = 0;
            $eventTypes = [];

            if (isset($payload['entry'])) {
                foreach ($payload['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            $eventType = $this->processWebhookChange($change, $provider);
                            if ($eventType) {
                                $processedEvents++;
                                $eventTypes[] = $eventType;
                            }
                        }
                    }
                }
            }

            $processingTime = round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => [
                    'processed_events' => $processedEvents,
                    'event_types' => array_unique($eventTypes),
                    'processing_time_ms' => $processingTime,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Verify Webhook
     *
     * Verify WhatsApp webhook endpoint during setup.
     *
     * @unauthenticated
     *
     * @urlParam provider string required WhatsApp provider identifier. Example: meta
     *
     * @queryParam hub.mode string required Verification mode. Example: subscribe
     * @queryParam hub.challenge string required Challenge string from Meta. Example: 1234567890
     * @queryParam hub.verify_token string required Verification token. Example: my_verify_token
     *
     * @response 200 1234567890
     * @response 403 {
     *   "error": "Forbidden",
     *   "message": "Invalid verify token"
     * }
     */
    public function verifyWebhook(Request $request, string $provider)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        // Get the expected verify token from settings
        $expectedToken = $this->getExpectedVerifyToken($provider);

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response()->json([
            'error' => 'Forbidden',
            'message' => 'Invalid verify token',
        ], 403);
    }

    /**
     * Process individual webhook change
     */
    private function processWebhookChange(array $change, string $provider): ?string
    {
        $field = $change['field'] ?? null;
        $value = $change['value'] ?? [];

        switch ($field) {
            case 'messages':
                return $this->processMessageEvent($value, $provider);

            case 'message_deliveries':
                return $this->processDeliveryEvent($value, $provider);

            case 'message_reads':
                return $this->processReadEvent($value, $provider);

            case 'messaging_optins':
                return $this->processOptInEvent($value, $provider);

            case 'messaging_optouts':
                return $this->processOptOutEvent($value, $provider);

            default:
                return null;
        }
    }

    /**
     * Process incoming message events
     */
    private function processMessageEvent(array $value, string $provider): string
    {
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                // Process incoming message
                $this->handleIncomingMessage($message, $value, $provider);
            }
        }

        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                // Process message status updates
                $this->handleMessageStatus($status, $value, $provider);
            }
        }

        return 'message_received';
    }

    /**
     * Process message delivery events
     */
    private function processDeliveryEvent(array $value, string $provider): string
    {
        return 'message_delivered';
    }

    /**
     * Process message read events
     */
    private function processReadEvent(array $value, string $provider): string
    {
        return 'message_read';
    }

    /**
     * Process opt-in events
     */
    private function processOptInEvent(array $value, string $provider): string
    {
        return 'user_opted_in';
    }

    /**
     * Process opt-out events
     */
    private function processOptOutEvent(array $value, string $provider): string
    {
        return 'user_opted_out';
    }

    /**
     * Handle incoming message
     */
    private function handleIncomingMessage(array $message, array $context, string $provider): void
    {
        $messageId = $message['id'] ?? null;
        $from = $message['from'] ?? null;
        $timestamp = $message['timestamp'] ?? null;
        $type = $message['type'] ?? 'unknown';

        // Extract message content based on type
        $content = $this->extractMessageContent($message, $type);

        // Here you would typically:
        // 1. Save the message to database
        // 2. Check for bot flow triggers
        // 3. Process auto-responses
        // 4. Update contact information
        // 5. Trigger any necessary webhooks or events
    }

    /**
     * Handle message status updates
     */
    private function handleMessageStatus(array $status, array $context, string $provider): void
    {
        $messageId = $status['id'] ?? null;
        $statusType = $status['status'] ?? null;
        $timestamp = $status['timestamp'] ?? null;

        // Here you would typically update the message status in your database
    }

    /**
     * Extract message content based on type
     */
    private function extractMessageContent(array $message, string $type): array
    {
        switch ($type) {
            case 'text':
                return [
                    'type' => 'text',
                    'text' => $message['text']['body'] ?? '',
                ];

            case 'image':
            case 'video':
            case 'audio':
            case 'document':
                return [
                    'type' => $type,
                    'media_id' => $message[$type]['id'] ?? null,
                    'mime_type' => $message[$type]['mime_type'] ?? null,
                    'caption' => $message[$type]['caption'] ?? null,
                ];

            case 'location':
                return [
                    'type' => 'location',
                    'latitude' => $message['location']['latitude'] ?? null,
                    'longitude' => $message['location']['longitude'] ?? null,
                    'name' => $message['location']['name'] ?? null,
                    'address' => $message['location']['address'] ?? null,
                ];

            case 'interactive':
                return [
                    'type' => 'interactive',
                    'interactive_type' => $message['interactive']['type'] ?? null,
                    'response' => $this->extractInteractiveResponse($message['interactive'] ?? []),
                ];

            default:
                return [
                    'type' => $type,
                    'raw' => $message,
                ];
        }
    }

    /**
     * Extract interactive message response
     */
    private function extractInteractiveResponse(array $interactive): array
    {
        $type = $interactive['type'] ?? null;

        switch ($type) {
            case 'button_reply':
                return [
                    'button_id' => $interactive['button_reply']['id'] ?? null,
                    'button_title' => $interactive['button_reply']['title'] ?? null,
                ];

            case 'list_reply':
                return [
                    'list_id' => $interactive['list_reply']['id'] ?? null,
                    'list_title' => $interactive['list_reply']['title'] ?? null,
                    'list_description' => $interactive['list_reply']['description'] ?? null,
                ];

            default:
                return $interactive;
        }
    }

    /**
     * Verify webhook signature (implement based on your security requirements)
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        // In production, implement proper signature verification using Meta's webhook secret
        // For now, return true for development
        return true;
    }

    /**
     * Get expected verify token for provider
     */
    private function getExpectedVerifyToken(string $provider): ?string
    {
        // Get from settings based on provider
        // For now, return a default token
        return config('whatsapp.webhook_verify_token', 'default_verify_token');
    }
}
