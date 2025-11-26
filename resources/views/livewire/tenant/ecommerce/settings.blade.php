<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('E-commerce Settings') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Configure your store settings and automation
                </p>
            </div>
            
            <div class="flex gap-3">
                <button wire:click="resetToDefaults" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Reset to Defaults
                </button>
                <button wire:click="saveSettings" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Settings
                </button>
            </div>
        </div>
    </x-slot>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Basic Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Store Settings</h3>
            
            <div class="space-y-4">
                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Currency
                    </label>
                    <select wire:model="settings.currency" 
                            id="currency"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @foreach($availableCurrencies as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('settings.currency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Tax Rate -->
                <div>
                    <label for="tax_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tax Rate (%)
                    </label>
                    <input type="number" 
                           wire:model="settings.tax_rate"
                           id="tax_rate"
                           step="0.01"
                           min="0"
                           max="100"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="0.00">
                    @error('settings.tax_rate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Google Sheets Connection -->
                <div>
                    <label for="google_sheets_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Google Sheets Connection
                    </label>
                    <div class="relative">
                        <input type="url" 
                               wire:model="settings.google_sheets_url" 
                               class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="https://docs.google.com/spreadsheets/d/...">
                        
                        @if(!empty($config->google_sheets_url))
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                                    <span class="text-xs text-green-600 dark:text-green-400">Connected</span>
                                </div>
                            </div>
                        @endif
                        
                        @error('settings.google_sheets_url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Customer Details Collection -->
                <div>
                    <div class="flex items-center space-x-2 mb-3">
                        <input type="checkbox" 
                               wire:model="settings.collect_customer_details"
                               id="collect_details"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="collect_details" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Collect Customer Details During Checkout
                        </label>
                    </div>
                    
                    @if($settings['collect_customer_details'] ?? false)
                        <div class="ml-6 space-y-2">
                            <p class="text-xs text-gray-500 mb-3">Select which details to require from customers:</p>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           wire:model="settings.required_customer_fields.name"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">👤 Full Name</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           wire:model="settings.required_customer_fields.phone"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">📱 Phone Number</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           wire:model="settings.required_customer_fields.address"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">🏠 Address</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           wire:model="settings.required_customer_fields.city"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">🏙️ City</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           wire:model="settings.required_customer_fields.email"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">📧 Email</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           wire:model="settings.required_customer_fields.notes"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">📝 Special Notes</span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Methods Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Methods & Responses</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Configure which payment methods to offer and customize the response message for each method.</p>
            
            <div class="space-y-6">
                @foreach($availablePaymentMethods as $method => $label)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <!-- Payment Method Toggle -->
                        <div class="flex items-center space-x-3 mb-3">
                            <input type="checkbox" 
                                   wire:model="settings.enabled_payment_methods.{{ $method }}"
                                   id="payment_{{ $method }}"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="payment_{{ $method }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $label }}
                            </label>
                        </div>
                        
                        <!-- Custom Response (shown only if enabled) -->
                        @if($settings['enabled_payment_methods'][$method] ?? false)
                            <div class="ml-6">
                                <label for="response_{{ $method }}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                                    Custom Response Message
                                </label>
                                <textarea wire:model="settings.payment_method_responses.{{ $method }}"
                                          id="response_{{ $method }}"
                                          rows="3"
                                          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                          placeholder="Enter the message customers will see when they select this payment method..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">This message will be sent to customers when they choose {{ $label }}.</p>
                                @error('settings.payment_method_responses.' . $method) 
                                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                                @enderror
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- AI & Automation Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI & Automation</h3>
            
            <div class="space-y-6">
                <!-- AI-Powered Mode -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-purple-900 dark:text-purple-100">🤖 AI-Powered E-commerce Bot</p>
                            <p class="text-xs text-purple-700 dark:text-purple-300">Let AI handle all customer interactions with your Google Sheets data</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="settings.ai_powered_mode" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <!-- Debug info (remove after testing) -->
                    <div class="text-xs text-gray-500 mb-2">
                        Debug: AI Mode = {{ $settings['ai_powered_mode'] ? 'ON' : 'OFF' }}
                    </div>

                    @if($settings['ai_powered_mode'])
                        <div class="space-y-4 mt-4 pt-4 border-t border-purple-200 dark:border-purple-700">
                            <!-- OpenAI API Key -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">OpenAI API Key *</label>
                                <input type="password" wire:model="settings.openai_api_key" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                       placeholder="sk-...">
                                <p class="text-xs text-gray-500 mt-1">Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank" class="text-purple-600 hover:underline">OpenAI Platform</a></p>
                            </div>

                            <!-- AI Model Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">AI Model</label>
                                <select wire:model="settings.openai_model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($availableAiModels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- AI Settings Row -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Temperature</label>
                                    <input type="number" wire:model="settings.ai_temperature" min="0" max="2" step="0.1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <p class="text-xs text-gray-500 mt-1">0 = Focused, 2 = Creative</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Tokens</label>
                                    <input type="number" wire:model="settings.ai_max_tokens" min="50" max="4000" step="50"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <p class="text-xs text-gray-500 mt-1">Response length limit</p>
                                </div>
                            </div>

                            <!-- Custom System Prompt -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom System Prompt (Optional)</label>
                                <textarea wire:model="settings.ai_system_prompt" rows="4" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                          placeholder="Leave empty to use default prompt..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">Customize how the AI assistant behaves</p>
                            </div>

                            <!-- Direct Integration Options -->
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 border border-yellow-200 dark:border-yellow-700">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">⚡ Direct Google Sheets Integration</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="settings.direct_sheets_integration" class="mr-2">
                                        <span class="text-sm text-yellow-700 dark:text-yellow-300">Read products directly from Google Sheets</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="settings.bypass_local_database" class="mr-2">
                                        <span class="text-sm text-yellow-700 dark:text-yellow-300">Bypass local database completely</span>
                                    </label>
                                </div>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">⚠️ This will make responses slightly slower but always use live data</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- AI Recommendations -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">AI Product Recommendations</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Suggest related products to customers</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="settings.ai_recommendations_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Upselling Settings -->
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Smart Upselling</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Suggest additional products during checkout</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="settings.upselling_settings.enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    @if($settings['upselling_settings']['enabled'])
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Minimum Order Value ($)</label>
                                <input type="number" 
                                       wire:model="settings.upselling_settings.threshold_amount"
                                       step="0.01"
                                       min="0"
                                       class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Abandoned Cart Settings -->
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Abandoned Cart Recovery</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Automatically remind customers about their cart</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="settings.abandoned_cart_settings.enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    @if($settings['abandoned_cart_settings']['enabled'])
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Delay (hours)</label>
                                <input type="number" 
                                       wire:model="settings.abandoned_cart_settings.delay_hours"
                                       min="1"
                                       max="168"
                                       class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Reminder Message</label>
                                <textarea wire:model="settings.abandoned_cart_settings.message"
                                          rows="2"
                                          class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                          placeholder="Enter reminder message..."></textarea>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Message Templates -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Message Templates</h3>
            
            <div class="space-y-4">
                <!-- Order Confirmation Message -->
                <div>
                    <label for="order_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Order Confirmation Message
                    </label>
                    <textarea wire:model="settings.order_confirmation_message"
                              id="order_message"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Message sent when order is confirmed..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Available variables: {order_number}, {total_amount}, {customer_name}</p>
                    @error('settings.order_confirmation_message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Payment Confirmation Message -->
                <div>
                    <label for="payment_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Payment Confirmation Message
                    </label>
                    <textarea wire:model="settings.payment_confirmation_message"
                              id="payment_message"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Message sent when payment is received..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Available variables: {order_number}, {payment_amount}, {customer_name}</p>
                    @error('settings.payment_confirmation_message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Shipping Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Shipping Settings</h3>
            
            <div class="space-y-4">
                <!-- Enable Shipping -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Shipping Charges</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Add shipping costs to orders</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="settings.shipping_settings.enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                @if($settings['shipping_settings']['enabled'])
                    <div class="space-y-3 pl-4 border-l-2 border-blue-200 dark:border-blue-800">
                        <!-- Default Shipping Cost -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Default Shipping Cost ($)
                            </label>
                            <input type="number" 
                                   wire:model="settings.shipping_settings.default_shipping_cost"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        <!-- Free Shipping Threshold -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Free Shipping Threshold ($)
                            </label>
                            <input type="number" 
                                   wire:model="settings.shipping_settings.free_shipping_threshold"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="0 = No free shipping">
                            <p class="text-xs text-gray-500 mt-1">Orders above this amount get free shipping</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Service Account Status -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🔑 Global Service Account Status</h3>
        
        <div class="mb-4">
            @if(isset($serviceAccountStatus['configured']) && $serviceAccountStatus['configured'])
                <div class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <div>
                        <p class="text-green-800 dark:text-green-200 font-medium">✅ Service Account Active</p>
                        <p class="text-green-600 dark:text-green-300 text-sm">
                            {{ $serviceAccountStatus['email'] ?? 'Global service account configured' }}<br>
                            <span class="text-xs">One-click sheet creation is fully automated!</span>
                        </p>
                    </div>
                </div>
            @else
                <div class="flex items-center p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                    <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    <div>
                        <p class="text-orange-800 dark:text-orange-200 font-medium">⚠️ Service Account Not Configured</p>
                        <p class="text-orange-600 dark:text-orange-300 text-sm">
                            System administrator needs to configure the global service account.<br>
                            <span class="text-xs">Sheet creation will use the fallback import method.</span>
                        </p>
                    </div>
                </div>
            @endif

            <!-- Information Box -->
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">ℹ️ About Global Service Account</h4>
                <div class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
                    <p><strong>🌐 Global Setup:</strong> One service account handles sheet creation for all tenants</p>
                    <p><strong>🔒 Security:</strong> Configured server-side by system administrators</p>
                    <p><strong>⚡ Benefits:</strong> Instant sheet creation without individual setup required</p>
                    <p><strong>🛠️ Admin Note:</strong> Place <code>google-service-account.json</code> in the project root</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        
        <div class="flex gap-3 flex-wrap mb-4">
            @if(!empty($config->google_sheets_url))
                <button wire:click="syncSheets" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    ⚡ One-Click Create Sheets
                </button>
                
                <button wire:click="syncWithGoogleSheets" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    🔄 Sync Data with Sheets
                </button>

                <button wire:click="disconnectGoogleSheets" 
                        wire:confirm="Are you sure you want to disconnect Google Sheets? This will remove all connection data."
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    🔌 Disconnect Sheets
                </button>
            @else
                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                        📋 <strong>Google Sheets URL required:</strong> Please enter your Google Sheets URL in the configuration above to enable sheet integration.
                    </p>
                </div>
            @endif
            
            <a href="{{ tenant_route('tenant.ecommerce.dashboard') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                📊 View Dashboard
            </a>
            
            <a href="{{ tenant_route('tenant.ecommerce.products') }}" 
               class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                🛍️ Manage Products
            </a>
        </div>

        <!-- Help Section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">📋 Sheet Sync Guide</h4>
            <div class="space-y-2 text-sm text-blue-800 dark:text-blue-300">
                <p><strong>⚡ One-Click Create Sheets:</strong> Automatically creates all required sheets with proper structure:</p>
                <ul class="list-disc list-inside ml-4 space-y-1">
                    <li><strong>Products</strong> - ID, Name, SKU, Description, Price, Category, Stock, etc.</li>
                    <li><strong>Orders</strong> - Order Number, Customer Info, Items, Payment Status, etc.</li>
                    <li><strong>Customers</strong> - Phone, Name, Email, Order History, etc.</li>
                </ul>
                <p><strong>🔄 Sync Data:</strong> Imports existing data from your Google Sheets into the system.</p>
                
                <div class="mt-3 p-2 bg-green-50 dark:bg-green-900/30 rounded border border-green-200 dark:border-green-800">
                    <p class="text-xs text-green-800 dark:text-green-200">
                        <strong>✨ With Service Account:</strong> Fully automatic - creates sheets, adds headers, formats, and adds sample data instantly!<br>
                        <strong>🔄 Without Service Account:</strong> Smart fallback with copy-paste ready data and CSV downloads.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Apps Script Modal -->
    @if($showScriptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeScriptModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    📄 Google Apps Script - Create E-commerce Sheets
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Follow these steps to create the required sheets in your Google Spreadsheet
                                </p>
                            </div>
                            <button wire:click="closeScriptModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">📋 Step-by-Step Instructions:</h4>
                            <ol class="text-sm text-blue-800 dark:text-blue-300 space-y-2">
                                <li><strong>1.</strong> Open your Google Sheet: <a href="{{ $config->google_sheets_url ?? '#' }}" target="_blank" class="underline hover:no-underline">{{ $config->google_sheets_url ?? 'N/A' }}</a></li>
                                <li><strong>2.</strong> Click on <strong>Extensions</strong> → <strong>Apps Script</strong></li>
                                <li><strong>3.</strong> Delete any existing code in the editor</li>
                                <li><strong>4.</strong> Copy and paste the code below</li>
                                <li><strong>5.</strong> Click <strong>Save</strong> (💾 icon)</li>
                                <li><strong>6.</strong> Click <strong>Run</strong> (▶️ icon) to execute the script</li>
                                <li><strong>7.</strong> Grant permissions when prompted</li>
                                <li><strong>8.</strong> Check your Google Sheet - new tabs should be created!</li>
                            </ol>
                        </div>

                        <!-- Generated Apps Script Code -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Generated Apps Script Code:
                                </label>
                                <button onclick="navigator.clipboard.writeText(document.getElementById('appsScriptCode').value)" 
                                        class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                    📋 Copy Code
                                </button>
                            </div>
                            <textarea id="appsScriptCode" 
                                      readonly 
                                      class="w-full h-64 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg font-mono text-sm bg-gray-50 dark:bg-gray-700 dark:text-white"
                                      style="font-family: 'Courier New', monospace;">{{ $generatedScript }}</textarea>
                        </div>

                        <!-- What will be created -->
                        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <h4 class="text-sm font-semibold text-green-900 dark:text-green-200 mb-2">✅ What this script creates:</h4>
                            <div class="text-sm text-green-800 dark:text-green-300 space-y-1">
                                <div><strong>📦 Products Sheet:</strong> ID, Name, SKU, Description, Price, Category, Stock, etc. (13 columns)</div>
                                <div><strong>📋 Orders Sheet:</strong> Order Number, Customer Info, Items, Payment Status, etc. (16 columns)</div>
                                <div><strong>👥 Customers Sheet:</strong> Phone, Name, Email, Order History, etc. (9 columns)</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="closeScriptModal" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Got it!
                        </button>
                        <a href="{{ $config->google_sheets_url ?? '#' }}" 
                           target="_blank"
                           class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">
                            🔗 Open Google Sheet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.tenant.ecommerce.sync-debug-console')

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImportModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    📋 Manual Sheet Creation - Copy & Paste Method
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Follow these steps to manually create the required sheets
                                </p>
                            </div>
                            <button wire:click="closeImportModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-2">📋 Quick Setup Instructions:</h4>
                            <ol class="text-sm text-yellow-800 dark:text-yellow-300 space-y-2">
                                <li><strong>1.</strong> Open your Google Sheet: <a href="{{ $config->google_sheets_url ?? '#' }}" target="_blank" class="underline hover:no-underline">Click here</a></li>
                                <li><strong>2.</strong> For each sheet below, create a new tab with the exact name</li>
                                <li><strong>3.</strong> Copy the headers and paste them in row 1</li>
                                <li><strong>4.</strong> Copy the sample data and paste it in row 2</li>
                                <li><strong>5.</strong> Save and you're done!</li>
                            </ol>
                        </div>

                        <!-- Sheet Data -->
                        @if(!empty($importData))
                            <div class="space-y-6">
                                @foreach($importData as $sheetName => $sheetData)
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                            📊 {{ $sheetName }} Sheet
                                        </h4>
                                        
                                        <!-- Headers -->
                                        <div class="mb-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Headers (paste in row 1):
                                                </label>
                                                <button onclick="navigator.clipboard.writeText('{{ implode(chr(9), $sheetData['headers']) }}')" 
                                                        class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                                    📋 Copy Headers
                                                </button>
                                            </div>
                                            <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded border text-sm font-mono">
                                                {{ implode(' | ', $sheetData['headers']) }}
                                            </div>
                                        </div>

                                        <!-- Sample Data -->
                                        @if(!empty($sheetData['sample_data']))
                                            <div class="mb-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Sample Data (paste in row 2):
                                                    </label>
                                                    <button onclick="navigator.clipboard.writeText('{{ implode(chr(9), $sheetData['sample_data'][0]) }}')" 
                                                            class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                        📋 Copy Sample
                                                    </button>
                                                </div>
                                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded border text-sm font-mono">
                                                    {{ implode(' | ', $sheetData['sample_data'][0]) }}
                                                </div>
                                            </div>
                                        @endif

                                        <!-- CSV Download -->
                                        <div class="mt-3">
                                            <button onclick="downloadCSV('{{ $sheetName }}', `{{ $sheetData['csv_content'] }}`)" 
                                                    class="px-3 py-1 text-sm bg-purple-600 text-white rounded hover:bg-purple-700">
                                                💾 Download {{ $sheetName }}.csv
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="closeImportModal" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Done!
                        </button>
                        <a href="{{ $config->google_sheets_url ?? '#' }}" 
                           target="_blank"
                           class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">
                            🔗 Open Google Sheet
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function downloadCSV(sheetName, csvContent) {
                const element = document.createElement('a');
                const file = new Blob([csvContent], {type: 'text/csv'});
                element.href = URL.createObjectURL(file);
                element.download = sheetName.toLowerCase() + '_import.csv';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
            }
        </script>
    @endif

    <!-- Bottom Save Button -->
    <div class="mt-8 flex justify-center space-x-4">
        <button wire:click="testConnection" 
                class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            🔗 Test Connection
        </button>
        <button wire:click="saveSettings" 
                onclick="console.log('🔧 Save button clicked!'); console.log('Livewire component:', @this);"
                class="px-8 py-3 bg-blue-600 text-white text-lg font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-lg">
            💾 Save All Settings
        </button>
    </div>
</div>
