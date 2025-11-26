<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;

class CustomerProfile extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'contact_id',
        'full_name',
        'phone',
        'email',
        'tier',
        'preferences',
        'total_orders',
        'total_spent',
        'average_order_value',
        'favorite_categories',
        'last_interaction_at',
        'last_order_date',
        'behavioral_score',
        'price_sensitivity',
        'interaction_frequency',
        'preferred_language',
        'seasonal_patterns',
        'purchase_timing',
        'communication_preferences',
        'ai_insights',
        'custom_attributes'
    ];

    protected $casts = [
        'preferences' => 'array',
        'favorite_categories' => 'array',
        'seasonal_patterns' => 'array',
        'purchase_timing' => 'array',
        'communication_preferences' => 'array',
        'ai_insights' => 'array',
        'custom_attributes' => 'array',
        'last_interaction_at' => 'datetime',
        'last_order_date' => 'datetime',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2'
    ];

    /**
     * Get tier display name
     */
    public function getTierDisplayAttribute(): string
    {
        return match($this->tier) {
            'vip' => 'VIP Customer',
            'premium' => 'Premium Customer',
            'regular' => 'Regular Customer',
            'standard' => 'Standard Customer',
            default => 'New Customer'
        };
    }

    /**
     * Get tier benefits
     */
    public function getTierBenefitsAttribute(): array
    {
        return match($this->tier) {
            'vip' => [
                'Free express shipping',
                'Priority customer support',
                '15% discount on all orders',
                'Early access to new products',
                'Personal shopping assistant',
                'Free returns & exchanges'
            ],
            'premium' => [
                'Free standard shipping',
                'Priority support',
                '10% discount on orders',
                'Early sale access',
                'Extended return period'
            ],
            'regular' => [
                'Free shipping on orders over $50',
                '5% loyalty discount',
                'Birthday special offers',
                'Member-only deals'
            ],
            'standard' => [
                'Welcome discount',
                'Earn loyalty points',
                'Newsletter exclusive offers'
            ],
            default => ['Welcome bonus']
        };
    }

    /**
     * Get behavioral insights
     */
    public function getBehavioralInsightsAttribute(): array
    {
        $insights = [];

        if ($this->behavioral_score >= 80) {
            $insights[] = 'Highly engaged customer';
        } elseif ($this->behavioral_score >= 60) {
            $insights[] = 'Regular engaged customer';
        } else {
            $insights[] = 'Low engagement - needs attention';
        }

        if ($this->price_sensitivity === 'high') {
            $insights[] = 'Price-conscious buyer';
        } elseif ($this->price_sensitivity === 'low') {
            $insights[] = 'Premium product preference';
        }

        if (!empty($this->seasonal_patterns)) {
            $insights[] = 'Seasonal buyer: ' . implode(', ', $this->seasonal_patterns);
        }

        return $insights;
    }

    /**
     * Get recommended communication style
     */
    public function getRecommendedCommunicationStyleAttribute(): array
    {
        $style = $this->communication_preferences ?? [];
        
        // AI-generated recommendations based on profile
        if ($this->tier === 'vip') {
            $style['tone'] = 'premium';
            $style['detail_level'] = 'comprehensive';
            $style['personalization'] = 'high';
        } elseif ($this->behavioral_score >= 70) {
            $style['tone'] = 'friendly';
            $style['detail_level'] = 'moderate';
            $style['personalization'] = 'medium';
        } else {
            $style['tone'] = 'helpful';
            $style['detail_level'] = 'simple';
            $style['personalization'] = 'basic';
        }

        return $style;
    }
}
