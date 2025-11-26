<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['width']));

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

foreach (array_filter((['width']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php if (isset($component)) { $__componentOriginal4a18472d0e12ecf3266d6d759f2c6a97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a18472d0e12ecf3266d6d759f2c6a97 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.chevron-up','data' => ['width' => $width]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($width)]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a18472d0e12ecf3266d6d759f2c6a97)): ?>
<?php $attributes = $__attributesOriginal4a18472d0e12ecf3266d6d759f2c6a97; ?>
<?php unset($__attributesOriginal4a18472d0e12ecf3266d6d759f2c6a97); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a18472d0e12ecf3266d6d759f2c6a97)): ?>
<?php $component = $__componentOriginal4a18472d0e12ecf3266d6d759f2c6a97; ?>
<?php unset($__componentOriginal4a18472d0e12ecf3266d6d759f2c6a97); ?>
<?php endif; ?><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/storage/framework/views/d4b7dbc43073a811ef3495a30210c5e2.blade.php ENDPATH**/ ?>