<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'columns' => null,
    'theme' => null,
    'tableName' => null,
    'filtersFromColumns' => null,
    'showFilters' => false,
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
    'columns' => null,
    'theme' => null,
    'tableName' => null,
    'filtersFromColumns' => null,
    'showFilters' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<div
    x-data="{ open: <?php if ((object) ('showFilters') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showFilters'->value()); ?>')<?php echo e('showFilters'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showFilters'); ?>')<?php endif; ?>.live }"
    class="mt-2 md:mt-0"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transform duration-100"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transform duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="pg-filter-container"
    >
        <?php
            $customConfig = [];

            $componentFilters = collect($this->filters());
            $filterOrderMap = $componentFilters->pluck('field')->flip();

            // Sort filters based on the order they appear in filters() method
            $sortedFilters = $filtersFromColumns->sortBy(function ($column) use ($filterOrderMap) {
                $fieldName = data_get($column, 'filters.field');
                return $filterOrderMap->get($fieldName, 999); // 999 for fields not found in filters()
            });
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6 gap-3">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sortedFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $filter = data_get($column, 'filters');
                    $title = data_get($column, 'title');
                    $baseClass = data_get($filter, 'baseClass');
                    $className = str(data_get($filter, 'className'));
                ?>

                <div class="<?php echo e($baseClass); ?>">
                    <!--[if BLOCK]><![endif]--><?php if($className->contains('FilterMultiSelect')): ?>
                        <?php if (isset($component)) { $__componentOriginal6b5037bea931bbd774061236a74bcc3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b5037bea931bbd774061236a74bcc3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.inputs.select','data' => ['inline' => false,'theme' => $theme,'tableName' => $tableName,'filter' => $filter,'title' => $title,'initialValues' => data_get(data_get($filter, 'multi_select'), data_get($filter, 'field'), [])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::inputs.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['inline' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($theme),'table-name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tableName),'filter' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filter),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'initial-values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(data_get(data_get($filter, 'multi_select'), data_get($filter, 'field'), []))]); ?>
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
                    <?php elseif($className->contains(['FilterDateTimePicker', 'FilterDatePicker'])): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterDatePicker.view'), [
                            'filter' => $filter,
                            'tableName' => $tableName,
                            'classAttr' => 'w-full',
                            'type' => $className->contains('FilterDateTimePicker') ? 'datetime' : 'date',
                        ])) echo $__env->make(theme_style($theme, 'filterDatePicker.view'), [
                            'filter' => $filter,
                            'tableName' => $tableName,
                            'classAttr' => 'w-full',
                            'type' => $className->contains('FilterDateTimePicker') ? 'datetime' : 'date',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($className->contains(['FilterSelect', 'FilterEnumSelect'])): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterSelect.view'), [
                            'filter' => $filter,
                        ])) echo $__env->make(theme_style($theme, 'filterSelect.view'), [
                            'filter' => $filter,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($className->contains('FilterNumber')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterNumber.view'), [
                            'filter' => $filter,
                        ])) echo $__env->make(theme_style($theme, 'filterNumber.view'), [
                            'filter' => $filter,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($className->contains('FilterInputText')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterInputText.view'), [
                            'filter' => $filter,
                        ])) echo $__env->make(theme_style($theme, 'filterInputText.view'), [
                            'filter' => $filter,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($className->contains('FilterBoolean')): ?>
                        <?php if ($__env->exists(theme_style($theme, 'filterBoolean.view'), [
                            'filter' => $filter,
                        ])) echo $__env->make(theme_style($theme, 'filterBoolean.view'), [
                            'filter' => $filter,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php elseif($className->contains('FilterDynamic')): ?>
                        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => data_get($filter, 'component', '')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => new \Illuminate\View\ComponentAttributeBag(data_get($filter, 'attributes', []))]); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/filter.blade.php ENDPATH**/ ?>