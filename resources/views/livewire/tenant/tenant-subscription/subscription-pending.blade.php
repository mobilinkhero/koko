<div class="container mx-auto py-10 max-w-3xl" wire:poll.5s="reload">
    <x-card>
        <x-slot:header>
            <h3 class="text-2xl font-medium text-gray-900 dark:text-slate-200">
                {{ t('subscription_pending_approval') }}
            </h3>
            <p class="mt-2 text-gray-600 dark:text-slate-500">
                {{ t('payment_has_been_recorded') }}
            </p>
        </x-slot:header>

        <x-slot:content>
            <!-- Animated Clock Icon -->
            <div class="flex items-center justify-center mb-6">
                <div x-data="{ pulse: true }" class="relative">
                    <!-- Yellow background circle with pulsing animation -->
                    <div class="bg-warning-100 p-4 rounded-full transform transition-all duration-700"
                        :class="{ 'scale-105': pulse, 'scale-100': !pulse }"
                        x-init="setInterval(() => { pulse = !pulse }, 1500)">
                        <!-- Base Clock SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-warning-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>

                    <!-- Rotating outer ring -->
                    <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
                        <svg class="w-full h-full animate-spin-slow" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="#FBBF24" stroke-width="2"
                                stroke-dasharray="4,8" />
                        </svg>
                    </div>

                    <!-- Subtle glow effect -->
                    <div
                        class="absolute -inset-1 bg-warning-200 rounded-full filter blur-md opacity-40 animate-pulse-slow">
                    </div>
                </div>
            </div>

            <div class="text-center mb-8">
                <h4 class="text-lg font-medium text-gray-900 dark:text-slate-200 mb-2">
                    {{ t('waiting_for_admin_approval') }}</h4>
                <p class="text-gray-600 dark:text-gray-500">
                    {{ t('pending_approval_payment') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <x-card>
                    <x-slot:content>
                        <span class="text-sm text-gray-500 block mb-1">{{ t('selected_plan') }}</span>
                        <span class="font-medium dark:text-slate-200">{{ $subscription->plan->name ?? 'Unknown'
                            }}</span>
                    </x-slot:content>
                </x-card>
                <x-card>
                    <x-slot:content>
                        <span class="text-sm text-gray-500 block mb-1">{{ t('price') }}</span>
                        @php
                        // Get the invoice related to this subscription
                        $invoice = \App\Models\Invoice\Invoice::where('subscription_id', $subscription->id)
                        ->latest()
                        ->first();
                        $basePrice = $subscription->plan->price;
                        $taxes = get_default_taxes();

                        if ($invoice) {
                        // If we have an invoice, show the total with tax
                        $priceDisplay = $invoice->formattedTotal();
                        } else {
                        // Calculate approximate tax if no invoice
                        $taxAmount = 0;
                        foreach ($taxes as $tax) {
                        $taxAmount += $basePrice * ($tax->rate / 100);
                        }
                        $totalWithTax = $basePrice + $taxAmount;
                        $priceDisplay = get_base_currency()->format($totalWithTax);
                        }

                        // Prepare tooltip text with price breakdown
                        $baseAmount = get_base_currency()->format($basePrice);
                        $taxBreakdown = [];
                        foreach ($taxes as $tax) {
                        $taxBreakdown[] = $tax->rate . '% ' . $tax->name;
                        }
                        @endphp
                        <span class="font-medium dark:text-slate-200">
                            {{ $priceDisplay }}
                        </span>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="py-1 rounded inline-block">
                                {{ $baseAmount }}<br>
                                @foreach ($taxBreakdown as $taxLine)
                                <span class="ml-2">+ {{ $taxLine }}</span><br>
                                @endforeach
                            </span>
                        </div>
                    </x-slot:content>
                </x-card>
                <x-card>
                    <x-slot:content>
                        <span class="text-sm text-gray-500 block mb-1">{{ t('payment_method') }}</span>
                        <span class="font-medium dark:text-slate-200">{{ ucfirst($subscription->payment_method ??
                            t('offline')) }}</span>
                    </x-slot:content>
                </x-card>
                <x-card>
                    <x-slot:content>
                        <span class="text-sm text-gray-500 block mb-1">{{ t('requested_on') }}</span>
                        <span class="font-medium dark:text-slate-200">{{ $subscription->created_at->format('M d, Y')
                            }}</span>
                    </x-slot:content>
                </x-card>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <p class="text-sm text-gray-600 dark:text-gray-500">
                {{ t('subscription_not_approve') }}
                <a href="mailto:{{ $supportEmail }}" class="text-primary-600 hover:text-primary-500">{{ $supportEmail
                    }}</a>.
            </p>
        </x-slot:footer>
    </x-card>
</div>