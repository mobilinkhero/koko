<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @group Group Management
 *
 * APIs for managing contact groups within the tenant context. Groups help organize contacts for targeted messaging campaigns.
 *
 * **Authentication Required:** All endpoints require a valid API token with appropriate permissions.
 * **Multi-tenant:** All groups are isolated per tenant (subdomain).
 *
 * **Group Features:**
 * - Create, read, update, and delete contact groups
 * - Organize contacts by categories (e.g., "VIP Customers", "Newsletter Subscribers")
 * - Use groups for targeted WhatsApp campaigns
 * - Track group membership and engagement
 */
class GroupController extends Controller
{
    /**
     * List Groups
     *
     * Get a paginated list of contact groups with optional filtering. Groups are returned in descending order of creation.
     *
     * **Use Cases:**
     * - Get all groups for dropdown menus
     * - List groups with contact counts for analytics
     * - Filter groups for campaign targeting
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page (max 100). Example: 15
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 19,
     *       "tenant_id": 13,
     *       "name": "VIP Customers",
     *       "contact_count": 25,
     *       "created_at": "2024-02-08T10:00:00.000000Z",
     *       "updated_at": "2024-02-08T10:00:00.000000Z"
     *     },
     *     {
     *       "id": 18,
     *       "tenant_id": 13,
     *       "name": "Newsletter Subscribers",
     *       "contact_count": 150,
     *       "created_at": "2024-02-07T09:30:00.000000Z",
     *       "updated_at": "2024-02-07T09:30:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 10,
     *     "per_page": 15,
     *     "last_page": 1
     *   }
     * }
     * @response status=401 scenario="unauthenticated" {
     *   "status": "error",
     *   "message": "Invalid API token or insufficient permissions"
     * }
     */
    public function index(Request $request)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $group = Group::where('tenant_id', $tenant_id)
                ->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $group,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_groups'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Groups Details
     *
     * Get detailed information about a specific Groups.
     *
     * @urlParam id integer required The ID of the Groups. Example: 1
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": {
     *      "id": 19,
     *      "tenant_id": 13,
     *      "name": "group2",
     *      "created_at": "2025-07-14T06:53:01.000000Z",
     *      "updated_at": "2025-07-14T06:53:01.000000Z"
     *   }
     * }
     * @response status=404 scenario="not found" {
     *   "status": "error",
     *   "message": "Groups not found"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $group = Group::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $group) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('groups_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $group,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('groups_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create Group
     *
     * Create a new Group in the system.
     *
     * @bodyParam name string required The name of the Group. Example: Referral
     *
     * @response scenario=success status=201 {
     *   "status": "success",
     *   "message": "Group created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Referral",
     *     "created_at": "2024-02-08 10:00:00"
     *   }
     * }
     * @response status=422 scenario="validation error" {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function store(Request $request, $subdomain)
    {
        try {
            $tenant_id = $request->get('tenant_id');
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('groups')->where(function ($query) use ($tenant_id) {
                        return $query->where('tenant_id', $tenant_id);
                    }),
                ],

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $group = Group::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => t('group_saved_successfully'),
                'data' => $group,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_create_groups'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Groups
     *
     * Update an existing Group's information.
     *
     * @urlParam id integer required The ID of the group. Example: 1
     *
     * @bodyParam name string required The name of the group. Example: Referral Program
     *
     * @response scenario=success {
     *   "status": "success",
     *   "message": "Group updated successfully",
     *   "data": {
     *   "id": 42,
     *   "tenant_id": 2,
     *   "name": "group100",
     *   "created_at": "2025-08-07T05:41:09.000000Z",
     *   "updated_at": "2025-08-07T05:48:07.000000Z"
     * }
     * }
     */
    public function update(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $group = Group::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $group) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('groups_not_found'),
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('groups')->ignore($id)->where(function ($query) use ($tenant_id) {
                        return $query->where('tenant_id', $tenant_id);
                    }),
                ],

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $group->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => t('group_update_successfully'),
                'data' => $group,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_update_group'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Group
     *
     * Remove a Group from the system.
     *
     * @urlParam id integer required The ID of the Group. Example: 1
     *
     * @response scenario=success {
     *   "status": "success",
     *   "message": "Group deleted successfully"
     * }
     * @response status=404 {
     *   "status": "error",
     *   "message": "Group not found"
     * }
     */
    public function destroy(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');
            $table = $subdomain.'_contacts';

            $group = Group::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $group) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('groups_not_found'),
                ], 404);
            }

            // Check if the group is used in the contacts table
            $isGroupUsed = false;

            if (Schema::hasTable($table)) {
                $rawGroupIds = DB::table($table)
                    ->pluck('group_id')
                    ->toArray();

                $usedGroupIds = collect($rawGroupIds)
                    ->flatMap(function ($item) {
                        return is_string($item) ? json_decode($item, true) : (array) $item;
                    })
                    ->unique()
                    ->values()
                    ->toArray();

                $isGroupUsed = in_array($group->id, $usedGroupIds);
            }

            if ($isGroupUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('group_in_use_notify'),
                ], 400);
            }

            // Proceed to delete
            $group->delete();

            return response()->json([
                'status' => 'success',
                'message' => t('group_delete_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_delete_group'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
