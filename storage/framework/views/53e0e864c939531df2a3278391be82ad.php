
<?php if (isset($component)) { $__componentOriginal3bcae8ae865f80e68ed09e5eb746dee0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3bcae8ae865f80e68ed09e5eb746dee0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-navigation','data' => ['collapsed' => false,'menuitems' => $menuItems,'setupMenuitems' => $setupMenuItems]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'menuitems' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuItems),'setupMenuitems' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setupMenuItems)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3bcae8ae865f80e68ed09e5eb746dee0)): ?>
<?php $attributes = $__attributesOriginal3bcae8ae865f80e68ed09e5eb746dee0; ?>
<?php unset($__attributesOriginal3bcae8ae865f80e68ed09e5eb746dee0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3bcae8ae865f80e68ed09e5eb746dee0)): ?>
<?php $component = $__componentOriginal3bcae8ae865f80e68ed09e5eb746dee0; ?>
<?php unset($__componentOriginal3bcae8ae865f80e68ed09e5eb746dee0); ?>
<?php endif; ?>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/admin/partials/admin-sidebar-navigation.blade.php ENDPATH**/ ?>