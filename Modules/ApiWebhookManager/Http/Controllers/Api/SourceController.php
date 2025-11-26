<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Source as TenantSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @group Source Management
 *
 * APIs for managing contact sources within the tenant context
 */
class SourceController extends Controller
{
    /**
     * List Sources
     *
     * Get a list of sources with optional pagination.
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Website",
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
    public function index(Request $request, $subdomain)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $sources = TenantSource::where('tenant_id', $tenant_id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $sources,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_sources'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Source
     *
     * Create a new source in the system.
     *
     * @bodyParam name string required The name of the source. Example: Referral
     *
     * @response scenario=success status=201 {
     *   "status": "success",
     *   "message": "Source created successfully",
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
                    Rule::unique('sources')->where(function ($query) use ($tenant_id) {
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

            $source = TenantSource::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => t('source_saved_successfully'),
                'data' => $source,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_create_source'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Source Details
     *
     * Get detailed information about a specific source.
     *
     * @urlParam id integer required The ID of the source. Example: 1
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": {
     *     "id": 1,
     *     "name": "Website",
     *     "created_at": "2024-02-08 10:00:00",
     *     "updated_at": "2024-02-08 10:00:00"
     *   }
     * }
     * @response status=404 scenario="not found" {
     *   "status": "error",
     *   "message": "Source not found"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $source = TenantSource::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $source) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('source_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $source,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('source_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update Source
     *
     * Update an existing source's information.
     *
     * @urlParam id integer required The ID of the source. Example: 1
     *
     * @bodyParam name string required The name of the source. Example: Referral Program
     *
     * @response scenario=success {
     *   "status": "success",
     *   "message": "Source updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Referral Program",
     *     "updated_at": "2024-02-08 11:00:00"
     *   }
     * }
     */
    public function update(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $source = TenantSource::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $source) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('source_not_found'),
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('sources')->ignore($id)->where(function ($query) use ($tenant_id) {
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

            $source->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => t('source_update_successfully'),
                'data' => $source,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_update_source'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Source
     *
     * Remove a source from the system.
     *
     * @urlParam id integer required The ID of the source. Example: 1
     *
     * @response scenario=success {
     *   "status": "success",
     *   "message": "Source deleted successfully"
     * }
     * @response status=404 {
     *   "status": "error",
     *   "message": "Source not found"
     * }
     */
    public function destroy(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');
            $table = $subdomain.'_contacts';

            $source = TenantSource::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $source) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('source_not_found'),
                ], 404);
            }

            // Check if the status is used in the contacts table
            $isSourceUsed = false;

            if (Schema::hasTable($table)) {
                $usedSourceIds = DB::table($table)
                    ->select('source_id')
                    ->distinct()
                    ->pluck('source_id')
                    ->toArray();

                $isSourceUsed = in_array($source->id, $usedSourceIds);
            }

            if ($isSourceUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('source_in_use_notify'),
                ], 400);
            }

            $source->delete();

            return response()->json([
                'status' => 'success',
                'message' => t('source_delete_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_delete_source'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
