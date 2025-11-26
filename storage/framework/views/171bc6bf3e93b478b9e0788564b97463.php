<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'type' => 'primary',
'dismissable' => false,
'message' => '',
'title' => '',
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
'type' => 'primary',
'dismissable' => false,
'message' => '',
'title' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
// Light mode classes for each alert type
$alertClasses = [
'primary' => 'bg-info-100 border-info-500 text-info-700',
'info' => 'bg-teal-100 border-teal-500 text-teal-700',
'warning' => 'bg-warning-100 border-warning-500 text-warning-800',
'danger' => 'bg-danger-100 border-danger-500 text-danger-700',
'success' => 'bg-success-100 border-success-500 text-success-700',
];

// Dark mode classes for each alert type
$darkAlertClasses = [
'primary' => 'dark:bg-gray-700 dark:border-info-600 dark:text-white',
'info' => 'dark:bg-gray-700 dark:border-teal-300 dark:text-teal-300',
'warning' => 'dark:bg-gray-700 dark:border-warning-300 dark:text-warning-300',
'danger' => 'dark:bg-gray-700 dark:border-danger-300 dark:text-danger-300',
'success' => 'dark:bg-gray-700 dark:border-success-300 dark:text-success-300',
];

// Determine the classes to apply based on the alert type
$class = $alertClasses[$type] ?? $alertClasses['primary'];
$darkClass = $darkAlertClasses[$type] ?? $darkAlertClasses['primary'];
?>

<div <?php echo e($attributes->merge(['class' => "px-4 py-3 border-l-4 rounded-r-md z-100 flex $class $darkClass"])); ?>

  role="alert">
  <div class="flex flex-col">
    <!--[if BLOCK]><![endif]--><?php if($title): ?>
    <strong class="font-semibold text-sm mb-1"><?php echo e($title); ?></strong>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <span class="block sm:inline text-sm leading-6">
      <?php echo e($message ?: $slot); ?>

    </span>
  </div>
  <div class="flex items-center">
    <!--[if BLOCK]><![endif]--><?php if($dismissable): ?>
    <span class=" px-2 py-1 cursor-pointer" onclick="this.parentElement.style.display='none'">
      <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-x-mark'); ?>
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
    </span>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
  </div>
</div><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/components/dynamic-alert.blade.php ENDPATH**/ ?>