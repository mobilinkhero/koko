<x-app-layout>
    <x-slot:title>
        {{ t('paypal_payment_settings') }}
    </x-slot:title>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-0 sm:items-center justify-between">
                <div>
                    <h1 class="font-display text-3xl text-slate-900 dark:text-slate-200 font-medium">
                        {{ t('paypal_payment_settings') }}
                    </h1>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                        {{ t('configure_paypal_payments_description') }}
                    </p>
                </div>
                <x-button.secondary type="button" onclick="history.back()">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                    {{ t('back') }}
                </x-button.secondary>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-900 mt-6">
            <div class="max-w-7xl mx-auto">
                <!-- Main Content -->
                <form id="paypal-settings-form" method="POST"
                    action="{{ route('admin.settings.payment.paypal.update') }}"
                    x-data="{
                        paypalEnabled: {{ $settings->paypal_enabled ? 'true' : 'false' }},
                        webhookUrl: '{{ url('/webhooks/paypal') }}',
                        copyWebhookUrl() {
                            navigator.clipboard.writeText(this.webhookUrl)
                                .then(() => {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: {
                                            type: 'success',
                                            message: '{{ t('webhook_url_copied') }}'
                                        }
                                    }));
                                })
                                .catch(() => {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: {
                                            type: 'error',
                                            message: '{{ t('copy_failed') }}'
                                        }
                                    }));
                                });
                        }
                    }">
                    @csrf
                    <x-card>
                        <x-slot:content>
                            <div class="space-y-8">
                                <!-- Enable/Disable Section -->
                                <x-card>
                                    <x-slot:content>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <x-checkbox id="paypal_enabled" name="paypal_enabled" :checked="$settings->paypal_enabled"
                                                    x-model="paypalEnabled"
                                                    class="h-5 w-5 rounded border-gray-300 text-primary-600 transition duration-150 ease-in-out dark:border-gray-600 dark:bg-gray-700" />
                                                <x-label for="paypal_enabled" value="{{ t('enable_paypal_payments') }}"
                                                    class="ml-3 font-medium text-gray-900 dark:text-white" />
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ t('enable_paypal_payments_description') }}
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Basic Settings Card -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('paypal_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('paypal_configuration_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <div class="space-y-6">
                                            <!-- PayPal Mode -->
                                            <div x-data="{
                                                isLiveMode: {{ $settings->paypal_mode === 'live' ? 'true' : 'false' }},
                                                updateMode() {
                                                    $refs.paypalModeInput.value = this.isLiveMode ? 'live' : 'sandbox';
                                                }
                                            }" x-init="updateMode()">
                                                <x-label for="paypal_mode" :value="t('paypal_mode')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('toggle_between_sandbox_and_live_mode') }}
                                                </p>
                                                <div class="mt-3 flex items-center space-x-4">
                                                    <span class="text-sm font-medium"
                                                        :class="!isLiveMode ? 'text-gray-900 dark:text-white' :
                                                            'text-gray-500 dark:text-gray-400'">
                                                        {{ t('sandbox') }}
                                                    </span>

                                                    <!-- Custom Toggle Implementation -->
                                                    <label class="relative inline-flex items-center cursor-pointer mt-2 group">
                                                        <input type="checkbox" x-model="isLiveMode"
                                                            x-bind:disabled="!paypalEnabled"
                                                            @change="updateMode()"
                                                            class="sr-only peer">
                                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer transition-all duration-300 ease-in-out
                                                            peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 peer-focus:ring-opacity-50
                                                            dark:peer-focus:ring-primary-800 dark:bg-gray-700 dark:border-gray-600
                                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                                            after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                                                            after:bg-white after:border-gray-300 after:border after:rounded-full
                                                            after:h-5 after:w-5 after:transition-all after:duration-300 after:ease-in-out
                                                            after:shadow-md hover:after:shadow-lg
                                                            peer-checked:bg-primary-600 peer-checked:shadow-lg
                                                            hover:bg-gray-300 dark:hover:bg-gray-600
                                                            peer-checked:hover:bg-primary-700
                                                            group-hover:scale-105 transform transition-transform duration-200">
                                                        </div>
                                                    </label>

                                                    <span class="text-sm font-medium"
                                                        :class="isLiveMode ? 'text-gray-900 dark:text-white' :
                                                            'text-gray-500 dark:text-gray-400'">
                                                        {{ t('live') }}
                                                    </span>
                                                </div>
                                                <input type="hidden" name="paypal_mode" x-ref="paypalModeInput"
                                                    value="{{ $settings->paypal_mode }}">
                                            </div>

                                            <!-- PayPal Keys -->
                                            <div class="space-y-6">
                                                <!-- Client ID -->
                                                <div>
                                                    <x-label for="paypal_client_id" :value="t('paypal_client_id')"
                                                        class="text-base font-medium text-gray-900 dark:text-white" />
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        {{ t('paypal_client_id_description') }}
                                                    </p>
                                                    <x-input id="paypal_client_id" name="paypal_client_id"
                                                        type="text" x-bind:disabled="!paypalEnabled"
                                                        class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        :value="$settings->paypal_client_id" />
                                                    <x-input-error for="paypal_client_id" class="mt-2" />
                                                </div>

                                                <!-- Client Secret -->
                                                <div>
                                                    <x-label for="paypal_client_secret" :value="t('paypal_client_secret')"
                                                        class="text-base font-medium text-gray-900 dark:text-white" />
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        {{ t('paypal_client_secret_description') }}
                                                    </p>
                                                    <x-input id="paypal_client_secret" name="paypal_client_secret"
                                                        type="password" x-bind:disabled="!paypalEnabled"
                                                        class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        :value="$settings->paypal_client_secret" />
                                                    <x-input-error for="paypal_client_secret" class="mt-2" />
                                                </div>
                                            </div>

                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Webhook Settings -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('webhook_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('paypal_webhook_setup_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <div class="">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ t('webhook_url') }}:
                                            </p>
                                            <div class="mt-2 flex">
                                                <x-input id="webhook_url" type="text" readonly x-model="webhookUrl"
                                                    class="block flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                                <button type="button" @click="copyWebhookUrl()"
                                                    class="ml-2 inline-flex items-center px-2 border border-transparent text-xs font-medium rounded text-info-700 bg-info-100 hover:bg-info-200 dark:text-info-400 dark:bg-info-900 dark:hover:bg-info-800">
                                                    {{ t('copy') }}
                                                </button>
                                            </div>

                                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                                                {{ t('paypal_webhook_events_note') }}
                                            </p>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            </div>
                        </x-slot:content>

                        <!-- Form Actions -->
                        @if (checkPermission('admin.payment_settings.edit'))
                            <x-slot:footer class="bg-gray-50 dark:bg-transparent px-6 py-3">
                                <div class="flex justify-end">
                                    <x-button.primary type="submit">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ t('save_settings') }}
                                    </x-button.primary>
                                </div>
                            </x-slot:footer>
                        @endif
                    </x-card>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
