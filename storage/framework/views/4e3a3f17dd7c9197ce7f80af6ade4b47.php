<div>
    <?php if ($__env->exists(data_get($setUp, 'header.includeViewOnTop'))) echo $__env->make(data_get($setUp, 'header.includeViewOnTop'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mb-3 md:flex md:flex-row w-full justify-between items-center">
        <div class="md:flex md:flex-row w-full gap-1">
            <div x-data="pgRenderActions">
                <span class="pg-actions" x-html="toHtml"></span>
            </div>
            <div class="flex flex-row items-center text-sm flex-wrap">
                <!--[if BLOCK]><![endif]--><?php if(data_get($setUp, 'exportable')): ?>
                    <div
                        class="mr-2 mt-2 sm:mt-0"
                        id="pg-header-export"
                    >
                        <?php echo $__env->make(data_get($theme, 'root') . '.header.export', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php if ($__env->exists(data_get($theme, 'root') . '.header.toggle-columns')) echo $__env->make(data_get($theme, 'root') . '.header.toggle-columns', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if ($__env->exists(data_get($theme, 'root') . '.header.soft-deletes')) echo $__env->make(data_get($theme, 'root') . '.header.soft-deletes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <!--[if BLOCK]><![endif]--><?php if(config('livewire-powergrid.filter') == 'outside' && count($this->filters()) > 0): ?>
                    <?php if ($__env->exists(data_get($theme, 'root') . '.header.filters')) echo $__env->make(data_get($theme, 'root') . '.header.filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            <?php echo $__env->renderWhen(boolval(data_get($setUp, 'header.wireLoading')),
                data_get($theme, 'root') . '.header.loading', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
        </div>
        <?php echo $__env->make(data_get($theme, 'root') . '.header.search', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php if ($__env->exists(data_get($theme, 'root') . '.header.enabled-filters')) echo $__env->make(data_get($theme, 'root') . '.header.enabled-filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->renderWhen(data_get($setUp, 'exportable.batchExport.queues', 0), data_get($theme, 'root') . '.header.batch-exporting', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($multiSort, data_get($theme, 'root') . '.header.multi-sort', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php if ($__env->exists(data_get($setUp, 'header.includeViewOnBottom'))) echo $__env->make(data_get($setUp, 'header.includeViewOnBottom'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if ($__env->exists(data_get($theme, 'root') . '.header.message-soft-deletes')) echo $__env->make(data_get($theme, 'root') . '.header.message-soft-deletes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header.blade.php ENDPATH**/ ?>