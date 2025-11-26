<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false]));

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

foreach (array_filter((['disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<button <?php echo e($disabled ? 'disabled' : ''); ?> <?php echo e($attributes->merge(['type' => 'submit', 'class' => 'inline-flex
  items-center justify-center p-2 text-sm border border-transparent font-medium disabled:opacity-50
  disabled:pointer-events-none transition text-white rounded-full bg-primary-600 hover:bg-primary-500 focus:outline-none
  focus:ring-2 focus:ring-offset-2 focus:ring-info-500'])); ?>>
  <?php echo e($slot); ?>

</button><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/components/button/primary-round.blade.php ENDPATH**/ ?>