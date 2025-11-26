<x-app-layout>
    <x-slot:title>
        {{ t('transaction_details') }}
    </x-slot:title>
    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('transactions'), 'route' => route('admin.transactions.index')],
        ['label' => t('transaction_details')],
    ]" />
    <div>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <x-card class="relative rounded-lg self-start">
                <x-slot:content>
                    <div>
                        @if (session('success'))
                        <div
                            class="px-4 py-3 mb-6 text-sm text-success-800 dark:text-success-400 bg-success-100 dark:bg-success-900/30 border border-success-200 dark:border-success-800 rounded-md">
                            {{ session('success') }}
                        </div>
                        @endif

                        @if (session('error'))
                        <div
                            class="px-4 py-3 mb-6 text-sm text-danger-800 dark:text-danger-400 bg-danger-100 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-md">
                            {{ session('error') }}
                        </div>
                        @endif

                        <div class="flex flex-col justify-center item-center gap-4">
                            <!-- Transaction Information -->
                            <div>
                                <h3 class="mb-4 text-lg font-medium text-primary-600 dark:text-gray-100">
                                    {{ t('transaction_details') }}</h3>
                                <div
                                    class="p-4 border border-gray-200 dark:border-slate-700 rounded-md dark:bg-slate-700/50">
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('transaction_id') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->id }}</dd>
                                        </div>
                                        <div>
                                            @php
                                            $invoice = $transaction->invoice;
                                            $subtotal = $invoice->subTotal();
                                            $taxDetails = $invoice->getTaxDetails();
                                            $fee = $invoice->fee ?: 0;

                                            $taxAmount = collect($taxDetails)->sum(function ($tax) use ($subtotal) {
                                            return $tax['amount'] > 0
                                            ? $tax['amount']
                                            : $subtotal * ($tax['rate'] / 100);
                                            });

                                            $totalWithTax = $subtotal + $taxAmount + $fee;
                                            @endphp


                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('amount') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $invoice->formatAmount($transaction->amount) }} </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('status') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                @if ($transaction->isPending())
                                                <span
                                                    class="px-2 py-1 text-xs text-white bg-warning-500 rounded-full">{{
                                                    t('pending') }}</span>
                                                @elseif($transaction->isSuccessful())
                                                <span
                                                    class="px-2 py-1 text-xs text-white bg-success-500 rounded-full">{{
                                                    t('approved') }}</span>
                                                @elseif($transaction->isFailed())
                                                <span class="px-2 py-1 text-xs text-white bg-danger-500 rounded-full">{{
                                                    t('failed') }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('date') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->created_at->format('M d, Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('payment_reference') }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->getPaymentReference() ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('payment_date') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                @if ($transaction->getPaymentDate())
                                                    {{ $transaction->getPaymentDate()->format('M d, Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('payment_method') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->getPaymentMethod() }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('additional_details') }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                @php $additionalDetails = $transaction->getAdditionalDetails(); @endphp
                                                @if (!empty($additionalDetails))
                                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                                        @foreach ($additionalDetails as $key => $value)
                                                            <div>
                                                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $key }}</dt>
                                                                <dd class="text-xs text-gray-900 dark:text-gray-200">{{ $value }}</dd>
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                        </div>
                                        @if($transaction->getPaymentDetailsText())
                                        <div class="col-span-2">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('payment_details') }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {!! nl2br(e($transaction->getPaymentDetailsText())) !!}
                                            </dd>
                                        </div>
                                        @endif
                                        @if($transaction->getGatewayStatus())
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Gateway Status
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                @php
                                                    $status = strtolower($transaction->getGatewayStatus());
                                                    $badgeClass = match($status) {
                                                        'completed', 'captured', 'paid', 'success', 'verified' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                        'pending', 'processing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                        'failed', 'cancelled', 'refunded' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                    {{ strtoupper($transaction->getGatewayStatus()) }}
                                                </span>
                                            </dd>
                                        </div>
                                        @endif
                                        @if($transaction->getAmountReceived())
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Amount Received
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->getAmountReceived() }}
                                            </dd>
                                        </div>
                                        @endif
                                        @if($transaction->getGatewayTransactionId())
                                        <div class="col-span-2">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Gateway Transaction ID
                                            </dt>
                                            <dd class="mt-1 text-xs text-gray-900 dark:text-gray-200 font-mono break-all">
                                                {{ $transaction->getGatewayTransactionId() }}
                                            </dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>

                            <!-- Customer and Invoice Information -->
                            <div>
                                <h3 class="mb-4 text-lg font-medium text-primary-600 dark:text-gray-100">
                                    {{ t('invoice_information') }}</h3>
                                <div
                                    class="p-4 border border-gray-200 dark:border-slate-700 rounded-md dark:bg-slate-700/50">
                                    <dl class="grid grid-cols-2 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('invoice_number') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->invoice->invoice_number ??
                                                format_draft_invoice_number() }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('invoice_date') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->invoice->created_at->format('M d, Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('customer') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $user->firstname . ' ' . $user->lastname }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('email') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 break-all">
                                                {{ $user->email }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ t('description') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                                {{ $transaction->invoice->description ?? 'N/A' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-slot:content>
            </x-card>

            <div class="flex flex-col items-center gap-6">
                <x-card class="self-start w-full">
                    <x-slot:content>
                        <!-- Invoice Items -->
                        <div>
                            <h3 class="mb-4 text-lg font-medium text-primary-600 dark:text-gray-100">
                                {{ t('Invoice Items') }}</h3>
                            <div class="overflow-x-auto border border-gray-200 dark:border-slate-700 rounded-md">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                    <thead class="bg-gray-50 dark:bg-slate-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-300 uppercase">
                                                {{ t('description') }}
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-300 uppercase">
                                                {{ t('quantity') }}
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-300 uppercase">
                                                {{ t('price') }}
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-300 uppercase">
                                                {{ t('total') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                        @foreach ($transaction->invoice->items as $item)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $item->title }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $item->description }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $item->quantity }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $transaction->invoice->formatAmount($item->amount) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $transaction->invoice->formatAmount($item->amount *
                                                    $item->quantity) }}
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-slate-700">
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-500 dark:text-gray-300">
                                                {{ t('subtotal') }}
                                            </td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
                                                {{
                                                $transaction->invoice->formatAmount($transaction->invoice->subTotal())
                                                }}
                                            </td>
                                        </tr>

                                        <!-- Coupon discount row (show only if coupon is applied) -->
                                        @if ($transaction->invoice->hasCoupon())
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-500 dark:text-gray-300">
                                                <x-heroicon-s-tag class="inline-block w-4 h-4 mr-1" /> {{ t('coupon_discount') }} ({{ $transaction->invoice->coupon_code }})
                                            </td>
                                            <td class="px-6 py-3 text-sm font-medium text-success-600 dark:text-success-400">
                                                -{{ $transaction->invoice->formatAmount($transaction->invoice->getCouponDiscount()) }}
                                            </td>
                                        </tr>
                                        @endif

                                        @php
                                        $taxDetails = $transaction->invoice->getTaxDetails();
                                        $subtotal = $transaction->invoice->subTotal();

                                        // Create price breakdown display
                                        $priceBreakdown = $transaction->invoice->formatAmount($subtotal);
                                        $taxBreakdown = [];
                                        foreach ($taxDetails as $tax) {
                                        $taxBreakdown[] = $tax['formatted_rate'] . ' ' . $tax['name'];
                                        }
                                        @endphp

                                        @if (count($taxDetails) > 0)
                                        <!-- Tax Details Rows -->
                                        @foreach ($taxDetails as $tax)
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-500 dark:text-gray-300">
                                                {{ $tax['name'] }} ({{ $tax['formatted_rate'] }})
                                            </td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
                                                @php
                                                // Calculate tax amount based on rate and subtotal if it's showing as 0
                                                $taxAmount = $tax['amount'];
                                                if ($taxAmount <= 0 && $tax['rate']> 0) {
                                                    $taxAmount = $subtotal * ($tax['rate'] / 100);
                                                    echo $transaction->invoice->formatAmount($taxAmount);
                                                    } else {
                                                    echo $tax['formatted_amount'];
                                                    }
                                                    @endphp
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif
                                        @if ($transaction->invoice->fee > 0)
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-500 dark:text-gray-300">
                                                {{ t('fee') }}
                                            </td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
                                                {{ $transaction->invoice->formatAmount($transaction->invoice->fee) }}
                                            </td>
                                        </tr>
                                        @endif
                                        <tr class="bg-primary-50 dark:bg-slate-700">
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-900 dark:text-gray-100">
                                                {{ t('total') }}
                                            </td>
                                            <td
                                                class="px-6 py-3 text-sm font-bold text-primary-600 dark:text-primary-300">
                                                @php
                                                // Total should be subtotal minus coupon discount (if any)
                                                $totalAfterCoupon = $transaction->invoice->subTotal();
                                                if ($transaction->invoice->hasCoupon()) {
                                                    $appliedCredit = $transaction->invoice->getCreditTransactions()->sum('amount');
                                                    $couponDiscount = $transaction->invoice->getCouponDiscountAfterCredit($appliedCredit);
                                                    $totalAfterCoupon -= $couponDiscount;
                                                }
                                                echo $transaction->invoice->formatAmount($totalAfterCoupon);
                                                @endphp
                                            </td>
                                        </tr>
                                        @if ($remainingCredit > 0 && $transaction->status == 'pending')
                                        <tr class="bg-primary-50 dark:bg-slate-700">
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-900 dark:text-gray-100">
                                                {{ t('total_credit_remaining') }}
                                            </td>
                                            <td
                                                class="px-6 py-3 text-sm font-bold text-primary-600 dark:text-primary-300">
                                                @php
                                                $invoiceTotal = $transaction->invoice->total();
                                                if ($remainingCredit > $invoiceTotal) {
                                                $remainingCredit = $invoiceTotal;
                                                }
                                                echo '-' . $invoice->formatAmount($remainingCredit);
                                                @endphp
                                            </td>
                                        </tr>
                                        <tr class="bg-primary-50 dark:bg-slate-700">
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-900 dark:text-gray-100">
                                                {{ t('final_total') }}
                                            </td>
                                            <td
                                                class="px-6 py-3 text-sm font-bold text-primary-600 dark:text-primary-300">
                                                @php
                                                // Final amount should match the actual transaction amount
                                                // This accounts for the correct order: total - credit - coupon
                                                $appliedCredit = $transaction->invoice->getCreditTransactions()->sum('amount');
                                                $finalAmount = $transaction->invoice->finalPayableAmount($appliedCredit);
                                                echo $invoice->formatAmount($finalAmount);
                                                @endphp
                                            </td>
                                        </tr>
                                        @elseif (count($creditTransactions) > 0)
                                        <tr class="bg-primary-50 dark:bg-slate-700">
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-900 dark:text-gray-100">
                                                {{ t('total_credit_remaining') }}
                                            </td>
                                            <td
                                                class="px-6 py-3 text-sm font-bold text-primary-600 dark:text-primary-300">
                                                @php
                                                $invoiceTotal = $transaction->invoice->total();
                                                $credits = $creditTransactions->sum('amount');
                                                if ($credits > $invoiceTotal) {
                                                $credits = $invoiceTotal;
                                                }
                                                echo '-' . $invoice->formatAmount($credits);
                                                @endphp
                                            </td>
                                        </tr>
                                        <tr class="bg-primary-50 dark:bg-slate-700">
                                            <td colspan="3"
                                                class="px-6 py-3 text-sm font-medium text-right text-gray-900 dark:text-gray-100">
                                                {{ t('final_total') }}
                                            </td>
                                            <td
                                                class="px-6 py-3 text-sm font-bold text-primary-600 dark:text-primary-300">
                                                @php
                                                // Use the same correct calculation as the first section
                                                $appliedCredit = $transaction->invoice->getCreditTransactions()->sum('amount');
                                                $finalAmount = $transaction->invoice->finalPayableAmount($appliedCredit);
                                                echo $invoice->formatAmount($finalAmount);
                                                @endphp
                                            </td>
                                        </tr>
                                        @endif
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </x-slot:content>
                </x-card>
                @if (checkPermission('admin.transactions.actions'))
                <x-card class="w-full">
                    <x-slot:content>
                        <!-- Actions -->
                        <div>
                            <!-- Initialize Alpine.js store for form state sharing -->
                            <script>
                                document.addEventListener('alpine:init', () => {
                                        Alpine.store('formState', {
                                            processing: false
                                        });
                                    });
                            </script>

                            @if ($transaction->isPending() && $transaction->type === 'offline')
                            <div x-data="{
                                        showModal: false,
                                        modalTitle: '',
                                        modalMessage: '',
                                        confirmButtonText: '',
                                        confirmButtonClass: '',
                                        targetForm: null,
                                        processing: false,
                                        setupModal(title, message, buttonText, buttonClass, formId) {
                                            this.modalTitle = title;
                                            this.modalMessage = message;
                                            this.confirmButtonText = buttonText;
                                            this.confirmButtonClass = buttonClass;
                                            this.targetForm = formId;
                                            this.showModal = true;
                                        },
                                        confirmAction() {
                                            this.processing = true;
                                            // Update Alpine store to share processing state
                                            Alpine.store('formState').processing = true;
                                            this.showModal = false;
                                            // Find the form by ID and submit it
                                            const form = document.getElementById(this.targetForm);
                                            if (form) {
                                                // Set submitting state if it exists in the form's Alpine data
                                                if (form.__x && form.__x.$data.submitting !== undefined) {
                                                    form.__x.$data.submitting = true;
                                                }
                                                form.submit();
                                            }
                                        }
                                      }"
                                @modal-trigger.window="setupModal($event.detail.title, $event.detail.message, $event.detail.buttonText, $event.detail.buttonClass, $event.detail.formId)"
                                class="grid grid-cols-1 gap-4 w-full">

                                <!-- Approve Form -->
                                <div>
                                    <h3 class="mb-4 text-lg font-medium text-success-600 dark:text-success-500">
                                        {{ t('approve_payment') }}
                                    </h3>
                                    <div
                                        class="p-4 border border-gray-200 dark:border-slate-700 rounded-md dark:bg-slate-700/50">
                                        <form id="approve-form" x-data="{ submitting: false }" @submit.prevent="
                                                    $dispatch('modal-trigger', {
                                                        title: '{{ t('confirm_approval') }}',
                                                        message: '{{ t('are_you_sure_approve') }}',
                                                        buttonText: '{{ t('yes_approve') }}',
                                                        buttonClass: 'bg-success-600 hover:bg-success-700',
                                                        formId: 'approve-form'
                                                    });
                                                " action="{{ route('admin.transactions.approve', $transaction->id) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="credit_used" value="{{ $remainingCredit }}">
                                            <p class="mb-4 text-sm text-gray-700 dark:text-gray-300">
                                                {{ t('approve_this_payment') }}
                                            </p>
                                            <x-button.loading-button type="submit" target="approve"
                                                class="w-full bg-success-600 hover:bg-success-700">
                                                <span x-text="(submitting || $store.formState.processing) ? '{{ t('processing') }}' :
                                                             '{{ t('approve_payment') }}'"></span>
                                            </x-button.loading-button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Reject Form -->
                                <div>
                                    <h3 class="mb-4 text-lg font-medium text-danger-600 dark:text-danger-500">
                                        {{ t('reject_payment') }}</h3>
                                    <div
                                        class="p-4 border border-gray-200 dark:border-slate-700 rounded-md dark:bg-slate-700/50">
                                        <form id="reject-form" x-data="{ submitting: false, reason: '' }"
                                            @submit.prevent="
                                                        if (reason.trim() === '') return;
                                                        $dispatch('modal-trigger', {
                                                            title: '{{ t('confirm_rejection') }}',
                                                            message: '{{ t('reject_payment_description') }}',
                                                            buttonText: '{{ t('yes_reject') }}',
                                                            buttonClass: 'bg-danger-600 hover:bg-danger-700',
                                                            formId: 'reject-form'
                                                        });
                                                    "
                                            action="{{ route('admin.transactions.reject', $transaction->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-4">
                                                <label for="reason"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                                    t('reason_for_rejection') }}</label>
                                                <x-textarea id="reason" name="reason" rows="3" required x-model="reason"
                                                    placeholder="{{ t('please_provide_reason') }}" />
                                                @error('reason')
                                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-500">
                                                    {{ $message }}</p>
                                                @enderror
                                            </div>
                                            <button type="submit" :disabled="reason.trim() === '' || submitting || $store.formState
                                                            .processing"
                                                :class="(reason.trim() === '' || submitting || $store.formState
                                                            .processing) ?
                                                        'w-full px-4 py-2 text-white bg-danger-600 rounded-md opacity-75 cursor-not-allowed' :
                                                        'w-full px-4 py-2 text-white bg-danger-600 rounded-md hover:bg-danger-700'">
                                                <span
                                                    x-text="(submitting || $store.formState.processing) ? '{{ t('processing') }}' : '{{ t('reject_payment') }}'">
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Confirmation Modal -->
                                <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    style="display: none;">
                                    <div
                                        class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                                            aria-hidden="true" @click="showModal = false"></div>

                                        <!-- Modal panel -->
                                        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                                            <div class="px-4 pt-5 pb-4 bg-white dark:bg-slate-800 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div
                                                        class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-gray-100 dark:bg-slate-700 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100"
                                                            x-text="modalTitle"></h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500 dark:text-gray-400"
                                                                x-text="modalMessage">
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="px-4 py-3 bg-gray-50 dark:bg-slate-700 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button type="button" @click="confirmAction()" :class="'inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm sm:ml-3 sm:w-auto sm:text-sm ' +
                                                            confirmButtonClass" :disabled="$store.formState.processing"
                                                    x-text="$store.formState.processing ? '{{ t('processing') }}' : confirmButtonText">
                                                </button>
                                                <button type="button" @click="showModal = false"
                                                    :disabled="$store.formState.processing"
                                                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                                    :class="$store.formState.processing ?
                                                                'opacity-75 cursor-not-allowed' :
                                                                ''">
                                                    {{ t('cancel') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($transaction->isSuccessful())
                            <!-- Payment Approved Message -->
                            <div class="col-span-2 mt-6">
                                <div
                                    class="p-4 border border-success-200 dark:border-success-800 rounded-md bg-success-50 dark:bg-success-900/30">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-success-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-success-800 dark:text-success-400">
                                                {{ t('payment_approved_title') }}</h3>
                                            <div class="mt-2 text-sm text-success-700 dark:text-success-300">
                                                <p>{{ t('payment_approved_message') }}</p>
                                                @if ($transaction->updated_at)
                                                <p class="mt-2 font-medium">{{ t('payment_approved_on') }}
                                                    {{ format_date_time($transaction->updated_at) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($transaction->isFailed())
                            <!-- Payment Rejected Message -->
                            <div class="col-span-2 mt-6">
                                <div
                                    class="p-4 border border-danger-200 dark:border-danger-800 rounded-md bg-danger-50 dark:bg-danger-900/30">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-danger-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-400">
                                                {{ t('payment_rejected_title') }}</h3>
                                            <div class="mt-2 text-sm text-danger-700 dark:text-danger-300">
                                                <p>{{ t('payment_rejected_message') }}</p>
                                                @if ($transaction->error)
                                                <p class="mt-2 font-medium">
                                                    {{ t('payment_rejection_reason') }}{{ $transaction->error }}
                                                </p>
                                                @endif
                                                @if ($transaction->updated_at)
                                                <p class="mt-2 font-medium">{{ t('payment_rejected_on') }}
                                                    {{ format_date_time($transaction->updated_at) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </x-slot:content>
                </x-card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
