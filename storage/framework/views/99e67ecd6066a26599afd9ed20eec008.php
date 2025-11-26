<div>
    <!--[if BLOCK]><![endif]--><?php if($faqs->count() > 0): ?>
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <h2 class="mb-4 text-3xl text-center font-extrabold tracking-tight text-gray-900 dark:text-white font-display">
            <?php echo e($themeSettings['theme.faq_section_title'] ?: 'Default Faq Title'); ?>

        </h2>
        <p class="mb-5 font-light text-center text-gray-500 sm:text-lg dark:text-gray-400">
            <?php echo e($themeSettings['theme.faq_section_subtitle'] ?: 'Default Faq SubTitle'); ?>

        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-4">
                <?php if (isset($component)) { $__componentOriginalf37c7fa867bbb37ca7b59380c8fa1d1e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf37c7fa867bbb37ca7b59380c8fa1d1e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.accordion','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('accordion'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                     <?php $__env->slot('title', null, []); ?> 
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            <?php echo e($faq->question); ?>

                        </h2>
                     <?php $__env->endSlot(); ?>

                     <?php $__env->slot('content', null, []); ?> 
                        <div
                            class="border-b py-6 border-primary-600 dark:border-primary-400 text-gray-500 dark:text-gray-400">
                            <?php echo nl2br(e($faq->answer)); ?>

                        </div>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf37c7fa867bbb37ca7b59380c8fa1d1e)): ?>
<?php $attributes = $__attributesOriginalf37c7fa867bbb37ca7b59380c8fa1d1e; ?>
<?php unset($__attributesOriginalf37c7fa867bbb37ca7b59380c8fa1d1e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf37c7fa867bbb37ca7b59380c8fa1d1e)): ?>
<?php $component = $__componentOriginalf37c7fa867bbb37ca7b59380c8fa1d1e; ?>
<?php unset($__componentOriginalf37c7fa867bbb37ca7b59380c8fa1d1e); ?>
<?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]--><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/livewire/frontend/faq-list.blade.php ENDPATH**/ ?>