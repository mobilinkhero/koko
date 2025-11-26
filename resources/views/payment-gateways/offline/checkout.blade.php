@php
// Batch load invoice settings to avoid multiple database queries
$invoiceSettings = get_batch_settings([
'invoice.bank_name',
'invoice.account_name',
'invoice.account_number',
'invoice.ifsc_code',
'payment.offline_description',
'payment.offline_instructions'
]);
@endphp

<x-app-layout>
    <x-slot:title>
        {{ t('offline_payment') }}
    </x-slot:title>

    <div class="max-w-5xl  mx-auto">
        <x-card>
            <!-- Enhanced Header Section -->
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-banknotes class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('offline_payment') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('complete_your_purchase') }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
                <!-- Main Content -->
                <div class="space-y-6">
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
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right" data-invoice-total>
                                        {{ $invoice->formattedTotal() }}
                                    </dd>
                                </div>

                                <!-- Coupon discount section (shown when no credit is applied) -->
                                @if ($invoice->hasCoupon() && $remainingCredit == 0)
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-500 text-left">{{ t('coupon_discount') }} ({{ $invoice->coupon_code }})</dt>
                                    <dd class="mt-1 text-sm text-success-600 dark:text-success-400 sm:mt-0 text-right">
                                        -{{ $invoice->formatAmount($invoice->coupon_discount) }}
                                    </dd>
                                </div>

                                <!-- Final payable amount when coupon is applied but no credit -->
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        {{ t('final_payable_amount') }}</dt>
                                    <dd class="mt-1 text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        {{ $invoice->formatAmount($invoice->finalPayableAmount(0)) }}
                                    </dd>
                                </div>
                                @endif

                                @if ($remainingCredit > 0)
                                @php
                                // Calculate total for credit comparison
                                $total = $invoice->total();
                                @endphp
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        {{ t('total_credit_remaining') }}</dt>
                                    <dd
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        @php
                                        if ($remainingCredit > $total) {
                                        $remainingCredit = $total;
                                        }
                                        echo '-' . $invoice->formatAmount($remainingCredit);
                                        @endphp
                                    </dd>
                                </div>

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

                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        {{ t('final_payable_amount') }}</dt>
                                    <dd
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        @php
                                        $finalamount = $invoice->finalPayableAmount($remainingCredit);
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

                    <!-- Bank Details Panel -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg mt-6"
                        x-data="{ expanded: true }">

                        <div class="flex items-center justify-between px-6 py-4  bg-primary-50 dark:bg-slate-700 cursor-pointer"
                            @click="expanded = !expanded">
                            <div class="flex items-center">
                                <x-heroicon-o-building-library class="h-6 w-6 text-gray-400  mr-3" />
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-slate-200">
                                    {{ t('bank_details') }}</h2>
                            </div>
                            <div class="flex items-center">
                                <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-500 transition-transform duration-200" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-500 transition-transform duration-200 rotate-180"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">
                            <dl class="divide-y divide-gray-200 dark:divide-gray-700 text-left">
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500">{{ t('account_name') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        {{ $invoiceSettings['invoice.bank_name'] ?? 'N/A' }}</dd>
                                </div>
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500">{{ t('account_name') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        {{ $invoiceSettings['invoice.account_name'] ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500">{{ t('account_number') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        {{ $invoiceSettings['invoice.account_number'] ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500">{{ t('ifsc_code') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        {{ $invoiceSettings['invoice.ifsc_code'] ?? 'N/A' }}</dd>
                                </div>
                                <!-- Optional: Add more fields if needed -->
                            </dl>
                        </div>
                    </div>


                    <!-- Payment Instructions Panel -->
                    @if ($invoiceSettings['payment.offline_description'] && $invoiceSettings['payment.offline_instructions'])
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg mt-6" x-data="{ expanded: false }">
                            <div class="flex items-center justify-between px-6 py-4 bg-primary-50 dark:bg-slate-700 cursor-pointer" @click="expanded = !expanded">
                                <div class="flex items-center space-x-3">
                                    <x-heroicon-o-information-circle class="h-6 w-6 text-gray-400" />
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-800 dark:text-slate-200">{{ t('payment_instructions') }}</h2>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transition-transform duration-200 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                <!-- Description Section -->
                                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 border-b border-gray-200 dark:border-slate-600">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <x-heroicon-o-information-circle class="h-5 w-5 text-primary-500" />
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-700 dark:text-slate-300">
                                                {{ $invoiceSettings['payment.offline_description'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions Section -->
                                <div class="px-6 py-4">
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-slate-200">{{ t('follow_these_steps') }}:</h3>
                                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
                                            {!! $invoiceSettings['payment.offline_instructions'] !!}
                                        </div>
                                    </div>
                                </div>

                                <!-- Important Note -->
                                <div class="px-6 py-3 bg-warning-50 dark:bg-slate-700/30">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <x-heroicon-s-exclamation-circle class="h-5 w-5 text-warning-400" />
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-warning-700 dark:text-warning-400">
                                                {{ t('please_include_reference') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Payment Confirmation Form -->
                    <div
                        class="bg-white dark:bg-slate-800 overflow-hidden shadow sm:rounded-lg border border-slate-200 dark:border-slate-700 ">
                        <div class="px-4 bg-primary-50 py-5 dark:bg-slate-700 sm:px-6 ">
                            <div class="flex items-center">
                                <x-heroicon-s-check-circle class="h-6 w-6 text-success-500 mr-3" />
                                <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                                    {{ t('confirm_your_payment') }}
                                </h2>
                            </div>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <p class="text-sm text-gray-500 mb-6">
                                {{ t('provide_payment_details') }}
                            </p>

                            <!-- Form Alert -->
                            <div class="mb-6 rounded-md bg-warning-50 dark:bg-slate-600 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-clock class="h-5 w-5 text-warning-400 dark:text-warning-500" />
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-500">
                                            {{ t('verification_period') }}
                                        </h3>
                                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-500">
                                            <p>
                                                {{ t('subscription_active_after_verify') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <form method="POST"
                                action="{{ tenant_route('tenant.payment.offline.process', ['invoice' => $invoice->id]) }}"
                                x-data="{ submitting: false, paymentMethod: 'Bank Transfer' }"
                                @submit="submitting = true">
                                @csrf

                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <!-- Payment Reference Field -->
                                        <div class="sm:col-span-3">
                                            <label for="payment_reference"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                {{ t('payment_reference_transaction_id') }} <span
                                                    class="text-danger-500">*</span>
                                                <span class="text-xs text-gray-500 font-normal">{{
                                                    t('invoice_number_prefilled') }}</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm ">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-s-hashtag class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <input type="text" name="payment_reference" id="payment_reference"
                                                    value="{{ $invoice->invoice_number }}" autocomplete="off"
                                                    class="pl-10 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:text-slate-200">
                                            </div>
                                            @error('payment_reference')
                                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Payment Date Field -->
                                        <div class="sm:col-span-3">
                                            <label for="payment_date"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                {{ t('payment_date') }} <span class="text-danger-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-s-calendar class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <input type="date" name="payment_date" id="payment_date"
                                                    class="pl-10 focus:ring-primary-500 dark:bg-slate-800 dark:text-slate-200  focus:border-primary-500 block w-full sm:text-sm border-gray-300 dark:border-slate-700 rounded-md"
                                                    value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            @error('payment_date')
                                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Payment Method Field -->
                                        <div class="sm:col-span-6">
                                            <label for="payment_method"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                {{ t('payment_method') }} <span class="text-danger-500">*</span>
                                            </label>
                                            <div class="mt-1">
                                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                                    <div class="relative bg-white dark:bg-slate-800 dark:border-slate-700 rounded-lg border border-gray-300 p-4 flex cursor-pointer focus:outline-none"
                                                        :class="{ 'border-primary-500 ring-2 ring-primary-500': paymentMethod === 'Bank Transfer' }"
                                                        @click="paymentMethod = 'Bank Transfer'">
                                                        <input type="radio" name="payment_method" value="Bank Transfer"
                                                            x-model="paymentMethod" class="sr-only"
                                                            aria-labelledby="payment-method-0-label">
                                                        <div class="flex-1 flex">
                                                            <div class="flex flex-col">
                                                                <span id="payment-method-0-label"
                                                                    class="block text-sm font-medium text-gray-900 dark:text-slate-200">
                                                                    {{ t('bank_transfer') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Bank Transfer'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <x-heroicon-s-check-circle class="h-5 w-5" />
                                                        </div>
                                                    </div>

                                                    <div class="relative bg-white dark:bg-slate-800 dark:border-slate-700 rounded-lg border border-gray-300 p-4 flex cursor-pointer focus:outline-none"
                                                        :class="{ 'border-primary-500 ring-2 ring-primary-500': paymentMethod === 'Cash Deposit' }"
                                                        @click="paymentMethod = 'Cash Deposit'">
                                                        <input type="radio" name="payment_method" value="Cash Deposit"
                                                            x-model="paymentMethod" class="sr-only"
                                                            aria-labelledby="payment-method-1-label">
                                                        <div class="flex-1 flex">
                                                            <div class="flex flex-col">
                                                                <span id="payment-method-1-label"
                                                                    class="block text-sm font-medium text-gray-900 dark:text-slate-200">
                                                                    {{ t('cash_deposit') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Cash Deposit'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <x-heroicon-s-check-circle class="h-5 w-5" />
                                                        </div>
                                                    </div>

                                                    <div class="relative bg-white dark:bg-slate-800 dark:border-slate-700 rounded-lg border border-gray-300 p-4 flex cursor-pointer focus:outline-none"
                                                        :class="{ 'border-primary-500 ring-2 ring-primary-500': paymentMethod === 'Check' }"
                                                        @click="paymentMethod = 'Check'">
                                                        <input type="radio" name="payment_method" value="Check"
                                                            x-model="paymentMethod" class="sr-only"
                                                            aria-labelledby="payment-method-2-label">
                                                        <div class="flex-1 flex">
                                                            <div class="flex flex-col">
                                                                <span id="payment-method-2-label"
                                                                    class="block text-sm font-medium text-gray-900 dark:text-slate-200">
                                                                    {{ t('check') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Check'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <x-heroicon-s-check-circle class="h-5 w-5" />
                                                        </div>
                                                    </div>

                                                    <div class="relative bg-white dark:bg-slate-800 rounded-lg border border-gray-300 dark:border-slate-700 p-4 flex cursor-pointer focus:outline-none"
                                                        :class="{ 'border-primary-500 ring-2 ring-primary-500': paymentMethod === 'Other' }"
                                                        @click="paymentMethod = 'Other'">
                                                        <input type="radio" name="payment_method" value="Other"
                                                            x-model="paymentMethod" class="sr-only"
                                                            aria-labelledby="payment-method-3-label">
                                                        <div class="flex-1 flex">
                                                            <div class="flex flex-col">
                                                                <span id="payment-method-3-label"
                                                                    class="block text-sm font-medium text-gray-900 dark:text-slate-200">
                                                                    {{ t('other') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Other'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <x-heroicon-s-check-circle class="h-5 w-5" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('payment_method')
                                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Additional Details Field -->
                                        <div class="sm:col-span-6">
                                            <label for="payment_details"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                {{ t('additional_details') }}
                                                <span class="text-gray-500 text-xs">{{ t('optional') }}</span>
                                            </label>
                                            <div class="mt-1">
                                                <x-textarea id="payment_details" name="payment_details" rows="3"
                                                    autocomplete="off"
                                                    placeholder="Any additional information that would help us verify your payment">
                                                </x-textarea>
                                            </div>
                                            @error('payment_details')
                                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="pt-5">
                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                                :disabled="submitting">
                                                <span x-show="!submitting">{{ t('submit_payment_details') }}</span>
                                                <span x-show="submitting" class="flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    {{ t('processing') }}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="rounded-md bg-gray-50 p-4 shadow-sm dark:bg-slate-700">
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
                                        class="whitespace-nowrap font-medium  text-primary-600 dark:text-primary-500 hover:text-primary-500">
                                        {{ t('contact_support') }} <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</x-app-layout>

