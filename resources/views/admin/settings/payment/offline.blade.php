<x-app-layout>
    <x-slot:title>
        {{ t('offline_payment_settings') }}
    </x-slot:title>
    <div>
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-0 sm:items-center justify-between">
                    <div>
                        <h1 class="font-display text-3xl text-slate-900 dark:text-slate-200 font-medium">
                            {{ t('offline_payment_settings') }}
                        </h1>
                        <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                            {{ t('configure_tenants_offline_payment_methods') }}
                        </p>
                    </div>
                    <x-button.secondary type="button" onclick="history.back()">
                        <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                        {{ t('back') }}
                    </x-button.secondary>
                </div>
            </div>

            <!-- Main Content -->
            <form method="POST" action="{{ route('admin.settings.payment.offline.update') }}">
                @csrf
                @method('PUT')
                <x-card>
                    <x-slot:content>
                        <div class="space-y-6">
                            <!-- Enable/Disable Section -->
                            @if(checkPermission('admin.payment_settings.edit'))
                                <x-card>
                                    <x-slot:content>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <x-checkbox id="offline_enabled" name="offline_enabled" :checked="$settings->offline_enabled"
                                                    class="h-5 w-5 rounded border-gray-300 text-primary-600 transition duration-150 ease-in-out dark:border-gray-600 dark:bg-gray-700" />
                                                <x-label for="offline_enabled" value="{{ t('enable_offline_payments') }}"
                                                    class="ml-3 font-medium text-gray-900 dark:text-white" />
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ t('allow_tenants_to_pay') }}
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            @endif

                            <!-- Basic Settings Card -->
                            <x-card>
                                <x-slot:header>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ t('basic_information') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('set_up_the_basic_info') }}
                                    </p>
                                </x-slot:header>
                                <x-slot:content>
                                    <!-- Description -->
                                    <div>
                                        <x-label for="offline_description" value="{{ t('description') }}"
                                            class="text-base font-medium text-gray-900 dark:text-white" />
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('brief_description') }}
                                        </p>
                                        <x-textarea id="offline_description" name="offline_description"
                                            rows="3">{{ old('offline_description', $settings->offline_description) }}</x-textarea>
                                        <x-input-error for="offline_description" class="mt-1" />
                                    </div>

                                    <!-- Payment Instructions -->
                                    <div class="mt-6">
                                        <x-label for="offline_instructions" value="{{ t('payment_instructions') }}"
                                            class="text-base font-medium text-gray-900 dark:text-white" />
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('detailed_instructions_payment') }}
                                        </p>
                                        <x-textarea id="offline_instructions" name="offline_instructions" rows="5">{{ old('offline_instructions', $settings->offline_instructions) }}</x-textarea>
                                        <x-input-error for="offline_instructions" class="mt-1" />
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
</x-app-layout>
