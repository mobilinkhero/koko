<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'readyToLoad' => false,
    'items' => null,
    'lazy' => false,
    'tableName' => null,
    'theme' => null,
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
    'readyToLoad' => false,
    'items' => null,
    'lazy' => false,
    'tableName' => null,
    'theme' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<div <?php if(isset($this->setUp['responsive'])): ?> x-data="pgResponsive" <?php endif; ?>>
    <div x-data="{ expandedId: null }">
        <table
            id="table_base_<?php echo e($tableName); ?>"
            class="table power-grid-table <?php echo e(theme_style($theme, 'table.layout.table')); ?>"
        >
            <thead
                class="<?php echo e(theme_style($theme, 'table.header.thead')); ?>"
            >
                <?php echo e($header); ?>

            </thead>
            <!--[if BLOCK]><![endif]--><?php if($readyToLoad): ?>
                <tbody
                    class="<?php echo e(theme_style($theme, 'table.body.tbody')); ?>"
                >
                    <?php echo e($body); ?>

                </tbody>
            <?php else: ?>
                <tbody
                    class="<?php echo e(theme_style($theme, 'table.body.tbody')); ?>"
                >
                    <?php echo e($loading); ?>

                </tbody>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </table>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($this->canLoadMore && $lazy): ?>
        <div class="justify-center items-center" wire:loading.class="flex" wire:target="loadMore">
            <?php echo $__env->make(data_get($theme, 'root') . '.header.loading', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div x-data="pgLoadMore"></div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/table-base.blade.php ENDPATH**/ ?>