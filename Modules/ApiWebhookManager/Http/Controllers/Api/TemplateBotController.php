<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TemplateBot;
use Illuminate\Http\Request;

/**
 * @group Template Bot Management
 *
 * APIs for managing WhatsApp template bots within the tenant context
 */
class TemplateBotController extends Controller
{
    /**
     * List TemplateBot
     *
     * Get a list of TemplateBot with optional pagination.
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "tenant_id": 2,
     *       "name": "tbot1",
     *       "rel_type": "lead",
     *       "template_id": 510070465356446,
     *       "header_params": "[]",
     *       "body_params": "[]",
     *       "footer_params": "[]",
     *       "filename": "tenant/2/template-bot/CTzitrN1GbYUu6qbbaMElkOlv3qTaZTXPVyNXDAM.png",
     *       "trigger": "heloo",
     *       "reply_type": 1,
     *       "is_bot_active": 1,
     *       "created_at": "2025-07-12T11:16:01.000000Z",
     *       "updated_at": "2025-07-12T11:16:01.000000Z",
     *       "sending_count": 0
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

            $templatebots = TemplateBot::where('tenant_id', $tenant_id)
                ->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $templatebots,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_templatebot'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get TemplateBot Details
     *
     * Get detailed information about a specific TemplateBot.
     *
     * @urlParam id integer required The ID of the TemplateBot. Example: 1
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": {
     *       "id": 1,
     *       "tenant_id": 2,
     *       "name": "tbot1",
     *       "rel_type": "lead",
     *       "template_id": 510070465356446,
     *       "header_params": "[]",
     *       "body_params": "[]",
     *       "footer_params": "[]",
     *       "filename": "tenant/2/template-bot/CTzitrN1GbYUu6qbbaMElkOlv3qTaZTXPVyNXDAM.png",
     *       "trigger": "heloo",
     *       "reply_type": 1,
     *       "is_bot_active": 1,
     *       "created_at": "2025-07-12T11:16:01.000000Z",
     *       "updated_at": "2025-07-12T11:16:01.000000Z",
     *       "sending_count": 0
     *   }
     * }
     * @response status=404 scenario="not found" {
     *   "status": "error",
     *   "message": "Template bot not found"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $templatebot = TemplateBot::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $templatebot) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('template_bot_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $templatebot,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('template_bot_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
