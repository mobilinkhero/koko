<div class="mx-auto ">
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('clear_chat_history')); ?>

     <?php $__env->endSlot(); ?>
    <!-- Page Heading -->
    <div class="pb-6">
        <?php if (isset($component)) { $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings-heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(t('application_settings')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $attributes = $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $component = $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <?php if (isset($component)) { $__componentOriginalc8e4fb70d65a02746ea1846f97b983ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8e4fb70d65a02746ea1846f97b983ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tenant-whatsmark-settings-navigation','data' => ['wire:ignore' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tenant-whatsmark-settings-navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:ignore' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8e4fb70d65a02746ea1846f97b983ff)): ?>
<?php $attributes = $__attributesOriginalc8e4fb70d65a02746ea1846f97b983ff; ?>
<?php unset($__attributesOriginalc8e4fb70d65a02746ea1846f97b983ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8e4fb70d65a02746ea1846f97b983ff)): ?>
<?php $component = $__componentOriginalc8e4fb70d65a02746ea1846f97b983ff; ?>
<?php unset($__componentOriginalc8e4fb70d65a02746ea1846f97b983ff); ?>
<?php endif; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit.prevent="save" x-data="{ 'enable_auto_clear_chat': <?php if ((object) ('enable_auto_clear_chat') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_auto_clear_chat'->value()); ?>')<?php echo e('enable_auto_clear_chat'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_auto_clear_chat'); ?>')<?php endif; ?> }"
                class="space-y-6">
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
                     <?php $__env->slot('header', null, []); ?> 
                        <?php if (isset($component)) { $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings-heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php echo e(t('auto_clear_chat_history')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $attributes = $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $component = $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginald4840e1146262bfa3abec1048daf8661 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4840e1146262bfa3abec1048daf8661 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings-description','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings-description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php echo e(t('setup_auto_clear_chat')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4840e1146262bfa3abec1048daf8661)): ?>
<?php $attributes = $__attributesOriginald4840e1146262bfa3abec1048daf8661; ?>
<?php unset($__attributesOriginald4840e1146262bfa3abec1048daf8661); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4840e1146262bfa3abec1048daf8661)): ?>
<?php $component = $__componentOriginald4840e1146262bfa3abec1048daf8661; ?>
<?php unset($__componentOriginald4840e1146262bfa3abec1048daf8661); ?>
<?php endif; ?>
                     <?php $__env->endSlot(); ?>
                     <?php $__env->slot('content', null, []); ?> 
                        <div class="grid sm:grid-cols-2">
                            <div x-data="{ enable_auto_clear_chat: <?php if ((object) ('enable_auto_clear_chat') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_auto_clear_chat'->value()); ?>')<?php echo e('enable_auto_clear_chat'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_auto_clear_chat'); ?>')<?php endif; ?>.defer }">
                                <?php if (isset($component)) { $__componentOriginald8ba2b4c22a13c55321e34443c386276 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8ba2b4c22a13c55321e34443c386276 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.label','data' => ['for' => 'message','value' => t('activate_auto_clear_chat')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'message','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t('activate_auto_clear_chat'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $attributes = $__attributesOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__attributesOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $component = $__componentOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__componentOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>

                                <div class="flex justify-start items-center">
                                    <?php if (isset($component)) { $__componentOriginal592735d30e1926fbb04ff9e089d1fccf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal592735d30e1926fbb04ff9e089d1fccf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toggle','data' => ['id' => 'auto-clear-chat-toggle','name' => 'enable_auto_clear_chat','value' => $enable_auto_clear_chat,'wire:model' => 'enable_auto_clear_chat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'auto-clear-chat-toggle','name' => 'enable_auto_clear_chat','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enable_auto_clear_chat),'wire:model' => 'enable_auto_clear_chat']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal592735d30e1926fbb04ff9e089d1fccf)): ?>
<?php $attributes = $__attributesOriginal592735d30e1926fbb04ff9e089d1fccf; ?>
<?php unset($__attributesOriginal592735d30e1926fbb04ff9e089d1fccf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal592735d30e1926fbb04ff9e089d1fccf)): ?>
<?php $component = $__componentOriginal592735d30e1926fbb04ff9e089d1fccf; ?>
<?php unset($__componentOriginal592735d30e1926fbb04ff9e089d1fccf); ?>
<?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4 sm:mt-0"
                                x-data="{ 'enable_auto_clear_chat': <?php if ((object) ('enable_auto_clear_chat') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_auto_clear_chat'->value()); ?>')<?php echo e('enable_auto_clear_chat'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_auto_clear_chat'); ?>')<?php endif; ?> }">
                                <div class="flex items-center">
                                    <span x-show="enable_auto_clear_chat" x-cloak class="text-danger-500 mr-1">*</span>
                                    <?php if (isset($component)) { $__componentOriginald8ba2b4c22a13c55321e34443c386276 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8ba2b4c22a13c55321e34443c386276 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.label','data' => ['for' => 'auto_clear_history_time','value' => t('auto_clear_history_time'),'class' => ' mb-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'auto_clear_history_time','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t('auto_clear_history_time')),'class' => ' mb-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $attributes = $__attributesOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__attributesOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $component = $__componentOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__componentOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
                                </div>
                                <div
                                    class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-800">
                                    <input type="number" wire:model.defer="auto_clear_history_time"
                                        id="auto_clear_history_time"
                                        class=" block w-full border-0 text-slate-900 sm:text-sm disabled:opacity-50 dark:bg-slate-800
                                                  dark:placeholder-slate-500 dark:text-slate-200 dark:focus:placeholder-slate-600 px-3 py-2
                                                  border-r border-gray-300 focus:outline-none focus:ring-0 focus:border-transparent" min="1"
                                        max="365">
                                    <span class="px-3  border-gray-300 text-gray-600 dark:text-gray-400 "><?php echo e(t('days')); ?></span>
                                </div>
                                <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['for' => 'auto_clear_history_time','class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'auto_clear_history_time','class' => 'mt-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                            </div>
                        </div>

                        <!-- Chat Cleanup Results Display -->
                        <!--[if BLOCK]><![endif]--><?php if($showCleanupResults && $cleanupResults): ?>
                        <div class="mt-4">
                            <div x-data="{ show: true }" x-show="show"
                                class="rounded-md border <?php echo e($cleanupResults['status'] === 'success' ? 'bg-success-50 border-success-200 dark:bg-success-900/20 dark:border-success-700' : 'bg-danger-50 border-danger-200 dark:bg-danger-900/20 dark:border-danger-700'); ?>">
                                <div class="p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <!--[if BLOCK]><![endif]--><?php if($cleanupResults['status'] === 'success'): ?>
                                            <svg class="h-5 w-5 text-success-400 dark:text-success-500"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <?php else: ?>
                                            <svg class="h-5 w-5 text-danger-400 dark:text-danger-500"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h3
                                                class="text-sm font-medium <?php echo e($cleanupResults['status'] === 'success' ? 'text-success-800 dark:text-success-200' : 'text-danger-800 dark:text-danger-200'); ?>">
                                                <?php echo e(t('chat_cleanup_completed')); ?>

                                            </h3>
                                            <div
                                                class="mt-2 text-sm <?php echo e($cleanupResults['status'] === 'success' ? 'text-success-700 dark:text-success-300' : 'text-danger-700 dark:text-danger-300'); ?>">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    <!--[if BLOCK]><![endif]--><?php if($cleanupResults['status'] === 'success'): ?>
                                                    <li>
                                                        <?php echo e(t('found_messages', ['count' =>
                                                        $cleanupResults['messagesFound'] ?? 0])); ?>

                                                    </li>
                                                    <li>
                                                        <?php echo e(t('deleted_messages', ['count' =>
                                                        $cleanupResults['messagesDeleted'] ?? 0])); ?>

                                                    </li>
                                                    <li>
                                                        <?php echo e(t('deleted_conversations', ['count' =>
                                                        $cleanupResults['conversationsDeleted'] ?? 0])); ?>

                                                    </li>
                                                    <?php else: ?>
                                                    <li>
                                                        <?php echo e($cleanupResults['errorMessage'] ?? t('error_during_cleanup')); ?>

                                                    </li>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="ml-auto pl-3">
                                            <div class="-mx-1.5 -my-1.5">
                                                <button wire:click="dismissResults" type="button"
                                                    class="inline-flex rounded-md p-1.5 <?php echo e($cleanupResults['status'] === 'success' ? 'text-success-500 hover:bg-success-100 dark:hover:bg-success-800' : 'text-danger-500 hover:bg-danger-100 dark:hover:bg-danger-800'); ?> focus:outline-none">
                                                    <span class="sr-only"><?php echo e(t('dismiss')); ?></span>
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <div class="mt-3">
                            <?php if (isset($component)) { $__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dynamic-alert','data' => ['type' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning']); ?>
                                <b><?php echo e(t('note')); ?></b>
                                <?php echo e(t('enabling')); ?>

                                <b><?php echo e(t('auto_clear_chat_history')); ?></b>
                                <?php echo e(t('auto_clear_note')); ?>

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
                        </div>

                        <div class="mt-3">
                            <?php if (isset($component)) { $__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dynamic-alert','data' => ['type' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'primary']); ?>
                                <?php echo e(t('cron_job_required')); ?> <b class="font-semibold"><?php echo e(t('cron_job')); ?></b>.
                                <?php echo e(t('cron_job_setup_info')); ?> <b class="font-semibold"><?php echo e(t('documentation')); ?></b>
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
                        </div>

                     <?php $__env->endSlot(); ?>
                    <!-- Submit Button -->
                    <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.whatsmark_settings.edit')): ?>
                     <?php $__env->slot('footer', null, ['class' => 'bg-slate-50 dark:bg-transparent rounded-b-lg']); ?> 
                        <div class="flex items-center justify-end gap-1">
                            <!--[if BLOCK]><![endif]--><?php if($enable_auto_clear_chat && !is_null($auto_clear_history_time)): ?>
                            <div>
                                <button type="button" wire:click="runCleanup" wire:loading.attr="disabled"
                                    wire:target="runCleanup"
                                    class="inline-flex items-center px-4 py-2 bg-slate-200 dark:bg-slate-700 border border-transparent rounded-md text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-800"
                                    onclick="return confirm('<?php echo e(t('confirm_run_cleanup')); ?>')">

                                    <span class="flex items-center" wire:loading.remove wire:target="runCleanup">
                                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-trash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'hidden md:inline h-4 w-4 mr-1']); ?>
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
                                        <span><?php echo e(t('run_cleanup_now')); ?></span>
                                    </span>

                                    <span class="flex items-center" wire:loading wire:target="runCleanup">
                                        <svg class="animate-spin h-4 w-4 mr-1 inline-flex"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span><?php echo e(t('processing')); ?></span>
                                    </span>

                                </button>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <?php if (isset($component)) { $__componentOriginal533f51d0b2818acbd35337da747efa74 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal533f51d0b2818acbd35337da747efa74 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.loading-button','data' => ['type' => 'submit','target' => 'save']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.loading-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','target' => 'save']); ?>
                                <?php echo e(t('save_changes')); ?>

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
                        </div>
                     <?php $__env->endSlot(); ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
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
            </form>
        </div>
    </div>
</div><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/tenant/settings/whats-mark/auto-clear-chat-history-settings.blade.php ENDPATH**/ ?>