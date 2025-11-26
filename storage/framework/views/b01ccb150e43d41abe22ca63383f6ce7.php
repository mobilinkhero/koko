<?php
    $columns = collect($columns)->map(function ($column) {
        return data_forget($column, 'rawQueries');
    });
?>

<div
    class="flex flex-col"
    <?php if($deferLoading): ?> wire:init="fetchDatasource" <?php endif; ?>
>
    <div
        id="power-grid-table-container"
        class="<?php echo e(theme_style($theme, 'table.layout.container')); ?>"
    >
        <div
            id="power-grid-table-base"
            class="<?php echo e(theme_style($theme, 'table.layout.base')); ?>"
        >
            <?php echo $__env->make(theme_style($theme, 'layout.header'), [
                'enabledFilters' => $enabledFilters,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if(config('livewire-powergrid.filter') === 'outside'): ?>
                <?php
                    $filtersFromColumns = $columns
                        ->filter(fn($column) => filled(data_get($column, 'filters')));
                ?>

                <!--[if BLOCK]><![endif]--><?php if($filtersFromColumns->count() > 0): ?>
                    <?php if (isset($component)) { $__componentOriginal98b22e53a70aa57b1b8cc690c0d51ee5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98b22e53a70aa57b1b8cc690c0d51ee5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.frameworks.tailwind.filter','data' => ['enabledFilters' => $enabledFilters,'tableName' => $tableName,'columns' => $columns,'filtersFromColumns' => $filtersFromColumns,'theme' => $theme]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::frameworks.tailwind.filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['enabled-filters' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enabledFilters),'tableName' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tableName),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($columns),'filtersFromColumns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filtersFromColumns),'theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($theme)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98b22e53a70aa57b1b8cc690c0d51ee5)): ?>
<?php $attributes = $__attributesOriginal98b22e53a70aa57b1b8cc690c0d51ee5; ?>
<?php unset($__attributesOriginal98b22e53a70aa57b1b8cc690c0d51ee5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98b22e53a70aa57b1b8cc690c0d51ee5)): ?>
<?php $component = $__componentOriginal98b22e53a70aa57b1b8cc690c0d51ee5; ?>
<?php unset($__componentOriginal98b22e53a70aa57b1b8cc690c0d51ee5); ?>
<?php endif; ?>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <div
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'overflow-auto' => $readyToLoad,
                    'overflow-hidden' => !$readyToLoad,
                    theme_style($theme, 'table.layout.div'),
                ]); ?>"
            >
                <?php echo $__env->make($table, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <?php echo $__env->make(theme_style($theme, 'footer.view'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/table-base.blade.php ENDPATH**/ ?>