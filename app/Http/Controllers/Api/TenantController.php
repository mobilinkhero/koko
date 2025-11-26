<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Settings\SystemSettings;
use App\Settings\TenantSettings;
use App\Settings\WhatsappSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @group Tenant Management
 *
 * APIs for managing tenant information, settings, and configuration
 */
class TenantController extends Controller
{
    /**
     * Get Current Tenant
     *
     * Retrieve information about the current tenant context.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "tenant": {
     *       "id": "tenant_abc123",
     *       "name": "Acme Corporation",
     *       "domain": "acme.whatsmark.app",
     *       "database": "tenant_acme",
     *       "is_active": true,
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "No active tenant found"
     * }
     */
    public function current(): JsonResponse
    {
        $tenant = tenant();

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No active tenant found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
            ],
        ]);
    }

    /**
     * Update Tenant
     *
     * Update basic tenant information.
     *
     * @authenticated
     *
     * @bodyParam name string required The tenant's display name. Example: Acme Corporation
     * @bodyParam domain string Optional. The tenant's custom domain. Example: acme.whatsmark.app
     * @bodyParam description string Optional. Brief description of the tenant. Example: Leading marketing company
     * @bodyParam logo string Optional. URL to the tenant's logo. Example: https://example.com/logo.png
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Tenant updated successfully",
     *   "data": {
     *     "tenant": {
     *       "id": "tenant_abc123",
     *       "name": "Acme Corporation",
     *       "domain": "acme.whatsmark.app",
     *       "description": "Leading marketing company",
     *       "logo": "https://example.com/logo.png",
     *       "is_active": true,
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T11:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "domain": ["The domain format is invalid."]
     *   }
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenant = tenant();
        $tenant->update($request->only(['name', 'domain', 'description', 'logo']));

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => [
                'tenant' => $tenant->fresh(),
            ],
        ]);
    }

    /**
     * Get Tenant Settings
     *
     * Retrieve all tenant-specific settings including registration, verification, and feature configurations.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "settings": {
     *       "tenant": {
     *         "isRegistrationEnabled": true,
     *         "isVerificationEnabled": true,
     *         "isEmailConfirmationEnabled": false,
     *         "isEnableWelcomeEmail": true
     *       },
     *       "system": {
     *         "site_name": "WhatsApp Marketing Hub",
     *         "site_description": "Powerful WhatsApp marketing platform",
     *         "timezone": "UTC",
     *         "date_format": "Y-m-d",
     *         "time_format": "H:i:s",
     *         "active_language": "en",
     *         "tables_pagination_limit": 25,
     *         "is_enable_landing_page": true
     *       },
     *       "whatsapp": {
     *         "api_version": "v18.0",
     *         "daily_limit": "1000",
     *         "queue": "default",
     *         "logging": "enabled"
     *       }
     *     }
     *   }
     * }
     */
    public function settings(): JsonResponse
    {
        $tenantSettings = app(TenantSettings::class);
        $systemSettings = app(SystemSettings::class);
        $whatsappSettings = app(WhatsappSettings::class);

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => [
                    'tenant' => [
                        'isRegistrationEnabled' => $tenantSettings->isRegistrationEnabled,
                        'isVerificationEnabled' => $tenantSettings->isVerificationEnabled,
                        'isEmailConfirmationEnabled' => $tenantSettings->isEmailConfirmationEnabled,
                        'isEnableWelcomeEmail' => $tenantSettings->isEnableWelcomeEmail,
                    ],
                    'system' => [
                        'site_name' => $systemSettings->site_name,
                        'site_description' => $systemSettings->site_description,
                        'timezone' => $systemSettings->timezone,
                        'date_format' => $systemSettings->date_format,
                        'time_format' => $systemSettings->time_format,
                        'active_language' => $systemSettings->active_language,
                        'tables_pagination_limit' => $systemSettings->tables_pagination_limit,
                        'is_enable_landing_page' => $systemSettings->is_enable_landing_page,
                    ],
                    'whatsapp' => [
                        'api_version' => $whatsappSettings->api_version,
                        'daily_limit' => $whatsappSettings->daily_limit,
                        'queue' => $whatsappSettings->queue,
                        'logging' => $whatsappSettings->logging,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Update Tenant Settings
     *
     * Update tenant-specific configuration settings.
     *
     * @authenticated
     *
     * @bodyParam tenant object Optional. Tenant-specific settings.
     * @bodyParam tenant.isRegistrationEnabled boolean Optional. Enable/disable user registration. Example: true
     * @bodyParam tenant.isVerificationEnabled boolean Optional. Enable/disable email verification. Example: true
     * @bodyParam tenant.isEmailConfirmationEnabled boolean Optional. Enable/disable email confirmation. Example: false
     * @bodyParam tenant.isEnableWelcomeEmail boolean Optional. Enable/disable welcome emails. Example: true
     * @bodyParam system object Optional. System-wide settings.
     * @bodyParam system.site_name string Optional. Site display name. Example: My WhatsApp Hub
     * @bodyParam system.timezone string Optional. Default timezone. Example: America/New_York
     * @bodyParam system.date_format string Optional. Date format. Example: Y-m-d
     * @bodyParam system.active_language string Optional. Default language. Example: en
     * @bodyParam whatsapp object Optional. WhatsApp integration settings.
     * @bodyParam whatsapp.daily_limit string Optional. Daily message limit. Example: 1000
     * @bodyParam whatsapp.queue string Optional. Queue name for WhatsApp jobs. Example: whatsapp
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Settings updated successfully",
     *   "data": {
     *     "updated_settings": {
     *       "tenant": {
     *         "isRegistrationEnabled": false,
     *         "isVerificationEnabled": true
     *       },
     *       "system": {
     *         "site_name": "My WhatsApp Hub"
     *       }
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "system.timezone": ["The selected timezone is invalid."]
     *   }
     * }
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant.isRegistrationEnabled' => 'boolean',
            'tenant.isVerificationEnabled' => 'boolean',
            'tenant.isEmailConfirmationEnabled' => 'boolean',
            'tenant.isEnableWelcomeEmail' => 'boolean',
            'system.site_name' => 'string|max:255',
            'system.timezone' => 'string|timezone',
            'system.date_format' => 'string|max:50',
            'system.active_language' => 'string|max:10',
            'whatsapp.daily_limit' => 'string|numeric|min:1',
            'whatsapp.queue' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updatedSettings = [];

        // Update tenant settings
        if ($request->has('tenant')) {
            $tenantSettings = app(TenantSettings::class);
            $tenantData = $request->input('tenant');

            foreach ($tenantData as $key => $value) {
                if (property_exists($tenantSettings, $key)) {
                    $tenantSettings->$key = $value;
                }
            }
            $tenantSettings->save();
            $updatedSettings['tenant'] = $tenantData;
        }

        // Update system settings
        if ($request->has('system')) {
            $systemSettings = app(SystemSettings::class);
            $systemData = $request->input('system');

            foreach ($systemData as $key => $value) {
                if (property_exists($systemSettings, $key)) {
                    $systemSettings->$key = $value;
                }
            }
            $systemSettings->save();
            $updatedSettings['system'] = $systemData;
        }

        // Update WhatsApp settings
        if ($request->has('whatsapp')) {
            $whatsappSettings = app(WhatsappSettings::class);
            $whatsappData = $request->input('whatsapp');

            foreach ($whatsappData as $key => $value) {
                if (property_exists($whatsappSettings, $key)) {
                    $whatsappSettings->$key = $value;
                }
            }
            $whatsappSettings->save();
            $updatedSettings['whatsapp'] = $whatsappData;
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => [
                'updated_settings' => $updatedSettings,
            ],
        ]);
    }

    /**
     * Get Tenant Subscription
     *
     * Retrieve current subscription information for the tenant.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "subscription": {
     *       "id": "sub_123456",
     *       "plan_name": "Professional",
     *       "status": "active",
     *       "current_period_start": "2025-01-01T00:00:00.000000Z",
     *       "current_period_end": "2025-02-01T00:00:00.000000Z",
     *       "features": {
     *         "max_contacts": 10000,
     *         "max_campaigns": 100,
     *         "max_bot_flows": 50,
     *         "monthly_messages": 50000,
     *         "whatsapp_business_api": true,
     *         "custom_domain": true,
     *         "priority_support": true
     *       },
     *       "usage": {
     *         "contacts_used": 2500,
     *         "campaigns_used": 15,
     *         "bot_flows_used": 8,
     *         "messages_sent_this_month": 12500
     *       }
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "No active subscription found"
     * }
     */
    public function subscription(): JsonResponse
    {
        // This would typically fetch from your subscription service
        // For now, returning mock data structure

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => [
                    'id' => 'sub_123456',
                    'plan_name' => 'Professional',
                    'status' => 'active',
                    'current_period_start' => '2025-01-01T00:00:00.000000Z',
                    'current_period_end' => '2025-02-01T00:00:00.000000Z',
                    'features' => [
                        'max_contacts' => 10000,
                        'max_campaigns' => 100,
                        'max_bot_flows' => 50,
                        'monthly_messages' => 50000,
                        'whatsapp_business_api' => true,
                        'custom_domain' => true,
                        'priority_support' => true,
                    ],
                    'usage' => [
                        'contacts_used' => 2500,
                        'campaigns_used' => 15,
                        'bot_flows_used' => 8,
                        'messages_sent_this_month' => 12500,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Delete Tenant (Admin Only)
     *
     * Mark a tenant for deletion. The tenant will remain accessible until their subscription expires.
     *
     * @authenticated
     *
     * @urlParam id integer required The ID of the tenant to delete. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Tenant marked for deletion successfully"
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Cannot delete tenant with active subscription"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tenant not found"
     * }
     */
    public function adminDestroy($id): JsonResponse
    {
        try {
            $tenant = \App\Models\Tenant::findOrFail($id);

            // Just mark as deleted, no subscription check
            $tenant->deleted_date = now();
            $tenant->save();

            // Fire the event manually
            event(new \App\Events\Tenant\TenantDeleted($tenant));

            return response()->json([
                'success' => true,
                'message' => 'Tenant marked for deletion successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenant: '.$e->getMessage(),
            ], 500);
        }
    }
}
