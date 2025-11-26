<x-app-layout>
    <x-slot:title>
        {{ t('setup_auto_billing') }} - {{ t('razorpay') }}
    </x-slot:title>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-info-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-info-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('setup_auto_billing') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('setup_razorpay_auto_billing_description') }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
                <div class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-info-100 rounded-full flex items-center justify-center mb-4">
                        <x-heroicon-o-information-circle class="w-8 h-8 text-info-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-300 mb-2">
                        {{ t('auto_billing_setup_required') }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                        {{ t('razorpay_auto_billing_info') }}
                    </p>

                    <div
                        class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-info-600" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-info-800 dark:text-info-200">
                                    {{ t('razorpay_rbi_compliance') }}
                                </h3>
                                <div class="mt-2 text-sm text-info-700 dark:text-info-300">
                                    <p>{{ t('razorpay_authentication_requirement') }}</p>
                                    <p class="mt-2">{{ t('razorpay_auto_billing_steps') }}</p>
                                    <ul class="list-disc pl-5 mt-2 space-y-1">
                                        <li>{{ t('razorpay_step1_save_payment') }}</li>
                                        <li>{{ t('razorpay_step2_approve_each_payment') }}</li>
                                        <li>{{ t('razorpay_step3_check_email') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-warning-700 dark:text-warning-300">
                                    {{ t('razorpay_important_notice') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ $returnUrl ?? tenant_route('tenant.subscription.dashboard') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-info-600 hover:bg-info-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500">
                            {{ t('return_to_dashboard') }}
                        </a>
                        <a href="{{ tenant_route('tenant.tickets.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            {{ t('contact_support') }}
                        </a>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</x-app-layout>