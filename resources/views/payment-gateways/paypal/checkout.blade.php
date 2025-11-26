<x-app-layout>
    <x-slot:title>
        {{ t('checkout_with_paypal') }}
    </x-slot:title>

    <div class="max-w-full mx-auto">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- First Card: Invoice Details -->
            <x-card class="w-full lg:w-1/2">
                <x-slot:header>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-credit-card class="w-6 h-6 text-primary-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                {{ t('paypal_payment') }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                {{ t('complete_payment_paypal') }}
                            </p>
                        </div>
                    </div>
                </x-slot:header>

                <x-slot:content>
                   <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg"
                x-data="{ expanded: true }">
                <div class="flex items-center justify-between px-4 py-5 sm:px-6 bg-primary-50 dark:bg-slate-700 cursor-pointer"
                    @click="expanded = !expanded">
                    <div class="flex items-center">
                        <x-heroicon-s-receipt-refund class="h-6 w-6 text-gray-600 dark:text-gray-400 mr-3" />
                        <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                            {{ t('invoice_details') }}</h2>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-3 text-sm font-semibold text-primary-600 dark:text-slate-200">
                            {{ $invoice->formattedTotal() }}
                        </span>
                        <x-heroicon-s-chevron-down x-show="!expanded" class="h-5 w-5 text-gray-500" />
                        <x-heroicon-s-chevron-up x-show="expanded" class="h-5 w-5 text-gray-500" />
                    </div>
                </div>

                <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    <dl class="divide-y divide-gray-200 dark:divide-slate-700">
                        <div class="px-4 py-4 sm:px-6 grid grid-cols-2">
                            <dt class="text-sm font-medium text-gray-500 text-left">{{ t('invoice_number') }}
                            </dt>
                            <dd class="text-sm text-gray-900 dark:text-slate-200 text-right">
                                {{ $invoice->invoice_number ?? format_draft_invoice_number() }}
                            </dd>
                        </div>

                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                            <dt class="text-sm font-medium text-gray-500 text-left">{{ t('description') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                {{ $invoice->title }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                            <dt class="text-sm font-medium text-gray-500 text-left">{{ t('subtotal') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right" data-invoice-subtotal>
                                {{ $invoice->formatAmount($invoice->subTotal()) }}
                            </dd>
                        </div>

                        @php
                        // Make sure taxes are calculated and applied
                        if ($invoice->taxes()->count() === 0) {
                            $invoice->applyTaxes();
                        }

                        // Recalculate tax details after ensuring they're applied
                        $taxDetails = $invoice->getTaxDetails();
                        $baseAmount = $invoice->formatAmount($invoice->subTotal());
                        $totalTaxAmount = 0;
                        $taxBreakdown = [];

                        // Calculate total tax amount from tax details
                        foreach ($taxDetails as $tax) {
                            $taxBreakdown[] = $tax['formatted_rate'] . ' ' . $tax['name'];
                            $totalTaxAmount += $tax['amount'];
                        }

                        // Force recalculation of total with taxes
                        $invoice->calculateTotalTaxAmount();

                        // Use the $remainingCredit variable passed from controller
                        // (similar to Stripe and Razorpay implementations)
                        @endphp

                        <!-- Detailed tax breakdown -->
                        @if (count($taxDetails) > 0)
                            @foreach ($taxDetails as $tax)
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">
                                    {{ $tax['name'] }} ({{ $tax['formatted_rate'] }})
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    @php
                                    // Calculate tax amount based on rate and subtotal if it's showing as 0
                                    $taxAmount = $tax['amount'];
                                    if ($taxAmount <= 0 && $tax['rate'] > 0) {
                                        $taxAmount = $invoice->subTotal() * ($tax['rate'] / 100);
                                    }
                                    echo $invoice->formatAmount($taxAmount);
                                    @endphp
                                </dd>
                            </div>
                            @endforeach
                        @endif

                        @if ($invoice->fee > 0)
                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                            <dt class="text-sm font-medium text-gray-500 text-left">{{ t('fee') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                {{ $invoice->formatAmount($invoice->fee) }}
                            </dd>
                        </div>
                        @endif

                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                            <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                {{ t('total_amount') }}</dt>
                            <dd
                                class="mt-1 text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right"
                                data-invoice-total>
                                {{ $invoice->formattedTotal() }}
                            </dd>
                        </div>

                        @if ($remainingCredit > 0)
                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                            <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                {{ t('total_credit_remaining') }}</dt>
                            <dd
                                class="mt-1 text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                {{ '-' . $invoice->formatAmount($remainingCredit) }}
                            </dd>
                        </div>
                        @endif

                        @if ($invoice->hasCoupon())
                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800" data-invoice-discount-after-credit-row>
                            <dt class="text-sm font-medium text-gray-500 text-left">{{ t('coupon_discount') }} ({{ $invoice->coupon_code }})</dt>
                            <dd class="mt-1 text-sm text-success-600 dark:text-success-400 sm:mt-0 text-right" data-invoice-discount-after-credit>
                                @php
                                // Show the actual coupon discount applied after credit deduction
                                $creditAmount = $remainingCredit > 0 ? $remainingCredit : 0;
                                $displayCouponAmount = $invoice->getCouponDiscountAfterCredit($creditAmount);
                                @endphp
                                -{{ $invoice->formatAmount($displayCouponAmount) }}
                            </dd>
                        </div>
                        @endif

                        <!-- Final payable amount section - show when there's credit OR when there's a coupon -->
                        @if ($remainingCredit > 0 || $invoice->hasCoupon())
                        <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                            <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                {{ t('final_payable_amount') }}</dt>
                            <dd
                                class="mt-1 text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                @php
                                $creditAmount = $remainingCredit > 0 ? $remainingCredit : 0;
                                @endphp
                                {{ $invoice->formatAmount($invoice->finalPayableAmount($creditAmount)) }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Coupon Form -->
            @include('partials.coupon-form', ['invoice' => $invoice])

                </x-slot:content>
            </x-card>

            <!-- Payment Details -->
            <x-card class="w-full lg:w-1/2">
                <x-slot:header>
                    <div class="flex items-center">
                        <x-heroicon-s-credit-card class="h-6 w-6 text-gray-500 dark:text-gray-400 mr-3" />
                        <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                            {{ t('payment_details') }}
                        </h2>
                    </div>
                </x-slot:header>

                <x-slot:content>
                  <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 bg-primary-50 dark:bg-slate-700">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-primary-600 dark:text-primary-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">{{ t('checkout_with_paypal') }}</h2>
                    </div>
                </div>

                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center">
                        <div id="paypal-button-container" class="w-full max-w-md mx-auto mb-6"></div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            {{ t('secure_payment_powered_by_paypal') }}
                        </div>

                        <div class="mt-6">
                            <a href="{{ tenant_route('tenant.invoices.show', ['id' => $invoice->id]) }}"
                               class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ t('back_to_invoice') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
                </x-slot:content>
            </x-card>
        </div>

    </div>

    @push('scripts')
        @php
            $paypalMode = get_setting('payment.paypal_mode', 'sandbox');
            $paypalSdkUrl =
                $paypalMode === 'sandbox' ? 'https://www.sandbox.paypal.com/sdk/js' : 'https://www.paypal.com/sdk/js';
        @endphp
        <script
            src="{{ $paypalSdkUrl }}?client-id={{ $settings['payment.paypal_client_id'] }}&currency={{ $invoice->currency->code ?? 'USD' }}">
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof paypal === 'undefined') {
                    document.getElementById('paypal-button-container').innerHTML =
                        '<div class="text-red-500 text-center p-4 border border-red-300 rounded">PayPal SDK failed to load. Please check your configuration.</div>';
                    return;
                }

                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return fetch(
                                '{{ tenant_route('tenant.payment.paypal.process', ['invoice' => $invoice->id]) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    return data.id;
                                }
                                throw new Error(data.error || 'Payment creation failed');
                            });
                    },
                    onApprove: function(data, actions) {
                        window.location.href =
                            '{{ tenant_route('tenant.payment.paypal.capture', ['invoice' => $invoice->id]) }}?token=' +
                            data.orderID;
                    },
                    onCancel: function(data) {
                        window.location.href =
                            '{{ tenant_route('tenant.invoices.show', ['id' => $invoice->id]) }}';
                    },
                    onError: function(err) {
                        console.error('PayPal error:', err);
                        alert('{{ t('payment_error_occurred') }}');
                    }
                }).render('#paypal-button-container');
            });
        </script>
    @endpush
</x-app-layout>
