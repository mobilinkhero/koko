<div>
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('waba')); ?>

     <?php $__env->endSlot(); ?>

    <?php
        $wpSettings = tenant_settings_by_group('whatsapp');
        $healthStatus = json_decode($wpSettings['wm_health_data']);
        $defaultPhoneNumberData = collect($phone_numbers)->firstWhere('id', $wpSettings['wm_default_phone_number_id']);
    ?>

    <!-- Page Header -->
    <div class="mb-4">
        <div class="flex flex-col items-start md:flex-row md:items-center md:justify-between">
            <h3 class="text-2xl font-semibold text-slate-900 dark:text-slate-200">
                <?php echo e(t('whatsapp_business_account')); ?>

            </h3>
            <div class="flex flex-row items-center justify-center md:justify-start mt-4 gap-3 w-full md:w-auto">
                <button data-tippy-content="It helps to check your server and application configurations"
                    x-on:click="window.captureScreenshot('capture-area')"
                    class="p-2 rounded-md transition duration-300 ease-in-out bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 focus:ring-offset-gray-200 dark:focus:ring-offset-gray-900">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-camera'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-white dark:text-gray-200 font-medium']); ?>
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
                </button>

                <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['xData' => true,'xOn:click' => '$dispatch(\'open-modal\', { reset: true })','class' => 'hidden sm:inline-flex']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-data' => true,'x-on:click' => '$dispatch(\'open-modal\', { reset: true })','class' => 'hidden sm:inline-flex']); ?>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-squares-2x2'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 inline-block']); ?>
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
                    <?php echo e(t('qr_code')); ?>

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

                <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['xData' => true,'xOn:click' => '$dispatch(\'open-modal\', { reset: true })','class' => 'sm:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-data' => true,'x-on:click' => '$dispatch(\'open-modal\', { reset: true })','class' => 'sm:hidden']); ?>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-squares-2x2'); ?>
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
                    <span class="hidden xss:block"><?php echo e(t('get_qr_code')); ?></span>
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

                <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.connect_account.disconnect')): ?>
                    <?php if (isset($component)) { $__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.danger','data' => ['wire:click' => '$set(\'confirmingDeletion\', true)','class' => 'hidden sm:inline-flex']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.danger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'confirmingDeletion\', true)','class' => 'hidden sm:inline-flex']); ?>
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 inline-block']); ?>
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
                        <?php echo e(t('disconnect_account')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee)): ?>
<?php $attributes = $__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee; ?>
<?php unset($__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee)): ?>
<?php $component = $__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee; ?>
<?php unset($__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee); ?>
<?php endif; ?>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <?php if (isset($component)) { $__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.danger','data' => ['wire:click' => '$set(\'confirmingDeletion\', true)','class' => 'sm:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.danger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'confirmingDeletion\', true)','class' => 'sm:hidden']); ?>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
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
                    <span class="hidden xss:block"><?php echo e(t('disconnect')); ?></span>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee)): ?>
<?php $attributes = $__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee; ?>
<?php unset($__attributesOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee)): ?>
<?php $component = $__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee; ?>
<?php unset($__componentOriginal8e7eb5f4ff8ff9be375a67ea4dc288ee); ?>
<?php endif; ?>
            </div>

        </div>
    </div>
    <!-- Main Content -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6" id="capture-area">
        <!-- Left Column -->
        <div class="space-y-6">
            <!-- Access Token Information -->
            <div class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600">
                <div class="border-b border-slate-300 px-4 py-5 sm:px-6 dark:border-slate-600">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-info-600 rounded-lg">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-key'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-white']); ?>
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo e(t('access_token_info')); ?>

                        </h3>
                    </div>
                </div>

                <div>
                    <div class="divide-y   dark:divide-white/5">
                        <!-- Access Token -->
                        <div class="px-6 py-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <?php echo e(t('access_token')); ?>

                                </h4>
                                <div x-data="{ copied: false }" class="group relative">

                                    <div class="mt-1 flex rounded-md shadow-sm">

                                        <!-- For Admin Users (Show Full Access Token) -->
                                        <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.connect_account.connect')): ?>
                                            <div class="relative flex flex-grow items-stretch focus-within:z-10">
                                                <input type="text" value="<?php echo e($wpSettings['wm_access_token']); ?>"
                                                    readonly
                                                    class="block w-full rounded-l-lg border-0 py-2 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 text-sm leading-6" />
                                            </div>

                                            <button type="button"
                                                x-on:click="navigator.clipboard.writeText($el.previousElementSibling.querySelector('input').value).then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                                                class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-pg-primary-200">
                                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                            </button>

                                            <!-- For Non-Admin Users (Blurred and Uncopyable Content) -->
                                        <?php else: ?>
                                            <div class="relative w-full flex items-center justify-start rounded-lg">
                                                <span
                                                    class="text-danger-600 dark:text-danger-400 text-sm font-medium flex items-center gap-1">
                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-lock-closed'); ?>
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
                                                    <?php echo e(t('not_allowed_to_view')); ?>

                                                </span>
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                </div>

                            </div>
                        </div>

                        <!-- Permission Scopes -->
                        <div class="px-6 py-4">
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <?php echo e(t('permission_scopes')); ?>

                                </h4>
                                <!--[if BLOCK]><![endif]--><?php if(!empty($token_info['scopes'])): ?>
                                    <div class="flex flex-wrap gap-2">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $token_info['scopes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scope): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium bg-success-50 text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/20">
                                                <?php echo e($scope); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <!-- Token Details -->
                        <div class="px-6 py-4 grid grid-cols-2 gap-6">
                            <!-- Issued At -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <?php echo e(t('issued')); ?>

                                </h4>
                                <p class="mt-2 text-sm text-gray-900 dark:text-white">
                                    <?php echo e($token_info['issued_at'] ?? 'N/A'); ?>

                                </p>
                            </div>

                            <!-- Expiry -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <?php echo e(t('expiry')); ?>

                                </h4>
                                <p class="mt-2 text-sm text-gray-900 dark:text-white">
                                    <?php echo e(empty($token_info['expires_at']) ? 'N/A' : $token_info['expires_at'] ?? 'N/A'); ?>

                                </p>
                            </div>
                        </div>

                        <!-- Webhook URL -->
                        <div class="px-6 py-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <?php echo e(t('webhook_url')); ?>

                                </h4>
                                <div x-data="{ copied: false }" class="group relative">
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.connect_account.connect')): ?>
                                            <div class="relative flex flex-grow items-stretch focus-within:z-10">
                                                <input type="text"
                                                    value="<?php echo e(implode(', ', array_column(array_column($phone_numbers, 'webhook_configuration'), 'application'))); ?>"
                                                    readonly
                                                    class="block w-full rounded-l-lg border-0 py-2 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 text-sm leading-6" />
                                            </div>
                                            <button type="button"
                                                x-on:click="navigator.clipboard.writeText($el.previousElementSibling.querySelector('input').value).then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                                                class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-pg-primary-200">
                                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                            </button>
                                        <?php else: ?>
                                            <!-- For Non-Admin Users (Access Restricted Message) -->
                                            <div class="relative w-full flex items-center justify-start rounded-lg">
                                                <span
                                                    class="text-danger-600 dark:text-danger-400 text-sm font-medium flex items-center gap-1">
                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-lock-closed'); ?>
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
                                                    <?php echo e(t('not_allowed_to_view')); ?>

                                                </span>
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--[if BLOCK]><![endif]--><?php if(Auth::user()->is_admin): ?>
                <!-- Test Message Card -->
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
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-success-600 rounded-lg">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chat-bubble-bottom-center-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-white']); ?>
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo e(t('test_message')); ?>

                            </h3>
                        </div>
                     <?php $__env->endSlot(); ?>

                     <?php $__env->slot('content', null, ['class' => 'p-6 space-y-4']); ?> 
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-question-mark-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-gray-500 dark:text-gray-400']); ?>
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

                                <?php echo e(t('wp_number')); ?>

                            </label>
                            <input type="text" id="wac_business_account_id" wire:model="wm_test_message"
                                class="block w-full rounded-lg border-0 py-2 pl-3 pr-10 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-primary-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 text-sm leading-6"
                                placeholder="Enter WhatsApp number with country code" />
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['for' => 'wm_test_message','class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'wm_test_message','class' => 'mt-2']); ?>
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

                        <div class="flex justify-end">
                            <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['wire:click' => 'sendTestMessage','class' => 'bg-success-600 hover:bg-success-500 focus-visible:outline-success-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'sendTestMessage','class' => 'bg-success-600 hover:bg-success-500 focus-visible:outline-success-600']); ?>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-paper-airplane'); ?>
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
                                <?php echo e(t('send_message')); ?>

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

                <!-- Verify Webhook Card -->
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
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-purple-600 rounded-lg">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-command-line'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-white']); ?>
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo e(t('verify_webhook')); ?>

                            </h3>
                        </div>
                     <?php $__env->endSlot(); ?>

                     <?php $__env->slot('content', null, ['class' => 'p-6']); ?> 
                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['wire:click' => 'verifyWebhook','class' => 'w-full bg-purple-600 hover:bg-purple-500 focus-visible:outline-purple-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'verifyWebhook','class' => 'w-full bg-purple-600 hover:bg-purple-500 focus-visible:outline-purple-600']); ?>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 animate-spin','wire:loading' => true,'wire:target' => 'verifyWebhook']); ?>
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
<?php $component->withName('heroicon-o-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5','wire:loading.remove' => true,'wire:target' => 'verifyWebhook']); ?>
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
                            <?php echo e(t('verify_webhook')); ?>

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
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        <!-- WhatsApp Business Dashboard -->
        <div class="space-y-6">
            <!-- Phone Numbers Section -->
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $phone_numbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isDefault = $phone['id'] == $wpSettings['wm_default_phone_number_id'];
                    $qualityColor = match ($phone['quality_rating']) {
                        'GREEN' => 'text-success-500',
                        'YELLOW' => 'text-warning-500',
                        'RED' => 'text-danger-500',
                        default => 'text-gray-500',
                    };
                    $statusColor = match ($phone['code_verification_status']) {
                        'VERIFIED'
                            => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/10 dark:text-success-400
            dark:ring-success-500/20',
                        'EXPIRED'
                            => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-400
            dark:ring-danger-500/20',
                        'PENDING'
                            => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-500/10 dark:text-warning-400
            dark:ring-warning-500/20',
                        default
                            => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-500/10 dark:text-gray-400 dark:ring-gray-500/20',
                    };
                ?>

                <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-800 border  dark:border-slate-600">
                    <!-- Card Header -->
                    <div class="px-6 py-4 border-b dark:border-white/5 dark:bg-slate-800/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary-600 rounded-lg">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-phone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-white']); ?>
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
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(t('phone')); ?>

                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e($isDefault ? t('default_phone_number') : t('additional_phone_number')); ?>

                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset <?php echo e($statusColor ?? ''); ?>">
                                    <?php echo e($phone['code_verification_status'] ?? ''); ?>

                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('display_phone_number')); ?>

                                    </h4>
                                    <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($phone['display_phone_number'] ?? ''); ?>

                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('verified_name')); ?>

                                    </h4>
                                    <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($phone['verified_name'] ?? ''); ?>

                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('number_id')); ?>

                                    </h4>

                                    <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.connect_account.connect')): ?>
                                        <p class="mt-2 text-base font-medium text-gray-900 dark:text-white font-mono">
                                            <?php echo e($phone['id'] ?? ''); ?>

                                        </p>
                                    <?php else: ?>
                                        <div class="mt-2 flex items-center space-x-1 rounded-lg">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-lock-closed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-danger-600 dark:text-danger-400']); ?>
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
                                            <span class="text-danger-600 dark:text-danger-400 text-sm font-medium">
                                                <?php echo e(t('not_allowed_to_view')); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('quality')); ?>

                                    </h4>
                                    <p class="mt-2 text-base font-semibold <?php echo e($qualityColor ?? ''); ?>">
                                        <?php echo e($phone['quality_rating'] ?? ''); ?>

                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('messaging_limit')); ?>

                                    </h4>
                                    <?php
                                        $phoneNumber = preg_replace('/\D/', '', $phone['display_phone_number']);
                                        $analytics = $message_details['analytics'] ?? [];
                                        $phoneNumbers = $analytics['phone_numbers'] ?? [];
                                        $dataPoints = $analytics['data_points'] ?? [];
                                        $index = array_search($phoneNumber, $phoneNumbers, true);
                                        $sentCount =
                                            $index !== false && isset($dataPoints[$index]['sent'])
                                                ? $dataPoints[$index]['sent']
                                                : 0;
                                        $limit = !empty($message_details['limit_value'] ?? 1000)
                                            ? $message_details['limit_value']
                                            : 1000;
                                        $percentage = ($sentCount / ($limit ?? 1000)) * 100;
                                    ?>

                                    <div class="mt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                <div class="bg-primary-600 h-2 rounded-full"
                                                    style="width:<?php echo e($percentage); ?>%">
                                                    <div class="bg-primary-600 h-2 rounded-full"
                                                        style="width: <?php echo e($percentage); ?>%">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($percentage); ?>%</span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($sentCount . '/' . $message_details['limit_value']); ?>

                                                <?php echo e(t('messages_sent_today')); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t   dark:border-white/5">
                        <div class="flex justify-between items-center">
                            <!--[if BLOCK]><![endif]--><?php if($isDefault): ?>
                                <a href="https://business.facebook.com/wa/manage/phone-numbers/?waba_id=<?php echo e($wpSettings['wm_business_account_id']); ?>"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white rounded-lg shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50 dark:bg-slate-700 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-slate-600">
                                    <?php echo e(t('manage_phone_numbers')); ?>

                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-top-right-on-square'); ?>
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

                                </a>
                            <?php else: ?>
                                <button
                                    wire:click="setDefaultNumber('<?php echo e($phone['id']); ?>', '<?php echo e($phone['display_phone_number']); ?>')"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-check-circle'); ?>
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

                                    <?php echo e(t('mark_as_default')); ?>

                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <!--[if BLOCK]><![endif]--><?php if($phone['quality_rating'] == 'UNKNOWN' && Auth::user()->is_admin): ?>
                                <button wire:click="registerNumber('<?php echo e($phone['id']); ?>')"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-check-circle'); ?>
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

                                    <?php echo e(t('register_phone_number')); ?>

                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

            <!-- Overall Health Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Health Status Card -->
                <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-800 border  dark:border-slate-600">
                    <div class="px-6 py-4 border-b   dark:border-white/5 dark:bg-slate-800/50">
                        <div class="flex items-center gap-5">
                            <div class="p-2 bg-purple-600 rounded-lg flex items-center justify-center">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-heart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-white']); ?>
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
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(t('overall_health')); ?>

                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo e(t('last_checked')); ?> <?php echo e($wpSettings['wm_health_check_time']); ?>

                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y   dark:divide-white/5">
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        <?php echo e(t('whatsapp_business_id')); ?>

                                    </h4>

                                    <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.connect_account.connect')): ?>
                                        <p class="mt-1 text-base font-medium text-gray-900 dark:text-white font-mono">
                                            <?php echo e($healthStatus->id); ?>

                                        </p>
                                    <?php else: ?>
                                        <div class="mt-1 flex items-center space-x-1 rounded-lg">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-lock-closed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3 h-3 text-danger-600 dark:text-danger-400']); ?>
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
                                            <span class="text-danger-600 dark:text-danger-400 text-sm font-medium">
                                                <?php echo e(t('not_allowed_to_view')); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset <?php echo e($healthStatus->health_status->can_send_message === 'AVAILABLE' ? 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/20' : 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-500/20'); ?>">
                                    <?php echo e($healthStatus->health_status->can_send_message); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WABA Entities -->
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $healthStatus->health_status->entities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="overflow-hidden rounded-lg bg-white dark:bg-slate-800 border  dark:border-slate-600">
                        <div class="px-6 py-4 border-b   dark:border-white/5 dark:bg-slate-800/50">
                            <div class="flex items-center gap-4">
                                <div class="p-2 bg-info-600 rounded-lg flex items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-computer-desktop'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-white']); ?>
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

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($entity->entity_type); ?>

                                    </h3>

                                    <!--[if BLOCK]><![endif]--><?php if(checkPermission('tenant.connect_account.connect')): ?>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: <?php echo e($entity->id); ?>

                                        </p>
                                    <?php else: ?>
                                        <div class="flex items-center space-x-1">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-lock-closed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-danger-600 dark:text-danger-400']); ?>
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
                                            <span class="text-danger-600 dark:text-danger-400 text-sm font-medium">
                                                <?php echo e(t('not_allowed_to_view')); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>

                        <div class="divide-y   dark:divide-white/5">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            <?php echo e(t('can_send_message')); ?>

                                        </h4>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset <?php echo e($entity->can_send_message === 'AVAILABLE' ? 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/20' : 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-500/20'); ?>">
                                        <?php echo e($entity->can_send_message ?? ''); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Refresh Button -->
            <div class="flex justify-end">
                <button wire:click="refreshHealth"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-success-600 rounded-lg shadow-sm hover:bg-success-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-success-600">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 animate-spin','wire:loading' => true,'wire:target' => 'refreshHealth']); ?>
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
<?php $component->withName('heroicon-s-arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4','wire:loading.remove' => true,'wire:target' => 'refreshHealth']); ?>
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
                    <?php echo e(t('refresh_health_status')); ?>

                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <?php if (isset($component)) { $__componentOriginal79e52b819ddc9a73b4560c41923d18f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79e52b819ddc9a73b4560c41923d18f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.confirm-box','data' => ['maxWidth' => 'lg','id' => 'delete-contact-modal','title' => ''.e(t('disconnect_account')).'','wire:model.defer' => 'confirmingDeletion','description' => ''.e(t('disconnect_message')).' ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal.confirm-box'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['maxWidth' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('lg'),'id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('delete-contact-modal'),'title' => ''.e(t('disconnect_account')).'','wire:model.defer' => 'confirmingDeletion','description' => ''.e(t('disconnect_message')).' ']); ?>
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.delete-button','data' => ['wire:click' => 'disconnectAccount','class' => 'mt-3 sm:mt-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'disconnectAccount','class' => 'mt-3 sm:mt-0']); ?>
                <?php echo e(t('disconnect')); ?>

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

    <!-- Stylish QR Code Modal -->
    <div x-data="{
        isOpen: false,
        qrUrl: {
            link: '<?php echo e(asset('storage/tenant/' . tenant_id() . '/images/qrcode.png')); ?>',
            copied: false
        },
        whatsappUrl: {
            link: 'https://api.whatsapp.com/send?phone=' + '<?php echo e($wpSettings['wm_default_phone_number']); ?>',
            copied: false
        },
        copyToClipboard(text, type) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    if (type === 'qr') {
                        this.qrUrl.copied = true;
                        setTimeout(() => this.qrUrl.copied = false, 2000);
                    } else {
                        this.whatsappUrl.copied = true;
                        setTimeout(() => this.whatsappUrl.copied = false, 2000);
                    }
                });
        }
    }" x-on:open-modal.window="isOpen = true" x-on:keydown.escape.window="isOpen = false">
        <template x-if="isOpen">
            <div class="fixed inset-0 z-50">
                <!-- Stylish Backdrop with Gradient -->
                <div class="fixed inset-0 backdrop-blur-sm bg-gradient-to-br from-black/30 to-black/60">
                </div>

                <!-- Modal Container with Animation -->
                <div class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white/95 dark:bg-slate-800/95 shadow-2xl ring-1 ring-black/5 dark:ring-white/5">
                            <!-- Gradient Background Accent -->
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-primary-50/50 via-transparent to-purple-50/50 dark:from-primary-900/10 dark:to-purple-900/10">
                            </div>

                            <!-- Content Container -->
                            <div class="relative">
                                <!-- Stylish Header -->
                                <div
                                    class="px-6 py-4 border-b border-black/5 dark:border-white/5 bg-white/50 dark:bg-slate-800/50">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-primary-500 rounded-lg">

                                        </div>
                                        <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-200">
                                            <?php echo e(t('qr_code_to_start_chat')); ?>

                                        </h1>
                                    </div>
                                </div>

                                <!-- Modern Content Section -->
                                <div class="px-6 py-4 space-y-6">
                                    <!-- Info Banner with Gradient -->
                                    <div
                                        class="p-4 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 dark:from-primary-700 dark:to-primary-800">
                                        <div class="flex items-center justify-center text-white">
                                            <span class="text-medium font-medium">
                                                <?php echo e(t('qr_code_to_invite_people')); ?>

                                            </span>
                                        </div>
                                    </div>

                                    <!-- Modern Card Design -->
                                    <div
                                        class="overflow-hidden rounded-lg bg-white dark:bg-slate-700/50 ring-1 ring-black/5 dark:ring-white/5">
                                        <!-- Company Header -->
                                        <div
                                            class="px-6 py-4 border-b border-black/5 dark:border-white/5 bg-gradient-to-r from-gray-50 to-white dark:from-slate-800 dark:to-slate-700/50">
                                            <div class="flex items-center justify-center">
                                                <h3
                                                    class="text-lg font-semibold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent dark:from-primary-400 dark:to-primary-300">
                                                    <?php echo e($defaultPhoneNumberData['verified_name'] ?? ''); ?>

                                                    <span
                                                        class="text-slate-600 dark:text-slate-300">(<?php echo e($defaultPhoneNumberData['display_phone_number'] ?? ''); ?>)</span>
                                                </h3>
                                            </div>
                                        </div>

                                        <!-- QR Code Section -->
                                        <div class="p-6">
                                            <div class="flex flex-col items-center space-y-6">
                                                <!-- Stylish QR Code Container -->
                                                <div class="p-3 bg-white rounded-lg shadow-lg ring-1 ring-black/5">
                                                    <div class="p-2 bg-gradient-to-br from-primary-50 to-purple-50">
                                                        <img src="<?php echo e(asset('storage/tenant/' . tenant_id() . '/images/qrcode.png')); ?>"
                                                            alt="QR Code" class="h-48 w-48">
                                                    </div>
                                                </div>

                                                <!-- Phone Number Display -->
                                                <div class="flex flex-col items-center">
                                                    <span
                                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"><?php echo e(t('phone')); ?></span>
                                                    <span
                                                        class="text-lg font-semibold text-success-500 dark:text-success-400">
                                                        <?php echo e($defaultPhoneNumberData['display_phone_number'] ?? ''); ?>

                                                    </span>
                                                </div>

                                                <!-- QR URL with Hover Effect -->
                                                <div
                                                    class="w-full p-4 rounded-lg bg-gray-50 dark:bg-slate-600/30 group hover:bg-gray-100 dark:hover:bg-slate-600/50 transition-all">
                                                    <div class="flex items-center justify-between flex-wrap">
                                                        <div class="flex flex-col">
                                                            <h5
                                                                class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                                <?php echo e(t('url_for_qr_image')); ?></h5>
                                                            <a :href="qrUrl.link"
                                                                class="text-wrap text-sm text-primary-500 dark:text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 truncate transition-colors break-all"
                                                                x-text="qrUrl.link" target="_blank"></a>
                                                        </div>
                                                        <button x-on:click="copyToClipboard(qrUrl.link, 'qr')"
                                                            class="hidden sm:block mt-3 md:mt-0 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-white dark:bg-slate-700 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/5 hover:shadow-md transition-all"
                                                            x-text="qrUrl.copied ? '<?php echo e(t('Copied')); ?>' : '<?php echo e(t('Copy')); ?>'">
                                                        </button>
                                                        <button x-on:click="copyToClipboard(qrUrl.link, 'qr')"
                                                            data-tippy-content="<?php echo e(t('copied')); ?>"
                                                            class="sm:hidden mt-3 md:mt-0 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 dark:bg-slate-700 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/5 hover:shadow-md transition-all">
                                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('carbon-copy'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-h']); ?>
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
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- WhatsApp URL with Hover Effect -->
                                                <div
                                                    class="w-full p-4 rounded-lg bg-gray-50 dark:bg-slate-600/30 group hover:bg-gray-100 dark:hover:bg-slate-600/50 transition-all">
                                                    <div class="flex items-center justify-between flex-wrap">
                                                        <div class="flex flex-col">
                                                            <h5
                                                                class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                                <?php echo e(t('whatsapp_url')); ?></h5>
                                                            <a :href="whatsappUrl.link"
                                                                class="text-sm text-primary-500 dark:text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 truncate transition-colors text-wrap break-all"
                                                                x-text="whatsappUrl.link" target="_blank"></a>
                                                        </div>
                                                        <div class="flex flex-row items-center gap-2 ml-0.5 my-2 ">
                                                            <button
                                                                x-on:click="copyToClipboard(whatsappUrl.link, 'wa')"
                                                                class="hidden sm:block px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-white dark:bg-slate-700 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/5 hover:shadow-md transition-all"
                                                                x-text="whatsappUrl.copied ? '<?php echo e(t('Copied')); ?>' : '<?php echo e(t('Copy')); ?>'">
                                                            </button>
                                                            <button
                                                                x-on:click="copyToClipboard(whatsappUrl.link, 'wa')"
                                                                data-tippy-content="<?php echo e(t('copied')); ?>"
                                                                class="sm:hidden px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-white dark:bg-slate-700 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/5 hover:shadow-md transition-all">
                                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('carbon-copy'); ?>
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
                                                            </button>

                                                            <a :href="whatsappUrl.link" target="_blank"
                                                                class="hidden sm:block px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-success-500 to-success-600 dark:from-success-600 dark:to-success-700 rounded-lg shadow-sm hover:shadow-md transition-all">
                                                                <?php echo e(t('whatsapp_now')); ?>

                                                            </a>
                                                            <a :href="whatsappUrl.link" target="_blank"
                                                                class="sm:hidden px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-success-500 to-success-600 dark:from-success-600 dark:to-success-700 rounded-lg shadow-sm hover:shadow-md transition-all">
                                                                <svg class="h-5 w-5"
                                                                    xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                                    width="100" height="100"
                                                                    viewBox="0,0,256,256">
                                                                    <g fill="#ffffff" fill-rule="nonzero"
                                                                        stroke="none" stroke-width="1"
                                                                        stroke-linecap="butt" stroke-linejoin="miter"
                                                                        stroke-miterlimit="10" stroke-dasharray=""
                                                                        stroke-dashoffset="0" font-family="none"
                                                                        font-weight="none" font-size="none"
                                                                        text-anchor="none"
                                                                        style="mix-blend-mode: normal">
                                                                        <g transform="scale(10.66667,10.66667)">
                                                                            <path
                                                                                d="M12.01172,2c-5.506,0 -9.98823,4.47838 -9.99023,9.98438c-0.001,1.76 0.45998,3.47819 1.33398,4.99219l-1.35547,5.02344l5.23242,-1.23633c1.459,0.796 3.10144,1.21384 4.77344,1.21484h0.00391c5.505,0 9.98528,-4.47937 9.98828,-9.98437c0.002,-2.669 -1.03588,-5.17841 -2.92187,-7.06641c-1.886,-1.887 -4.39245,-2.92673 -7.06445,-2.92773zM12.00977,4c2.136,0.001 4.14334,0.8338 5.65234,2.3418c1.509,1.51 2.33794,3.51639 2.33594,5.65039c-0.002,4.404 -3.58423,7.98633 -7.99023,7.98633c-1.333,-0.001 -2.65341,-0.3357 -3.81641,-0.9707l-0.67383,-0.36719l-0.74414,0.17578l-1.96875,0.46484l0.48047,-1.78516l0.2168,-0.80078l-0.41406,-0.71875c-0.698,-1.208 -1.06741,-2.58919 -1.06641,-3.99219c0.002,-4.402 3.58528,-7.98437 7.98828,-7.98437zM8.47656,7.375c-0.167,0 -0.43702,0.0625 -0.66602,0.3125c-0.229,0.249 -0.875,0.85208 -0.875,2.08008c0,1.228 0.89453,2.41503 1.01953,2.58203c0.124,0.166 1.72667,2.76563 4.26367,3.76563c2.108,0.831 2.53614,0.667 2.99414,0.625c0.458,-0.041 1.47755,-0.60255 1.68555,-1.18555c0.208,-0.583 0.20848,-1.0845 0.14648,-1.1875c-0.062,-0.104 -0.22852,-0.16602 -0.47852,-0.29102c-0.249,-0.125 -1.47608,-0.72755 -1.70508,-0.81055c-0.229,-0.083 -0.3965,-0.125 -0.5625,0.125c-0.166,0.25 -0.64306,0.81056 -0.78906,0.97656c-0.146,0.167 -0.29102,0.18945 -0.54102,0.06445c-0.25,-0.126 -1.05381,-0.39024 -2.00781,-1.24024c-0.742,-0.661 -1.24267,-1.47656 -1.38867,-1.72656c-0.145,-0.249 -0.01367,-0.38577 0.11133,-0.50977c0.112,-0.112 0.24805,-0.2915 0.37305,-0.4375c0.124,-0.146 0.167,-0.25002 0.25,-0.41602c0.083,-0.166 0.04051,-0.3125 -0.02149,-0.4375c-0.062,-0.125 -0.54753,-1.35756 -0.76953,-1.85156c-0.187,-0.415 -0.3845,-0.42464 -0.5625,-0.43164c-0.145,-0.006 -0.31056,-0.00586 -0.47656,-0.00586z">
                                                                            </path>
                                                                        </g>
                                                                    </g>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stylish Footer -->
                                <div
                                    class="px-6 py-4 border-t border-black/5 dark:border-white/5 bg-gradient-to-b from-transparent to-gray-50 dark:to-slate-800/50">
                                    <div class="flex justify-end">
                                        <button x-on:click="isOpen = false"
                                            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700/50 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/5 hover:bg-gray-50 dark:hover:bg-slate-700 hover:shadow-md transition-all">
                                            <?php echo e(t('close')); ?>

                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/livewire/tenant/waba/disconnect-waba.blade.php ENDPATH**/ ?>