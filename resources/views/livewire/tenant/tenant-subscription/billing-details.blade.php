<div>
    <div class="py-4 sm:py-10">
        <div class="max-w-6xl mx-auto">
            <x-card class="rounded-lg mb-6">
                <x-slot:header>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-credit-card class="w-6 h-6 text-primary-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                {{ t('checkout') }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                {{ t("payment_confirm_message") }}
                            </p>
                        </div>
                    </div>

                </x-slot:header>
                <x-slot:content>
                    @if (session('error'))
                    <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-6" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                    @endif
                    <!-- Plan Details Card -->
                    <div class="mb-8 bg-primary-500 dark:bg-slate-700 rounded-lg shadow-md p-6 text-white">
                        <h3 class="text-xl font-bold mb-4">{{ t('Subscription Details') }}</h3>

                        @php
                        $taxes = get_default_taxes();
                        $totalTaxAmount = 0;
                        foreach ($taxes as $tax) {
                        $totalTaxAmount += $plan->price * ($tax->rate / 100);
                        }
                        $finalAmount = $plan->price + $totalTaxAmount;


                        $baseAmount = get_base_currency()->format($plan->price);
                        $taxBreakdown = [];
                        foreach($taxes as $tax) {
                        $taxBreakdown[] = $tax->rate . '% ' . $tax->name;
                        }
                        @endphp

                        <!-- First row with 3 columns for plan details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-primary-100">{{ t('plan_name') }}</p>
                                <p class="font-semibold text-lg">{{ $plan->name }}</p>
                            </div>
                            <div>
                                <p class="text-primary-100">{{ t('base_amount') }}</p>
                                <p class="font-semibold text-lg">
                                    {{ get_base_currency()->format($plan->price) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-primary-100">{{ t('billing_cycle') }}</p>
                                <p class="font-semibold text-lg">{{ ucfirst($plan->billing_period) }}</p>
                            </div>
                        </div>

                        <!-- Second row with 3 columns for interval, price breakdown and tax details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-primary-100">{{ t('interval') }}</p>
                                <p class="font-semibold text-lg">Per {{ $interval }}</p>
                            </div>
                            <div>
                                <p class="text-primary-100">{{ t('price_breakdown') }}</p>
                                <div class="font-semibold text-lg">
                                    {{ $baseAmount }}<br>
                                    @foreach($taxBreakdown as $taxLine)
                                    <span class="ml-2 block text-sm">+ {{ $taxLine }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @if($taxes->count() > 0)
                            <div>
                                <p class="text-primary-100">{{ t('tax_details') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($taxes as $tax)
                                    <span class="text-sm bg-primary-600 dark:bg-slate-600 px-2 py-1 rounded-md">
                                        {{ $tax->name }} ({{ $tax->rate }}%): {{
                                        get_base_currency()->format($plan->price * ($tax->rate / 100)) }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Total amount in a separate row -->
                        @if($taxes->count() > 0)
                        <div class="border-t border-primary-400 dark:border-slate-600 pt-4 mt-4">
                            <div class="flex justify-end">
                                <div class="text-right">
                                    <p class="text-primary-100">{{ t('total_amount') }}</p>
                                    <p class="font-semibold text-2xl">
                                        {{ get_base_currency()->format($finalAmount) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <form action="{{ tenant_route('tenant.checkout.process') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <!-- Payment Method Selection -->
                        <div>
                            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">{{
                                t('payment_method') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @php
                                $billingManager = app('billing.manager');
                                $gateways = $billingManager->getActiveGateways();
                                @endphp

                                @foreach ($gateways as $name => $gateway)
                                <div
                                    class="relative border border-slate-200 dark:border-slate-700 dark:bg-slate-700 rounded-lg p-4 hover:border-info-500 cursor-pointer">
                                    <input type="radio" name="payment_method" id="payment_{{ $name }}"
                                        value="{{ $name }}" class="absolute h-4 w-4 top-4 right-4" {{ $loop->first ||
                                    old('payment_method') == $name ? 'checked' : '' }}>
                                    <label for="payment_{{ $name }}" class="cursor-pointer block">
                                        <div class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $gateway->getName() }}</div>
                                        <p class="text-gray-500  text-sm mt-1">{{ $gateway->getDescription() }}</p>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        @if ($plan->isFree())
                        <div class="bg-success-50 dark:bg-slate-700 border-l-4 border-success-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-success-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-success-700">
                                        {{ t('free_plan_no_payment') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-info-50 dark:bg-slate-700 border-l-4 border-info-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-info-400 " xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-info-700 dark:text-info-500">
                                        {{ t('redirect_to_payment_page') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Terms and Conditions -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" name="terms" type="checkbox"
                                    class="focus:ring-info-500 h-4 w-4 text-info-600 border-gray-300 rounded" required>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="font-medium text-gray-700 dark:text-slate-200">{{
                                    t('i_agree_to') }}
                                    {{ t('the') }} <a href="{{ route('terms.conditions') }}"
                                        class="text-info-600 hover:text-info-500">
                                        {{ t('terms_conditions')}}</a> {{ t('and') }} <a
                                        href="{{ route('privacy.policy') }}"
                                        class="text-info-600 hover:text-info-500">{{ t('privacy_policy')}}</a></label>
                            </div>
                        </div>


                        <div class="flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-4">
                            <!-- Back Button -->
                            <a href="{{ tenant_route('tenant.subscription') }}"
                                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-slate-700 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                {{ t('back_to_plans') }}
                            </a>

                            <!-- Submit Button -->
                            <x-button.primary type="submit" class="w-full sm:w-auto">
                                @if ($plan->isFree())
                                {{ t('subscribe_now') }}
                                @else
                                {{ t('proceed_to_payment') }}
                                @endif
                                <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </x-button.primary>
                        </div>

                    </form>
                </x-slot:content>
            </x-card>

        </div>
    </div>
</div>