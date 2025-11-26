<?php

namespace App\Livewire\Admin;

use App\Enum\SubscriptionStatus;
use App\Facades\AdminCache;
use Carbon\Carbon;
use Corbital\Installer\Classes\EnvironmentManager;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // Admin user data
    public $adminName = '';

    public $baseCurrency = '';

    public $lastUpdated = '';

    // Statistics data
    public $activeSubscriptions = 0;

    public $totalEarnings = 0;

    public $totalClients = 0;

    public $totalCampaigns = 0;

    // Month-over-month changes
    public $activeSubscriptionsChange = 0;

    public $totalEarningsChange = 0;

    public $totalClientsChange = 0;

    public $totalCampaignsChange = 0;

    // Chart data
    public $earningsData = [];

    public $planDistributionData = [];

    public $currencyFormat;

    /**
     * Format current time according to admin settings
     *
     * @param  \Carbon\Carbon  $dateTime
     * @return string
     */
    private function formatDateTimeWithAdminSettings($dateTime)
    {
        // Get admin settings
        $settings = get_batch_settings([
            'system.timezone',
            'system.date_format',
            'system.time_format',
        ]);

        // Get settings with defaults
        $timezone = $settings['system.timezone'] ?? config('app.timezone');
        $dateFormat = $settings['system.date_format'] ?? config('app.date_format');
        $timeFormat = $settings['system.time_format'] == '12' ? 'h:i A' : 'H:i';

        // Format the date and time
        return Carbon::parse($dateTime)
            ->setTimezone($timezone)
            ->format("$dateFormat $timeFormat");
    }

    public function mount()
    {
        // Get authenticated admin user information
        $user = auth()->user();
        $this->adminName = $user ? trim($user->firstname.' '.$user->lastname) : 'Admin';

        // Get base currency symbol
        $currency = get_base_currency();
        $this->baseCurrency = $currency ? $currency->symbol : '$';

        // Set initial last updated time using admin settings for date and time format
        $this->lastUpdated = $this->formatDateTimeWithAdminSettings(now());

        $this->loadDashboardData();
        $this->initChartData();

        $this->currencyFormat = $currency ? $currency->format : 'after_amount';
    }

    public function loadDashboardData()
    {
        try {
            // Cache dashboard data for 5 minutes using AdminCache
            $stats = AdminCache::remember('dashboard_stats', function () {
                // Gather all basic stats in a more efficient way
                $basicStats = $this->getBasicStats();
                $changeStats = $this->getChangeStats();

                return array_merge($basicStats, $changeStats);
            }, ['admin_dashboard'], 300); // 5 minutes TTL

            // Validate stats array structure
            if (! is_array($stats)) {
                $stats = $this->getFallbackStats();
            }

            // Assign properties from cached data with safe array access
            $this->activeSubscriptions = $stats['active_subscriptions'] ?? 0;
            $this->totalEarnings = $stats['total_earnings'] ?? 0;
            $this->totalClients = $stats['total_clients'] ?? 0;
            $this->totalCampaigns = $stats['total_campaigns'] ?? 0;
            $this->activeSubscriptionsChange = $stats['active_subscriptions_change'] ?? 0;
            $this->totalEarningsChange = $stats['total_earnings_change'] ?? 0;
            $this->totalClientsChange = $stats['total_clients_change'] ?? 0;
            $this->totalCampaignsChange = $stats['total_campaigns_change'] ?? 0;
        } catch (\Exception $e) {
            app_log('Dashboard: Failed to load dashboard data', 'error', $e, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Set fallback values
            $this->setFallbackValues();
        }
    }

    /**
     * Gather all basic stats in a more efficient way
     *
     * @return array
     */
    private function getBasicStats()
    {
        try {
            return [
                'active_subscriptions' => $this->safeDbQuery(function () {
                    return DB::table('subscriptions')->where('status', SubscriptionStatus::ACTIVE)->count();
                }, 0),
                'total_earnings' => $this->safeDbQuery(function () {
                    return DB::table('transactions')->where('status', 'success')->sum('amount');
                }, 0),
                'total_clients' => $this->safeDbQuery(function () {
                    return DB::table('tenants')->count();
                }, 0),
                'total_campaigns' => $this->safeDbQuery(function () {
                    return DB::table('campaigns')->count();
                }, 0),
            ];
        } catch (\Exception $e) {
            app_log('Dashboard: Failed to get basic stats', 'error', $e, ['error' => $e->getMessage()]);

            return [
                'active_subscriptions' => 0,
                'total_earnings' => 0,
                'total_clients' => 0,
                'total_campaigns' => 0,
            ];
        }
    }

    /**
     * Safely execute a database query with fallback
     */
    private function safeDbQuery(callable $query, $default = 0)
    {
        try {
            return $query() ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get fallback stats structure
     *
     * @return array
     */
    private function getFallbackStats()
    {
        return [
            'active_subscriptions' => 0,
            'total_earnings' => 0,
            'total_clients' => 0,
            'total_campaigns' => 0,
            'active_subscriptions_change' => 0,
            'total_earnings_change' => 0,
            'total_clients_change' => 0,
            'total_campaigns_change' => 0,
        ];
    }

    /**
     * Set fallback values for all properties
     */
    private function setFallbackValues()
    {
        $this->activeSubscriptions = 0;
        $this->totalEarnings = 0;
        $this->totalClients = 0;
        $this->totalCampaigns = 0;
        $this->activeSubscriptionsChange = 0;
        $this->totalEarningsChange = 0;
        $this->totalClientsChange = 0;
        $this->totalCampaignsChange = 0;
    }

    /**
     * Gather all change stats in a more efficient way
     *
     * @return array
     */
    private function getChangeStats()
    {
        try {
            return [
                'active_subscriptions_change' => $this->safeDbQuery(function () {
                    return $this->getActiveSubscriptionsChange();
                }, 0),
                'total_earnings_change' => $this->safeDbQuery(function () {
                    return $this->getTotalEarningsChange();
                }, 0),
                'total_clients_change' => $this->safeDbQuery(function () {
                    return $this->getTotalClientsChange();
                }, 0),
                'total_campaigns_change' => $this->safeDbQuery(function () {
                    return $this->getTotalCampaignsChange();
                }, 0),
            ];
        } catch (\Exception $e) {
            app_log('Dashboard: Failed to get change stats', 'error', $e, ['error' => $e->getMessage()]);

            return [
                'active_subscriptions_change' => 0,
                'total_earnings_change' => 0,
                'total_clients_change' => 0,
                'total_campaigns_change' => 0,
            ];
        }
    }

    /**
     * Refresh all dashboard data at once
     */
    public function refreshDashboardData()
    {
        // Clear all dashboard caches using AdminCache
        AdminCache::invalidateTag('admin_dashboard');

        // Reload data
        $this->loadDashboardData();

        // Refresh chart data
        $this->initChartData();

        // Update last refreshed time using admin settings for date and time format
        $this->lastUpdated = $this->formatDateTimeWithAdminSettings(now());

        // Dispatch event to refresh charts
        $this->dispatch('chartDataUpdated');
    }

    private function getActiveSubscriptionsChange()
    {
        try {
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Get both current and last month data in a single query
            $subscriptionCounts = DB::table('subscriptions')
                ->where('status', SubscriptionStatus::ACTIVE)
                ->select(
                    DB::raw("SUM(CASE WHEN created_at >= '{$currentMonthStart->toDateTimeString()}' AND created_at <= '{$currentMonthEnd->toDateTimeString()}' THEN 1 ELSE 0 END) as current_month"),
                    DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart->toDateTimeString()}' AND created_at <= '{$lastMonthEnd->toDateTimeString()}' THEN 1 ELSE 0 END) as last_month")
                )
                ->first();

            $currentMonth = $subscriptionCounts->current_month ?? 0;
            $lastMonth = $subscriptionCounts->last_month ?? 0;

            return $currentMonth - $lastMonth;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalEarningsChange()
    {
        try {
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Get both current and last month data in a single query
            $earningsSummary = DB::table('transactions')
                ->where('status', 'success')
                ->select(
                    DB::raw("SUM(CASE WHEN created_at >= '{$currentMonthStart->toDateTimeString()}' AND created_at <= '{$currentMonthEnd->toDateTimeString()}' THEN amount ELSE 0 END) as current_month"),
                    DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart->toDateTimeString()}' AND created_at <= '{$lastMonthEnd->toDateTimeString()}' THEN amount ELSE 0 END) as last_month")
                )
                ->first();

            $currentMonth = $earningsSummary->current_month ?? 0;
            $lastMonth = $earningsSummary->last_month ?? 0;

            return $currentMonth - $lastMonth;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalClientsChange()
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Get both current and last month data in a single query
        $clientCounts = DB::table('tenants')
            ->select(
                DB::raw("SUM(CASE WHEN created_at >= '{$currentMonthStart->toDateTimeString()}' AND created_at <= '{$currentMonthEnd->toDateTimeString()}' THEN 1 ELSE 0 END) as current_month"),
                DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart->toDateTimeString()}' AND created_at <= '{$lastMonthEnd->toDateTimeString()}' THEN 1 ELSE 0 END) as last_month")
            )
            ->first();

        $currentMonth = $clientCounts->current_month ?? 0;
        $lastMonth = $clientCounts->last_month ?? 0;

        return $currentMonth - $lastMonth;
    }

    private function getTotalCampaignsChange()
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Get both current and last month data in a single query
        $campaignCounts = DB::table('campaigns')
            ->select(
                DB::raw("SUM(CASE WHEN created_at >= '{$currentMonthStart->toDateTimeString()}' AND created_at <= '{$currentMonthEnd->toDateTimeString()}' THEN 1 ELSE 0 END) as current_month"),
                DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart->toDateTimeString()}' AND created_at <= '{$lastMonthEnd->toDateTimeString()}' THEN 1 ELSE 0 END) as last_month")
            )
            ->first();

        $currentMonth = $campaignCounts->current_month ?? 0;
        $lastMonth = $campaignCounts->last_month ?? 0;

        return $currentMonth - $lastMonth;
    }

    public function updateEnv()
    {
        $environmentManager = new EnvironmentManager;
        if (! env('APP_PREVIOUS_KEYS')) {
            $environmentManager->saveEnv([
                'APP_PREVIOUS_KEYS' => env('APP_KEY'),
            ]);
        }

        $this->notify([
            'type' => 'success',
            'message' => t('env_updated_successfully'),
        ]);

        $this->dispatch('reload-page');
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }

    /**
     * Initialize chart data
     */
    private function initChartData()
    {
        // Get real earnings data from the database
        $this->earningsData = $this->getEarningsChartData();

        // Get real plan distribution data from database
        $this->planDistributionData = $this->getPlanDistributionData();
    }

    /**
     * Get earnings chart data for the last 12 months
     *
     * @return array
     */
    private function getEarningsChartData()
    {
        // Cache key for earnings chart data
        $cacheKey = 'admin_dashboard_earnings_chart';
        $cacheTime = 300; // 5 minutes (same as other dashboard stats)

        return AdminCache::remember($cacheKey, function () {
            // Get the last 12 months (including the current month)
            $months = collect();
            for ($i = 11; $i >= 0; $i--) {
                $months->push(Carbon::now()->subMonths($i));
            }

            // Format the month labels
            $labels = $months->map(function ($month) {
                return $month->format('M');
            })->toArray();

            // Get monthly earnings data
            $earningsData = $this->getMonthlyEarningsData($months);

            // Get monthly new subscriptions data
            $subscriptionsData = $this->getMonthlySubscriptionsData($months);

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Earnings',
                        'data' => $earningsData,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    [
                        'label' => 'New Subscriptions',
                        'data' => $subscriptionsData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    ],
                ],
            ];
        });
    }

    /**
     * Get monthly earnings data for the last 12 months
     *
     * @param  \Illuminate\Support\Collection  $months
     * @return array
     */
    private function getMonthlyEarningsData($months)
    {
        // Get monthly earnings for the last 12 months from transactions table
        $monthlyEarnings = DB::table('transactions')
            ->where('status', 'success')
            ->where('created_at', '>=', Carbon::now()->subMonths(12)->startOfMonth())
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                // Create a key in the format "Y-m" for easy lookup
                return ["{$item->year}-{$item->month}" => $item->total];
            });

        // Map the data to the corresponding months
        return $months->map(function ($month) use ($monthlyEarnings) {
            $key = $month->format('Y-n');

            return $monthlyEarnings[$key] ?? 0;
        })->toArray();
    }

    /**
     * Get monthly new subscriptions data for the last 12 months
     *
     * @param  \Illuminate\Support\Collection  $months
     * @return array
     */
    private function getMonthlySubscriptionsData($months)
    {
        // Get monthly new subscriptions for the last 12 months in a single query
        $monthlySubscriptions = DB::table('subscriptions')
            ->where('created_at', '>=', Carbon::now()->subMonths(12)->startOfMonth())
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                // Create a key in the format "Y-m" for easy lookup
                return ["{$item->year}-{$item->month}" => $item->total];
            });

        // Map the data to the corresponding months
        return $months->map(function ($month) use ($monthlySubscriptions) {
            $key = $month->format('Y-n');

            return $monthlySubscriptions[$key] ?? 0;
        })->toArray();
    }

    /**
     * Get plan distribution data from the database
     *
     * @return array
     */
    private function getPlanDistributionData()
    {
        // Use the same cache key prefix as other dashboard stats, but with a specific suffix for plan distribution
        $cacheKey = 'admin_dashboard_plan_distribution';
        $cacheTime = 300; // 5 minutes (same as other dashboard stats)

        // Get data from cache or generate if not available
        return AdminCache::remember($cacheKey, function () {
            // Default colors if plan colors are not set
            $defaultColors = [
                '#3b82f6', // Blue
                '#10b981', // Green
                '#8b5cf6', // Purple
                '#f59e0b', // Amber
                '#ef4444', // Red
                '#06b6d4', // Cyan
                '#6366f1', // Indigo
                '#a855f7', // Purple
                '#ec4899', // Pink
            ];

            // Get all active plans
            $plans = \App\Models\Plan::where('is_active', true)->get();

            // Get the count of active subscriptions for each plan in a single query
            $subscriptionCounts = DB::table('subscriptions')
                ->where('status', \App\Enum\SubscriptionStatus::ACTIVE)
                ->select('plan_id', DB::raw('count(*) as count'))
                ->groupBy('plan_id')
                ->pluck('count', 'plan_id')
                ->toArray();

            $labels = [];
            $data = [];
            $backgroundColor = [];
            $borderColor = [];
            $hoverBackgroundColor = [];
            $plansData = []; // New array for card-based layout

            // Process the data
            foreach ($plans as $index => $plan) {
                $count = $subscriptionCounts[$plan->id] ?? 0;

                // Only include plans with at least one active subscription
                if ($count > 0) {
                    $labels[] = $plan->name;
                    $data[] = $count;

                    // Use plan color if available, otherwise use default color
                    $color = $plan->color ? $plan->color : $defaultColors[$index % count($defaultColors)];
                    $backgroundColor[] = $color;
                    $borderColor[] = $this->adjustColorOpacity($color, 0.8);
                    $hoverBackgroundColor[] = $this->adjustColorBrightness($color, -10); // Slightly darker on hover

                    // Add plan to the new card-based format
                    $plansData[] = [
                        'name' => $plan->name,
                        'count' => $count,
                        'price' => get_base_currency()->format($plan->price),
                        'color' => $color,
                    ];
                }
            }

            // Sort plans by subscription count (descending)
            usort($plansData, function ($a, $b) {
                return $b['count'] - $a['count'];
            });

            // If no active subscriptions found, return dummy data
            if (empty($data)) {
                return [
                    'labels' => ['No Active Subscriptions'],
                    'data' => [1],
                    'backgroundColor' => ['#6b7280'], // Gray
                    'borderColor' => ['rgba(107, 114, 128, 0.8)'],
                    'hoverBackgroundColor' => ['#4b5563'],
                    'plans' => [], // Empty plans array for card layout
                ];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'hoverBackgroundColor' => $hoverBackgroundColor,
                'plans' => $plansData, // Add plans data for card layout
            ];
        });
    }

    /**
     * Adjust color opacity
     *
     * @param  string  $color  Hex color code
     * @param  float  $opacity  Opacity value between 0 and 1
     * @return string RGBA color string
     */
    private function adjustColorOpacity($color, $opacity)
    {
        // Convert hex to rgb
        $hex = str_replace('#', '', $color);

        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return "rgba($r, $g, $b, $opacity)";
    }

    /**
     * Adjust color brightness
     *
     * @param  string  $color  Hex color code
     * @param  int  $percent  Amount to brighten or darken (-100 to 100)
     * @return string Hex color code
     */
    private function adjustColorBrightness($color, $percent)
    {
        $hex = str_replace('#', '', $color);

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $rgb = [];
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = hexdec(substr($hex, $i * 2, 2));
            $rgb[$i] = round($rgb[$i] * (100 + $percent) / 100);
            $rgb[$i] = min(255, max(0, $rgb[$i]));
            $rgb[$i] = sprintf('%02x', $rgb[$i]);
        }

        return '#'.implode('', $rgb);
    }
}
