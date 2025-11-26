<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Contact as TenantContact;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @group Contact Management
 *
 * APIs for managing contacts within the tenant context.
 *
 * Contacts are the core entities in your WhatsApp marketing system. Each contact represents
 * a potential or existing customer with whom you can communicate via WhatsApp.
 *
 * **Contact Types:**
 * - `lead`: Potential customers who haven't converted yet
 * - `customer`: Existing customers or converted leads
 *
 * **Key Features:**
 * - Create and manage contacts with comprehensive data fields
 * - Assign contacts to multiple groups for targeted messaging
 * - Track contact sources and statuses for better analytics
 * - Automatic contact creation when sending messages to new numbers
 * - Unique phone number and email validation within tenant context
 */
class ContactController extends Controller
{
    protected $featureLimitChecker;

    /**
     * ContactController constructor.
     */
    public function __construct(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    /**
     * List Contacts
     *
     * Retrieve a paginated list of all contacts in your tenant. You can filter and search
     * contacts using various query parameters to find specific segments of your audience.
     *
     * **Use Cases:**
     * - Browse all contacts in your database
     * - Filter contacts by type (leads vs customers)
     * - Find contacts from specific sources or with certain statuses
     * - Implement pagination for large contact lists
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     *
     * @queryParam type string Filter contacts by type. Must be either "lead" or "customer". Example: lead
     * @queryParam source_id integer Filter contacts by their source ID (where they came from). Example: 1
     * @queryParam status_id integer Filter contacts by their current status ID. Example: 1
     * @queryParam page integer The page number for pagination. Default: 1. Example: 1
     * @queryParam per_page integer Number of contacts per page. Maximum: 100, Default: 15. Example: 15
     *
     * @response scenario="success" status=200 {
     *   "status": "success",
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "firstname": "John",
     *         "lastname": "Doe",
     *         "company": "Demo Company",
     *         "type": "lead",
     *         "email": "john@example.com",
     *         "phone": "+1234567890",
     *         "source_id": 1,
     *         "status_id": 1,
     *         "description": "Interested in premium services",
     *         "country_id": 101,
     *         "group_id": [1, 2],
     *         "created_at": "2024-02-08T10:00:00.000000Z",
     *         "updated_at": "2024-02-08T10:00:00.000000Z"
     *       }
     *     ],
     *     "first_page_url": "https://yourdomain.com/api/v1/tenantx/contacts?page=1",
     *     "from": 1,
     *     "last_page": 7,
     *     "last_page_url": "https://yourdomain.com/api/v1/tenantx/contacts?page=7",
     *     "links": [
     *       {
     *         "url": null,
     *         "label": "&laquo; Previous",
     *         "active": false
     *       },
     *       {
     *         "url": "https://yourdomain.com/api/v1/tenantx/contacts?page=1",
     *         "label": "1",
     *         "active": true
     *       }
     *     ],
     *     "next_page_url": "https://yourdomain.com/api/v1/tenantx/contacts?page=2",
     *     "path": "https://yourdomain.com/api/v1/tenantx/contacts",
     *     "per_page": 15,
     *     "prev_page_url": null,
     *     "to": 15,
     *     "total": 100
     *   }
     * }
     * @response scenario="empty results" status=200 {
     *   "status": "success",
     *   "data": {
     *     "current_page": 1,
     *     "data": [],
     *     "total": 0,
     *     "per_page": 15
     *   }
     * }
     * @response scenario="unauthenticated" status=401 {
     *   "message": "Unauthenticated."
     * }
     * @response scenario="invalid subdomain" status=400 {
     *   "status": "error",
     *   "message": "Invalid or inactive tenant"
     * }
     */
    public function index(Request $request, $subdomain)
    {
        try {
            $query = TenantContact::fromTenant($subdomain)->query();

            // Filter by type if provided
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by source if provided
            if ($request->has('source_id')) {
                $query->where('source_id', $request->source_id);
            }

            // Filter by status if provided
            if ($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            }

            $contacts = $query
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $contacts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_contact'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a New Contact
     *
     * Add a new contact to your tenant database. You can specify the contact type (lead or customer),
     * assign them to groups, and include all relevant contact information.
     *
     * **Key Features:**
     * - Automatic validation of contact data
     * - Type classification (lead vs customer)
     * - Group assignment with auto-creation of new groups
     * - Source and status tracking
     * - International phone and country support
     * - **WhatsApp Auto Lead Settings Integration**: Automatic fallback to configured defaults
     *
     * **WhatsApp Auto Lead Fallback:**
     * When WhatsApp Auto Lead is enabled in your tenant settings, the system will automatically
     * provide default values for missing fields:
     * - `status_id`: Uses the configured default lead status if not provided
     * - `source_id`: Uses the configured default lead source if not provided
     * - `assignee`: Uses the configured default assignee if not provided
     *
     * This allows for simplified API calls where you only need to provide the essential contact
     * information, and the system handles lead management defaults automatically.
     *
     * **Available Reference Endpoints:**
     * To find the exact IDs for sources, statuses, and groups, use these endpoints:
     * - **Sources**: `GET /api/v1/{subdomain}/sources` - List all available sources with their IDs
     * - **Statuses**: `GET /api/v1/{subdomain}/statuses` - List all available statuses with their IDs
     * - **Groups**: `GET /api/v1/{subdomain}/groups` - List all available groups with their IDs
     * - **Individual items**: Use `GET /api/v1/{subdomain}/{resource}/{id}` to get specific details
     *
     * **Business Rules:**
     * - Email must be unique within the tenant (if provided)
     * - Phone numbers must be unique within the tenant
     * - Groups will be auto-created if they don't exist
     * - Contact creation counts towards your plan limits
     * - Auto Lead fallback only applies when the feature is enabled in tenant settings
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     *
     * @bodyParam firstname string required First name of the contact (max 255 characters). Example: John
     * @bodyParam lastname string optional Last name of the contact (max 255 characters). Example: Doe
     * @bodyParam company string optional Company or organization name (max 255 characters). Example: Corbital Technologies LLP
     * @bodyParam type string required Type of contact. Must be either "lead" or "customer". Example: lead
     * @bodyParam email string optional Email address (must be unique within tenant, max 191 characters). Example: john.doe@example.com
     * @bodyParam phone string required Phone number with country code (must be unique within tenant, max 20 characters). Example: +919925119284
     * @bodyParam source_id integer required|auto_fallback ID of the source where this contact came from. Must belong to the current tenant. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. If no valid default is available, an error will be returned. Example: 2
     * @bodyParam status_id integer required|auto_fallback Current status ID of the contact. Must belong to the current tenant. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. If no valid default is available, an error will be returned. Example: 1
     * @bodyParam description string optional Additional notes or description about the contact. Example: Potential client interested in premium services, contacted via website form
     * @bodyParam country_id integer optional Country ID for the contact's location. Example: 101
     * @bodyParam assigned_id integer optional|auto_fallback User ID to assign this contact to. When WhatsApp Auto Lead is enabled, this field will use the configured default if not provided. Example: 15
     * @bodyParam groups string optional Comma-separated group names to assign the contact to. Groups will be auto-created if they don't exist. Example: VIP Customers,Newsletter Subscribers,Product Updates
     *
     * @response scenario="contact created successfully" status=201 {
     *   "status": "success",
     *   "message": "Contact created successfully",
     *   "data": {
     *     "id": 25,
     *     "tenant_id": 13,
     *     "firstname": "John",
     *     "lastname": "Doe",
     *     "company": "Corbital Technologies LLP",
     *     "type": "lead",
     *     "email": "john.doe@example.com",
     *     "phone": "+919925119284",
     *     "source_id": 2,
     *     "status_id": 1,
     *     "description": "Potential client interested in premium services, contacted via website form",
     *     "country_id": 101,
     *     "assigned_id": 15,
     *     "addedfrom": 15,
     *     "created_at": "2024-02-08T14:30:25.000000Z",
     *     "updated_at": "2024-02-08T14:30:25.000000Z",
     *     "group_id": [1, 2, 3]
     *   }
     * }
     * @response scenario="validation errors" status=422 {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "firstname": [
     *       "The firstname field is required."
     *     ],
     *     "email": [
     *       "The email has already been taken.",
     *       "The email must be a valid email address."
     *     ],
     *     "phone": [
     *       "The phone has already been taken."
     *     ],
     *     "type": [
     *       "The selected type is invalid."
     *     ],
     *     "status_id": [
     *       "The selected status does not belong to this tenant."
     *     ],
     *     "source_id": [
     *       "The selected source does not belong to this tenant."
     *     ]
     *   }
     * }
     * @response scenario="required fields missing when auto-lead not configured" status=422 {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "status_id": [
     *       "The status field is required. Either provide a status_id or configure auto lead settings with a default status."
     *     ],
     *     "source_id": [
     *       "The source field is required. Either provide a source_id or configure auto lead settings with a default source."
     *     ]
     *   }
     * }
     * @response scenario="plan limit exceeded" status=400 {
     *   "status": "error",
     *   "message": "Contact limit exceeded for your current plan. Upgrade to add more contacts."
     * }
     * @response scenario="unauthenticated" status=401 {
     *   "message": "Unauthenticated."
     * }
     * @response scenario="invalid subdomain" status=400 {
     *   "status": "error",
     *   "message": "Invalid or inactive tenant"
     * }
     */
    public function store(Request $request, $subdomain)
    {

        $contactTable = TenantContact::fromTenant($subdomain)->getTable();
        $tenant_id = $request->get('tenant_id');
        $addedfrom = User::where('tenant_id', $tenant_id)
            ->value('id');
        $request->merge(['addedfrom' => $addedfrom]);

        try {
            // Apply WhatsApp Auto Lead settings fallback if enabled
            $requestData = $request->all();
            $autoLeadSettings = $this->getWhatsAppAutoLeadSettings($tenant_id);

            if ($autoLeadSettings['enabled']) {
                if (! $request->has('status_id') && ! empty($autoLeadSettings['lead_status'])) {
                    $requestData['status_id'] = $autoLeadSettings['lead_status'];
                }

                if (! $request->has('source_id') && ! empty($autoLeadSettings['lead_source'])) {
                    $requestData['source_id'] = $autoLeadSettings['lead_source'];
                }

                if (! $request->has('assigned_id') && ! empty($autoLeadSettings['lead_assigned_to'])) {
                    $requestData['assigned_id'] = $autoLeadSettings['lead_assigned_to'];
                }
            }

            // Final validation to ensure required fields are present
            if (empty($requestData['status_id'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => [
                        'status_id' => [t('status_id_is_required_configure_auto_lead_settings')],
                    ],
                ], 422);
            }

            if (empty($requestData['source_id'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => [
                        'source_id' => [t('source_id_is_required_configure_auto_lead_settings')],
                    ],
                ], 422);
            }
            $validator = Validator::make($requestData, [
                'firstname' => 'required|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'company' => 'nullable|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    'max:191',
                    Rule::unique($contactTable, 'email')->where(function ($query) use ($tenant_id) {
                        return $query->where('tenant_id', $tenant_id);
                    }),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique($contactTable, 'phone')
                        ->where(function ($query) use ($tenant_id) {
                            return $query->where('tenant_id', $tenant_id);
                        }),
                ],
                'type' => 'required|in:lead,customer',
                'source_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($tenant_id) {
                        if (! empty($value)) {
                            $exists = \App\Models\Tenant\Source::where('id', $value)
                                ->where('tenant_id', $tenant_id)
                                ->exists();

                            if (! $exists) {
                                $fail(t('selected_source_does_not_belong_to_tenant'));
                            }
                        }
                    },
                ],
                'status_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($tenant_id) {
                        if (! empty($value)) {
                            $exists = \App\Models\Tenant\Status::where('id', $value)
                                ->where('tenant_id', $tenant_id)
                                ->exists();

                            if (! $exists) {
                                $fail(t('selected_status_does_not_belong_to_tenant'));
                            }
                        }
                    },
                ],
                'description' => 'nullable|string',
                'country_id' => 'nullable|integer',
                'assigned_id' => 'nullable|integer',
                'groups' => 'nullable|string',
            ], [
                'source_id.required' => t('source_id_is_required'),
                'status_id.required' => t('status_id_is_required'),
                'source_id.integer' => t('source_id_must_be_integer'),
                'status_id.integer' => t('status_id_must_be_integer'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($this->featureLimitChecker->hasReachedLimit('contacts', TenantContact::class)) {
                $this->featureLimitChecker->trackUsage('contacts');

                return response()->json([
                    'status' => 'error',
                    'message' => t('contact_limit_reached_upgrade_plan'),
                ], 403);
            }

            $contact = TenantContact::fromTenant($subdomain)->create(collect($requestData)->except(['groups'])->toArray());

            // Handle groups if provided
            if (! empty($requestData['groups'])) {
                $this->assignContactToGroups($contact, $requestData['groups'], $tenant_id);
            }

            $this->featureLimitChecker->trackUsage('contacts');

            return response()->json([
                'status' => 'success',
                'message' => t('contact_created_successfully'),
                'data' => $contact,
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_create_contact'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Contact Details
     *
     * Retrieve detailed information about a specific contact by their ID. This endpoint
     * returns all available contact data including personal information, groups, and metadata.
     *
     * **Use Cases:**
     * - View complete contact profile
     * - Check contact details before updates
     * - Display contact information in admin interfaces
     * - Verify contact data for integrations
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     * @urlParam id integer required The ID of the contact to retrieve. Example: 25
     *
     * @response scenario="contact found" status=200 {
     *   "status": "success",
     *   "data": {
     *     "id": 25,
     *     "tenant_id": 13,
     *     "firstname": "John",
     *     "lastname": "Doe",
     *     "company": "Corbital Technologies LLP",
     *     "type": "lead",
     *     "email": "john.doe@example.com",
     *     "phone": "+919925119284",
     *     "source_id": 2,
     *     "status_id": 1,
     *     "description": "Potential client interested in premium services",
     *     "country_id": 101,
     *     "group_id": [1, 2],
     *     "addedfrom": 15,
     *     "created_at": "2024-02-08T14:30:25.000000Z",
     *     "updated_at": "2024-02-08T14:30:25.000000Z"
     *   }
     * }
     * @response scenario="contact not found" status=404 {
     *   "status": "error",
     *   "message": "Contact not found"
     * }
     * @response scenario="unauthenticated" status=401 {
     *   "message": "Unauthenticated."
     * }
     * @response scenario="invalid subdomain" status=400 {
     *   "status": "error",
     *   "message": "Invalid or inactive tenant"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $contact = TenantContact::fromTenant($subdomain)
                ->where('id', $id)
                ->first();

            if (! $contact) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('contact_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $contact,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('contact_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update Contact Information
     *
     * Modify an existing contact's details with full validation and group management.
     * This endpoint allows you to update any contact field while maintaining data integrity.
     *
     * **Key Features:**
     * - Update any contact field individually or in bulk
     * - Automatic validation with unique email/phone checks
     * - Group assignment with auto-creation
     * - Contact type conversion (lead to customer)
     * - Maintains audit trail with timestamps
     *
     * **Business Rules:**
     * - Email must remain unique within the tenant (if changed)
     * - Phone must remain unique within the tenant (if changed)
     * - Groups will be auto-created if they don't exist
     * - Original contact ID and tenant cannot be changed
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     * @urlParam id integer required The ID of the contact to update. Example: 25
     *
     * @bodyParam firstname string required First name of the contact (max 255 characters). Example: John
     * @bodyParam lastname string required Last name of the contact (max 255 characters). Example: Doe
     * @bodyParam company string optional Company or organization name (max 255 characters). Example: Corbital Technologies LLP
     * @bodyParam type string required Type of contact. Must be either "lead" or "customer". Example: customer
     * @bodyParam email string optional Email address (must be unique within tenant, max 191 characters). Example: john.doe@acmecorp.com
     * @bodyParam phone string required Phone number with country code (must be unique within tenant, max 20 characters). Example: +919925119284
     * @bodyParam source_id integer required ID of the source where this contact originated from. Must belong to the current tenant. Example: 2
     * @bodyParam status_id integer required Current status ID of the contact. Must belong to the current tenant. Example: 3
     * @bodyParam description string optional Additional notes or description about the contact. Example: Updated client status after successful meeting, moved to active customer
     * @bodyParam country_id integer optional Country ID for the contact's location. Example: 101
     * @bodyParam groups string optional Comma-separated group names to assign the contact to. Example: Active Customers,Premium Support,Monthly Newsletter
     *
     * @response scenario="contact updated successfully" status=200 {
     *   "status": "success",
     *   "message": "Contact updated successfully",
     *   "data": {
     *     "id": 25,
     *     "tenant_id": 13,
     *     "firstname": "John",
     *     "lastname": "Doe",
     *     "company": "Corbital Technologies LLP",
     *     "type": "customer",
     *     "email": "john.doe@acmecorp.com",
     *     "phone": "+919925119284",
     *     "source_id": 2,
     *     "status_id": 3,
     *     "description": "Updated client status after successful meeting, moved to active customer",
     *     "country_id": 101,
     *     "group_id": [1, 2, 3],
     *     "addedfrom": 15,
     *     "created_at": "2024-02-08T14:30:25.000000Z",
     *     "updated_at": "2024-02-08T16:45:30.000000Z"
     *   }
     * }
     * @response scenario="contact not found" status=404 {
     *   "status": "error",
     *   "message": "Contact not found"
     * }
     * @response scenario="validation errors" status=422 {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "firstname": [
     *       "The firstname field is required.",
     *       "The firstname may not be greater than 255 characters."
     *     ],
     *     "email": [
     *       "The email has already been taken.",
     *       "The email must be a valid email address."
     *     ],
     *     "phone": [
     *       "The phone has already been taken."
     *     ],
     *     "type": [
     *       "The selected type is invalid."
     *     ],
     *     "status_id": [
     *       "The selected status does not belong to this tenant."
     *     ],
     *     "source_id": [
     *       "The selected source does not belong to this tenant."
     *     ]
     *   }
     * }
     * @response scenario="unauthenticated" status=401 {
     *   "message": "Unauthenticated."
     * }
     * @response scenario="invalid subdomain" status=400 {
     *   "status": "error",
     *   "message": "Invalid or inactive tenant"
     * }
     */
    public function update(Request $request, $subdomain, $id)
    {
        try {
            $contactTable = TenantContact::fromTenant($subdomain)->getTable();
            $tenant_id = $request->get('tenant_id');

            // Assign a user to "addedfrom"
            $addedfrom = User::where('tenant_id', $tenant_id)->value('id');
            $request->merge(['addedfrom' => $addedfrom]);

            $contact = TenantContact::fromTenant($subdomain)
                ->where('id', $id)
                ->first();

            if (! $contact) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('contact_not_found'),
                ], 404);
            }

            // Apply WhatsApp Auto Lead settings fallback if enabled (for missing values only)
            $requestData = $request->all();
            $autoLeadSettings = $this->getWhatsAppAutoLeadSettings($tenant_id);

            if ($autoLeadSettings['enabled']) {
                if (! $request->has('status_id') && ! empty($autoLeadSettings['lead_status'])) {
                    $requestData['status_id'] = $autoLeadSettings['lead_status'];
                }

                if (! $request->has('source_id') && ! empty($autoLeadSettings['lead_source'])) {
                    $requestData['source_id'] = $autoLeadSettings['lead_source'];
                }

                if (! $request->has('assignee') && ! empty($autoLeadSettings['lead_assigned_to'])) {
                    $requestData['assignee'] = $autoLeadSettings['lead_assigned_to'];
                }
            }

            // Fix validation rules
            $validator = Validator::make($requestData, [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'company' => 'nullable|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    'max:191',
                    Rule::unique($contactTable, 'email')->ignore($id)->where(function ($query) use ($tenant_id) {
                        return $query->where('tenant_id', $tenant_id);
                    }),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique($contactTable, 'phone')->ignore($id)
                        ->where(function ($query) use ($tenant_id) {
                            return $query->where('tenant_id', $tenant_id);
                        }),
                ],
                'type' => 'required|in:lead,customer',
                'source_id' => 'nullable|integer',
                'status_id' => 'nullable|integer|max:50',
                'description' => 'nullable|string',
                'country_id' => 'nullable|integer',
                'assigned_id' => 'nullable|integer',
                'groups' => 'nullable|string',
            ]);

            if ($validator->fails()) {

                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $contact->update(collect($requestData)->except(['groups'])->toArray());

            // Handle groups if provided
            if (! empty($requestData['groups'])) {
                $this->assignContactToGroups($contact, $requestData['groups'], $tenant_id);
            }

            return response()->json([
                'status' => 'success',
                'message' => t('contact_update_successfully'),
                'data' => $contact,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_update_contact'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Contact
     *
     * Permanently remove a contact from your tenant database. This action cannot be undone.
     * Use with caution as this will remove all associated data.
     *
     * **Use Cases:**
     * - Remove outdated or invalid contacts
     * - Clean up test or duplicate contacts
     * - Comply with data privacy requests
     * - Manage contact database size
     *
     * **Important Notes:**
     * - This action is irreversible
     * - All chat history and notes will be deleted
     * - Group memberships will be removed
     * - Analytics data may be affected
     * - Consider archiving instead of deleting for compliance
     *
     * @urlParam subdomain string required The tenant subdomain. Example: tenantx
     * @urlParam id integer required The ID of the contact to delete. Example: 25
     *
     * @response scenario="contact deleted successfully" status=200 {
     *   "status": "success",
     *   "message": "Contact deleted successfully",
     *   "data": {
     *     "deleted_contact_id": 25,
     *     "deleted_at": "2024-02-08T16:45:30.000000Z"
     *   }
     * }
     * @response scenario="contact not found" status=404 {
     *   "status": "error",
     *   "message": "Contact not found"
     * }
     * @response scenario="unauthenticated" status=401 {
     *   "message": "Unauthenticated."
     * }
     * @response scenario="invalid subdomain" status=400 {
     *   "status": "error",
     *   "message": "Invalid or inactive tenant"
     * }
     * @response scenario="server error" status=500 {
     *   "status": "error",
     *   "message": "Failed to delete contact",
     *   "error": "Database error details"
     * }
     */
    public function destroy(Request $request, $subdomain, $id)
    {
        try {
            $contact = TenantContact::fromTenant($subdomain)
                ->where('id', $id)
                ->first();

            if (! $contact) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('contact_not_found'),
                ], 404);
            }

            $contact->delete();

            return response()->json([
                'status' => 'success',
                'message' => t('contact_delete_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_delete_contact'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign contact to groups
     *
     * @param  string  $groups  Comma-separated group names
     */
    private function assignContactToGroups(TenantContact $contact, string $groups, int $tenant_id): void
    {
        $groupNames = array_map('trim', explode(',', $groups));
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

        if (! empty($groupIds)) {
            $contact->setGroups($groupIds);
        }
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
