<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\WhatsappTemplate;
use Illuminate\Http\Request;

/**
 * @group Template Management
 *
 * APIs for managing WhatsApp templates within the tenant context. Templates are pre-approved message formats by WhatsApp Business.
 *
 * **Read-Only Access:** These endpoints provide read-only access to your approved WhatsApp templates.
 * **Authentication Required:** All endpoints require a valid API token with template read permissions.
 * **Multi-tenant:** All templates are isolated per tenant (subdomain).
 *
 * **Template Features:**
 * - List all approved WhatsApp Business templates
 * - Get detailed template information including parameters
 * - Use template data for sending template messages via Message API
 * - Check template status and approval state
 *
 * **Template Types:**
 * - **MARKETING**: Promotional and marketing messages
 * - **UTILITY**: Transactional and utility messages
 * - **AUTHENTICATION**: OTP and verification messages
 */
class TemplateController extends Controller
{
    /**
     * List Templates
     *
     * Get a paginated list of approved WhatsApp Business templates. Templates are automatically synced from your WhatsApp Business Account.
     *
     * **Template Status:**
     * - **APPROVED**: Ready to use for sending messages
     * - **PENDING**: Under review by WhatsApp
     * - **REJECTED**: Not approved for use
     *
     * **Use Cases:**
     * - Get available templates for dropdown selection
     * - Check template parameters for dynamic content
     * - Verify template approval status before sending
     * - Build template-based messaging campaigns
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page (max 100). Example: 15
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "tenant_id": 2,
     *       "template_id": 510070465356446,
     *       "template_name": "whatsmarksaas_welcome_offer",
     *       "language": "en",
     *       "status": "APPROVED",
     *       "category": "MARKETING",
     *       "header_data_format": "IMAGE",
     *       "header_data_text": null,
     *       "header_params_count": 0,
     *       "body_data": "Welcome {{1}} to WhatsMarkSaaS! Get {{2}}% off on your first month. Use code: {{3}}",
     *       "body_params_count": 3,
     *       "footer_data": "WhatsMarkSaaS - Your Marketing Success Partner",
     *       "footer_params_count": 0,
     *       "buttons_data": "[{\"type\":\"URL\",\"text\":\"Start Free Trial\",\"url\":\"https://whatsmark.dev/register\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Contact Sales\"}]",
     *       "created_at": "2024-02-08T10:00:00.000000Z",
     *       "updated_at": "2024-02-08T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 25,
     *     "per_page": 15,
     *     "last_page": 2
     *   }
     * }
     * @response status=401 scenario="unauthenticated" {
     *   "status": "error",
     *   "message": "Invalid API token or insufficient permissions"
     * }
     * @response status=404 scenario="no templates" {
     *   "status": "error",
     *   "message": "No templates found. Please sync your WhatsApp Business templates first."
     * }
     */
    public function index(Request $request)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $templates = WhatsappTemplate::where('tenant_id', $tenant_id)
                ->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $templates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_templates'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Templates Details
     *
     * Get detailed information about a specific Templates.
     *
     * @urlParam id integer required The ID of the Templates. Example: 1
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": {
     *      "id": 1,
     *      "tenant_id": 2,
     *      "template_id": 510070465356446,
     *      "template_name": "welcome_to_whatsmarksaas",
     *      "language": "en",
     *      "status": "APPROVED",
     *      "category": "MARKETING",
     *      "header_data_format": "IMAGE",
     *      "header_data_text": null,
     *      "header_params_count": 0,
     *      "body_data": "Welcome to WhatsMarkSaaS - The Complete WhatsApp Marketing & Automation Platform! �\n\nDiscover powerful features for your business:\n\n📱 *WhatsApp Marketing*: Reach customers instantly\n🤖 *Smart Automation*: AI-powered chat bots\n📊 *Analytics & Reports*: Track your success\n\nStart growing your business today!",
     *      "body_params_count": 0,
     *      "footer_data": "WhatsMarkSaaS - Your Success Partner",
     *      "footer_params_count": 0,
     *      "buttons_data": "[{\"type\":\"URL\",\"text\":\"Get Started\",\"url\":\"https:\\/\\/codecanyon.net\\/item\\/whatsmarksaas-whatsapp-marketing-automation-saas-platform-with-bots-chats-bulk-sender-ai\\/58714968\"},{\"type\":\"URL\",\"text\":\"Watch Demo\",\"url\":\"https:\\/\\/www.youtube.com\\/watch?v=demo\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Learn More\"}]",
     *      "created_at": "2025-07-12T10:44:50.000000Z",
     *      "updated_at": "2025-07-12T10:44:50.000000Z"
     *   }
     * }
     * @response status=404 scenario="not found" {
     *   "status": "error",
     *   "message": "Template not found"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $template = WhatsappTemplate::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $template) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('template_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $template,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('template_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
