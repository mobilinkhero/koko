<div>
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('performance_optimization')); ?>

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

    <!-- Layout with Sidebar and Main Content -->
    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <?php if (isset($component)) { $__componentOriginal427362ae11d707f153ccf2a9f38ce42a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal427362ae11d707f153ccf2a9f38ce42a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tenant-system-settings-navigation','data' => ['wire:ignore' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tenant-system-settings-navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:ignore' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal427362ae11d707f153ccf2a9f38ce42a)): ?>
<?php $attributes = $__attributesOriginal427362ae11d707f153ccf2a9f38ce42a; ?>
<?php unset($__attributesOriginal427362ae11d707f153ccf2a9f38ce42a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal427362ae11d707f153ccf2a9f38ce42a)): ?>
<?php $component = $__componentOriginal427362ae11d707f153ccf2a9f38ce42a; ?>
<?php unset($__componentOriginal427362ae11d707f153ccf2a9f38ce42a); ?>
<?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white dark:bg-slate-900 rounded-lg border border-neutral-200 dark:border-neutral-500/30 ">
                <div class="px-6 py-4 border-b dark:border-neutral-500/30">
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
                        <?php echo e(t('performance_optimization')); ?>

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
                        <?php echo e(t('cache_description')); ?>

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

                    <!-- Tenant Cache Status Information -->
                    <!--[if BLOCK]><![endif]--><?php if(!empty($cacheStatus)): ?>
                    <div
                        class="mt-4 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-700/50">
                        <h4 class="text-sm font-semibold text-primary-900 dark:text-primary-200 mb-2">
                            <?php echo e(t('tenant_cache_status')); ?>

                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('cache_driver')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['cache_driver'] ?? 'unknown'); ?></span>
                            </div>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('tenant_id')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['tenant_id'] ?? 'Unknown'); ?></span>
                            </div>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('total_keys')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['total_keys'] ?? 0); ?></span>
                            </div>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('total_size')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['total_size'] ?? 'N/A'); ?></span>
                            </div>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('hit_rate')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['hit_rate'] ?? 'N/A'); ?>%</span>
                            </div>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('cache_health')); ?></span>
                                <span
                                    class="ml-1 font-medium <?php echo e($cacheStatus['cache_health'] === 'healthy' ? 'text-success-600' : 'text-warning-600'); ?>">
                                    <?php echo e(ucfirst($cacheStatus['cache_health'] ?? 'unknown')); ?>

                                </span>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php if(isset($cacheStatus['last_cleared'])): ?>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('last_cleared')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['last_cleared'] ?: 'Never'); ?></span>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <div>
                                <span class="text-primary-700 dark:text-primary-300"><?php echo e(t('cache_tags')); ?></span>
                                <span class="ml-1 font-medium"><?php echo e($cacheStatus['cache_tags_supported'] ? '✅ Supported' :
                                    '❌ Not Supported'); ?></span>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-primary-600 dark:text-primary-400">
                            ℹ️ <?php echo e(t('cache_clear_desc')); ?>

                        </div>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <ul class="divide-y divide-neutral-200 dark:divide-neutral-500/30">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cacheSizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex flex-col sm:flex-row  sm:items-center sm:space-y-0">
                            <div class="grow flex items-start space-x-4">
                                <!-- Icons based on type -->
                                <div class="shrink-0 mt-1">
                                    <!--[if BLOCK]><![endif]--><?php if($type === 'framework'): ?>
                                    <div class="p-2 bg-emerald-100 dark:bg-emerald-500/20 rounded-lg">
                                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7" />
                                        </svg>
                                    </div>
                                    <?php elseif($type === 'views'): ?>
                                    <div class="p-2 bg-info-100 dark:bg-info-500/20 rounded-lg">
                                        <svg class="w-5 h-5 text-info-600 dark:text-info-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                    <?php elseif($type === 'config'): ?>
                                    <div class="p-2 bg-purple-100 dark:bg-purple-500/20 rounded-lg">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                        </svg>
                                    </div>
                                    <?php elseif($type === 'routing'): ?>
                                    <div class="p-2 bg-warning-100 dark:bg-warning-500/20 rounded-lg">
                                        <svg class="w-5 h-5 text-warning-600 dark:text-warning-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <?php elseif($type === 'logs'): ?>
                                    <div class="p-2 bg-danger-100 dark:bg-danger-500/20 rounded-lg">
                                        <svg class="w-5 h-5 text-danger-600 dark:text-danger-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <div>
                                    <p class="text-slate-700 dark:text-slate-300 font-medium">
                                        <!--[if BLOCK]><![endif]--><?php if($type === 'framework'): ?>
                                        <?php echo e(t('clear_framework_text')); ?>

                                        <?php elseif($type === 'views'): ?>
                                        <?php echo e(t('view_text')); ?>

                                        <?php elseif($type === 'config'): ?>
                                        <?php echo e(t('clear_config')); ?>

                                        <?php elseif($type === 'routing'): ?>
                                        <?php echo e(t('clear_cache_routing')); ?>

                                        <?php elseif($type === 'logs'): ?>
                                        <?php echo e(t('clear_system_log_file')); ?>

                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </p>
                                    <span
                                        class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium
                                          <?php echo e($size === '0 B' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-primary-100 text-primary-800 dark:bg-primary-700 dark:text-primary-300'); ?>">
                                        <?php echo e(t('size') . ' ' . $size); ?>

                                    </span>
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="flex justify-end shrink-0 sm:ml-4">
                                <button wire:click="clearCache('<?php echo e($type); ?>')"
                                    class="bg-primary-600 dark:bg-primary-500 dark:focus:ring-offset-slate-800 dark:hover:bg-primary-600 disabled:opacity-50 disabled:pointer-events-none duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 font-medium hover:bg-primary-700 inline-flex items-center justify-center px-3 py-2 rounded-lg sm:px-3 sm:py-2 sm:text-sm sm:w-auto text-white text-xs transition-colors"
                                    wire:loading.attr="disabled" wire:target="clearCache('<?php echo e($type); ?>')">

                                    <span wire:loading.remove wire:target="clearCache('<?php echo e($type); ?>')">
                                        <?php echo e(t('run_tool')); ?>

                                    </span>
                                    <span wire:loading wire:target="clearCache('<?php echo e($type); ?>')"
                                        class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-flex" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <?php echo e(t('processing')); ?>

                                    </span>
                                </button>
                            </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex flex-wrap gap-4 sm:gap-8 w-full justify-between">
                            <!-- WhatsApp Log -->
                            <div class="flex items-start space-x-4">
                                <!-- WhatsApp Log Icon -->
                                <div class="shrink-0">
                                    <div class="p-2 bg-success-100 dark:bg-success-500/20 rounded-lg">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- WhatsApp Log Label -->
                                <div class="flex items-center justify-between w-64">
                                    <div>
                                        <p class="text-slate-700 dark:text-slate-300 font-medium">
                                            <?php echo e(t('enable_whatsapp_log')); ?>

                                        </p>
                                        <span
                                            class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-700 dark:text-primary-300">
                                            Mode : <?php echo e($enable_wp_log ? 'true' : 'false'); ?>

                                        </span>
                                    </div>

                                    <!-- WhatsApp Log Switch -->
                                    <div x-data="{ enable_wp_log: <?php if ((object) ('enable_wp_log') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_wp_log'->value()); ?>')<?php echo e('enable_wp_log'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('enable_wp_log'); ?>')<?php endif; ?>.defer }">
                                        <?php if (isset($component)) { $__componentOriginal592735d30e1926fbb04ff9e089d1fccf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal592735d30e1926fbb04ff9e089d1fccf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toggle','data' => ['id' => 'enable_wp_log','name' => 'enable_wp_log','value' => $enable_wp_log,'wire:click' => 'toggleEnableWpLog','wire:model' => 'enable_wp_log']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'enable_wp_log','name' => 'enable_wp_log','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enable_wp_log),'wire:click' => 'toggleEnableWpLog','wire:model' => 'enable_wp_log']); ?>
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
                            </div>
                        </div>
                    </li>
                </ul>
                <!-- Notification -->
                <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                <div
                    class="mt-4 p-4 text-sm text-success-700 bg-success-100 rounded-lg dark:bg-success-200 dark:text-success-800">
                    <?php echo e(session('message')); ?>

                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <div wire:loading wire:target="clearCache" class="my-4 text-center px-6">
                    <p class="text-sm text-info-600"><?php echo e(t('processing_cache_clearing')); ?></p>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/ahtisham/app.chatvoo.com/Modules/CacheManager/resources/views/livewire/tenant/settings/system/cache-management-settings.blade.php ENDPATH**/ ?>