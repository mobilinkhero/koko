<div class="container mx-auto py-10 max-w-3xl" wire:poll.5s="reload">
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
         <?php $__env->slot('header', null, []); ?> 
            <h3 class="text-2xl font-medium text-gray-900 dark:text-slate-200">
                <?php echo e(t('subscription_pending_approval')); ?>

            </h3>
            <p class="mt-2 text-gray-600 dark:text-slate-500">
                <?php echo e(t('payment_has_been_recorded')); ?>

            </p>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('content', null, []); ?> 
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
                    <?php echo e(t('waiting_for_admin_approval')); ?></h4>
                <p class="text-gray-600 dark:text-gray-500">
                    <?php echo e(t('pending_approval_payment')); ?>

                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
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
                     <?php $__env->slot('content', null, []); ?> 
                        <span class="text-sm text-gray-500 block mb-1"><?php echo e(t('selected_plan')); ?></span>
                        <span class="font-medium dark:text-slate-200"><?php echo e($subscription->plan->name ?? 'Unknown'); ?></span>
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
                     <?php $__env->slot('content', null, []); ?> 
                        <span class="text-sm text-gray-500 block mb-1"><?php echo e(t('price')); ?></span>
                        <?php
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
                        ?>
                        <span class="font-medium dark:text-slate-200">
                            <?php echo e($priceDisplay); ?>

                        </span>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="py-1 rounded inline-block">
                                <?php echo e($baseAmount); ?><br>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $taxBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxLine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="ml-2">+ <?php echo e($taxLine); ?></span><br>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </span>
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
                     <?php $__env->slot('content', null, []); ?> 
                        <span class="text-sm text-gray-500 block mb-1"><?php echo e(t('payment_method')); ?></span>
                        <span class="font-medium dark:text-slate-200"><?php echo e(ucfirst($subscription->payment_method ??
                            t('offline'))); ?></span>
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
                     <?php $__env->slot('content', null, []); ?> 
                        <span class="text-sm text-gray-500 block mb-1"><?php echo e(t('requested_on')); ?></span>
                        <span class="font-medium dark:text-slate-200"><?php echo e($subscription->created_at->format('M d, Y')); ?></span>
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
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('footer', null, []); ?> 
            <p class="text-sm text-gray-600 dark:text-gray-500">
                <?php echo e(t('subscription_not_approve')); ?>

                <a href="mailto:<?php echo e($supportEmail); ?>" class="text-primary-600 hover:text-primary-500"><?php echo e($supportEmail); ?></a>.
            </p>
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
</div><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/tenant/tenant-subscription/subscription-pending.blade.php ENDPATH**/ ?>