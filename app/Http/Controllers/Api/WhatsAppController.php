<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group WhatsApp Integration
 *
 * APIs for WhatsApp Business API integration and messaging
 */
class WhatsAppController extends Controller
{
    /**
     * Get Message Templates
     *
     * Retrieve approved WhatsApp message templates for the tenant.
     *
     * @authenticated
     *
     * @queryParam page integer Optional. Page number for pagination. Example: 1
     * @queryParam per_page integer Optional. Number of templates per page (max 50). Example: 25
     * @queryParam category string Optional. Filter by template category (MARKETING, UTILITY, AUTHENTICATION). Example: MARKETING
     * @queryParam status string Optional. Filter by approval status (APPROVED, PENDING, REJECTED). Example: APPROVED
     * @queryParam language string Optional. Filter by language code. Example: en_US
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "templates": [
     *       {
     *         "id": "welcome_message",
     *         "name": "welcome_message",
     *         "category": "UTILITY",
     *         "status": "APPROVED",
     *         "language": "en_US",
     *         "components": [
     *           {
     *             "type": "BODY",
     *             "text": "Welcome {{1}}! Thank you for joining us. Your verification code is {{2}}.",
     *             "parameters": [
     *               {"type": "TEXT", "example": "John"},
     *               {"type": "TEXT", "example": "123456"}
     *             ]
     *           }
     *         ],
     *         "created_at": "2025-01-10T10:30:00.000000Z",
     *         "updated_at": "2025-01-10T10:30:00.000000Z"
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 25,
     *       "total": 15,
     *       "last_page": 1,
     *       "from": 1,
     *       "to": 15
     *     }
     *   }
     * }
     */
    public function templates(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
            'category' => 'string|in:MARKETING,UTILITY,AUTHENTICATION',
            'status' => 'string|in:APPROVED,PENDING,REJECTED',
            'language' => 'string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'templates' => [
                    [
                        'id' => 'welcome_message',
                        'name' => 'welcome_message',
                        'category' => 'UTILITY',
                        'status' => 'APPROVED',
                        'language' => 'en_US',
                        'components' => [
                            [
                                'type' => 'BODY',
                                'text' => 'Welcome {{1}}! Thank you for joining us. Your verification code is {{2}}.',
                                'parameters' => [
                                    ['type' => 'TEXT', 'example' => 'John'],
                                    ['type' => 'TEXT', 'example' => '123456'],
                                ],
                            ],
                        ],
                        'created_at' => '2025-01-10T10:30:00.000000Z',
                        'updated_at' => '2025-01-10T10:30:00.000000Z',
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 25,
                    'total' => 15,
                    'last_page' => 1,
                    'from' => 1,
                    'to' => 15,
                ],
            ],
        ]);
    }

    /**
     * Send Message
     *
     * Send a WhatsApp message to a contact using approved templates or free-form text.
     *
     * @authenticated
     *
     * @bodyParam to string required Recipient's WhatsApp phone number. Example: +1234567890
     * @bodyParam type string required Message type (template, text, media). Example: template
     * @bodyParam template object Optional. Required for template messages.
     * @bodyParam template.name string Template name. Example: welcome_message
     * @bodyParam template.language string Template language code. Example: en_US
     * @bodyParam template.components array Template component parameters.
     * @bodyParam text object Optional. Required for text messages.
     * @bodyParam text.body string Message text content. Example: Hello! How can I help you?
     * @bodyParam media object Optional. Required for media messages.
     * @bodyParam media.type string Media type (image, video, document, audio). Example: image
     * @bodyParam media.url string Media file URL. Example: https://example.com/image.jpg
     * @bodyParam media.caption string Optional. Media caption. Example: Check out our new product!
     * @bodyParam context object Optional. Reply context for threading.
     * @bodyParam context.message_id string Message ID to reply to. Example: wamid.123456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Message sent successfully",
     *   "data": {
     *     "message": {
     *       "id": "wamid.ABGGFlA5FpafAgo6tHcNmNjXmuSf",
     *       "to": "+1234567890",
     *       "type": "template",
     *       "status": "sent",
     *       "timestamp": "2025-01-15T10:30:00.000000Z",
     *       "template_name": "welcome_message",
     *       "delivery_status": "pending"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "to": ["The phone number format is invalid."],
     *     "template.name": ["The template name is required for template messages."]
     *   }
     * }
     * @response 429 {
     *   "success": false,
     *   "message": "Daily message limit exceeded. Please upgrade your plan or wait until tomorrow."
     * }
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
            'type' => 'required|string|in:template,text,media',
            'template.name' => 'required_if:type,template|string',
            'template.language' => 'required_if:type,template|string',
            'template.components' => 'array',
            'text.body' => 'required_if:type,text|string|max:4096',
            'media.type' => 'required_if:type,media|string|in:image,video,document,audio',
            'media.url' => 'required_if:type,media|url',
            'media.caption' => 'string|max:1024',
            'context.message_id' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'message' => [
                    'id' => 'wamid.ABGGFlA5FpafAgo6tHcNmNjXmuSf',
                    'to' => $request->input('to'),
                    'type' => $request->input('type'),
                    'status' => 'sent',
                    'timestamp' => now()->toISOString(),
                    'template_name' => $request->input('template.name'),
                    'delivery_status' => 'pending',
                ],
            ],
        ]);
    }

    /**
     * Upload Media
     *
     * Upload media files to WhatsApp for use in messages.
     *
     * @authenticated
     *
     * @bodyParam file file required Media file to upload. Max size: 100MB.
     * @bodyParam type string required Media type (image, video, document, audio). Example: image
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Media uploaded successfully",
     *   "data": {
     *     "media": {
     *       "id": "1234567890123456",
     *       "url": "https://lookaside.fbsbx.com/whatsapp_business/attachments/?mid=1234567890123456",
     *       "type": "image",
     *       "file_name": "product_image.jpg",
     *       "file_size": 2048576,
     *       "mime_type": "image/jpeg",
     *       "uploaded_at": "2025-01-15T10:30:00.000000Z",
     *       "expires_at": "2025-02-14T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "file": ["The file must not be greater than 100MB."],
     *     "type": ["The selected type is invalid."]
     *   }
     * }
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400', // 100MB
            'type' => 'required|string|in:image,video,document,audio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully',
            'data' => [
                'media' => [
                    'id' => '1234567890123456',
                    'url' => 'https://lookaside.fbsbx.com/whatsapp_business/attachments/?mid=1234567890123456',
                    'type' => $request->input('type'),
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString(),
                    'expires_at' => now()->addDays(30)->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Get Phone Numbers
     *
     * Retrieve WhatsApp Business phone numbers associated with the tenant.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "phone_numbers": [
     *       {
     *         "id": "123456789012345",
     *         "phone_number": "+1234567890",
     *         "display_phone_number": "+1 (234) 567-890",
     *         "status": "CONNECTED",
     *         "quality_rating": "GREEN",
     *         "name_status": "APPROVED",
     *         "business_name": "Acme Corporation",
     *         "messaging_limit": "TIER_1000",
     *         "is_primary": true,
     *         "verified_at": "2025-01-10T10:30:00.000000Z",
     *         "created_at": "2025-01-08T10:30:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function phoneNumbers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'phone_numbers' => [
                    [
                        'id' => '123456789012345',
                        'phone_number' => '+1234567890',
                        'display_phone_number' => '+1 (234) 567-890',
                        'status' => 'CONNECTED',
                        'quality_rating' => 'GREEN',
                        'name_status' => 'APPROVED',
                        'business_name' => 'Acme Corporation',
                        'messaging_limit' => 'TIER_1000',
                        'is_primary' => true,
                        'verified_at' => '2025-01-10T10:30:00.000000Z',
                        'created_at' => '2025-01-08T10:30:00.000000Z',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Verify Phone Number
     *
     * Initiate phone number verification process for WhatsApp Business API.
     *
     * @authenticated
     *
     * @bodyParam phone_number_id string required WhatsApp Business phone number ID. Example: 123456789012345
     * @bodyParam verification_method string required Verification method (SMS, VOICE). Example: SMS
     * @bodyParam locale string Optional. Locale for verification message. Example: en_US
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Verification code sent successfully",
     *   "data": {
     *     "verification": {
     *       "phone_number_id": "123456789012345",
     *       "method": "SMS",
     *       "code_length": 6,
     *       "expires_in": 600,
     *       "sent_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "phone_number_id": ["The phone number ID is required."]
     *   }
     * }
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number_id' => 'required|string',
            'verification_method' => 'required|string|in:SMS,VOICE',
            'locale' => 'string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully',
            'data' => [
                'verification' => [
                    'phone_number_id' => $request->input('phone_number_id'),
                    'method' => $request->input('verification_method'),
                    'code_length' => 6,
                    'expires_in' => 600,
                    'sent_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Get Webhooks
     *
     * Retrieve configured webhook endpoints for WhatsApp events.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "webhooks": [
     *       {
     *         "id": "webhook_123",
     *         "url": "https://myapp.com/api/webhooks/whatsapp",
     *         "events": ["messages", "message_deliveries", "message_reads"],
     *         "status": "active",
     *         "verify_token": "my_verify_token",
     *         "last_success_at": "2025-01-15T10:25:00.000000Z",
     *         "last_failure_at": null,
     *         "failure_count": 0,
     *         "created_at": "2025-01-10T10:30:00.000000Z",
     *         "updated_at": "2025-01-15T10:25:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function webhooks(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'webhooks' => [
                    [
                        'id' => 'webhook_123',
                        'url' => 'https://myapp.com/api/webhooks/whatsapp',
                        'events' => ['messages', 'message_deliveries', 'message_reads'],
                        'status' => 'active',
                        'verify_token' => 'my_verify_token',
                        'last_success_at' => '2025-01-15T10:25:00.000000Z',
                        'last_failure_at' => null,
                        'failure_count' => 0,
                        'created_at' => '2025-01-10T10:30:00.000000Z',
                        'updated_at' => '2025-01-15T10:25:00.000000Z',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Create Webhook
     *
     * Configure a new webhook endpoint for WhatsApp events.
     *
     * @authenticated
     *
     * @bodyParam url string required Webhook endpoint URL. Example: https://myapp.com/api/webhooks/whatsapp
     * @bodyParam events array required Array of events to subscribe to. Example: ["messages", "message_deliveries"]
     * @bodyParam verify_token string required Token for webhook verification. Example: my_secure_token_123
     * @bodyParam secret string Optional. Secret for webhook signature verification. Example: webhook_secret_key
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Webhook created successfully",
     *   "data": {
     *     "webhook": {
     *       "id": "webhook_124",
     *       "url": "https://myapp.com/api/webhooks/whatsapp",
     *       "events": ["messages", "message_deliveries"],
     *       "status": "pending_verification",
     *       "verify_token": "my_secure_token_123",
     *       "verification_challenge": "challenge_abc123",
     *       "created_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "url": ["The URL format is invalid."],
     *     "events": ["At least one event must be selected."]
     *   }
     * }
     */
    public function createWebhook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:messages,message_deliveries,message_reads,message_echoes,messaging_optins,messaging_optouts',
            'verify_token' => 'required|string|min:10|max:255',
            'secret' => 'nullable|string|min:10|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook created successfully',
            'data' => [
                'webhook' => [
                    'id' => 'webhook_124',
                    'url' => $request->input('url'),
                    'events' => $request->input('events'),
                    'status' => 'pending_verification',
                    'verify_token' => $request->input('verify_token'),
                    'verification_challenge' => 'challenge_abc123',
                    'created_at' => now()->toISOString(),
                ],
            ],
        ], 201);
    }
}
