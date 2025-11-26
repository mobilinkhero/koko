<div class="space-y-6" x-data="{ activeTab: 'stripe' }">
    <!-- Webhook Configuration Card -->
    <div
        class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                {{ t('payment_gateway_webhooks') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ t('configure_webhook_real_time_notification') }}
            </p>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6">
            <nav class="-mb-px flex space-x-6 overflow-x-auto py-2 scrollbar-hide" aria-label="Webhook Gateways">
                @foreach ($webhooks as $gateway => $config)
                <button @click="activeTab = '{{ $gateway }}'"
                    :class="activeTab === '{{ $gateway }}' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap px-4 py-3 border-b-2 font-medium text-sm focus:outline-none focus:ring-0 transition-colors duration-200 flex items-center"
                    :aria-current="activeTab === '{{ $gateway }}' ? 'page' : 'false'">

                    <!-- Gateway Icon (You can customize these based on the gateway) -->
                    @if ($gateway === 'stripe')
                    <svg class="mr-2 h-5 w-5"
                        :class="activeTab === '{{ $gateway }}' ? 'text-primary-500' : 'text-gray-400'"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.5 4.5L9.5 19.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M6.5 8.5L19.5 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M4.5 15.5L17.5 15.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    @else
                    <svg class="mr-2 h-5 w-5"
                        :class="activeTab === '{{ $gateway }}' ? 'text-primary-500' : 'text-gray-400'"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3 10H21M7 15H8M12 15H13M6 19H18C19.6569 19 21 17.6569 21 16V8C21 6.34315 19.6569 5 18 5H6C4.34315 5 3 6.34315 3 8V16C3 17.6569 4.34315 19 6 19Z"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    @endif

                    <!-- Gateway Name -->
                    <span>{{ ucfirst($gateway) }}</span>

                    <!-- Configuration Status Badge -->
                    @if ($config['is_configured'])
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300">
                        <svg class="mr-0.5 h-2 w-2 text-success-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        {{ t('configured') }}
                    </span>
                    @else
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ t('not_configured') }}
                    </span>
                    @endif
                </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            @foreach ($webhooks as $gateway => $config)
            <div x-cloak x-show="activeTab === '{{ $gateway }}'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" class="space-y-6">
                <div class="space-y-4">
                    <!-- Webhook URL -->
                    @if ($gateway !== 'stripe')
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                        <label for="{{ $gateway }}_webhook_url"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{
                            t('webhook_endpoint_url') }}</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <div class="relative flex items-stretch flex-grow">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 text-sm">
                                    URL
                                </span>
                                <input type="text" id="{{ $gateway }}_webhook_url" readonly
                                    value="{{ $config['webhook_url'] }}"
                                    class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white cursor-default">
                            </div>
                            <button type="button" x-data="{ copySuccess: false }" @click="
                                        navigator.clipboard.writeText('{{ $config['webhook_url'] }}');
                                        copySuccess = true;
                                        $wire.copyToClipboard('{{ $config['webhook_url'] }}');
                                        setTimeout(() => copySuccess = false, 1500);
                                    "
                                class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    :class="copySuccess ? 'text-success-500' : 'text-gray-400'" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path x-show="!copySuccess" d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                    <path x-show="!copySuccess"
                                        d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                    <path x-show="copySuccess" fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span x-text="copySuccess ? '{{ t('copied') }}' : '{{ t('copy') }}'"></span>
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-info-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ t('use_this_url_in_your_gateway_webhook_settings') }}
                        </p>
                    </div>
                    @endif

                    <!-- Gateway-specific instructions -->
                    @if ($gateway === 'stripe')
                    <livewire:admin.payment.manage-stripe-webhooks />
                    @endif

                    @if ($gateway !== 'stripe')
                    <!-- Webhook Secret Configuration -->
                    <div class="pt-5 border-t border-gray-200 dark:border-gray-700">
                        @if ($regeneratingSecret)
                        <div x-show="activeTab === '{{ $gateway }}'"
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <label for="{{ $gateway }}_webhook_secret"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                t('new_webhook_secret') }}</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                </span>
                                <input type="text" id="{{ $gateway }}_webhook_secret" wire:model="newSecret" readonly
                                    class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                <svg class="w-4 h-4 mr-1 text-warning-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                {{ t('copy_this_secret_and_use_it_in_your_gateway_settings') }}
                            </p>
                            <div class="mt-4 flex space-x-3">
                                <x-button.primary type="button" wire:click="saveWebhookSecret('{{ $gateway }}')">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ t('save_secret') }}
                                </x-button.primary>
                                <x-button.secondary type="button" wire:click="cancelRegenerateSecret">
                                    {{ t('cancel') }}
                                </x-button.secondary>
                            </div>
                        </div>
                        @else
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3
                                        class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                                        <svg class="w-5 h-5 mr-1.5 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                        {{ t('webhook_secret') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('secure_your_webhooks_with_a_secret_token_to_verify_requests') }}
                                    </p>
                                </div>
                                <button type="button" wire:click="regenerateWebhookSecret('{{ $gateway }}')"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    @if ($config['is_configured'])
                                    {{ t('regenerate_secret') }}
                                    @else
                                    {{ t('generate_secret') }}
                                    @endif
                                </button>
                            </div>

                            @if ($config['is_configured'])
                            <div class="mt-4 px-4 py-3 sm:px-6 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-success-400 dark:text-success-300"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{
                                        t('webhook_secret_is_configured') }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ t('webhook_secret_is_stored_securely_and_not_displayed_for_security_reasons') }}
                                </p>
                            </div>
                            @else
                            <div
                                class="mt-4 px-4 py-3 sm:px-6 bg-warning-50 dark:bg-warning-900/30 rounded-md border border-warning-100 dark:border-warning-800">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-2 text-sm text-warning-800 dark:text-warning-300">{{
                                        t('webhook_secret_not_configured') }}</span>
                                </div>
                                <p class="mt-1 text-xs text-warning-700 dark:text-warning-400">
                                    {{ t('we_recommend_generating_a_webhook_secret_to_secure_your_payment_events') }}
                                </p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>