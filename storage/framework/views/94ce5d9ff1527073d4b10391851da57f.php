<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'enabledFilters' => [],
    'column' => null,
    'inline' => null,
    'filter' => null,
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
    'enabledFilters' => [],
    'column' => null,
    'inline' => null,
    'filter' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<div>
    <?php
        $fieldClassName = data_get($filter, 'className');

        $field = strval(data_get($filter, 'field'));
        $title = strval(data_get($column, 'title'));
        $operators = (array) data_get($filter, 'operators', []);
        $placeholder = strval(data_get($filter, 'placeholder'));
        $componentAttributes = (array) data_get($filter, 'attributes', []);

        $inputTextOptions = $fieldClassName::getInputTextOperators();
        $inputTextOptions = count($operators) > 0 ? $operators : $inputTextOptions;
        $showSelectOptions = !(count($inputTextOptions) === 1 && in_array('contains', $inputTextOptions));

        $defaultPlaceholder = data_get($column, 'placeholder') ?: data_get($column, 'title');
        $overridePlaceholder = $placeholder ?: $defaultPlaceholder;

        unset($filter['placeholder']);

        $defaultAttributes = $fieldClassName::getWireAttributes($field, $title);

        $selectClasses = theme_style($theme, 'filterInputText.select');
        $inputClasses = theme_style($theme, 'filterInputText.input');

        $params = array_merge(
            [
                'showSelectOptions' => $showSelectOptions,
                'placeholder' => ($placeholder = $componentAttributes['placeholder'] ?? $overridePlaceholder),
                ...data_get($filter, 'attributes'),
                ...$defaultAttributes,
            ],
            $filter,
        );
    ?>

    <!--[if BLOCK]><![endif]--><?php if($params['component']): ?>
        <?php unset($params['operators'], $params['attributes']); ?>

        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $params['component']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => new \Illuminate\View\ComponentAttributeBag($params)]); ?>
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
    <?php else: ?>
        <div
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([theme_style($theme, 'filterInputText.base'), 'space-y-1' => !$inline]); ?>"
        >
            <!--[if BLOCK]><![endif]--><?php if(!$inline): ?>
                <label class="block text-sm font-semibold text-pg-primary-700 dark:text-pg-primary-300">
                    <?php echo e($title); ?>

                </label>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'w-full space-y-2 sm:flex sm:space-y-0' => !$inline && $showSelectOptions,
                'flex flex-col space-y-1.5' => $inline && $showSelectOptions,
            ]); ?>">
                <!--[if BLOCK]><![endif]--><?php if($showSelectOptions): ?>
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'pl-0 w-full sm:pr-3 sm:w-1/2' => !$inline,
                    ]); ?>">
                        <div class="relative">
                            <select
                                class="<?php echo e($selectClasses); ?>"
                                style="<?php echo e(data_get($column, 'headerStyle')); ?>"
                                data-cy="input_text_options_<?php echo e($tableName); ?>_<?php echo e($field); ?>"
                                <?php echo e($defaultAttributes['selectAttributes']); ?>

                            >
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $inputTextOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        wire:key="input-text-options-<?php echo e($tableName); ?>-<?php echo e($key . '-' . $value); ?>"
                                        value="<?php echo e($value); ?>"
                                    ><?php echo e(trans('livewire-powergrid::datatable.input_text_options.' . $value)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-pg-primary-700 dark:text-pg-primary-300">
                                <?php if (isset($component)) { $__componentOriginal0fd0aee7f7ebcb95e3693513ac55802a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0fd0aee7f7ebcb95e3693513ac55802a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.down','data' => ['class' => 'w-4 h-4 dark:text-gray-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 dark:text-gray-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0fd0aee7f7ebcb95e3693513ac55802a)): ?>
<?php $attributes = $__attributesOriginal0fd0aee7f7ebcb95e3693513ac55802a; ?>
<?php unset($__attributesOriginal0fd0aee7f7ebcb95e3693513ac55802a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0fd0aee7f7ebcb95e3693513ac55802a)): ?>
<?php $component = $__componentOriginal0fd0aee7f7ebcb95e3693513ac55802a; ?>
<?php unset($__componentOriginal0fd0aee7f7ebcb95e3693513ac55802a); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'pl-0 w-full sm:w-1/2' => !$inline && $showSelectOptions,
                    'pt-1' => !$showSelectOptions,
                ]); ?>">
                    <input
                        data-cy="input_text_<?php echo e($tableName); ?>_<?php echo e($field); ?>"
                        wire:key="input-<?php echo e($field); ?>"
                        data-id="<?php echo e($field); ?>"
                        <?php if(isset($enabledFilters[$field]['disabled']) && boolval($enabledFilters[$field]['disabled']) === true): ?> disabled
                            <?php else: ?>
                                <?php echo e($defaultAttributes['inputAttributes']); ?> <?php endif; ?>
                        type="text"
                        class="<?php echo e($inputClasses); ?>"
                        placeholder="<?php echo e($placeholder); ?>"
                    />
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/filters/input-text.blade.php ENDPATH**/ ?>