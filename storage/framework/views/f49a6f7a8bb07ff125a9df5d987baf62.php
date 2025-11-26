<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'status' => session('status'),
'error' => session('error'),
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
'status' => session('status'),
'error' => session('error'),
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($status): ?>
<div <?php echo e($attributes->merge(['class' => 'mb-4 text-sm bg-success-50 border-l-4 border-success-300 text-success-800 px-2
  py-3 rounded dark:bg-gray-800 dark:border-success-800 dark:text-success-300'])); ?>

  role="alert">
  <?php echo e($status); ?>

</div>
<?php endif; ?>

<?php if($error): ?>
<div <?php echo e($attributes->merge(['class' => 'mb-4 text-sm bg-danger-50 border-l-4 border-danger-300 text-danger-800 px-2 py-3
  rounded
  dark:bg-gray-800 dark:border-danger-800 dark:text-danger-300'])); ?>

  role="alert">
  <?php echo e($error); ?>

</div>
<?php endif; ?><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/components/auth-session-status.blade.php ENDPATH**/ ?>