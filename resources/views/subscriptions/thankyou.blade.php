<x-app-layout>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl shadow-xl overflow-hidden">
            <!-- Success Banner -->
            <div class="bg-gradient-to-r from-primary-500 to-purple-600 p-8">
                <div class="flex flex-col items-center justify-center">
                    <!-- Using Heroicon properly -->
                    <x-heroicon-o-check-badge class="w-20 h-20 text-gray-200" />

                    <h1 class="mt-4 text-3xl font-extrabold text-gray-200 tracking-tight">
                        {{ t('payment_successful') }}!
                    </h1>

                    <p class="mt-2 text-primary-100">
                        {{ t('subscription_activated_successfully') }}
                    </p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-6 sm:p-8">
                <!-- Summary Section -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">{{ t('thank_you_for_your_purchase') }}</h2>
                    <p class="mt-2 text-gray-600">
                        {{ t('received_your_payment') }}
                    </p>
                </div>

                <!-- Subscription Details -->
                @if (isset($invoice))
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ t('purchase_details') }}</h3>
                        <!-- Document Icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if (isset($invoice->subscription) && isset($invoice->subscription->plan))
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ t('plan_name') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $invoice->subscription->plan->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ t('billing_period') }}</p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $invoice->subscription->plan->billing_period }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ t('start_date') }}</p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $invoice->subscription->created_at->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ t('next_billing_date') }}</p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $invoice->subscription->current_period_ends_at->format('F j, Y') }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ t('invoice_number') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $invoice->invoice_number ??
                                format_draft_invoice_number() }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ t('amount_paid') }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $invoice->formattedTotal() }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Subscription Features -->
                @if (isset($invoice->subscription) &&
                isset($invoice->subscription->plan) &&
                isset($invoice->subscription->plan->features) &&
                is_array($invoice->subscription->plan->features) &&
                count($invoice->subscription->plan->features) > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ t('your_plan_includes') }}</h3>
                    <ul class="space-y-3">
                        @foreach ($invoice->subscription->plan->features as $feature)
                        <li class="flex items-start">
                            <!-- Check Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white mr-2 flex-shrink-0 mt-0.5"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-700">{{ t($feature) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Download Invoice -->
                @if (isset($invoice) && isset($invoice->uid))
                <div class="border border-gray-200 rounded-lg p-6 mb-8 flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Invoice Icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-500 mr-4"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ t('download_your_invoice') }}</p>
                            <p class="text-sm text-gray-500">{{ t('records_accounting_purposes') }}</p>
                        </div>
                    </div>
                    <x-button.primary href="{{ route('invoices.download', $invoice->uid) }}">
                        {{ t('download_pdf') }}
                    </x-button.primary>
                </div>
                @endif

                <!-- Next Steps -->
                <div class="bg-primary-50 rounded-lg p-6 mb-8">
                    <div class="flex">
                        <!-- Lightbulb Icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-500 mr-3"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5">
                            </path>
                            <path d="M9 18h6"></path>
                            <path d="M10 22h4"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-primary-900">{{ t('what_next') }}</h3>
                            <p class="mt-2 text-sm text-primary-700">
                                {{ t('subscription_active') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
                    <x-button.primary href="{{ tenant_route('tenant.dashboard') }}" class="flex-1">
                        <!-- Dashboard Icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        {{ t('go_to_dashboard') }}
                    </x-button.primary>
                    @if (isset($invoice->subscription) && isset($invoice->subscription->id))
                    <x-button.secondary
                        href="{{ tenant_route('tenant.subscriptions.show', ['id' => $invoice->subscription->id]) }}"
                        class="flex-1">
                        <!-- Settings Icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path
                                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                            </path>
                        </svg>
                        {{ t('manage_subscription') }}
                    </x-button.secondary>
                    @else
                    <x-button.secondary href="{{ tenant_route('tenant.subscriptions.index') }}" class="flex-1">
                        <!-- Settings Icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path
                                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                            </path>
                        </svg>
                        {{ t('your_subscriptions') }}
                    </x-button.secondary>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-center">
                    <!-- Support Icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                        </path>
                    </svg>
                    <span class="text-sm text-gray-500">Need help? <a href="#"
                            class="text-primary-600 hover:text-primary-500">{{ t('contact_our_support_team')
                            }}</a></span>
                </div>
            </div>
        </div>

        <!-- Email Confirmation -->
        @if (isset($invoice->subscription) &&
        isset($invoice->subscription->customer) &&
        isset($invoice->subscription->customer->user))
        <div class="mt-8 text-center">
            <div class="flex justify-center mb-2">
                <!-- Email Icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <p class="text-sm text-gray-500">
                {{ t('confirmation_email_sent_to') }} {{ $invoice->subscription->customer->user->email }}.
                {{ t('please_check_your_inbox') }}
            </p>
        </div>
        @else
        <div class="mt-8 text-center">
            <div class="flex justify-center mb-2">
                <!-- Email Icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <p class="text-sm text-gray-500">
                {{ t('confirmation_email_sent') }}
            </p>
        </div>
        @endif
    </div>
</x-app-layout>