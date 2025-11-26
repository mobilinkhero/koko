<div
    wire:key="toggle-filters-<?php echo e($tableName); ?>"
    id="toggle-filters"
    class="flex mr-2 mt-2 sm:mt-0 gap-3"
>
    <button
        wire:click="toggleFilters"
        type="button"
        class="focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 rounded-md border-0 bg-transparent py-2 px-3 ring-0 placeholder:text-gray-400 focus:outline-none sm:text-sm sm:leading-6 w-auto"
    >
        <?php if (isset($component)) { $__componentOriginal741000299fce87de7e024358cf3b3f95 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal741000299fce87de7e024358cf3b3f95 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.filter','data' => ['class' => 'h-4 w-4 text-pg-primary-500 dark:text-pg-primary-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 text-pg-primary-500 dark:text-pg-primary-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal741000299fce87de7e024358cf3b3f95)): ?>
<?php $attributes = $__attributesOriginal741000299fce87de7e024358cf3b3f95; ?>
<?php unset($__attributesOriginal741000299fce87de7e024358cf3b3f95); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal741000299fce87de7e024358cf3b3f95)): ?>
<?php $component = $__componentOriginal741000299fce87de7e024358cf3b3f95; ?>
<?php unset($__componentOriginal741000299fce87de7e024358cf3b3f95); ?>
<?php endif; ?>
    </button>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header/filters.blade.php ENDPATH**/ ?>