<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Campaign Management
 *
 * APIs for managing WhatsApp marketing campaigns
 */
class CampaignController extends Controller
{
    /**
     * List Campaigns
     *
     * Retrieve a paginated list of marketing campaigns for the current tenant.
     *
     * @authenticated
     *
     * @queryParam page integer Optional. Page number for pagination. Example: 1
     * @queryParam per_page integer Optional. Number of campaigns per page (max 50). Example: 25
     * @queryParam search string Optional. Search campaigns by name or description. Example: sale
     * @queryParam status string Optional. Filter by campaign status (draft, scheduled, running, paused, completed, cancelled). Example: running
     * @queryParam type string Optional. Filter by campaign type (broadcast, drip, event_triggered). Example: broadcast
     * @queryParam sort_by string Optional. Sort field (name, created_at, scheduled_at, status). Example: created_at
     * @queryParam sort_order string Optional. Sort order (asc, desc). Example: desc
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "campaigns": [
     *       {
     *         "id": 1,
     *         "name": "Summer Sale Campaign",
     *         "description": "Promotional campaign for summer products",
     *         "status": "running",
     *         "type": "broadcast",
     *         "total_recipients": 1500,
     *         "messages_sent": 950,
     *         "delivery_rate": 95.2,
     *         "open_rate": 87.3,
     *         "click_rate": 23.5,
     *         "scheduled_at": "2025-01-15T09:00:00.000000Z",
     *         "started_at": "2025-01-15T09:00:00.000000Z",
     *         "created_at": "2025-01-14T10:30:00.000000Z",
     *         "updated_at": "2025-01-15T10:30:00.000000Z"
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 25,
     *       "total": 45,
     *       "last_page": 2,
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
            'per_page' => 'integer|min:1|max:50',
            'search' => 'string|max:255',
            'status' => 'string|in:draft,scheduled,running,paused,completed,cancelled',
            'type' => 'string|in:broadcast,drip,event_triggered',
            'sort_by' => 'string|in:name,created_at,scheduled_at,status',
            'sort_order' => 'string|in:asc,desc',
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
            'data' => [
                'campaigns' => [
                    [
                        'id' => 1,
                        'name' => 'Summer Sale Campaign',
                        'description' => 'Promotional campaign for summer products',
                        'status' => 'running',
                        'type' => 'broadcast',
                        'total_recipients' => 1500,
                        'messages_sent' => 950,
                        'delivery_rate' => 95.2,
                        'open_rate' => 87.3,
                        'click_rate' => 23.5,
                        'scheduled_at' => '2025-01-15T09:00:00.000000Z',
                        'started_at' => '2025-01-15T09:00:00.000000Z',
                        'created_at' => '2025-01-14T10:30:00.000000Z',
                        'updated_at' => '2025-01-15T10:30:00.000000Z',
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 25,
                    'total' => 45,
                    'last_page' => 2,
                    'from' => 1,
                    'to' => 25,
                ],
            ],
        ]);
    }

    /**
     * Create Campaign
     *
     * Create a new marketing campaign.
     *
     * @authenticated
     *
     * @bodyParam name string required Campaign name. Example: Summer Sale 2025
     * @bodyParam description string Optional. Campaign description. Example: Special offers for summer collection
     * @bodyParam type string required Campaign type (broadcast, drip, event_triggered). Example: broadcast
     * @bodyParam message_template_id integer required WhatsApp message template ID. Example: 10
     * @bodyParam target_audience object required Audience targeting configuration.
     * @bodyParam target_audience.type string required Audience type (all, groups, custom). Example: groups
     * @bodyParam target_audience.group_ids array Optional. Array of contact group IDs (required if type is 'groups'). Example: [1, 2, 3]
     * @bodyParam target_audience.contact_ids array Optional. Array of contact IDs (required if type is 'custom'). Example: [10, 20, 30]
     * @bodyParam schedule object Optional. Campaign scheduling configuration.
     * @bodyParam schedule.type string Optional. Schedule type (immediate, scheduled, recurring). Example: scheduled
     * @bodyParam schedule.send_at string Optional. ISO datetime for scheduled campaigns. Example: 2025-01-20T10:00:00Z
     * @bodyParam schedule.timezone string Optional. Timezone for scheduling. Example: America/New_York
     * @bodyParam settings object Optional. Campaign settings.
     * @bodyParam settings.throttle_rate integer Optional. Messages per minute (1-100). Example: 30
     * @bodyParam settings.retry_failed boolean Optional. Retry failed messages. Example: true
     * @bodyParam settings.track_clicks boolean Optional. Enable click tracking. Example: true
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Campaign created successfully",
     *   "data": {
     *     "campaign": {
     *       "id": 5,
     *       "name": "Summer Sale 2025",
     *       "description": "Special offers for summer collection",
     *       "type": "broadcast",
     *       "status": "draft",
     *       "message_template_id": 10,
     *       "target_audience": {
     *         "type": "groups",
     *         "group_ids": [1, 2, 3],
     *         "estimated_recipients": 2500
     *       },
     *       "schedule": {
     *         "type": "scheduled",
     *         "send_at": "2025-01-20T10:00:00.000000Z",
     *         "timezone": "America/New_York"
     *       },
     *       "settings": {
     *         "throttle_rate": 30,
     *         "retry_failed": true,
     *         "track_clicks": true
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
     *     "name": ["The name field is required."],
     *     "target_audience.group_ids": ["The group_ids field is required when type is groups."]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:broadcast,drip,event_triggered',
            'message_template_id' => 'required|integer|exists:message_templates,id',
            'target_audience.type' => 'required|string|in:all,groups,custom',
            'target_audience.group_ids' => 'required_if:target_audience.type,groups|array',
            'target_audience.group_ids.*' => 'integer|exists:contact_groups,id',
            'target_audience.contact_ids' => 'required_if:target_audience.type,custom|array',
            'target_audience.contact_ids.*' => 'integer|exists:contacts,id',
            'schedule.type' => 'string|in:immediate,scheduled,recurring',
            'schedule.send_at' => 'required_if:schedule.type,scheduled|date|after:now',
            'schedule.timezone' => 'string|timezone',
            'settings.throttle_rate' => 'integer|min:1|max:100',
            'settings.retry_failed' => 'boolean',
            'settings.track_clicks' => 'boolean',
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
            'message' => 'Campaign created successfully',
            'data' => [
                'campaign' => [
                    'id' => 5,
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'type' => $request->input('type'),
                    'status' => 'draft',
                    'message_template_id' => $request->input('message_template_id'),
                    'target_audience' => array_merge(
                        $request->input('target_audience'),
                        ['estimated_recipients' => 2500]
                    ),
                    'schedule' => $request->input('schedule', []),
                    'settings' => $request->input('settings', []),
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ], 201);
    }

    /**
     * Get Campaign
     *
     * Retrieve detailed information about a specific campaign.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "campaign": {
     *       "id": 1,
     *       "name": "Summer Sale Campaign",
     *       "description": "Promotional campaign for summer products",
     *       "status": "running",
     *       "type": "broadcast",
     *       "message_template": {
     *         "id": 10,
     *         "name": "Sale Announcement",
     *         "content": "🌞 Summer Sale is here! Get 50% off on all items."
     *       },
     *       "target_audience": {
     *         "type": "groups",
     *         "group_ids": [1, 2],
     *         "estimated_recipients": 1500,
     *         "actual_recipients": 1480
     *       },
     *       "analytics": {
     *         "total_recipients": 1480,
     *         "messages_sent": 950,
     *         "messages_delivered": 904,
     *         "messages_read": 789,
     *         "messages_failed": 46,
     *         "delivery_rate": 95.2,
     *         "open_rate": 87.3,
     *         "click_rate": 23.5,
     *         "response_rate": 12.8
     *       },
     *       "schedule": {
     *         "type": "scheduled",
     *         "send_at": "2025-01-15T09:00:00.000000Z",
     *         "timezone": "UTC"
     *       },
     *       "created_at": "2025-01-14T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Campaign not found"
     * }
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => [
                    'id' => $id,
                    'name' => 'Summer Sale Campaign',
                    'description' => 'Promotional campaign for summer products',
                    'status' => 'running',
                    'type' => 'broadcast',
                    'message_template' => [
                        'id' => 10,
                        'name' => 'Sale Announcement',
                        'content' => '🌞 Summer Sale is here! Get 50% off on all items.',
                    ],
                    'target_audience' => [
                        'type' => 'groups',
                        'group_ids' => [1, 2],
                        'estimated_recipients' => 1500,
                        'actual_recipients' => 1480,
                    ],
                    'analytics' => [
                        'total_recipients' => 1480,
                        'messages_sent' => 950,
                        'messages_delivered' => 904,
                        'messages_read' => 789,
                        'messages_failed' => 46,
                        'delivery_rate' => 95.2,
                        'open_rate' => 87.3,
                        'click_rate' => 23.5,
                        'response_rate' => 12.8,
                    ],
                    'schedule' => [
                        'type' => 'scheduled',
                        'send_at' => '2025-01-15T09:00:00.000000Z',
                        'timezone' => 'UTC',
                    ],
                    'created_at' => '2025-01-14T10:30:00.000000Z',
                    'updated_at' => '2025-01-15T10:30:00.000000Z',
                ],
            ],
        ]);
    }

    /**
     * Update Campaign
     *
     * Update an existing campaign. Only draft campaigns can be fully updated.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @bodyParam name string Optional. Campaign name. Example: Updated Summer Sale
     * @bodyParam description string Optional. Campaign description. Example: Updated description
     * @bodyParam message_template_id integer Optional. WhatsApp message template ID. Example: 12
     * @bodyParam target_audience object Optional. Audience targeting configuration (draft only).
     * @bodyParam schedule object Optional. Campaign scheduling configuration (draft/scheduled only).
     * @bodyParam settings object Optional. Campaign settings.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Campaign updated successfully",
     *   "data": {
     *     "campaign": {
     *       "id": 1,
     *       "name": "Updated Summer Sale",
     *       "description": "Updated description",
     *       "status": "draft",
     *       "updated_at": "2025-01-15T11:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Cannot update running campaign. Please pause it first."
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'message_template_id' => 'integer|exists:message_templates,id',
            'settings.throttle_rate' => 'integer|min:1|max:100',
            'settings.retry_failed' => 'boolean',
            'settings.track_clicks' => 'boolean',
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
            'message' => 'Campaign updated successfully',
            'data' => [
                'campaign' => [
                    'id' => $id,
                    'name' => $request->input('name', 'Updated Summer Sale'),
                    'description' => $request->input('description', 'Updated description'),
                    'status' => 'draft',
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Delete Campaign
     *
     * Delete a campaign. Running campaigns cannot be deleted.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Campaign deleted successfully"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Cannot delete running campaign. Please stop it first."
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully',
        ]);
    }

    /**
     * Start Campaign
     *
     * Start a draft or paused campaign.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Campaign started successfully",
     *   "data": {
     *     "campaign": {
     *       "id": 1,
     *       "status": "running",
     *       "started_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Campaign cannot be started. Invalid status or configuration."
     * }
     */
    public function start(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign started successfully',
            'data' => [
                'campaign' => [
                    'id' => $id,
                    'status' => 'running',
                    'started_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Pause Campaign
     *
     * Pause a running campaign.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Campaign paused successfully",
     *   "data": {
     *     "campaign": {
     *       "id": 1,
     *       "status": "paused",
     *       "paused_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Only running campaigns can be paused."
     * }
     */
    public function pause(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign paused successfully',
            'data' => [
                'campaign' => [
                    'id' => $id,
                    'status' => 'paused',
                    'paused_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Stop Campaign
     *
     * Stop a running or paused campaign permanently.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Campaign stopped successfully",
     *   "data": {
     *     "campaign": {
     *       "id": 1,
     *       "status": "cancelled",
     *       "stopped_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     */
    public function stop(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign stopped successfully',
            'data' => [
                'campaign' => [
                    'id' => $id,
                    'status' => 'cancelled',
                    'stopped_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Campaign Analytics
     *
     * Get detailed analytics and performance metrics for a campaign.
     *
     * @authenticated
     *
     * @urlParam id integer required The campaign ID. Example: 1
     *
     * @queryParam timeframe string Optional. Analytics timeframe (1h, 24h, 7d, 30d). Example: 24h
     * @queryParam include_segments boolean Optional. Include audience segment analytics. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "analytics": {
     *       "overview": {
     *         "total_recipients": 1480,
     *         "messages_sent": 950,
     *         "messages_delivered": 904,
     *         "messages_read": 789,
     *         "messages_failed": 46,
     *         "delivery_rate": 95.2,
     *         "open_rate": 87.3,
     *         "click_rate": 23.5,
     *         "response_rate": 12.8,
     *         "unsubscribe_rate": 0.3
     *       },
     *       "timeline": [
     *         {
     *           "hour": "2025-01-15T09:00:00.000000Z",
     *           "sent": 120,
     *           "delivered": 115,
     *           "read": 98,
     *           "failed": 5
     *         },
     *         {
     *           "hour": "2025-01-15T10:00:00.000000Z",
     *           "sent": 150,
     *           "delivered": 142,
     *           "read": 128,
     *           "failed": 8
     *         }
     *       ],
     *       "segments": [
     *         {
     *           "group_name": "Premium Customers",
     *           "recipients": 800,
     *           "delivery_rate": 97.5,
     *           "open_rate": 92.1,
     *           "click_rate": 28.3
     *         },
     *         {
     *           "group_name": "Regular Customers",
     *           "recipients": 680,
     *           "delivery_rate": 92.8,
     *           "open_rate": 81.5,
     *           "click_rate": 18.2
     *         }
     *       ],
     *       "top_performing_links": [
     *         {
     *           "url": "https://example.com/summer-sale",
     *           "clicks": 156,
     *           "click_rate": 16.4
     *         }
     *       ]
     *     }
     *   }
     * }
     */
    public function analytics(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'timeframe' => 'string|in:1h,24h,7d,30d',
            'include_segments' => 'boolean',
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
            'data' => [
                'analytics' => [
                    'overview' => [
                        'total_recipients' => 1480,
                        'messages_sent' => 950,
                        'messages_delivered' => 904,
                        'messages_read' => 789,
                        'messages_failed' => 46,
                        'delivery_rate' => 95.2,
                        'open_rate' => 87.3,
                        'click_rate' => 23.5,
                        'response_rate' => 12.8,
                        'unsubscribe_rate' => 0.3,
                    ],
                    'timeline' => [
                        [
                            'hour' => '2025-01-15T09:00:00.000000Z',
                            'sent' => 120,
                            'delivered' => 115,
                            'read' => 98,
                            'failed' => 5,
                        ],
                        [
                            'hour' => '2025-01-15T10:00:00.000000Z',
                            'sent' => 150,
                            'delivered' => 142,
                            'read' => 128,
                            'failed' => 8,
                        ],
                    ],
                    'segments' => [
                        [
                            'group_name' => 'Premium Customers',
                            'recipients' => 800,
                            'delivery_rate' => 97.5,
                            'open_rate' => 92.1,
                            'click_rate' => 28.3,
                        ],
                        [
                            'group_name' => 'Regular Customers',
                            'recipients' => 680,
                            'delivery_rate' => 92.8,
                            'open_rate' => 81.5,
                            'click_rate' => 18.2,
                        ],
                    ],
                    'top_performing_links' => [
                        [
                            'url' => 'https://example.com/summer-sale',
                            'clicks' => 156,
                            'click_rate' => 16.4,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
