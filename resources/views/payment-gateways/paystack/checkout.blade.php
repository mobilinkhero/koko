<x-app-layout>
    <x-slot:title>
        {{ t('paystack_payment') ?? 'Paystack Payment' }}
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
                            {{ t('paystack_payment') ?? 'Paystack Payment' }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('complete_payment_paystack') ?? 'Complete your payment with Paystack' }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
                <!-- Invoice Details Panel -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg"
                    x-data="{ expanded: true }">
                    <div class="flex items-center justify-between px-4 py-5 sm:px-6 bg-primary-50 dark:bg-slate-700 cursor-pointer"
                        @click="expanded = !expanded">
                        <div class="flex items-center">
                            <x-heroicon-s-receipt-refund class="h-6 w-6 text-gray-600 dark:text-gray-400 mr-3" />
                            <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                                {{ t('invoice_details') ?? 'Invoice Details' }}</h2>
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
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('invoice_number') ?? 'Invoice Number' }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-slate-200 text-right">
                                    {{ $invoice->invoice_number ?? format_draft_invoice_number() }}
                                </dd>
                            </div>

                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('description') ?? 'Description' }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->title }}
                                </dd>
                            </div>
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('subtotal') ?? 'Subtotal' }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->formatAmount($invoice->subTotal()) }}
                                </dd>
                            </div>

                            @if($invoice->getTax() > 0)
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('tax') ?? 'Tax' }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    {{ $invoice->formatAmount($invoice->getTax()) }}
                                </dd>
                            </div>
                            @endif

                            @if($remainingCredit > 0)
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('credit_applied') ?? 'Credit Applied' }}</dt>
                                <dd class="mt-1 text-sm text-green-600 dark:text-green-400 sm:mt-0 text-right">
                                    -{{ $currencySymbol }}{{ number_format($remainingCredit, 2) }}
                                </dd>
                            </div>
                            @endif

                            @if ($invoice->hasCoupon())
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2" data-invoice-discount-after-credit-row>
                                <dt class="text-sm font-medium text-gray-500 text-left">{{ t('coupon_discount') }} ({{ $invoice->coupon_code }})</dt>
                                <dd class="mt-1 text-sm text-green-600 dark:text-green-400 sm:mt-0 text-right" data-invoice-discount-after-credit>
                                    @php
                                    // Show the actual coupon discount applied after credit deduction
                                    $creditAmount = $remainingCredit > 0 ? min($remainingCredit, $invoice->total()) : 0;
                                    $displayCouponAmount = $invoice->getCouponDiscountAfterCredit($creditAmount);
                                    @endphp
                                    -{{ $invoice->formatAmount($displayCouponAmount) }}
                                </dd>
                            </div>
                            @endif

                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-700">
                                <dt class="text-lg font-semibold text-gray-900 dark:text-slate-200 text-left">{{ t('total') ?? 'Total' }}</dt>
                                <dd class="mt-1 text-lg font-bold text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                    @if($finalAmount > 0)
                                        {{ $currencySymbol }}{{ number_format($finalAmount, 2) }} {{ $currency }}
                                    @else
                                        {{ t('paid_with_credit') ?? 'Paid with Credit' }}
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Coupon Form Panel -->
                @include('partials.coupon-form', ['invoice' => $invoice])

                <!-- Payment Section -->
                <div class="mt-8">
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-slate-200 mb-4">
                                {{ t('payment_information') ?? 'Payment Information' }}
                            </h3>

                            <!-- Paystack Payment Options -->
                            <div class="space-y-4">
                                <!-- Payment Methods Info -->
                                <div class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <x-heroicon-o-information-circle class="h-5 w-5 text-blue-500 mr-3" />
                                    <div class="text-sm text-blue-700 dark:text-blue-300">
                                        <p class="font-medium">{{ t('paystack_accepts') ?? 'Paystack accepts:' }}</p>
                                        <p>{{ $gateway->getShortDescription() }}</p>
                                    </div>
                                </div>

                                @if($finalAmount > 0)
                                    @php
                                        $user = getUserByTenantId($invoice->tenant_id);
                                    @endphp
                                    <!-- Payment Button -->
                                    <div class="flex justify-center">
                                        <button
                                            id="paystack-payment-button"
                                            type="button"
                                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                            data-invoice-id="{{ $invoice->id }}"
                                            data-amount="{{ $finalAmount * 100 }}"
                                            data-currency="{{ $currency }}"
                                            data-email="{{ $user->email }}"
                                            data-public-key="{{ $publicKey }}"
                                        >
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="button-text">{{ t('pay_now') ?? 'Pay Now' }} {{ $currencySymbol }}{{ number_format($finalAmount, 2) }}</span>
                                        </button>
                                    </div>

                                    <!-- Payment Info -->
                                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                                        <p>{{ t('secure_payment_paystack') ?? 'Your payment is secured by Paystack' }}</p>
                                        <p class="mt-1">{{ t('minimum_amount') ?? 'Minimum amount' }}: {{ $currencySymbol }}{{ $minimumAmount }} {{ $currency }}</p>
                                    </div>
                                @else
                                    <!-- Credit Payment -->
                                    <div class="text-center">
                                        <div class="mb-4">
                                            <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-500" />
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-slate-200 mb-2">
                                            {{ t('invoice_paid_with_credit') ?? 'Invoice paid with credit balance' }}
                                        </h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4">
                                            {{ t('no_payment_required') ?? 'No additional payment required.' }}
                                        </p>
                                        <button
                                            id="process-credit-payment"
                                            type="button"
                                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            data-invoice-id="{{ $invoice->id }}"
                                        >
                                            {{ t('confirm_payment') ?? 'Confirm Payment' }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="mt-6 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg p-4">
                    <div class="flex">
                        <x-heroicon-o-shield-check class="h-5 w-5 text-green-500 mr-3 mt-0.5" />
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            <p class="font-medium">{{ t('secure_payment') ?? 'Secure Payment' }}</p>
                            <p>{{ t('paystack_security_notice') ?? 'Your payment information is processed securely by Paystack. We do not store your card details.' }}</p>
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>

    @push('scripts')
    <!-- Paystack Inline JS -->
    <script src="https://js.paystack.co/v1/inline.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentButton = document.getElementById('paystack-payment-button');
            const creditPaymentButton = document.getElementById('process-credit-payment');

            if (paymentButton) {
                paymentButton.addEventListener('click', function() {
                    const button = this;
                    const buttonText = button.querySelector('.button-text');
                    const spinner = button.querySelector('.animate-spin');

                    // Show loading state
                    button.disabled = true;
                    spinner.classList.remove('hidden');
                    buttonText.textContent = '{{ t("processing") ?? "Processing..." }}';

                    // Initialize payment with Paystack
                    initializePaystackPayment(button);
                });
            }

            if (creditPaymentButton) {
                creditPaymentButton.addEventListener('click', function() {
                    const button = this;
                    const invoiceId = button.dataset.invoiceId;

                    button.disabled = true;
                    button.textContent = '{{ t("processing") ?? "Processing..." }}';

                    // Process credit payment
                    processCreditPayment(invoiceId);
                });
            }
        });

        function initializePaystackPayment(button) {
            const invoiceId = button.dataset.invoiceId;
            const amount = parseInt(button.dataset.amount);
            const currency = button.dataset.currency;
            const email = button.dataset.email;
            const publicKey = button.dataset.publicKey;

            // First, get payment reference from server
            fetch(`{{ tenant_route('tenant.payment.paystack.process', ['invoice' => $invoice->id]) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    invoice_id: invoiceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // If payment was completed with credit
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    // Initialize Paystack popup
                    const handler = PaystackPop.setup({
                        key: publicKey,
                        email: email,
                        amount: amount,
                        currency: currency,
                        ref: data.reference,
                        callback: function(response) {
                            // Payment successful, redirect to callback URL
                            window.location.href = `{{ tenant_route('tenant.payment.paystack.callback') }}?reference=${response.reference}`;
                        },
                        onClose: function() {
                            // Reset button state when popup is closed
                            resetButtonState(button);
                        }
                    });

                    handler.openIframe();
                } else {
                    showNotification(data.error, 'danger');
                    resetButtonState(button);
                }
            });
        }

        function processCreditPayment(invoiceId) {
            fetch(`{{ tenant_route('tenant.payment.paystack.process', ['invoice' => $invoice->id]) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    invoice_id: invoiceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showNotification(data.error, 'danger');

                    // Reset button
                    const button = document.getElementById('process-credit-payment');
                    button.disabled = false;
                    button.textContent = '{{ t("confirm_payment") ?? "Confirm Payment" }}';
                }
            });
        }

        function resetButtonState(button) {
            const buttonText = button.querySelector('.button-text');
            const spinner = button.querySelector('.animate-spin');

            button.disabled = false;
            spinner.classList.add('hidden');
            buttonText.textContent = `{{ t('pay_now') ?? 'Pay Now' }} {{ $currencySymbol }}{{ number_format($finalAmount, 2) }}`;
        }
    </script>
    @endpush
</x-app-layout>
