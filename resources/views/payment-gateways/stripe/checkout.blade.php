<x-app-layout>
    <x-slot:title>
        {{ t('stripe_payment') }}
    </x-slot:title>

    <div class="max-w-5xl mx-auto">
        <x-card>
            <!-- Enhanced Header Section -->
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('stripe_payment') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('complete_payment_stripe') }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
                @push('styles')
                <style>
                    .StripeElement {
                        background-color: white;
                        padding: 12px;
                        border-radius: 4px;
                        border: 1px solid rgba(209, 213, 219, 1);
                        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                        transition: box-shadow 150ms ease;
                    }

                    .StripeElement--focus {
                        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
                    }

                    .StripeElement--invalid {
                        border-color: #fa755a;
                    }

                    .dark .StripeElement {
                        background-color: rgb(30 41 59);
                        border-color: rgb(51 65 85);
                    }

                    .StripeElement--webkit-autofill {
                        background-color: #fefde5 !important;
                    }
                </style>
                @endpush

                <!-- Invoice Details Panel -->
                <div class="bg-white  dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg"
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
                                <dd class="mt-1 text-sm text-gray-900  dark:text-slate-200 sm:mt-0 text-right">
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
                                <dd class="mt-1 text-sm  text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
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
                                    @php
                                    // Ensure we calculate and display the correct total with tax
                                    $subtotal = $invoice->subTotal();
                                    $taxAmount = 0;

                                    // Calculate actual tax amount if needed
                                    foreach ($taxDetails as $tax) {
                                    $taxAmount +=
                                    $tax['amount'] > 0
                                    ? $tax['amount']
                                    : $subtotal * ($tax['rate'] / 100);
                                    }

                                    $fee = $invoice->fee ?: 0;
                                    $total = $subtotal + $taxAmount + $fee;

                                    echo $invoice->formatAmount($total);
                                    @endphp
                                </dd>
                            </div>

                            @if ($remainingCredit > 0)
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                                <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                    {{ t('total_credit_remaining') }}</dt>
                                <dd
                                    class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
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
                                <dd
                                    class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
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

                <!-- Payment Form Panel -->
                <div
                    class="bg-white dark:bg-slate-800 overflow-hidden shadow sm:rounded-lg border border-slate-200 dark:border-slate-700 mt-6">
                    <div class="px-4 bg-primary-50 py-5 dark:bg-slate-700 sm:px-6">
                        <div class="flex items-center">
                            <x-heroicon-s-credit-card class="h-6 w-6 text-gray-500 dark:text-gray-400 mr-3" />
                            <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">{{ t('payment_details') }}
                            </h2>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div id="payment-form-container">
                            <form id="payment-form" class="space-y-6">
                                <div>
                                    <label for="card-element"
                                        class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">
                                        {{ t('credit_or_debit_card') }}
                                    </label>
                                    <div id="card-element" class="StripeElement dark:bg-slate-800 dark:text-slate-200">
                                    </div>
                                    <div id="card-errors" role="alert" class="text-danger-500 text-sm mt-1"></div>
                                </div>
                                @php
                                // Always use the correct calculation that includes both credit and coupon discounts
                                $creditAmount = $remainingCredit > 0 ? min($remainingCredit, $total) : 0;
                                $finalamount = $invoice->finalPayableAmount($creditAmount);
                                $payAmount = $invoice->formatAmount($finalamount);
                                @endphp
                                <div class="pt-5">
                                    <div class="flex justify-end">
                                        <button type="submit" id="submit-button"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50">
                                            {{ t('pay') }} {{ $payAmount }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div id="payment-success" class="hidden">
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
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        {{ t('continue') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="rounded-md bg-gray-50 p-4 shadow-sm dark:bg-slate-700 mt-6">
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
                                    class="whitespace-nowrap font-medium text-primary-600 dark:text-primary-500 hover:text-primary-500">
                                    {{ t('contact_support') }} <span aria-hidden="true">&rarr;</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                // Initialize Stripe
                const stripe = Stripe('{{ $publishableKey }}');
                const elements = stripe.elements();

                // Create card element
                const cardElement = elements.create('card');
                cardElement.mount('#card-element');

                // Handle form submission
                const form = document.getElementById('payment-form');
                const submitButton = document.getElementById('submit-button');
                const clientSecret = '{{ $clientSecret }}';

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    // Disable the submit button to prevent multiple clicks
                    submitButton.disabled = true;

                    // Confirm the card payment
                    const {
                        error,
                        paymentIntent
                    } = await stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: '{{ $invoice->tenant->billing_name }}',
                                email: '{{ $invoice->tenant->billing_email }}',
                            }
                        }
                    });

                    if (error) {
                        // Show error message
                        const errorElement = document.getElementById('card-errors');
                        errorElement.textContent = error.message;

                        // Re-enable the submit button
                        submitButton.disabled = false;
                    } else {
                        // The payment has been processed!
                        if (paymentIntent.status === 'succeeded') {
                            // Send the payment ID to our server
                            fetch('{{ tenant_route('tenant.payment.stripe.confirm') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        payment_intent_id: paymentIntent.id,
                                        invoice_id: '{{ $invoice->id }}'
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Show the success message
                                        document.getElementById('payment-form-container').classList.add(
                                            'hidden');
                                        document.getElementById('payment-success').classList.remove(
                                            'hidden');

                                        // Set the redirect link
                                        document.getElementById('success-redirect').href = data
                                        .redirect;

                                        // Redirect after a short delay
                                        setTimeout(() => {
                                            window.location.href = data.redirect;
                                        }, 3000);
                                    } else {
                                        // Show error message
                                        const errorElement = document.getElementById('card-errors');
                                        errorElement.textContent = data.message ||
                                            'An error occurred. Please try again.';

                                        // Re-enable the submit button
                                        submitButton.disabled = false;
                                    }
                                })
                                .catch(error => {
                                    // Show error message
                                    const errorElement = document.getElementById('card-errors');
                                    errorElement.textContent = 'An error occurred. Please try again.';

                                    // Re-enable the submit button
                                    submitButton.disabled = false;
                                });
                        }
                    }
                });
            });
    </script>
    @endpush
</x-app-layout>
