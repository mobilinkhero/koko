<?php

namespace Modules\ApiWebhookManager\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Http\Request;

class PlanApiController extends Controller
{
    /**
     * List Pricing Plan
     *
     * Retrieves a grouped list of all active pricing plans along with their features.
     * Plans are grouped by their billing period (e.g., Monthly, Yearly), and each plan
     * includes details like name, slug, price, and an array of features. The billing
     * period key is capitalized (e.g., "Monthly").
     *
     * @response status=200 scenario="success" {
     *   "status": "success",
     *   "data": {
     *     "Monthly": [
     *       {
     *         "id": 10,
     *         "name": "Business Plan",
     *         "slug": "plan-77",
     *         "description": "Business Plan",
     *         "price": "$700.00",
     *         "yearly_price": "$0.00",
     *         "yearly_discount": 0,
     *         "billing_period": "monthly",
     *         "trial_days": 0,
     *         "interval": 1,
     *         "is_active": true,
     *         "is_free": false,
     *         "featured": true,
     *         "color": "#000000",
     *         "currency_id": 1,
     *         "created_at": "2025-10-16T04:43:49.000000Z",
     *         "updated_at": "2025-10-16T04:43:49.000000Z",
     *         "planFeatures": [
     *           {
     *             "id": 334,
     *             "plan_id": 10,
     *             "feature_id": 1,
     *             "name": "Contacts",
     *             "slug": "contacts",
     *             "value": "5",
     *             "feature": {
     *               "id": 1,
     *               "name": "Contacts ",
     *               "slug": "contacts",
     *               "type": "limit",
     *               "description": "Number of contacts allowed"
     *             }
     *           },
     *           {
     *             "id": 335,
     *             "plan_id": 10,
     *             "feature_id": 2,
     *             "name": "Template Bots ",
     *             "slug": "template_bots",
     *             "value": "5",
     *             "feature": {
     *               "id": 2,
     *               "name": "Template Bots ",
     *               "slug": "template_bots",
     *               "type": "limit",
     *               "description": "Number of template bots allowed"
     *             }
     *           },
     *           ...
     *           {
     *             "id": 343,
     *             "plan_id": 10,
     *             "feature_id": 8,
     *             "name": "Conversation ",
     *             "slug": "conversations",
     *             "value": "Unlimited",
     *             "feature": {
     *               "id": 8,
     *               "name": "Conversation ",
     *               "slug": "conversations",
     *               "type": "limit",
     *               "description": "Number of conversation allowed"
     *             }
     *           },
     *           {
     *             "id": 345,
     *             "plan_id": 10,
     *             "feature_id": 12,
     *             "name": "Enable Api ",
     *             "slug": "enable_api",
     *             "value": "0",
     *             "feature": {
     *               "id": 12,
     *               "name": "Enable Api ",
     *               "slug": "enable_api",
     *               "type": "limit",
     *               "description": "Enable(-1) or Disable(0) Api"
     *             }
     *           },
     *           {
     *             "name": "Whatsapp Webhook",
     *             "slug": "whatsapp_webhook",
     *             "value": "Unlimited"
     *           }

     *         ],
     *          "Currency": {
     *              "id": 1,
     *              "name": "US Dollar",
     *              "code": "USD",
     *              "symbol": "$",
     *              "format": "before_amount",
     *              "exchange_rate": "1.000000",
     *              "is_default": true,
     *              "created_at": "2025-10-15T06:52:07.000000Z",
     *              "updated_at": "2025-10-15T06:52:07.000000Z"
     *              }
     *       }
     *     ]
     *   }
     * }
     * @response status=404 scenario="no plans found" {
     *   "status": "error",
     *   "message": "Plan not found"
     * }
     * @response status=500 scenario="server error" {
     *   "status": "error",
     *   "message": "Failed to fetch plans",
     *   "error": "Failed to fetch plans"
     * }
     * @response status=401 scenario="unauthorized" {
     *   "status": "error",
     *   "error": "Invalid API token"
     * }
     */
    public function index(Request $request)
    {
        try {
            $planRepo = app(PlanRepository::class);

            // Get all plans with features
            $plans = collect($planRepo->getPlansWithFeatures())
                ->where('is_active', true)
                ->sortBy([
                    ['is_free', 'desc'],
                    ['price', 'asc'],
                ])
                ->values();

            if ($plans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('plan_not_found'),
                ], 404);
            }

            // Group plans by billing_period
            $grouped = $plans->groupBy('billing_period')->mapWithKeys(function ($group, $key) {
                return [
                    $key => $group->map(function ($plan) {
                        // Normalize plan to array
                        if (is_array($plan)) {
                            $planArray = $plan;
                        } elseif (is_object($plan) && method_exists($plan, 'toArray')) {
                            $planArray = $plan->toArray();
                        } else {
                            $planArray = (array) $plan;
                        }

                        $planArray['plan_url'] = route('admin.dashboard', ['plan_id' => $plan['id']]);
                        $planArray['currency'] = get_base_currency();
                        foreach ($planArray['planFeatures'] as $key => $feature) {
                            if (isset($feature['value']) && $feature['value'] == '-1') {
                                $feature['value'] = 'Unlimited';
                            }
                            $planArray['planFeatures'][$key] = $feature;
                        }

                        // Remove the unwanted raw plan_features
                        unset($planArray['plan_features']);

                        return $planArray;
                    })->values()->all(),
                ];
            })->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $grouped,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_plans'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List Pricing Plan
     *
     * Retrieves a grouped list of all active pricing plans along with their features.
     * Plans are grouped by their billing period (e.g., Monthly, Yearly), and each plan
     * includes details like name, slug, price, and an array of features. The billing
     * period key is capitalized (e.g., "Monthly").
     *
     * @urlParam id integer required The ID of the plan. Example: 1
     *
     * @response status=200 scenario="success" {
     *   "status": "success",
     *   "data": {
     *     "Monthly": [
     *       {
     *         "id": 10,
     *         "name": "Business Plan",
     *         "slug": "plan-77",
     *         "description": "Business Plan",
     *         "price": "$700.00",
     *         "yearly_price": "$0.00",
     *         "yearly_discount": 0,
     *         "billing_period": "monthly",
     *         "trial_days": 0,
     *         "interval": 1,
     *         "is_active": true,
     *         "is_free": false,
     *         "featured": true,
     *         "color": "#000000",
     *         "currency_id": 1,
     *         "created_at": "2025-10-16T04:43:49.000000Z",
     *         "updated_at": "2025-10-16T04:43:49.000000Z",
     *         "planFeatures": [
     *           {
     *             "id": 334,
     *             "plan_id": 10,
     *             "feature_id": 1,
     *             "name": "Contacts ",
     *             "slug": "contacts",
     *             "value": "5",
     *             "feature": {
     *               "id": 1,
     *               "name": "Contacts ",
     *               "slug": "contacts",
     *               "type": "limit",
     *               "description": "Number of contacts allowed"
     *             }
     *           },
     *           {
     *             "id": 335,
     *             "plan_id": 10,
     *             "feature_id": 2,
     *             "name": "Template Bots ",
     *             "slug": "template_bots",
     *             "value": "5",
     *             "feature": {
     *               "id": 2,
     *               "name": "Template Bots ",
     *               "slug": "template_bots",
     *               "type": "limit",
     *               "description": "Number of template bots allowed"
     *             }
     *           },
     *           ...
     *           {
     *             "id": 343,
     *             "plan_id": 10,
     *             "feature_id": 8,
     *             "name": "Conversation ",
     *             "slug": "conversations",
     *             "value": "Unlimited",
     *             "feature": {
     *               "id": 8,
     *               "name": "Conversation ",
     *               "slug": "conversations",
     *               "type": "limit",
     *               "description": "Number of conversation allowed"
     *             }
     *           },
     *           {
     *             "id": 345,
     *             "plan_id": 10,
     *             "feature_id": 12,
     *             "name": "Enable Api ",
     *             "slug": "enable_api",
     *             "value": "0",
     *             "feature": {
     *               "id": 12,
     *               "name": "Enable Api ",
     *               "slug": "enable_api",
     *               "type": "limit",
     *               "description": "Enable(-1) or Disable(0) Api"
     *             }
     *           },
     *           {
     *             "name": "Whatsapp Webhook",
     *             "slug": "whatsapp_webhook",
     *             "value": "Unlimited"
     *           },
     *         "Currency": {
     *              "id": 1,
     *              "name": "US Dollar",
     *              "code": "USD",
     *              "symbol": "$",
     *              "format": "before_amount",
     *              "exchange_rate": "1.000000",
     *              "is_default": true,
     *              "created_at": "2025-10-15T06:52:07.000000Z",
     *              "updated_at": "2025-10-15T06:52:07.000000Z"
     *              }
     *         ]
     *       }
     *     ]
     *   }
     * }
     * @response status=404 scenario="no plans found" {
     *   "status": "error",
     *   "message": "Plan not found"
     * }
     * @response status=500 scenario="server error" {
     *   "status": "error",
     *   "message": "Failed to fetch plans",
     *   "error": "Failed to fetch plans"
     * }
     * @response status=401 scenario="unauthorized" {
     *   "status": "error",
     *   "error": "Invalid API token"
     * }
     */
    public function show(Request $request, $id)
    {
        try {
            $planRepo = app(PlanRepository::class);

            // Get all plans with features
            $plans = collect($planRepo->getPlansWithFeatures())
                ->where('is_active', true)
                ->where('id', $id)
                ->sortBy([
                    ['is_free', 'desc'],
                    ['price', 'asc'],
                ])
                ->values();

            if ($plans->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('plan_not_found'),
                ], 404);
            }

            // Group plans by billing_period and ensure the resulting structure is arrays (JSON serializable)
            $grouped = $plans->groupBy('billing_period')->mapWithKeys(function ($group, $key) {
                return [
                    $key => $group->map(function ($plan) {
                        // Normalize plan to array
                        if (is_array($plan)) {
                            $planArray = $plan;
                        } elseif (is_object($plan) && method_exists($plan, 'toArray')) {
                            $planArray = $plan->toArray();
                        } else {
                            $planArray = (array) $plan;
                        }

                        $planArray['currency'] = get_base_currency();
                        $planArray['plan_url'] = route('admin.dashboard', ['plan_id' => $plan['id']]);

                        foreach ($planArray['planFeatures'] as $key => $feature) {
                            if (isset($feature['value']) && $feature['value'] == '-1') {
                                $feature['value'] = 'Unlimited';
                            }
                            $planArray['planFeatures'][$key] = $feature;
                        }

                        // Remove the unwanted raw plan_features
                        unset($planArray['plan_features']);

                        return $planArray;
                    })->values()->all(),
                ];
            })->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $grouped,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => t('failed_to_fetch_plans'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
