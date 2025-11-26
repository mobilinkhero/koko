<div class="relative" x-init="getObserver()">
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('whatsapp_template')); ?>

     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('whatsapp_template')],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('whatsapp_template')],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dynamic-alert','data' => ['type' => 'warning','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','class' => 'mb-4']); ?>
        <div class="space-y-1">
            <p class="text-warning-700 dark:text-warning-300">
                <strong><?php echo e(t('imp_real_time_template_updates')); ?> </strong><br>

                <?php echo e(t('in_your')); ?><strong><?php echo e(t('meta_whatsapp_api_configuration')); ?></strong><?php echo e(t('if_you_have_subscribe_to_event')); ?>

                <code>message_template_status_update</code> <?php echo e(t('and')); ?> <code>template_category_update</code><?php echo e(t('template_status_or_category_update_alert')); ?><strong><?php echo e(t('real_time')); ?></strong>.<br>

                <span class="block mt-1">
                    <?php echo e(t('if_you_are')); ?><strong><?php echo e(t('not_subscribed')); ?></strong><?php echo e(t('template_changes_will_not_update_automatically')); ?><strong><?php echo e(t('manually_reload')); ?></strong><?php echo e(t('get_latest_status_and_category_information')); ?>

                </span>

                <span class="block mt-1">
                    <?php echo e(t('after_any')); ?><strong><?php echo e(t('template_status_update')); ?></strong><?php echo e(t('please_verify')); ?>

                    <strong><?php echo e(t('configured_template_bots')); ?></strong> <?php echo e(t('and')); ?> <strong><?php echo e(t('scheduled_campaigns')); ?></strong>
                    <?php echo e(t('validation_ensure')); ?>

                </span>
            </p>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822)): ?>
<?php $attributes = $__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822; ?>
<?php unset($__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822)): ?>
<?php $component = $__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822; ?>
<?php unset($__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822); ?>
<?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-start items-start lg:items-center gap-2 mb-4">
        <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.template.load_template')): ?>
        <!--[if BLOCK]><![endif]--><?php if(get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected') != 0): ?>
        <?php if (isset($component)) { $__componentOriginal533f51d0b2818acbd35337da747efa74 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal533f51d0b2818acbd35337da747efa74 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.loading-button','data' => ['type' => 'button','target' => 'loadTemplate','wire:click' => 'loadTemplate','class' => 'whitespace-nowrap px-4 py-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.loading-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','target' => 'loadTemplate','wire:click' => 'loadTemplate','class' => 'whitespace-nowrap px-4 py-2']); ?>
            <?php echo e(t('load_template')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal533f51d0b2818acbd35337da747efa74)): ?>
<?php $attributes = $__attributesOriginal533f51d0b2818acbd35337da747efa74; ?>
<?php unset($__attributesOriginal533f51d0b2818acbd35337da747efa74); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal533f51d0b2818acbd35337da747efa74)): ?>
<?php $component = $__componentOriginal533f51d0b2818acbd35337da747efa74; ?>
<?php unset($__componentOriginal533f51d0b2818acbd35337da747efa74); ?>
<?php endif; ?>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <a href="https://business.facebook.com/wa/manage/message-templates/" target="_blank" rel="noopener noreferrer">
            <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['class' => 'whitespace-nowrap px-4 py-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'whitespace-nowrap px-4 py-2']); ?>
                <?php echo e(t('template_management')); ?>

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
        </a>
        <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.template.create')): ?>
        <!--[if BLOCK]><![endif]--><?php if(get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected') != 0): ?>
        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['class' => 'whitespace-nowrap px-4 py-2','href' => ''.e(tenant_route('tenant.dynamic-template.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'whitespace-nowrap px-4 py-2','href' => ''.e(tenant_route('tenant.dynamic-template.index')).'']); ?>
            <button type="button" class="relative hover:text-primary-500 text-gray-500 dark:text-slate-400 mr-2">
                <!-- Status Indicator -->
                <span class="flex items-center justify-center">
                    <span class="absolute h-3 w-3 rounded-full opacity-75 bg-gray-200 animate-ping"></span>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-plus'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-white']); ?>
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
            </button>
            <?php echo e(t('create_template')); ?>

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
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rounded-lg']); ?>
         <?php $__env->slot('content', null, []); ?> 
            <div class="lg:mt-0" wire:poll.10s="refreshTable">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tenant.tables.whatspp-template-table', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-4088508492-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
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

    <!-- Delete Confirmation Modal -->
    <?php if (isset($component)) { $__componentOriginal79e52b819ddc9a73b4560c41923d18f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79e52b819ddc9a73b4560c41923d18f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.confirm-box','data' => ['maxWidth' => 'lg','id' => 'delete-template-modal','title' => ''.e(t('delete_template')).'','wire:model' => 'showDeleteConfirmation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal.confirm-box'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['maxWidth' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('lg'),'id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('delete-template-modal'),'title' => ''.e(t('delete_template')).'','wire:model' => 'showDeleteConfirmation']); ?>
         <?php $__env->slot('description', null, []); ?> 
            <div class="space-y-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <?php echo e(t('template_delete_confirmation')); ?>

                    <!--[if BLOCK]><![endif]--><?php if($templateToDelete): ?>
                    <strong class="text-gray-900 dark:text-white"><?php echo e($templateToDelete['name']); ?></strong>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </p>
                <div class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-exclamation-triangle'); ?>
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
                            <p class="text-sm text-warning-700 dark:text-warning-200">
                                <?php echo e(t('template_delete_warning')); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>
         <?php $__env->endSlot(); ?>

        <?php if (isset($component)) { $__componentOriginal254f851538d10c3f8455184bad85911f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal254f851538d10c3f8455184bad85911f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.delete-button','data' => ['wire:click' => 'confirmDelete','wire:loading.attr' => 'disabled','class' => 'sm:ml-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'confirmDelete','wire:loading.attr' => 'disabled','class' => 'sm:ml-3']); ?>
            <span wire:loading.remove wire:target="confirmDelete">
                <?php echo e(t('delete')); ?>

            </span>
            <span wire:loading wire:target="confirmDelete" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <?php echo e(t('deleting')); ?>...
            </span>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal254f851538d10c3f8455184bad85911f)): ?>
<?php $attributes = $__attributesOriginal254f851538d10c3f8455184bad85911f; ?>
<?php unset($__attributesOriginal254f851538d10c3f8455184bad85911f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal254f851538d10c3f8455184bad85911f)): ?>
<?php $component = $__componentOriginal254f851538d10c3f8455184bad85911f; ?>
<?php unset($__componentOriginal254f851538d10c3f8455184bad85911f); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalae37219fcdee25763f87d04348a96c20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae37219fcdee25763f87d04348a96c20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.cancel-button','data' => ['wire:click' => 'cancelDelete','wire:loading.attr' => 'disabled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.cancel-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'cancelDelete','wire:loading.attr' => 'disabled']); ?>
            <?php echo e(t('cancel')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalae37219fcdee25763f87d04348a96c20)): ?>
<?php $attributes = $__attributesOriginalae37219fcdee25763f87d04348a96c20; ?>
<?php unset($__attributesOriginalae37219fcdee25763f87d04348a96c20); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalae37219fcdee25763f87d04348a96c20)): ?>
<?php $component = $__componentOriginalae37219fcdee25763f87d04348a96c20; ?>
<?php unset($__componentOriginalae37219fcdee25763f87d04348a96c20); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79e52b819ddc9a73b4560c41923d18f7)): ?>
<?php $attributes = $__attributesOriginal79e52b819ddc9a73b4560c41923d18f7; ?>
<?php unset($__attributesOriginal79e52b819ddc9a73b4560c41923d18f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79e52b819ddc9a73b4560c41923d18f7)): ?>
<?php $component = $__componentOriginal79e52b819ddc9a73b4560c41923d18f7; ?>
<?php unset($__componentOriginal79e52b819ddc9a73b4560c41923d18f7); ?>
<?php endif; ?>
</div><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/tenant/template/template-list.blade.php ENDPATH**/ ?>