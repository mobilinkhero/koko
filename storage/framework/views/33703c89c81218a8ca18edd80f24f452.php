<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'loading' => false,
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
    'loading' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<tr
    class="<?php echo e(theme_style($theme, 'table.header.tr')); ?>"
>
    <!--[if BLOCK]><![endif]--><?php if($loading): ?>
        <td
            class="<?php echo e(theme_style($theme, 'table.body.tbodyEmpty')); ?>"
            colspan="999"
        >
            <!--[if BLOCK]><![endif]--><?php if($loadingComponent): ?>
                <?php echo $__env->make($loadingComponent, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <?php echo e(__('Loading')); ?>

            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </td>
    <?php else: ?>
        <!--[if BLOCK]><![endif]--><?php if(data_get($setUp, 'detail.showCollapseIcon')): ?>
            <th
                scope="col"
                class="<?php echo e(theme_style($theme, 'table.header.th')); ?>"
                wire:key="show-collapse-<?php echo e($tableName); ?>"
            >
            </th>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if(isset($setUp['responsive'])): ?>
            <th
                fixed
                x-show="hasHiddenElements"
                class="<?php echo e(theme_style($theme, 'table.header.th')); ?>"
            >
            </th>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($radio): ?>
            <th
                class="<?php echo e(theme_style($theme, 'table.header.th')); ?>"
            >
            </th>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($checkbox): ?>
            <?php echo $__env->make('livewire-powergrid::components.checkbox-all', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal577dc6db6f201a17d3cae4747c63c7eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal577dc6db6f201a17d3cae4747c63c7eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.cols','data' => ['wire:key' => 'cols-'.e(data_get($column, 'field')).' }}','column' => $column,'theme' => $theme,'enabledFilters' => $enabledFilters]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::cols'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => 'cols-'.e(data_get($column, 'field')).' }}','column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($column),'theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($theme),'enabledFilters' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enabledFilters)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal577dc6db6f201a17d3cae4747c63c7eb)): ?>
<?php $attributes = $__attributesOriginal577dc6db6f201a17d3cae4747c63c7eb; ?>
<?php unset($__attributesOriginal577dc6db6f201a17d3cae4747c63c7eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal577dc6db6f201a17d3cae4747c63c7eb)): ?>
<?php $component = $__componentOriginal577dc6db6f201a17d3cae4747c63c7eb; ?>
<?php unset($__componentOriginal577dc6db6f201a17d3cae4747c63c7eb); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if(isset($actions) && count($actions)): ?>
            <?php
                $responsiveActionsColumnName =
                    \PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive::ACTIONS_COLUMN_NAME;

                $isActionFixedOnResponsive =
                    isset($this->setUp['responsive']) &&
                    in_array($responsiveActionsColumnName, data_get($this->setUp, 'responsive.fixedColumns'))
                        ? true
                        : false;
            ?>

            <th
                <?php if($isActionFixedOnResponsive): ?> fixed <?php endif; ?>
                class="<?php echo e(theme_style($theme, 'table.header.th') . ' ' . theme_style($theme, 'table.header.thAction')); ?>"
                scope="col"
                colspan="999"
                wire:key="<?php echo e(md5('actions')); ?>"
            >
                <?php echo e(trans('livewire-powergrid::datatable.labels.action')); ?>

            </th>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</tr>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/table/tr.blade.php ENDPATH**/ ?>