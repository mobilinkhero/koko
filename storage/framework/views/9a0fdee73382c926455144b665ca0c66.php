<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'route' => null,
'routeNames' => [],
'icon' => null,
'label' => null,
'tooltip' => null,
'badge' => null,
'permission' => null,
'isActive' => false,
'collapsed' => false,
'class' => '',
]));

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

foreach (array_filter(([
'route' => null,
'routeNames' => [],
'icon' => null,
'label' => null,
'tooltip' => null,
'badge' => null,
'permission' => null,
'isActive' => false,
'collapsed' => false,
'class' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
// Handle both single route and multiple route names
$routeNames = is_array($routeNames) ? $routeNames : (is_string($routeNames) ? [$routeNames] : []);
if ($route) {
$routeNames[] = $route;
}

// Check if current route is active
$isCurrentlyActive = $isActive || (!empty($routeNames) && request()->routeIs($routeNames));

// Define CSS classes
$baseClasses = 'group flex items-center px-4 py-2 text-sm font-medium rounded-r-md transition-colors duration-200';
$activeClasses = 'border-l-4 border-primary-600 bg-primary-50 dark:border-primary-600 text-primary-700 dark:bg-slate-900
dark:text-white';
$inactiveClasses = 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300
dark:hover:bg-slate-700
dark:hover:text-white';

$iconActiveClasses = 'text-primary-600 dark:text-slate-300';
$iconInactiveClasses = 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300';

$linkClasses = $baseClasses . ' ' . ($isCurrentlyActive ? $activeClasses : $inactiveClasses);
$iconClasses = 'mr-4 flex-shrink-0 h-6 w-6 ' . ($isCurrentlyActive ? $iconActiveClasses : $iconInactiveClasses);
?>

<!--[if BLOCK]><![endif]--><?php if(!$permission || checkPermission($permission)): ?>
<a wire:navigate href="<?php echo e($route ? route($route) : '#'); ?>" class="<?php echo e($linkClasses); ?>" <?php if($tooltip && $collapsed): ?>
    data-tippy-content="<?php echo e($tooltip); ?>" data-tippy-placement="right" <?php endif; ?>>

    <!--[if BLOCK]><![endif]--><?php if($icon && !empty($icon)): ?>
    <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($icon).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => ''.e($class).'','aria-hidden' => 'true']); ?>
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

    <span class="whitespace-nowrap" x-show="!isCollapsed" x-transition:enter.duration.700ms>
        <?php echo e($label); ?>

    </span>

    <!--[if BLOCK]><![endif]--><?php if($badge): ?>
    <span
        class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-primary-500 bg-primary-100 dark:bg-primary-700 rounded-full">
        <?php echo e($badge); ?>

    </span>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</a>
<?php endif; ?><!--[if ENDBLOCK]><![endif]--><?php /**PATH /home/qrpayuco/whatsapp.qrpayu.com/resources/views/components/admin/sidebar-navigation-item.blade.php ENDPATH**/ ?>