<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['id' => null, 'name' => null, 'value' => false]));

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

foreach (array_filter((['id' => null, 'name' => null, 'value' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div x-data="{ isOn: <?php echo e($value ? 'true' : 'false'); ?> }">
    <label class="relative inline-flex items-center cursor-pointer mt-2 group">
        <input type="checkbox" x-model="isOn" <?php if($id): ?> id="<?php echo e($id); ?>" <?php endif; ?> <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
            class="sr-only peer" @change="$dispatch('toggle-changed', { value: isOn })" <?php echo e($attributes); ?>>
        <div class="w-11 h-6 bg-gray-200 rounded-full peer transition-all duration-300 ease-in-out
            peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 peer-focus:ring-opacity-50
            dark:peer-focus:ring-primary-800 dark:bg-gray-700 dark:border-gray-600
            peer-checked:after:translate-x-full peer-checked:after:border-white 
            after:content-[''] after:absolute after:top-0.5 after:left-[2px] 
            after:bg-white after:border-gray-300 after:border after:rounded-full 
            after:h-5 after:w-5 after:transition-all after:duration-300 after:ease-in-out
            after:shadow-md hover:after:shadow-lg
            peer-checked:bg-primary-600 peer-checked:shadow-lg
            hover:bg-gray-300 dark:hover:bg-gray-600
            peer-checked:hover:bg-primary-700
            group-hover:scale-105 transform transition-transform duration-200">
        </div>
    </label>
</div><?php /**PATH /home/qrpayuco/whatsapp.qrpayu.com/resources/views/components/toggle.blade.php ENDPATH**/ ?>