<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MessageBot;
use Illuminate\Http\Request;

/**
 * @group Message Bot Management
 *
 * APIs for managing WhatsApp message bots within the tenant context
 */
class MessageBotController extends Controller
{
    /**
     * List Messagebots
     *
     * Get a list of Messagebots
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": [
     *     {
     *        "id": 3,
     *        "tenant_id": 2,
     *        "name": "mbot3",
     *        "rel_type": "lead",
     *        "reply_text": "hello from reply text",
     *        "reply_type": 1,
     *        "trigger": "mbot3",
     *        "bot_header": null,
     *        "bot_footer": null,
     *        "button1": null,
     *        "button1_id": null,
     *        "button2": null,
     *        "button2_id": null,
     *        "button3": null,
     *        "button3_id": null,
     *        "button_name": null,
     *        "button_url": null,
     *        "addedfrom": 1,
     *        "is_bot_active": true,
     *        "sending_count": 0,
     *        "filename": null,
     *        "created_at": "2025-07-12T12:18:33.000000Z",
     *        "updated_at": "2025-07-12T12:18:33.000000Z"
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

            $messagebots = MessageBot::where('tenant_id', $tenant_id)
                ->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $messagebots,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_message_bots'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Messagebots Details
     *
     * Get detailed information about a specific Messagebots.
     *
     * @urlParam id integer required The ID of the status. Example: 1
     *
     * @response scenario=success status=200 {
     *   "status": "success",
     *   "data": {
     *     "id": 1,
     *     "tenant_id": 2,
     *     "name": "messagebot 1",
     *     "rel_type": "lead",
     *     "reply_text": "hello from message bot testing",
     *     "reply_type": 1,
     *     "trigger": "hello",
     *     "bot_header": null,
     *     "bot_footer": null,
     *     "button1": null,
     *     "button1_id": null,
     *     "button2": null,
     *     "button2_id": null,
     *     "button3": null,
     *     "button3_id": null,
     *     "button_name": null,
     *     "button_url": null,
     *     "addedfrom": 2,
     *     "is_bot_active": true,
     *     "sending_count": 0,
     *     "filename": null,
     *     "created_at": "2025-07-12T11:44:58.000000Z",
     *     "updated_at": "2025-07-12T11:44:58.000000Z"
     *   }
     * }
     * @response status=404 scenario="not found" {
     *   "status": "error",
     *   "message": "Message bot not found"
     * }
     */
    public function show(Request $request, $subdomain, $id)
    {
        try {
            $tenant_id = $request->get('tenant_id');

            $messagebot = MessageBot::where([
                ['tenant_id', '=', $tenant_id],
                ['id', '=', $id],
            ])->first();

            if (! $messagebot) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('message_bot_not_found'),
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $messagebot,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('message_bot_not_found'),
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
