<?php
// Batch load invoice settings to avoid multiple database queries
$invoiceSettings = get_batch_settings([
'invoice.bank_name',
'invoice.account_name',
'invoice.account_number',
'invoice.ifsc_code',
'payment.offline_description',
'payment.offline_instructions'
]);
?>

<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('offline_payment')); ?>

     <?php $__env->endSlot(); ?>

    <div class="max-w-5xl  mx-auto">
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <!-- Enhanced Header Section -->
             <?php $__env->slot('header', null, []); ?> 
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-banknotes'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-primary-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            <?php echo e(t('offline_payment')); ?>

                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            <?php echo e(t('complete_your_purchase')); ?>

                        </p>
                    </div>
                </div>
             <?php $__env->endSlot(); ?>

             <?php $__env->slot('content', null, []); ?> 
                <!-- Main Content -->
                <div class="space-y-6">
                    <!-- Invoice Details Panel -->
                    <div class="bg-white  dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg"
                        x-data="{ expanded: true }">
                        <div class="flex items-center justify-between px-4 py-5 sm:px-6 bg-primary-50 dark:bg-slate-700 cursor-pointer"
                            @click="expanded = !expanded">
                            <div class="flex items-center">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-receipt-refund'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-gray-600 dark:text-gray-400 mr-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                                    <?php echo e(t('invoice_details')); ?></h2>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-3 text-sm font-semibold text-primary-600 dark:text-slate-200">
                                    <?php echo e($invoice->formattedTotal()); ?>

                                </span>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => '!expanded','class' => 'h-5 w-5 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'expanded','class' => 'h-5 w-5 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            </div>
                        </div>

                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">
                            <dl class="divide-y divide-gray-200 dark:divide-slate-700">
                                <div class="px-4 py-4 sm:px-6 grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500 text-left"><?php echo e(t('invoice_number')); ?>

                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 text-right">
                                        <?php echo e($invoice->invoice_number ?? format_draft_invoice_number()); ?>

                                    </dd>
                                </div>

                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500 text-left"><?php echo e(t('description')); ?></dt>
                                    <dd class="mt-1 text-sm text-gray-900  dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoice->title); ?>

                                    </dd>
                                </div>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500 text-left"><?php echo e(t('subtotal')); ?></dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right" data-invoice-subtotal>
                                        <?php echo e($invoice->formatAmount($invoice->subTotal())); ?>

                                    </dd>
                                </div>

                                <?php
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

                                ?>


                                <!-- Detailed tax breakdown -->
                                <?php if(count($taxDetails) > 0): ?>
                                <?php $__currentLoopData = $taxDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500 text-left">
                                        <?php echo e($tax['name']); ?> (<?php echo e($tax['formatted_rate']); ?>)
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php
                                        // Calculate tax amount based on rate and subtotal if it's showing as 0
                                        $taxAmount = $tax['amount'];
                                        if ($taxAmount <= 0 && $tax['rate']> 0) {
                                            $taxAmount = $invoice->subTotal() * ($tax['rate'] / 100);
                                            }
                                            echo $invoice->formatAmount($taxAmount);
                                            ?>
                                    </dd>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                                <?php if($invoice->fee > 0): ?>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500 text-left"><?php echo e(t('fee')); ?>

                                    </dt>
                                    <dd class="mt-1 text-sm  text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoice->formatAmount($invoice->fee)); ?>

                                    </dd>
                                </div>
                                <?php endif; ?>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        <?php echo e(t('total_amount')); ?></dt>
                                    <dd
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right" data-invoice-total>
                                        <?php echo e($invoice->formattedTotal()); ?>

                                    </dd>
                                </div>

                                <!-- Coupon discount section (shown when no credit is applied) -->
                                <?php if($invoice->hasCoupon() && $remainingCredit == 0): ?>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-500 text-left"><?php echo e(t('coupon_discount')); ?> (<?php echo e($invoice->coupon_code); ?>)</dt>
                                    <dd class="mt-1 text-sm text-success-600 dark:text-success-400 sm:mt-0 text-right">
                                        -<?php echo e($invoice->formatAmount($invoice->coupon_discount)); ?>

                                    </dd>
                                </div>

                                <!-- Final payable amount when coupon is applied but no credit -->
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        <?php echo e(t('final_payable_amount')); ?></dt>
                                    <dd class="mt-1 text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoice->formatAmount($invoice->finalPayableAmount(0))); ?>

                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php if($remainingCredit > 0): ?>
                                <?php
                                // Calculate total for credit comparison
                                $total = $invoice->total();
                                ?>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        <?php echo e(t('total_credit_remaining')); ?></dt>
                                    <dd
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php
                                        if ($remainingCredit > $total) {
                                        $remainingCredit = $total;
                                        }
                                        echo '-' . $invoice->formatAmount($remainingCredit);
                                        ?>
                                    </dd>
                                </div>

                                <?php if($invoice->hasCoupon()): ?>
                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 dark:bg-slate-800" data-invoice-discount-after-credit-row>
                                    <dt class="text-sm font-medium text-gray-500 text-left"><?php echo e(t('coupon_discount')); ?> (<?php echo e($invoice->coupon_code); ?>)</dt>
                                    <dd class="mt-1 text-sm text-success-600 dark:text-success-400 sm:mt-0 text-right" data-invoice-discount-after-credit>
                                        <?php
                                        // Show the actual coupon discount applied after credit deduction
                                        $creditAmount = $remainingCredit > 0 ? min($remainingCredit, $invoice->total()) : 0;
                                        $displayCouponAmount = $invoice->getCouponDiscountAfterCredit($creditAmount);
                                        ?>
                                        -<?php echo e($invoice->formatAmount($displayCouponAmount)); ?>

                                    </dd>
                                </div>
                                <?php endif; ?>

                                <div class="px-4 py-4 sm:px-6 sm:grid sm:grid-cols-2 bg-gray-50 dark:bg-slate-800">
                                    <dt class="text-sm font-medium text-gray-900 dark:text-slate-500 text-left">
                                        <?php echo e(t('final_payable_amount')); ?></dt>
                                    <dd
                                        class="mt-1  text-sm font-bold text-primary-600 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php
                                        $finalamount = $invoice->finalPayableAmount($remainingCredit);
                                        echo $invoice->formatAmount($finalamount);
                                        ?>
                                    </dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Coupon Form Panel -->
                    <?php echo $__env->make('partials.coupon-form', ['invoice' => $invoice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <!-- Bank Details Panel -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg mt-6"
                        x-data="{ expanded: true }">

                        <div class="flex items-center justify-between px-6 py-4  bg-primary-50 dark:bg-slate-700 cursor-pointer"
                            @click="expanded = !expanded">
                            <div class="flex items-center">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-building-library'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-gray-400  mr-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-slate-200">
                                    <?php echo e(t('bank_details')); ?></h2>
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
                                    <dt class="text-sm font-medium text-gray-500"><?php echo e(t('account_name')); ?></dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoiceSettings['invoice.bank_name'] ?? 'N/A'); ?></dd>
                                </div>
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500"><?php echo e(t('account_name')); ?></dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoiceSettings['invoice.account_name'] ?? 'N/A'); ?>

                                    </dd>
                                </div>
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500"><?php echo e(t('account_number')); ?></dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoiceSettings['invoice.account_number'] ?? 'N/A'); ?>

                                    </dd>
                                </div>
                                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                                    <dt class="text-sm font-medium text-gray-500"><?php echo e(t('ifsc_code')); ?></dt>
                                    <dd class="text-sm text-gray-900 dark:text-slate-200 sm:mt-0 text-right">
                                        <?php echo e($invoiceSettings['invoice.ifsc_code'] ?? 'N/A'); ?></dd>
                                </div>
                                <!-- Optional: Add more fields if needed -->
                            </dl>
                        </div>
                    </div>


                    <!-- Payment Instructions Panel -->
                    <?php if($invoiceSettings['payment.offline_description'] && $invoiceSettings['payment.offline_instructions']): ?>
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg mt-6" x-data="{ expanded: false }">
                            <div class="flex items-center justify-between px-6 py-4 bg-primary-50 dark:bg-slate-700 cursor-pointer" @click="expanded = !expanded">
                                <div class="flex items-center space-x-3">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-information-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-800 dark:text-slate-200"><?php echo e(t('payment_instructions')); ?></h2>
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
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-information-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-primary-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-700 dark:text-slate-300">
                                                <?php echo e($invoiceSettings['payment.offline_description']); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions Section -->
                                <div class="px-6 py-4">
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-slate-200"><?php echo e(t('follow_these_steps')); ?>:</h3>
                                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-slate-300">
                                            <?php echo $invoiceSettings['payment.offline_instructions']; ?>

                                        </div>
                                    </div>
                                </div>

                                <!-- Important Note -->
                                <div class="px-6 py-3 bg-warning-50 dark:bg-slate-700/30">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-exclamation-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-warning-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-warning-700 dark:text-warning-400">
                                                <?php echo e(t('please_include_reference')); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Payment Confirmation Form -->
                    <div
                        class="bg-white dark:bg-slate-800 overflow-hidden shadow sm:rounded-lg border border-slate-200 dark:border-slate-700 ">
                        <div class="px-4 bg-primary-50 py-5 dark:bg-slate-700 sm:px-6 ">
                            <div class="flex items-center">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-success-500 mr-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200">
                                    <?php echo e(t('confirm_your_payment')); ?>

                                </h2>
                            </div>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <p class="text-sm text-gray-500 mb-6">
                                <?php echo e(t('provide_payment_details')); ?>

                            </p>

                            <!-- Form Alert -->
                            <div class="mb-6 rounded-md bg-warning-50 dark:bg-slate-600 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-clock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-warning-400 dark:text-warning-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-500">
                                            <?php echo e(t('verification_period')); ?>

                                        </h3>
                                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-500">
                                            <p>
                                                <?php echo e(t('subscription_active_after_verify')); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <form method="POST"
                                action="<?php echo e(tenant_route('tenant.payment.offline.process', ['invoice' => $invoice->id])); ?>"
                                x-data="{ submitting: false, paymentMethod: 'Bank Transfer' }"
                                @submit="submitting = true">
                                <?php echo csrf_field(); ?>

                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <!-- Payment Reference Field -->
                                        <div class="sm:col-span-3">
                                            <label for="payment_reference"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                <?php echo e(t('payment_reference_transaction_id')); ?> <span
                                                    class="text-danger-500">*</span>
                                                <span class="text-xs text-gray-500 font-normal"><?php echo e(t('invoice_number_prefilled')); ?></span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm ">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-hashtag'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                                </div>
                                                <input type="text" name="payment_reference" id="payment_reference"
                                                    value="<?php echo e($invoice->invoice_number); ?>" autocomplete="off"
                                                    class="pl-10 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:text-slate-200">
                                            </div>
                                            <?php $__errorArgs = ['payment_reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-danger-600"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!-- Payment Date Field -->
                                        <div class="sm:col-span-3">
                                            <label for="payment_date"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                <?php echo e(t('payment_date')); ?> <span class="text-danger-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-calendar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                                </div>
                                                <input type="date" name="payment_date" id="payment_date"
                                                    class="pl-10 focus:ring-primary-500 dark:bg-slate-800 dark:text-slate-200  focus:border-primary-500 block w-full sm:text-sm border-gray-300 dark:border-slate-700 rounded-md"
                                                    value="<?php echo e(date('Y-m-d')); ?>" required>
                                            </div>
                                            <?php $__errorArgs = ['payment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-danger-600"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!-- Payment Method Field -->
                                        <div class="sm:col-span-6">
                                            <label for="payment_method"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                <?php echo e(t('payment_method')); ?> <span class="text-danger-500">*</span>
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
                                                                    <?php echo e(t('bank_transfer')); ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Bank Transfer'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
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
                                                                    <?php echo e(t('cash_deposit')); ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Cash Deposit'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
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
                                                                    <?php echo e(t('check')); ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Check'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
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
                                                                    <?php echo e(t('other')); ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="paymentMethod === 'Other'"
                                                            class="h-5 w-5 text-primary-600">
                                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-danger-600"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <!-- Additional Details Field -->
                                        <div class="sm:col-span-6">
                                            <label for="payment_details"
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                                <?php echo e(t('additional_details')); ?>

                                                <span class="text-gray-500 text-xs"><?php echo e(t('optional')); ?></span>
                                            </label>
                                            <div class="mt-1">
                                                <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['id' => 'payment_details','name' => 'payment_details','rows' => '3','autocomplete' => 'off','placeholder' => 'Any additional information that would help us verify your payment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'payment_details','name' => 'payment_details','rows' => '3','autocomplete' => 'off','placeholder' => 'Any additional information that would help us verify your payment']); ?>
                                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
                                            </div>
                                            <?php $__errorArgs = ['payment_details'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-danger-600"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="pt-5">
                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                                :disabled="submitting">
                                                <span x-show="!submitting"><?php echo e(t('submit_payment_details')); ?></span>
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
                                                    <?php echo e(t('processing')); ?>

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
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-light-bulb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            </div>
                            <div class="ml-3 flex-1 md:flex md:justify-between">
                                <p class="text-sm text-gray-400">
                                    <?php echo e(t('need_assistance_with_payment')); ?>

                                </p>
                                <p class="mt-3 text-sm md:mt-0 md:ml-6">
                                    <a href="<?php echo e(tenant_route('tenant.tickets.index')); ?>"
                                        class="whitespace-nowrap font-medium  text-primary-600 dark:text-primary-500 hover:text-primary-500">
                                        <?php echo e(t('contact_support')); ?> <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/payment-gateways/offline/checkout.blade.php ENDPATH**/ ?>