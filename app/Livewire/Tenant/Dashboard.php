<?php

namespace App\Livewire\Tenant;

use App\Facades\TenantCache;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Tenant\AiPrompt;
use App\Models\Tenant\BotFlow;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignDetail;
use App\Models\Tenant\CannedReply;
use App\Models\Tenant\ChatMessage;
use App\Models\Tenant\Contact;
use App\Models\Tenant\MessageBot;
use App\Models\Tenant\TemplateBot;
use App\Models\User;
use App\Services\SubscriptionCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $tenantUser;

    public $currentTenant;

    public $activeSubscription;

    public $appName;

    public $nextBillingDate;

    public $planName;

    public $daysUntilBilling;

    // Usage Statistics
    public $teamMemberCount;

    public $teamMemberLimit;

    public $totalCampaigns;

    public $campaignLimit;

    public $totalContacts;

    public $contactLimit;

    public $totalConversations;

    public $conversationLimit;

    public $totalTemplateBots;

    public $templateBotLimit;

    public $totalMessageBots;

    public $messageBotLimit;

    public $totalAiPrompts;

    public $aiPromptLimit;

    public $totalCannedReplies;

    public $cannedReplyLimit;

    // Chart Data
    public $weeklyMessageData;

    public $contactSourcesData;

    public $audienceGrowthData;

    public $campaignStatisticsData;

    public $tenant_id;

    public $tenant_subdomain;

    // flow

    public $totalFlowsLimit;

    public $totalFlowsUsage;

    public $settings = [];

    /**
     * Get the current tenant using session-based context with fallback
     *
     * @return \App\Models\Tenant|null
     */
    protected function getCurrentTenant()
    {
        try {
            // Primary method: Use session-based tenant identification
            $tenantId = session('current_tenant_id');

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant instanceof Tenant) {
                    return $tenant;
                }
            }

            // Fallback method: Use traditional tenant context
            if (Tenant::checkCurrent()) {
                $tenant = Tenant::current();
                if ($tenant instanceof Tenant) {
                    // Sync session with current tenant for consistency
                    session(['current_tenant_id' => $tenant->id]);

                    return $tenant;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function mount()
    {
        $this->settings = get_batch_settings([
            'system.site_name',
        ]);
        // Only perform initial data load without showing success message
        // Initial data load from cache or fresh if needed
        $this->loadDashboardData();

        // Note: We don't call refreshDashboardData() on mount anymore
        // to avoid showing the success message on page load
    }

    private function getCacheKey($type = 'main')
    {
        $tenantId = $this->currentTenant->id;

        switch ($type) {
            case 'usage_stats':
                return "dashboard_usage_stats_tenant_{$tenantId}";
            case 'chart_data':
                return "dashboard_chart_data_tenant_{$tenantId}";
            case 'subscription':
                return "dashboard_subscription_tenant_{$tenantId}";
            case 'feature_usage':
                return "dashboard_feature_usage_tenant_{$tenantId}";
            case 'app_settings':
                return "dashboard_app_settings_tenant_{$tenantId}";
            default:
                return "dashboard_data_tenant_{$tenantId}";
        }
    }

    public function loadDashboardData()
    {
        try {
            // Get current authenticated user
            $this->tenantUser = Auth::user();

            // Get current tenant
            $this->currentTenant = $this->getCurrentTenant();

            if (! $this->currentTenant) {
                throw new \Exception('No current tenant found');
            }

            // Cache different components separately for better performance
            $this->loadCachedSubscriptionData();
            $this->loadCachedUsageStatistics();
            $this->loadCachedChartData();
            $this->loadCachedAppSettings();
        } catch (\Exception $e) {
            // Set defaults on error
            $this->setDefaultValues();
        }
    }

    private function setDefaultValues()
    {
        $this->appName = $this->settings['system.site_name'] ?? 'Whatsmark-SaaS';
        $this->activeSubscription = null;
        $this->planName = 'Free';
        $this->nextBillingDate = null;
        $this->daysUntilBilling = null;

        $this->teamMemberCount = 0;
        $this->teamMemberLimit = 1;
        $this->totalCampaigns = 0;
        $this->campaignLimit = 5;
        $this->totalContacts = 0;
        $this->contactLimit = 100;
        $this->totalConversations = 0;
        $this->conversationLimit = 1000;
        $this->totalTemplateBots = 0;
        $this->templateBotLimit = 3;
        $this->totalMessageBots = 0;
        $this->messageBotLimit = 3;
        $this->totalAiPrompts = 0;
        $this->aiPromptLimit = 5;
        $this->totalCannedReplies = 0;
        $this->cannedReplyLimit = 5;

        $this->weeklyMessageData = json_encode(['labels' => [], 'data' => []]);
        $this->contactSourcesData = json_encode(['labels' => [], 'data' => []]);
        $this->audienceGrowthData = ['labels' => [], 'datasets' => []];
        $this->campaignStatisticsData = ['labels' => [], 'datasets' => []];
    }

    private function getUsageStatistics($activeSubscription)
    {
        try {
            $plan = $activeSubscription ? $activeSubscription->plan : null;

            // Initialize usage statistics
            $usageStats = [
                'staff' => ['current' => 0, 'limit' => 0],
                'campaigns' => ['current' => 0, 'limit' => 0],
                'contacts' => ['current' => 0, 'limit' => 0],
                'conversations' => ['current' => 0, 'limit' => 0],
                'template_bots' => ['current' => 0, 'limit' => 0],
                'message_bots' => ['current' => 0, 'limit' => 0],
                'ai_prompts' => ['current' => 0, 'limit' => 0],
                'canned_replies' => ['current' => 0, 'limit' => 0],
                'bot_flow' => ['current' => 0, 'limit' => 0],
            ];

            // Try to get feature usage data from FeatureUsage model
            if ($activeSubscription) {
                // Get feature usage records for this tenant
                $featureUsages = \App\Models\Tenant\FeatureUsage::where('tenant_id', $this->currentTenant->id)
                    ->where('subscription_id', $activeSubscription->id)
                    ->get();

                // Process feature usages
                foreach ($featureUsages as $usage) {
                    if (isset($usageStats[$usage->feature_slug])) {
                        $usageStats[$usage->feature_slug]['current'] = $usage->used;
                        $usageStats[$usage->feature_slug]['limit'] = $usage->limit_value === -1 ? t('unlimited') : $usage->limit_value;
                    }
                }
            }

            // If we don't have usage data from FeatureUsage, or for missing features,
            // get the actual counts from the database with individual error handling

            // Get team member count (users in current tenant)
            if ($usageStats['staff']['current'] === 0) {
                try {
                    $usageStats['staff']['current'] = User::where('tenant_id', $this->currentTenant->id)
                        ->where('is_admin', false)
                        ->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get campaign count
            if ($usageStats['campaigns']['current'] === 0) {
                try {
                    $usageStats['campaigns']['current'] = Campaign::where('tenant_id', $this->currentTenant->id)->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get contact count
            if ($usageStats['contacts']['current'] === 0) {
                try {
                    if (! $this->tenant_id) {
                        $this->tenant_id = $this->currentTenant->id;
                    }
                    if (! $this->tenant_subdomain) {
                        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
                    }

                    if ($this->tenant_subdomain) {
                        $usageStats['contacts']['current'] = Contact::fromTenant($this->tenant_subdomain)->count();
                    }
                } catch (\Exception $e) {
                    // Silently continue if error occurs
                }
            }

            // Get conversation count (chat messages)
            if ($usageStats['conversations']['current'] === 0) {
                try {
                    if (! $this->tenant_id) {
                        $this->tenant_id = $this->currentTenant->id;
                    }
                    if (! $this->tenant_subdomain) {
                        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
                    }

                    if ($this->tenant_subdomain) {
                        $usageStats['conversations']['current'] = ChatMessage::fromTenant($this->tenant_subdomain)->count();
                    }
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get template bot count
            if ($usageStats['template_bots']['current'] === 0) {
                try {
                    $usageStats['template_bots']['current'] = TemplateBot::where('tenant_id', $this->currentTenant->id)->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get message bot count
            if ($usageStats['message_bots']['current'] === 0) {
                try {
                    $usageStats['message_bots']['current'] = MessageBot::where('tenant_id', $this->currentTenant->id)->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get bot flow count
            if ($usageStats['bot_flow']['current'] === 0) {
                try {
                    $usageStats['bot_flow']['current'] = BotFlow::where('tenant_id', $this->currentTenant->id)->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get AI prompt count
            if ($usageStats['ai_prompts']['current'] === 0) {
                try {
                    $usageStats['ai_prompts']['current'] = AiPrompt::where('tenant_id', $this->currentTenant->id)->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // Get canned reply count
            if ($usageStats['canned_replies']['current'] === 0) {
                try {
                    $usageStats['canned_replies']['current'] = CannedReply::withoutGlobalScope('tenant')
                        ->where('tenant_id', $this->currentTenant->id)
                        ->count();
                } catch (\Exception $e) {
                    // Silently continue if table doesn't exist
                }
            }

            // For limits, if we have an active subscription with a plan, get the limits from plan features
            if ($plan) {
                $planFeatures = $plan->features()->get();

                foreach ($planFeatures as $feature) {
                    if (isset($usageStats[$feature->slug])) {
                        $limitValue = $feature->value === '-1' ? t('unlimited') : (int) $feature->value;
                        $usageStats[$feature->slug]['limit'] = $limitValue;
                    }
                }
            }

            return $usageStats;
        } catch (\Exception $e) {
            // Return default values on error
            return [
                'staff' => ['current' => 0, 'limit' => 1],
                'campaigns' => ['current' => 0, 'limit' => 5],
                'contacts' => ['current' => 0, 'limit' => 100],
                'conversations' => ['current' => 0, 'limit' => 1000],
                'template_bots' => ['current' => 0, 'limit' => 3],
                'message_bots' => ['current' => 0, 'limit' => 3],
                'ai_prompts' => ['current' => 0, 'limit' => 5],
                'canned_replies' => ['current' => 0, 'limit' => 5],
                'bot_flow' => ['current' => 0, 'limit' => 0],
            ];
        }
    }

    private function getChartData()
    {
        try {
            $contactSources = $this->getContactSourcesData();
            $audienceGrowth = $this->getAudienceGrowthData();
            $campaignStatistics = $this->getCampaignStatisticsData();
            $weeklyMessages = $this->getWeeklyMessageData();

            return [
                'contact_sources' => $contactSources,
                'audience_growth' => $audienceGrowth,
                'campaign_statistics' => $campaignStatistics,
                'weekly_messages' => $weeklyMessages,
            ];
        } catch (\Exception $e) {
            return [
                'contact_sources' => ['labels' => ['No Data'], 'data' => [1]],
                'audience_growth' => ['labels' => [], 'datasets' => []],
                'campaign_statistics' => ['labels' => [], 'datasets' => []],
                'weekly_messages' => [
                    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'datasets' => [
                        [
                            'label' => t('messages_sent'),
                            'data' => [0, 0, 0, 0, 0, 0, 0],
                            'borderColor' => '#3B82F6',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        ],
                        [
                            'label' => t('delivered'),
                            'data' => [0, 0, 0, 0, 0, 0, 0],
                            'borderColor' => '#10B981',
                            'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        ],
                    ],
                ],
            ];
        }
    }

    private function getWeeklyMessageData()
    {
        try {
            $this->tenant_id = tenant_id();
            $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);

            $days = [];
            $messageCounts = [];
            $deliveredCounts = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $days[] = $date->format('D');

                $messageCount = ChatMessage::fromTenant($this->tenant_subdomain)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();
                $messageCounts[] = $messageCount;

                $deliveredCount = ChatMessage::fromTenant($this->tenant_subdomain)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status', 'delivered')
                    ->count();
                $deliveredCounts[] = $deliveredCount;
            }

            return [
                'labels' => $days,
                'datasets' => [
                    [
                        'label' => t('messages_sent'),
                        'data' => $messageCounts,
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    [
                        'label' => t('delivered'),
                        'data' => $deliveredCounts,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'datasets' => [
                    [
                        'label' => t('messages_sent'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    [
                        'label' => t('delivered'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    ],
                ],
            ];
        }
    }

    private function getContactSourcesData()
    {
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
        $contactModel = new Contact;
        $tableName = $contactModel->getTable();

        $sources = DB::table($tableName.' as contacts')
            ->join('sources', 'contacts.source_id', '=', 'sources.id')
            ->selectRaw('sources.name, COUNT(*) as count')
            ->groupBy('sources.name')
            ->pluck('count', 'name')
            ->toArray();

        if (empty($sources)) {
            return [
                'labels' => [t('website'), t('social_media'), t('referral')],
                'data' => [0, 0, 0],
            ];
        }

        return [
            'labels' => array_keys($sources),
            'data' => array_values($sources),
        ];
    }

    private function getAudienceGrowthData()
    {
        try {
            $this->tenant_id = tenant_id();
            $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);

            $months = [];
            $totalContactsData = [];
            $newContactsData = [];
            $totalLeadsData = [];
            $newLeadsData = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->format('M Y');
                $startOfMonth = $date->startOfMonth()->copy();
                $endOfMonth = $date->endOfMonth()->copy();

                $totalContacts = Contact::fromTenant($this->tenant_subdomain)
                    ->where('tenant_id', $this->tenant_id)
                    ->where('type', 'customer')
                    ->where('created_at', '<=', $endOfMonth)
                    ->count();
                $totalContactsData[] = $totalContacts;

                $newContacts = Contact::fromTenant($this->tenant_subdomain)
                    ->where('tenant_id', $this->tenant_id)
                    ->where('type', 'customer')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();
                $newContactsData[] = $newContacts;

                $totalLeads = Contact::fromTenant($this->tenant_subdomain)
                    ->where('tenant_id', $this->tenant_id)
                    ->where('type', 'lead')
                    ->where('created_at', '<=', $endOfMonth)
                    ->count();
                $totalLeadsData[] = $totalLeads;

                $newLeads = Contact::fromTenant($this->tenant_subdomain)
                    ->where('tenant_id', $this->tenant_id)
                    ->where('type', 'lead')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();
                $newLeadsData[] = $newLeads;
            }

            return [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => t('total_contacts'),
                        'data' => $totalContactsData,
                        'borderColor' => '#4F46E5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => '#4F46E5',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => t('new_contacts'),
                        'data' => $newContactsData,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => '#10B981',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => t('total_leads'),
                        'data' => $totalLeadsData,
                        'borderColor' => '#F59E0B',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => '#F59E0B',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => t('new_leads'),
                        'data' => $newLeadsData,
                        'borderColor' => '#EF4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => '#EF4444',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
    }

    private function getCampaignStatisticsData()
    {
        try {
            $this->tenant_id = tenant_id();

            $months = [];
            $totalCampaignsData = [];
            $sentCampaignsData = [];
            $deliveredCampaignsData = [];
            $readCampaignsData = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->format('M Y');
                $startOfMonth = $date->startOfMonth()->copy();
                $endOfMonth = $date->endOfMonth()->copy();

                $totalCampaigns = Campaign::where('tenant_id', $this->tenant_id)
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();
                $totalCampaignsData[] = $totalCampaigns;

                $sentCampaigns = Campaign::where('tenant_id', $this->tenant_id)
                    ->where('is_sent', 1)
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();
                $sentCampaignsData[] = $sentCampaigns;

                $deliveryStats = CampaignDetail::join('campaigns', 'campaign_details.campaign_id', '=', 'campaigns.id')
                    ->where('campaigns.tenant_id', $this->tenant_id)
                    ->where('campaign_details.tenant_id', $this->tenant_id)
                    ->whereBetween('campaigns.created_at', [$startOfMonth, $endOfMonth])
                    ->selectRaw('
                        COUNT(CASE WHEN campaign_details.message_status = "delivered" THEN 1 END) as delivered_count,
                        COUNT(CASE WHEN campaign_details.message_status = "read" THEN 1 END) as read_count
                    ')
                    ->first();

                $deliveredCampaignsData[] = $deliveryStats->delivered_count ?? 0;
                $readCampaignsData[] = $deliveryStats->read_count ?? 0;
            }

            return [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => t('campaigns_created'),
                        'data' => $totalCampaignsData,
                        'borderColor' => '#6366F1',
                        'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 4,
                        'pointBackgroundColor' => '#6366F1',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => t('campaigns_sent'),
                        'data' => $sentCampaignsData,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 4,
                        'pointBackgroundColor' => '#10B981',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => t('messages_delivered'),
                        'data' => $deliveredCampaignsData,
                        'borderColor' => '#F59E0B',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 4,
                        'pointBackgroundColor' => '#F59E0B',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => t('messages_read'),
                        'data' => $readCampaignsData,
                        'borderColor' => '#8B5CF6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'borderWidth' => 2,
                        'pointRadius' => 4,
                        'pointBackgroundColor' => '#8B5CF6',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            // Return default data on error
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
    }

    public function refreshDashboardData()
    {
        if ($this->currentTenant) {
            $this->updateFeatureUsageCounts();
            $this->clearTenantCache();
            $this->clearFeatureUsageCache();

            // Force refresh by loading fresh data
            $this->loadCachedAppSettings();
            $this->loadCachedSubscriptionData();
            $this->loadCachedUsageStatistics();
            $this->loadCachedChartData();

            $this->dispatch('chartDataUpdated');

            $this->notify([
                'type' => 'success',
                'message' => t('dashboard_data_refreshed'),
            ]);
        }
    }

    private function clearFeatureUsageCache()
    {
        if ($this->activeSubscription) {
            $featureUsageCacheKey = "feature_usage_tenant_{$this->currentTenant->id}_subscription_{$this->activeSubscription->id}";
            TenantCache::forget($featureUsageCacheKey);

            $features = ['staff', 'campaigns', 'contacts', 'conversations', 'template_bots', 'message_bots', 'ai_prompts', 'canned_replies'];
            foreach ($features as $feature) {
                TenantCache::forget("usage_{$feature}_tenant_{$this->currentTenant->id}");
            }
        }
    }

    private function loadCachedAppSettings()
    {
        $this->appName = $this->settings['system.site_name'] ?? 'Whatsmark-SaaS';
    }

    private function loadCachedSubscriptionData()
    {
        $cacheKey = $this->getCacheKey('subscription');

        $subscriptionData = TenantCache::remember($cacheKey, 300, function () {
            $activeSubscription = Subscription::where('tenant_id', $this->currentTenant->id)
                ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL])
                ->with(['plan', 'plan.features'])
                ->latest()
                ->first();

            $planName = 'Free';
            $nextBillingDate = null;
            $daysUntilBilling = null;

            if ($activeSubscription) {
                $planName = $activeSubscription->plan->name ?? t('unknown_plan');

                if ($activeSubscription->status === Subscription::STATUS_TRIAL && $activeSubscription->trial_ends_at) {
                    $nextBillingDate = $activeSubscription->trial_ends_at->format('Y-m-d');
                    $daysUntilBilling = round(Carbon::now()->diffInDays($activeSubscription->trial_ends_at, false));
                } elseif ($activeSubscription->status === Subscription::STATUS_ACTIVE && $activeSubscription->current_period_ends_at) {
                    $nextBillingDate = $activeSubscription->current_period_ends_at->format('Y-m-d');
                    $daysUntilBilling = round(Carbon::now()->diffInDays($activeSubscription->current_period_ends_at, false));
                }
            }

            return [
                'subscription' => $activeSubscription,
                'plan_name' => $planName,
                'next_billing' => $nextBillingDate,
                'days_until_billing' => $daysUntilBilling,
            ];
        }, ['dashboard', 'subscription', 'billing']);

        $this->activeSubscription = $subscriptionData['subscription'];
        $this->planName = $subscriptionData['plan_name'];
        $this->nextBillingDate = $subscriptionData['next_billing'];
        $this->daysUntilBilling = $subscriptionData['days_until_billing'];
    }

    private function loadCachedUsageStatistics()
    {
        $cacheKey = $this->getCacheKey('usage_stats');

        $usageStats = TenantCache::remember($cacheKey, 300, function () {
            return $this->getUsageStatistics($this->activeSubscription);
        }, ['dashboard', 'usage', 'statistics']);

        if ($usageStats) {
            $this->teamMemberCount = $usageStats['staff']['current'] ?? 0;
            $this->teamMemberLimit = $usageStats['staff']['limit'] ?? 1;
            $this->totalCampaigns = $usageStats['campaigns']['current'] ?? 0;
            $this->campaignLimit = $usageStats['campaigns']['limit'] ?? 5;
            $this->totalContacts = $usageStats['contacts']['current'] ?? 0;
            $this->contactLimit = $usageStats['contacts']['limit'] ?? 100;
            $this->totalConversations = $usageStats['conversations']['current'] ?? 0;
            $this->conversationLimit = $usageStats['conversations']['limit'] ?? 1000;
            $this->totalTemplateBots = $usageStats['template_bots']['current'] ?? 0;
            $this->templateBotLimit = $usageStats['template_bots']['limit'] ?? 3;
            $this->totalMessageBots = $usageStats['message_bots']['current'] ?? 0;
            $this->messageBotLimit = $usageStats['message_bots']['limit'] ?? 3;
            $this->totalAiPrompts = $usageStats['ai_prompts']['current'] ?? 0;
            $this->aiPromptLimit = $usageStats['ai_prompts']['limit'] ?? 5;
            $this->totalCannedReplies = $usageStats['canned_replies']['current'] ?? 0;
            $this->cannedReplyLimit = $usageStats['canned_replies']['limit'] ?? 5;

            // flow limit
            $this->totalFlowsLimit = $usageStats['bot_flow']['current'] ?? 0;
            $this->totalFlowsUsage = $usageStats['bot_flow']['limit'] ?? 0;
        }
    }

    private function loadCachedChartData()
    {
        $cacheKey = $this->getCacheKey('chart_data');

        $chartData = TenantCache::remember($cacheKey, 300, function () {
            return $this->getChartData();
        });

        if ($chartData) {
            $this->contactSourcesData = json_encode($chartData['contact_sources'] ?? ['labels' => ['No Data'], 'data' => [1]]);
            $this->weeklyMessageData = json_encode($chartData['weekly_messages'] ?? [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'datasets' => [
                    [
                        'label' => t('messages_sent'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    [
                        'label' => t('delivered'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    ],
                ],
            ]);
            $this->audienceGrowthData = $chartData['audience_growth'] ?? ['labels' => [], 'datasets' => []];
            $this->campaignStatisticsData = $chartData['campaign_statistics'] ?? ['labels' => [], 'datasets' => []];
        } else {
            $this->contactSourcesData = json_encode(['labels' => ['No Data'], 'data' => [1]]);
            $this->weeklyMessageData = json_encode([
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'datasets' => [
                    [
                        'label' => t('messages_sent'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    [
                        'label' => t('delivered'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    ],
                ],
            ]);
            $this->audienceGrowthData = ['labels' => [], 'datasets' => []];
            $this->campaignStatisticsData = ['labels' => [], 'datasets' => []];
        }
    }

    public function clearTenantCache()
    {
        $cacheKeys = [
            $this->getCacheKey('main'),
            $this->getCacheKey('usage_stats'),
            $this->getCacheKey('chart_data'),
            $this->getCacheKey('subscription'),
            $this->getCacheKey('feature_usage'),
            $this->getCacheKey('app_settings'),
        ];

        foreach ($cacheKeys as $key) {
            TenantCache::forget($key);
        }

        $chartCacheKeys = [
            "dashboard_weekly_messages_tenant_{$this->currentTenant->id}",
            "dashboard_contact_sources_tenant_{$this->currentTenant->id}",
            "dashboard_audience_growth_tenant_{$this->currentTenant->id}",
            "dashboard_campaign_statistics_tenant_{$this->currentTenant->id}",
        ];

        foreach ($chartCacheKeys as $key) {
            TenantCache::forget($key);
        }

        $usageCacheKeys = [
            "tenant_{$this->currentTenant->id}",
            "tenant_{$this->currentTenant->id}_contacts_count",
            "tenant_{$this->currentTenant->id}_campaigns_count",
            "tenant_{$this->currentTenant->id}_staff_count",
            "tenant_{$this->currentTenant->id}_conversations_count",
            "tenant_{$this->currentTenant->id}_template_bots_count",
            "tenant_{$this->currentTenant->id}_message_bots_count",
            "tenant_{$this->currentTenant->id}_ai_prompts_count",
            "tenant_{$this->currentTenant->id}_canned_replies_count",
        ];

        foreach ($usageCacheKeys as $key) {
            TenantCache::forget($key);
        }

        SubscriptionCache::clearCache($this->currentTenant->id);
    }

    public function redirectToSubscriptions()
    {
        return redirect()->to(tenant_route('tenant.subscription'));
    }

    private function updateFeatureUsageCounts()
    {
        if (! $this->activeSubscription) {
            return;
        }

        try {
            if (! $this->tenant_id) {
                $this->tenant_id = $this->currentTenant->id;
            }
            if (! $this->tenant_subdomain) {
                $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
            }

            if ($this->tenant_subdomain) {
                $actualContactCount = Contact::fromTenant($this->tenant_subdomain)->count();

                \App\Models\Tenant\FeatureUsage::updateOrCreate(
                    [
                        'tenant_id' => $this->currentTenant->id,
                        'subscription_id' => $this->activeSubscription->id,
                        'feature_slug' => 'contacts',
                    ],
                    [
                        'used' => $actualContactCount,
                        'limit_value' => $this->activeSubscription->plan->features()
                            ->where('slug', 'contacts')
                            ->first()?->value ?? 100,
                    ]
                );
            }

            if ($this->tenant_subdomain) {
                $actualConversationCount = ChatMessage::fromTenant($this->tenant_subdomain)->count();

                \App\Models\Tenant\FeatureUsage::updateOrCreate(
                    [
                        'tenant_id' => $this->currentTenant->id,
                        'subscription_id' => $this->activeSubscription->id,
                        'feature_slug' => 'conversations',
                    ],
                    [
                        'used' => $actualConversationCount,
                        'limit_value' => $this->activeSubscription->plan->features()
                            ->where('slug', 'conversations')
                            ->first()?->value ?? 1000,
                    ]
                );
            }

            $featureCounts = [
                'campaigns' => Campaign::where('tenant_id', $this->currentTenant->id)->count(),
                'staff' => User::where('tenant_id', $this->currentTenant->id)->where('is_admin', false)->count(),
                'template_bots' => TemplateBot::where('tenant_id', $this->currentTenant->id)->count(),
                'message_bots' => MessageBot::where('tenant_id', $this->currentTenant->id)->count(),
                'ai_prompts' => AiPrompt::where('tenant_id', $this->currentTenant->id)->count(),
                'canned_replies' => CannedReply::withoutGlobalScope('tenant')
                    ->where('tenant_id', $this->currentTenant->id)->count(),
            ];

            foreach ($featureCounts as $featureSlug => $actualCount) {
                \App\Models\Tenant\FeatureUsage::updateOrCreate(
                    [
                        'tenant_id' => $this->currentTenant->id,
                        'subscription_id' => $this->activeSubscription->id,
                        'feature_slug' => $featureSlug,
                    ],
                    [
                        'used' => $actualCount,
                        'limit_value' => $this->activeSubscription->plan->features()
                            ->where('slug', $featureSlug)
                            ->first()?->value ?? -1,
                    ]
                );
            }
        } catch (\Exception $e) {
            // Silently continue on error
        }
    }

    public function updateTemplateSettings()
    {
        save_tenant_setting('whats-mark', 'is_templates_changed', 0);
    }

    public function render()
    {
        return view('livewire.tenant.dashboard');
    }
}
