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
<?php if (isset($component)) { $__componentOriginal69a11001a40e4be7439e683d60cdafab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69a11001a40e4be7439e683d60cdafab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.chevron-up-down','data' => ['width' => $width]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.chevron-up-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($width)]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69a11001a40e4be7439e683d60cdafab)): ?>
<?php $attributes = $__attributesOriginal69a11001a40e4be7439e683d60cdafab; ?>
<?php unset($__attributesOriginal69a11001a40e4be7439e683d60cdafab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69a11001a40e4be7439e683d60cdafab)): ?>
<?php $component = $__componentOriginal69a11001a40e4be7439e683d60cdafab; ?>
<?php unset($__componentOriginal69a11001a40e4be7439e683d60cdafab); ?>
<?php endif; ?><?php /**PATH /home/adphwiudpl/mark.chatvoo.com/storage/framework/views/6da562a044c365ee08fb55b6325b90a1.blade.php ENDPATH**/ ?>