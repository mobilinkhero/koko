<x-app-layout>
    <x-slot:title>
        {{ t('upgrade_subscription') }}
    </x-slot:title>
    <div class="max-w-6xl mx-auto">
        <x-card>
            <!-- Enhanced Header Section -->
            <x-slot:header>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-primary-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                {{ t('upgrade_subscription') }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                {{ t('select_new_plan_below') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-start px-5 lg:px-0 items-center gap-2">
                        <x-button.secondary
                            href="{{ tenant_route('tenant.subscriptions.show', ['id' => $subscription->id]) }}">
                            <x-heroicon-o-arrow-small-left class="w-4 h-4 mr-1" />{{ t('back_to_subscription') }}
                        </x-button.secondary>
                    </div>
                </div>
            </x-slot:header>
            <x-slot:content>


                <div class="mb-8">
                    <div class="bg-primary-50 dark:bg-primary-900/20 border-l-4 border-primary-500 p-5 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-information-circle
                                    class="h-6 w-6 text-primary-500 dark:text-primary-400" />
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-primary-800 dark:text-primary-200">{{
                                    t('current_plan') }}
                                </h3>
                                <p class="mt-1 text-sm text-primary-700 dark:text-primary-300">
                                    {{ t('you_are_currently_on_the') }}<strong class="dark:text-white">{{
                                        $subscription->plan->name }}</strong> {{ t('upgrade_plan_desc') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('error'))
                <div class="rounded-md bg-danger-50 dark:bg-danger-900/20 p-4 mb-6">
                    <div class="flex">
                        <x-heroicon-o-exclamation-circle class="h-5 w-5 text-danger-400 dark:text-danger-300" />
                        <div class="ml-3">
                            <p class="text-sm font-medium text-danger-800 dark:text-danger-200">{{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if (session('success'))
                <div class="rounded-md bg-success-50 dark:bg-success-900/20 p-4 mb-6">
                    <div class="flex">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-success-400 dark:text-success-300" />
                        <div class="ml-3">
                            <p class="text-sm font-medium text-success-800 dark:text-success-200">
                                {{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($plans->isEmpty())
                <div class="rounded-md bg-warning-50 dark:bg-warning-900/20 p-4">
                    <div class="flex">
                        <x-heroicon-o-exclamation-circle class="h-5 w-5 text-warning-400 dark:text-warning-300" />
                        <div class="ml-3">
                            <p class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                {{ t('no_higher_tier_plans') }}
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <div
                    class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-heroicon-o-light-bulb class="w-5 h-5 mr-2 text-primary-500" />
                        {{ t('before_you_upgrade') }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-credit-card class="h-5 w-5 text-primary-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('immediate_payment')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('payment_required_active_plan') }}
                            </p>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-currency-dollar class="h-5 w-5 text-primary-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('prorated_billing')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('credit_applied_unused_time') }}
                            </p>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-arrows-right-left class="h-5 w-5 text-primary-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('instant_switch')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('features_available_after_payment')
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($plans as $plan)
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden h-full min-h-[600px] flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold text-gray-700 dark:text-white">
                                    {{ $plan->name }}</h3>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200">
                                    {{ t('available') }}
                                </span>
                            </div>

                            <div class="flex items-baseline justify-center my-8 gap-2">
                                <span
                                    class="text-5xl font-medium text-primary-600 dark:text-primary-400 flex items-baseline gap-1">
                                    <span class="text-3xl">{{ get_base_currency()->symbol }}</span>
                                    <span>{{ number_format($plan->price, 2) }}</span>
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">/{{ ucfirst($plan->billing_period)
                                    }}</span>
                            </div>

                            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $plan->description }}</p>

                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-700 dark:text-white flex items-center">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2 text-primary-500" />
                                    {{ t('plan_features') }}
                                </h4>
                                <ul class="space-y-3">
                                    @foreach ($plan->features ?? [] as $feature)
                                    @if ($feature->value != 0)
                                    <li class="flex items-start">
                                        <x-heroicon-o-check class="h-5 w-5 text-success-500 mr-2" />
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-medium">{{ t($feature->slug) }}:</span>
                                            {{ $feature->value == '-1' ? 'Unlimited' : number_format($feature->value) }}
                                        </span>
                                    </li>
                                    @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                            <form method="POST"
                                action="{{ tenant_route('tenant.subscriptions.upgrade.process', ['id' => $subscription->id]) }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                                @php
                                $pendingPlanChange = $subscription->getUnpaidChangePlanInvoice();
                                @endphp

                                @if ($pendingPlanChange)
                                <div class="mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="override_existing" value="1"
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ t('override_pending_plan_change') }}
                                        </span>
                                    </label>
                                </div>
                                @endif

                                <x-button.primary type="submit" class="w-full">
                                    <x-heroicon-o-arrow-up-circle class="w-5 h-5 mr-2" />
                                    {{ t('upgrade_to') }} {{ $plan->name }}
                                </x-button.primary>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-slot:content>
        </x-card>
    </div>
</x-app-layout>