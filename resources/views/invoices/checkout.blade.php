<x-app-layout>
    <x-slot:title>
        {{ t('complete_your_payment') }}
    </x-slot:title>

    <div class="max-w-5xl mx-auto">
        <x-card>
            <!-- Enhanced Header Section -->
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-shopping-cart class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('complete_your_payment') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            #{{ $invoice->invoice_number ?? format_draft_invoice_number()}}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
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
                                <dd class="mt-1 text-sm  text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
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
                                        <dd
                                            class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
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
                                    <dd class="mt-1 text-sm  text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        {{ $invoice->formatAmount($invoice->fee) }}
                                    </dd>
                                </div>
                            @endif
                            <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                    {{ t('total_amount') }}</dt>
                                <dd
                                    class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
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
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        {{ t('final_payable_amount') }}</dt>
                                    <dd
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        @php
                                            $finalamount = $total - $remainingCredit;
                                            echo $invoice->formatAmount($finalamount);
                                        @endphp
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Payment Methods Panel -->
                <div
                    class="bg-white dark:bg-slate-800 overflow-hidden shadow sm:rounded-lg border border-slate-200 dark:border-slate-700 mt-6">
                    <div class="px-4 bg-primary-50 py-5 dark:bg-slate-700 sm:px-6">
                        <div class="flex items-center">
                            <x-heroicon-s-credit-card class="h-6 w-6 text-gray-500 dark:text-gray-400 mr-3" />
                            <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">{{
                                t('select_payment_method') }}</h2>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        @php
                        $billingManager = app('billing.manager');
                        $gateways = $billingManager->getActiveGateways();
                        @endphp

                        @if ($gateways->count() > 0)
                        <div class="grid gap-4">
                            @foreach ($gateways as $gateway)
                            <a href="{{ $gateway->getCheckoutUrl($invoice) }}"
                                class="relative bg-white dark:bg-slate-800 rounded-lg border border-gray-300 dark:border-slate-700 p-4 flex cursor-pointer hover:border-primary-500 hover:ring-2 hover:ring-primary-500 transition-all duration-200">
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center">
                                        <div class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900 dark:text-slate-200">
                                                {{ $gateway->getName() }}
                                            </span>
                                            <span class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                                                {{ $gateway->getDescription() }}
                                            </span>
                                        </div>
                                    </div>
                                    <x-heroicon-s-chevron-right class="h-5 w-5 text-gray-400" />
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <x-heroicon-o-x-circle class="h-6 w-6 text-gray-400" />
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
                                {{ t('no_payment_methods_available') }}
                            </p>
                        </div>
                        @endif
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
                                <a href="{{ url()->previous() }}"
                                    class="whitespace-nowrap font-medium text-primary-600 dark:text-primary-500 hover:text-primary-500">
                                    {{ t('back') }} <span aria-hidden="true">&rarr;</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</x-app-layout>