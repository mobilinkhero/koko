<x-app-layout>
    <x-slot:title>
        {{ t('stripe_payment_settings') }}
    </x-slot:title>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-0 sm:items-center justify-between">
                <div>
                    <h1 class="font-display text-3xl text-slate-900 dark:text-slate-200 font-medium">
                        {{ t('stripe_payment_settings') }}
                    </h1>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                        {{ t('configure_stripe_payments_description') }}
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
                <form id="stripe-settings-form" method="POST"
                    action="{{ route('admin.settings.payment.stripe.update') }}" x-data="{
                        stripeEnabled: {{ $settings->stripe_enabled ? 'true' : 'false' }},
                        webhookUrl: '{{ route('webhook.stripe') }}',
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
                                                <x-checkbox id="stripe_enabled" name="stripe_enabled"
                                                    :checked="$settings->stripe_enabled" x-model="stripeEnabled"
                                                    class="h-5 w-5 rounded border-gray-300 text-primary-600 transition duration-150 ease-in-out dark:border-gray-600 dark:bg-gray-700" />
                                                <x-label for="stripe_enabled" value="{{ t('enable_stripe_payments') }}"
                                                    class="ml-3 font-medium text-gray-900 dark:text-white" />
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ t('enable_stripe_payments_description') }}
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>

                                <!-- Basic Settings Card -->
                                <x-card>
                                    <x-slot:header>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ t('stripe_configuration') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('stripe_configuration_description') }}
                                        </p>
                                    </x-slot:header>
                                    <x-slot:content>
                                        <!-- Stripe Keys -->
                                        <div class="space-y-6">
                                            <!-- Publishable Key -->
                                            <div>
                                                <x-label for="stripe_key" :value="t('publishable_key')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('publishable_key_description') }}
                                                </p>
                                                <x-input id="stripe_key" name="stripe_key" type="text"
                                                    x-bind:disabled="!stripeEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->stripe_key" />
                                                <x-input-error for="stripe_key" class="mt-2" />
                                            </div>

                                            <!-- Secret Key -->
                                            <div>
                                                <x-label for="stripe_secret" :value="t('secret_key')"
                                                    class="text-base font-medium text-gray-900 dark:text-white" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ t('secret_key_description') }}
                                                </p>
                                                <x-input id="stripe_secret" name="stripe_secret" type="password"
                                                    x-bind:disabled="!stripeEnabled"
                                                    class="mt-2 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                    :value="$settings->stripe_secret" />
                                                <x-input-error for="stripe_secret" class="mt-2" />
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
                <div class="mt-6">
                    <livewire:admin.payment.manage-stripe-webhooks />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>