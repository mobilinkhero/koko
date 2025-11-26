<div>
    <?php if ($__env->exists(data_get($setUp, 'footer.includeViewOnTop'))) echo $__env->make(data_get($setUp, 'footer.includeViewOnTop'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <footer
        id="pg-footer"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'justify-between' => filled(data_get($setUp, 'footer.perPage')),
            'justify-end' => blank(data_get($setUp, 'footer.perPage')),
            theme_style($theme, 'footer.footer', 'border-x border-b rounded-b-lg border-b border-pg-primary-200 dark:bg-pg-primary-700 dark:border-pg-primary-600'),
            theme_style($theme, 'footer.footer_with_pagination', 'md:flex md:flex-row w-full items-center py-3 bg-white overflow-y-auto pl-2 pr-2 relative dark:bg-pg-primary-900') => blank(data_get($setUp, 'footer.pagination')),
        ]); ?>"
    >
        <!--[if BLOCK]><![endif]--><?php if(filled(data_get($setUp, 'footer.perPage')) &&
                count(data_get($setUp, 'footer.perPageValues')) > 1 &&
                blank(data_get($setUp, 'footer.pagination'))): ?>
            <div class="flex flex-row justify-center md:justify-start mb-2 md:mb-0">
                <div class="relative">
                    <select
                        wire:model.live="setUp.footer.perPage"
                        class="<?php echo e(theme_style($theme, 'footer.select')); ?>"
                    >
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = data_get($setUp, 'footer.perPageValues'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>">
                                <!--[if BLOCK]><![endif]--><?php if($value == 0): ?>
                                    <?php echo e(trans('livewire-powergrid::datatable.labels.all')); ?>

                                <?php else: ?>
                                    <?php echo e($value); ?>

                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-pg-primary-700 dark:text-pg-primary-300">
                        <?php if (isset($component)) { $__componentOriginal0fd0aee7f7ebcb95e3693513ac55802a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0fd0aee7f7ebcb95e3693513ac55802a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'livewire-powergrid::components.icons.down','data' => ['class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('livewire-powergrid::icons.down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
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
                <div
                    class="pl-4 hidden sm:block md:block lg:block w-full"
                    style="padding-top: 6px;"
                >
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div>
            <!--[if BLOCK]><![endif]--><?php if(method_exists($this->records, 'links')): ?>
                <?php echo $this->records->links(data_get($setUp, 'footer.pagination') ?: data_get($theme, 'root') . '.pagination', [
                    'recordCount' => data_get($setUp, 'footer.recordCount'),
                    'perPage' => data_get($setUp, 'footer.perPage'),
                    'perPageValues' => data_get($setUp, 'footer.perPageValues'),
                ]); ?>

            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </footer>
    <?php if ($__env->exists(data_get($setUp, 'footer.includeViewOnBottom'))) echo $__env->make(data_get($setUp, 'footer.includeViewOnBottom'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/footer.blade.php ENDPATH**/ ?>