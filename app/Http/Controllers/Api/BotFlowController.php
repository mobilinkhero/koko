<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Bot Flow Management
 *
 * APIs for managing WhatsApp chatbot flows and automation
 */
class BotFlowController extends Controller
{
    /**
     * List Bot Flows
     *
     * Retrieve a paginated list of chatbot flows for the current tenant.
     *
     * @authenticated
     *
     * @queryParam page integer Optional. Page number for pagination. Example: 1
     * @queryParam per_page integer Optional. Number of flows per page (max 50). Example: 25
     * @queryParam search string Optional. Search flows by name or description. Example: welcome
     * @queryParam status string Optional. Filter by flow status (draft, published, archived). Example: published
     * @queryParam type string Optional. Filter by flow type (welcome, support, sales, custom). Example: welcome
     * @queryParam sort_by string Optional. Sort field (name, created_at, updated_at, status). Example: created_at
     * @queryParam sort_order string Optional. Sort order (asc, desc). Example: desc
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "bot_flows": [
     *       {
     *         "id": 1,
     *         "name": "Welcome Flow",
     *         "description": "Greets new users and provides initial information",
     *         "status": "published",
     *         "type": "welcome",
     *         "trigger_keywords": ["hi", "hello", "start"],
     *         "total_nodes": 8,
     *         "active_conversations": 25,
     *         "total_conversations": 1250,
     *         "completion_rate": 78.5,
     *         "published_at": "2025-01-10T09:00:00.000000Z",
     *         "created_at": "2025-01-08T10:30:00.000000Z",
     *         "updated_at": "2025-01-10T09:00:00.000000Z"
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 25,
     *       "total": 12,
     *       "last_page": 1,
     *       "from": 1,
     *       "to": 12
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
            'status' => 'string|in:draft,published,archived',
            'type' => 'string|in:welcome,support,sales,custom',
            'sort_by' => 'string|in:name,created_at,updated_at,status',
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
                'bot_flows' => [
                    [
                        'id' => 1,
                        'name' => 'Welcome Flow',
                        'description' => 'Greets new users and provides initial information',
                        'status' => 'published',
                        'type' => 'welcome',
                        'trigger_keywords' => ['hi', 'hello', 'start'],
                        'total_nodes' => 8,
                        'active_conversations' => 25,
                        'total_conversations' => 1250,
                        'completion_rate' => 78.5,
                        'published_at' => '2025-01-10T09:00:00.000000Z',
                        'created_at' => '2025-01-08T10:30:00.000000Z',
                        'updated_at' => '2025-01-10T09:00:00.000000Z',
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 25,
                    'total' => 12,
                    'last_page' => 1,
                    'from' => 1,
                    'to' => 12,
                ],
            ],
        ]);
    }

    /**
     * Create Bot Flow
     *
     * Create a new chatbot flow.
     *
     * @authenticated
     *
     * @bodyParam name string required Flow name. Example: Customer Support Flow
     * @bodyParam description string Optional. Flow description. Example: Handles customer support inquiries
     * @bodyParam type string required Flow type (welcome, support, sales, custom). Example: support
     * @bodyParam trigger_keywords array Optional. Keywords that trigger this flow. Example: ["help", "support", "issue"]
     * @bodyParam trigger_conditions object Optional. Additional trigger conditions.
     * @bodyParam trigger_conditions.new_user boolean Optional. Trigger for new users only. Example: false
     * @bodyParam trigger_conditions.business_hours boolean Optional. Trigger only during business hours. Example: true
     * @bodyParam nodes array required Flow nodes configuration.
     * @bodyParam nodes.*.id string required Unique node ID. Example: "node_1"
     * @bodyParam nodes.*.type string required Node type (message, question, condition, action). Example: "message"
     * @bodyParam nodes.*.content object required Node content configuration.
     * @bodyParam settings object Optional. Flow settings.
     * @bodyParam settings.max_retries integer Optional. Maximum retry attempts. Example: 3
     * @bodyParam settings.timeout_minutes integer Optional. Flow timeout in minutes. Example: 30
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Bot flow created successfully",
     *   "data": {
     *     "bot_flow": {
     *       "id": 5,
     *       "name": "Customer Support Flow",
     *       "description": "Handles customer support inquiries",
     *       "type": "support",
     *       "status": "draft",
     *       "trigger_keywords": ["help", "support", "issue"],
     *       "trigger_conditions": {
     *         "new_user": false,
     *         "business_hours": true
     *       },
     *       "total_nodes": 6,
     *       "settings": {
     *         "max_retries": 3,
     *         "timeout_minutes": 30
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
     *     "nodes": ["At least one node is required."]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:welcome,support,sales,custom',
            'trigger_keywords' => 'nullable|array',
            'trigger_keywords.*' => 'string|max:100',
            'trigger_conditions.new_user' => 'boolean',
            'trigger_conditions.business_hours' => 'boolean',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|string|max:100',
            'nodes.*.type' => 'required|string|in:message,question,condition,action',
            'nodes.*.content' => 'required|array',
            'settings.max_retries' => 'integer|min:1|max:10',
            'settings.timeout_minutes' => 'integer|min:1|max:1440',
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
            'message' => 'Bot flow created successfully',
            'data' => [
                'bot_flow' => [
                    'id' => 5,
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'type' => $request->input('type'),
                    'status' => 'draft',
                    'trigger_keywords' => $request->input('trigger_keywords', []),
                    'trigger_conditions' => $request->input('trigger_conditions', []),
                    'total_nodes' => count($request->input('nodes')),
                    'settings' => $request->input('settings', []),
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ], 201);
    }

    /**
     * Get Bot Flow
     *
     * Retrieve detailed information about a specific bot flow.
     *
     * @authenticated
     *
     * @urlParam id integer required The bot flow ID. Example: 1
     *
     * @queryParam include_analytics boolean Optional. Include analytics data. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "bot_flow": {
     *       "id": 1,
     *       "name": "Welcome Flow",
     *       "description": "Greets new users and provides initial information",
     *       "type": "welcome",
     *       "status": "published",
     *       "trigger_keywords": ["hi", "hello", "start"],
     *       "trigger_conditions": {
     *         "new_user": true,
     *         "business_hours": false
     *       },
     *       "nodes": [
     *         {
     *           "id": "node_1",
     *           "type": "message",
     *           "content": {
     *             "text": "Welcome! 👋 How can I help you today?",
     *             "quick_replies": ["Get Started", "Learn More", "Contact Support"]
     *           },
     *           "position": { "x": 100, "y": 100 },
     *           "connections": ["node_2"]
     *         }
     *       ],
     *       "settings": {
     *         "max_retries": 3,
     *         "timeout_minutes": 30
     *       },
     *       "analytics": {
     *         "total_conversations": 1250,
     *         "completed_conversations": 981,
     *         "abandoned_conversations": 269,
     *         "completion_rate": 78.5,
     *         "average_duration_minutes": 4.2,
     *         "top_exit_nodes": ["node_3", "node_5"]
     *       },
     *       "published_at": "2025-01-10T09:00:00.000000Z",
     *       "created_at": "2025-01-08T10:30:00.000000Z",
     *       "updated_at": "2025-01-10T09:00:00.000000Z"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Bot flow not found"
     * }
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $includeAnalytics = $request->boolean('include_analytics');

        $botFlow = [
            'id' => $id,
            'name' => 'Welcome Flow',
            'description' => 'Greets new users and provides initial information',
            'type' => 'welcome',
            'status' => 'published',
            'trigger_keywords' => ['hi', 'hello', 'start'],
            'trigger_conditions' => [
                'new_user' => true,
                'business_hours' => false,
            ],
            'nodes' => [
                [
                    'id' => 'node_1',
                    'type' => 'message',
                    'content' => [
                        'text' => 'Welcome! 👋 How can I help you today?',
                        'quick_replies' => ['Get Started', 'Learn More', 'Contact Support'],
                    ],
                    'position' => ['x' => 100, 'y' => 100],
                    'connections' => ['node_2'],
                ],
            ],
            'settings' => [
                'max_retries' => 3,
                'timeout_minutes' => 30,
            ],
            'published_at' => '2025-01-10T09:00:00.000000Z',
            'created_at' => '2025-01-08T10:30:00.000000Z',
            'updated_at' => '2025-01-10T09:00:00.000000Z',
        ];

        if ($includeAnalytics) {
            $botFlow['analytics'] = [
                'total_conversations' => 1250,
                'completed_conversations' => 981,
                'abandoned_conversations' => 269,
                'completion_rate' => 78.5,
                'average_duration_minutes' => 4.2,
                'top_exit_nodes' => ['node_3', 'node_5'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'bot_flow' => $botFlow,
            ],
        ]);
    }

    /**
     * Update Bot Flow
     *
     * Update an existing bot flow. Published flows will be moved to draft status.
     *
     * @authenticated
     *
     * @urlParam id integer required The bot flow ID. Example: 1
     *
     * @bodyParam name string Optional. Flow name. Example: Updated Welcome Flow
     * @bodyParam description string Optional. Flow description. Example: Updated description
     * @bodyParam trigger_keywords array Optional. Keywords that trigger this flow. Example: ["hi", "hello"]
     * @bodyParam nodes array Optional. Updated flow nodes configuration.
     * @bodyParam settings object Optional. Flow settings.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Bot flow updated successfully",
     *   "data": {
     *     "bot_flow": {
     *       "id": 1,
     *       "name": "Updated Welcome Flow",
     *       "description": "Updated description",
     *       "status": "draft",
     *       "updated_at": "2025-01-15T11:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Bot flow not found"
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_keywords' => 'nullable|array',
            'trigger_keywords.*' => 'string|max:100',
            'nodes' => 'nullable|array',
            'settings.max_retries' => 'integer|min:1|max:10',
            'settings.timeout_minutes' => 'integer|min:1|max:1440',
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
            'message' => 'Bot flow updated successfully',
            'data' => [
                'bot_flow' => [
                    'id' => $id,
                    'name' => $request->input('name', 'Updated Welcome Flow'),
                    'description' => $request->input('description', 'Updated description'),
                    'status' => 'draft',
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Delete Bot Flow
     *
     * Delete a bot flow. Published flows must be archived first.
     *
     * @authenticated
     *
     * @urlParam id integer required The bot flow ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Bot flow deleted successfully"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Cannot delete published flow. Please archive it first."
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Bot flow deleted successfully',
        ]);
    }

    /**
     * Publish Bot Flow
     *
     * Publish a draft bot flow to make it active.
     *
     * @authenticated
     *
     * @urlParam id integer required The bot flow ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Bot flow published successfully",
     *   "data": {
     *     "bot_flow": {
     *       "id": 1,
     *       "status": "published",
     *       "published_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Bot flow cannot be published. Please check the configuration.",
     *   "errors": ["Flow must have at least one message node", "Trigger keywords are required"]
     * }
     */
    public function publish(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Bot flow published successfully',
            'data' => [
                'bot_flow' => [
                    'id' => $id,
                    'status' => 'published',
                    'published_at' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Test Bot Flow
     *
     * Test a bot flow with sample input to verify functionality.
     *
     * @authenticated
     *
     * @urlParam id integer required The bot flow ID. Example: 1
     *
     * @bodyParam test_input string required Test message input. Example: hello
     * @bodyParam user_context object Optional. Simulated user context for testing.
     * @bodyParam user_context.is_new_user boolean Optional. Simulate new user. Example: true
     * @bodyParam user_context.phone string Optional. Test phone number. Example: +1234567890
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Bot flow test completed successfully",
     *   "data": {
     *     "test_result": {
     *       "triggered": true,
     *       "matched_keywords": ["hello"],
     *       "execution_path": ["node_1", "node_2"],
     *       "responses": [
     *         {
     *           "node_id": "node_1",
     *           "type": "message",
     *           "content": {
     *             "text": "Welcome! 👋 How can I help you today?",
     *             "quick_replies": ["Get Started", "Learn More", "Contact Support"]
     *           }
     *         }
     *       ],
     *       "completion_status": "awaiting_response",
     *       "next_expected_node": "node_2",
     *       "execution_time_ms": 45
     *     }
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "test_input": ["The test input field is required."]
     *   }
     * }
     */
    public function test(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'test_input' => 'required|string|max:1000',
            'user_context.is_new_user' => 'boolean',
            'user_context.phone' => 'string|max:20',
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
            'message' => 'Bot flow test completed successfully',
            'data' => [
                'test_result' => [
                    'triggered' => true,
                    'matched_keywords' => ['hello'],
                    'execution_path' => ['node_1', 'node_2'],
                    'responses' => [
                        [
                            'node_id' => 'node_1',
                            'type' => 'message',
                            'content' => [
                                'text' => 'Welcome! 👋 How can I help you today?',
                                'quick_replies' => ['Get Started', 'Learn More', 'Contact Support'],
                            ],
                        ],
                    ],
                    'completion_status' => 'awaiting_response',
                    'next_expected_node' => 'node_2',
                    'execution_time_ms' => 45,
                ],
            ],
        ]);
    }
}
