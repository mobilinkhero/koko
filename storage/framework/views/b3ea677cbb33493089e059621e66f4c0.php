<?php if (isset($component)) { $__componentOriginalc90fb34650db9397d263052ddcf90e93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc90fb34650db9397d263052ddcf90e93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select-status','data' => ['type' => 'occurrence','options' => $options,'userId' => $userId,'selected' => $selected]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'occurrence','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($options),'userId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userId),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selected)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc90fb34650db9397d263052ddcf90e93)): ?>
<?php $attributes = $__attributesOriginalc90fb34650db9397d263052ddcf90e93; ?>
<?php unset($__attributesOriginalc90fb34650db9397d263052ddcf90e93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc90fb34650db9397d263052ddcf90e93)): ?>
<?php $component = $__componentOriginalc90fb34650db9397d263052ddcf90e93; ?>
<?php unset($__componentOriginalc90fb34650db9397d263052ddcf90e93); ?>
<?php endif; ?><?php /**PATH /home/adphwiudpl/mark.chatvoo.com/storage/framework/views/9334257a1c585c1ff038f58e45af4f8f.blade.php ENDPATH**/ ?>