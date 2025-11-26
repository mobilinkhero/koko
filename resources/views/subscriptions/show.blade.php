<x-app-layout>
    <x-slot:title>
        {{ t('subscription_details') }}
    </x-slot:title>
        <x-breadcrumb :items="[
            ['label' => t('dashboard'), 'route' => auth()->user()->user_type === 'admin' ? route('admin.dashboard') : tenant_route('tenant.dashboard')],
            ['label' => t('subscription'), 'route' => auth()->user()->user_type === 'admin' ? route('admin.subscription.list') : tenant_route('tenant.subscriptions') ],
            ['label' => t('subscription_details')],
        ]" />
    <div class="mx-auto">
        <!-- Alert Messages -->
        @if (session('error'))
        <div class="mb-6 p-4 bg-danger-50 border-l-4 border-danger-400 rounded-lg">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-danger-400 mr-3" />
                <span class="text-danger-700">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if (session('success'))
        <div class="mb-6 p-4 bg-success-50 border-l-4 border-success-400 rounded-lg">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="w-5 h-5 text-success-400 mr-3" />
                <span class="text-success-700">{{ session('success') }}</span>
            </div>
        </div>
        @endif
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-stretch">
            <div class="xl:col-span-2">
                <x-card class="mb-6">
                    <x-slot:header>
                        <div class="flex items-center">
                            <x-carbon-plan class="w-6 h-6 text-info-500 mr-3" />
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ t('plan_details') }}</h2>
                        </div>
                    </x-slot:header>
                    <x-slot:content>

                        <!-- Content Grid - Two Columns -->
                        <div class="grid xl:grid-cols-2 grid-cols-1 gap-6">

                            <!-- Main Subscription Card -->
                            <x-card class="mb-6 self-start">
                                <x-slot:header>
                                    <div class="flex items-center">
                                        <x-heroicon-o-credit-card class="w-6 h-6 text-info-500 mr-3" />
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                            {{ $subscription->plan->name }}
                                            {{ t('plan') }}</h2>
                                    </div>
                                </x-slot:header>
                                <x-slot:content>
                                    <div class="flex flex-col items-start justify-between space-y-4">
                                        <!-- Subscription Info -->
                                        <div>
                                            <!-- Horizontal Info Grid -->
                                            <div class="flex flex-col justify-start space-y-4">
                                                <div class="flex items-center">
                                                    <x-heroicon-o-check-badge class="w-5 h-5 text-gray-400 mr-2" />
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">{{
                                                        t('status') }}:</span>
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                                        @if ($subscription->isActive()) bg-success-100 text-success-800
                                                        @elseif($subscription->isNew()) bg-info-100 text-info-800
                                                        @elseif($subscription->isCancelled()) bg-warning-100 text-warning-800
                                                        @elseif($subscription->isTerminated()) bg-danger-100 text-danger-800
                                                        @elseif($subscription->IsTrial()) bg-purple-100 text-purple-800
                                                        @elseif($subscription->isPause()) bg-warning-100 text-warning-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($subscription->status) }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center">
                                                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-gray-400 mr-2" />
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">{{
                                                        t('base_price') }}:</span>
                                                    <span
                                                        class="font-semibold text-gray-900 dark:text-white">{{get_base_currency()->format($subscription->plan->price)
                                                        }}</span>
                                                </div>

                                                @php
                                                $taxes = get_default_taxes();
                                                $totalTaxAmount = 0;
                                                $taxBreakdown = [];

                                                foreach ($taxes as $tax) {
                                                $taxAmount = $subscription->plan->price * ($tax->rate / 100);
                                                $totalTaxAmount += $taxAmount;
                                                $taxBreakdown[] = [
                                                'name' => $tax->name,
                                                'rate' => $tax->rate,
                                                'amount' => $taxAmount,
                                                'formatted' => $tax->name . ' (' . $tax->rate . '%): ' .
                                                get_base_currency()->format($taxAmount)
                                                ];
                                                }

                                                $finalAmount = $subscription->plan->price + $totalTaxAmount;
                                                @endphp

                                                @if($taxes->count() > 0)
                                                <div class="mt-2">
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-5 h-5 text-gray-400 mr-2" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                        <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">{{
                                                            t('taxes') }}:</span>
                                                    </div>
                                                    <div class="ml-7 mt-1">
                                                        @foreach($taxBreakdown as $tax)
                                                        <div
                                                            class="font-semibold text-gray-900 dark:text-white text-sm">
                                                            {{ $tax['formatted'] }}
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div
                                                    class="flex items-center mt-2 border-t pt-2 border-gray-200 dark:border-gray-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="w-5 h-5 text-gray-400 mr-2" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">Total
                                                        Price:</span>
                                                    <span class="font-semibold text-gray-900 dark:text-white">
                                                        {{ get_base_currency()->format($finalAmount) }}/{{
                                                        $subscription->plan->billing_period }}
                                                    </span>
                                                </div>
                                                @endif

                                                <div class="flex items-center mt-2">
                                                    <x-heroicon-o-calendar class="w-5 h-5 text-gray-400 mr-2" />
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">{{
                                                        t('ends') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">
                                                        {{ $subscription->trial_ends_at
                                                        ? $subscription->trial_ends_at->format('M d, Y')
                                                        : ($subscription->current_period_ends_at
                                                        ? $subscription->current_period_ends_at->format('M d, Y')
                                                        : 'N/A') }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center">
                                                    <x-heroicon-o-arrows-right-left
                                                        class="w-5 h-5 text-gray-400 mr-2" />
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">{{
                                                        t('auto_renewal') }}:</span>
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium {{ $subscription->is_recurring ? 'bg-success-100 text-success-800' : 'bg-danger-100 text-danger-800' }}">
                                                        {{ $subscription->is_recurring ? 'Enabled' : 'Disabled' }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center">
                                                    <x-heroicon-o-clock class="w-5 h-5 text-gray-400 mr-2" />
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 mr-2">{{
                                                        t('created') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{
                                                        $subscription->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </x-slot:content>
                                @if (auth()->user()->user_type !== 'admin' && ($subscription->isActive() ||
                                $subscription->isPause() && $subscription->current_period_ends_at >= now()))
                                <x-slot:footer>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-3">
                                        {{-- Upgrade --}}
                                        <a href="{{ tenant_route('tenant.subscriptions.upgrade', ['id' => $subscription->id]) }}"
                                            class="inline-flex items-center justify-center px-2 py-1 text-sm border border-success-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-success-100 text-success-700 hover:bg-success-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-300 dark:bg-slate-700 dark:border-slate-500 dark:text-success-400 dark:hover:border-success-600 dark:hover:bg-success-600 dark:hover:text-white dark:focus:ring-offset-slate-800 w-full">
                                            <x-heroicon-o-arrow-up-circle class="w-5 h-5 mr-2" />
                                            {{ t('upgrade') }}
                                        </a>

                                        {{-- Downgrade --}}
                                        <a href="{{ tenant_route('tenant.subscriptions.downgrade', ['id' => $subscription->id]) }}"
                                            class="inline-flex items-center justify-center px-2 py-1 text-sm border border-warning-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-warning-100 text-warning-700 hover:bg-warning-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-300 dark:bg-slate-700 dark:border-slate-500 dark:text-warning-400 dark:hover:border-warning-600 dark:hover:bg-warning-600 dark:hover:text-white dark:focus:ring-offset-slate-800 w-full">
                                            <x-heroicon-o-arrow-down-circle class="w-5 h-5 mr-2" />
                                            {{ t('downgrade') }}
                                        </a>

                                        {{-- Toggle Recurring --}}
                                        <form method="POST"
                                            action="{{ tenant_route('tenant.subscriptions.toggle-recurring', ['id' => $subscription->id]) }}">
                                            @csrf
                                            <input type="hidden" name="enable"
                                                value="{{ $subscription->is_recurring ? '0' : '1' }}">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center px-2 py-1 text-sm border border-gray-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:bg-slate-700 dark:border-slate-500 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-offset-slate-800 w-full">
                                                @if ($subscription->is_recurring)
                                                <x-heroicon-s-no-symbol class="h-5 w-5 mr-1" />
                                                {{ t('disable_auto_Renew') }}
                                                @else
                                                <x-heroicon-s-arrow-path class="h-5 w-5 mr-1" />
                                                {{ t('enable_auto_renew') }}
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Cancel --}}
                                        <div x-data="cancelHandler()"
                                            x-init="$watch('showModal', val => { if (!val) resetState() })">
                                            {{-- Cancel Button --}}
                                            <button type="button"
                                                @click="openModal('{{ t('cancel_subscription') }}', '{{ t('Are you sure you want to cancel this subscription?') }}')"
                                                class="inline-flex items-center justify-center px-2 py-1 text-sm border border-danger-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-danger-100 text-danger-700 hover:bg-danger-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-300 dark:bg-slate-700 dark:border-slate-500 dark:text-danger-400 dark:hover:border-danger-600 dark:hover:bg-danger-600 dark:hover:text-white dark:focus:ring-offset-slate-800 w-full">
                                                <x-heroicon-o-x-circle class="w-5 h-5 mr-1" />
                                                {{ t('cancel') }}
                                            </button>

                                            {{-- Modal --}}
                                            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0" x-cloak>
                                                <div
                                                    class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                                                        aria-hidden="true" @click="showModal = false"></div>

                                                    <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                        x-transition:enter="transition ease-out duration-300"
                                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                        x-transition:leave="transition ease-in duration-200"
                                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                                                        <div
                                                            class="px-4 pt-5 pb-4 bg-white dark:bg-slate-800 sm:p-6 sm:pb-4">
                                                            <div class="sm:flex sm:items-start">
                                                                <div
                                                                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-danger-100 dark:bg-slate-700 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                                                    <x-heroicon-o-exclamation-triangle
                                                                        class="w-6 h-6 text-danger-600 dark:text-danger-300" />

                                                                </div>
                                                                <div
                                                                    class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100"
                                                                        x-text="modalTitle"></h3>
                                                                    <div class="mt-2">
                                                                        <p class="text-sm text-gray-500 dark:text-gray-400"
                                                                            x-text="modalMessage"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="px-4 py-3 bg-gray-50 dark:bg-slate-700 sm:px-6 sm:flex sm:flex-row-reverse">
                                                            <button type="button" @click="confirmAction"
                                                                :class="'inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm sm:ml-3 sm:w-auto sm:text-sm ' + confirmButtonClass"
                                                                x-text="confirmButtonText">
                                                            </button>
                                                            <button type="button" @click="showModal = false"
                                                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                {{ t('cancel') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Hidden Form --}}
                                            <form x-ref="cancelForm" method="POST"
                                                action="{{ tenant_route('tenant.subscriptions.cancel', ['id' => $subscription->id]) }}"
                                                class="hidden">
                                                @csrf
                                            </form>
                                        </div>

                                    </div>
                                </x-slot:footer>
                                @endif
                            </x-card>

                            <!-- Plan Features -->
                            <x-card class="mb-6">
                                <x-slot:header>
                                    <div class="flex items-center">
                                        <x-heroicon-o-star class="w-6 h-6 text-info-500 mr-3" />
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{
                                            t('plan_features') }}</h2>
                                    </div>
                                </x-slot:header>
                                <x-slot:content>
                                    @if (!empty($subscription->plan->features))
                                    <div class="space-y-3">
                                        @foreach ($subscription->plan->features as $feature)
                                        @if ($feature->value != 0)
                                        <div class="flex items-center">
                                            <x-heroicon-o-check-circle
                                                class="h-5 w-5 text-success-500 mr-3 flex-shrink-0" />
                                            <span class="text-gray-700 dark:text-gray-300">{{ t($feature->slug) }}:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                                {{ $feature->value == '-1' ? 'Unlimited' :
                                                number_format($feature->value) }}
                                            </span>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="flex items-center text-gray-500">
                                        <x-heroicon-o-information-circle class="w-5 h-5 mr-2" />
                                        <p>{{ t('no_specific_feature_for_plan') }}</p>
                                    </div>
                                    @endif
                                </x-slot:content>
                            </x-card>

                        </div>
                    </x-slot:content>
                </x-card>
            </div>
            <!-- Subscription Timeline -->
            <x-card class="mb-6">
                <x-slot:header>
                    <div class="flex items-center">
                        <x-heroicon-o-clock class="w-6 h-6 text-info-500 mr-3" />
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ t('timeline') }}</h2>
                    </div>
                </x-slot:header>
                <x-slot:content>

                    @if ($subscription->subscriptionLogs->isEmpty())
                    <div class="flex items-center text-gray-500">
                        <x-heroicon-o-information-circle class="w-5 h-5 mr-2" />
                        <p>{{ t('no_history_available_subscription') }}</p>
                    </div>
                    @else
                    <div class="space-y-4 h-[570px] overflow-y-auto">
                        @foreach ($subscription->getLogs() as $log)
                        <div class="flex items-start">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-info-100 rounded-full flex items-center justify-center">
                                @switch($log->type)
                                @case('created')
                                <x-heroicon-o-plus-circle class="h-4 w-4 text-info-600" />
                                @break

                                @case('activated')
                                <x-heroicon-o-check-circle class="h-4 w-4 text-info-600" />
                                @break

                                @case('renewed')
                                <x-heroicon-o-arrow-path class="h-4 w-4 text-info-600" />
                                @break

                                @case('cancelled')
                                <x-heroicon-o-x-circle class="h-4 w-4 text-info-600" />
                                @break

                                @case('plan_changed')
                                @case('plan_upgraded')

                                @case('plan_downgraded')
                                <x-heroicon-o-arrows-right-left class="h-4 w-4 text-info-600" />
                                @break

                                @default
                                <x-heroicon-o-information-circle class="h-4 w-4 text-info-600" />
                                @endswitch
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ ucfirst(str_replace('_', ' ', $log->type)) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ format_date_time($log->created_at) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </x-slot:content>
            </x-card>
        </div>
        <!-- Recent Invoices -->
        <x-card class="mb-6">
            <x-slot:header>
                <div class="flex items-center">
                    <x-heroicon-o-document-text class="w-6 h-6 text-info-500 mr-3" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ t('recent_invoices') }}</h2>
                </div>
            </x-slot:header>
            <x-slot:content>

                @if ($subscription->invoices->isEmpty())
                <div class="p-6 flex items-center text-gray-500">
                    <x-heroicon-o-document class="w-5 h-5 mr-2" />
                    <p> {{ t('no_invoices_found') }} </p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('sr_no') }}</th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('invoice') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('date') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('amount') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('status') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ( $subscription->invoices
                            ->sortBy([
                            fn($a) => $a->status !== 'new',
                            fn($a) => -$a->invoice_number,
                            ])
                            ->take(5)
                            as $invoice)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <x-heroicon-o-document-text class="h-4 w-4 text-gray-400 mr-2" />
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $invoice->invoice_number ?? format_draft_invoice_number() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $invoice->created_at->format('M d, Y') }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $invoice->formatted_total }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-medium rounded-full
                                            @if ($invoice->status === 'paid') bg-success-100 text-success-800
                                            @elseif($invoice->status === 'new') bg-info-100 text-info-800
                                            @elseif($invoice->status === 'failed') bg-danger-100 text-danger-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ auth()->user()->user_type === 'admin'
                                                    ? route('admin.invoices.show', ['id' => $invoice->id])
                                                    : tenant_route('tenant.invoices.show', ['id' => $invoice->id]) }}"
                                            class="text-info-600 hover:text-info-500 flex items-center text-sm">
                                            <x-heroicon-o-eye class="h-4 w-4 mr-1" />
                                            {{ t('view') }}
                                        </a>

                                        @if (auth()->user()->user_type !== 'admin')
                                        @if ($invoice->status === 'new' && !$invoice->hasPendingTransactions())
                                        <a href="{{ tenant_route('tenant.invoices.pay', ['id' => $invoice->id]) }}"
                                            class="text-success-600 hover:text-success-500 flex items-center text-sm">
                                            <x-heroicon-o-credit-card class="h-4 w-4 mr-1" />
                                            {{ t('pay') }}
                                        </a>
                                        @elseif($invoice->hasPendingTransactions())
                                        <span class="text-warning-600 flex items-center text-sm">
                                            <x-heroicon-o-clock class="h-4 w-4 mr-1" />
                                            {{ t('pending') }}
                                        </span>
                                        @elseif($invoice->status === 'new' && $invoice->hasOnlyFailedTransactions())
                                        <a href="{{ tenant_route('tenant.invoices.pay', ['id' => $invoice->id]) }}"
                                            class="text-success-600 hover:text-success-500 flex items-center text-sm">
                                            <x-heroicon-o-credit-card class="h-4 w-4 mr-1" />
                                            {{ t('retry') }}
                                        </a>
                                        @endif
                                        @endif

                                        @if ($invoice->status === 'paid')
                                        <a href="{{ auth()->user()->user_type === 'admin'
                                                        ? route('admin.invoices.download', ['id' => $invoice->id])
                                                        : tenant_route('tenant.invoices.download', ['id' => $invoice->id]) }}"
                                            class="text-info-600 hover:text-info-500 flex items-center text-sm">
                                            <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-1" />
                                            {{ t('download') }}
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($subscription->invoices->count() > 5)
                <div
                    class="px-6 py-3 bg-gray-50 dark:bg-gray-900 text-center border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ auth()->user()->user_type === 'admin' ? route('admin.invoices.list') : tenant_route('tenant.invoices') }}"
                        class="text-info-600 hover:text-info-900 flex items-center justify-center text-sm font-medium">
                        <x-heroicon-o-arrow-right class="h-4 w-4 mr-1" />
                        {{ t('view_all_invoices') }}
                    </a>
                </div>
                @endif
                @endif
            </x-slot:content>
        </x-card>
    </div>
    <script>
        function cancelHandler() {
        return {
            showModal: false,
            modalTitle: '',
            modalMessage: '',
            confirmButtonText: 'Yes Cancel',
            confirmButtonClass: 'bg-danger-600 hover:bg-danger-700',
            openModal(title, message) {
                this.modalTitle = title;
                this.modalMessage = message;
                this.showModal = true;
            },
            confirmAction() {
                this.$refs.cancelForm.submit();
            },
            resetState() {
                this.modalTitle = '';
                this.modalMessage = '';
            }
        }
    }
    </script>

</x-app-layout>