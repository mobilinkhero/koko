<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => [], 'size' => 'normal', 'variant' => 'standard', 'width' => 'full']));

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

foreach (array_filter((['items' => [], 'size' => 'normal', 'variant' => 'standard', 'width' => 'full']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
$sizeClasses = [
    'compact' => 'px-2 py-1.5 text-xs',
    'normal' => 'px-3 py-2 text-sm',
    'large' => 'px-4 py-4 text-base'
];

$variantClasses = [
    'standard' => 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 shadow-sm',
    'compact' => 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600',
    'minimal' => 'bg-transparent border-transparent'
];

$widthClasses = [
    'full' => 'w-full',
    'container' => 'w-full max-w-7xl mx-auto',
    'content' => 'w-full max-w-6xl mx-auto',
    'table' => 'w-full max-w-full overflow-x-auto'
];

// Ensure we have valid string values
$size = $size ?? 'normal';
$variant = $variant ?? 'standard';
$width = $width ?? 'full';

$currentSizeClass = $sizeClasses[$size] ?? $sizeClasses['normal'];
$currentVariantClass = $variantClasses[$variant] ?? $variantClasses['standard'];
$currentWidthClass = $widthClasses[$width] ?? $widthClasses['full'];

// Responsive icon sizes
$iconSize = match($size) {
    'compact' => 'w-3 h-3 sm:w-3 sm:h-3',
    'large' => 'w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6',
    default => 'w-4 h-4 sm:w-4 sm:h-4'
};

// Responsive chevron sizes
$chevronSize = match($size) {
    'compact' => 'w-2 h-2 sm:w-2.5 sm:h-2.5',
    'large' => 'w-3 h-3 sm:w-4 sm:h-4',
    default => 'w-2.5 h-2.5 sm:w-3 sm:h-3'
};

// Responsive text sizes
$textSize = match($size) {
    'compact' => 'text-xs sm:text-xs',
    'large' => 'text-sm sm:text-base lg:text-lg',
    default => 'text-xs sm:text-sm'
};

// Responsive spacing
$spacing = match($size) {
    'compact' => 'space-x-0.5 sm:space-x-1',
    'large' => 'space-x-1 sm:space-x-2 lg:space-x-3',
    default => 'space-x-1 sm:space-x-1 md:space-x-2'
};

$marginBottom = $variant === 'compact' ? 'mb-3 sm:mb-4' : 'mb-4 sm:mb-6';
?>

<div class="<?php echo e($currentWidthClass); ?> <?php echo e($marginBottom); ?>">
    <nav aria-label="breadcrumb" class="w-full">
        <ol class="flex flex-wrap items-center <?php echo e($spacing); ?> rtl:space-x-reverse <?php echo e($currentVariantClass); ?> <?php echo e($currentSizeClass); ?> rounded-lg border">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <!--[if BLOCK]><![endif]--><?php if($loop->first): ?>
                    
                    <li class="inline-flex items-center min-w-0">
                        <!--[if BLOCK]><![endif]--><?php if(isset($item['route']) && $item['route']): ?>
                            <a href="<?php echo e($item['route']); ?>"
                               class="inline-flex items-center font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200 <?php echo e($textSize); ?> truncate">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-home'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => ''.e($iconSize).' me-1 sm:me-2 flex-shrink-0']); ?>
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
                                <span class="hidden sm:inline truncate"><?php echo e($item['label']); ?></span>
                                <span class="sm:hidden truncate"><?php echo e(Str::limit($item['label'], 8)); ?></span>
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center font-medium text-gray-700 dark:text-gray-400 <?php echo e($textSize); ?> truncate">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-home'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => ''.e($iconSize).' me-1 sm:me-2 flex-shrink-0']); ?>
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
                                <span class="hidden sm:inline truncate"><?php echo e($item['label']); ?></span>
                                <span class="sm:hidden truncate"><?php echo e(Str::limit($item['label'], 8)); ?></span>
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </li>
                <?php elseif($loop->last): ?>
                    
                    <li class="inline-flex items-center min-w-0">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rtl:rotate-180 '.e($chevronSize).' text-primary-400 mx-0.5 sm:mx-1 flex-shrink-0']); ?>
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
                        <span class="font-medium text-gray-500 dark:text-gray-400 <?php echo e($textSize); ?> truncate">
                            <span class="hidden sm:inline truncate"><?php echo e($item['label']); ?></span>
                            <span class="sm:hidden truncate"><?php echo e(Str::limit($item['label'], 12)); ?></span>
                        </span>
                    </li>
                <?php else: ?>
                    
                    <li class="inline-flex items-center min-w-0">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rtl:rotate-180 '.e($chevronSize).' text-primary-400 mx-0.5 sm:mx-1 flex-shrink-0']); ?>
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
                        <!--[if BLOCK]><![endif]--><?php if(isset($item['route']) && $item['route']): ?>
                            <a href="<?php echo e($item['route']); ?>"
                               class="font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200 <?php echo e($textSize); ?> truncate">
                                <span class="hidden sm:inline truncate"><?php echo e($item['label']); ?></span>
                                <span class="sm:hidden truncate"><?php echo e(Str::limit($item['label'], 10)); ?></span>
                            </a>
                        <?php else: ?>
                            <span class="font-medium text-gray-700 dark:text-gray-400 <?php echo e($textSize); ?> truncate">
                                <span class="hidden sm:inline truncate"><?php echo e($item['label']); ?></span>
                                <span class="sm:hidden truncate"><?php echo e(Str::limit($item['label'], 10)); ?></span>
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </li>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </ol>
    </nav>
</div>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/components/breadcrumb.blade.php ENDPATH**/ ?>