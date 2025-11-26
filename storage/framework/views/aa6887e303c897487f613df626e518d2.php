<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'column' => null,
    'enabledFilters' => null,
    'actions' => null,
    'dataField' => null,
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
    'column' => null,
    'enabledFilters' => null,
    'actions' => null,
    'dataField' => null,
    'theme' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
    $field = data_get($column, 'dataField', data_get($column, 'field'));

    $isFixedOnResponsive = false;

    if (isset($this->setUp['responsive'])) {
        if (in_array($field, data_get($this->setUp, 'responsive.fixedColumns'))) {
            $isFixedOnResponsive = true;
        }

        if (
            data_get($column, 'isAction') &&
            in_array(
                \PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive::ACTIONS_COLUMN_NAME,
                data_get($this->setUp, 'responsive.fixedColumns'),
            )
        ) {
            $isFixedOnResponsive = true;
        }

        if (data_get($column, 'fixedOnResponsive')) {
            $isFixedOnResponsive = true;
        }
    }

    $sortOrder = isset($this->setUp['responsive'])
        ? data_get($this->setUp, "responsive.sortOrder.{$field}", null)
        : null;
?>
<th x-data="{ sortable: <?php echo \Illuminate\Support\Js::from(data_get($column, 'sortable'))->toHtml() ?> }"
    data-column="<?php echo e(data_get($column, 'isAction') ? 'actions' : $field); ?>"
    <?php if($sortOrder): ?> sort_order="<?php echo e($sortOrder); ?>" <?php endif; ?>
    <?php if($isFixedOnResponsive): ?> fixed <?php endif; ?>
    <?php if(data_get($column, 'enableSort')): ?> x-multisort-shift-click="<?php echo e($this->getId()); ?>"
    wire:click="sortBy('<?php echo e($field); ?>')" <?php endif; ?>
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        theme_style($theme, 'table.header.th') => true,
        data_get($column, 'headerClass') => true,
    ]); ?>"
    style="<?php echo \Illuminate\Support\Arr::toCssStyles([
        'display:none' => data_get($column, 'hidden') === true,
        'cursor:pointer' => data_get($column, 'enableSort'),
        data_get($column, 'headerStyle') => filled(data_get($column, 'headerStyle')),
        'width: max-content !important',
    ]) ?>"
>
    <div class="<?php echo e(theme_style($theme, 'cols.div')); ?>">
        <span data-value><?php echo data_get($column, 'title'); ?></span>

        <!--[if BLOCK]><![endif]--><?php if(data_get($column, 'enableSort')): ?>
            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => ''.e($this->sortIcon($field)).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '16']); ?>
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
</th>
<?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/cols.blade.php ENDPATH**/ ?>