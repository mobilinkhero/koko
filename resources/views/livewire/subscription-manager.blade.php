<div>
    <x-slot:title>
        {{ t('subscription_details') }}
    </x-slot:title>

    <div class="mx-auto max-w-6xl">
          <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('subscription_details')],
    ]" />
        <x-card>
            <!-- Enhanced Header Section -->
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('subscription_details') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('manage_your_active_plans') }}
                        </p>
                    </div>
                </div>
            </x-slot:header>
            <x-slot:content>
                <div class="mb-6">
                    <!-- Quick Stats Bar -->
                    @if ($subscriptions->count() > 0)
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-card>
                            <x-slot:content>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-check-circle class="h-8 w-8 text-success-500" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{
                                            t('active_plans') }}
                                        </p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-white">
                                            {{ $subscriptions->whereIn('status', ['active', 'trial'])->count() }}
                                        </p>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-card>

                        <x-card>
                            <x-slot:content>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-currency-dollar class="h-8 w-8 text-info-500" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{
                                            t('monthly_spend') }}
                                        </p>
                                        @php
                                        $taxes = get_default_taxes();
                                        $totalWithTax = $subscriptions->where('status', 'active')->sum(function
                                        ($subscription) use ($taxes) {
                                        // Get the latest paid invoice for this subscription to include coupon discounts
                                        $latestInvoice = $subscription->invoices()->where('status', 'paid')->latest()->first();

                                        if ($latestInvoice) {
                                            // Use the actual invoice total which includes coupon discounts
                                            return $latestInvoice->total();
                                        }

                                        // Fallback to plan price + tax if no paid invoice exists
                                        $basePrice = $subscription->plan->price;
                                        $totalTaxAmount = 0;
                                        foreach ($taxes as $tax) {
                                            $totalTaxAmount += $basePrice * ($tax->rate / 100);
                                        }
                                        return $basePrice + $totalTaxAmount;
                                        });
                                        @endphp
                                        <p class="text-2xl font-bold text-slate-900 dark:text-white">
                                            {{ get_base_currency()->format($totalWithTax) }} </p>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-card>

                        <x-card>
                            <x-slot:content>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-clock class="h-8 w-8 text-warning-500" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{
                                            t('next_renewal_after') }}
                                        </p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-white">
                                            @php
                                            $nextRenewal = $subscriptions
                                            ->where('status', 'active')
                                            ->where('current_period_ends_at', '!=', null)
                                            ->min('current_period_ends_at');

                                            $daysDiff = null;

                                            if ($nextRenewal && \Carbon\Carbon::parse($nextRenewal)->isFuture()) {
                                            $daysDiff = \Carbon\Carbon::now()->diffInDays(
                                            \Carbon\Carbon::parse($nextRenewal),
                                            );
                                            }
                                            @endphp
                                            {{ $daysDiff !== null ? intval($daysDiff) . ' days' : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-card>
                    </div>
                    @endif
                </div>

                @forelse($subscriptions as $subscription)
                <!-- Modern Subscription Card -->
                <div class="mb-8">
                    <x-card class="overflow-hidden">
                        <x-slot name="header">
                            <div class="flex sm:items-center justify-between flex-col sm:flex-row gap-4">
                                <div class="flex items-center space-x-4">
                                    <!-- Enhanced Status Badge -->
                                    @if ($subscription->status === 'active')
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-success-500 rounded-full animate-pulse"></div>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 dark:bg-success-900/50 text-success-800 dark:text-success-400">
                                            {{ t('active') }}
                                        </span>
                                    </div>
                                    @elseif($subscription->status === 'cancelled')
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-warning-500 rounded-full"></div>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-warning-100 dark:bg-warning-900/50 text-warning-800 dark:text-warning-400">
                                            {{ t('cancelled') }}
                                        </span>
                                    </div>
                                    @elseif($subscription->status === 'ended')
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-slate-400 rounded-full"></div>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-300">
                                            {{ t('ended') }}
                                        </span>
                                    </div>
                                    @elseif($subscription->status === 'trial')
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-purple-500 rounded-full animate-pulse"></div>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-400">
                                            {{ t('trial') }}
                                        </span>
                                    </div>
                                    @elseif($subscription->status === 'paused')
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-warning-500 rounded-full"></div>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-warning-100 dark:bg-warning-900/50 text-warning-800 dark:text-warning-400">
                                            {{ t('paused') }}
                                        </span>
                                    </div>
                                    @elseif($subscription->status === 'new')
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-info-500 rounded-full animate-pulse"></div>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info-100 dark:bg-info-900/50 text-info-800 dark:text-info-400">
                                            {{ t('new') }}
                                        </span>
                                    </div>
                                    @endif

                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white">
                                            {{ $subscription->plan->name }}
                                        </h3>
                                        @php
                                        $taxes = get_default_taxes();
                                        $basePrice = $subscription->plan->price;
                                        $totalTaxAmount = 0;
                                        $taxBreakdown = [];

                                        foreach ($taxes as $tax) {
                                        $taxAmount = $basePrice * ($tax->rate / 100);
                                        $totalTaxAmount += $taxAmount;
                                        $taxBreakdown[] = $tax->name . ' (' . $tax->rate . '%): ' .
                                        get_base_currency()->format($taxAmount);
                                        }

                                        $finalAmount = $basePrice + $totalTaxAmount;
                                        @endphp
                                        <p class="text-sm text-slate-500 dark:text-slate-400">

                                            {{ t('total') }}:<span class="font-semibold">{{
                                                get_base_currency()->format($finalAmount) }}</span>/{{
                                            $subscription->plan->billing_period }} <span
                                                class="text-slate-500 dark:text-slate-400">{{ t('including_tax')
                                                }}</span>

                                        </p>
                                    </div>
                                </div>

                                <x-button :href="tenant_route('tenant.subscriptions.show', ['id' => $subscription->id])"
                                    size="sm"
                                    class="bg-info-100 hover:bg-info-200 text-info-700 dark:bg-info-700 dark:hover:bg-info-600 dark:text-info-300">
                                    <x-heroicon-s-eye class="h-4 w-4 mr-2" />
                                    {{ t('view_details') }}
                                </x-button>
                            </div>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Key Information Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-o-calendar class="h-5 w-5 text-slate-400" />
                                        <div>
                                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                                {{ t('started') }}
                                            </p>
                                            <p class="text-sm text-slate-900 dark:text-white">
                                                {{ $subscription->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($subscription->trial_ends_at)
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-o-clock class="h-5 w-5 text-purple-400" />
                                        <div>
                                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                                {{ t('trial') }}
                                                {{ t('ends') }}
                                            </p>
                                            <p class="text-sm text-slate-900 dark:text-white">
                                                {{ $subscription->trial_ends_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @elseif ($subscription->current_period_ends_at)
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-o-arrow-path class="h-5 w-5 text-info-400" />
                                        <div>
                                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                                {{ $subscription->is_recurring ? t('renews') : t('expires') }}
                                            </p>
                                            <p class="text-sm text-slate-900 dark:text-white">
                                                {{ $subscription->current_period_ends_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if (!$subscription->isTrial())
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        @if ($subscription->is_recurring)
                                        <x-heroicon-o-arrow-path class="h-5 w-5 text-success-400" />
                                        @else
                                        <x-heroicon-o-pause-circle class="h-5 w-5 text-slate-400" />
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                                {{ t('auto_renew') }}
                                            </p>
                                            <p
                                                class="text-sm {{ $subscription->is_recurring ? 'text-success-600 dark:text-success-400' : 'text-slate-600 dark:text-slate-400' }}">
                                                {{ $subscription->is_recurring ? t('enabled') : t('disabled') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Payment Alert for Offline Payments -->
                            @if (
                            $subscription->status === 'new' &&
                            method_exists($subscription, 'hasUnpaidInitInvoice') &&
                            $subscription->hasUnpaidInitInvoice() &&
                            $subscription->getUnpaidInitInvoice()->type === 'offline')
                            <div
                                class="mb-6 rounded-lg bg-info-50 dark:bg-info-900/20 p-4 border border-info-200 dark:border-info-800">
                                <div class="flex items-start">
                                    <x-heroicon-s-information-circle
                                        class="h-5 w-5 text-info-500 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <h4 class="text-sm font-medium text-info-800 dark:text-info-200">{{
                                            t('payment_verification_pending') }}</h4>
                                        <p class="mt-1 text-sm text-info-700 dark:text-info-300">
                                            {{ t('payment_activate_description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Status Information -->
                            <div
                                class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-400 mb-4">
                                <div class="flex items-center space-x-2">
                                    @if ($subscription->status === 'active')
                                    <x-heroicon-o-shield-check class="h-4 w-4 text-success-500" />
                                    <span>
                                        @if ($subscription->is_recurring)
                                        {{ t('auto_renews_on') }}
                                        {{ $subscription->current_period_ends_at->format('M d, Y') }}
                                        @else
                                        {{ t('expires_on') }}
                                        {{ $subscription->current_period_ends_at->format('M d, Y') }}
                                        @endif
                                    </span>
                                    @elseif($subscription->status === 'cancelled')
                                    <x-heroicon-o-hand-raised class="h-4 w-4 text-warning-500" />
                                    <span>{{ t('active_until') }}
                                        {{ $subscription->current_period_ends_at->format('M d, Y') }}</span>
                                    @elseif($subscription->status === 'ended')
                                    <x-heroicon-o-x-circle class="h-4 w-4 text-slate-400" />
                                    <span>Ended {{ $subscription->ended_at->format('M d, Y') }}</span>
                                    @elseif($subscription->status === 'new')
                                    <x-heroicon-o-clock class="h-4 w-4 text-info-500" />
                                    <span>{{ t('awaiting_payment_confirmation') }}</span>
                                    @endif
                                </div>
                            </div>
                        </x-slot>

                        <x-slot name="footer">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <!-- Primary Actions -->
                                <div class="flex flex-wrap gap-2">
                                    @if ($subscription->status === 'active' || $subscription->status === 'paused')
                                    @if ($subscription->is_recurring)
                                    <form method="POST"
                                        action="{{ tenant_route('tenant.subscriptions.toggle-recurring', ['id' => $subscription->id]) }}">
                                        @csrf
                                        <input type="hidden" name="enable" value="0">
                                        <x-button type="submit" size="sm"
                                            class="bg-warning-100 hover:bg-warning-200 text-warning-800 dark:bg-warning-900/50 dark:hover:bg-warning-900/70 dark:text-warning-300">
                                            <x-heroicon-s-pause-circle class="h-4 w-4 mr-2" />
                                            {{ t('disable_auto_Renew') }}
                                        </x-button>
                                    </form>
                                    @else
                                    <form method="POST"
                                        action="{{ tenant_route('tenant.subscriptions.toggle-recurring', ['id' => $subscription->id]) }}">
                                        @csrf
                                        <input type="hidden" name="enable" value="1">
                                        <x-button type="submit" size="sm"
                                            class="bg-success-100 hover:bg-success-200 text-success-800 dark:bg-success-900/50 dark:hover:bg-success-900/70 dark:text-success-300">
                                            <x-heroicon-s-arrow-path class="h-4 w-4 mr-2" />
                                            {{ t('enable_auto_renew') }}
                                        </x-button>
                                    </form>
                                    @endif
                                    @elseif($subscription->status === 'new')
                                    @if (method_exists($subscription, 'hasUnpaidInitInvoice') &&
                                    $subscription->hasUnpaidInitInvoice())
                                    <x-button :href="tenant_route('tenant.invoices.checkout', [
                                                                'id' => $subscription->getUnpaidInitInvoice()->id,
                                                            ])" size="sm"
                                        class="bg-info-600 hover:bg-info-700 text-white dark:bg-info-700 dark:hover:bg-info-600">
                                        <x-heroicon-s-credit-card class="h-4 w-4 mr-2" />
                                        {{ t('complete_payment') }}
                                    </x-button>
                                    @endif
                                    @elseif($subscription->status === 'cancelled' || $subscription->status === 'ended')
                                    <x-button
                                        :href="tenant_route('tenant.subscriptions.show', ['id' => $subscription->id])"
                                        size="sm"
                                        class="bg-info-600 hover:bg-info-700 text-white dark:bg-info-700 dark:hover:bg-info-600">
                                        <x-heroicon-s-arrow-path-rounded-square class="h-4 w-4 mr-2" />
                                        {{ t('renew_subscription') }}
                                    </x-button>
                                     @if(tenant_on_active_plan())
                                     <x-button
                                        :href="tenant_route('tenant.subscription')"
                                        size="sm"
                                        class="bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-700 dark:hover:bg-primary-600">
                                        {{ t('available_plans') }}
                                    </x-button>
                                    @endif
                                    @endif
                                </div>

                                <!-- Cancel Action -->
                                @if ($subscription->status === 'active' || $subscription->status === 'paused')
                                <div x-data="cancelHandler()">
                                    <x-button.soft-danger type="button" size="sm" @click="openModal(
            '{{ t('cancel_subscription') }}',
            '{{ t('Are you sure you want to cancel this subscription? You will still have access until the end of your billing period.') }}'
        )">
                                        {{ t('cancel') }}
                                    </x-button.soft-danger>

                                    {{-- Hidden Form --}}
                                    <form x-ref="cancelForm" method="POST"
                                        action="{{ tenant_route('tenant.subscriptions.cancel', ['id' => $subscription->id]) }}"
                                        class="hidden">
                                        @csrf
                                    </form>

                                    {{-- Include the modal only once per page --}}
                                    @once
                                    {{-- Place this at the bottom of the page or layout --}}
                                    <template x-teleport="body">
                                        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                            x-cloak>
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
                                                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-danger-100 dark:bg-danger-700 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                                                <x-heroicon-o-exclamation-triangle
                                                                    class="w-6 h-6 text-danger-600 dark:text-danger-300" />

                                                            </div>
                                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
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
                                    </template>
                                    @endonce
                                </div>

                                @endif
                            </div>
                        </x-slot>
                    </x-card>
                </div>
                @empty
                <!-- Enhanced Empty State -->
                <div class="text-center py-16">
                    <x-card class="max-w-md mx-auto">
                        <x-slot name="content">
                            <div class="text-center">
                                <div
                                    class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-r from-info-100 to-primary-100 dark:from-info-900/50 dark:to-primary-900/50 mb-8">
                                    <x-heroicon-o-bookmark class="h-10 w-10 text-info-600 dark:text-info-400" />
                                </div>
                                <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
                                    {{ t('no_active_subscriptions') }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 mb-8">
                                    {{ t('no_active_subscriptions_get_preminum') }}
                                </p>
                                <x-button :href="tenant_route('tenant.subscription')"
                                    class="bg-gradient-to-r from-info-600 to-primary-600 hover:from-info-700 hover:to-primary-700 text-white">
                                    <x-heroicon-s-plus-circle class="h-5 w-5 mr-2" />
                                    {{ t('browse_available_plans') }}
                                </x-button>
                            </div>
                        </x-slot>
                    </x-card>
                </div>
                @endforelse

                <!-- Recent Invoices Section -->
                @if (isset($recentInvoices) && $recentInvoices->count() > 0)
                <div class="mt-16">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ t('recent_invoices') }}</h2>
                        <x-button :href="tenant_route('tenant.invoices.index')" size="sm"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-300">
                            {{ t('view_all') }}
                            <x-heroicon-s-arrow-right class="h-4 w-4 ml-2" />
                        </x-button>
                    </div>

                    <x-card>
                        <x-slot name="content" class="p-0">
                            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($recentInvoices as $invoice)
                                <a href="{{ tenant_route('tenant.invoices.show', ['id' => $invoice->uid]) }}"
                                    class="block p-6 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                                    <x-heroicon-o-document-text
                                                        class="h-5 w-5 text-slate-600 dark:text-slate-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900 dark:text-white">
                                                    {{ $invoice->invoice_number ?? format_draft_invoice_number() }}
                                                </p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                                    {{ $invoice->title }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <!-- Invoice Status -->
                                            @if ($invoice->status === 'paid')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/50 text-success-800 dark:text-success-400">
                                                {{ t('paid') }}
                                            </span>
                                            @elseif($invoice->status === 'new')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/50 text-warning-800 dark:text-warning-400">
                                                {{ t('pending') }}
                                            </span>
                                            @elseif($invoice->status === 'cancelled')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-300">
                                                {{ t('cancelled') }}
                                            </span>
                                            @endif

                                            <div class="text-right">
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                    {{ $invoice->formattedTotal() }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $invoice->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </x-slot>
                    </x-card>
                </div>
                @endif

                <!-- Pagination -->
                @if (isset($subscriptions) && method_exists($subscriptions, 'hasPages') && $subscriptions->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $subscriptions->links() }}
                </div>
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

</div>
