<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\ManageChat;
use App\Models\Tenant\Chat;
use App\Models\Tenant\ChatMessage;
use App\Models\Tenant\Contact as TenantContact;
use App\Services\FeatureService;
use App\Services\pusher\PusherService;
use App\Traits\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

/**
 * @group Send Messages
 *
 * APIs for sending WhatsApp messages including text, template, and media messages.
 *
 * **Note:** If a contact is not available in the application, the system will automatically
 * create a new contact and then send the message. This ensures seamless message delivery
 * even for new phone numbers.
 *
 * ## 24-Hour Customer Service Window Policy (Meta/WhatsApp)
 *
 * **Simple Messages & Media Messages:**
 * - Can only be sent within the 24-hour customer service window
 * - This window starts (or refreshes) whenever a WhatsApp user messages you or calls you
 * - When the window is open, you can send any type of message to the user
 * - After the 24-hour window expires, free-form messages will not be delivered
 * - Use approved template messages for communication beyond the 24-hour window
 *
 * **Template Messages:**
 * - Can be sent at any time, regardless of the 24-hour messaging window
 * - Template messages are the only type that can be sent outside a customer service window
 * - No time restrictions apply to approved template messages
 * - Perfect for marketing, notifications, and re-engaging inactive customers
 */
class MessageController extends Controller
{
    use WhatsApp;

    protected $featureLimitChecker;

    /**
     * MessageController constructor.
     */
    public function __construct(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    /**
     * Send Simple Message
     *
     * Send a simple text message to a specific phone number via WhatsApp.
     *
     * **Contact Behavior:**
     * - If contact exists by phone number → Message sent to existing contact
     * - If contact doesn't exist AND `contact` object provided → New contact created with provided data, then message sent
     * - If contact doesn't exist AND no `contact` object → New contact created with phone number as name, then message sent
     *
     * **Contact Creation Requirements:**
     * - If `contact` object is provided, the specified data will be used
     * - If no `contact` object is provided, a basic contact will be created automatically using the phone number as the firstname
     * - `firstname` defaults to phone number if not provided in contact data
     * - `lastname` defaults to empty string if not provided
     * - Contact will be created as type "lead" with WhatsApp Auto Lead settings applied
     * - If WhatsApp Auto Lead is enabled, default status, source, and assignee will be applied automatically
     *
     * **Available Reference Endpoints for IDs:**
     * To find exact IDs for contact assignment fields:
     * - **Sources**: `GET /api/v1/{subdomain}/sources` - List all sources with their IDs
     * - **Statuses**: `GET /api/v1/{subdomain}/statuses` - List all statuses with their IDs
     * - **Groups**: `GET /api/v1/{subdomain}/groups` - List all groups with their IDs
     *
     * **Important - 24-Hour Customer Service Window:** Simple text messages are subject to Meta's customer service window policy.
     * You can only send free-form text messages within 24 hours of the customer's last message to your business.
     * This window starts or refreshes whenever the customer messages or calls you. After this 24-hour window expires,
     * the message will not be delivered. Use approved template messages for communication beyond the 24-hour window.
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     *
     * @bodyParam phone_number string required The phone number to send message to (with + prefix). Example: +919909919284
     * @bodyParam message_body string required The text message to send (max 4096 characters). Example: Hello! This is a sample message to demonstrate the API.
     * @bodyParam contact object optional Contact information to create if contact doesn't exist. If not provided, a basic contact will be created using the phone number as the name.
     * @bodyParam contact.firstname string optional Contact's first name (defaults to phone number if not provided). Example: John
     * @bodyParam contact.lastname string optional Contact's last name (defaults to empty string if not provided). Example: Doe
     * @bodyParam contact.email string optional Contact's email address (must be valid email format). Example: john.doe@example.com
     * @bodyParam contact.country string optional Contact's country name or ID. Example: India
     * @bodyParam contact.status_id integer optional|auto_fallback Status ID for the contact. Must belong to the current tenant. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. If no valid default is available, an error will be returned. Example: 1
     * @bodyParam contact.source_id integer optional|auto_fallback Source ID for the contact. Must belong to the current tenant. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. If no valid default is available, an error will be returned. Example: 2
     * @bodyParam contact.assigned_id integer optional|auto_fallback User ID to assign this contact to. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. Example: 15
     * @bodyParam contact.groups string optional Comma-separated group names for contact assignment. Example: VIP Customers,Newsletter Subscribers
     *
     * @bodyExample {
     *   "phone_number": "+919909919284",
     *   "message_body": "Hello! This is a sample message to demonstrate the API.",
     *   "contact": {
     *     "firstname": "John",
     *     "lastname": "Doe",
     *     "email": "john.doe@example.com",
     *     "country": "India",
     *     "status_id": 1,
     *     "source_id": 2,
     *     "groups": "VIP Customers,Newsletter Subscribers"
     *   }
     * }
     *
     * @bodySample{json} {
     *     "phone_number": "+919909919284",
     *     "message_body": "Hello! This is a sample message to demonstrate the API.",
     *     "contact": {
     *         "firstname": "John",
     *         "lastname": "Doe",
     *         "email": "john.doe@example.com",
     *         "country": "India",
     *         "status_id": 1,
     *         "source_id": 2,
     *         "groups": "VIP Customers,Newsletter Subscribers"
     *     }
     * }
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "message": "Message sent successfully",
     *   "data": {
     *     "message_id": "wamid.HBgMOTE5ODEwNjAwMDAwFQIAERgSNUU1RjE4MUM0QjY5MjFFNzYzAA==",
     *     "contact_id": 15,
     *     "phone": "+919909919284",
     *     "message": "Hello! This is a sample message to demonstrate the API.",
     *     "status": "sent",
     *     "sent_at": "2024-02-08 14:30:25",
     *     "chat_id": 8,
     *     "contact_created": true
     *   }
     * }
     * @response status=422 scenario="validation error" {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "phone_number": ["The phone number field is required."],
     *     "message_body": ["The message body field is required.", "The message body may not be greater than 4096 characters."],
     *     "contact.email": ["The contact email must be a valid email address."],
     *     "status_id": ["The selected status does not belong to this tenant."],
     *     "source_id": ["The selected source does not belong to this tenant."]
     *   }
     * }
     * @response status=422 scenario="contact validation error when auto-lead not configured" {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "status_id": ["The status field is required. Either provide a status_id or configure auto lead settings with a default status."],
     *     "source_id": ["The source field is required. Either provide a source_id or configure auto lead settings with a default source."]
     *   }
     * }
     * @response status=422 scenario="whatsapp not configured" {
     *   "status": "error",
     *   "message": "WhatsApp connection not configured for this tenant"
     * }
     * @response status=403 scenario="conversation limit reached" {
     *   "status": "error",
     *   "message": "Conversation limit reached. Please upgrade your plan to continue messaging.",
     *   "limit_reached": true
     * }
     * @response status=403 scenario="contact limit reached" {
     *   "status": "error",
     *   "message": "Contact limit reached. Please upgrade your plan to create new contacts."
     * }
     * @response status=500 scenario="whatsapp api error" {
     *   "status": "error",
     *   "message": "Failed to send WhatsApp message",
     *   "error": "Message delivery failed - recipient phone number not registered with WhatsApp"
     * }
     * @response status=500 scenario="internal error" {
     *   "status": "error",
     *   "message": "Failed to send message",
     *   "error": "Internal server error"
     * }
     */
    public function sendMessage(Request $request, $subdomain)
    {
        try {
            // Get tenant ID from request (set by middleware)
            $tenant_id = $request->get('tenant_id');

            // Set WhatsApp tenant context
            $this->setWaTenantId($tenant_id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string',
                'message_body' => 'required|string|max:4096',
                'contact' => 'nullable|array',
                'contact.firstname' => 'nullable|string|max:255',
                'contact.lastname' => 'nullable|string|max:255',
                'contact.email' => 'nullable|email|max:191',
                'contact.country' => 'nullable|string|max:100',
                'contact.assigned_id' => 'nullable|integer',
                'contact.groups' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Clean phone number for WhatsApp
            $phoneNumber = $this->cleanPhoneNumber($request->phone_number);

            // Find or create contact
            $contactResult = $this->findOrCreateContact($phoneNumber, $request->contact ?? null, $tenant_id, $subdomain);

            if (! $contactResult['success']) {
                $response = [
                    'status' => 'error',
                    'message' => $contactResult['message'],
                ];

                // Include validation errors if they exist
                if (isset($contactResult['errors'])) {
                    $response['errors'] = $contactResult['errors'];
                }

                return response()->json($response, $contactResult['status_code']);
            }

            $contact = $contactResult['contact'];
            $contactCreated = $contactResult['created'];

            // Check if WhatsApp connection is configured for this tenant
            $whatsappSettings = $this->getWhatsAppConnectionSettings($tenant_id);

            if (! $whatsappSettings) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('whatsapp_connection_not_configured'),
                ], 422);
            }

            // Check conversation limits before sending
            if ($this->featureLimitChecker->checkConversationLimit($contact->id, $tenant_id, $subdomain, $contact->type)) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('conversation_limit_reached_upgrade_plan'),
                    'limit_reached' => true,
                ], 403);
            }

            // Parse message text with contact data
            $parsedMessage = $this->parseMessageText([
                'rel_type' => $contact->type,
                'rel_id' => $contact->id,
                'reply_text' => $request->message_body,
                'tenant_id' => $tenant_id,
            ]);

            $finalMessage = $parsedMessage['reply_text'] ?? $request->message_body;

            // Send WhatsApp message
            $messageResponse = $this->sendWhatsAppMessage(
                $phoneNumber,
                $finalMessage,
                $whatsappSettings
            );

            if (! $messageResponse['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('failed_to_send_whatsapp_message'),
                    'error' => $messageResponse['error'] ?? 'Unknown error',
                ], 500);
            }

            // Create or update chat interaction
            $chatInteraction = $this->createOrUpdateChatInteraction(
                $phoneNumber,
                $contact,
                $whatsappSettings,
                $finalMessage,
                $tenant_id,
                $subdomain
            );

            // Save message to database
            $chatMessage = $this->saveChatMessage(
                $chatInteraction,
                $finalMessage,
                $messageResponse['message_id'],
                $tenant_id,
                $subdomain
            );

            // Track conversation usage
            $this->trackConversationUsage($contact->id, $contact->type, $tenant_id, $subdomain);

            return response()->json([
                'status' => 'success',
                'message' => t('message_sent_successfully'),
                'data' => [
                    'message_id' => $messageResponse['message_id'],
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'message' => $finalMessage,
                    'status' => 'sent',
                    'sent_at' => now()->toDateTimeString(),
                    'chat_id' => $chatInteraction->id ?? null,
                    'contact_created' => $contactCreated,
                ],
            ], 200);
        } catch (\Exception $e) {
            whatsapp_log('API send message error', 'error', [
                'phone_number' => $request->phone_number ?? null,
                'tenant_id' => $tenant_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e, $tenant_id ?? null);

            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_send_message'),
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Find existing contact by phone number or create new one
     */
    private function findOrCreateContact($phoneNumber, $contactData, $tenant_id, $subdomain)
    {
        try {
            // Normalize phone number for contact storage (with + prefix)
            $normalizedPhone = $this->normalizePhoneForContact($phoneNumber);

            // First try to find existing contact by phone number (try both formats)
            $contact = TenantContact::fromTenant($subdomain)->where('tenant_id', $tenant_id)
                ->where(function ($query) use ($normalizedPhone, $phoneNumber) {
                    $query->where('phone', 'like', "%{$normalizedPhone}")
                        ->orWhere('phone', 'like', "%{$phoneNumber}");
                })
                ->first();

            if ($contact) {
                // Update existing contact phone to normalized format if needed
                if ($contact->phone !== $normalizedPhone) {
                    $contact->phone = $normalizedPhone;
                    $contact->save();
                }

                return [
                    'success' => true,
                    'contact' => $contact,
                    'created' => false,
                ];
            }

            // If contact doesn't exist and no contact data provided, create basic contact with phone number as name
            if (! $contactData || empty($contactData)) {
                $contactData = [
                    'firstname' => $normalizedPhone, // Use phone number as firstname
                    'lastname' => '', // Empty lastname
                ];
            }

            // Check contact creation limits
            if ($this->featureLimitChecker->hasReachedLimit('contacts', TenantContact::class)) {
                return [
                    'success' => false,
                    'message' => t('contact_limit_reached_upgrade_plan'),
                    'status_code' => 403,
                ];
            }

            // Get WhatsApp Auto Lead settings for fallback values
            $autoLeadSettings = $this->getWhatsAppAutoLeadSettings($tenant_id);

            // Handle country conversion
            $countryId = null;
            if (! empty($contactData['country'])) {
                if (is_numeric($contactData['country'])) {
                    $countryId = $contactData['country'];
                } else {
                    $countryId = get_country_id_by_name($contactData['country']);
                }
            }

            // Prepare contact data with fallbacks
            $contactCreateData = [
                'tenant_id' => $tenant_id,
                'firstname' => $contactData['firstname'] ?? $normalizedPhone, // Use phone number if no firstname provided
                'lastname' => $contactData['lastname'] ?? '',
                'email' => $contactData['email'] ?? null,
                'phone' => $normalizedPhone, // Store with + prefix
                'country_id' => $countryId,
                'type' => 'lead', // Default type for API created contacts
                'addedfrom' => 0, // API creation (0 = system/API)
            ];

            // Handle status_id - prioritize user-provided value, then auto-lead, then default
            if (! empty($contactData['status_id'])) {
                // User provided status_id - validate it belongs to tenant
                if ($this->validateStatusBelongsToTenant($contactData['status_id'], $tenant_id)) {
                    $contactCreateData['status_id'] = $contactData['status_id'];
                } else {
                    // Invalid status_id provided by user
                    return [
                        'success' => false,
                        'message' => t('validation_failed'),
                        'errors' => ['status_id' => [t('selected_status_is_invalid')]],
                        'status_code' => 422,
                    ];
                }
            } else {
                // No status_id provided, try auto-lead settings
                if ($autoLeadSettings['enabled'] && ! empty($autoLeadSettings['lead_status'])) {
                    $contactCreateData['status_id'] = $autoLeadSettings['lead_status'];
                } else {
                    // No auto-lead settings, try default
                    $contactCreateData['status_id'] = $this->getDefaultStatusId($tenant_id);
                }
            }

            // Handle source_id - prioritize user-provided value, then auto-lead, then default
            if (! empty($contactData['source_id'])) {
                // User provided source_id - validate it belongs to tenant
                if ($this->validateSourceBelongsToTenant($contactData['source_id'], $tenant_id)) {
                    $contactCreateData['source_id'] = $contactData['source_id'];
                } else {
                    // Invalid source_id provided by user
                    return [
                        'success' => false,
                        'message' => t('validation_failed'),
                        'errors' => ['source_id' => [t('selected_source_is_invalid')]],
                        'status_code' => 422,
                    ];
                }
            } else {
                // No source_id provided, try auto-lead settings
                if ($autoLeadSettings['enabled'] && ! empty($autoLeadSettings['lead_source'])) {
                    $contactCreateData['source_id'] = $autoLeadSettings['lead_source'];
                } else {
                    // No auto-lead settings, try default
                    $contactCreateData['source_id'] = $this->getDefaultSourceId($tenant_id);
                }
            }

            // Handle assigned_id
            if (! empty($contactData['assigned_id'])) {
                $contactCreateData['assigned_id'] = $contactData['assigned_id'];
            } elseif ($autoLeadSettings['enabled'] && ! empty($autoLeadSettings['lead_assigned_to'])) {
                $contactCreateData['assigned_id'] = $autoLeadSettings['lead_assigned_to'];
            }

            // Final validation - ensure we have valid status_id and source_id
            $validationErrors = [];

            if (empty($contactCreateData['status_id'])) {
                $validationErrors['status_id'] = [t('status_id_is_required_configure_auto_lead_settings')];
            }

            if (empty($contactCreateData['source_id'])) {
                $validationErrors['source_id'] = [t('source_id_is_required_configure_auto_lead_settings')];
            }

            // If validation fails, return error
            if (! empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => t('validation_failed'),
                    'errors' => $validationErrors,
                    'status_code' => 422,
                ];
            }

            // Create new contact
            $newContact = TenantContact::fromTenant($subdomain)->create($contactCreateData);

            // Handle groups if provided
            if (! empty($contactData['groups'])) {
                $this->assignContactToGroups($newContact, $contactData['groups'], $tenant_id);
            }

            // Track contact creation
            $this->featureLimitChecker->trackUsage('contacts');

            return [
                'success' => true,
                'contact' => $newContact,
                'created' => true,
            ];
        } catch (\Exception $e) {
            whatsapp_log('Error finding/creating contact', 'error', [
                'phone' => $phoneNumber,
                'contactData' => $contactData,
                'tenant_id' => $tenant_id,
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], $e, $tenant_id);

            return [
                'success' => false,
                'message' => t('failed_to_create_contact'),
                'status_code' => 500,
            ];
        }
    }

    /**
     * Get default source ID for contact creation
     */
    private function getDefaultSourceId($tenant_id)
    {
        try {
            $source = \App\Models\Tenant\Source::where([
                ['tenant_id', '=', $tenant_id],
                ['name', '=', 'API'],
            ])->first() ?: \App\Models\Tenant\Source::where('tenant_id', $tenant_id)->first();

            return $source ? $source->id : null; // Return null instead of hardcoded fallback
        } catch (\Exception $e) {
            return null; // Return null instead of hardcoded fallback
        }
    }

    /**
     * Get default status ID for contact creation
     */
    private function getDefaultStatusId($tenant_id)
    {
        try {
            $status = \App\Models\Tenant\Status::where([
                ['tenant_id', '=', $tenant_id],
                ['name', '=', 'New'],
            ])->first() ?: \App\Models\Tenant\Status::where('tenant_id', $tenant_id)->first();

            return $status ? $status->id : null; // Return null instead of hardcoded fallback
        } catch (\Exception $e) {
            return null; // Return null instead of hardcoded fallback
        }
    }

    /**
     * Assign contact to specified groups
     */
    private function assignContactToGroups($contact, $groupsString, $tenant_id)
    {
        try {
            $groupNames = array_map('trim', explode(',', $groupsString));
            $groupIds = [];

            foreach ($groupNames as $groupName) {
                if (empty($groupName)) {
                    continue;
                }

                // Find or create group
                $group = \App\Models\Tenant\Group::firstOrCreate([
                    'tenant_id' => $tenant_id,
                    'name' => $groupName,
                ]);

                $groupIds[] = $group->id;
            }
            // Assign contact to groups (assuming you have a pivot table)
            if (! empty($groupIds) && method_exists($contact, 'groups')) {
                $contact->setGroupIds($groupIds);
                $contact->save();
            }
        } catch (\Exception $e) {
            whatsapp_log('Error assigning contact to groups', 'error', [
                'contact_id' => $contact->id,
                'groups' => $groupsString,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);
        }
    }

    /**
     * Clean phone number for WhatsApp API (removes + for API calls)
     */
    private function cleanPhoneNumber($phone)
    {
        // Remove all non-numeric characters except +
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

        // Remove + if present at the start
        if (str_starts_with($cleanPhone, '+')) {
            $cleanPhone = substr($cleanPhone, 1);
        }

        return $cleanPhone;
    }

    /**
     * Normalize phone number for contact storage (keeps + for contacts)
     */
    private function normalizePhoneForContact($phone)
    {
        // Remove all non-numeric characters except +
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

        // Ensure it starts with + if it doesn't already
        if (! str_starts_with($cleanPhone, '+')) {
            $cleanPhone = '+'.$cleanPhone;
        }

        return $cleanPhone;
    }

    /**
     * Get WhatsApp connection settings for tenant
     */
    private function getWhatsAppConnectionSettings($tenant_id)
    {
        try {
            // Get tenant-specific WhatsApp settings
            $settings = tenant_settings_by_group('whatsapp', $tenant_id);
            if (empty($settings['wm_business_account_id']) || empty($settings['wm_access_token'])) {
                return null;
            }

            return $settings;
        } catch (\Exception $e) {
            whatsapp_log('Error getting WhatsApp settings', 'error', [
                'tenant_id' => $tenant_id,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return null;
        }
    }

    /**
     * Send WhatsApp message using Cloud API
     */
    private function sendWhatsAppMessage($phoneNumber, $message, $whatsappSettings)
    {
        try {
            $whatsapp = new WhatsAppCloudApi([
                'from_phone_number_id' => $whatsappSettings['wm_default_phone_number_id'],
                'access_token' => $whatsappSettings['wm_access_token'],
            ]);

            $response = $whatsapp->sendTextMessage($phoneNumber, $message, true);
            $responseData = $response->decodedBody();

            if (isset($responseData['messages'][0]['id'])) {
                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'],
                    'response' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No message ID in response',
                    'response' => $responseData,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => $e,
            ];
        }
    }

    /**
     * Parse message text with contact data
     */
    private function parseMessageText($data)
    {
        // Use existing parseMessageText function if available
        if (function_exists('parseMessageText')) {
            return parseMessageText($data);
        }

        // Fallback basic parsing
        $message = $data['reply_text'] ?? '';

        // You can add basic variable replacement here
        // For example: {firstname}, {lastname}, {company}, etc.

        return ['reply_text' => $message];
    }

    /**
     * Create or update chat interaction
     */
    private function createOrUpdateChatInteraction($phoneNumber, $contact, $whatsappSettings, $message, $tenant_id, $subdomain)
    {
        try {
            // Find existing chat or create new one
            $chat = Chat::fromTenant($subdomain)
                ->where('receiver_id', $phoneNumber)
                ->where('type', $contact->type)
                ->where('type_id', $contact->id)
                ->first();

            if (! $chat) {
                $chat = Chat::fromTenant($subdomain)->create([
                    'tenant_id' => $tenant_id,
                    'receiver_id' => $phoneNumber,
                    'wa_no' => $whatsappSettings['wm_default_phone_number'],
                    'wa_no_id' => $whatsappSettings['wm_default_phone_number_id'],
                    'name' => $contact->firstname.' '.$contact->lastname,
                    'type' => $contact->type,
                    'type_id' => $contact->id,
                    'last_message' => $message,
                    'last_msg_time' => now(),
                    'time_sent' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $chat->update([
                    'last_message' => $message,
                    'last_msg_time' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $chat;
        } catch (\Exception $e) {
            whatsapp_log('Error creating/updating chat interaction', 'error', [
                'phone' => $phoneNumber,
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return null;
        }
    }

    /**
     * Save chat message to database
     */
    private function saveChatMessage($chatInteraction, $message, $messageId, $tenant_id, $subdomain)
    {
        try {
            if (! $chatInteraction) {
                return null;
            }

            $chatMessage = ChatMessage::fromTenant($subdomain)->create([
                'tenant_id' => $tenant_id,
                'interaction_id' => $chatInteraction->id,
                'sender_id' => $chatInteraction->wa_no,
                'message' => $message,
                'message_id' => $messageId,
                'type' => 'text',
                'staff_id' => null, // API messages don't have staff_id
                'status' => 'sent',
                'time_sent' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'is_read' => 1,
            ]);

            if (! empty(get_tenant_setting_by_tenant_id('pusher', 'app_key', null, $tenant_id)) && ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_secret', null, $tenant_id)) && ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_id', null, $tenant_id)) && ! empty(get_tenant_setting_by_tenant_id('pusher', 'cluster', null, $tenant_id))) {
                $pusherService = new PusherService($tenant_id);
                $pusherService->trigger('whatsmark-saas-chat-channel', 'whatsmark-saas-chat-event', [
                    'chat' => ManageChat::newChatMessage($chatInteraction->id, $chatMessage->id, $tenant_id),
                ]);
            }

            return $chatMessage;
        } catch (\Exception $e) {
            whatsapp_log('Error saving chat message', 'error', [
                'chat_id' => $chatInteraction->id ?? null,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return null;
        }
    }

    /**
     * Track conversation usage for billing
     */
    private function trackConversationUsage($contactId, $contactType, $tenant_id, $subdomain)
    {
        try {
            // Force initialize conversation tracking
            $this->featureLimitChecker->forceInitializeConversationTracking();

            // Track usage if this is a new conversation
            $this->featureLimitChecker->trackUsage('conversations');
        } catch (\Exception $e) {
            whatsapp_log('Error tracking conversation usage', 'error', [
                'contact_id' => $contactId,
                'contact_type' => $contactType,
                'tenant_id' => $tenant_id,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);
        }
    }

    /**
     * Send Template Message
     *
     * Send a WhatsApp template message to a specific phone number with comprehensive parameter support and template validation.
     * Optionally creates a contact if it doesn't exist.
     *
     * **Important - No 24-Hour Restrictions:** Template messages can be sent at any time, regardless of the customer service window.
     * Unlike simple text and media messages, approved templates are not restricted by Meta's 24-hour policy.
     * They are the only message type that can be sent outside a customer service window, making them perfect
     * for marketing campaigns, notifications, and re-engaging customers after extended periods of inactivity.
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     *
     * @bodyParam phone_number string required The phone number to send template to (with + prefix). Example: +919909919284
     * @bodyParam template_name string required The template name from WhatsApp Business (must be approved). Example: welcome_message
     * @bodyParam template_language string required Template language code (ISO 639-1). Example: en
     * @bodyParam from_phone_number_id string optional Sender phone number ID (uses default if not provided). Example: 1234567890
     * @bodyParam contact object optional Contact information to create if contact doesn't exist. Can be omitted entirely, passed as empty object {}, or null. If omitted and contact doesn't exist, a basic contact will be created using the phone number as the name.
     * @bodyParam contact.firstname string optional Contact's first name. Example: John
     * @bodyParam contact.lastname string optional Contact's last name. Example: Doe
     * @bodyParam contact.email string optional Contact's email address (must be valid email format). Example: john.doe@example.com
     * @bodyParam contact.country string optional Contact's country name or ID. Example: India
     * @bodyParam contact.status_id integer optional|auto_fallback Status ID for the contact. Must belong to the current tenant. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. If no valid default is available, an error will be returned. Example: 1
     * @bodyParam contact.source_id integer optional|auto_fallback Source ID for the contact. Must belong to the current tenant. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. If no valid default is available, an error will be returned. Example: 2
     * @bodyParam contact.assigned_id integer optional|auto_fallback User ID to assign this contact to. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. Example: 15
     * @bodyParam contact.groups string optional Comma-separated group names for contact assignment. Example: VIP Customers,Newsletter Subscribers
     *
     * **Reference Endpoints for Contact IDs:**
     * Use these endpoints to find exact IDs for contact fields: `GET /api/v1/{subdomain}/sources` (sources),
     * `GET /api/v1/{subdomain}/statuses` (statuses), `GET /api/v1/{subdomain}/groups` (groups)
     *
     * **Header Parameters (conditionally required based on template's header format):**
     * These parameters are only required if your WhatsApp template has a header of the corresponding type.
     * If your template has no header or a different header type, these can be omitted entirely.
     * @bodyParam header_image_url string optional|conditional Header image URL (REQUIRED only for IMAGE header templates). Example: https://cdn.pixabay.com/photo/2015/01/07/15/51/woman-591576_1280.jpg
     * @bodyParam header_image_file file optional|conditional Header image file upload (alternative to URL, REQUIRED only for IMAGE header templates, max 5MB)
     * @bodyParam header_video_url string optional|conditional Header video URL (REQUIRED only for VIDEO header templates). Example: http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4
     * @bodyParam header_video_file file optional|conditional Header video file upload (alternative to URL, REQUIRED only for VIDEO header templates, max 16MB)
     * @bodyParam header_document_url string optional|conditional Header document URL (REQUIRED only for DOCUMENT header templates). Example: https://example.com/brochure.pdf
     * @bodyParam header_document_file file optional|conditional Header document file upload (alternative to URL, REQUIRED only for DOCUMENT header templates, max 100MB)
     * @bodyParam header_document_name string optional Header document display name (used with document headers). Example: Product Brochure 2024
     * @bodyParam header_field_1 string optional|conditional Header text parameter (REQUIRED only for TEXT header templates with parameters). Example: John
     *
     * **Body Parameters (conditionally required based on template's body placeholders):**
     * These are only required if your WhatsApp template body contains placeholders like {{1}}, {{2}}, etc.
     * If your template has no placeholders, these can be omitted entirely.
     * @bodyParam field_1 string optional|conditional Body parameter 1 (REQUIRED only if template has {{1}} placeholder). Example: 25
     * @bodyParam field_2 string optional|conditional Body parameter 2 (REQUIRED only if template has {{2}} placeholder). Example: John Doe
     * @bodyParam field_3 string optional|conditional Body parameter 3 (REQUIRED only if template has {{3}} placeholder). Example: Premium
     * @bodyParam field_4 string optional|conditional Body parameter 4 (REQUIRED only if template has {{4}} placeholder). Example: December 2024
     * @bodyParam field_5 string optional|conditional Body parameter 5 (REQUIRED only if template has {{5}} placeholder)
     * @bodyParam field_6 string optional|conditional Body parameter 6 (REQUIRED only if template has {{6}} placeholder)
     * @bodyParam field_7 string optional|conditional Body parameter 7 (REQUIRED only if template has {{7}} placeholder)
     * @bodyParam field_8 string optional|conditional Body parameter 8 (REQUIRED only if template has {{8}} placeholder)
     * @bodyParam field_9 string optional|conditional Body parameter 9 (REQUIRED only if template has {{9}} placeholder)
     * @bodyParam field_10 string optional|conditional Body parameter 10 (REQUIRED only if template has {{10}} placeholder)
     *
     * **Button Parameters (conditionally required based on template's button configuration):**
     * These are only required if your WhatsApp template has buttons with dynamic parameters.
     * If your template has no buttons or static buttons, these can be omitted entirely.
     * @bodyParam button_0 string optional|conditional Button parameter 1 (REQUIRED only for URL/PHONE buttons with variables). Example: john.doe@example.com
     * @bodyParam button_1 string optional|conditional Button parameter 2 (REQUIRED only for URL/PHONE buttons with variables). Example: +919909919284
     * @bodyParam button_2 string optional|conditional Button parameter 3 (REQUIRED only for URL/PHONE buttons with variables)
     * @bodyParam copy_code string optional|conditional Copy code for quick reply buttons (REQUIRED only for copy code buttons). Example: SAVE20
     *
     * @bodyExample {
     *   "phone_number": "+919909919284",
     *   "template_name": "welcome_message",
     *   "template_language": "en",
     *   "field_1": "John",
     *   "field_2": "Premium",
     *   "field_3": "September 5, 2025",
     *   "contact": {
     *     "firstname": "John",
     *     "lastname": "Doe",
     *     "email": "john.doe@example.com",
     *     "country": "India",
     *     "groups": "VIP Customers,Newsletter Subscribers"
     *   }
     * }
     * @bodyExample scenario="Minimal request without optional fields" {
     *   "phone_number": "+919909919284",
     *   "template_name": "simple_hello",
     *   "template_language": "en"
     * }
     * @bodyExample scenario="Template with image header" {
     *   "phone_number": "+919909919284",
     *   "template_name": "product_announcement",
     *   "template_language": "en",
     *   "header_image_url": "https://example.com/product.jpg",
     *   "field_1": "iPhone 15",
     *   "field_2": "$999",
     *   "contact": {}
     * }
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "message": "Template message sent successfully",
     *   "data": {
     *     "message_id": "wamid.HBgMOTE5ODEwNjAwMDAwFQIAERgSNUU1RjE4MUM0QjY5MjFFNzYzAA==",
     *     "contact_id": 15,
     *     "phone": "+919909919284",
     *     "template_name": "welcome_message",
     *     "template_language": "en",
     *     "status": "sent",
     *     "sent_at": "2024-02-08 14:30:25",
     *     "chat_id": 8,
     *     "contact_created": false,
     *     "parameters_used": {
     *       "header": ["John"],
     *       "body": ["25", "John Doe", "Premium"],
     *       "buttons": ["john.doe@example.com"]
     *     }
     *   }
     * }
     * @response status=422 scenario="validation error" {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "phone_number": ["The phone number field is required.", "The phone number must be between 8 and 15 characters."],
     *     "template_name": ["The template name field is required."],
     *     "template_language": ["The template language field is required.", "The template language must be between 2 and 10 characters."],
     *     "contact.email": ["The contact email must be a valid email address."],
     *     "header_image_file": ["The header image file must not be greater than 5120 kilobytes."],
     *     "header_video_file": ["The header video file must not be greater than 16384 kilobytes."],
     *     "header_document_file": ["The header document file must not be greater than 102400 kilobytes."]
     *   }
     * }
     * @response status=422 scenario="template requirements validation error" {
     *   "status": "error",
     *   "message": "Template validation failed",
     *   "errors": {
     *     "template_fields": [
     *       "field_1 (Body parameter 1 required)",
     *       "field_2 (Body parameter 2 required)",
     *       "header_image_url or header_image_file (Header image required)"
     *     ]
     *   }
     * }
     * @response status=422 scenario="contact validation error when auto-lead not configured" {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "status_id": ["The status field is required. Either provide a status_id or configure auto lead settings with a default status."],
     *     "source_id": ["The source field is required. Either provide a source_id or configure auto lead settings with a default source."]
     *   }
     * }
     * @response status=404 scenario="template not found" {
     *   "status": "error",
     *   "message": "Template not found or not approved",
     *   "template_name": "welcome_message",
     *   "available_templates": ["hello_world", "order_confirmation", "appointment_reminder"]
     * }
     * @response status=422 scenario="template parameter mismatch" {
     *   "status": "error",
     *   "message": "Template parameter count mismatch",
     *   "details": {
     *     "expected_body_params": 3,
     *     "provided_body_params": 1,
     *     "expected_header_params": 1,
     *     "provided_header_params": 0
     *   }
     * }
     * @response status=403 scenario="conversation limit reached" {
     *   "status": "error",
     *   "message": "Conversation limit reached. Please upgrade your plan to continue messaging.",
     *   "limit_reached": true
     * }
     * @response status=500 scenario="whatsapp api error" {
     *   "status": "error",
     *   "message": "Failed to send template message",
     *   "error": "Template message delivery failed - invalid template parameters"
     * }
     */
    public function sendTemplateMessage(Request $request, $subdomain)
    {
        $tenant_id = $request->get('tenant_id');
        // Set tenant ID for WhatsApp trait
        $this->setWaTenantId($tenant_id);

        // Basic validation
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:8|max:15',
            'template_name' => 'required|string',
            'template_language' => 'required|string|min:2|max:10',
            'from_phone_number_id' => 'nullable|string',

            // Contact creation fields - allow null/empty values, validate structure if provided
            'contact' => 'nullable',
            'contact.firstname' => 'nullable|string|max:255',
            'contact.lastname' => 'nullable|string|max:255',
            'contact.email' => 'nullable|email|max:255',
            'contact.country' => 'nullable|string|max:100',
            'contact.assigned_id' => 'nullable|integer',
            'contact.groups' => 'nullable|string',

            // Header media files/URLs - allow null/empty values, validate when actually provided
            'header_image_url' => 'nullable|url',
            'header_image_file' => 'nullable|file|max:5120', // 5MB
            'header_video_url' => 'nullable|url',
            'header_video_file' => 'nullable|file|max:16384', // 16MB
            'header_document_url' => 'nullable|url',
            'header_document_file' => 'nullable|file|max:102400', // 100MB
            'header_document_name' => 'nullable|string|max:255',
            'header_field_1' => 'nullable|string',

            // Body fields (dynamic parameters) - allow null/empty, validate based on template requirements
            'field_1' => 'nullable|string',
            'field_2' => 'nullable|string',
            'field_3' => 'nullable|string',
            'field_4' => 'nullable|string',
            'field_5' => 'nullable|string',
            'field_6' => 'nullable|string',
            'field_7' => 'nullable|string',
            'field_8' => 'nullable|string',
            'field_9' => 'nullable|string',
            'field_10' => 'nullable|string',

            // Button parameters - allow null/empty, validate based on template requirements
            'button_0' => 'nullable|string',
            'button_1' => 'nullable|string',
            'button_2' => 'nullable|string',
            'copy_code' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => t('validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phoneNumber = $this->cleanPhoneNumber($request->input('phone_number'));
        $templateName = $request->input('template_name');
        $language = $request->input('template_language');
        $contactData = $request->input('contact', []);

        try {
            // 1. TEMPLATE VALIDATION - Check if template exists and get requirements
            $template = \App\Models\Tenant\WhatsappTemplate::where('tenant_id', $tenant_id)
                ->where('template_name', $templateName)
                ->where('language', $language)
                ->where('status', 'APPROVED') // Only allow approved templates
                ->first();

            if (! $template) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('template_not_found'),
                    'errors' => [
                        'template' => [t('template_not_found_message', ['template' => $templateName])],
                    ],
                ], 404);
            }

            // 2. TEMPLATE REQUIREMENTS VALIDATION
            $templateValidation = $this->validateTemplateRequirements($template, $request);
            if (! $templateValidation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('template_validation_failed'),
                    'errors' => $templateValidation['errors'],
                ], 422);
            }

            // 3. HANDLE HEADER MEDIA (if template requires it)
            $headerMediaResult = null;
            if (in_array($template->header_data_format, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                $headerMediaResult = $this->handleTemplateHeaderMedia($template, $request, $tenant_id);
                if (! $headerMediaResult['success']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $headerMediaResult['message'],
                        'errors' => $headerMediaResult['errors'] ?? [],
                    ], 422);
                }
            }

            // 4. FIND OR CREATE CONTACT
            $contactResult = $this->findOrCreateContact($phoneNumber, $contactData, $tenant_id, $subdomain);
            if (! $contactResult['success']) {
                $response = [
                    'status' => 'error',
                    'message' => $contactResult['message'],
                ];

                // Include validation errors if they exist
                if (isset($contactResult['errors'])) {
                    $response['errors'] = $contactResult['errors'];
                }

                return response()->json($response, $contactResult['status_code']);
            }

            $contact = $contactResult['contact'];
            $contactCreated = $contactResult['created'];

            // 5. GET WHATSAPP SETTINGS
            $whatsappSettings = $this->getWhatsAppConnectionSettings($tenant_id);
            if (! $whatsappSettings) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('whatsapp_not_connected'),
                ], 503);
            }

            // 6. CHECK CONVERSATION LIMITS
            if ($this->featureLimitChecker->checkConversationLimit($contact->id, $tenant_id, $subdomain, $contact->type)) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('conversation_limit_reached_upgrade_plan'),
                    'limit_reached' => true,
                ], 403);
            }

            // 7. EXTRACT AND PARSE TEMPLATE PARAMETERS
            $templateParams = $this->extractAndParseTemplateParameters($request, $contact, $tenant_id);

            // 8. PREPARE TEMPLATE DATA
            $templateData = [
                'rel_type' => $contact->type ?? 'guest',
                'rel_id' => $contact->id,
                'tenant_id' => $tenant_id,
                'template_id' => $template->template_id,
                'template_name' => $template->template_name,
                'language' => $template->language,
                'header_data_format' => $template->header_data_format,
                'header_data_text' => $template->header_data_text,
                'body_data' => $template->body_data,
                'footer_data' => $template->footer_data,
                'buttons_data' => $template->buttons_data,
                'header_params_count' => $template->header_params_count ?? 0,
                'body_params_count' => $template->body_params_count ?? 0,
                'footer_params_count' => $template->footer_params_count ?? 0,
                'filename' => $headerMediaResult['filename'] ?? '',
                'filelink' => $headerMediaResult['filelink'] ?? '',
                'header_message' => $template->header_data_text,
                'body_message' => $template->body_data,
                'footer_message' => $template->footer_data,
            ];

            // Add parsed parameters
            if (! empty($templateParams['header'])) {
                $templateData['header_params'] = json_encode($templateParams['header']);
            }
            if (! empty($templateParams['body'])) {
                $templateData['body_params'] = json_encode($templateParams['body']);
            }
            if (! empty($templateParams['footer'])) {
                $templateData['footer_params'] = json_encode($templateParams['footer']);
            }

            // 9. SEND TEMPLATE
            $fromPhoneNumberId = $request->input('from_phone_number_id');
            $whatsappResult = $this->sendWhatsAppTemplate($phoneNumber, $templateData, 'campaign', $fromPhoneNumberId);

            if ($whatsappResult['status']) {
                // 10. CREATE CHAT INTERACTION
                $chatInteraction = $this->createOrUpdateChatInteraction(
                    $phoneNumber,
                    $contact,
                    $whatsappSettings,
                    "Template: {$templateName}",
                    $tenant_id,
                    $subdomain
                );

                // 11. STORE TEMPLATE MESSAGE IN CHAT
                $this->storeTemplateMessageInChat(
                    $templateData,
                    $chatInteraction->id,
                    $contact,
                    'template_bot',
                    $whatsappResult['data'] ?? [],
                    $tenant_id,
                    $subdomain,
                    $headerMediaResult
                );

                // 12. TRACK CONVERSATION USAGE
                $this->trackConversationUsage($contact->id, 'contact', $tenant_id, $subdomain);

                return response()->json([
                    'status' => 'success',
                    'message' => t('template_sent_successfully'),
                    'data' => [
                        'contact' => [
                            'id' => $contact->id,
                            'phone' => $contact->phone,
                            'first_name' => $contact->firstname ?? '',
                            'last_name' => $contact->lastname ?? '',
                            'email' => $contact->email ?? '',
                            'created' => $contactCreated,
                        ],
                        'template' => [
                            'template_name' => $templateName,
                            'language' => $language,
                            'sent' => true,
                            'header_format' => $template->header_data_format,
                            'has_media' => ! empty($headerMediaResult['filename']),
                        ],
                        'whatsapp_response' => $whatsappResult['data'] ?? [],
                        'media_info' => $headerMediaResult ? [
                            'filename' => $headerMediaResult['filename'],
                            'media_url' => $headerMediaResult['media_url'] ?? null,
                            'media_type' => $headerMediaResult['media_type'] ?? null,
                        ] : null,
                    ],
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $whatsappResult['message'] ?? t('failed_to_send_template'),
                    'errors' => [
                        'whatsapp' => [$whatsappResult['message'] ?? t('whatsapp_api_error')],
                    ],
                ], 500);
            }
        } catch (\Exception $e) {
            whatsapp_log('API Template Send Error', 'error', [
                'phone_number' => $phoneNumber,
                'template_name' => $templateName,
                'tenant_id' => $tenant_id,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return response()->json([
                'status' => 'error',
                'message' => t('internal_server_error'),
                'errors' => [
                    'system' => [t('template_send_system_error')],
                ],
            ], 500);
        }
    }

    /**
     * Send Media Message
     * Validate template requirements based on template structure
     */
    private function validateTemplateRequirements($template, Request $request)
    {
        $errors = [];
        $fieldMissing = [];

        // 1. HEADER VALIDATION
        if ($template->header_data_format) {
            switch ($template->header_data_format) {
                case 'TEXT':
                    if ($template->header_params_count > 0) {
                        if (! $request->filled('header_field_1')) {
                            $fieldMissing[] = 'header_field_1 (Header text parameter is required for TEXT template with parameters)';
                        }
                    }
                    break;

                case 'IMAGE':
                    if (! $request->filled('header_image_url') && ! $request->hasFile('header_image_file')) {
                        $fieldMissing[] = 'header_image_url or header_image_file (Header image is required for IMAGE template)';
                    }
                    break;

                case 'VIDEO':
                    if (! $request->filled('header_video_url') && ! $request->hasFile('header_video_file')) {
                        $fieldMissing[] = 'header_video_url or header_video_file (Header video is required for VIDEO template)';
                    }
                    break;

                case 'DOCUMENT':
                    if (! $request->filled('header_document_url') && ! $request->hasFile('header_document_file')) {
                        $fieldMissing[] = 'header_document_url or header_document_file (Header document is required for DOCUMENT template)';
                    }
                    break;
            }
        }

        // 2. BODY VALIDATION - Check required parameters
        if ($template->body_params_count > 0) {
            for ($i = 1; $i <= $template->body_params_count; $i++) {
                if (! $request->filled("field_{$i}")) {
                    $fieldMissing[] = "field_{$i} (Body parameter {$i} is required for this template)";
                }
            }
        }

        // 3. FOOTER VALIDATION (if has parameters)
        if ($template->footer_params_count > 0) {
            // Footer parameters are usually handled by button parameters or other fields
        }

        // 4. BUTTON VALIDATION (if template has buttons)
        if (! empty($template->buttons_data)) {
            $buttons = json_decode($template->buttons_data, true);
            if ($buttons) {
                foreach ($buttons as $index => $button) {
                    if (isset($button['type'])) {
                        switch ($button['type']) {
                            case 'QUICK_REPLY':
                                // Quick reply buttons might need copy codes
                                break;
                            case 'URL':
                                // URL buttons might need dynamic URLs
                                if (! $request->has("button_{$index}")) {
                                    // URL parameter might be optional
                                }
                                break;
                            case 'PHONE_NUMBER':
                                // Phone number buttons might need dynamic numbers
                                if (! $request->has("button_{$index}")) {
                                    // Phone parameter might be optional
                                }
                                break;
                        }
                    }
                }
            }
        }

        if (! empty($fieldMissing)) {
            $errors['template_fields'] = $fieldMissing;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Handle header media files and URLs for templates
     */
    private function handleTemplateHeaderMedia($template, Request $request, $tenant_id)
    {
        $mediaType = strtolower($template->header_data_format); // image, video, document
        $allowedMedia = get_meta_allowed_extension();

        $rules = $allowedMedia[$mediaType] ?? null;
        if (! $rules) {
            return [
                'success' => false,
                'message' => "Invalid header media type: {$mediaType}",
                'errors' => ['header_media' => ["Unsupported media type for header: {$mediaType}"]],
            ];
        }

        // Convert allowed extensions string to array
        $allowedExtensions = array_map(function ($ext) {
            return strtolower(ltrim(trim($ext), '.'));
        }, explode(',', $rules['extension']));

        $mediaUrl = null;
        $fileName = null;
        $fileExt = null;

        try {
            // Check if file upload or URL is provided
            $fileKey = "header_{$mediaType}_file";
            $urlKey = "header_{$mediaType}_url";

            if ($request->hasFile($fileKey)) {
                // Handle file upload
                $file = $request->file($fileKey);
                $fileExt = strtolower($file->getClientOriginalExtension());
                $fileSizeMB = $file->getSize() / (1024 * 1024);

                // Validate extension
                if (! in_array($fileExt, $allowedExtensions)) {
                    return [
                        'success' => false,
                        'message' => "Invalid file extension for header {$mediaType}",
                        'errors' => ['header_media' => ['Invalid file extension. Allowed: '.implode(', ', $allowedExtensions)]],
                    ];
                }

                // Validate file size
                if ($fileSizeMB > $rules['size']) {
                    return [
                        'success' => false,
                        'message' => "File size exceeds limit for header {$mediaType}",
                        'errors' => ['header_media' => ["File size exceeds limit. Max allowed is {$rules['size']} MB"]],
                    ];
                }

                // Generate unique filename
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = \Illuminate\Support\Str::slug($originalName, '_');
                $fileName = 'template_header_'.time().'_'.$cleanName.'.'.$fileExt;

                // Store the file
                $file->storeAs('whatsapp-templates', $fileName, 'public');
                $mediaUrl = url('storage/whatsapp-templates/'.$fileName);
            } elseif ($request->has($urlKey)) {
                // Handle URL
                $mediaUrl = $request->input($urlKey);

                // Validate URL accessibility
                $headers = @get_headers($mediaUrl, 1);
                if (! $headers) {
                    return [
                        'success' => false,
                        'message' => 'Cannot access header media URL',
                        'errors' => ['header_media' => ['The provided media URL is not accessible']],
                    ];
                }

                // Extract file info from URL
                $pathInfo = pathinfo(parse_url($mediaUrl, PHP_URL_PATH));
                $fileExt = strtolower($pathInfo['extension'] ?? '');

                // Validate extension from URL
                if ($fileExt && ! in_array($fileExt, $allowedExtensions)) {
                    return [
                        'success' => false,
                        'message' => "Invalid file extension in URL for header {$mediaType}",
                        'errors' => ['header_media' => ['Invalid file extension in URL. Allowed: '.implode(', ', $allowedExtensions)]],
                    ];
                }

                // Check file size from headers
                if (isset($headers['Content-Length'])) {
                    $fileSizeBytes = is_array($headers['Content-Length']) ? end($headers['Content-Length']) : $headers['Content-Length'];
                    $fileSizeMB = $fileSizeBytes / (1024 * 1024);

                    if ($fileSizeMB > $rules['size']) {
                        return [
                            'success' => false,
                            'message' => "File size exceeds limit for header {$mediaType}",
                            'errors' => ['header_media' => ["File size exceeds limit. Max allowed is {$rules['size']} MB"]],
                        ];
                    }
                }

                // Optionally download and store the file locally for better reliability
                try {
                    $fileContents = file_get_contents($mediaUrl);
                    if ($fileContents !== false) {
                        $originalName = $pathInfo['filename'] ?? 'template_media';
                        $cleanName = \Illuminate\Support\Str::slug($originalName, '_');
                        $fileName = 'template_header_'.time().'_'.$cleanName.($fileExt ? '.'.$fileExt : '');

                        \Illuminate\Support\Facades\Storage::disk('public')->put('whatsapp-templates/'.$fileName, $fileContents);
                        $mediaUrl = url('storage/whatsapp-templates/'.$fileName);
                    }
                } catch (\Exception $e) {
                    whatsapp_log('Error downloading header media from URL', 'warning', [
                        'url' => $mediaUrl,
                        'error' => $e->getMessage(),
                    ], $e, $tenant_id);
                    // Continue with original URL if download fails
                }
            }

            return [
                'success' => true,
                'filename' => 'whatsapp-templates/'.$fileName,
                'filelink' => 'whatsapp-templates/'.$fileName,
                'media_url' => $mediaUrl,
                'media_type' => $mediaType,
                'file_extension' => $fileExt,
            ];
        } catch (\Exception $e) {
            whatsapp_log('Error handling template header media', 'error', [
                'template_id' => $template->id,
                'media_type' => $mediaType,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return [
                'success' => false,
                'message' => 'Error processing header media: '.$e->getMessage(),
                'errors' => ['header_media' => ['Error processing header media file']],
            ];
        }
    }

    /**
     * Extract and parse template parameters from request
     */
    private function extractAndParseTemplateParameters(Request $request, $contact, $tenant_id)
    {
        $params = [
            'header' => [],
            'body' => [],
            'footer' => [],
            'buttons' => [],
        ];

        // Header parameters
        if ($request->has('header_field_1')) {
            $headerText = $this->parseMessageText([
                'rel_type' => $contact->type,
                'rel_id' => $contact->id,
                'reply_text' => $request->input('header_field_1'),
                'tenant_id' => $tenant_id,
            ]);
            $params['header'][] = $headerText['reply_text'];
        }

        // Body parameters (field_1 to field_10)
        for ($i = 1; $i <= 10; $i++) {
            if ($request->has("field_{$i}")) {
                $bodyText = $this->parseMessageText([
                    'rel_type' => $contact->type,
                    'rel_id' => $contact->id,
                    'reply_text' => $request->input("field_{$i}"),
                    'tenant_id' => $tenant_id,
                ]);
                $params['body'][] = $bodyText['reply_text'];
            }
        }

        // Button parameters
        for ($i = 0; $i <= 2; $i++) {
            if ($request->has("button_{$i}")) {
                $buttonText = $this->parseMessageText([
                    'rel_type' => $contact->type,
                    'rel_id' => $contact->id,
                    'reply_text' => $request->input("button_{$i}"),
                    'tenant_id' => $tenant_id,
                ]);
                $params['buttons'][] = $buttonText['reply_text'];
            }
        }

        return $params;
    }

    /**
     * Store template message in chat with proper formatting including media
     */
    private function storeTemplateMessageInChat($templateData, $interactionId, $contact, $type, $response, $tenant_id, $subdomain, $headerMediaResult = null)
    {
        try {
            // Parse template content with contact data - using the exact pattern you specified
            $header = parseText($templateData['rel_type'], 'header', $templateData);
            $body = parseText($templateData['rel_type'], 'body', $templateData);
            $footer = parseText($templateData['rel_type'], 'footer', $templateData);

            // Build buttons HTML
            $buttonHtml = '';
            if (! empty(json_decode($templateData['buttons_data']))) {
                $buttons = json_decode($templateData['buttons_data']);
                $buttonHtml = "<div class='flex flex-col mt-2 space-y-2'>";
                foreach ($buttons as $button) {
                    $buttonHtml .= "<button class='bg-gray-100 text-success-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full
                    dark:bg-gray-800 dark:text-success-400'>".e($button->text).'</button>';
                }
                $buttonHtml .= '</div>';
            }

            // Build header data based on format
            $headerData = '';

            // Use the filename from headerMediaResult if available, otherwise from templateData
            $filename = $headerMediaResult['filename'] ?? $templateData['filename'] ?? '';

            if (! empty($filename)) {
                $fileExtensions = get_meta_allowed_extension();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $fileType = array_key_first(array_filter($fileExtensions, fn ($data) => in_array('.'.$extension, explode(', ', $data['extension']))));

                if ($templateData['header_data_format'] === 'IMAGE' && $fileType == 'image') {
                    $headerData = "<a href='".asset('storage/'.$filename)."' data-lightbox='image-group'>
                    <img src='".asset('storage/'.$filename)."' class='rounded-lg w-full mb-2'>
                </a>";
                } elseif ($templateData['header_data_format'] === 'DOCUMENT') {
                    $headerData = "<a href='".asset('storage/'.$filename)."' target='_blank' class='btn btn-secondary w-full'>".t('document').'</a>';
                } elseif ($templateData['header_data_format'] === 'VIDEO') {
                    $headerData = "<video src='".asset('storage/'.$filename)."' controls class='rounded-lg w-full'></video>";
                }
            }

            // Handle TEXT header format or empty format
            if ($templateData['header_data_format'] === 'TEXT' || $templateData['header_data_format'] === '' || empty($headerData)) {
                $headerData = "<span class='font-bold mb-3'>".nl2br(decodeWhatsAppSigns(e($header ?? ''))).'</span>';
            }

            // Build the complete chat message following your exact pattern
            $chat_message = [
                'interaction_id' => $interactionId,
                'sender_id' => get_tenant_setting_by_tenant_id('whatsapp', 'wm_default_phone_number', null, $tenant_id),
                'url' => $headerMediaResult['media_url'] ?? null,
                'message' => "
                $headerData
                <p>".nl2br(decodeWhatsAppSigns(e($body)))."</p>
                <span class='text-gray-500 text-sm'>".nl2br(decodeWhatsAppSigns(e($footer ?? '')))."</span>
                $buttonHtml
            ",
                'status' => 'sent',
                'time_sent' => now()->toDateTimeString(),
                'message_id' => $response->messages[0]->id ?? $response->messages[0]->id ?? null,
                'staff_id' => 0,
                'type' => 'text',
                'tenant_id' => $tenant_id,
                'is_read' => '1',
            ];

            $message_id = ChatMessage::fromTenant($subdomain)->insertGetId($chat_message);

            // Send real-time notification via Pusher if configured
            if (
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_key', null, $tenant_id)) &&
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_secret', null, $tenant_id)) &&
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_id', null, $tenant_id)) &&
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'cluster', null, $tenant_id))
            ) {
                $pusherService = new PusherService($tenant_id);
                $pusherService->trigger('whatsmark-saas-chat-channel', 'whatsmark-saas-chat-event', [
                    'chat' => ManageChat::newChatMessage($interactionId, $message_id, $tenant_id),
                ]);
            }

            return $message_id;
        } catch (\Exception $e) {
            whatsapp_log('Error storing template message in chat', 'error', [
                'interaction_id' => $interactionId,
                'template_name' => $templateData['template_name'] ?? 'unknown',
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return null;
        }
    }

    /**
     * Send Media Message to Contact
     *
     * Send a media message (image, document, video, audio) with optional text to a specific phone number via WhatsApp.
     * Optionally creates a contact if it doesn't exist.
     *
     * **Important - 24-Hour Customer Service Window:** Media messages are subject to Meta's customer service window policy,
     * similar to text messages. You can only send media messages within 24 hours of the customer's last message to your business.
     * This window starts or refreshes whenever the customer messages or calls you. After this 24-hour window expires,
     * the media message will not be delivered. Use approved template messages instead for communication beyond the 24-hour window.
     *
     * **File Size Limits:**
     * - Images: Max 5MB (JPEG, PNG, WebP)
     * - Videos: Max 16MB (MP4, 3GPP)
     * - Audio: Max 16MB (MP3, AAC, AMR, OGG)
     * - Documents: Max 100MB (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT)
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     *
     * @bodyParam phone_number string required The phone number to send media to (without + prefix). Example: +919909919284
     * @bodyParam media_type string required Type of media to send. Must be one of: image, document, video, audio. Example: image
     * @bodyParam media_url string required URL of the media file to send (use this OR media_file). Example: https://cdn.pixabay.com/photo/2015/01/07/15/51/woman-591576_1280.jpg
     * @bodyParam media_file file optional Media file upload (use this OR media_url). Max size varies by type.
     * @bodyParam caption string optional Caption text for the media (max 1024 characters). Example: Check out our latest product catalog! 📱
     * @bodyParam filename string optional Custom filename for document type (max 255 characters). Example: Product_Catalog_2024.pdf
     * @bodyParam contact object optional Contact information to create if contact doesn't exist. If not provided and contact not found, a basic contact will be created using the phone number as the name.
     * @bodyParam contact.firstname string Contact's first name. Example: John
     * @bodyParam contact.lastname string Contact's last name. Example: Doe
     * @bodyParam contact.email string Contact's email address (must be valid email format). Example: john.doe@example.com
     * @bodyParam contact.country string Contact's country name or ID. Example: India
     * @bodyParam contact.assigned_id integer optional|auto_fallback User ID to assign this contact to. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. Example: 15
     * @bodyParam contact.groups string Comma-separated group names for contact assignment. Example: VIP Customers,Product Updates
     *
     * **Reference Endpoints for Contact IDs:**
     * Use these endpoints to find exact IDs: `GET /api/v1/{subdomain}/sources`,
     * `GET /api/v1/{subdomain}/statuses`, `GET /api/v1/{subdomain}/groups`
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "message": "Media message sent successfully",
     *   "data": {
     *     "message_id": "wamid.HBgMOTE5ODEwNjAwMDAwFQIAERgSNUU1RjE4MUM0QjY5MjFFNzYzAA==",
     *     "contact_id": 15,
     *     "phone": "+919909919284",
     *     "media_type": "image",
     *     "media_url": "https://cdn.pixabay.com/photo/2015/01/07/15/51/woman-591576_1280.jpg",
     *     "caption": "Check out our latest product catalog! 📱",
     *     "filename": null,
     *     "status": "sent",
     *     "sent_at": "2024-02-08 14:30:25",
     *     "chat_id": 8,
     *     "contact_created": true,
     *     "file_size_mb": 2.3
     *   }
     * }
     * @response status=422 scenario="validation error" {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "phone_number": ["The phone number field is required.", "The phone number must be between 10 and 15 characters."],
     *     "media_type": ["The media type field is required.", "The selected media type is invalid."],
     *     "media_url": ["The media url field is required when media file is not present.", "The media url format is invalid."],
     *     "caption": ["The caption may not be greater than 1024 characters."],
     *     "contact.email": ["The contact email must be a valid email address."]
     *   }
     * }
     * @response status=422 scenario="invalid media type" {
     *   "status": "error",
     *   "message": "Invalid media type",
     *   "allowed_types": ["image", "document", "video", "audio"]
     * }
     * @response status=422 scenario="file validation error" {
     *   "status": "error",
     *   "message": "File validation failed",
     *   "details": {
     *     "file_extension": "txt",
     *     "allowed_extensions": ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx"],
     *     "file_size_mb": 120.5,
     *     "max_size_mb": 100
     *   }
     * }
     * @response status=422 scenario="whatsapp not configured" {
     *   "status": "error",
     *   "message": "WhatsApp connection not configured for this tenant"
     * }
     * @response status=403 scenario="conversation limit reached" {
     *   "status": "error",
     *   "message": "Conversation limit reached. Please upgrade your plan to continue messaging.",
     *   "limit_reached": true
     * }
     * @response status=500 scenario="media upload error" {
     *   "status": "error",
     *   "message": "Failed to upload media to WhatsApp",
     *   "error": "Media upload failed - file corrupted or invalid format"
     * }
     * @response status=500 scenario="whatsapp api error" {
     *   "status": "error",
     *   "message": "Failed to send WhatsApp media message",
     *   "error": "Message delivery failed - recipient phone number not registered with WhatsApp"
     * }
     */
    public function sendMediaMessage(Request $request, $subdomain)
    {
        try {
            // Get tenant ID from request (set by middleware)
            $tenant_id = $request->get('tenant_id');

            // Set WhatsApp tenant context
            $this->setWaTenantId($tenant_id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string|min:10|max:15',
                'media_type' => 'required|string|in:image,document,video,audio',
                'media_url' => 'nullable|url|required_without:media_file',
                'media_file' => 'nullable|file|required_without:media_url',
                'caption' => 'nullable|string|max:1024',
                'filename' => 'nullable|string|max:255',
                'contact' => 'nullable',
                'contact.firstname' => 'nullable|string|max:255',
                'contact.lastname' => 'nullable|string|max:255',
                'contact.email' => 'nullable|email|max:191',
                'contact.country' => 'nullable|string|max:100',
                'contact.assigned_id' => 'nullable|integer',
                'contact.groups' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Clean phone number for WhatsApp
            $phoneNumber = $this->cleanPhoneNumber($request->phone_number);
            $mediaType = $request->input('media_type');
            $mediaUrl = $request->input('media_url');
            $caption = $request->input('caption', '');
            $filename = $request->input('filename', '');
            $contactData = $request->input('contact', []);

            $allowedMedia = get_meta_allowed_extension();

            $rules = $allowedMedia[$mediaType] ?? null;
            if (! $rules) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid media type',
                ], 422);
            }

            // Convert allowed extensions string to array (remove dots & spaces)
            $allowedExtensions = array_map(function ($ext) {
                return strtolower(ltrim(trim($ext), '.'));
            }, explode(',', $rules['extension']));

            if ($request->hasFile('media_file')) {
                // Direct upload case
                $file = $request->file('media_file');
                $fileExt = strtolower($file->getClientOriginalExtension());
                $fileSizeMB = $file->getSize() / (1024 * 1024);

                if (! in_array($fileExt, $allowedExtensions)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Invalid file extension for {$mediaType}. Allowed: ".implode(', ', $allowedExtensions),
                    ], 422);
                }

                if ($fileSizeMB > $rules['size']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "File size exceeds limit for {$mediaType}. Max allowed is {$rules['size']} MB",
                    ], 422);
                }

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                // Sanitize filename: remove special chars, replace spaces
                $cleanName = Str::slug($originalName, '_');

                // Append timestamp to ensure uniqueness
                $fileName = time().'_'.$cleanName.'.'.$fileExt;

                // Store the file
                $file->storeAs('whatsapp-attachments', $fileName, 'public');
                $mediaUrl = url('storage/whatsapp-attachments/'.$fileName);
            } else {
                $mediaUrl = $request->input('media_url');

                $fileContents = @file_get_contents($mediaUrl);
                if ($fileContents === false) {
                    whatsapp_log('Error downloading media file', 'error', [
                        'media_url' => $mediaUrl,
                    ], null, $tenant_id);

                    return null;
                }

                $pathInfo = pathinfo(parse_url($mediaUrl, PHP_URL_PATH));
                $originalName = $pathInfo['filename'] ?? 'whatsapp_file';
                $fileExt = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

                if (! in_array($fileExt, $allowedExtensions)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Invalid file extension for {$mediaType}. Allowed: ".implode(', ', $allowedExtensions),
                    ], 422);
                }

                $fileSizeBytes = 0;
                $headers = @get_headers($mediaUrl, 1);
                if ($headers && isset($headers['Content-Length'])) {
                    $fileSizeBytes = is_array($headers['Content-Length']) ? end($headers['Content-Length']) : $headers['Content-Length'];
                }
                $fileSizeMB = $fileSizeBytes / (1024 * 1024);

                if ($fileSizeMB > $rules['size']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "File size exceeds limit for {$mediaType}. Max allowed is {$rules['size']} MB",
                    ], 422);
                }

                // Sanitize filename
                $cleanName = \Illuminate\Support\Str::slug($originalName, '_');
                $fileName = time().'_'.$cleanName.($fileExt ? '.'.$fileExt : '');

                $storagePath = 'whatsapp-attachments/'.$fileName;
                \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $fileContents);
            }

            // Find or create contact
            $contactResult = $this->findOrCreateContact($phoneNumber, $contactData, $tenant_id, $subdomain);

            if (! $contactResult['success']) {
                $response = [
                    'status' => 'error',
                    'message' => $contactResult['message'],
                ];

                // Include validation errors if they exist
                if (isset($contactResult['errors'])) {
                    $response['errors'] = $contactResult['errors'];
                }

                return response()->json($response, $contactResult['status_code']);
            }

            $contact = $contactResult['contact'];
            $contactCreated = $contactResult['created'];

            // Check if WhatsApp connection is configured for this tenant
            $whatsappSettings = $this->getWhatsAppConnectionSettings($tenant_id);

            if (! $whatsappSettings) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('whatsapp_connection_not_configured'),
                ], 422);
            }

            // Check conversation limits before sending
            if ($this->featureLimitChecker->checkConversationLimit($contact->id, $tenant_id, $subdomain, $contact->type)) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('conversation_limit_reached_upgrade_plan'),
                    'limit_reached' => true,
                ], 403);
            }

            // Parse caption text with contact data if provided
            $parsedCaption = '';
            if ($caption) {
                $parsedMessage = $this->parseMessageText([
                    'rel_type' => $contact->type,
                    'rel_id' => $contact->id,
                    'reply_text' => $caption,
                    'tenant_id' => $tenant_id,
                ]);
                $parsedCaption = $parsedMessage['reply_text'] ?? $caption;
            }

            // Send WhatsApp media message
            $messageResponse = $this->sendWhatsAppMediaMessage(
                $phoneNumber,
                $mediaType,
                $mediaUrl,
                $parsedCaption,
                $filename,
                $whatsappSettings
            );

            if (! $messageResponse['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('failed_to_send_whatsapp_media_message'),
                    'error' => $messageResponse['error'] ?? 'Unknown error',
                ], 500);
            }

            // Create or update chat interaction
            $chatInteraction = $this->createOrUpdateChatInteraction(
                $phoneNumber,
                $contact,
                $whatsappSettings,
                $parsedCaption ?: ucfirst($mediaType).' message',
                $tenant_id,
                $subdomain
            );

            // Save media message to database
            $chatMessage = $this->saveMediaChatMessage(
                $chatInteraction,
                $mediaType,
                $fileName,
                $parsedCaption,
                $filename,
                $messageResponse['message_id'],
                $tenant_id,
                $subdomain
            );

            // Track conversation usage
            $this->trackConversationUsage($contact->id, $contact->type, $tenant_id, $subdomain);

            return response()->json([
                'status' => 'success',
                'message' => t('media_message_sent_successfully'),
                'data' => [
                    'message_id' => $messageResponse['message_id'],
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl,
                    'caption' => $parsedCaption,
                    'filename' => $filename,
                    'status' => 'sent',
                    'sent_at' => now()->toDateTimeString(),
                    'chat_id' => $chatInteraction->id ?? null,
                    'chat_message_id' => $chatMessage->id ?? null,
                    'contact_created' => $contactCreated,
                ],
            ], 200);
        } catch (\Exception $e) {
            whatsapp_log('API send media message error', 'error', [
                'phone_number' => $request->phone_number ?? null,
                'media_type' => $request->media_type ?? null,
                'media_url' => $request->media_url ?? null,
                'tenant_id' => $tenant_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e, $tenant_id ?? null);

            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_send_media_message'),
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Send WhatsApp media message using Cloud API
     */
    private function sendWhatsAppMediaMessage($phoneNumber, $mediaType, $mediaUrl, $caption, $filename, $whatsappSettings)
    {
        try {
            $whatsapp = new WhatsAppCloudApi([
                'from_phone_number_id' => $whatsappSettings['wm_default_phone_number_id'],
                'access_token' => $whatsappSettings['wm_access_token'],
            ]);

            $response = null;
            $link = new LinkID($mediaUrl);

            switch ($mediaType) {
                case 'image':
                    $response = $whatsapp->sendImage($phoneNumber, $link, $caption);
                    break;
                case 'document':
                    $response = $whatsapp->sendDocument($phoneNumber, $link, $caption, $filename);
                    break;
                case 'video':
                    $response = $whatsapp->sendVideo($phoneNumber, $link, $caption);
                    break;
                case 'audio':
                    $response = $whatsapp->sendAudio($phoneNumber, $link);
                    break;
                default:
                    return [
                        'success' => false,
                        'error' => 'Unsupported media type: '.$mediaType,
                    ];
            }

            $responseData = $response->decodedBody();

            if (isset($responseData['messages'][0]['id'])) {
                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'],
                    'response' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No message ID in response',
                    'response' => $responseData,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => $e,
            ];
        }
    }

    /**
     * Save media chat message to database with proper formatting
     */
    private function saveMediaChatMessage($chatInteraction, $mediaType, $mediaUrl, $caption, $filename, $messageId, $tenant_id, $subdomain)
    {
        try {
            if (! $chatInteraction) {
                return null;
            }

            // Build formatted message for chat display
            $formattedMessage = $this->buildFormattedMediaMessage($mediaType, $mediaUrl, $caption, $filename);

            $chatMessage = ChatMessage::fromTenant($subdomain)->create([
                'tenant_id' => $tenant_id,
                'interaction_id' => $chatInteraction->id,
                'sender_id' => $chatInteraction->wa_no,
                'message' => $formattedMessage,
                'message_id' => $messageId,
                'type' => $mediaType,
                'url' => $mediaUrl,
                'staff_id' => null, // API messages don't have staff_id
                'status' => 'sent',
                'time_sent' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'is_read' => 1,
            ]);

            // Send real-time notification via Pusher
            if (
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_key', null, $tenant_id)) &&
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_secret', null, $tenant_id)) &&
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'app_id', null, $tenant_id)) &&
                ! empty(get_tenant_setting_by_tenant_id('pusher', 'cluster', null, $tenant_id))
            ) {

                $pusherService = new PusherService($tenant_id);
                $pusherService->trigger('whatsmark-saas-chat-channel', 'whatsmark-saas-chat-event', [
                    'chat' => ManageChat::newChatMessage($chatInteraction->id, $chatMessage->id, $tenant_id),
                ]);
            }

            return $chatMessage;
        } catch (\Exception $e) {
            whatsapp_log('Error saving media chat message', 'error', [
                'chat_id' => $chatInteraction->id ?? null,
                'message_id' => $messageId,
                'media_type' => $mediaType,
                'media_url' => $mediaUrl,
                'error' => $e->getMessage(),
            ], $e, $tenant_id);

            return null;
        }
    }

    /**
     * Build formatted media message for chat display
     */
    private function buildFormattedMediaMessage($mediaType, $mediaUrl, $caption, $filename)
    {
        $message = '';

        switch ($mediaType) {
            case 'image':
                $message = "<a href='".htmlspecialchars($mediaUrl)."' data-lightbox='image-group'>";
                $message .= "<img src='".htmlspecialchars($mediaUrl)."' class='rounded-lg w-full max-w-sm mb-2' alt='Shared Image'>";
                $message .= '</a>';
                break;

            case 'video':
                $message = "<video src='".htmlspecialchars($mediaUrl)."' controls class='rounded-lg w-full max-w-sm'>";
                $message .= 'Your browser does not support the video tag.';
                $message .= '</video>';
                break;

            case 'audio':
                $message = "<audio controls class='w-64'>";
                $message .= "<source src='".htmlspecialchars($mediaUrl)."' type='audio/mpeg'>";
                $message .= 'Your browser does not support the audio element.';
                $message .= '</audio>';
                break;

            case 'document':
                $displayName = $filename ?: 'Document';
                $message = "<a href='".htmlspecialchars($mediaUrl)."' target='_blank' class='bg-gray-100 text-success-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full dark:bg-gray-800 dark:text-success-400'>";
                $message .= "<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
                $message .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>";
                $message .= '</svg>';
                $message .= '<span>'.htmlspecialchars($displayName).'</span>';
                $message .= '</a>';
                break;

            default:
                $message = '<p>Media file: '.ucfirst($mediaType).'</p>';
                break;
        }

        // Add caption if provided
        if ($caption) {
            $message .= "\n<p class='mt-2 text-sm'>".nl2br(htmlspecialchars($caption)).'</p>';
        }

        return $message;
    }

    /**
     * Helper method to call WhatsApp trait's sendTemplate method
     * This avoids naming conflicts with the controller method
     */
    private function sendWhatsAppTemplate($to, $template_data, $type = 'api', $fromNumber = null)
    {
        // Since we have the WhatsApp trait included, we can call its methods directly
        // The trait's sendTemplate method is available in this class context
        return $this->sendTemplate($to, $template_data, $type, $fromNumber, false);
    }

    /**
     * Extract template parameters from comprehensive JSON structure
     */
    private function extractTemplateParameters(Request $request)
    {
        $params = [
            'header' => [],
            'body' => [],
            'footer' => [],
            'buttons' => [],
            'media' => [],
            'location' => [],
        ];

        // Header parameters
        if ($request->has('header_field_1')) {
            $params['header'][] = $request->input('header_field_1');
        }

        // Media parameters for header
        if ($request->has('header_image')) {
            $params['media']['header_image'] = $request->input('header_image');
        }
        if ($request->has('header_video')) {
            $params['media']['header_video'] = $request->input('header_video');
        }
        if ($request->has('header_document')) {
            $params['media']['header_document'] = $request->input('header_document');
            $params['media']['header_document_name'] = $request->input('header_document_name', 'Document');
        }

        // Body parameters (field_1 to field_10)
        for ($i = 1; $i <= 10; $i++) {
            if ($request->has("field_{$i}")) {
                $params['body'][] = $request->input("field_{$i}");
            }
        }

        // Button parameters
        for ($i = 0; $i <= 2; $i++) {
            if ($request->has("button_{$i}")) {
                $params['buttons'][] = $request->input("button_{$i}");
            }
        }

        // Copy code for quick reply
        if ($request->has('copy_code')) {
            $params['copy_code'] = $request->input('copy_code');
        }

        // Location parameters
        if ($request->has('location_latitude') && $request->has('location_longitude')) {
            $params['location'] = [
                'latitude' => $request->input('location_latitude'),
                'longitude' => $request->input('location_longitude'),
                'name' => $request->input('location_name', ''),
                'address' => $request->input('location_address', ''),
            ];
        }

        // Support legacy template_params format for backward compatibility
        if ($request->has('template_params')) {
            $legacyParams = $request->input('template_params', []);

            if (! empty($legacyParams['header'])) {
                $params['header'] = array_merge($params['header'], $legacyParams['header']);
            }
            if (! empty($legacyParams['body'])) {
                $params['body'] = array_merge($params['body'], $legacyParams['body']);
            }
            if (! empty($legacyParams['footer'])) {
                $params['footer'] = array_merge($params['footer'], $legacyParams['footer']);
            }
        }

        return $params;
    }

    public function storeBotMessages($data, $interactionId, $relData, $type, $response, $tenant_id, $subdomain)
    {
        $data['header_message'] = $data['header_data_text'];
        $data['body_message'] = $data['body_data'];
        $data['footer_message'] = $data['footer_data'];

        if ($type == 'template_bot') {
            $header = parseText($data['rel_type'], 'header', $data);
            $body = parseText($data['rel_type'], 'body', $data);
            $footer = parseText($data['rel_type'], 'footer', $data);
            $buttonHtml = '';
            if (! empty(json_decode($data['buttons_data']))) {
                $buttons = json_decode($data['buttons_data']);
                $buttonHtml = "<div class='flex flex-col mt-2 space-y-2'>";
                foreach ($buttons as $button) {
                    $buttonHtml .= "<button class='bg-gray-100 text-success-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full
                        dark:bg-gray-800 dark:text-success-400'>".e($button->text).'</button>';
                }
                $buttonHtml .= '</div>';
            }

            $headerData = '';
            $fileExtensions = get_meta_allowed_extension();
            $extension = strtolower(pathinfo($data['filename'], PATHINFO_EXTENSION));
            $fileType = array_key_first(array_filter($fileExtensions, fn ($data) => in_array('.'.$extension, explode(', ', $data['extension']))));
            if ($data['header_data_format'] === 'IMAGE' && $fileType == 'image') {
                $headerData = "<a href='".asset('storage/'.$data['filename'])."' data-lightbox='image-group'>
                <img src='".asset('storage/'.$data['filename'])."' class='rounded-lg w-full mb-2'>
            </a>";
            } elseif ($data['header_data_format'] === 'TEXT' || $data['header_data_format'] === '') {
                $headerData = "<span class='font-bold mb-3'>".nl2br(decodeWhatsAppSigns(e($header ?? ''))).'</span>';
            } elseif ($data['header_data_format'] === 'DOCUMENT') {
                $headerData = "<a href='".asset('storage/'.$data['filename'])."' target='_blank' class='btn btn-secondary w-full'>".t('document').'</a>';
            } elseif ($data['header_data_format'] === 'VIDEO') {
                $headerData = "<video src='".asset('storage/'.$data['filename'])."' controls class='rounded-lg w-full'></video>";
            }

            $chat_message = [
                'interaction_id' => $interactionId,
                'sender_id' => get_tenant_setting_by_tenant_id('whatsapp', 'wm_default_phone_number', null, $tenant_id),
                'url' => null,
                'message' => "
                $headerData
                <p>".nl2br(decodeWhatsAppSigns(e($body)))."</p>
                <span class='text-gray-500 text-sm'>".nl2br(decodeWhatsAppSigns(e($footer ?? '')))."</span>
                $buttonHtml
            ",
                'status' => 'sent',
                'time_sent' => now()->toDateTimeString(),
                'message_id' => $response->messages[0]->id ?? null,
                'staff_id' => 0,
                'type' => 'text',
                'tenant_id' => $tenant_id,
                'is_read' => '1',
            ];

            $message_id = ChatMessage::fromTenant($subdomain)->insertGetId($chat_message);

            if (
                ! empty($this->pusher_settings['app_key']) && ! empty($this->pusher_settings['app_secret']) && ! empty($this->pusher_settings['app_id']) && ! empty($this->pusher_settings['cluster'])
            ) {
                $pusherService = new PusherService($subdomain);
                $pusherService->trigger('whatsmark-saas-chat-channel', 'whatsmark-saas-chat-event', [
                    'chat' => ManageChat::newChatMessage($interactionId, $message_id, $subdomain),
                ]);
            }

            return $message_id;
        }

        return null;
    }

    /**
     * Validate that status_id belongs to the current tenant
     */
    private function validateStatusBelongsToTenant($statusId, $tenantId): bool
    {
        if (empty($statusId)) {
            return false;
        }

        return \App\Models\Tenant\Status::where('id', $statusId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    /**
     * Validate that source_id belongs to the current tenant
     */
    private function validateSourceBelongsToTenant($sourceId, $tenantId): bool
    {
        if (empty($sourceId)) {
            return false;
        }

        return \App\Models\Tenant\Source::where('id', $sourceId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    /**
     * Get WhatsApp Auto Lead settings for the tenant
     */
    private function getWhatsAppAutoLeadSettings(int $tenant_id): array
    {
        try {
            $settings = tenant_settings_by_group('whats-mark', $tenant_id);

            return [
                'enabled' => (bool) ($settings['auto_lead_enabled'] ?? false),
                'lead_status' => $settings['lead_status'] ?? null,
                'lead_source' => $settings['lead_source'] ?? null,
                'lead_assigned_to' => $settings['lead_assigned_to'] ?? null,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch WhatsApp Auto Lead settings', [
                'tenant_id' => $tenant_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'enabled' => false,
                'lead_status' => null,
                'lead_source' => null,
                'lead_assigned_to' => null,
            ];
        }
    }
}
