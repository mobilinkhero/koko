<x-app-layout>
    <x-slot:title>
        {{ t('razorpay_payment') }}
    </x-slot:title>

    <div class="max-w-full mx-auto">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- First Card: Invoice Details -->
            <x-card class="w-full lg:w-1/2">
                <x-slot:header>
                     <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('razorpay_payment') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('complete_payment_razorpay') }}
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

                            // Log values for debugging
                            $subtotal = $invoice->subTotal();
                            $tax = $invoice->getTax();
                            $fee = $invoice->fee ?: 0;
                            $calculatedTotal = $subtotal + $tax + $fee;
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
                                    if ($taxAmount <= 0 && $tax['rate']> 0) {
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
                                <dd class="mt-1 text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right"
                                   data-invoice-total>
                                    @php
                                    // Use the properly calculated total from the invoice model
                                    $total = $invoice->total();
                                    echo $invoice->formattedTotal();
                                    @endphp
                                </dd>
                            </div>

                            @if ($remainingCredit > 0)
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                                <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                    {{ t('total_credit_remaining') }}</dt>
                                <dd class="mt-1 text-sm font-bold text-info-600 dark:text-slate-200 sm:mt-0 text-right">
                                    @php
                                    echo '-' . $invoice->formatAmount($remainingCredit);
                                    @endphp
                                </dd>
                            </div>
                            @endif

                            @if ($invoice->hasCoupon())
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800" data-invoice-discount-after-credit-row>
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('coupon_discount') }} ({{ $invoice->coupon_code }})</dt>
                                <dd class="mt-1 text-sm text-success-600 dark:text-success-400 sm:mt-0 text-right" data-invoice-discount-after-credit>
                                    @php
                                    // Show the actual coupon discount applied after credit deduction
                                    $creditAmount = $remainingCredit > 0 ? min($remainingCredit, $invoice->total()) : 0;
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
                                <dd class="mt-1 text-sm font-bold text-info-600 dark:text-slate-200 sm:mt-0 text-right">
                                    @php
                                    $creditAmount = $remainingCredit > 0 ? min($remainingCredit, $total) : 0;
                                    $finalamount = $invoice->finalPayableAmount($creditAmount);
                                    echo $invoice->formatAmount($finalamount);
                                    @endphp
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Coupon Form Panel -->
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
                      <div class="px-4 py-5 sm:p-6">
                        <div id="payment-form-container" class="mt-12">
                            @php
                            // Default to the invoice's formatted total
                            $payAmount = $invoice->formattedTotal();

                            // Calculate final payable amount after credit deduction using new method
                            $creditAmount = $remainingCredit > 0 ? min($remainingCredit, $total) : 0;
                            $finalPayableAmount = $invoice->finalPayableAmount($creditAmount);
                            $payAmount = $invoice->formatAmount($finalPayableAmount);
                            @endphp

                            <div class="text-center mb-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ t('razorpay_supported_methods') }}
                                </p>
                                <div class="flex justify-center space-x-4 mb-6">
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">UPI</span>
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Cards</span>
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Net
                                        Banking</span>
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Wallets</span>
                                </div>

                            </div>

                            <div class="text-center">
                                <button type="button" id="razorpay-button"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500 disabled:opacity-50">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ t('pay') }} {{ $payAmount }}
                                </button>
                            </div>
                        </div>
                      </div>

                    <!-- Success Message (can remain inside this card) -->
                    <div id="payment-success" class="hidden mt-6">
                      <div
                        class="bg-white dark:bg-slate-800 overflow-hidden shadow sm:rounded-lg border border-slate-200 dark:border-slate-700 mt-6">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="text-center">
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-success-100 dark:bg-success-900">
                                    <x-heroicon-s-check class="h-6 w-6 text-success-600 dark:text-success-300" />
                                </div>
                                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-slate-200">{{
                                    t('payment_successful') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{
                                    t('payment_process_successfully') }}</p>
                                <div class="mt-6">
                                    <a href="#" id="success-redirect"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-info-600 hover:bg-info-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500">
                                        {{ t('continue') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>

                      <!-- Help Section -->
                <div class="rounded-md bg-gray-50 p-4 shadow-sm dark:bg-slate-700 mt-24">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-light-bulb class="h-5 w-5 text-gray-400" />
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-gray-400">
                                {{ t('need_assistance_with_payment') }}
                            </p>
                            <p class="mt-3 text-sm md:mt-0 md:ml-6">
                                <a href="{{ tenant_route('tenant.tickets.index') }}"
                                    class="whitespace-nowrap font-medium text-info-600 dark:text-info-500 hover:text-info-500">
                                    {{ t('contact_support') }} <span aria-hidden="true">&rarr;</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                </x-slot:content>
            </x-card>
        </div>

    </div>

    @push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const razorpayButton = document.getElementById('razorpay-button');
            const paymentSuccess = document.getElementById('payment-success');
            const paymentFormContainer = document.getElementById('payment-form-container');

            // Razorpay configuration
            const options = {
                key: '{{ $keyId }}',
                amount: {{ $order['amount'] }}, // Amount in smallest currency unit
                currency: '{{ $order['currency'] }}',
                name: '{{ config('app.name') }}',
                description: '{{ $invoice->title }}',
                order_id: '{{ $order['id'] }}',
                handler: function(response) {
                    // Payment successful, verify on server
                    verifyPayment(response);
                },
                prefill: {
                    name: '{{ getUserByTenantId(tenant_id())->firstname }} {{ getUserByTenantId(tenant_id())->lastname }}',
                    email: '{{ getUserByTenantId(tenant_id())->email }}'
                },
                theme: {
                    color: '#3B82F6'
                },
                modal: {
                    ondismiss: function() {
                        console.log('Razorpay modal dismissed');
                    },
                    // Add debug callback to check if currency is being changed
                    onGenerate: function() {
                        console.log('Razorpay modal generated with options:', {
                            currency: options.currency,
                            amount: options.amount,
                            order_id: options.order_id
                        });
                    }
                }
            };

            const rzp = new Razorpay(options);

            razorpayButton.addEventListener('click', function(e) {
                e.preventDefault();
                rzp.open();
            });

            function verifyPayment(response) {
                // Show loading state
                razorpayButton.disabled = true;
                razorpayButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ t("processing") }}...';

                // Verify payment on server
                fetch('{{ tenant_route('tenant.payment.razorpay.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                        invoice_id: '{{ $invoice->id }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide payment form and show success message
                        paymentFormContainer.style.display = 'none';
                        paymentSuccess.classList.remove('hidden');

                        // Set redirect URL
                        document.getElementById('success-redirect').href = data.redirect;

                        // Auto redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 3000);
                    } else {
                        // Show error message
                        alert(data.message || '{{ t("payment_verification_failed") }}');

                        // Reset button
                        razorpayButton.disabled = false;
                        razorpayButton.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>{{ t("pay") }} {{ $payAmount }}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ t("payment_verification_error") }}');

                    // Reset button
                    razorpayButton.disabled = false;
                    razorpayButton.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>{{ t("pay") }} {{ $payAmount }}';
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
