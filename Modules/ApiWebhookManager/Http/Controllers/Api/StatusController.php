<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Status as TenantStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @group Status Management
 *
 * APIs for managing contact statuses within the tenant context
 */
class StatusController extends Controller
{
    /**
     * List Statuses
     *
     * Get a list of statuses with optional pagination.
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Active",
     *       "color": "#00FF00",
     *       "isdefault": true,
     *       "created_at": "2024-02-08 10:00:00",
     *       "updated_at": "2024-02-08 10:00:00"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 10,
     *     "per_page": 15
     *   }
     * }
     * @response status=401 scenario="unauthenticated" {
     *   "status": "error",
     *   "message": "Invalid API token"
     * }
     */
    public function index(Request $request)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $statuses = TenantStatus::where('tenant_id', $tenant_id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $statuses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_statuses'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Status
     *
     * Create a new status in the system.
     *
     * @bodyParam name string required The name of the status. Example: Pending
     * @bodyParam color string The color code for the status. Example: #FF0000
     * @bodyParam isdefault boolean Whether this is the default status. Example: false
     *
     * @response scenario=success status=201 {
     *   "status": "success",
     *   "message": "Status created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Pending",
     *     "color": "#FF0000",
     *     "isdefault": false,
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
                    'min:3',
                    'max:255',
                    Rule::unique('statuses')->where(function ($query) use ($tenant_id) {
                        return $query->where('tenant_id', $tenant_id);
                    }),
                ],

                'color' => 'nullable|string|max:7',
                'isdefault' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->input('isdefault', false)) {
                TenantStatus::where('isdefault', true)->update(['isdefault' => false]);
            }

            $status = TenantStatus::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => t('status_save_successfully'),
                'data' => $status,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_create_status'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Status Details
     *
     * Get detailed information about a specific status.
     *
     * @urlParam id integer required The ID of the status. Example: 1
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": {
     *     "id": 1,
     *     "name": "Active",
     *     "color": "#00FF00",
     *     "isdefault": true,
     *     "created_at": "2024-02-08 10:00:00",
     *     "updated_at": "2024-02-08 10:00:00"
     *   }
     * }
     * @response status=404 scenario="not found" {
     *   "status": "error",
     *   "message": "Status not found"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            // Keep only this:
            $status = TenantStatus::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $status) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('status_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('status_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update Status
     *
     * Update an existing status's information.
     *
     * @urlParam id integer required The ID of the status. Example: 1
     *
     * @bodyParam name string The name of the status. Example: In Progress
     * @bodyParam color string The color code for the status. Example: #0000FF
     * @bodyParam isdefault boolean Whether this is the default status. Example: true
     *
     * @response scenario=success {
     *   "status": "success",
     *   "message": "Status updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "In Progress",
     *     "color": "#0000FF",
     *     "updated_at": "2024-02-08 11:00:00"
     *   }
     * }
     */
    public function update(Request $request, $subdomain, $id)
    {

        try {
            $tenant_id = $request->get('tenant_id');

            $status = TenantStatus::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $status) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('status_not_found'),
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'string',
                    'min:3',
                    'max:255',
                    Rule::unique('statuses')->ignore($id)->where(function ($query) use ($tenant_id) {
                        return $query->where('tenant_id', $tenant_id);
                    }),
                ],

                'color' => 'nullable|string|max:7',
                'isdefault' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->input('isdefault', false)) {
                TenantStatus::where('isdefault', true)->update(['isdefault' => false]);
            }

            $status->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => t('status_update_successfully'),
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_update_status'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Status
     *
     * Remove a status from the system.
     *
     * @urlParam id integer required The ID of the status. Example: 1
     *
     * @response scenario=success {
     *   "status": "success",
     *   "message": "Status deleted successfully"
     * }
     ** @response status=404 {
     *   "status": "error",
     *   "message": "Status not found"
     * }
     * @response status=400 {
     *   "status": "error",
     *   "message": "Cannot delete the default status"
     * }
     */
    public function destroy(Request $request, $subdomain, $id)
    {

        try {
            $tenant_id = $request->get('tenant_id');
            $table = $subdomain.'_contacts';

            // Find the status
            $status = TenantStatus::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $status) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('status_not_found'),
                ], 404);
            }

            // Prevent deletion if it's the default status
            if ($status->isdefault) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('default_status_not_delete'),
                ], 400);
            }

            // Check if the status is used in the contacts table
            $isStatusUsed = false;

            if (Schema::hasTable($table)) {
                $usedStatusIds = DB::table($table)
                    ->select('status_id')
                    ->distinct()
                    ->pluck('status_id')
                    ->toArray();

                $isStatusUsed = in_array($status->id, $usedStatusIds);
            }

            if ($isStatusUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('status_delete_in_use_notify'),
                ], 400);
            }

            // Delete the status
            $status->delete();

            return response()->json([
                'status' => 'success',
                'message' => t('status_delete_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_delete_status'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
