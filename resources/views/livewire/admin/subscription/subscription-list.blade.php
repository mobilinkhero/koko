<div class="px-4 md:px-0">
    <x-slot:title>
        {{ t('subscription_plans') }}
    </x-slot:title>
    <div class="flex justify-start mb-3 px-5 lg:px-0 items-center gap-2">
        <x-button.primary wire:click="createSubscription">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('add_subscription') }}
        </x-button.primary>
    </div>

    <x-card class="mx-4 lg:mx-0 rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.subscription-table />
            </div>
        </x-slot:content>
    </x-card>

    <x-modal.custom-modal :id="'faq-modal'" :maxWidth="'2xl'" wire:model.defer="showSubscriptionModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('subscription_plans') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="mb-4 px-6">
                <div wire:ignore>
                    <div class="flex items-center gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="tenant_id" :value="t('tenant')" />
                    </div>
                    <x-select wire:model.defer="subscription.tenant_id" id="subscription.tenant_id"
                        class="block w-full mt-1 tom-select">
                        <option value="">{{ t('choose_a_company') }}</option>
                        @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->company_name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-input-error for="subscription.tenant_id" class="mt-2" />
            </div>

            <div class="mb-4 px-6">
                <div wire:ignore>
                    <div class="flex items-center gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="subscription.plan_id" :value="t('plan')" />
                    </div>
                    <x-select wire:model.defer="subscription.plan_id" id="subscription.plan_id"
                        class="block w-full mt-1 tom-select">
                        <option value="">{{ t('choose_a_plan') }}</option>
                        @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name . ' - ' . $plan->billing_period }}
                        </option>
                        @endforeach
                    </x-select>
                </div>
                <x-input-error for="subscription.plan_id" class="mt-2" />
            </div>

            <div class="mb-4 px-6">
                <div>
                    <div class="flex items-center gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="subscription.payment_id" :value="t('transaction_id')" />
                    </div>
                    <x-input wire:model.defer="subscription.payment_id" id="subscription.payment_id" />
                    <x-input-error for="subscription.payment_id" class="mt-2" />
                </div>
            </div>

            <div class="mb-4 px-6">
                <div>
                    <div class="flex items-center gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="subscription.price" :value="t('price')" />
                    </div>
                    <x-input wire:model.defer="subscription.price" id="subscription.price" />
                    <x-input-error for="subscription.price" class="mt-2" />
                </div>
            </div>

            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showSubscriptionModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    {{-- View Subscription Modal --}}
    <x-modal.custom-modal :id="'view-subscription-modal'" :maxWidth="'4xl'" wire:model.defer="viewSubscriptionModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 flex justify-between">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('subscription_details') }}
            </h1>
            <button class="text-gray-500 hover:text-gray-700 text-2xl dark:hover:text-gray-300"
                wire:click="$set('viewSubscriptionModal', false)">
                &times;
            </button>
        </div>

        @if(isset($subscription))
        <div class="p-6">
            <!-- Main content area -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Left column - Basic info -->
                <div class="md:col-span-8 space-y-6">
                    <!-- Basic subscription info card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{
                                    t('basic_information') }}</h2>

                                <!-- Status badge -->
                                @if(isset($subscription->status))
                                <div>
                                    <span
                                        @class([ 'inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium'
                                        , 'bg-success-50 dark:bg-success-900/20 text-success-700 dark:text-success-400 ring-1 ring-success-600/20 dark:ring-success-500/30'=>
                                        $subscription->status->value === 'active',
                                        'bg-warning-50 dark:bg-warning-900/20 text-warning-700 dark:text-warning-400
                                        ring-1
                                        ring-warning-600/20 dark:ring-warning-500/30' => $subscription->status->value
                                        ===
                                        'pending',
                                        'bg-danger-50 dark:bg-danger-900/20 text-danger-700 dark:text-danger-400 ring-1
                                        ring-danger-600/20 dark:ring-danger-500/30' =>
                                        in_array($subscription->status->value,
                                        ['rejected', 'canceled']),
                                        'bg-neutral-100 dark:bg-neutral-800/40 text-neutral-700 dark:text-neutral-300
                                        ring-1 ring-neutral-700/10 dark:ring-neutral-500/30' =>
                                        $subscription->status->value === 'inactive',
                                        'bg-gray-50 dark:bg-gray-900/20 text-gray-700 dark:text-gray-400 ring-1
                                        ring-gray-600/20 dark:ring-gray-500/30' => $subscription->status->value ===
                                        'expired',
                                        'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 ring-1
                                        ring-purple-600/20 dark:ring-purple-500/30' => $subscription->status->value ===
                                        'trial',
                                        'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 ring-1
                                        ring-orange-600/20 dark:ring-orange-500/30' => $subscription->status->value ===
                                        'past_due',
                                        ])>
                                        {{ $subscription->status->label() }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('tenant') }}
                                    </p>
                                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{
                                        $subscription->tenant->company_name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('plan') }}</p>
                                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{
                                        $subscription->plan->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('price') }}</p>
                                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{
                                        isset($subscription->price) ? get_base_currency()->format($subscription->price)
                                        : '0.00' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{
                                        t('billing_cycle') }}</p>
                                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{
                                        ucfirst($subscription->billing_cycle ?? 'N/A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline and dates card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-5">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{
                                t('subscription_timeline') }}</h2>

                            <div class="relative">
                                <!-- Timeline track -->
                                <div class="absolute h-full w-0.5 bg-gray-200 dark:bg-gray-700 left-1.5 top-0"></div>

                                <div class="space-y-6">
                                    <!-- Created date -->
                                    <div class="relative flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-4 w-4 rounded-full bg-info-500 mt-1"></div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                                t('created') }}</p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ isset($subscription->created_at) ?
                                                $subscription->created_at->format('M d, Y h:i A') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Purchase date -->
                                    @if(isset($subscription->purchase_date))
                                    <div class="relative flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-4 w-4 rounded-full bg-success-500 mt-1"></div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                                t('started') }}</p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $subscription->purchase_date->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Trial end date -->
                                    @if(isset($subscription->trial_ends_at))
                                    <div class="relative flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-4 w-4 rounded-full bg-purple-500 mt-1"></div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                                t('trial_ends') }}</p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $subscription->trial_ends_at->format('M d, Y') }}
                                                @if($subscription->trial_ends_at->isFuture())
                                                <span class="text-xs text-purple-600 dark:text-purple-400 ml-2">
                                                    ({{ $subscription->trial_ends_at->diffForHumans() }})
                                                </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- End date -->
                                    @if(isset($subscription->ends_at))
                                    <div class="relative flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-4 w-4 rounded-full bg-danger-500 mt-1"></div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                                ('expires') }}</p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $subscription->ends_at->format('M d, Y') }}
                                                @if($subscription->ends_at->isFuture())
                                                <span class="text-xs text-danger-600 dark:text-danger-400 ml-2">
                                                    ({{ $subscription->ends_at->diffForHumans() }})
                                                </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column - Payment details -->
                <div class="md:col-span-4 space-y-6">
                    <!-- Payment details card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-5">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{
                                t('payment_information') }}</h2>

                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('payment_id')
                                        }}</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{
                                        $subscription->payment_id ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{
                                        t('payment_method') }}</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{
                                        $subscription->payment_method ?? 'N/A' }}</p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Stats card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-5">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{
                                t('subscription_stats') }}</h2>

                            <div class="grid grid-cols-2 gap-4">
                                @if(isset($subscription->created_at) && isset($subscription->ends_at))
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-2xl font-semibold text-info-600 dark:text-info-400">
                                        {{ $subscription->created_at->diffInDays($subscription->ends_at) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('total_days') }}</p>
                                </div>
                                @endif

                                @if(isset($subscription->ends_at) && $subscription->ends_at->isFuture())
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-2xl font-semibold text-success-600 dark:text-success-400">
                                        {{ (int)$subscription->created_at->diffInDays($subscription->ends_at) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('days_remaining') }}
                                    </p>
                                </div>
                                @endif

                                @if(isset($subscription->trial_ends_at) && $subscription->trial_ends_at->isFuture())
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-2xl font-semibold text-success-600 dark:text-success-400">
                                        {{ (int)$subscription->created_at->diffInDays($subscription->trial_ends_at) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('days_remaining') }}
                                    </p>
                                </div>
                                @endif

                                @if(isset($subscription->trial_ends_at) && $subscription->trial_ends_at->isFuture())
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-2xl font-semibold text-success-600 dark:text-success-400">
                                        {{ (int)$subscription->created_at->diffInDays($subscription->trial_ends_at) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('days_remaining') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                    wire:click="$set('viewSubscriptionModal', false)">
                    {{ t('close') }}
                </button>
            </div>
        </div>
        @else
        <div class="p-6">
            <div class="flex justify-center items-center py-12">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <svg class="w-8 h-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('please_wait_while_we_retrieve_the_subscription_information') }}
                    </p>
                </div>
            </div>
        </div>
        @endif
    </x-modal.custom-modal>

</div>