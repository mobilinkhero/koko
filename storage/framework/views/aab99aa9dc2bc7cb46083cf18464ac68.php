<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['collapsed' => false, 'menuitems' => [], 'setupMenuitems' => []]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['collapsed' => false, 'menuitems' => [], 'setupMenuitems' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php if (isset($component)) { $__componentOriginale071754038f7fbe79354a2e3135b7d68 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale071754038f7fbe79354a2e3135b7d68 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-layout','data' => ['collapsed' => $collapsed]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($collapsed)]); ?>
    
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $menuitems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!--[if BLOCK]><![endif]--><?php if($menuItem->type === 'item'): ?>
    <?php if (isset($component)) { $__componentOriginal785efd5f0afdfec196d246c935838b49 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal785efd5f0afdfec196d246c935838b49 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-navigation-item','data' => ['route' => $menuItem->route,'routeNames' => $menuItem->active_routes,'icon' => $menuItem->icon,'class' => 'w-5 h-5 mr-2','label' => t($menuItem->label),'tooltip' => t($menuItem->label),'badge' => $menuItem->badge,'permission' => $menuItem->permission,'collapsed' => $collapsed]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-navigation-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->route),'route-names' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->active_routes),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->icon),'class' => 'w-5 h-5 mr-2','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t($menuItem->label)),'tooltip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t($menuItem->label)),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->badge),'permission' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->permission),'collapsed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($collapsed)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal785efd5f0afdfec196d246c935838b49)): ?>
<?php $attributes = $__attributesOriginal785efd5f0afdfec196d246c935838b49; ?>
<?php unset($__attributesOriginal785efd5f0afdfec196d246c935838b49); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal785efd5f0afdfec196d246c935838b49)): ?>
<?php $component = $__componentOriginal785efd5f0afdfec196d246c935838b49; ?>
<?php unset($__componentOriginal785efd5f0afdfec196d246c935838b49); ?>
<?php endif; ?>
    <?php elseif($menuItem->type === 'section'): ?>
    <?php if (isset($component)) { $__componentOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-expandable-section','data' => ['title' => t($menuItem->label),'icon' => $menuItem->icon,'collapsed' => $collapsed,'sectionId' => $menuItem->section_id,'defaultExpanded' => $menuItem->default_expanded]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-expandable-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t($menuItem->label)),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->icon),'collapsed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($collapsed),'section-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->section_id),'default-expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItem->default_expanded)]); ?>

        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $menuItem->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal785efd5f0afdfec196d246c935838b49 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal785efd5f0afdfec196d246c935838b49 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-navigation-item','data' => ['route' => $childItem->route,'routeNames' => $childItem->active_routes,'icon' => $childItem->icon,'class' => 'w-5 h-5 mr-2','label' => t($childItem->label),'tooltip' => t($childItem->label),'badge' => $childItem->badge,'permission' => $childItem->permission,'collapsed' => $collapsed]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-navigation-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($childItem->route),'route-names' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($childItem->active_routes),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($childItem->icon),'class' => 'w-5 h-5 mr-2','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t($childItem->label)),'tooltip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t($childItem->label)),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($childItem->badge),'permission' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($childItem->permission),'collapsed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($collapsed)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal785efd5f0afdfec196d246c935838b49)): ?>
<?php $attributes = $__attributesOriginal785efd5f0afdfec196d246c935838b49; ?>
<?php unset($__attributesOriginal785efd5f0afdfec196d246c935838b49); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal785efd5f0afdfec196d246c935838b49)): ?>
<?php $component = $__componentOriginal785efd5f0afdfec196d246c935838b49; ?>
<?php unset($__componentOriginal785efd5f0afdfec196d246c935838b49); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d)): ?>
<?php $attributes = $__attributesOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d; ?>
<?php unset($__attributesOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d)): ?>
<?php $component = $__componentOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d; ?>
<?php unset($__componentOriginale0cc8b150cf8b9f40a7a3ebbb733cf3d); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

    
    <button x-on:click.prevent="setupMenu = true"
        class="group items-center hidden lg:flex px-4 py-2 text-sm font-medium rounded-r-md text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white mt-2 w-full">
        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-cog-6-tooth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data-tippy-content' => ''.e(t('setup')).'','data-tippy-placement' => 'right','class' => 'mr-4 flex-shrink-0 h-6 w-6 text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300','aria-hidden' => 'true']); ?>
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
        <span class="whitespace-nowrap" x-show="!isCollapsed" x-transition:enter.duration.700ms><?php echo e(t('setup')); ?></span>
    </button>

    
    <button x-on:click.prevent="mobileOpen = true"
        class="group lg:hidden flex items-center px-4 py-2 text-sm font-medium rounded-r-md text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white mt-2 w-full">
        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-cog-6-tooth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data-tippy-content' => ''.e(t('setup')).'','data-tippy-placement' => 'right','class' => 'mr-4 flex-shrink-0 h-6 w-6 text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300','aria-hidden' => 'true']); ?>
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
        <span class="whitespace-nowrap" x-show="!isCollapsed" x-transition:enter.duration.700ms><?php echo e(t('setup')); ?></span>
    </button>

    
    <?php $__env->slot('mobileSetupMenu'); ?>
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $setupMenuitems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setupItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (isset($component)) { $__componentOriginal785efd5f0afdfec196d246c935838b49 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal785efd5f0afdfec196d246c935838b49 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-navigation-item','data' => ['route' => $setupItem->route,'routeNames' => $setupItem->active_routes,'icon' => $setupItem->icon,'class' => 'w-5 h-5 mr-2','label' => t($setupItem->label),'permission' => $setupItem->permission]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-navigation-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setupItem->route),'route-names' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setupItem->active_routes),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setupItem->icon),'class' => 'w-5 h-5 mr-2','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(t($setupItem->label)),'permission' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setupItem->permission)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal785efd5f0afdfec196d246c935838b49)): ?>
<?php $attributes = $__attributesOriginal785efd5f0afdfec196d246c935838b49; ?>
<?php unset($__attributesOriginal785efd5f0afdfec196d246c935838b49); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal785efd5f0afdfec196d246c935838b49)): ?>
<?php $component = $__componentOriginal785efd5f0afdfec196d246c935838b49; ?>
<?php unset($__componentOriginal785efd5f0afdfec196d246c935838b49); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale071754038f7fbe79354a2e3135b7d68)): ?>
<?php $attributes = $__attributesOriginale071754038f7fbe79354a2e3135b7d68; ?>
<?php unset($__attributesOriginale071754038f7fbe79354a2e3135b7d68); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale071754038f7fbe79354a2e3135b7d68)): ?>
<?php $component = $__componentOriginale071754038f7fbe79354a2e3135b7d68; ?>
<?php unset($__componentOriginale071754038f7fbe79354a2e3135b7d68); ?>
<?php endif; ?><?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/components/admin/sidebar-navigation.blade.php ENDPATH**/ ?>