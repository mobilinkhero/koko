<div>
    <div class="py-4 sm:py-10">
        <div class="max-w-6xl mx-auto">
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'rounded-lg mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rounded-lg mb-6']); ?>
                 <?php $__env->slot('header', null, []); ?> 
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-credit-card'); ?>
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
                                <?php echo e(t('checkout')); ?>

                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                <?php echo e(t("payment_confirm_message")); ?>

                            </p>
                        </div>
                    </div>

                 <?php $__env->endSlot(); ?>
                 <?php $__env->slot('content', null, []); ?> 
                    <!--[if BLOCK]><![endif]--><?php if(session('error')): ?>
                    <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-6" role="alert">
                        <p><?php echo e(session('error')); ?></p>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!-- Plan Details Card -->
                    <div class="mb-8 bg-primary-500 dark:bg-slate-700 rounded-lg shadow-md p-6 text-white">
                        <h3 class="text-xl font-bold mb-4"><?php echo e(t('Subscription Details')); ?></h3>

                        <?php
                        $taxes = get_default_taxes();
                        $totalTaxAmount = 0;
                        foreach ($taxes as $tax) {
                        $totalTaxAmount += $plan->price * ($tax->rate / 100);
                        }
                        $finalAmount = $plan->price + $totalTaxAmount;


                        $baseAmount = get_base_currency()->format($plan->price);
                        $taxBreakdown = [];
                        foreach($taxes as $tax) {
                        $taxBreakdown[] = $tax->rate . '% ' . $tax->name;
                        }
                        ?>

                        <!-- First row with 3 columns for plan details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-primary-100"><?php echo e(t('plan_name')); ?></p>
                                <p class="font-semibold text-lg"><?php echo e($plan->name); ?></p>
                            </div>
                            <div>
                                <p class="text-primary-100"><?php echo e(t('base_amount')); ?></p>
                                <p class="font-semibold text-lg">
                                    <?php echo e(get_base_currency()->format($plan->price)); ?>

                                </p>
                            </div>
                            <div>
                                <p class="text-primary-100"><?php echo e(t('billing_cycle')); ?></p>
                                <p class="font-semibold text-lg"><?php echo e(ucfirst($plan->billing_period)); ?></p>
                            </div>
                        </div>

                        <!-- Second row with 3 columns for interval, price breakdown and tax details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-primary-100"><?php echo e(t('interval')); ?></p>
                                <p class="font-semibold text-lg">Per <?php echo e($interval); ?></p>
                            </div>
                            <div>
                                <p class="text-primary-100"><?php echo e(t('price_breakdown')); ?></p>
                                <div class="font-semibold text-lg">
                                    <?php echo e($baseAmount); ?><br>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $taxBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxLine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="ml-2 block text-sm">+ <?php echo e($taxLine); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php if($taxes->count() > 0): ?>
                            <div>
                                <p class="text-primary-100"><?php echo e(t('tax_details')); ?></p>
                                <div class="flex flex-wrap gap-2">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="text-sm bg-primary-600 dark:bg-slate-600 px-2 py-1 rounded-md">
                                        <?php echo e($tax->name); ?> (<?php echo e($tax->rate); ?>%): <?php echo e(get_base_currency()->format($plan->price * ($tax->rate / 100))); ?>

                                    </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Total amount in a separate row -->
                        <!--[if BLOCK]><![endif]--><?php if($taxes->count() > 0): ?>
                        <div class="border-t border-primary-400 dark:border-slate-600 pt-4 mt-4">
                            <div class="flex justify-end">
                                <div class="text-right">
                                    <p class="text-primary-100"><?php echo e(t('total_amount')); ?></p>
                                    <p class="font-semibold text-2xl">
                                        <?php echo e(get_base_currency()->format($finalAmount)); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <form action="<?php echo e(tenant_route('tenant.checkout.process')); ?>" method="POST" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="plan_id" value="<?php echo e($plan->id); ?>">
                        <!-- Payment Method Selection -->
                        <div>
                            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200"><?php echo e(t('payment_method')); ?></h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php
                                $billingManager = app('billing.manager');
                                $gateways = $billingManager->getActiveGateways();
                                ?>

                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    class="relative border border-slate-200 dark:border-slate-700 dark:bg-slate-700 rounded-lg p-4 hover:border-info-500 cursor-pointer">
                                    <input type="radio" name="payment_method" id="payment_<?php echo e($name); ?>"
                                        value="<?php echo e($name); ?>" class="absolute h-4 w-4 top-4 right-4" <?php echo e($loop->first ||
                                    old('payment_method') == $name ? 'checked' : ''); ?>>
                                    <label for="payment_<?php echo e($name); ?>" class="cursor-pointer block">
                                        <div class="font-medium text-gray-800 dark:text-gray-200">
                                            <?php echo e($gateway->getName()); ?></div>
                                        <p class="text-gray-500  text-sm mt-1"><?php echo e($gateway->getDescription()); ?></p>
                                    </label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($plan->isFree()): ?>
                        <div class="bg-success-50 dark:bg-slate-700 border-l-4 border-success-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-success-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-success-700">
                                        <?php echo e(t('free_plan_no_payment')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-info-50 dark:bg-slate-700 border-l-4 border-info-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-info-400 " xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-info-700 dark:text-info-500">
                                        <?php echo e(t('redirect_to_payment_page')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!-- Terms and Conditions -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" name="terms" type="checkbox"
                                    class="focus:ring-info-500 h-4 w-4 text-info-600 border-gray-300 rounded" required>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="font-medium text-gray-700 dark:text-slate-200"><?php echo e(t('i_agree_to')); ?>

                                    <?php echo e(t('the')); ?> <a href="<?php echo e(route('terms.conditions')); ?>"
                                        class="text-info-600 hover:text-info-500">
                                        <?php echo e(t('terms_conditions')); ?></a> <?php echo e(t('and')); ?> <a
                                        href="<?php echo e(route('privacy.policy')); ?>"
                                        class="text-info-600 hover:text-info-500"><?php echo e(t('privacy_policy')); ?></a></label>
                            </div>
                        </div>


                        <div class="flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-4">
                            <!-- Back Button -->
                            <a href="<?php echo e(tenant_route('tenant.subscription')); ?>"
                                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-slate-700 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <?php echo e(t('back_to_plans')); ?>

                            </a>

                            <!-- Submit Button -->
                            <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['type' => 'submit','class' => 'w-full sm:w-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'w-full sm:w-auto']); ?>
                                <!--[if BLOCK]><![endif]--><?php if($plan->isFree()): ?>
                                <?php echo e(t('subscribe_now')); ?>

                                <?php else: ?>
                                <?php echo e(t('proceed_to_payment')); ?>

                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                        </div>

                    </form>
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
    </div>
</div><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/tenant/tenant-subscription/billing-details.blade.php ENDPATH**/ ?>