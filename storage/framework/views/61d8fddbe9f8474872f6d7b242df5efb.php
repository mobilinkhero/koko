
<!--[if BLOCK]><![endif]--><?php if(count($enabledFilters)): ?>
    <div
        data-cy="enabled-filters"
        class="pg-enabled-filters-base"
    >
        <?php if(count($enabledFilters) > 1): ?>
            <div class="flex group items-center gap-3 cursor-pointer">
                <span
                    wire:click.prevent="clearAllFilters"
                    class="select-none rounded-md outline-none inline-flex items-center border px-2 py-0.5 font-bold text-xs border-pg-primary-500 bg-pg-primary-100 dark:border-pg-primary-500 dark:bg-pg-primary-900 dark:text-pg-primary-300 dark:hover:text-pg-primary-400 text-pg-primary-600 hover:text-pg-primary-500"
                >
                    <?php echo e(trans('livewire-powergrid::datatable.buttons.clear_all_filters')); ?>

                    <?php if (isset($component)) { $__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.x','data' => ['class' => 'w-4 h-4 ml-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 ml-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01)): ?>
<?php $attributes = $__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01; ?>
<?php unset($__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01)): ?>
<?php $component = $__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01; ?>
<?php unset($__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01); ?>
<?php endif; ?>
                </span>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $enabledFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <!--[if BLOCK]><![endif]--><?php if(isset($filter['label'])): ?>
                <div
                    wire:key="enabled-filters-<?php echo e($filter['field']); ?>"
                    class="flex group items-center gap-3 cursor-pointer"
                >
                    <span
                        data-cy="enabled-filters-clear-<?php echo e($filter['field']); ?>"
                        wire:click.prevent="clearFilter('<?php echo e($filter['field']); ?>')"
                        class="select-none rounded-md outline-none inline-flex items-center border px-2 py-0.5 font-bold text-xs border-pg-primary-300 bg-white dark:border-pg-primary-600 dark:bg-pg-primary-800 dark:text-pg-primary-300 dark:hover:text-pg-primary-400 text-pg-primary-600 hover:text-pg-primary-500"
                    >
                        <?php echo e($filter['label']); ?>

                        <?php if (isset($component)) { $__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.x','data' => ['class' => 'w-4 h-4 ml-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 ml-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01)): ?>
<?php $attributes = $__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01; ?>
<?php unset($__attributesOriginal7d60ff8e342013a80b7e0c15ae7afd01); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01)): ?>
<?php $component = $__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01; ?>
<?php unset($__componentOriginal7d60ff8e342013a80b7e0c15ae7afd01); ?>
<?php endif; ?>
                    </span>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header/enabled-filters.blade.php ENDPATH**/ ?>