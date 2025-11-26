<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;

/**
 * E-commerce Configuration Model
 * Stores Google Sheets configuration and e-commerce settings for each tenant
 */
class EcommerceConfiguration extends BaseModel
{
    use BelongsToTenant, TracksFeatureUsage;

    protected $fillable = [
        'tenant_id',
        'is_configured',
        'google_sheets_url',
        'google_sheets_enabled',
        'products_sheet_id',
        'orders_sheet_id',
        'customers_sheet_id',
        'required_customer_fields',
        'enabled_payment_methods',
        'payment_method_responses',
        'collect_customer_details',
        'payment_methods',
        'currency',
        'tax_rate',
        'shipping_settings',
        'order_confirmation_message',
        'payment_confirmation_message',
        'abandoned_cart_settings',
        'upselling_settings',
        'ai_recommendations_enabled',
        'ai_powered_mode',
        'openai_api_key',
        'openai_model',
        'ai_temperature',
        'ai_max_tokens',
        'ai_system_prompt',
        'ai_product_context',
        'ai_response_templates',
        'direct_sheets_integration',
        'bypass_local_database',
        'sync_status',
        'last_sync_at',
        'configuration_completed_at',
    ];

    protected $casts = [
        'tenant_id' => 'int',
        'is_configured' => 'bool',
        'google_sheets_enabled' => 'bool',
        'required_customer_fields' => 'json',
        'enabled_payment_methods' => 'json',
        'payment_method_responses' => 'json',
        'collect_customer_details' => 'bool',
        'payment_methods' => 'json',
        'shipping_settings' => 'json',
        'abandoned_cart_settings' => 'json',
        'upselling_settings' => 'json',
        'ai_recommendations_enabled' => 'bool',
        'ai_powered_mode' => 'bool',
        'ai_temperature' => 'decimal:1',
        'ai_max_tokens' => 'int',
        'ai_response_templates' => 'json',
        'direct_sheets_integration' => 'bool',
        'bypass_local_database' => 'bool',
        'tax_rate' => 'decimal:2',
        'last_sync_at' => 'datetime',
        'configuration_completed_at' => 'datetime',
    ];

    /**
     * Get the feature slug for tracking usage
     */
    public function getFeatureSlug(): ?string
    {
        return 'ecommerce';
    }

    /**
     * Check if e-commerce is fully configured
     */
    public function isFullyConfigured(): bool
    {
        return $this->is_configured;
    }

    /**
     * Get payment methods as array
     */
    public function getPaymentMethodsAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    /**
     * Get default abandoned cart settings
     */
    public function getDefaultAbandonedCartSettings(): array
    {
        return [
            'enabled' => true,
            'delay_hours' => [1, 6, 24],
            'discount_percentage' => [0, 5, 10],
            'messages' => [
                'Forgot something? Complete your order now!',
                'Still interested? Here\'s 5% off your cart!',
                'Last chance! 10% off expires soon!'
            ]
        ];
    }

    /**
     * Get default upselling settings
     */
    public function getDefaultUpsellingSettings(): array
    {
        return [
            'enabled' => true,
            'cross_sell_enabled' => true,
            'minimum_order_value' => 0,
            'upsell_percentage' => 20,
            'max_recommendations' => 3
        ];
    }

    /**
     * Get required customer fields with defaults
     */
    public function getRequiredCustomerFields(): array
    {
        return $this->required_customer_fields ?? [
            'name' => true,
            'phone' => true, 
            'address' => true,
            'city' => false,
            'email' => false,
            'notes' => false
        ];
    }

    /**
     * Get enabled payment methods with defaults  
     */
    public function getEnabledPaymentMethods(): array
    {
        return $this->enabled_payment_methods ?? [
            'cod' => true,
            'bank_transfer' => true,
            'card' => false,
            'online' => false
        ];
    }

    /**
     * Get payment method responses with defaults
     */
    public function getPaymentMethodResponses(): array
    {
        return $this->payment_method_responses ?? [
            'cod' => "💵 *Cash on Delivery*\nOur delivery team will contact you within 24 hours.\nPlease keep exact cash amount ready.",
            'bank_transfer' => "🏦 *Bank Transfer*\nAccount: 1234-5678-9012\nBank: ABC Bank\nPlease send us the transfer receipt.",
            'card' => "💳 *Card Payment*\nOur team will send you a secure payment link shortly.",
            'online' => "🌐 *Online Payment*\nRedirecting to secure payment gateway..."
        ];
    }

    /**
     * Check if customer details collection is enabled
     */
    public function shouldCollectCustomerDetails(): bool
    {
        return $this->collect_customer_details ?? true;
    }
}
