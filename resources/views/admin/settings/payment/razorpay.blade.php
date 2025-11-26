<x-app-layout>
    <x-slot:title>
        {{ t('razorpay_payment_settings') }}
    </x-slot:title>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-0 sm:items-center justify-between">
                <div>
                    <h1 class="font-display text-3xl text-slate-900 dark:text-slate-200 font-medium">
                        {{ t('razorpay_payment_settings') }}
                    </h1>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                        {{ t('configure_razorpay_payments_description') }}
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
                <form id="razorpay-settings-form" method="POST"
                    action="{{ route('admin.settings.payment.razorpay.update') }}" x-data="{
                        razorpayEnabled: {{ $settings->razorpay_enabled ? 'true' : 'false' }},
                        webhookUrl: '{{ route('webhook.razorpay') }}',
                        copyWebhookUrl() {                                                navigator.clipboard.writeText(this.webhookUrl)
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
                                                <x-checkbox id="razorpay_enabled" name="razorpay_enabled"
                                                    :checked="$settings->razorpay_enabled" x-model="razorpayEnabled"
                                                    class="h-5 w-5 rounded border-gray-300 text-primary-600 transition duration-150 ease-in-out dark:border-gray-600 dark:bg-gray-700" />
                                                <x-label for="razorpay_enabled"
                                                    value="{{ t('enable_razorpay_payments') }}"
                                                    class="ml-3 font-medium text-gray-900 dark:text-white" />
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ t('enable_razorpay_payments_description') }}
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                                <!-- Basic Settings Card -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('razorpay_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('razorpay_configuration_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <!-- Razorpay Keys -->
                                        <div class="space-y-6">
                                            <!-- Key ID -->
                                            <div>
                                                <x-label for="razorpay_key_id" :value="t('razorpay_key_id')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('razorpay_key_id_description') }}
                                                </p>
                                                <x-input id="razorpay_key_id" name="razorpay_key_id" type="text"
                                                    x-bind:disabled="!razorpayEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->razorpay_key_id" />
                                                <x-input-error for="razorpay_key_id" class="mt-2" />
                                            </div>

                                            <!-- Key Secret -->
                                            <div>
                                                <x-label for="razorpay_key_secret" :value="t('razorpay_key_secret')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('razorpay_key_secret_description') }}
                                                </p>
                                                <x-input id="razorpay_key_secret" name="razorpay_key_secret"
                                                    type="password" x-bind:disabled="!razorpayEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->razorpay_key_secret" />
                                                <x-input-error for="razorpay_key_secret" class="mt-2" />
                                            </div>

                                            <!-- Webhook Secret -->
                                            <div>
                                                <x-label for="razorpay_webhook_secret"
                                                    :value="t('razorpay_webhook_secret')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('razorpay_webhook_secret_description') }}
                                                </p>
                                                <x-input id="razorpay_webhook_secret" name="razorpay_webhook_secret"
                                                    type="password" x-bind:disabled="!razorpayEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->razorpay_webhook_secret" />
                                                <x-input-error for="razorpay_webhook_secret" class="mt-2" />
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Webhook Configuration -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('webhook_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('razorpay_webhook_configuration_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <div
                                            class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg p-4">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-info-400 mt-0.5 mr-3 flex-shrink-0"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4
                                                        class="text-sm font-medium text-info-800 dark:text-info-200 mb-2">
                                                        {{ t('webhook_setup_instructions') }}
                                                    </h4>
                                                    <p class="text-sm text-info-700 dark:text-info-300 mb-3">
                                                        {{ t('razorpay_webhook_setup_description') }}
                                                    </p>
                                                    <div
                                                        class="bg-white dark:bg-gray-800 rounded border p-3 font-mono text-sm break-all">
                                                        <span x-text="webhookUrl"></span>
                                                        <button type="button" @click="copyWebhookUrl()"
                                                            class="ml-2 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-info-700 bg-info-100 hover:bg-info-200 dark:text-info-400 dark:bg-info-900 dark:hover:bg-info-800">
                                                            {{ t('copy') }}
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-info-600 dark:text-info-400 mt-2">
                                                        {{ t('razorpay_webhook_events_note') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            </div>
                        </x-slot:content>

                        <!-- Form Actions -->
                        @if(checkPermission('admin.payment_settings.edit'))
                        <x-slot:footer class="bg-gray-50 dark:bg-transparent px-6 py-3">
                            <div class="flex justify-end">
                                <x-button.primary type="submit">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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