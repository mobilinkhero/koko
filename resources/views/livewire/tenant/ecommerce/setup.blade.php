<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('E-commerce Setup') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Configure your WhatsApp e-commerce automation in {{ $totalSteps }} easy steps
                </p>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Step {{ $currentStep }} of {{ $totalSteps }}
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                @for($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center @if($i < $totalSteps) flex-1 @endif">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-200
                                  @if($i < $currentStep) bg-blue-600 border-blue-600 text-white
                                  @elseif($i == $currentStep) bg-blue-100 border-blue-600 text-blue-600 dark:bg-blue-900/50
                                  @else bg-gray-100 border-gray-300 text-gray-400 dark:bg-gray-700 dark:border-gray-600 @endif">
                            @if($i < $currentStep)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        @if($i < $totalSteps)
                            <div class="flex-1 h-1 mx-4 rounded-full @if($i < $currentStep) bg-blue-600 @else bg-gray-200 dark:bg-gray-600 @endif transition-all duration-200"></div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Google Sheets</span>
                <span>Verification</span>
                <span>Payment Setup</span>
                <span>Automation</span>
            </div>
        </div>

        <!-- Setup Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @if($currentStep == 1)
                <!-- Step 1: Google Sheets Configuration -->
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Connect Google Sheets</h3>
                        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                            Your products and orders will be synced with Google Sheets for easy management. 
                            You can either use an existing spreadsheet or let us create the structure for you.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <!-- Google Sheets URL Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Google Sheets URL <span class="text-red-500">*</span>
                            </label>
                            <input type="url" 
                                   wire:model.defer="googleSheetsUrl" 
                                   placeholder="https://docs.google.com/spreadsheets/d/your-sheet-id/edit"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('googleSheetsUrl')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Create New Sheets Option -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Don't have a spreadsheet yet?</h4>
                            <p class="text-blue-700 dark:text-blue-300 text-sm mb-4">
                                We'll show you exactly what sheets to create and how to structure them for optimal performance.
                            </p>
                            <button wire:click="createDefaultSheets" 
                                    type="button"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                📋 Show Me Sheet Structure
                            </button>
                        </div>

                        @if($createNewSheets)
                            <!-- Sheets Structure Guide -->
                            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Required Sheets Structure</h4>
                                
                                @foreach($sheetsStructure as $sheetKey => $sheet)
                                    <div class="mb-6 last:mb-0">
                                        <h5 class="font-medium text-gray-900 dark:text-white mb-2">{{ $sheet['name'] }} Sheet</h5>
                                        <div class="bg-gray-50 dark:bg-gray-800 rounded border p-3">
                                            <div class="text-xs font-mono">
                                                <div class="grid grid-cols-3 gap-2">
                                                    @foreach($sheet['columns'] as $col => $name)
                                                        <div class="text-gray-600 dark:text-gray-400">{{ $col }}: {{ $name }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                                        <strong>Important:</strong> Make sure to set your Google Sheet sharing to "Anyone with the link can view" for the sync to work properly.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            @elseif($currentStep == 2)
                <!-- Step 2: Sheet Verification -->
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Verification Complete!</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Great! Your Google Sheets is accessible and ready for integration.
                        </p>
                    </div>

                    @if($sheetsValid)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-green-600 dark:text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Sheet Verified Successfully</h4>
                            <p class="text-green-700 dark:text-green-300">{{ $sheetValidationMessage }}</p>
                            <div class="mt-4 text-sm text-green-600 dark:text-green-400">
                                <p><strong>Sheet ID:</strong> {{ $extractedSheetId }}</p>
                            </div>
                        </div>

                        <!-- Debug Test Button -->
                        <div class="mt-4 text-center">
                            <button wire:click="testLivewire" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm">
                                🔧 Test Livewire Connection
                            </button>
                        </div>
                    @endif
                </div>

            @elseif($currentStep == 3)
                <!-- Step 3: Payment & Settings Configuration -->
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-purple-100 dark:bg-purple-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Payment & Business Settings</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Configure how customers will pay and set up your business preferences.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <!-- Payment Methods -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Payment Methods <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($availablePaymentMethods as $method => $label)
                                    <label class="relative flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                                                 @if(in_array($method, $paymentMethods)) border-blue-500 bg-blue-50 dark:bg-blue-900/20 @endif">
                                        <input type="checkbox" 
                                               wire:click="togglePaymentMethod('{{ $method }}')"
                                               @if(in_array($method, $paymentMethods)) checked @endif
                                               class="sr-only">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 border-2 rounded @if(in_array($method, $paymentMethods)) border-blue-500 bg-blue-500 @else border-gray-300 dark:border-gray-600 @endif flex items-center justify-center">
                                                @if(in_array($method, $paymentMethods))
                                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('paymentMethods')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Currency -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Currency
                                </label>
                                <select wire:model.defer="currency" 
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    @foreach($availableCurrencies as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tax Rate -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tax Rate (%)
                                </label>
                                <input type="number" 
                                       wire:model.defer="taxRate" 
                                       step="0.1" 
                                       min="0" 
                                       max="100" 
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>

                        <!-- Shipping Settings -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Shipping Settings
                                </label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="shippingEnabled" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Enable Shipping</span>
                                </label>
                            </div>
                            
                            @if($shippingEnabled)
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                        Default Shipping Cost
                                    </label>
                                    <input type="number" 
                                           wire:model.defer="defaultShippingCost" 
                                           step="0.01" 
                                           min="0" 
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            @elseif($currentStep == 4)
                <!-- Step 4: AI & Automation Settings -->
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">AI & Automation Settings</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Configure intelligent automation features to maximize your sales and customer experience.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <!-- AI Features -->
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-4">🤖 AI-Powered Features</h4>
                            <div class="space-y-3">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Smart Product Recommendations</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">AI suggests relevant products based on customer behavior</p>
                                    </div>
                                    <input type="checkbox" wire:model="aiRecommendationsEnabled" 
                                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                </label>

                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Abandoned Cart Recovery</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Automatic follow-up messages for incomplete orders</p>
                                    </div>
                                    <input type="checkbox" wire:model="abandonedCartEnabled" 
                                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                </label>

                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Smart Upselling & Cross-selling</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Intelligent product suggestions to increase order value</p>
                                    </div>
                                    <input type="checkbox" wire:model="upsellingEnabled" 
                                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                </label>
                            </div>
                        </div>

                        <!-- Message Templates -->
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-4">📱 Message Templates</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Order Confirmation Message
                                    </label>
                                    <textarea wire:model.defer="orderConfirmationMessage" 
                                              rows="3" 
                                              class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"
                                              placeholder="Message sent when order is confirmed..."></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Available variables: {order_number}, {customer_name}, {total_amount}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Payment Confirmation Message
                                    </label>
                                    <textarea wire:model.defer="paymentConfirmationMessage" 
                                              rows="3" 
                                              class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"
                                              placeholder="Message sent when payment is received..."></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Available variables: {order_number}, {customer_name}, {total_amount}, {payment_method}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-8 py-6">
                <div class="flex justify-between">
                    <button wire:click="previousStep" 
                            @if($currentStep == 1) disabled @endif
                            class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        ← Previous
                    </button>

                    <button wire:click="nextStep" 
                            onclick="console.log('Next button clicked, current step: {{ $currentStep }}')"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        @if($currentStep == $totalSteps)
                            🚀 Complete Setup
                        @else
                            Next →
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
