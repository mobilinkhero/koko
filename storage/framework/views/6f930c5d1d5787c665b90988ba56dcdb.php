<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow sm:rounded-lg mt-6">
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('couponForm', () => ({
                open: true,
                code: '',
                loading: false,
                applied: <?php echo e(isset($invoice) && $invoice->hasCoupon() ? 'true' : 'false'); ?>,
                appliedCode: '<?php echo e(isset($invoice) && $invoice->hasCoupon() ? str_replace('\'', '\\\'', $invoice->coupon_code) : ''); ?>',
                errorMessage: '',
                discountAmount: <?php echo e(isset($invoice) && $invoice->hasCoupon() ? $invoice->getCouponDiscount() : 0); ?>,
                formattedDiscount: '<?php echo e(isset($invoice) && $invoice->hasCoupon() ? str_replace('\'', '\\\'', $invoice->formatAmount($invoice->getCouponDiscount())) : ''); ?>',
                csrfToken: '<?php echo e(csrf_token()); ?>',

                validateCoupon() {
                    if (this.code.trim() === '') return;

                    this.loading = true;
                    this.errorMessage = '';

                    fetch('<?php echo e(tenant_route('tenant.coupon.validate')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({
                            code: this.code,
                            invoice_id: '<?php echo e(isset($invoice) ? $invoice->id : ''); ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;

                        if (data.success) {
                            this.applyCoupon();
                        } else {
                            this.errorMessage = data.message || '<?php echo e(t('invalid_coupon_code')); ?>';
                        }
                    })
               
                },

                applyCoupon() {
                    if (this.code.trim() === '') return;

                    this.loading = true;

                    fetch('<?php echo e(tenant_route('tenant.coupon.apply')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({
                            code: this.code,
                            invoice_id: '<?php echo e(isset($invoice) ? $invoice->id : ''); ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;

                        if (data.success) {
                            this.applied = true;
                            this.appliedCode = data.coupon_code || this.code;  // Use server response for correct case
                            this.discountAmount = data.discount_amount;
                            this.formattedDiscount = data.formatted_discount;

                            // Update the invoice display
                            this.updateInvoiceDisplay(data);

                            // Refresh the page to ensure all server-side calculations are updated
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            this.errorMessage = data.message || '<?php echo e(t('error_applying_coupon')); ?>';
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        this.errorMessage = '<?php echo e(t('error_applying_coupon')); ?>';
                        console.error('Error applying coupon:', error);
                    });
                },

                removeCoupon() {
                    this.loading = true;
                    this.errorMessage = '';

                    fetch('<?php echo e(tenant_route('tenant.coupon.remove')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            invoice_id: '<?php echo e(isset($invoice) ? $invoice->id : ''); ?>'
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 419) {
                                throw new Error('CSRF_TOKEN_EXPIRED');
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.loading = false;

                        if (data.success) {
                            this.applied = false;
                            this.appliedCode = '';
                            this.code = '';
                            this.discountAmount = 0;
                            this.formattedDiscount = '';

                            // Update the invoice display
                            this.updateInvoiceDisplay(data);

                            // Refresh the page to ensure all server-side calculations are updated
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            this.errorMessage = data.message || '<?php echo e(t('error_removing_coupon')); ?>';
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error('Error removing coupon:', error);

                        // Handle CSRF token expiry or session timeout
                        if (error.message === 'CSRF_TOKEN_EXPIRED') {
                            this.errorMessage = 'Your session has expired. Refreshing the page...';
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else if (error.message && (error.message.includes('Failed to fetch') || error.message.includes('NetworkError'))) {
                            this.errorMessage = 'Network error. Please check your connection and try again.';
                        } else {
                            this.errorMessage = '<?php echo e(t('error_removing_coupon')); ?>';
                        }
                    });
                },

                updateInvoiceDisplay(data) {
                    // Update the discount after credit row (the only coupon discount we show)
                    const discountAfterCreditRow = document.querySelector('[data-invoice-discount-after-credit-row]');
                    const discountAfterCreditAmount = document.querySelector('[data-invoice-discount-after-credit]');

                    if (discountAfterCreditRow && discountAfterCreditAmount) {
                        if (this.applied) {
                            discountAfterCreditRow.classList.remove('hidden');
                            // The amount will be recalculated on server side
                        } else {
                            discountAfterCreditRow.classList.add('hidden');
                        }
                    }

                    // Update the total
                    const totalElement = document.querySelector('[data-invoice-total]');
                    if (totalElement && data.formatted_total) {
                        totalElement.textContent = data.formatted_total;
                    }

                    // If there's a PayPal amount input, update it
                    const paypalAmountInput = document.querySelector('input[name="amount"]');
                    if (paypalAmountInput && data.total) {
                        paypalAmountInput.value = data.total;
                    }

                    // Update final payable amount and coupon after credit display
                    this.updateFinalAmounts();
                },

                updateFinalAmounts() {
                    // Show/hide the after-credit discount row (the only coupon discount shown)
                    const discountAfterCreditRow = document.querySelector('[data-invoice-discount-after-credit-row]');

                    if (discountAfterCreditRow) {
                        if (this.applied) {
                            discountAfterCreditRow.classList.remove('hidden');
                        } else {
                            discountAfterCreditRow.classList.add('hidden');
                        }
                    }
                },

                // Method to refresh CSRF token via a separate request
                refreshCsrfToken() {
                    fetch('/csrf-token', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.csrf_token) {
                            this.csrfToken = data.csrf_token;
                            console.log('CSRF token refreshed');
                        }
                    })
                    .catch(error => {
                        console.error('Could not refresh CSRF token:', error);
                    });
                }
            }))
        })
    </script>

    <div x-data="couponForm">
        <!-- Coupon Header -->
        <div class="px-4 py-5 sm:px-6 bg-gray-50 dark:bg-slate-700 flex justify-between items-center cursor-pointer"
             @click="open = !open">
            <div class="flex items-center gap-3">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-ticket'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-info-600 dark:text-info-400 ']); ?>
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
                <h2 class="text-lg font-medium text-gray-900 dark:text-slate-200"><?php echo e(t('coupon_code')); ?></h2>
                <span x-show="applied" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200 mr-2">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-1']); ?>
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
                    <?php echo e(t('applied')); ?>

                </span>
            </div>
            <div >
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => '!open','class' => 'h-5 w-5 text-gray-500']); ?>
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
<?php $component->withAttributes(['x-show' => 'open','class' => 'h-5 w-5 text-gray-500']); ?>
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

        <!-- Coupon Form -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0" class="p-4">

            <div x-show="!applied">
                <label for="coupon_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <?php echo e(t('enter_coupon_code')); ?>

                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="text" x-model="code" id="coupon_code" name="coupon_code"
                           class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                           placeholder="<?php echo e(t('enter_code_here')); ?>"
                           @keydown.enter.prevent="validateCoupon()">
                    <button type="button" @click="validateCoupon()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            :disabled="loading || code.trim() === ''">
                        <span x-show="loading">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'animate-spin h-4 w-4 mr-1']); ?>
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
                        </span>
                        <?php echo e(t('apply')); ?>

                    </button>
                </div>
                <p x-show="errorMessage" x-text="errorMessage"
                   class="mt-2 text-sm text-danger-600 dark:text-danger-500"></p>
            </div>

            <div x-show="applied">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e(t('applied_coupon')); ?>:</span>
                        <span x-text="appliedCode" class="ml-2 text-sm font-semibold text-info-600 dark:text-info-500"></span>
                        <div class="mt-1">
                            <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo e(t('discount')); ?>: </span>
                            <span x-text="formattedDiscount" class="text-sm font-semibold text-success-600 dark:text-success-500"></span>
                        </div>
                    </div>
                    <button type="button" @click="removeCoupon()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span x-show="loading">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'animate-spin h-4 w-4 mr-1']); ?>
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
                        </span>
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 mr-1']); ?>
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
                        <?php echo e(t('remove')); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/partials/coupon-form.blade.php ENDPATH**/ ?>