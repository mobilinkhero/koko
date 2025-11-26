<?php

namespace App\Services;

use App\Models\Tenant\Product;
use App\Models\Tenant\EcommerceOrder;
use App\Models\Tenant\CustomerProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * AI-Powered Recommendation Engine
 * Provides personalized product recommendations using multiple algorithms:
 * - Collaborative filtering
 * - Content-based filtering  
 * - Behavioral pattern analysis
 * - Seasonal trend analysis
 * - Real-time contextual recommendations
 */
class RecommendationEngineService
{
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Get personalized recommendations for customer
     */
    public function getPersonalizedRecommendations($customerProfile, string $currentMessage = ''): array
    {
        $cacheKey = "recommendations_{$this->tenantId}_{$customerProfile->id}_" . md5($currentMessage);
        
        return Cache::remember($cacheKey, 1800, function() use ($customerProfile, $currentMessage) {
            $recommendations = [];

            // 1. Collaborative Filtering Recommendations
            $collaborativeRecs = $this->getCollaborativeRecommendations($customerProfile);
            
            // 2. Content-Based Recommendations
            $contentRecs = $this->getContentBasedRecommendations($customerProfile);
            
            // 3. Behavioral Pattern Recommendations
            $behavioralRecs = $this->getBehavioralRecommendations($customerProfile);
            
            // 4. Contextual Recommendations (based on current message)
            $contextualRecs = $this->getContextualRecommendations($customerProfile, $currentMessage);
            
            // 5. Seasonal/Trending Recommendations
            $seasonalRecs = $this->getSeasonalRecommendations($customerProfile);

            // Combine and score recommendations
            $allRecs = collect([
                ...$this->scoreRecommendations($collaborativeRecs, 'collaborative', 0.3),
                ...$this->scoreRecommendations($contentRecs, 'content_based', 0.25),
                ...$this->scoreRecommendations($behavioralRecs, 'behavioral', 0.2),
                ...$this->scoreRecommendations($contextualRecs, 'contextual', 0.15),
                ...$this->scoreRecommendations($seasonalRecs, 'seasonal', 0.1)
            ]);

            // Remove duplicates and sort by score
            $finalRecs = $allRecs->groupBy('product_id')
                ->map(function($group) {
                    $product = $group->first();
                    $product['total_score'] = $group->sum('score');
                    $product['recommendation_reasons'] = $group->pluck('reason')->unique()->toArray();
                    return $product;
                })
                ->sortByDesc('total_score')
                ->take(10)
                ->values()
                ->toArray();

            return $finalRecs;
        });
    }

    /**
     * Collaborative filtering: "Customers like you also bought"
     */
    protected function getCollaborativeRecommendations($customerProfile): array
    {
        if ($customerProfile->total_orders === 0) {
            return $this->getPopularProducts();
        }

        // Find customers with similar purchase patterns
        $customerCategories = $customerProfile->favorite_categories ?? [];
        if (empty($customerCategories)) {
            return [];
        }

        // Get customers who bought from similar categories
        $similarCustomers = CustomerProfile::where('tenant_id', $this->tenantId)
            ->where('id', '!=', $customerProfile->id)
            ->whereJsonOverlaps('favorite_categories', $customerCategories)
            ->limit(20)
            ->get();

        $recommendedProducts = [];
        foreach ($similarCustomers as $similarCustomer) {
            $orders = EcommerceOrder::where('tenant_id', $this->tenantId)
                ->where('contact_id', $similarCustomer->contact_id)
                ->get();

            foreach ($orders as $order) {
                $items = json_decode($order->order_items, true) ?? [];
                foreach ($items as $item) {
                    $productId = $item['product_id'];
                    if (!isset($recommendedProducts[$productId])) {
                        $product = Product::find($productId);
                        if ($product && $product->status === 'active' && $product->stock_quantity > 0) {
                            $recommendedProducts[$productId] = [
                                'product_id' => $productId,
                                'product' => $product,
                                'frequency' => 0,
                                'reason' => 'Customers with similar preferences bought this'
                            ];
                        }
                    }
                    if (isset($recommendedProducts[$productId])) {
                        $recommendedProducts[$productId]['frequency']++;
                    }
                }
            }
        }

        // Sort by frequency and return top products
        usort($recommendedProducts, fn($a, $b) => $b['frequency'] <=> $a['frequency']);
        return array_slice($recommendedProducts, 0, 5);
    }

    /**
     * Content-based filtering: Based on product attributes and customer preferences
     */
    protected function getContentBasedRecommendations($customerProfile): array
    {
        $favoriteCategories = $customerProfile->favorite_categories ?? [];
        if (empty($favoriteCategories)) {
            return [];
        }

        // Get products from favorite categories that customer hasn't bought
        $purchasedProductIds = $this->getCustomerPurchasedProductIds($customerProfile);
        
        $products = Product::where('tenant_id', $this->tenantId)
            ->whereIn('category', $favoriteCategories)
            ->whereNotIn('id', $purchasedProductIds)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->limit(10)
            ->get();

        return $products->map(function($product) use ($customerProfile) {
            return [
                'product_id' => $product->id,
                'product' => $product,
                'reason' => "Based on your interest in {$product->category}",
                'relevance_score' => $this->calculateContentRelevance($product, $customerProfile)
            ];
        })->sortByDesc('relevance_score')->take(5)->toArray();
    }

    /**
     * Behavioral pattern recommendations
     */
    protected function getBehavioralRecommendations($customerProfile): array
    {
        $recommendations = [];

        // Price range preferences
        $avgOrderValue = $customerProfile->average_order_value;
        if ($avgOrderValue > 0) {
            $priceMin = $avgOrderValue * 0.7;
            $priceMax = $avgOrderValue * 1.3;

            $products = Product::where('tenant_id', $this->tenantId)
                ->whereBetween('price', [$priceMin, $priceMax])
                ->where('status', 'active')
                ->where('stock_quantity', '>', 0)
                ->limit(5)
                ->get();

            foreach ($products as $product) {
                $recommendations[] = [
                    'product_id' => $product->id,
                    'product' => $product,
                    'reason' => 'Matches your typical spending range'
                ];
            }
        }

        // Tier-based recommendations
        if ($customerProfile->tier === 'vip' || $customerProfile->tier === 'premium') {
            $premiumProducts = Product::where('tenant_id', $this->tenantId)
                ->where('featured', true)
                ->where('price', '>', 100)
                ->where('status', 'active')
                ->where('stock_quantity', '>', 0)
                ->limit(3)
                ->get();

            foreach ($premiumProducts as $product) {
                $recommendations[] = [
                    'product_id' => $product->id,
                    'product' => $product,
                    'reason' => 'Premium selection for valued customers'
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Contextual recommendations based on current message
     */
    protected function getContextualRecommendations($customerProfile, string $message): array
    {
        if (empty($message)) return [];

        $recommendations = [];
        $messageLower = strtolower($message);

        // Extract product/category mentions from message
        $products = Product::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->get();

        foreach ($products as $product) {
            $nameMatch = str_contains($messageLower, strtolower($product->name));
            $categoryMatch = str_contains($messageLower, strtolower($product->category));
            $descriptionMatch = str_contains($messageLower, strtolower($product->description ?? ''));

            if ($nameMatch || $categoryMatch || $descriptionMatch) {
                $relevanceScore = 0;
                $reason = 'Related to your inquiry';

                if ($nameMatch) {
                    $relevanceScore += 10;
                    $reason = 'Exact match for your search';
                } elseif ($categoryMatch) {
                    $relevanceScore += 7;
                    $reason = 'From the category you mentioned';
                } elseif ($descriptionMatch) {
                    $relevanceScore += 5;
                    $reason = 'Matches your description';
                }

                $recommendations[] = [
                    'product_id' => $product->id,
                    'product' => $product,
                    'reason' => $reason,
                    'relevance_score' => $relevanceScore
                ];
            }
        }

        // Sort by relevance and return top matches
        usort($recommendations, fn($a, $b) => $b['relevance_score'] <=> $a['relevance_score']);
        return array_slice($recommendations, 0, 5);
    }

    /**
     * Seasonal and trending recommendations
     */
    protected function getSeasonalRecommendations($customerProfile): array
    {
        $currentSeason = $this->getCurrentSeason();
        $recommendations = [];

        // Products popular in current season
        $seasonalProducts = Product::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->where(function($query) use ($currentSeason) {
                $query->whereJsonContains('tags', $currentSeason)
                      ->orWhere('description', 'like', "%{$currentSeason}%");
            })
            ->limit(5)
            ->get();

        foreach ($seasonalProducts as $product) {
            $recommendations[] = [
                'product_id' => $product->id,
                'product' => $product,
                'reason' => "Perfect for {$currentSeason} season"
            ];
        }

        return $recommendations;
    }

    /**
     * Score recommendations based on algorithm type
     */
    protected function scoreRecommendations(array $recommendations, string $algorithm, float $weight): array
    {
        return array_map(function($rec) use ($algorithm, $weight) {
            $baseScore = $rec['relevance_score'] ?? $rec['frequency'] ?? 5;
            $rec['score'] = $baseScore * $weight;
            $rec['algorithm'] = $algorithm;
            return $rec;
        }, $recommendations);
    }

    /**
     * Get popular products for new customers
     */
    protected function getPopularProducts(): array
    {
        $products = Product::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->where('featured', true)
            ->limit(5)
            ->get();

        return $products->map(function($product) {
            return [
                'product_id' => $product->id,
                'product' => $product,
                'reason' => 'Popular choice among customers'
            ];
        })->toArray();
    }

    /**
     * Get customer's previously purchased product IDs
     */
    protected function getCustomerPurchasedProductIds($customerProfile): array
    {
        $orders = EcommerceOrder::where('tenant_id', $this->tenantId)
            ->where('contact_id', $customerProfile->contact_id)
            ->get();

        $productIds = [];
        foreach ($orders as $order) {
            $items = json_decode($order->order_items, true) ?? [];
            foreach ($items as $item) {
                $productIds[] = $item['product_id'];
            }
        }

        return array_unique($productIds);
    }

    /**
     * Calculate content relevance score
     */
    protected function calculateContentRelevance($product, $customerProfile): float
    {
        $score = 0;

        // Category match
        if (in_array($product->category, $customerProfile->favorite_categories ?? [])) {
            $score += 10;
        }

        // Price appropriateness
        $avgOrderValue = $customerProfile->average_order_value ?? 0;
        if ($avgOrderValue > 0) {
            $priceDiff = abs($product->price - $avgOrderValue) / $avgOrderValue;
            $score += max(0, 5 - ($priceDiff * 5)); // Max 5 points for price match
        }

        // Featured product bonus
        if ($product->featured) {
            $score += 3;
        }

        // Stock availability
        if ($product->stock_quantity > 10) {
            $score += 2;
        }

        return $score;
    }

    /**
     * Get current season
     */
    protected function getCurrentSeason(): string
    {
        $month = now()->month;
        if (in_array($month, [12, 1, 2])) return 'winter';
        if (in_array($month, [3, 4, 5])) return 'spring';
        if (in_array($month, [6, 7, 8])) return 'summer';
        return 'autumn';
    }
}
