<div class="relative">
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('message_bots')); ?>

     <?php $__env->endSlot(); ?>

      <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('message_bots')],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('message_bots')],
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

    <div class="flex flex-col sm:flex-row gap-2 justify-between mb-4">
        <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.message_bot.create')): ?>
        <a href="<?php echo e(tenant_route('tenant.messagebot.create')); ?>">
            <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-plus'); ?>
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
<?php endif; ?><?php echo e(t('message_bot')); ?>

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
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Feature Limit Badge -->
        <div>
            <!--[if BLOCK]><![endif]--><?php if(isset($this->isUnlimited) && $this->isUnlimited): ?>
                <?php if (isset($component)) { $__componentOriginal2474b3053492ad88b606267c9b7ef95d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2474b3053492ad88b606267c9b7ef95d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.unlimited-badge','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('unlimited-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php echo e(t('unlimited')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2474b3053492ad88b606267c9b7ef95d)): ?>
<?php $attributes = $__attributesOriginal2474b3053492ad88b606267c9b7ef95d; ?>
<?php unset($__attributesOriginal2474b3053492ad88b606267c9b7ef95d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2474b3053492ad88b606267c9b7ef95d)): ?>
<?php $component = $__componentOriginal2474b3053492ad88b606267c9b7ef95d; ?>
<?php unset($__componentOriginal2474b3053492ad88b606267c9b7ef95d); ?>
<?php endif; ?>
            <?php elseif(isset($this->remainingLimit) && isset($this->totalLimit)): ?>
                <?php if (isset($component)) { $__componentOriginal6261997bb4bcd12eea01202754e1db02 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6261997bb4bcd12eea01202754e1db02 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.remaining-limit-badge','data' => ['label' => ''.e(t('remaining')).'','value' => $this->remainingLimit,'count' => $this->totalLimit]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('remaining-limit-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(t('remaining')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->remainingLimit),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->totalLimit)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6261997bb4bcd12eea01202754e1db02)): ?>
<?php $attributes = $__attributesOriginal6261997bb4bcd12eea01202754e1db02; ?>
<?php unset($__attributesOriginal6261997bb4bcd12eea01202754e1db02); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6261997bb4bcd12eea01202754e1db02)): ?>
<?php $component = $__componentOriginal6261997bb4bcd12eea01202754e1db02; ?>
<?php unset($__componentOriginal6261997bb4bcd12eea01202754e1db02); ?>
<?php endif; ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>


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
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tenant.tables.message-bot-table', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1380715426-0', $__slots ?? [], get_defined_vars());

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

    
    <?php if (isset($component)) { $__componentOriginal79e52b819ddc9a73b4560c41923d18f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79e52b819ddc9a73b4560c41923d18f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.confirm-box','data' => ['maxWidth' => 'lg','id' => 'delete-messagebot-modal','title' => ''.e(t('delete_message_bot')).'','wire:model.defer' => 'confirmingDeletion','description' => ''.e(t('delete_message')).' ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal.confirm-box'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['maxWidth' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('lg'),'id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('delete-messagebot-modal'),'title' => ''.e(t('delete_message_bot')).'','wire:model.defer' => 'confirmingDeletion','description' => ''.e(t('delete_message')).' ']); ?>
        <div
            class="border-neutral-200  flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <?php if (isset($component)) { $__componentOriginalae37219fcdee25763f87d04348a96c20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae37219fcdee25763f87d04348a96c20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.cancel-button','data' => ['wire:click' => '$set(\'confirmingDeletion\', false)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.cancel-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'confirmingDeletion\', false)']); ?>
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
            <?php if (isset($component)) { $__componentOriginal254f851538d10c3f8455184bad85911f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal254f851538d10c3f8455184bad85911f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.delete-button','data' => ['wire:click' => 'delete','class' => 'mt-3 sm:mt-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'delete','class' => 'mt-3 sm:mt-0']); ?>
                <?php echo e(t('delete')); ?>

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
        </div>
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
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/tenant/bot/message-bot-list.blade.php ENDPATH**/ ?>