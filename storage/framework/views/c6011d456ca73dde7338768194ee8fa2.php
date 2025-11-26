<!--[if BLOCK]><![endif]--><?php if(data_get($setUp, 'header.softDeletes')): ?>
    <div
        x-data="{ open: false }"
        class="mr-0 sm:mr-2 mt-2 sm:mt-0"
        @click.outside="open = false"
    >
        <button
            @click.prevent="open = ! open"
            class="focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 rounded-md border-0 bg-transparent py-2 px-3 ring-0 placeholder:text-gray-400 focus:outline-none sm:text-sm sm:leading-6 w-auto"
        >
            <div class="flex">
                <?php if (isset($component)) { $__componentOriginalf86e8445c3b4b21e8fd571a52134e584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf86e8445c3b4b21e8fd571a52134e584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.trash','data' => ['class' => 'text-pg-primary-500 dark:text-pg-primary-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.trash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-pg-primary-500 dark:text-pg-primary-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf86e8445c3b4b21e8fd571a52134e584)): ?>
<?php $attributes = $__attributesOriginalf86e8445c3b4b21e8fd571a52134e584; ?>
<?php unset($__attributesOriginalf86e8445c3b4b21e8fd571a52134e584); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf86e8445c3b4b21e8fd571a52134e584)): ?>
<?php $component = $__componentOriginalf86e8445c3b4b21e8fd571a52134e584; ?>
<?php unset($__componentOriginalf86e8445c3b4b21e8fd571a52134e584); ?>
<?php endif; ?>
            </div>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transform duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transform duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="mt-2 py-2 w-48 bg-white shadow-xl absolute z-10 dark:bg-pg-primary-700"
        >

            <div
                x-on:click="$wire.dispatch('pg:softDeletes-<?php echo e($tableName); ?>', {softDeletes: ''}); open = false"
                class="cursor-pointer flex justify-start block px-4 py-2 text-pg-primary-800 hover:bg-pg-primary-50 hover:text-black-200 dark:text-pg-primary-200 dark:hover:bg-gray-900 dark:hover:bg-pg-primary-700"
            >
                <?php echo app('translator')->get('livewire-powergrid::datatable.soft_deletes.without_trashed'); ?>
            </div>
            <div
                x-on:click="$wire.dispatch('pg:softDeletes-<?php echo e($tableName); ?>', {softDeletes: 'withTrashed'}); open = false"
                class="cursor-pointer flex justify-start block px-4 py-2 text-pg-primary-800 hover:bg-pg-primary-50 hover:text-black-200 dark:text-pg-primary-200 dark:hover:bg-gray-900 dark:hover:bg-pg-primary-700"
            >
                <?php echo app('translator')->get('livewire-powergrid::datatable.soft_deletes.with_trashed'); ?>
            </div>
            <div
                x-on:click="$wire.dispatch('pg:softDeletes-<?php echo e($tableName); ?>', {softDeletes: 'onlyTrashed'}); open = false"
                class="cursor-pointer flex justify-start block px-4 py-2 text-pg-primary-800 hover:bg-pg-primary-50 hover:text-black-200 dark:text-pg-primary-200 dark:hover:bg-gray-900 dark:hover:bg-pg-primary-700"
            >
                <?php echo app('translator')->get('livewire-powergrid::datatable.soft_deletes.only_trashed'); ?>
            </div>

        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header/soft-deletes.blade.php ENDPATH**/ ?>