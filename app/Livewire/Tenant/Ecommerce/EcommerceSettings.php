<?php

namespace App\Livewire\Tenant\Ecommerce;

use App\Models\Tenant\EcommerceConfiguration;
use App\Services\GoogleSheetsService;
use App\Services\GoogleSheetsDirectApiService;
use App\Services\GoogleSheetsServiceAccountService;
use App\Services\EcommerceLogger;
use App\Services\FeatureService;
use Livewire\Component;
use Livewire\WithFileUploads;

class EcommerceSettings extends Component
{
    use WithFileUploads;

    public $config;
    public $generatedScript = '';
    public $showScriptModal = false;
    public $showImportModal = false;
    public $importData = [];
    public $serviceAccountStatus = [];
    public $settings = [
        'google_sheets_url' => '',
        'google_sheets_enabled' => false,
        'currency' => 'USD',
        'tax_rate' => '0.00',
        'collect_customer_details' => true,
        'required_customer_fields' => [
            'name' => true,
            'phone' => true,
            'address' => true,
            'city' => false,
            'email' => false,
            'notes' => false
        ],
        'enabled_payment_methods' => [
            'cod' => true,
            'bank_transfer' => true,
            'card' => false,
            'online' => false
        ],
        'payment_method_responses' => [
            'cod' => '💵 *Cash on Delivery*\nOur delivery team will contact you within 24 hours.\nPlease keep exact cash amount ready.',
            'bank_transfer' => '🏦 *Bank Transfer*\nAccount: 1234-5678-9012\nBank: ABC Bank\nPlease send us the transfer receipt.',
            'card' => '💳 *Card Payment*\nOur team will send you a secure payment link shortly.',
            'online' => '🌐 *Online Payment*\nRedirecting to secure payment gateway...'
        ],
        'order_confirmation_message' => '',
        'payment_confirmation_message' => '',
        'ai_recommendations_enabled' => true,
        'abandoned_cart_settings' => [
            'enabled' => false,
            'delay_hours' => 24,
            'message' => ''
        ],
        'upselling_settings' => [
            'enabled' => true,
            'threshold_amount' => 50,
            'message_template' => ''
        ],
        'shipping_settings' => [
            'enabled' => false,
            'free_shipping_threshold' => 0,
            'default_shipping_cost' => 0
        ],
        'ai_powered_mode' => false,
        'openai_api_key' => '',
        'openai_model' => 'gpt-3.5-turbo',
        'ai_temperature' => 0.7,
        'ai_max_tokens' => 500,
        'ai_system_prompt' => '',
        'direct_sheets_integration' => false,
        'bypass_local_database' => false
    ];

    public $availablePaymentMethods = [
        'cod' => 'Cash on Delivery',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Credit/Debit Card',
        'online' => 'Online Payment'
    ];

    public $availableCurrencies = [
        'USD' => 'US Dollar ($)',
        'EUR' => 'Euro (€)',
        'GBP' => 'British Pound (£)',
        'INR' => 'Indian Rupee (₹)',
        'JPY' => 'Japanese Yen (¥)',
        'AUD' => 'Australian Dollar (A$)',
        'CAD' => 'Canadian Dollar (C$)'
    ];

    public $availableAiModels = [
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Recommended)',
        'gpt-4' => 'GPT-4 (More Advanced)',
        'gpt-4-turbo-preview' => 'GPT-4 Turbo (Latest)',
        'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16K (Long Context)'
    ];

    protected $rules = [
        'settings.google_sheets_url' => 'nullable|url',
        'settings.currency' => 'required|string|in:USD,EUR,GBP,INR,JPY,AUD,CAD',
        'settings.tax_rate' => 'required|numeric|min:0|max:100',
        'settings.collect_customer_details' => 'nullable|boolean',
        'settings.required_customer_fields.name' => 'nullable|boolean',
        'settings.required_customer_fields.phone' => 'nullable|boolean',
        'settings.required_customer_fields.address' => 'nullable|boolean',
        'settings.required_customer_fields.city' => 'nullable|boolean',
        'settings.required_customer_fields.email' => 'nullable|boolean',
        'settings.required_customer_fields.notes' => 'nullable|boolean',
        'settings.enabled_payment_methods.cod' => 'nullable|boolean',
        'settings.enabled_payment_methods.bank_transfer' => 'nullable|boolean',
        'settings.enabled_payment_methods.card' => 'nullable|boolean',
        'settings.enabled_payment_methods.online' => 'nullable|boolean',
        'settings.payment_method_responses.cod' => 'nullable|string|max:500',
        'settings.payment_method_responses.bank_transfer' => 'nullable|string|max:500',
        'settings.payment_method_responses.card' => 'nullable|string|max:500',
        'settings.payment_method_responses.online' => 'nullable|string|max:500',
        'settings.order_confirmation_message' => 'nullable|string|max:1000',
        'settings.payment_confirmation_message' => 'nullable|string|max:1000',
        'settings.ai_recommendations_enabled' => 'nullable|boolean',
        'settings.abandoned_cart_settings.enabled' => 'nullable|boolean',
        'settings.abandoned_cart_settings.delay_hours' => 'nullable|integer|min:1|max:168',
        'settings.abandoned_cart_settings.message' => 'nullable|string|max:1000',
        'settings.upselling_settings.enabled' => 'nullable|boolean',
        'settings.upselling_settings.threshold_amount' => 'nullable|numeric|min:0',
        'settings.shipping_settings.enabled' => 'nullable|boolean',
        'settings.shipping_settings.free_shipping_threshold' => 'nullable|numeric|min:0',
        'settings.shipping_settings.default_shipping_cost' => 'nullable|numeric|min:0',
        'settings.ai_powered_mode' => 'nullable|boolean',
        'settings.openai_api_key' => 'nullable|string|max:255',
        'settings.openai_model' => 'nullable|string|in:gpt-3.5-turbo,gpt-4,gpt-4-turbo-preview,gpt-3.5-turbo-16k',
        'settings.ai_temperature' => 'nullable|numeric|min:0|max:2',
        'settings.ai_max_tokens' => 'nullable|integer|min:50|max:4000',
        'settings.ai_system_prompt' => 'nullable|string|max:5000',
        'settings.direct_sheets_integration' => 'nullable|boolean',
        'settings.bypass_local_database' => 'nullable|boolean',
    ];

    public function mount(FeatureService $featureService)
    {
        // Check if user has access to Ecommerce Bot feature
        if (!$featureService->hasAccess('ecommerce_bot')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - Ecommerce Bot feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        if (!checkPermission('tenant.ecommerce.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->loadSettings();
        $this->checkServiceAccountStatus();
    }

    public function loadSettings()
    {
        $this->config = EcommerceConfiguration::where('tenant_id', tenant_id())->first();
        
        if ($this->config) {
            $this->settings = [
                'google_sheets_url' => $this->config->google_sheets_url ?? '',
                'google_sheets_enabled' => $this->config->google_sheets_enabled ?? false,
                'currency' => $this->config->currency ?? 'USD',
                'tax_rate' => number_format($this->config->tax_rate ?? 0, 2),
                'collect_customer_details' => $this->config->collect_customer_details ?? true,
                'required_customer_fields' => $this->config->getRequiredCustomerFields(),
                'enabled_payment_methods' => $this->config->getEnabledPaymentMethods(),
                'payment_method_responses' => $this->config->getPaymentMethodResponses(),
                'order_confirmation_message' => $this->config->order_confirmation_message ?? $this->getDefaultOrderMessage(),
                'payment_confirmation_message' => $this->config->payment_confirmation_message ?? $this->getDefaultPaymentMessage(),
                'ai_recommendations_enabled' => $this->config->ai_recommendations_enabled ?? true,
                'abandoned_cart_settings' => $this->config->abandoned_cart_settings ?? [
                    'enabled' => false,
                    'delay_hours' => 24,
                    'message' => $this->getDefaultAbandonedCartMessage()
                ],
                'upselling_settings' => $this->config->upselling_settings ?? [
                    'enabled' => true,
                    'threshold_amount' => 50,
                    'message_template' => $this->getDefaultUpsellingMessage()
                ],
                'shipping_settings' => $this->config->shipping_settings ?? [
                    'enabled' => false,
                    'free_shipping_threshold' => 0,
                    'default_shipping_cost' => 0
                ],
                'ai_powered_mode' => $this->config->ai_powered_mode ?? false,
                'openai_api_key' => $this->config->openai_api_key ?? '',
                'openai_model' => $this->config->openai_model ?? 'gpt-3.5-turbo',
                'ai_temperature' => $this->config->ai_temperature ?? 0.7,
                'ai_max_tokens' => $this->config->ai_max_tokens ?? 500,
                'ai_system_prompt' => $this->config->ai_system_prompt ?? '',
                'direct_sheets_integration' => $this->config->direct_sheets_integration ?? false,
                'bypass_local_database' => $this->config->bypass_local_database ?? false
            ];
        }
    }

    protected function ensureDefaultSettings()
    {
        // Ensure all required fields have default values
        if (!isset($this->settings['collect_customer_details'])) {
            $this->settings['collect_customer_details'] = true;
        }
        
        if (!isset($this->settings['required_customer_fields'])) {
            $this->settings['required_customer_fields'] = [
                'name' => true,
                'phone' => true,
                'address' => true,
                'city' => false,
                'email' => false,
                'notes' => false
            ];
        }
        
        if (!isset($this->settings['enabled_payment_methods'])) {
            $this->settings['enabled_payment_methods'] = [
                'cod' => true,
                'bank_transfer' => true,
                'card' => false,
                'online' => false
            ];
        }
        
        if (!isset($this->settings['payment_method_responses'])) {
            $this->settings['payment_method_responses'] = [
                'cod' => '💵 *Cash on Delivery*\nOur delivery team will contact you within 24 hours.\nPlease keep exact cash amount ready.',
                'bank_transfer' => '🏦 *Bank Transfer*\nAccount: 1234-5678-9012\nBank: ABC Bank\nPlease send us the transfer receipt.',
                'card' => '💳 *Card Payment*\nOur team will send you a secure payment link shortly.',
                'online' => '🌐 *Online Payment*\nRedirecting to secure payment gateway...'
            ];
        }
        
        // Ensure AI settings exist
        if (!isset($this->settings['ai_powered_mode'])) {
            $this->settings['ai_powered_mode'] = false;
        }
        if (!isset($this->settings['openai_api_key'])) {
            $this->settings['openai_api_key'] = '';
        }
        if (!isset($this->settings['openai_model'])) {
            $this->settings['openai_model'] = 'gpt-3.5-turbo';
        }
        if (!isset($this->settings['ai_temperature'])) {
            $this->settings['ai_temperature'] = 0.7;
        }
        if (!isset($this->settings['ai_max_tokens'])) {
            $this->settings['ai_max_tokens'] = 500;
        }
        if (!isset($this->settings['ai_system_prompt'])) {
            $this->settings['ai_system_prompt'] = '';
        }
        if (!isset($this->settings['direct_sheets_integration'])) {
            $this->settings['direct_sheets_integration'] = false;
        }
        if (!isset($this->settings['bypass_local_database'])) {
            $this->settings['bypass_local_database'] = false;
        }
    }

    public function saveSettings()
    {
        \Log::info('🔧 SaveSettings: Method called', [
            'tenant_id' => tenant_id(),
            'config_exists' => $this->config ? 'Yes' : 'No',
            'settings_count' => count($this->settings ?? [])
        ]);
        
        try {
            // Ensure all settings have proper defaults
            $this->ensureDefaultSettings();
            
            \Log::info('🔧 SaveSettings: Before validation', [
                'settings' => $this->settings
            ]);
            
            $this->validate();
            
            \Log::info('🔧 SaveSettings: Validation passed');

        } catch (\Exception $validationError) {
            \Log::error('🔧 SaveSettings: Validation failed', [
                'error' => $validationError->getMessage(),
                'trace' => $validationError->getTraceAsString()
            ]);
            $this->notify(['type' => 'danger', 'message' => 'Validation error: ' . $validationError->getMessage()]);
            return;
        }

        try {
            if (!$this->config) {
                \Log::warning('🔧 SaveSettings: No config found');
                $this->notify(['type' => 'danger', 'message' => 'Please complete e-commerce setup first']);
                return redirect()->to(tenant_route('tenant.ecommerce.setup'));
            }

            \Log::info('🔧 SaveSettings: About to update config', [
                'config_id' => $this->config->id,
                'update_data' => [
                    'currency' => $this->settings['currency'],
                    'tax_rate' => (float) $this->settings['tax_rate'],
                    'collect_customer_details' => $this->settings['collect_customer_details'],
                    'enabled_payment_methods' => $this->settings['enabled_payment_methods'],
                ]
            ]);

            $this->config->update([
                'google_sheets_url' => $this->settings['google_sheets_url'] ?? null,
                'google_sheets_enabled' => !empty($this->settings['google_sheets_url']),
                'currency' => $this->settings['currency'],
                'tax_rate' => (float) $this->settings['tax_rate'],
                'collect_customer_details' => $this->settings['collect_customer_details'],
                'required_customer_fields' => $this->settings['required_customer_fields'],
                'enabled_payment_methods' => $this->settings['enabled_payment_methods'],
                'payment_method_responses' => $this->settings['payment_method_responses'],
                'order_confirmation_message' => $this->settings['order_confirmation_message'],
                'payment_confirmation_message' => $this->settings['payment_confirmation_message'],
                'ai_recommendations_enabled' => $this->settings['ai_recommendations_enabled'],
                'abandoned_cart_settings' => $this->settings['abandoned_cart_settings'],
                'upselling_settings' => $this->settings['upselling_settings'],
                'shipping_settings' => $this->settings['shipping_settings'],
                'ai_powered_mode' => $this->settings['ai_powered_mode'] ?? false,
                'openai_api_key' => $this->settings['openai_api_key'] ?? null,
                'openai_model' => $this->settings['openai_model'] ?? 'gpt-3.5-turbo',
                'ai_temperature' => $this->settings['ai_temperature'] ?? 0.7,
                'ai_max_tokens' => $this->settings['ai_max_tokens'] ?? 500,
                'ai_system_prompt' => $this->settings['ai_system_prompt'] ?? null,
                'direct_sheets_integration' => $this->settings['direct_sheets_integration'] ?? false,
                'bypass_local_database' => $this->settings['bypass_local_database'] ?? false,
            ]);

            \Log::info('🔧 SaveSettings: Config updated successfully');
            $this->notify(['type' => 'success', 'message' => 'E-commerce settings updated successfully']);
            
        } catch (\Exception $e) {
            \Log::error('🔧 SaveSettings: Database update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config_id' => $this->config ? $this->config->id : 'null',
                'tenant_id' => tenant_id()
            ]);
            $this->notify(['type' => 'danger', 'message' => 'Error updating settings: ' . $e->getMessage()]);
        }
        
        \Log::info('🔧 SaveSettings: Method completed');
    }

    public function testConnection()
    {
        \Log::info('🔧 TestConnection: Method called successfully');
        $this->notify(['type' => 'info', 'message' => 'Livewire connection is working! Check logs for save issues.']);
    }

    public function resetToDefaults()
    {
        $this->settings = [
            'google_sheets_url' => '',
            'google_sheets_enabled' => false,
            'currency' => 'USD',
            'tax_rate' => '0.00',
            'payment_methods' => ['cash_on_delivery'],
            'order_confirmation_message' => $this->getDefaultOrderMessage(),
            'payment_confirmation_message' => $this->getDefaultPaymentMessage(),
            'ai_recommendations_enabled' => true,
            'abandoned_cart_settings' => [
                'enabled' => false,
                'delay_hours' => 24,
                'message' => $this->getDefaultAbandonedCartMessage()
            ],
            'upselling_settings' => [
                'enabled' => true,
                'threshold_amount' => 50,
                'message_template' => $this->getDefaultUpsellingMessage()
            ],
            'shipping_settings' => [
                'enabled' => false,
                'free_shipping_threshold' => 0,
                'default_shipping_cost' => 0
            ]
        ];

        $this->notify(['type' => 'info', 'message' => 'Settings reset to defaults']);
    }

    public function syncWithGoogleSheets()
    {
        try {
            $sheetsService = new GoogleSheetsService();
            $result = $sheetsService->syncProductsFromSheets();
            
            if ($result['success']) {
                $this->notify(['type' => 'success', 'message' => $result['message']]);
            } else {
                $this->notify(['type' => 'danger', 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Sync failed: ' . $e->getMessage()]);
        }
    }
    
    public function syncSheets()
    {
        try {
            if (!$this->config) {
                $this->notify(['type' => 'danger', 'message' => 'Please complete e-commerce setup first']);
                $this->dispatch('sync-error', message: 'No configuration found');
                return;
            }

            EcommerceLogger::info('Manual sync initiated from settings', [
                'tenant_id' => tenant_id(),
                'user_id' => auth()->id()
            ]);
            
            // Dispatch browser event
            $this->dispatch('sync-started', tenantId: tenant_id());

            $sheetsService = new GoogleSheetsService();
            $result = $sheetsService->syncProductsFromSheets();

            if ($result['success']) {
                EcommerceLogger::info('Sync completed successfully', ['result' => $result]);
                $this->notify(['type' => 'success', 'message' => $result['message']]);
                $this->dispatch('sync-completed', synced: $result['synced'] ?? 0, errors: $result['errors'] ?? 0);
            } else {
                EcommerceLogger::error('Sync failed', ['result' => $result]);
                $this->notify(['type' => 'danger', 'message' => $result['message']]);
                $this->dispatch('sync-error', message: $result['message']);
            }

        } catch (\Exception $e) {
            EcommerceLogger::error('Sync exception', [
                'tenant_id' => tenant_id(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMsg = 'Sync failed: ' . $e->getMessage();
            $this->notify(['type' => 'danger', 'message' => $errorMsg]);
            $this->dispatch('sync-error', message: $errorMsg);
        }
    }

    public function closeScriptModal()
    {
        $this->showScriptModal = false;
        $this->generatedScript = '';
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importData = [];
    }

    public function checkServiceAccountStatus()
    {
        $service = new GoogleSheetsServiceAccountService();
        $this->serviceAccountStatus = $service->checkServiceAccountSetup();
    }

    public function disconnectGoogleSheets()
    {
        try {
            if (!$this->config) {
                $this->notify(['type' => 'danger', 'message' => 'No e-commerce configuration found']);
                return;
            }

            $tenantId = tenant_id();
            $configId = $this->config->id;

            EcommerceLogger::info('Google Sheets disconnection initiated - DROPPING TABLE', [
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'config_id' => $configId,
                'previous_url' => $this->config->google_sheets_url
            ]);

            // Drop tenant-specific products table
            $tableService = new \App\Services\DynamicTenantTableService();
            $tableDropped = $tableService->dropTenantProductsTable($tenantId);

            if ($tableDropped) {
                EcommerceLogger::info('Tenant products table DROPPED', [
                    'tenant_id' => $tenantId,
                    'table_name' => "tenant_{$tenantId}_products"
                ]);
            }

            // Delete the entire ecommerce configuration record
            $this->config->delete();

            EcommerceLogger::info('E-commerce configuration record DELETED', [
                'tenant_id' => $tenantId,
                'config_id' => $configId
            ]);

            // Clear dynamic mapper configuration
            \App\Models\Tenant\TenantSheetConfiguration::where('tenant_id', $tenantId)
                ->where('sheet_type', 'products')
                ->delete();

            EcommerceLogger::info('Sheet configuration DELETED', [
                'tenant_id' => $tenantId
            ]);

            // Clear local config reference
            $this->config = null;
            
            // Reset settings
            $this->settings = [
                'google_sheets_url' => '',
                'google_sheets_enabled' => false,
                'currency' => 'USD',
                'tax_rate' => '0.00',
                'collect_customer_details' => true,
                'required_customer_fields' => [
                    'name' => true,
                    'phone' => true,
                    'address' => true,
                    'city' => false,
                    'email' => false,
                    'notes' => false
                ],
                'enabled_payment_methods' => [
                    'cod' => true,
                    'bank_transfer' => true,
                    'card' => false,
                    'online' => false
                ],
            ];

            EcommerceLogger::info('Complete e-commerce teardown successful', [
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'config_deleted' => true,
                'table_dropped' => $tableDropped
            ]);

            $this->notify([
                'type' => 'success', 
                'message' => "✅ Complete disconnect! Table dropped, configuration deleted. Ready for new setup."
            ]);

            // Redirect to setup page
            return redirect()->to(tenant_route('tenant.ecommerce.setup'));

        } catch (\Exception $e) {
            EcommerceLogger::error('Google Sheets disconnection failed', [
                'tenant_id' => tenant_id(),
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            $this->notify([
                'type' => 'danger', 
                'message' => 'Failed to disconnect: ' . $e->getMessage()
            ]);
        }
    }

    protected function getDefaultOrderMessage()
    {
        return "🎉 *Order Confirmed!*\n\nThank you for your order #{order_number}!\n\n*Order Details:*\n{order_items}\n\n*Total Amount:* {total_amount}\n*Payment Method:* {payment_method}\n\nWe'll process your order and keep you updated!";
    }

    protected function getDefaultPaymentMessage()
    {
        return "✅ Payment Received! Thank you for your payment of {payment_amount} for order #{order_number}. Your order will be shipped soon!";
    }

    protected function getDefaultAbandonedCartMessage()
    {
        return "🛒 You left something in your cart! Don't miss out on: {cart_items}. Complete your order now and get it delivered to your doorstep!";
    }

    protected function getDefaultUpsellingMessage()
    {
        return "🔥 Special Offer! Since you're ordering {current_items}, how about adding {recommended_item} for just {additional_cost} more? Perfect combo!";
    }

    public function render()
    {
        return view('livewire.tenant.ecommerce.settings', [
            'availablePaymentMethods' => $this->availablePaymentMethods,
            'availableCurrencies' => $this->availableCurrencies,
        ]);
    }
}
