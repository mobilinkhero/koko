<div>
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('admin_dashboard')); ?> - <?php echo e($adminName); ?>

     <?php $__env->endSlot(); ?>

    
    <?php
        $settings = get_batch_settings(['whats-mark.whatsmark_latest_version', 'whats-mark.wm_version']);
    ?>
    <!--[if BLOCK]><![endif]--><?php if(
        $settings['whats-mark.whatsmark_latest_version'] != null &&
            $settings['whats-mark.whatsmark_latest_version'] != $settings['whats-mark.wm_version'] &&
            $settings['whats-mark.wm_version'] <= $settings['whats-mark.whatsmark_latest_version']
    ): ?>
        <div class="mb-3">
            <div>
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
                    <p><?php echo e(t('new_update_available_alert')); ?> <a href="/admin/system-update"
                            class="alert-link underline font-semibold"><?php echo e(t('click_here')); ?></a><?php echo e(t('to_update_version')); ?>

                    </p>
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
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <!--[if BLOCK]><![endif]--><?php if(!env('APP_PREVIOUS_KEYS')): ?>
        <div class="mb-3 mt-3">
            <div>
                <?php if (isset($component)) { $__componentOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58f1ae2fa6fc61c6beeebb5be974a822 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dynamic-alert','data' => ['type' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger']); ?>
                     <?php $__env->slot('title', null, ['class' => 'mb-3']); ?> <?php echo e(t('configuration_sync_required')); ?> <?php $__env->endSlot(); ?>
                    <p><?php echo e(t('current_system_requirements')); ?> <a wire:click="updateEnv()"
                            class="alert-link cursor-pointer font-semibold underline"><?php echo e(t('click_here')); ?></a>
                        <?php echo e(t('current_system_requirements_contenant_2')); ?></p>
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
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <div class="mb-3" x-cloak x-data="{
        appMode: '<?php echo e(app()->environment()); ?>',
        appDebug: <?php echo json_encode(config('app.debug'), 15, 512) ?>,
        isVisible() {
            return this.appMode === 'local' && this.appDebug;
        }
    }" x-bind:class="{ 'hidden': !isVisible() }">
        <div x-show="isVisible()">
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
                 <?php $__env->slot('title', null, ['class' => 'mb-3']); ?> <?php echo e(t('development_warning_title')); ?> <?php $__env->endSlot(); ?>

                <?php echo e(t('development_warning_content')); ?>

                <ul>
                    <li><strong><?php echo e(t('app_env')); ?></strong> <span><?php echo e(t('production')); ?></span></li>
                    <li><strong><?php echo e(t('app_debug')); ?></strong> <span><?php echo e(t('debug_false')); ?></span></li>
                </ul>

                <?php echo e(t('development_warning_details')); ?>

                <?php echo e(t('performance_security_tip')); ?>

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
    </div>
    <!-- Dashboard Header -->
    <div class="mb-4 bg-white dark:bg-slate-800 px-4 py-3 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                    <?php echo e(t('hello')); ?>, <?php echo e($adminName); ?>

                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <?php echo e(t('welcome_to_dashboard')); ?>

                    <span class="ml-2 inline-flex items-center">
                        <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <?php echo e(t('last_updated')); ?> <span
                            class="font-medium text-slate-600 dark:text-slate-300"><?php echo e($lastUpdated); ?></span>
                    </span>
                </p>
            </div>
            <button wire:click="refreshDashboardData" wire:loading.class="opacity-75 cursor-wait"
                wire:loading.attr="disabled" wire:target="refreshDashboardData"
                class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 px-2.5 py-1.5 rounded-md transition-colors flex items-center text-xs">
                <svg wire:loading.remove wire:target="refreshDashboardData" class="w-3.5 h-3.5 mr-1" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="refreshDashboardData" class="animate-spin w-3.5 h-3.5 mr-1"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="refreshDashboardData"><?php echo e(t('refresh')); ?></span>
                <span wire:loading wire:target="refreshDashboardData"><?php echo e(t('refresh')); ?>...</span>
            </button>
        </div>
    </div>

    <!-- Statistics Cards Section -->
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
            <h2 class="text-lg font-medium text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                <?php echo e(t('system_statistics')); ?>

            </h2>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('content', null, []); ?> 
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Active Subscriptions -->
                <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => ''.e(t('total_subscriptions')).'','value' => $activeSubscriptions,'subtitle' => 'Since Last Month: '.e($activeSubscriptionsChange >= 0 ? '+' : '').''.e($activeSubscriptionsChange).'','color' => 'indigo','bg' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(t('total_subscriptions')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeSubscriptions),'subtitle' => 'Since Last Month: '.e($activeSubscriptionsChange >= 0 ? '+' : '').''.e($activeSubscriptionsChange).'','color' => 'indigo','bg' => true]); ?>
                     <?php $__env->slot('icon', null, []); ?> 
                        <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-9.618 5.04L2 8.5V14c0 4.97 4.03 9 9 9a9 9 0 009-9V8.5l-.382-.516z" />
                        </svg>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>

                <!-- Total Earnings -->
                <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => ''.e(t('total_earnings')).'','value' => get_base_currency()->format($totalEarnings),'subtitle' => 'Since Last Month: '.e($totalEarningsChange >= 0 ? '+' : '').''.e(get_base_currency()->format($totalEarningsChange)).'','color' => 'emerald','bg' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(t('total_earnings')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(get_base_currency()->format($totalEarnings)),'subtitle' => 'Since Last Month: '.e($totalEarningsChange >= 0 ? '+' : '').''.e(get_base_currency()->format($totalEarningsChange)).'','color' => 'emerald','bg' => true]); ?>
                     <?php $__env->slot('icon', null, []); ?> 
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>

                <!-- Total Clients -->
                <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => ''.e(t('total_clients')).'','value' => $totalClients,'subtitle' => 'Since Last Month: '.e($totalClientsChange >= 0 ? '+' : '').''.e($totalClientsChange).'','color' => 'blue','bg' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(t('total_clients')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalClients),'subtitle' => 'Since Last Month: '.e($totalClientsChange >= 0 ? '+' : '').''.e($totalClientsChange).'','color' => 'blue','bg' => true]); ?>
                     <?php $__env->slot('icon', null, []); ?> 
                        <svg class="h-6 w-6 text-info-600 dark:text-info-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>

                <!-- Total Campaigns -->
                <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => ''.e(t('total_campaigns')).'','value' => $totalCampaigns,'subtitle' => 'Since Last Month: '.e($totalCampaignsChange >= 0 ? '+' : '').''.e($totalCampaignsChange).'','color' => 'purple','bg' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(t('total_campaigns')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalCampaigns),'subtitle' => 'Since Last Month: '.e($totalCampaignsChange >= 0 ? '+' : '').''.e($totalCampaignsChange).'','color' => 'purple','bg' => true]); ?>
                     <?php $__env->slot('icon', null, []); ?> 
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
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

    <!-- Charts Section -->
    <div class="mt-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <!-- Earnings Report - 60% width -->
            <div class="w-full sm:w-3/5">
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
                        <h2
                            class="text-lg font-medium text-slate-700 dark:text-slate-300  border-slate-200 dark:border-slate-700">
                            <?php echo e(t('earnings_report')); ?>

                        </h2>
                     <?php $__env->endSlot(); ?>
                     <?php $__env->slot('content', null, []); ?> 
                        <div class="h-72" wire:ignore>
                            <canvas id="earningsChart"></canvas>
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

            <!-- Plan Distribution - 40% width -->
            <div class="w-full sm:w-2/5">
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
                        <h2
                            class="text-lg font-medium text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                            <?php echo e(t('best_selling_plan')); ?>

                        </h2>
                     <?php $__env->endSlot(); ?>
                     <?php $__env->slot('content', null, []); ?> 
                        <div class="space-y-3" id="plan-cards">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg text-center">
                                <p class="text-gray-500 dark:text-gray-400"><?php echo e(t('loading_plan_data')); ?></p>
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
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Charts Initialization -->
<script>
    document.addEventListener('livewire:initialized', function() {
        // Wait for DOM to be fully rendered before initializing charts
        setTimeout(() => {
            initEarningsChart();
            initPlanDistributionChart(); // Call function to initialize plan cards
        }, 100);

        Livewire.on('chartDataUpdated', function() {
            setTimeout(() => {
                initEarningsChart();
                initPlanDistributionChart(); // Call function to refresh plan cards
            }, 100);
        });

        Livewire.on('reload-page', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1000); // 1 second delay
        });
    });

    function initEarningsChart() {
        const earningsData = <?php echo \Illuminate\Support\Js::from($earningsData)->toHtml() ?>;
        const ctx = document.getElementById('earningsChart');
        const currencyFormat = <?php echo json_encode($currencyFormat, 15, 512) ?>;
        const baseCurrency = <?php echo json_encode($baseCurrency, 15, 512) ?>;

        // Destroy existing chart if it exists
        if (window.earningsChart instanceof Chart) {
            window.earningsChart.destroy();
        }

        window.earningsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: earningsData.labels,
                datasets: earningsData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 12
                        },
                        bodyFont: {
                            size: 11
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    const value = context.parsed.y.toLocaleString();
                                    label += currencyFormat === 'before_amount' ? baseCurrency + value :
                                        value + baseCurrency;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(200, 200, 200, 0.2)'
                        },
                        ticks: {
                            callback: function(value) {
                                return currencyFormat == 'before_amount' ? baseCurrency + value
                                    .toLocaleString() : value.toLocaleString() + baseCurrency;
                            },
                            font: {
                                size: 10
                            },
                            padding: 5
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 3,
                        hoverRadius: 5
                    },
                    line: {
                        tension: 0.4,
                        borderWidth: 2
                    }
                }
            }
        });
    }

    function initPlanDistributionChart() {
        // Plan distribution now uses cards instead of charts
        // We're using JavaScript to render the cards dynamically
        const planData = <?php echo \Illuminate\Support\Js::from($planDistributionData)->toHtml() ?>;
        const planCardsContainer = document.getElementById('plan-cards');

        if (!planCardsContainer) {
            console.error('Plan cards container not found');
            return;
        }

        // Clear existing content
        planCardsContainer.innerHTML = '';

        // Check if we have plans data
        if (planData && planData.plans && planData.plans.length > 0) {
            // Render each plan as a card
            planData.plans.forEach(plan => {
                const color = plan.color || '#6b7280';
                const name = plan.name || 'Unknown Plan';
                const count = plan.count || 0;
                const price = plan.price || '0.00';
                const currency = '<?php echo e($baseCurrency); ?>';

                const cardHtml = `
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="h-full">
                                <div class="h-full w-1.5 rounded-full" style="background-color: ${color}"></div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900 dark:text-white">${name}</p>
                                    <p class="font-medium text-gray-900 dark:text-white">${count}</p>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">${price}</p>
                            </div>
                        </div>
                    </div>
                `;

                planCardsContainer.innerHTML += cardHtml;
            });
        } else {
            // No plans available
            planCardsContainer.innerHTML = `
                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg text-center">
                    <p class="text-gray-500 dark:text-gray-400">No active subscriptions</p>
                </div>
            `;
        }
    }
</script>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>