<div x-data="{
    billingPeriod: '{{ $defaultBillingPeriod }}',
}">
    <x-slot:title>
        {{ t('my_subscription') }}
    </x-slot:title>
    <div class=" px-4 mx-auto lg:px-6 text-center">
        @if($showBillingToggle)
        <!-- Billing Period Toggle -->
        <div class="flex justify-center mb-8">
            <div
                class="bg-gray-100 dark:bg-slate-800 dark:border-slate-700 p-1 rounded-lg inline-flex border border-gray-300">
                <button type="button" x-on:click="billingPeriod = 'monthly'" :class="billingPeriod === 'monthly' ?
                        'bg-white dark:text-slate-200 dark:bg-slate-600 text-primary-600 shadow-sm' :
                        'text-gray-600 hover:text-gray-900 dark:hover:text-slate-200'"
                    class="px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    {{ t('monthly') }}
                </button>
                <button type="button" x-on:click="billingPeriod = 'yearly'" :class="billingPeriod === 'yearly' ?
                        'bg-white dark:text-slate-200 dark:bg-slate-600 text-primary-600 shadow-sm' :
                        'text-gray-600  hover:text-gray-900 dark:hover:text-slate-200'"
                    class="px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    {{ t('yearly') }}
                </button>
            </div>
        </div>
        @endif

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mx-auto">
            @forelse ($this->filteredPlans as $plan)
            @php
            $user = auth()->user();
            $isCurrentTenantUser = tenant_id() && $user && $user->tenant_id === tenant_id();
            $isCurrentPlan = $plan->id == $currentPlanId;
            $link = tenant_route('tenant.billing', ['plan_id' => $plan->id]);
            $buttonText = $plan->price == 0 ? 'Start Free Trial' : 'Change';
            @endphp
            <div @if($showBillingToggle)
                    x-show="billingPeriod === '{{ strtolower($plan->billing_period) }}' || '{{ $plan->is_free ? 'true' : 'false' }}' === 'true'"
                 @endif
                x-data="{ showFeatures: true }"
                class="relative flex flex-col rounded-2xl border dark:border-slate-600 bg-white dark:bg-slate-700 @if ($isCurrentPlan) ring-2 ring-primary-600 dark:ring-primary-500 @endif p-6 shadow-sm transition hover:shadow-md cursor-pointer h-full"
                @if($showBillingToggle) x-cloak @endif>
                <!-- Most Popular Badge -->
                @if ($plan['featured'] != 0)
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-primary-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                        {{ t('most_popular') }}
                    </span>
                </div>
                @endif

                <div class="flex flex-col h-full justify-between">
                    <!-- Top Content Section -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-slate-200">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 truncate">{{ $plan->description }}
                        </p>

                        <div class="mt-6">
                            @if ($plan->is_free && $plan->trial_days)
                            <div class="flex justify-between items-center gap-4">
                                <span class="text-4xl font-bold text-gray-900 dark:text-slate-200">{{ t('free')
                                    }}</span>
                                <p class="mt-2 text-sm text-success-600">
                                    {{ $plan->trial_days }} {{ t('days_free_trial') }}
                                </p>
                            </div>
                            @else
                            <span class="text-4xl font-bold text-gray-900 dark:text-slate-200">{{
                                get_base_currency()->format($plan->price) }}</span>
                            <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">/{{ $plan->billing_period
                                }}</span>
                            @endif
                        </div>

                        <!-- Features Toggle -->
                        <div class="mt-6">
                            <button @click="showFeatures = !showFeatures"
                                class="text-sm text-primary-600 dark:text-primary-500 hover:text-primary-700 flex items-center">
                                <span x-text="showFeatures ? 'Hide Features' : 'Show Features'"></span>
                                <svg class="w-4 h-4 ml-1 transition-transform duration-200"
                                    :class="{ 'rotate-180': showFeatures }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Features List -->
                        <div x-show="showFeatures" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-4 space-y-3">
                            @foreach ($plan['planFeatures'] as $feature)
                            @if ($feature['value'] != 0)
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="ml-3 text-sm text-gray-700 dark:text-slate-200 ">
                                    {{ t($feature['feature']['slug']) }}:
                                </span>
                                <span class="dark:text-slate-200 ml-2">
                                    {{ $feature['value'] == '-1' ? 'Unlimited' : number_format($feature['value'])
                                    }}</span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- CTA Button - Always at Bottom -->
                    <div class="mt-8">
                        @if ($isCurrentPlan)
                        <span
                            class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg text-white bg-gray-400 cursor-not-allowed">
                            {{ t('current_plan') }}
                            <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        @else
                        @if ($plan->price == 0)
                        <button wire:click="startFreeTrial({{ $plan->id }})"
                            class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition-all">
                            {{ $buttonText }}
                            <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        @else
                        <a href="{{ $link }}"
                            class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition-all">
                            {{ $buttonText }}
                            <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div x-cloak class="text-center py-10 col-span-full">
                <p class="text-gray-600 dark:text-gray-400">{{ t('no_plans_available') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
