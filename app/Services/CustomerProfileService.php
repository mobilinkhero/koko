<?php

namespace App\Services;

use App\Models\Tenant\CustomerProfile;
use App\Models\Tenant\EcommerceOrder;
use App\Models\Tenant\CustomerInteraction;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Customer Profile Service
 * Manages customer behavior analysis, preferences, and personalization data
 */
class CustomerProfileService
{
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Get or create comprehensive customer profile
     */
    public function getOrCreateProfile($contact): CustomerProfile
    {
        $profile = CustomerProfile::where('tenant_id', $this->tenantId)
            ->where('contact_id', $contact->id)
            ->first();

        if (!$profile) {
            $profile = $this->createInitialProfile($contact);
        } else {
            $profile = $this->updateProfileMetrics($profile);
        }

        return $profile;
    }

    /**
     * Create initial customer profile with behavioral analysis
     */
    protected function createInitialProfile($contact): CustomerProfile
    {
        // Analyze contact data to determine initial profile
        $initialTier = $this->determineInitialTier($contact);
        $predictedPreferences = $this->predictInitialPreferences($contact);

        return CustomerProfile::create([
            'tenant_id' => $this->tenantId,
            'contact_id' => $contact->id,
            'full_name' => trim($contact->firstname . ' ' . $contact->lastname),
            'phone' => $contact->phone,
            'email' => $contact->email,
            'tier' => $initialTier,
            'preferences' => $predictedPreferences,
            'total_orders' => 0,
            'total_spent' => 0,
            'average_order_value' => 0,
            'favorite_categories' => [],
            'last_interaction_at' => now(),
            'behavioral_score' => 50, // Start neutral
            'price_sensitivity' => 'medium',
            'interaction_frequency' => 'new',
            'preferred_language' => 'english',
            'seasonal_patterns' => [],
            'purchase_timing' => [],
            'communication_preferences' => [
                'style' => 'friendly',
                'detail_level' => 'moderate',
                'response_speed' => 'normal'
            ]
        ]);
    }

    /**
     * Update profile metrics based on recent activity
     */
    protected function updateProfileMetrics(CustomerProfile $profile): CustomerProfile
    {
        $cacheKey = "profile_metrics_{$profile->id}";
        
        // Only update metrics once per hour to avoid excessive computation
        $lastUpdate = Cache::get($cacheKey);
        if ($lastUpdate && Carbon::parse($lastUpdate)->gt(now()->subHour())) {
            return $profile;
        }

        // Calculate updated metrics
        $orders = EcommerceOrder::where('tenant_id', $this->tenantId)
            ->where('contact_id', $profile->contact_id)
            ->get();

        $totalOrders = $orders->count();
        $totalSpent = $orders->sum('total_amount');
        $avgOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        // Analyze purchase patterns
        $categoryFrequency = $this->analyzeCategoryPreferences($orders);
        $behavioralScore = $this->calculateBehavioralScore($profile, $orders);
        $seasonalPatterns = $this->analyzeSeasonalPatterns($orders);
        $priceSensitivity = $this->analyzePriceSensitivity($orders);

        // Determine tier based on spending and engagement
        $newTier = $this->calculateCustomerTier($totalSpent, $totalOrders, $behavioralScore);

        $profile->update([
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => $avgOrderValue,
            'favorite_categories' => array_keys($categoryFrequency),
            'tier' => $newTier,
            'behavioral_score' => $behavioralScore,
            'seasonal_patterns' => $seasonalPatterns,
            'price_sensitivity' => $priceSensitivity,
            'last_interaction_at' => now()
        ]);

        Cache::put($cacheKey, now()->toISOString(), 3600); // Cache for 1 hour

        return $profile;
    }

    /**
     * Analyze customer's category preferences based on purchase history
     */
    protected function analyzeCategoryPreferences($orders): array
    {
        $categoryCount = [];
        
        foreach ($orders as $order) {
            $items = json_decode($order->order_items, true) ?? [];
            foreach ($items as $item) {
                $product = \App\Models\Tenant\Product::find($item['product_id']);
                if ($product) {
                    $category = $product->category;
                    $categoryCount[$category] = ($categoryCount[$category] ?? 0) + $item['quantity'];
                }
            }
        }

        // Sort by frequency and return top categories
        arsort($categoryCount);
        return array_slice($categoryCount, 0, 5, true);
    }

    /**
     * Calculate behavioral score based on engagement patterns
     */
    protected function calculateBehavioralScore($profile, $orders): int
    {
        $score = 50; // Base score

        // Order frequency bonus
        $daysSinceFirstOrder = $orders->min('created_at') ? 
            Carbon::parse($orders->min('created_at'))->diffInDays(now()) : 1;
        $orderFrequency = $orders->count() / max($daysSinceFirstOrder, 1) * 365; // Orders per year
        
        if ($orderFrequency > 12) $score += 20; // Very frequent
        elseif ($orderFrequency > 6) $score += 15; // Frequent
        elseif ($orderFrequency > 3) $score += 10; // Regular
        elseif ($orderFrequency > 1) $score += 5; // Occasional

        // Spending level bonus
        $totalSpent = $orders->sum('total_amount');
        if ($totalSpent > 1000) $score += 20;
        elseif ($totalSpent > 500) $score += 15;
        elseif ($totalSpent > 200) $score += 10;
        elseif ($totalSpent > 50) $score += 5;

        // Recent activity bonus
        $recentOrders = $orders->where('created_at', '>', now()->subDays(30))->count();
        if ($recentOrders > 0) $score += 10;

        // Order completion rate (assume high for now)
        $score += 10;

        return min(100, max(0, $score));
    }

    /**
     * Determine customer tier based on multiple factors
     */
    protected function calculateCustomerTier($totalSpent, $totalOrders, $behavioralScore): string
    {
        if ($totalSpent >= 2000 && $totalOrders >= 10 && $behavioralScore >= 80) {
            return 'vip';
        } elseif ($totalSpent >= 500 && $totalOrders >= 5 && $behavioralScore >= 70) {
            return 'premium';
        } elseif ($totalSpent >= 100 && $totalOrders >= 2 && $behavioralScore >= 60) {
            return 'regular';
        } else {
            return 'standard';
        }
    }

    /**
     * Analyze seasonal purchase patterns
     */
    protected function analyzeSeasonalPatterns($orders): array
    {
        $patterns = [];
        $monthlySpending = [];

        foreach ($orders as $order) {
            $month = Carbon::parse($order->created_at)->format('m');
            $monthlySpending[$month] = ($monthlySpending[$month] ?? 0) + $order->total_amount;
        }

        // Identify peak seasons
        if (!empty($monthlySpending)) {
            $avgSpending = array_sum($monthlySpending) / count($monthlySpending);
            foreach ($monthlySpending as $month => $spending) {
                if ($spending > $avgSpending * 1.5) {
                    $patterns[] = $this->getSeasonName($month);
                }
            }
        }

        return array_unique($patterns);
    }

    /**
     * Get season name from month
     */
    protected function getSeasonName($month): string
    {
        $month = (int)$month;
        if (in_array($month, [12, 1, 2])) return 'winter';
        if (in_array($month, [3, 4, 5])) return 'spring';
        if (in_array($month, [6, 7, 8])) return 'summer';
        return 'autumn';
    }

    /**
     * Analyze price sensitivity based on purchase patterns
     */
    protected function analyzePriceSensitivity($orders): string
    {
        if ($orders->isEmpty()) return 'medium';

        $avgOrderValue = $orders->avg('total_amount');
        $priceVariance = $this->calculatePriceVariance($orders);

        if ($avgOrderValue > 200 && $priceVariance < 0.3) {
            return 'low'; // Consistently buys expensive items
        } elseif ($avgOrderValue < 50 || $priceVariance > 0.7) {
            return 'high'; // Price-conscious or very variable spending
        } else {
            return 'medium';
        }
    }

    /**
     * Calculate price variance across orders
     */
    protected function calculatePriceVariance($orders): float
    {
        if ($orders->count() < 2) return 0;

        $amounts = $orders->pluck('total_amount')->toArray();
        $mean = array_sum($amounts) / count($amounts);
        
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $amounts)) / count($amounts);

        return $mean > 0 ? sqrt($variance) / $mean : 0; // Coefficient of variation
    }

    /**
     * Track customer interaction for behavioral analysis
     */
    public function trackInteraction($contact, string $type, array $data = []): void
    {
        CustomerInteraction::create([
            'tenant_id' => $this->tenantId,
            'contact_id' => $contact->id,
            'interaction_type' => $type,
            'interaction_data' => $data,
            'created_at' => now()
        ]);

        // Update last interaction timestamp
        $profile = $this->getOrCreateProfile($contact);
        $profile->update(['last_interaction_at' => now()]);
    }

    /**
     * Get personalized recommendations based on profile
     */
    public function getPersonalizedRecommendations($profile): array
    {
        // This would integrate with RecommendationEngineService
        // For now, return basic category-based recommendations
        $favoriteCategories = $profile->favorite_categories ?? [];
        
        if (empty($favoriteCategories)) {
            return [];
        }

        return \App\Models\Tenant\Product::where('tenant_id', $this->tenantId)
            ->whereIn('category', $favoriteCategories)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->limit(3)
            ->get()
            ->toArray();
    }
}
