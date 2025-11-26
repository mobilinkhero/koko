<div
    x-data="{ open: false, countChecked: <?php if ((object) ('checkboxValues') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('checkboxValues'->value()); ?>')<?php echo e('checkboxValues'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('checkboxValues'); ?>')<?php endif; ?>.live }"
    x-on:keydown.esc="open = false"
    x-on:click.outside="open = false;"
>
    <button
        @click.prevent="open = true"
        class="focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 rounded-md border-0 bg-transparent py-2 px-3 ring-0 placeholder:text-gray-400 focus:outline-none sm:text-sm sm:leading-6 w-auto"
    >
        <div class="flex">
            <?php if (isset($component)) { $__componentOriginal6b8135c2c8cb1493dc3034248d76b61c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b8135c2c8cb1493dc3034248d76b61c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.download','data' => ['class' => 'h-5 w-5 text-pg-primary-500 dark:text-pg-primary-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.download'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-pg-primary-500 dark:text-pg-primary-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b8135c2c8cb1493dc3034248d76b61c)): ?>
<?php $attributes = $__attributesOriginal6b8135c2c8cb1493dc3034248d76b61c; ?>
<?php unset($__attributesOriginal6b8135c2c8cb1493dc3034248d76b61c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b8135c2c8cb1493dc3034248d76b61c)): ?>
<?php $component = $__componentOriginal6b8135c2c8cb1493dc3034248d76b61c; ?>
<?php unset($__componentOriginal6b8135c2c8cb1493dc3034248d76b61c); ?>
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
        class="absolute z-10 mt-2 rounded-md dark:bg-pg-primary-700 bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        tabindex="-1"
        @keydown.tab="open = false"
        @keydown.enter.prevent="open = false;"
        @keyup.space.prevent="open = false;"
    >
        <!--[if BLOCK]><![endif]--><?php if(in_array('xlsx', data_get($setUp, 'exportable.type'))): ?>
            <div class="flex items-center px-4 py-1 text-pg-primary-400 dark:text-pg-primary-300 border-b border-pg-primary-100 dark:border-pg-primary-600">
                <span class="w-12"><?php echo app('translator')->get('XLSX'); ?></span>
                <button
                    wire:click.prevent="exportToXLS"
                    x-on:click="open = false"
                    href="#"
                    class="px-2 py-1 block text-pg-primary-800 hover:bg-pg-primary-100 hover:text-black-300 dark:text-pg-primary-200 dark:hover:bg-pg-primary-800 rounded"
                >
                    <span class="export-count text-xs">(<?php echo e($total); ?>)</span>
                    <!--[if BLOCK]><![endif]--><?php if(count($enabledFilters) === 0): ?>
                        <?php echo app('translator')->get('livewire-powergrid::datatable.labels.all'); ?>
                    <?php else: ?>
                        <?php echo app('translator')->get('livewire-powergrid::datatable.labels.filtered'); ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                </button>
                <!--[if BLOCK]><![endif]--><?php if($checkbox): ?>
                    <button wire:click.prevent="exportToXLS(true)"
                       x-on:click="open = false"
                       x-bind:disabled="countChecked.length === 0"
                       :class="{'cursor-not-allowed' : countChecked.length === 0}"
                       class="px-2 py-1 block text-pg-primary-800 hover:bg-pg-primary-100 hover:text-black-300 dark:text-pg-primary-200 dark:hover:bg-pg-primary-800 rounded"
                    >
                        <span class="export-count text-xs" x-text="`(${countChecked.length})`"></span> <?php echo app('translator')->get('livewire-powergrid::datatable.labels.selected'); ?>
                    </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if(in_array('csv', data_get($setUp, 'exportable.type'))): ?>
            <div class="flex items-center px-4 py-1 text-pg-primary-400 dark:text-pg-primary-300">
                <span class="w-12"><?php echo app('translator')->get('Csv'); ?></span>
                <button
                    wire:click.prevent="exportToCsv"
                    x-on:click="open = false"
                    class="px-2 py-1 block text-pg-primary-800 hover:bg-pg-primary-100 hover:text-black-300 dark:text-pg-primary-200 dark:hover:bg-pg-primary-800 rounded"
                >
                    <span class="export-count text-xs">(<?php echo e($total); ?>)</span>
                    <!--[if BLOCK]><![endif]--><?php if(count($enabledFilters) === 0): ?>
                        <?php echo app('translator')->get('livewire-powergrid::datatable.labels.all'); ?>
                    <?php else: ?>
                        <?php echo app('translator')->get('livewire-powergrid::datatable.labels.filtered'); ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </button>
                <!--[if BLOCK]><![endif]--><?php if($checkbox): ?>
                    <button
                        wire:click.prevent="exportToCsv(true)"
                        x-on:click="open = false"
                        :class="{'cursor-not-allowed' : countChecked.length === 0}"
                        class="px-2 py-1 block text-pg-primary-800 hover:bg-pg-primary-100 hover:text-black-300 dark:text-pg-primary-200 dark:hover:bg-pg-primary-800 rounded"
                    >
                        <span class="export-count text-xs" x-text="`(${countChecked.length})`"></span> <?php echo app('translator')->get('livewire-powergrid::datatable.labels.selected'); ?>
                    </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header/export.blade.php ENDPATH**/ ?>