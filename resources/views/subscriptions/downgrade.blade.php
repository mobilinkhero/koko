<x-app-layout>
    <x-slot:title>
        {{ t('downgrade_subscription') }}
    </x-slot:title>

    <div class="max-w-6xl mx-auto ">
        <x-card>
            <x-slot:header>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-arrow-trending-down class="w-6 h-6 text-primary-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                {{ t('downgrade_subscription') }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                {{ t('select_new_plan_to_downgrade') }}
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
                    <div class="bg-warning-50 border-l-4 border-warning-500 p-5 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-information-circle class="h-6 w-6 text-warning-500" />
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-warning-800">{{ t('current_plan') }}</h3>
                                <p class="mt-1 text-sm text-warning-700">
                                    {{ t('you_are_currently') }} <strong>{{ $subscription->plan->name }}</strong> {{
                                    t('plan') }}
                                    {{ t('select_to_downgrade_plan') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('error'))
                <div class="rounded-md bg-danger-50 p-4 mb-6">
                    <div class="flex">
                        <x-heroicon-o-exclamation-circle class="h-5 w-5 text-danger-400" />
                        <div class="ml-3">
                            <p class="text-sm font-medium text-danger-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if (session('success'))
                <div class="rounded-md bg-success-50 p-4 mb-6">
                    <div class="flex">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-success-400" />
                        <div class="ml-3">
                            <p class="text-sm font-medium text-success-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if ($plans->isEmpty())
                <div class="rounded-md bg-warning-50 p-4">
                    <div class="flex">
                        <x-heroicon-o-exclamation-circle class="h-5 w-5 text-warning-400" />
                        <div class="ml-3">
                            <p class="text-sm font-medium text-warning-800">
                                {{ t('no_lower_tier_plans') }}
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <div
                    class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-heroicon-o-light-bulb class="w-5 h-5 mr-2 text-warning-500" />
                        {{ t('before_you_downgrade') }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-clock class="h-5 w-5 text-warning-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('immidiate_effect')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('changes_apply_after_confirmation')
                                }}</p>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-currency-dollar class="h-5 w-5 text-warning-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('prorated_credit')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('receive_credit_for_unused_time')
                                }}</p>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-calendar class="h-5 w-5 text-warning-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('billing_reset')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('billing_cycle_starts_fresh') }}
                            </p>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-warning-500" />
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ t('feature_access')
                                    }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('some_features_maybe_unavailable')
                                }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($plans as $plan)
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden h-full min-h-[600px] flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $plan->name }}</h3>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900 text-warning-800 dark:text-warning-200">
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
                                <h4 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2 text-warning-500" />
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
                                action="{{ tenant_route('tenant.subscriptions.downgrade.process', ['id' => $subscription->id]) }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                                @php
                                $pendingPlanChange = $subscription->getUnpaidChangePlanInvoice();
                                @endphp

                                @if ($pendingPlanChange)
                                <div class="mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="override_existing" value="1"
                                            class="rounded border-gray-300 text-warning-600 shadow-sm focus:border-warning-300 focus:ring focus:ring-warning-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ t('override_pending_plan_change') }}
                                        </span>
                                    </label>
                                </div>
                                @endif

                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-warning-600 hover:bg-warning-700 dark:bg-warning-500 dark:hover:bg-warning-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500 dark:focus:ring-offset-gray-900">
                                    <x-heroicon-o-arrow-down-circle class="w-5 h-5 mr-2" />
                                    {{ t('downgrade_to') }} {{ $plan->name }}
                                </button>
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