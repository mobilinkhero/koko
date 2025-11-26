<?php

namespace App\Livewire\Tenant\Ecommerce;

use App\Models\Tenant\EcommerceConfiguration;
use App\Services\GoogleSheetsService;
use App\Services\GoogleSheetsServiceAccountService;
use App\Services\FeatureService;
use Livewire\Component;

class EcommerceSetup extends Component
{
    public $currentStep = 1;
    public $totalSteps = 4;

    // Step 1: Google Sheets Configuration
    public $googleSheetsUrl = '';
    public $createNewSheets = false;
    
    // Step 2: Sheet Verification
    public $sheetsValid = false;
    public $sheetValidationMessage = '';
    public $extractedSheetId = '';
    
    // Step 3: Payment & Settings Configuration
    public $paymentMethods = [];
    public $currency = 'USD';
    public $taxRate = 10.0;
    public $shippingEnabled = true;
    public $defaultShippingCost = 0;
    
    // Step 4: AI & Automation Settings
    public $aiRecommendationsEnabled = true;
    public $abandonedCartEnabled = true;
    public $upsellingEnabled = true;
    public $orderConfirmationMessage = '';
    public $paymentConfirmationMessage = '';

    protected $rules = [
        'googleSheetsUrl' => 'required|url',
        'currency' => 'required|string|max:3',
        'taxRate' => 'numeric|min:0|max:100',
        'defaultShippingCost' => 'numeric|min:0',
        'orderConfirmationMessage' => 'nullable|string|max:1000',
        'paymentConfirmationMessage' => 'nullable|string|max:1000',
    ];

    public function mount(FeatureService $featureService)
    {
        // Check permissions
        if (!checkPermission('tenant.ecommerce.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // Check if user has access to Ecommerce Bot feature
        if (!$featureService->hasAccess('ecommerce_bot')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - Ecommerce Bot feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // Check if already configured
        $config = EcommerceConfiguration::where('tenant_id', tenant_id())->first();
        if ($config && $config->isFullyConfigured()) {
            return redirect()->to(tenant_route('tenant.ecommerce.dashboard'));
        }

        // Load existing configuration if any
        if ($config) {
            $this->loadExistingConfig($config);
        } else {
            $this->setDefaultValues();
        }
    }

    public function loadExistingConfig($config)
    {
        $this->googleSheetsUrl = $config->google_sheets_url ?? '';
        $this->paymentMethods = $config->payment_methods ?? [];
        $this->currency = $config->currency ?? 'USD';
        $this->taxRate = $config->tax_rate ?? 10.0;
        $this->aiRecommendationsEnabled = $config->ai_recommendations_enabled ?? true;
        $this->orderConfirmationMessage = $config->order_confirmation_message ?? '';
        $this->paymentConfirmationMessage = $config->payment_confirmation_message ?? '';
    }

    public function setDefaultValues()
    {
        $this->paymentMethods = ['cash_on_delivery', 'bank_transfer'];
        $this->orderConfirmationMessage = 'Thank you for your order! Your order #{order_number} has been confirmed. We will process it shortly.';
        $this->paymentConfirmationMessage = 'Payment received! Your order #{order_number} totaling {total_amount} has been confirmed. Tracking details will be shared soon.';
    }

    public function nextStep()
    {
        try {
            if ($this->currentStep == 1) {
                $this->validateStep1();
            } elseif ($this->currentStep == 2) {
                $this->validateStep2();
            } elseif ($this->currentStep == 3) {
                $this->validateStep3();
            } elseif ($this->currentStep == 4) {
                $this->completeSetup();
                return;
            }

            if ($this->currentStep < $this->totalSteps) {
                $this->currentStep++;
            }
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Navigation error: ' . $e->getMessage()]);
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function testLivewire()
    {
        $this->notify(['type' => 'success', 'message' => 'Livewire is working! Current step: ' . $this->currentStep]);
    }

    public function validateStep1()
    {
        $this->validate([
            'googleSheetsUrl' => 'required|url'
        ]);

        // Check if service account is available
        $serviceAccountService = new GoogleSheetsServiceAccountService();
        $serviceAccountStatus = $serviceAccountService->checkServiceAccountSetup();

        if ($serviceAccountStatus['configured']) {
            // Use service account validation
            $this->notify(['type' => 'info', 'message' => 'Debug: Using Service Account validation']);
            
            // Extract sheet ID from URL
            if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $this->googleSheetsUrl, $matches)) {
                $this->extractedSheetId = $matches[1];
                $this->sheetsValid = true;
                $this->sheetValidationMessage = 'Sheet validated successfully using Service Account. Make sure the sheet is shared with: ' . $serviceAccountStatus['email'];
                $this->notify(['type' => 'success', 'message' => 'Service Account detected! Please share your sheet with: ' . $serviceAccountStatus['email']]);
            } else {
                $this->addError('googleSheetsUrl', 'Invalid Google Sheets URL format');
                return;
            }
        } else {
            // Fallback to public access validation
            $this->notify(['type' => 'warning', 'message' => 'Debug: Service Account not configured, using public access validation']);
            
            $sheetsService = new GoogleSheetsService();
            $validation = $sheetsService->validateSheetsUrl($this->googleSheetsUrl);

            if (!$validation['valid']) {
                $this->addError('googleSheetsUrl', $validation['message']);
                return;
            }

            $this->extractedSheetId = $validation['sheet_id'];
            $this->sheetsValid = true;
            $this->sheetValidationMessage = $validation['message'];
        }
    }

    public function validateStep2()
    {
        // Debug logging
        $this->notify(['type' => 'info', 'message' => 'Step 2 validation started. sheetsValid: ' . ($this->sheetsValid ? 'true' : 'false') . ', extractedSheetId: ' . $this->extractedSheetId]);
        
        // If sheets are not already validated, validate them now
        if (!$this->sheetsValid || empty($this->extractedSheetId)) {
            if (empty($this->googleSheetsUrl)) {
                $this->notify(['type' => 'danger', 'message' => 'Debug: Google Sheets URL is empty']);
                $this->addError('googleSheetsUrl', 'Please enter your Google Sheets URL first');
                $this->currentStep = 1;
                return;
            }

            $this->notify(['type' => 'info', 'message' => 'Debug: Re-validating Google Sheets URL: ' . $this->googleSheetsUrl]);

            // Check if service account is available
            $serviceAccountService = new GoogleSheetsServiceAccountService();
            $serviceAccountStatus = $serviceAccountService->checkServiceAccountSetup();

            if ($serviceAccountStatus['configured']) {
                // Use service account validation
                $this->notify(['type' => 'info', 'message' => 'Debug: Using Service Account validation']);
                
                // Extract sheet ID from URL
                if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $this->googleSheetsUrl, $matches)) {
                    $this->extractedSheetId = $matches[1];
                    $this->sheetsValid = true;
                    $this->sheetValidationMessage = 'Sheet validated successfully using Service Account. Make sure the sheet is shared with: ' . $serviceAccountStatus['email'];
                    $this->notify(['type' => 'success', 'message' => 'Debug: Service Account validation successful, sheet ID: ' . $this->extractedSheetId]);
                } else {
                    $this->notify(['type' => 'danger', 'message' => 'Debug: Invalid Google Sheets URL format']);
                    $this->addError('googleSheetsUrl', 'Invalid Google Sheets URL format');
                    $this->currentStep = 1;
                    return;
                }
            } else {
                // Fallback to public access validation
                $this->notify(['type' => 'warning', 'message' => 'Debug: Service Account not configured, using public access validation']);
                
                $sheetsService = new GoogleSheetsService();
                $validation = $sheetsService->validateSheetsUrl($this->googleSheetsUrl);

                if (!$validation['valid']) {
                    $this->notify(['type' => 'danger', 'message' => 'Debug: Validation failed - ' . $validation['message']]);
                    $this->addError('googleSheetsUrl', $validation['message']);
                    $this->currentStep = 1;
                    return;
                }

                $this->extractedSheetId = $validation['sheet_id'];
                $this->sheetsValid = true;
                $this->sheetValidationMessage = $validation['message'];
                $this->notify(['type' => 'success', 'message' => 'Debug: Validation successful, sheet ID: ' . $this->extractedSheetId]);
            }
        }
        
        $this->notify(['type' => 'success', 'message' => 'Debug: Step 2 validation completed successfully']);
    }

    public function validateStep3()
    {
        $this->validate([
            'currency' => 'required|string|max:3',
            'taxRate' => 'numeric|min:0|max:100',
            'defaultShippingCost' => 'numeric|min:0',
        ]);

        if (empty($this->paymentMethods)) {
            $this->addError('paymentMethods', 'Please select at least one payment method');
            return;
        }
    }

    public function togglePaymentMethod($method)
    {
        if (in_array($method, $this->paymentMethods)) {
            $this->paymentMethods = array_diff($this->paymentMethods, [$method]);
        } else {
            $this->paymentMethods[] = $method;
        }
    }

    public function createDefaultSheets()
    {
        $this->createNewSheets = true;
        
        $sheetsService = new GoogleSheetsService();
        $structure = $sheetsService->createDefaultSheets();
        
        $this->notify([
            'type' => 'info', 
            'message' => 'Please create the required sheets in your Google Sheets document and make sure they are publicly viewable.'
        ]);
    }

    public function completeSetup()
    {
        $this->validate([
            'orderConfirmationMessage' => 'nullable|string|max:1000',
            'paymentConfirmationMessage' => 'nullable|string|max:1000',
        ]);

        try {
            // Create or update configuration
            $config = EcommerceConfiguration::updateOrCreate(
                ['tenant_id' => tenant_id()],
                [
                    'is_configured' => true,
                    'google_sheets_url' => $this->googleSheetsUrl,
                    'products_sheet_id' => $this->extractedSheetId,
                    'orders_sheet_id' => $this->extractedSheetId,
                    'customers_sheet_id' => $this->extractedSheetId,
                    'payment_methods' => $this->paymentMethods,
                    'currency' => $this->currency,
                    'tax_rate' => $this->taxRate,
                    'shipping_settings' => [
                        'enabled' => $this->shippingEnabled,
                        'default_cost' => $this->defaultShippingCost,
                    ],
                    'abandoned_cart_settings' => [
                        'enabled' => $this->abandonedCartEnabled,
                        'delay_hours' => [1, 6, 24],
                        'discount_percentage' => [0, 5, 10],
                    ],
                    'upselling_settings' => [
                        'enabled' => $this->upsellingEnabled,
                        'cross_sell_enabled' => true,
                        'minimum_order_value' => 0,
                    ],
                    'ai_recommendations_enabled' => $this->aiRecommendationsEnabled,
                    'order_confirmation_message' => $this->orderConfirmationMessage,
                    'payment_confirmation_message' => $this->paymentConfirmationMessage,
                    'configuration_completed_at' => now(),
                ]
            );

            // Initial sync from Google Sheets
            $sheetsService = new GoogleSheetsService();
            $syncResult = $sheetsService->syncProductsFromSheets();

            if ($syncResult['success']) {
                $this->notify([
                    'type' => 'success', 
                    'message' => 'E-commerce setup completed successfully! ' . $syncResult['message']
                ]);
            } else {
                $this->notify([
                    'type' => 'warning', 
                    'message' => 'Setup completed, but initial sync failed: ' . $syncResult['message']
                ]);
            }

            return redirect()->to(tenant_route('tenant.ecommerce.dashboard'));

        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Setup failed: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $sheetsService = new GoogleSheetsService();
        $sheetsStructure = $sheetsService->createDefaultSheets();

        return view('livewire.tenant.ecommerce.setup', [
            'sheetsStructure' => $sheetsStructure,
            'availablePaymentMethods' => [
                'cash_on_delivery' => 'Cash on Delivery',
                'bank_transfer' => 'Bank Transfer',
                'upi' => 'UPI Payment',
                'credit_card' => 'Credit Card',
                'debit_card' => 'Debit Card',
                'paypal' => 'PayPal',
                'stripe' => 'Stripe',
                'razorpay' => 'Razorpay',
            ],
            'availableCurrencies' => [
                'USD' => 'US Dollar ($)',
                'EUR' => 'Euro (€)',
                'GBP' => 'British Pound (£)',
                'INR' => 'Indian Rupee (₹)',
                'JPY' => 'Japanese Yen (¥)',
                'AUD' => 'Australian Dollar (A$)',
                'CAD' => 'Canadian Dollar (C$)',
            ],
        ]);
    }
}
