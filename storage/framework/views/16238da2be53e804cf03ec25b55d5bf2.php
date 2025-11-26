<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'checkbox' => null,
    'columns' => null,
    'actions' => null,
    'theme' => null,
    'enabledFilters' => null,
    'inputTextOptions' => [],
    'tableName' => null,
    'filters' => [],
    'setUp' => null,
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
    'checkbox' => null,
    'columns' => null,
    'actions' => null,
    'theme' => null,
    'enabledFilters' => null,
    'inputTextOptions' => [],
    'tableName' => null,
    'filters' => [],
    'setUp' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $trClasses = Arr::toCssClasses([theme_style($theme, 'table.body.tr'), theme_style($theme, 'table.body.trFilters')]);
    $tdClasses = Arr::toCssClasses([theme_style($theme, 'table.body.td'), theme_style($theme, 'table.body.tdFilters')]);
?>
<!--[if BLOCK]><![endif]--><?php if(config('livewire-powergrid.filter') === 'inline'): ?>
    <tr
        class="<?php echo e($trClasses); ?>"
    >

        <!--[if BLOCK]><![endif]--><?php if(data_get($setUp, 'detail.showCollapseIcon')): ?>
            <td
                class="<?php echo e($tdClasses); ?>"
            ></td>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if($checkbox): ?>
            <td
                class="<?php echo e($tdClasses); ?>"
            ></td>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $filterClass = str(data_get($column, 'filters.className'));
            ?>
            <td
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    theme_style($theme, 'table.body.td'),
                    theme_style($theme, 'table.body.tdFilters'),
                ]); ?>"
                wire:key="column-filter-<?php echo e(data_get($column, 'field')); ?>"
                style="<?php echo \Illuminate\Support\Arr::toCssStyles([
                    'display:none' => data_get($column, 'hidden') === true,
                ]) ?>"
            >
                <div wire:key="filter-<?php echo e(data_get($column, 'field')); ?>-<?php echo e($loop->index); ?>">
                    <!--[if BLOCK]><![endif]--><?php if($filterClass->contains('FilterMultiSelect')): ?>
                        <?php if (isset($component)) { $__componentOriginal6b5037bea931bbd774061236a74bcc3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b5037bea931bbd774061236a74bcc3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.inputs.select','data' => ['tableName' => $tableName,'theme' => $theme,'title' => data_get($column, 'title'),'filter' => (array) data_get($column, 'filters'),'initialValues' => data_get($filters, 'multi_select.' . data_get($column, 'dataField'))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::inputs.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['table-name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tableName),'theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($theme),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(data_get($column, 'title')),'filter' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((array) data_get($column, 'filters')),'initial-values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(data_get($filters, 'multi_select.' . data_get($column, 'dataField')))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b5037bea931bbd774061236a74bcc3d)): ?>
<?php $attributes = $__attributesOriginal6b5037bea931bbd774061236a74bcc3d; ?>
<?php unset($__attributesOriginal6b5037bea931bbd774061236a74bcc3d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b5037bea931bbd774061236a74bcc3d)): ?>
<?php $component = $__componentOriginal6b5037bea931bbd774061236a74bcc3d; ?>
<?php unset($__componentOriginal6b5037bea931bbd774061236a74bcc3d); ?>
<?php endif; ?>
                    <?php elseif($filterClass->contains(['FilterSelect', 'FilterEnumSelect'])): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterSelect.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ])) echo $__env->make(theme_style($theme, 'filterSelect.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($filterClass->contains('FilterInputText')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterInputText.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ])) echo $__env->make(theme_style($theme, 'filterInputText.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($filterClass->contains('FilterNumber')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterNumber.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ])) echo $__env->make(theme_style($theme, 'filterNumber.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($filterClass->contains('FilterDateTimePicker')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterDatePicker.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                            'type' => 'datetime',
                            'tableName' => $tableName,
                            'classAttr' => 'w-full',
                        ])) echo $__env->make(theme_style($theme, 'filterDatePicker.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                            'type' => 'datetime',
                            'tableName' => $tableName,
                            'classAttr' => 'w-full',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($filterClass->contains('FilterDatePicker')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterDatePicker.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                            'type' => 'date',
                            'classAttr' => 'w-full',
                        ])) echo $__env->make(theme_style($theme, 'filterDatePicker.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                            'type' => 'date',
                            'classAttr' => 'w-full',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($filterClass->contains('FilterBoolean')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterBoolean.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ])) echo $__env->make(theme_style($theme, 'filterBoolean.view'), [
                            'inline' => true,
                            'filter' => (array) data_get($column, 'filters'),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($filterClass->contains('FilterDynamic')): ?>
                        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => data_get($column, 'filters.component')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => new \Illuminate\View\ComponentAttributeBag(
                                data_get($column, 'filters.attributes', []),
                            )]); ?>
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
                </div>
            </td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if(isset($actions) && count($actions)): ?>
            <td colspan="<?php echo e(count($actions)); ?>"></td>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </tr>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/inline-filters.blade.php ENDPATH**/ ?>