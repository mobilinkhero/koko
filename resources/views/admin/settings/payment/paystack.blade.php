<x-app-layout>
    <x-slot:title>
        {{ t('paystack_payment_settings') }}
    </x-slot:title>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-0 sm:items-center justify-between">
                <div>
                    <h1 class="font-display text-3xl text-slate-900 dark:text-slate-200 font-medium">
                        {{ t('paystack_payment_settings') }}
                    </h1>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                        {{ t('configure_paystack_payments_description') }}
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
                <form id="paystack-settings-form" method="POST"
                    action="{{ route('admin.settings.payment.paystack.update') }}" x-data="{
                        paystackEnabled: {{ $settings->paystack_enabled ? 'true' : 'false' }},
                        webhookUrl: '{{ route('webhook.paystack') }}',
                        copyWebhookUrl() {
                            navigator.clipboard.writeText(this.webhookUrl)
                                .then(() => {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: {
                                            type: 'success',
                                            message: '{{ t('webhook_url_copied')}}'
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
                                                <x-checkbox id="paystack_enabled" name="paystack_enabled"
                                                    :checked="$settings->paystack_enabled" x-model="paystackEnabled"
                                                    class="h-5 w-5 rounded border-gray-300 text-primary-600 transition duration-150 ease-in-out dark:border-gray-600 dark:bg-gray-700" />
                                                <x-label for="paystack_enabled" value="{{ t('enable_paystack_payments') }}"
                                                    class="ml-3 font-medium text-gray-900 dark:text-white" />
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ t('enable_paystack_payments_description') }}
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Basic Settings Card -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('paystack_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('paystack_configuration_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <!-- Paystack Keys -->
                                        <div class="space-y-6">
                                            <!-- Public Key -->
                                            <div>
                                                <x-label for="paystack_public_key" :value="t('public_key')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('paystack_public_key_description') }}
                                                </p>
                                                <x-input id="paystack_public_key" name="paystack_public_key" type="text"
                                                    x-bind:disabled="!paystackEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->paystack_public_key"
                                                    placeholder="pk_test_..." />
                                                <x-input-error for="paystack_public_key" class="mt-2" />
                                            </div>

                                            <!-- Secret Key -->
                                            <div>
                                                <x-label for="paystack_secret_key" :value="t('secret_key')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('paystack_secret_key_description') }}
                                                </p>
                                                <x-input id="paystack_secret_key" name="paystack_secret_key" type="password"
                                                    x-bind:disabled="!paystackEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->paystack_secret_key"
                                                    placeholder="sk_test_..." />
                                                <x-input-error for="paystack_secret_key" class="mt-2" />
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Webhook Configuration Card -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('webhook_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('webhook_configuration_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <div class="space-y-4">
                                            <!-- Webhook URL -->
                                            <div>
                                                <x-label for="webhook_url" :value="t('webhook_url')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('paystack_webhook_url_description') }}
                                                </p>
                                                <div class="mt-2 flex">
                                                    <x-input id="webhook_url" type="text" readonly
                                                        class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm"
                                                        x-bind:value="webhookUrl" />
                                                    <button type="button" @click="copyWebhookUrl()"
                                                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-lg bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                        <x-heroicon-o-clipboard class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Webhook Events -->
                                            <div class="mt-4">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                                    {{ t('webhook_events') }}
                                                </h4>
                                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                        {{ t('configure_these_events') }}
                                                    </p>
                                                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                                        <li>• <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">charge.success</code> - {{ t('successful_payment') }}</li>
                                                        <li>• <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">charge.failed</code> - {{ t('failed_payment') }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Supported Features Card -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('supported_features') }}
                                        </h3>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ t('credit_card_payments') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ t('bank_transfer_payments') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ t('mobile_money_payments') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ t('ussd_payments') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ t('qr_code_payments') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ t('multi_currency_support') }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                            <div class="flex items-start space-x-3">
                                                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5" />
                                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                                    <p class="font-medium">{{ t('supported_currencies') }}</p>
                                                    <p class="mt-1">{{ t('paystack_supported_currencies_list') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <x-button.secondary type="button" onclick="history.back()">
                                        {{ t('cancel') }}
                                    </x-button.secondary>
                                    <x-button.primary type="submit">
                                        <x-heroicon-o-check class="w-4 h-4 mr-2" />
                                        {{ t('save_settings') }}
                                    </x-button.primary>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
