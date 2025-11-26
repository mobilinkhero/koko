<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Contact Management
 *
 * APIs for managing WhatsApp contacts within the tenant context
 */
class ContactController extends Controller
{
    /**
     * List Contacts
     *
     * Retrieve a paginated list of contacts for the current tenant.
     *
     * @authenticated
     *
     * @queryParam page integer Optional. Page number for pagination. Example: 1
     * @queryParam per_page integer Optional. Number of contacts per page (max 100). Example: 25
     * @queryParam search string Optional. Search contacts by name, phone, or email. Example: john
     * @queryParam group_id integer Optional. Filter by contact group ID. Example: 5
     * @queryParam status string Optional. Filter by contact status (active, blocked, unsubscribed). Example: active
     * @queryParam sort_by string Optional. Sort field (name, phone, created_at). Example: name
     * @queryParam sort_order string Optional. Sort order (asc, desc). Example: asc
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "contacts": [
     *       {
     *         "id": 1,
     *         "name": "John Doe",
     *         "phone": "+1234567890",
     *         "email": "john@example.com",
     *         "status": "active",
     *         "tags": ["customer", "vip"],
     *         "group_id": 5,
     *         "group_name": "Premium Customers",
     *         "created_at": "2025-01-15T10:30:00.000000Z",
     *         "updated_at": "2025-01-15T10:30:00.000000Z"
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 25,
     *       "total": 100,
     *       "last_page": 4,
     *       "from": 1,
     *       "to": 25
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
            'group_id' => 'integer|exists:contact_groups,id',
            'status' => 'string|in:active,blocked,unsubscribed',
            'sort_by' => 'string|in:name,phone,created_at',
            'sort_order' => 'string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mock data for demonstration
        return response()->json([
            'success' => true,
            'data' => [
                'contacts' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'phone' => '+1234567890',
                        'email' => 'john@example.com',
                        'status' => 'active',
                        'tags' => ['customer', 'vip'],
                        'group_id' => 5,
                        'group_name' => 'Premium Customers',
                        'created_at' => '2025-01-15T10:30:00.000000Z',
                        'updated_at' => '2025-01-15T10:30:00.000000Z',
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 25,
                    'total' => 100,
                    'last_page' => 4,
                    'from' => 1,
                    'to' => 25,
                ],
            ],
        ]);
    }

    /**
     * Create Contact
     *
     * Add a new contact to the current tenant's database.
     *
     * @authenticated
     *
     * @bodyParam name string required The contact's full name. Example: John Doe
     * @bodyParam phone string required The contact's WhatsApp phone number. Example: +1234567890
     * @bodyParam email string Optional. The contact's email address. Example: john@example.com
     * @bodyParam group_id integer Optional. Contact group ID to assign. Example: 5
     * @bodyParam tags array Optional. Array of tags to assign. Example: ["customer", "vip"]
     * @bodyParam custom_fields object Optional. Custom field values as key-value pairs. Example: {"company": "Acme Corp", "birthday": "1990-01-15"}
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Contact created successfully",
     *   "data": {
     *     "contact": {
     *       "id": 2,
     *       "name": "John Doe",
     *       "phone": "+1234567890",
     *       "email": "john@example.com",
     *       "status": "active",
     *       "group_id": 5,
     *       "tags": ["customer", "vip"],
     *       "custom_fields": {
     *         "company": "Acme Corp",
     *         "birthday": "1990-01-15"
     *       },
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "phone": ["The phone has already been taken."],
     *     "email": ["The email format is invalid."]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:contacts,phone',
            'email' => 'nullable|email|max:255',
            'group_id' => 'nullable|integer|exists:contact_groups,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mock contact creation
        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => [
                'contact' => [
                    'id' => 2,
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'status' => 'active',
                    'group_id' => $request->input('group_id'),
                    'tags' => $request->input('tags', []),
                    'custom_fields' => $request->input('custom_fields', []),
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ], 201);
    }

    /**
     * Get Contact
     *
     * Retrieve detailed information about a specific contact.
     *
     * @authenticated
     *
     * @urlParam id integer required The contact ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "contact": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "phone": "+1234567890",
     *       "email": "john@example.com",
     *       "status": "active",
     *       "group": {
     *         "id": 5,
     *         "name": "Premium Customers"
     *       },
     *       "tags": ["customer", "vip"],
     *       "custom_fields": {
     *         "company": "Acme Corp",
     *         "birthday": "1990-01-15"
     *       },
     *       "last_message_at": "2025-01-14T15:30:00.000000Z",
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Contact not found"
     * }
     */
    public function show(int $id): JsonResponse
    {
        // Mock contact retrieval
        return response()->json([
            'success' => true,
            'data' => [
                'contact' => [
                    'id' => $id,
                    'name' => 'John Doe',
                    'phone' => '+1234567890',
                    'email' => 'john@example.com',
                    'status' => 'active',
                    'group' => [
                        'id' => 5,
                        'name' => 'Premium Customers',
                    ],
                    'tags' => ['customer', 'vip'],
                    'custom_fields' => [
                        'company' => 'Acme Corp',
                        'birthday' => '1990-01-15',
                    ],
                    'last_message_at' => '2025-01-14T15:30:00.000000Z',
                    'created_at' => '2025-01-15T10:30:00.000000Z',
                    'updated_at' => '2025-01-15T10:30:00.000000Z',
                ],
            ],
        ]);
    }

    /**
     * Update Contact
     *
     * Update an existing contact's information.
     *
     * @authenticated
     *
     * @urlParam id integer required The contact ID. Example: 1
     *
     * @bodyParam name string Optional. The contact's full name. Example: John Smith
     * @bodyParam phone string Optional. The contact's WhatsApp phone number. Example: +1234567891
     * @bodyParam email string Optional. The contact's email address. Example: johnsmith@example.com
     * @bodyParam group_id integer Optional. Contact group ID to assign. Example: 6
     * @bodyParam tags array Optional. Array of tags to assign. Example: ["customer", "premium"]
     * @bodyParam status string Optional. Contact status (active, blocked, unsubscribed). Example: active
     * @bodyParam custom_fields object Optional. Custom field values as key-value pairs. Example: {"company": "New Corp"}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Contact updated successfully",
     *   "data": {
     *     "contact": {
     *       "id": 1,
     *       "name": "John Smith",
     *       "phone": "+1234567891",
     *       "email": "johnsmith@example.com",
     *       "status": "active",
     *       "group_id": 6,
     *       "tags": ["customer", "premium"],
     *       "custom_fields": {
     *         "company": "New Corp"
     *       },
     *       "updated_at": "2025-01-15T11:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Contact not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "phone": ["The phone has already been taken."]
     *   }
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone' => 'string|max:20|unique:contacts,phone,'.$id,
            'email' => 'nullable|email|max:255',
            'group_id' => 'nullable|integer|exists:contact_groups,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => 'string|in:active,blocked,unsubscribed',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mock contact update
        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully',
            'data' => [
                'contact' => [
                    'id' => $id,
                    'name' => $request->input('name', 'John Smith'),
                    'phone' => $request->input('phone', '+1234567891'),
                    'email' => $request->input('email', 'johnsmith@example.com'),
                    'status' => $request->input('status', 'active'),
                    'group_id' => $request->input('group_id', 6),
                    'tags' => $request->input('tags', ['customer', 'premium']),
                    'custom_fields' => $request->input('custom_fields', ['company' => 'New Corp']),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Delete Contact
     *
     * Remove a contact from the tenant's database.
     *
     * @authenticated
     *
     * @urlParam id integer required The contact ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Contact deleted successfully"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Contact not found"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ]);
    }

    /**
     * Import Contacts
     *
     * Bulk import contacts from CSV or Excel file.
     *
     * @authenticated
     *
     * @bodyParam file file required CSV or Excel file containing contacts. Max size: 10MB.
     * @bodyParam group_id integer Optional. Assign all imported contacts to this group. Example: 5
     * @bodyParam skip_duplicates boolean Optional. Skip contacts with duplicate phone numbers. Example: true
     * @bodyParam update_existing boolean Optional. Update existing contacts with new data. Example: false
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Contacts imported successfully",
     *   "data": {
     *     "summary": {
     *       "total_processed": 150,
     *       "imported": 120,
     *       "updated": 20,
     *       "skipped": 10,
     *       "errors": 0
     *     },
     *     "import_id": "imp_123456"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "file": ["The file must be a CSV or Excel file."]
     *   }
     * }
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'group_id' => 'nullable|integer|exists:contact_groups,id',
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mock import process
        return response()->json([
            'success' => true,
            'message' => 'Contacts imported successfully',
            'data' => [
                'summary' => [
                    'total_processed' => 150,
                    'imported' => 120,
                    'updated' => 20,
                    'skipped' => 10,
                    'errors' => 0,
                ],
                'import_id' => 'imp_123456',
            ],
        ]);
    }

    /**
     * Export Contacts
     *
     * Export contacts to CSV or Excel format.
     *
     * @authenticated
     *
     * @queryParam format string Optional. Export format (csv, xlsx). Example: csv
     * @queryParam group_id integer Optional. Export only contacts from specific group. Example: 5
     * @queryParam status string Optional. Export only contacts with specific status. Example: active
     * @queryParam include_fields array Optional. Fields to include in export. Example: ["name", "phone", "email"]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Export prepared successfully",
     *   "data": {
     *     "download_url": "https://api.whatsmark.app/downloads/exports/contacts_20250115.csv",
     *     "expires_at": "2025-01-15T20:30:00.000000Z",
     *     "file_size": "1.2MB",
     *     "total_contacts": 120
     *   }
     * }
     */
    public function export(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'format' => 'string|in:csv,xlsx',
            'group_id' => 'nullable|integer|exists:contact_groups,id',
            'status' => 'string|in:active,blocked,unsubscribed',
            'include_fields' => 'nullable|array',
            'include_fields.*' => 'string|in:name,phone,email,status,tags,created_at',
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
            'message' => 'Export prepared successfully',
            'data' => [
                'download_url' => 'https://api.whatsmark.app/downloads/exports/contacts_20250115.csv',
                'expires_at' => now()->addHours(10)->toISOString(),
                'file_size' => '1.2MB',
                'total_contacts' => 120,
            ],
        ]);
    }

    /**
     * Bulk Actions
     *
     * Perform bulk actions on multiple contacts.
     *
     * @authenticated
     *
     * @bodyParam action string required Action to perform (delete, update_group, update_status, add_tags, remove_tags). Example: update_status
     * @bodyParam contact_ids array required Array of contact IDs to perform action on. Example: [1, 2, 3, 4, 5]
     * @bodyParam group_id integer Optional. Required for update_group action. Example: 6
     * @bodyParam status string Optional. Required for update_status action (active, blocked, unsubscribed). Example: blocked
     * @bodyParam tags array Optional. Required for add_tags/remove_tags actions. Example: ["important", "follow-up"]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Bulk action completed successfully",
     *   "data": {
     *     "action": "update_status",
     *     "affected_contacts": 5,
     *     "successful": 4,
     *     "failed": 1,
     *     "errors": [
     *       {
     *         "contact_id": 3,
     *         "error": "Contact not found"
     *       }
     *     ]
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "action": ["The selected action is invalid."],
     *     "contact_ids": ["The contact ids field is required."]
     *   }
     * }
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:delete,update_group,update_status,add_tags,remove_tags',
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'integer|exists:contacts,id',
            'group_id' => 'required_if:action,update_group|integer|exists:contact_groups,id',
            'status' => 'required_if:action,update_status|string|in:active,blocked,unsubscribed',
            'tags' => 'required_if:action,add_tags,remove_tags|array',
            'tags.*' => 'string|max:50',
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
            'message' => 'Bulk action completed successfully',
            'data' => [
                'action' => $request->input('action'),
                'affected_contacts' => count($request->input('contact_ids')),
                'successful' => count($request->input('contact_ids')) - 1,
                'failed' => 1,
                'errors' => [
                    [
                        'contact_id' => 3,
                        'error' => 'Contact not found',
                    ],
                ],
            ],
        ]);
    }
}
