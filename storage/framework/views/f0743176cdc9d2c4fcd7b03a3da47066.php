<div class="mx-auto" x-data="{
    expandedVersions: { '1.1.3': true },
    toggleVersion(version) {
        this.expandedVersions[version] = !this.expandedVersions[version];
    }
}">
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('software_update_management')); ?>

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
<?php $component->withAttributes([]); ?><?php echo e(t('system_settings')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php if (isset($component)) { $__componentOriginal6cdec1e07ce4bcc0273b57671e412ae1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6cdec1e07ce4bcc0273b57671e412ae1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-system-settings-navigation','data' => ['wire:ignore' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-system-settings-navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:ignore' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6cdec1e07ce4bcc0273b57671e412ae1)): ?>
<?php $attributes = $__attributesOriginal6cdec1e07ce4bcc0273b57671e412ae1; ?>
<?php unset($__attributesOriginal6cdec1e07ce4bcc0273b57671e412ae1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6cdec1e07ce4bcc0273b57671e412ae1)): ?>
<?php $component = $__componentOriginal6cdec1e07ce4bcc0273b57671e412ae1; ?>
<?php unset($__componentOriginal6cdec1e07ce4bcc0273b57671e412ae1); ?>
<?php endif; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
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
                        <?php echo e(t('software_update_management')); ?>

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
                        <?php echo e(t('software_update_management_description')); ?>

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
                    <div class="mx-auto">
                        <!--[if BLOCK]><![endif]--><?php if(!empty($support)): ?>
                        <div
                            class="mb-6 border-l-4 rounded-r-md z-100  dark:bg-gray-700  dark:text-white <?php echo e($support['success'] == false ? 'bg-danger-100 border-danger-500 text-danger-700 dark:border-danger-600' : 'bg-success-100 border-success-500 text-success-700 dark:bg-gray-700 dark:border-success-600'); ?>">
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 ">
                                    <div
                                        class="flex flex-col items-start <?php echo e($support['success'] == false ? 'bg-danger-100 border-danger-500 text-danger-700 dark:border-danger-600' : 'bg-success-100 border-success-500 text-success-700 dark:bg-gray-700 dark:border-success-600'); ?> ">
                                        <h1
                                            class="text font-bold <?php echo e($support['success'] == false ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400'); ?>">
                                            <?php echo e(t('support')); ?>

                                        </h1>
                                        <div
                                            class="mt-2 text-sm font-medium <?php echo e($support['success'] == false ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400'); ?> ">
                                            <?php echo e($support['message']); ?>

                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php if($support['success'] == false): ?>
                                        <div
                                            class="mt-1 flex items-start flex-col text-danger-700 dark:text-danger-300">
                                            <a href="<?php echo e(config('installer.license_verification.renew_support_url')); ?>"
                                                class="text-sm mt-1 text-danger-700 dark:text-danger-400 underline"
                                                target="_blank">
                                                <?php echo e(t('renew_support')); ?> </a>
                                        </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex flex-col items-start md:items-end mt-4 md:mt-0">
                                        <span class="text-sm text-gray-600 dark:text-slate-400">
                                            <?php echo e(t('do_you_want_custom_service')); ?> </span>
                                        <a href="<?php echo e($support['support_url']); ?>" target="_blank"
                                            class="mt-2 w-auto px-4 py-2 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 rounded-lg border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors flex items-center space-x-2">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-arrow-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
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
                                            <span class="text-sm"> <?php echo e(t('create_support_ticket')); ?> </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <div
                            class="mb-6 border-l-4 rounded-r-md z-100 bg-warning-100 border-warning-500 text-warning-800 dark:bg-gray-700 dark:border-warning-300 dark:text-warning-300">
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 ">
                                    <div
                                        class="flex flex-col items-start bg-warning-100 border-warning-500 text-warning-800 dark:bg-gray-700 dark:border-warning-300 dark:text-warning-300">
                                        <h1 class="text font-bold text-warning-800 dark:text-warning-300">
                                            <?php echo e('Clear Cache'); ?>

                                        </h1>
                                        <div class="mt-2 text-sm font-medium text-warning-800 dark:text-warning-300">
                                            <?php echo e('We recommend clearing your cache after downloading the update.'); ?>

                                        </div>
                                    </div>
                                    <div class="flex flex-col items-start md:items-end mt-4 md:mt-0">
                                        <a wire:click="clearCache()"
                                            class="mt-2 w-auto px-4 py-2 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 rounded-lg border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors flex items-center space-x-2 cursor-pointer">
                                            <span> <?php echo e('Clear Cache'); ?> </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            <!-- Update Section -->
                            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['xData' => '{ purchase_key: \'\', username: \'\', isValid: false }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-data' => '{ purchase_key: \'\', username: \'\', isValid: false }']); ?>
                                 <?php $__env->slot('content', null, []); ?> 
                                    <div class="p-2">
                                        <h2 class="font-semibold text-gray-900 dark:text-white mb-6">
                                            <?php echo e(t('version_information')); ?> </h2>

                                        <div class="flex flex-col sm:flex-row mb-8 gap-4">
                                            <div
                                                class="flex-1 p-4 border-l-4 rounded-r-md z-100 dark:bg-gray-700 dark:text-white <?php echo e($currentVersion != $latestVersion ? 'bg-danger-100 border-danger-500 text-danger-700 dark:border-danger-600' : 'bg-success-100 border-success-500 text-success-700 dark:bg-gray-700 dark:border-success-600'); ?> ">
                                                <div
                                                    class="text-sm mb-1 <?php echo e($currentVersion != $latestVersion ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400'); ?> ">
                                                    <?php echo e(t('your_version')); ?>

                                                </div>
                                                <div
                                                    class="text-xl font-bold <?php echo e($currentVersion != $latestVersion ? 'text-danger-700 dark:text-danger-300' : 'text-success-700 dark:text-success-300'); ?> ">
                                                    <?php echo e($currentVersion); ?>

                                                </div>
                                            </div>
                                            <div class="hidden sm:flex items-center justify-center text-gray-400">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-arrow-long-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-8 h-8']); ?>
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
                                            <div class="sm:hidden flex justify-center text-gray-400">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-arrow-long-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-8 h-8']); ?>
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
                                            <div
                                                class="flex-1 p-4 border-l-4 rounded-r-md z-100 bg-success-100 border-success-500 text-success-700 dark:bg-gray-700 dark:border-success-600 dark:text-white">
                                                <div class="text-sm text-success-600 dark:text-success-400 mb-1">
                                                    <?php echo e(t('latest_version')); ?> </div>
                                                <div class="text-xl font-bold text-success-700 dark:text-success-300">
                                                    <?php echo e($latestVersion); ?>

                                                </div>
                                            </div>
                                        </div>
                                        <form wire:submit.prevent="save">
                                            <div class="space-y-4" x-data>
                                                <!-- Username -->
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1"
                                                        for="username">
                                                        <span class="text-danger-500">*</span> <?php echo e(t('username')); ?>

                                                    </label>
                                                    <input type="text" id="username" wire:model.defer="username"
                                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-info-500 dark:focus:ring-info-600 focus:border-transparent transition-colors"
                                                        placeholder="Enter your username" autocomplete="off">
                                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <p class="text-danger-500 text-sm mt-1"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <!-- Purchase Key -->
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1"
                                                        for="purchase_key">
                                                        <span class="text-danger-500">*</span> <?php echo e(t('purchase_key')); ?>

                                                    </label>
                                                    <input type="text" id="purchase_key" wire:model.defer="purchase_key"
                                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-info-500 dark:focus:ring-info-600 focus:border-transparent transition-colors"
                                                        placeholder="Enter your purchase key" autocomplete="off">
                                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['purchase_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <p class="text-danger-500 text-sm mt-1"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                            </div>

                                            <!-- Update Button -->
                                            <!--[if BLOCK]><![endif]--><?php if($currentVersion != $latestVersion): ?>
                                            <div class="mt-8">
                                                <button type="submit"
                                                    class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white rounded-lg transition-colors flex items-center justify-center space-x-2"
                                                    wire:loading.attr="disabled">
                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-arrow-down-tray'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
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
                                                    <span> <?php echo e(t('download_update')); ?> </span>
                                                </button>
                                            </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </form>

                                        <!-- Warning Message -->
                                        <div
                                            class="mt-6 p-4 bg-warning-50 dark:bg-warning-900/10 rounded-lg border border-warning-100 dark:border-warning-900/20">
                                            <div class="flex items-start space-x-3">
                                                <p class="text-warning-700 dark:text-warning-300 text-sm">
                                                    <?php echo e(t('before_update_description')); ?>

                                                </p>
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

                            <!-- Changelog Section -->
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
                                    <div class="p-2">
                                        <h2 class="font-semibold text-gray-900 dark:text-white mb-6">
                                            <?php echo e(t('change_log')); ?>

                                        </h2>

                                        <!-- Changelog Content -->
                                        <div wire:loading.block wire:target="loadReleases"
                                            class="flex justify-center items-center py-8">
                                            <div
                                                class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-500">
                                            </div>
                                        </div>

                                        <div wire:loading.remove wire:target="loadReleases"
                                            class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                                            <!--[if BLOCK]><![endif]--><?php if(isset($versionLog['versions']) && count($versionLog['versions']) > 0): ?>
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $versionLog['versions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div
                                                class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                                <!-- Version Header -->
                                                <div class="flex items-center justify-between p-4 cursor-pointer <?php echo e($version['is_latest'] ? 'bg-success-50 dark:bg-success-900/10' : 'bg-gray-50 dark:bg-gray-700/30'); ?>"
                                                    @click="toggleVersion('<?php echo e($version['version']); ?>')">
                                                    <div class="flex items-center space-x-2">
                                                        <span
                                                            class="<?php echo e($version['is_latest'] ? 'text-success-600 dark:text-success-400' : 'text-gray-600 dark:text-gray-400'); ?>">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                            </svg>
                                                        </span>
                                                        <div>
                                                            <span
                                                                class="font-medium <?php echo e($version['is_latest'] ? 'text-success-700 dark:text-success-300' : 'text-gray-700 dark:text-gray-300'); ?>">
                                                                <?php echo e($version['version']); ?>

                                                                <!--[if BLOCK]><![endif]--><?php if($version['is_latest']): ?>
                                                                <span
                                                                    class="ml-2 text-xs px-2 py-1 bg-success-100 dark:bg-success-800 text-success-800 dark:text-success-200 rounded-full">
                                                                    <?php echo e(t('latest')); ?>

                                                                </span>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            </span>
                                                            <span
                                                                class="ml-2 text-sm text-gray-500 dark:text-gray-400"><?php echo e($version['date']); ?></span>
                                                        </div>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-5 w-5 text-gray-400 transform transition-transform duration-200"
                                                        :class="{
                                                                    'rotate-180': expandedVersions[
                                                                        '<?php echo e($version['version']); ?>']
                                                                }" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>

                                                <!-- Change Items -->
                                                <?php
                                                $hasContentInFifTypes = false;
                                                foreach ($version['changes'] as $changeCheck) {
                                                if (
                                                in_array($changeCheck['type'], [
                                                'feature',
                                                'improvement',
                                                'bug',
                                                ]) &&
                                                !empty($changeCheck['description'])
                                                ) {
                                                $hasContentInFifTypes = true;
                                                break;
                                                }
                                                }
                                                ?>

                                                <div x-show="expandedVersions['<?php echo e($version['version']); ?>']"
                                                    class="p-4 border-t border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">

                                                    <!-- First display feature, improvement, bug if they have content -->
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $version['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <!--[if BLOCK]><![endif]--><?php if(in_array($change['type'], ['feature', 'improvement', 'bug']) &&
                                                    !empty($change['description'])): ?>
                                                    <div class="py-3 flex items-start">
                                                        <!--[if BLOCK]><![endif]--><?php if($change['type'] === 'feature'): ?>
                                                        <span class="flex-shrink-0 mr-3 mt-1">
                                                            <span
                                                                class="flex h-6 w-6 items-center justify-center rounded-full bg-info-100 text-info-500 dark:bg-info-900/30 dark:text-info-300">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                    fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                                </svg>
                                                            </span>
                                                        </span>
                                                        <div class="text-sm">
                                                            <p class="font-medium text-info-600 dark:text-info-400">
                                                                <?php echo e(t('new_feature')); ?></p>
                                                            <p class="text-gray-700 dark:text-gray-300">
                                                                <?php echo $change['description']; ?></p>
                                                        </div>
                                                        <?php elseif($change['type'] === 'improvement'): ?>
                                                        <span class="flex-shrink-0 mr-3 mt-1">
                                                            <span
                                                                class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-purple-500 dark:bg-purple-900/30 dark:text-purple-300">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                    fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                                </svg>
                                                            </span>
                                                        </span>
                                                        <div class="text-sm">
                                                            <p class="font-medium text-purple-600 dark:text-purple-400">
                                                                <?php echo e(t('improvement')); ?></p>
                                                            <p class="text-gray-700 dark:text-gray-300">
                                                                <?php echo $change['description']; ?></p>
                                                        </div>
                                                        <?php elseif($change['type'] === 'bug'): ?>
                                                        <span class="flex-shrink-0 mr-3 mt-1">
                                                            <span
                                                                class="flex h-6 w-6 items-center justify-center rounded-full bg-danger-100 text-danger-500 dark:bg-danger-900/30 dark:text-danger-300">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                    fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </span>
                                                        </span>
                                                        <div class="text-sm">
                                                            <p class="font-medium text-danger-600 dark:text-danger-400">
                                                                <?php echo e(t('bug_fix')); ?></p>
                                                            <p class="text-gray-700 dark:text-gray-300">
                                                                <?php echo $change['description']; ?></p>
                                                        </div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                                                    <!-- Only show changelog if none of the FIF types have content -->
                                                    <!--[if BLOCK]><![endif]--><?php if(!$hasContentInFifTypes): ?>
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $version['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <!--[if BLOCK]><![endif]--><?php if($change['type'] === 'changelog'): ?>
                                                    <div class="py-3 flex items-start">
                                                        <div class="text-sm">
                                                            <p class="text-gray-700 dark:text-gray-300">
                                                                <?php echo $change['description']; ?></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                            </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php else: ?>
                                            <div class="p-4 bg-gray-50 dark:bg-slate-700/30 rounded-lg text-center">
                                                <p class="text-gray-500 dark:text-gray-400 text-sm">
                                                    <?php echo e(t('no_release_information_available')); ?></p>
                                            </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
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
            <!-- Loading Modal -->
            <div wire:loading wire:target="save">
                <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-11/12 sm:w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg text-center">
                        <!-- Loading Spinner -->
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-gray-300 dark:border-gray-600 border-t-primary-500 dark:border-t-primary-400 rounded-full animate-spin mx-auto">
                        </div>

                        <!-- Message -->
                        <p class="mt-4 text-base sm:text-lg font-medium text-gray-700 dark:text-gray-200">
                            <?php echo e(t('updating_system')); ?>

                        </p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <?php echo e(t('please_do_not_close_this_window')); ?>

                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/qrpayuco/whatsapp.qrpayu.com/resources/views/livewire/admin/settings/system/system-update-settings.blade.php ENDPATH**/ ?>