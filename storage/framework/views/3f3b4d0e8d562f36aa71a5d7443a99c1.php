<!--[if BLOCK]><![endif]--><?php if(data_get($setUp, 'header.toggleColumns')): ?>
    <div
        x-data="{ open: false }"
        class="mr-2 mt-2 sm:mt-0"
        @click.outside="open = false"
    >
        <button
            data-cy="toggle-columns-<?php echo e($tableName); ?>"
            @click.prevent="open = ! open"
            class="focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 rounded-md border-0 bg-transparent py-2 px-3 ring-0 placeholder:text-gray-400 focus:outline-none sm:text-sm sm:leading-6 w-auto"
        >
            <div class="flex">
                <?php if (isset($component)) { $__componentOriginal491d64c78bef44602650443184da8c52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal491d64c78bef44602650443184da8c52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.eye-off','data' => ['class' => 'w-5 h-5 text-pg-primary-500 dark:text-pg-primary-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.eye-off'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-pg-primary-500 dark:text-pg-primary-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal491d64c78bef44602650443184da8c52)): ?>
<?php $attributes = $__attributesOriginal491d64c78bef44602650443184da8c52; ?>
<?php unset($__attributesOriginal491d64c78bef44602650443184da8c52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal491d64c78bef44602650443184da8c52)): ?>
<?php $component = $__componentOriginal491d64c78bef44602650443184da8c52; ?>
<?php unset($__componentOriginal491d64c78bef44602650443184da8c52); ?>
<?php endif; ?>
            </div>
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="toggle-columns-base group absolute z-10 mt-2 w-56 rounded-md dark:bg-pg-primary-700 bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            tabindex="-1"
            @keydown.tab="open = false"
            @keydown.enter.prevent="open = false;"
            @keyup.space.prevent="open = false;"
        >
            <div
                role="none"
            >
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->visibleColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        wire:key="toggle-column-<?php echo e(data_get($column, 'isAction') ? 'actions' : data_get($column, 'field')); ?>"
                        data-cy="toggle-field-<?php echo e(data_get($column, 'isAction') ? 'actions' : data_get($column, 'field')); ?>"
                        wire:click="$dispatch('pg:toggleColumn-<?php echo e($tableName); ?>', { field: '<?php echo e(data_get($column, 'field')); ?>'})"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'font-semibold bg-pg-primary-100 dark:bg-pg-primary-800 ' => data_get($column, 'hidden'),
                            'py-1' => $loop->first || $loop->last,
                            'cursor-pointer text-sm flex justify-between block px-4 py-2 text-pg-primary-800 hover:bg-pg-primary-100 hover:text-black-300 dark:text-pg-primary-200 dark:hover:bg-pg-primary-800'
                        ]); ?>"
                    >
                        <div>
                            <?php echo data_get($column, 'title'); ?>

                        </div>
                        <!--[if BLOCK]><![endif]--><?php if(!data_get($column, 'hidden')): ?>
                            <?php if (isset($component)) { $__componentOriginal44e829c8d9d7b7526c011eb87286160d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal44e829c8d9d7b7526c011eb87286160d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.eye','data' => ['class' => 'h-5 w-5 text-pg-primary-200 dark:text-pg-primary-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-pg-primary-200 dark:text-pg-primary-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal44e829c8d9d7b7526c011eb87286160d)): ?>
<?php $attributes = $__attributesOriginal44e829c8d9d7b7526c011eb87286160d; ?>
<?php unset($__attributesOriginal44e829c8d9d7b7526c011eb87286160d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal44e829c8d9d7b7526c011eb87286160d)): ?>
<?php $component = $__componentOriginal44e829c8d9d7b7526c011eb87286160d; ?>
<?php unset($__componentOriginal44e829c8d9d7b7526c011eb87286160d); ?>
<?php endif; ?>
                        <?php else: ?>
                            <?php if (isset($component)) { $__componentOriginal491d64c78bef44602650443184da8c52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal491d64c78bef44602650443184da8c52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.eye-off','data' => ['class' => 'h-5 w-5 text-pg-primary-500 dark:text-pg-primary-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.eye-off'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-pg-primary-500 dark:text-pg-primary-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal491d64c78bef44602650443184da8c52)): ?>
<?php $attributes = $__attributesOriginal491d64c78bef44602650443184da8c52; ?>
<?php unset($__attributesOriginal491d64c78bef44602650443184da8c52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal491d64c78bef44602650443184da8c52)): ?>
<?php $component = $__componentOriginal491d64c78bef44602650443184da8c52; ?>
<?php unset($__componentOriginal491d64c78bef44602650443184da8c52); ?>
<?php endif; ?>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header/toggle-columns.blade.php ENDPATH**/ ?>