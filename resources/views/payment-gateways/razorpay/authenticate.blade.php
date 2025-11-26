<x-app-layout>
    <x-slot:title>
        {{ t('authenticate_recurring_payment') }} - {{ t('razorpay') }}
    </x-slot:title>

    <div class="max-w-5xl mx-auto">
        <x-card>
            <!-- Enhanced Header Section -->
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-info-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-shield-check class="w-6 h-6 text-info-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('authenticate_recurring_payment') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('authenticate_payment_description') }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
                <!-- RBI Compliance Notice -->
                <div
                    class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-info-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-info-700 dark:text-info-300">
                                {{ t('rbi_authentication_notice') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Details Panel -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg"
                    x-data="{ expanded: true }">
                    <div class="flex items-center justify-between px-4 py-5 sm:px-6 bg-info-50 dark:bg-slate-700 cursor-pointer"
                        @click="expanded = !expanded">
                        <div class="flex items-center">
                            <x-heroicon-s-receipt-refund class="h-6 w-6 text-gray-600 dark:text-gray-400 mr-3" />
                            <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                                {{ t('invoice_details') }}</h2>
                        </div>
                        <div class="flex items-center">
                            <span class="mr-3 text-sm font-semibold text-info-600 dark:text-slate-200">
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
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('invoice_number') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->invoice_number }}</dd>
                            </div>
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('subscription') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->subscription->plan->name ?? t('one_time_payment') }}</dd>
                            </div>
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('date') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('subtotal') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->formatAmount($invoice->subTotal()) }}</dd>
                            </div>

                            @php
                            $taxDetails = $invoice->getTaxDetails();
                            @endphp

                            @if (count($taxDetails) > 0)
                            @foreach ($taxDetails as $tax)
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">
                                    {{ $tax['name'] }} ({{ $tax['formatted_rate'] }})
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    @php
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
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('fee') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->formatAmount($invoice->fee) }}</dd>
                            </div>
                            @endif

                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                    {{ t('total_amount') }}</dt>
                                <dd class="mt-1 text-sm font-bold text-info-600 dark:text-slate-200 sm:mt-0 text-right">
                                    @php $total = $invoice->total(); @endphp
                                    {{ $invoice->formattedTotal() }}
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
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                    {{ t('final_payable_amount') }}</dt>
                                <dd class="mt-1 text-sm font-bold text-info-600 dark:text-slate-200 sm:mt-0 text-right">
                                    @php
                                    $creditAmount = min($remainingCredit, $total);
                                    $finalamount = $invoice->finalPayableAmount($creditAmount);
                                    echo $invoice->formatAmount($finalamount);
                                    @endphp
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Payment Form Panel -->
                <div
                    class="bg-white dark:bg-slate-800 overflow-hidden shadow sm:rounded-lg border border-slate-200 dark:border-slate-700 mt-6">
                    <div class="px-4 bg-info-50 py-5 dark:bg-slate-700 sm:px-6">
                        <div class="flex items-center">
                            <x-heroicon-s-credit-card class="h-6 w-6 text-gray-500 dark:text-gray-400 mr-3" />
                            <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">{{
                                t('payment_authentication') }}</h2>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6" id="paymentFormContainer">
                        <div class="space-y-6">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    {{ t('razorpay_authenticate_description') }}
                                </p>
                            </div>

                            <!-- Payment Button -->
                            <div class="flex justify-center">
                                <button id="razorpayButton"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-info-600 hover:bg-info-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ t('authenticate_payment') }} {{ $invoice->formatAmount($total) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Message (Hidden by default) -->
                <div id="paymentSuccess"
                    class="hidden bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg p-6 text-center mt-6">
                    <div
                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-success-100 dark:bg-success-900">
                        <svg class="h-6 w-6 text-success-600 dark:text-success-400" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-success-800 dark:text-success-200">{{
                        t('payment_successful') }}</h3>
                    <p class="mt-2 text-sm text-success-700 dark:text-success-300">
                        {{ t('payment_successful_message') }}
                    </p>
                    <div class="mt-4">
                        <x-button.primary href="#" id="success-redirect"
                            class="bg-success-600 hover:bg-success-700 focus:ring-success-500">
                            {{ t('view_subscription') }}
                        </x-button.primary>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>

    @push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                key: "{{ $keyId }}",
                amount: "{{ $order->amount }}",
                currency: "{{ $order->currency }}",
                name: "{{ config('app.name') }}",
                description: "{{ t('authenticate_recurring_payment') }} #{{ $invoice->invoice_number }}",
                order_id: "{{ $order->id }}",
                handler: function(response) {
                    verifyPayment(response);
                },
                prefill: {
                    name: "{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}",
                    email: "{{ auth()->user()->email }}",
                    contact: "{{ auth()->user()->phone ?? '' }}"
                },
                theme: {
                    color: "#4f46e5"
                },
                modal: {
                    ondismiss: function() {
                        console.log('Payment authentication cancelled');
                    }
                }
            };

            const rzp = new Razorpay(options);
            const razorpayButton = document.getElementById('razorpayButton');
            const paymentFormContainer = document.getElementById('paymentFormContainer');
            const paymentSuccess = document.getElementById('paymentSuccess');

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
                        invoice_id: '{{ $invoice->id }}',
                        transaction_id: '{{ $transaction->id }}',
                        is_authentication: true
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
                        razorpayButton.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>{{ t("authenticate_payment") }} {{ $finalamount }}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ t("payment_verification_error") }}');

                    // Reset button
                    razorpayButton.disabled = false;
                    razorpayButton.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>{{ t("authenticate_payment") }} {{ $finalamount }}';
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
