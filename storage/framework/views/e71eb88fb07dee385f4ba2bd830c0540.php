

<?php
    $favicon = !empty($themeSettings['theme.favicon'])
        ? Storage::url($themeSettings['theme.favicon'])
        : url('./img/favicon-32x32.png');

    $siteLogo = !empty($themeSettings['theme.site_logo'])
        ? Storage::url($themeSettings['theme.site_logo'])
        : asset('img/light_logo.png');

    $darkLogo = !empty($themeSettings['theme.dark_logo'])
        ? Storage::url($themeSettings['theme.dark_logo'])
        : asset('img/dark_logo.png');
?>

<div style="color-scheme: dark;">
    
    <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
    <div x-cloak x-show="open" class="relative z-40 lg:hidden" role="dialog" aria-modal="true" x-data="{ mobileOpen: <?php echo e($this->shouldShowSetupMenu() ? 'true' : 'false'); ?> }">
        <div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-on:click="open = false"
            class="fixed inset-0 bg-slate-600 bg-opacity-75"></div>

        <div class="fixed inset-0 flex z-40">
            <!-- Mobile Menu (Overlapping Open Menu) -->
            <div x-show="mobileOpen"
                class="absolute top-0 left-0 z-50 lg:hidden sm:w-80 w-60 h-full bg-white dark:bg-slate-800 shadow-lg"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform -translate-x-full">

                <!-- Close Button -->
                <div class="flex justify-between items-center py-4 flex-shrink-0 px-5 bg-white dark:bg-slate-800">
                    <span class="text-lg font-semibold text-gray-600 dark:text-slate-300">
                        <?php echo e(t('setup')); ?>

                    </span>
                    <button x-on:click.stop="mobileOpen = false" class="text-gray-500 dark:text-slate-400">
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
                    </button>
                </div>

                <div class="flex-1 flex flex-col overflow-y-auto">
                    <nav class="flex-1 px-2">
                        
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $setupMenuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setupItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <!--[if BLOCK]><![endif]--><?php if(checkPermission($setupItem->permission)): ?>
                                <!--[if BLOCK]><![endif]--><?php if($setupItem->feature_required || in_array($setupItem->feature_required, $features)): ?>
                                    <?php if (feature($setupItem->feature_required)): ?>
                                        <a href="<?php echo e(tenant_route($setupItem->route)); ?>"
                                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                                <?php echo e($this->isActiveRoute($setupItem->active_routes)
                                                    ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($setupItem->icon): ?>
                                                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($setupItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                        '.e($this->isActiveRoute($setupItem->active_routes)
                                                            ? 'text-primary-600 dark:text-slate-300'
                                                            : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php echo e(t($setupItem->label)); ?>

                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo e(tenant_route($setupItem->route)); ?>"
                                        class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                            <?php echo e($this->isActiveRoute($setupItem->active_routes)
                                                ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                        <!--[if BLOCK]><![endif]--><?php if($setupItem->icon): ?>
                                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($setupItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                    '.e($this->isActiveRoute($setupItem->active_routes)
                                                        ? 'text-primary-600 dark:text-slate-300'
                                                        : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php echo e(t($setupItem->label)); ?>

                                    </a>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </nav>
                </div>
            </div>

            <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                x-on:click.away="open = false" class="relative flex flex-col pt-5 bg-white dark:bg-slate-800">
                <div x-show="open" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="absolute top-0 right-0 -mr-12 pt-2">
                </div>

                <div class="flex-shrink-0 flex items-center justify-center w-full">
                    <a href="<?php echo e(tenant_route('tenant.dashboard')); ?>" class="flex items-center bg-white dark:bg-slate-800">
                        <img x-bind:src="theme === 'light' || (theme === 'system' && window.matchMedia(
                                    '(prefers-color-scheme: light)')
                                .matches) ?
                            '<?php echo e($siteLogo); ?>' :
                            '<?php echo e($darkLogo); ?>'"
                            alt="#" class="md:h-12 h-8 sm:h-12 px-4 w-auto object-cover" x-cloak>
                    </a>
                </div>
                <div class="mt-5 flex-1 h-0 overflow-y-auto">
                    <nav class="px-2 py-4">
                        
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <!--[if BLOCK]><![endif]--><?php if($menuItem->type === 'item'  && checkPermission($menuItem->permission)): ?>
                                 <!--[if BLOCK]><![endif]--><?php if($menuItem->feature_required || in_array($menuItem->feature_required, $features)): ?>
                                    <?php if (feature($menuItem->feature_required)): ?>
                                        <a href="<?php echo e(tenant_route($menuItem->route)); ?>"
                                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                                <?php echo e($this->isActiveRoute($menuItem->active_routes)
                                                    ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($menuItem->icon): ?>
                                                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($menuItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                        '.e($this->isActiveRoute($menuItem->active_routes)
                                                            ? 'text-primary-600 dark:text-slate-300'
                                                            : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php echo e(t($menuItem->label)); ?>

                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo e(tenant_route($menuItem->route)); ?>"
                                        class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                            <?php echo e($this->isActiveRoute($menuItem->active_routes)
                                                ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                        <!--[if BLOCK]><![endif]--><?php if($menuItem->icon): ?>
                                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($menuItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                    '.e($this->isActiveRoute($menuItem->active_routes)
                                                        ? 'text-primary-600 dark:text-slate-300'
                                                        : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php echo e(t($menuItem->label)); ?>

                                    </a>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php elseif($menuItem->type === 'section' && $menuItem->children->isNotEmpty()): ?>
                                
                                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium px-5 py-4">
                                    <?php echo e(t($menuItem->label)); ?>

                                </p>
                                
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $menuItem->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <!--[if BLOCK]><![endif]--><?php if($childItem->feature_required || in_array($childItem->key, $features)): ?>
                                        <?php if (feature($childItem->feature_required)): ?>
                                            <a href="<?php echo e(tenant_route($childItem->route)); ?>"
                                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                                <?php echo e($this->isActiveRoute($childItem->active_routes)
                                                    ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($childItem->icon): ?>
                                                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($childItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                        '.e($this->isActiveRoute($childItem->active_routes)
                                                            ? 'text-primary-600 dark:text-slate-300'
                                                            : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php echo e(t($childItem->label)); ?>

                                        </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo e(tenant_route($childItem->route)); ?>"
                                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                                <?php echo e($this->isActiveRoute($childItem->active_routes)
                                                    ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($childItem->icon): ?>
                                                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($childItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                        '.e($this->isActiveRoute($childItem->active_routes)
                                                            ? 'text-primary-600 dark:text-slate-300'
                                                            : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php echo e(t($childItem->label)); ?>

                                        </a>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                        <button x-on:click.prevent="mobileOpen = true"
                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white mt-2 w-full">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-cog-6-tooth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-4 flex-shrink-0 h-6 w-6 text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300','aria-hidden' => 'true']); ?>
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
                            <?php echo e(t('setup')); ?>

                        </button>
                    </nav>
                </div>
            </div>
            <div class="flex-shrink-0 w-14" aria-hidden="true">
                <!-- Dummy element to force sidebar to shrink to fit close icon -->
            </div>
        </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:flex lg:fixed lg:inset-y-0 z-40 transition-[width] duration-300 ease-in-out bg-white dark:bg-slate-800 border-r border-slate-300 dark:border-slate-600"
        x-data="{
            isCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            setupMenu: <?php echo e($this->shouldShowSetupMenu() ? 'true' : 'false'); ?>

        }" x-init="$watch('isCollapsed', value => {
            localStorage.setItem('sidebarCollapsed', value);
            window.dispatchEvent(new CustomEvent('sidebar-state-changed', {
                detail: { collapsed: value }
            }));
        });" :class="isCollapsed ? 'lg:w-[75px]' : 'lg:w-[240px]'">

        <div class="flex-1 flex flex-col min-h-0 border-r border-slate-300 dark:border-r dark:border-slate-600 relative"
            :class="isCollapsed ? 'w-0' : 'lg:w-[240px]'">

            
            <div x-show="setupMenu" x-cloak class="hidden lg:flex lg:fixed lg:inset-y-0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform -translate-x-full">
                <div
                    :class="(isCollapsed ? 'w-[75px]' : 'w-[15rem]') +
                    ' flex flex-col min-h-0 border-r border-slate-300 dark:border-slate-600 transition-all ease-in-out duration-300'">
                    <!-- Top bar with Close button -->
                    <div class="flex justify-between items-center py-4 flex-shrink-0 px-5 bg-white dark:bg-slate-800">
                        <span x-show="!isCollapsed" class="text-lg font-semibold text-gray-600 dark:text-slate-300">
                            <?php echo e(t('setup')); ?>

                        </span>
                        <button :class="isCollapsed ? 'pl-[8px]' : 'pl-[0px]'" x-on:click="setupMenu = false"
                            class="text-gray-500 dark:text-slate-400">
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
                        </button>
                    </div>

                    <div class="flex-1 flex flex-col overflow-y-auto bg-white dark:bg-slate-800">
                        <nav class="flex-1 px-2">
                            
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $setupMenuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setupItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <!--[if BLOCK]><![endif]--><?php if(checkPermission($setupItem->permission)): ?>
                                    <!--[if BLOCK]><![endif]--><?php if($setupItem->feature_required || in_array($setupItem->feature_required, $features)): ?>
                                        <?php if (feature($setupItem->feature_required)): ?>
                                            <a href="<?php echo e(tenant_route($setupItem->route)); ?>"
                                                class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                                    <?php echo e($this->isActiveRoute($setupItem->active_routes)
                                                        ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                        : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                                <!--[if BLOCK]><![endif]--><?php if($setupItem->icon): ?>
                                                    <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($setupItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data-tippy-content' => ''.e(t($setupItem->label)).'','data-tippy-placement' => 'right','class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                            '.e($this->isActiveRoute($setupItem->active_routes)
                                                                ? 'text-primary-600 dark:text-slate-300'
                                                                : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <span x-show="!isCollapsed"><?php echo e(t($setupItem->label)); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo e(tenant_route($setupItem->route)); ?>"
                                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                                <?php echo e($this->isActiveRoute($setupItem->active_routes)
                                                    ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($setupItem->icon): ?>
                                                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($setupItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data-tippy-content' => ''.e(t($setupItem->label)).'','data-tippy-placement' => 'right','class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                        '.e($this->isActiveRoute($setupItem->active_routes)
                                                            ? 'text-primary-600 dark:text-slate-300'
                                                            : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <span x-show="!isCollapsed"><?php echo e(t($setupItem->label)); ?></span>
                                        </a>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </nav>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-center transition-all duration-300 ease-in-out">
                <a href="<?php echo e(tenant_route('tenant.dashboard')); ?>" class="flex items-center bg-white dark:bg-slate-800 pt-2">
                    <img x-show="!isCollapsed"
                        x-bind:src="theme === 'light' || (theme === 'system' && window.matchMedia(
                                    '(prefers-color-scheme: light)')
                                .matches) ?
                            '<?php echo e($siteLogo); ?>' :
                            '<?php echo e($darkLogo); ?>'"
                        alt="#" class="h-14 my-1 object-contain" x-cloak>

                    <img x-show="isCollapsed" x-cloak
                        x-bind:src="theme === 'light' || (theme === 'system' && window.matchMedia(
                                    '(prefers-color-scheme: light)')
                                .matches) ?
                            '<?php echo e($favicon); ?>' :
                            '<?php echo e($favicon); ?>'"
                        alt="Logo" class="h-12 object-contain">
                </a>
            </div>

            
            <div x-show="!setupMenu" class="absolute right-[-16px] top-4 transition-all duration-300 ease-in-out">
                <button @click="isCollapsed = !isCollapsed"
                    class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-slate-700 shadow-md border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-white focus:outline-none transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-105">
                    <template x-if="!isCollapsed">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-arrow-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 transition-transform duration-300 ease-in-out']); ?>
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
                    </template>
                    <template x-if="isCollapsed">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-arrow-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 transition-transform duration-300 ease-in-out']); ?>
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
                    </template>
                </button>
            </div>

            
            <div class="flex-1 flex flex-col overflow-y-auto scrollbar-visible bg-white dark:bg-slate-800">
                <nav class="flex-1 py-4 " :class="isCollapsed ? 'px-1' : 'px-2'">
                    
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <!--[if BLOCK]><![endif]--><?php if($menuItem->type === 'item' && checkPermission($menuItem->permission)): ?>
                            
                            <!--[if BLOCK]><![endif]--><?php if($menuItem->feature_required || in_array($menuItem->feature_required, $features)): ?>
                                <?php if (feature($menuItem->feature_required)): ?>
                                    <a href="<?php echo e(tenant_route($menuItem->route)); ?>"
                                        class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                    <?php echo e($this->isActiveRoute($menuItem->active_routes)
                                        ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                        : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                        <!--[if BLOCK]><![endif]--><?php if($menuItem->icon): ?>
                                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($menuItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:data-tippy-content' => 'isCollapsed ? \''.e(t($menuItem->label)).'\' : null','x-bind:data-tippy-placement' => 'isCollapsed ? \'right\' : null','class' => 'mr-4 flex-shrink-0 h-6 w-6
                                            '.e($this->isActiveRoute($menuItem->active_routes)
                                                ? 'text-primary-600 dark:text-slate-300'
                                                : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <span x-show="!isCollapsed" x-transition:enter.duration.700ms
                                            class="whitespace-nowrap">
                                            <?php echo e(t($menuItem->label)); ?>

                                        </span>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo e(tenant_route($menuItem->route)); ?>"
                                    class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                    <?php echo e($this->isActiveRoute($menuItem->active_routes)
                                        ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                        : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                    <!--[if BLOCK]><![endif]--><?php if($menuItem->icon): ?>
                                        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($menuItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:data-tippy-content' => 'isCollapsed ? \''.e(t($menuItem->label)).'\' : null','x-bind:data-tippy-placement' => 'isCollapsed ? \'right\' : null','class' => 'mr-4 flex-shrink-0 h-6 w-6
                                            '.e($this->isActiveRoute($menuItem->active_routes)
                                                ? 'text-primary-600 dark:text-slate-300'
                                                : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <span x-show="!isCollapsed" x-transition:enter.duration.700ms
                                        class="whitespace-nowrap">
                                        <?php echo e(t($menuItem->label)); ?>

                                    </span>
                                </a>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php elseif($menuItem->type === 'section' && $menuItem->children->isNotEmpty() && checkPermission($menuItem->permission)): ?>
                            
                            <div x-show="!isCollapsed" x-transition:enter.duration.700ms
                                class="text-sm text-gray-500 dark:text-slate-400 font-medium px-5 py-4">
                                <?php echo e(t($menuItem->label)); ?>

                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $menuItem->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <!--[if BLOCK]><![endif]--><?php if($childItem->feature_required || in_array($childItem->key, $features)): ?>
                                    <?php if (feature($childItem->feature_required)): ?>
                                        <a href="<?php echo e(tenant_route($childItem->route)); ?>"
                                            class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                            <?php echo e($this->isActiveRoute($childItem->active_routes)
                                                ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                                : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($childItem->icon): ?>
                                                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($childItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:data-tippy-content' => 'isCollapsed ? \''.e(t($childItem->label)).'\' : null','x-bind:data-tippy-placement' => 'isCollapsed ? \'right\' : null','class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                    '.e($this->isActiveRoute($childItem->active_routes)
                                                        ? 'text-primary-600 dark:text-slate-300'
                                                        : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <span x-show="!isCollapsed" x-transition:enter.duration.700ms
                                                class="whitespace-nowrap">
                                                <?php echo e(t($childItem->label)); ?>

                                            </span>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo e(tenant_route($childItem->route)); ?>"
                                        class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md
                                        <?php echo e($this->isActiveRoute($childItem->active_routes)
                                            ? 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                                            : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white'); ?>">
                                        <!--[if BLOCK]><![endif]--><?php if($childItem->icon): ?>
                                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($childItem->icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:data-tippy-content' => 'isCollapsed ? \''.e(t($childItem->label)).'\' : null','x-bind:data-tippy-placement' => 'isCollapsed ? \'right\' : null','class' => 'mr-4 flex-shrink-0 h-6 w-6
                                                '.e($this->isActiveRoute($childItem->active_routes)
                                                    ? 'text-primary-600 dark:text-slate-300'
                                                    : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300').'','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <span x-show="!isCollapsed" x-transition:enter.duration.700ms
                                            class="whitespace-nowrap">
                                            <?php echo e(t($childItem->label)); ?>

                                        </span>
                                    </a>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <button x-on:click.prevent="setupMenu = true"
                        class="group flex items-center px-4 py-2 text-sm font-medium rounded-r-md text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white mt-2 w-full">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-cog-6-tooth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:data-tippy-content' => 'isCollapsed ? \''.e(t('setup')).'\' : null','x-bind:data-tippy-placement' => 'isCollapsed ? \'right\' : null','class' => 'mr-4 flex-shrink-0 h-6 w-6 text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300','aria-hidden' => 'true']); ?>
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
                        <span x-show="!isCollapsed" x-transition:enter.duration.700ms class="whitespace-nowrap">
                            <?php echo e(t('setup')); ?>

                        </span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/tenant/partials/tenant-sidebar-navigation.blade.php ENDPATH**/ ?>