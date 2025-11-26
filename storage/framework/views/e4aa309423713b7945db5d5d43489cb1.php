<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> 
        <?php echo e(t('invoice_details')); ?>

     <?php $__env->endSlot(); ?>


    <div class="max-w-6xl mx-auto">
          <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => t('dashboard'), 'route' => auth()->user()->user_type === 'admin' ? route('admin.dashboard') : tenant_route('tenant.dashboard')],
            ['label' => t('invoice'), 'route' => auth()->user()->user_type === 'admin' ? route('admin.invoices.list') : tenant_route('tenant.invoices') ],
            ['label' => t('invoice_details')],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => t('dashboard'), 'route' => auth()->user()->user_type === 'admin' ? route('admin.dashboard') : tenant_route('tenant.dashboard')],
            ['label' => t('invoice'), 'route' => auth()->user()->user_type === 'admin' ? route('admin.invoices.list') : tenant_route('tenant.invoices') ],
            ['label' => t('invoice_details')],
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>

        <!-- Main Content -->
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('header', null, []); ?> 
                <!--Invoice Header -->
                <div>
                    <div
                        class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-14 h-14 bg-primary-100  rounded-full flex items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-document-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-primary-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <?php echo e(t('invoice')); ?>

                                    #<?php echo e($invoice->invoice_number ?? format_draft_invoice_number()); ?>

                                </h2>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-calendar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e($invoice->created_at->format('F j, Y')); ?>

                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <!-- Status Badge - Moved to header section for better visibility -->
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?php echo e(match ($invoice->status) {
                                    'paid' => 'bg-success-100 text-success-800 dark:bg-success-800/30 dark:text-success-400',
                                    'cancelled' => 'bg-danger-100 text-danger-800 dark:bg-danger-800/30 dark:text-danger-400',
                                    'new' => 'bg-info-100 text-info-800 dark:bg-info-800/30 dark:text-info-400',
                                    'failed' => 'bg-danger-100 text-danger-800 dark:bg-danger-800/30 dark:text-danger-400',
                                    'pending' => 'bg-warning-100 text-warning-800 dark:bg-warning-800/30 dark:text-warning-400',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-400',
                                }); ?>">
                                <?php switch($invoice->status):
                                case ('paid'): ?>
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?php echo e(t('paid')); ?>

                                <?php break; ?>

                                <?php case ('cancelled'): ?>
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?php echo e(t('cancelled')); ?>

                                <?php break; ?>

                                <?php case ('failed'): ?>
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?php echo e(t('failed')); ?>

                                <?php break; ?>

                                <?php case ('pending'): ?>
                                <svg class="w-4 h-4 mr-1.5 animate-spin" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <?php echo e(t('pending')); ?>

                                <?php break; ?>

                                <?php case ('new'): ?>
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <?php echo e(t('new')); ?>

                                <?php break; ?>

                                <?php default: ?>
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?php echo e(ucfirst($invoice->status)); ?>

                                <?php endswitch; ?>
                            </span>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-2">
                                <?php if($invoice->status === 'paid'): ?>
                                <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.pdf', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.pdf', ['id' => $invoice->id])).'','target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.pdf', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.pdf', ['id' => $invoice->id])).'','target' => '_blank']); ?>
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e(t('view_pdf')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $attributes = $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $component = $__componentOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>

                                <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.download', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.download', ['id' => $invoice->id])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.download', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.download', ['id' => $invoice->id])).'']); ?>
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-down-tray'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e(t('download')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                                <?php elseif($invoice->status === 'new' || $invoice->status === 'pending'): ?>
                                <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.pdf', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.pdf', ['id' => $invoice->id])).'','target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.pdf', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.pdf', ['id' => $invoice->id])).'','target' => '_blank']); ?>
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e(t('view_pdf')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $attributes = $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $component = $__componentOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>

                                <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.download', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.download', ['id' => $invoice->id])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.download', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.download', ['id' => $invoice->id])).'']); ?>
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-down-tray'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e(t('download')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>

                                <?php if(auth()->user()->user_type === 'tenant'): ?>
                                    <?php
                                        $hasPendingTransaction = $invoice->transactions->contains('status', 'pending');
                                        $pendingPayPalTransaction = $invoice->transactions
                                            ->where('status', 'pending')
                                            ->where('type', 'paypal')
                                            ->first();
                                    ?>

                                    <?php if($hasPendingTransaction && $pendingPayPalTransaction): ?>
                                        
                                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['onclick' => 'handlePendingPayPalPayment('.e($invoice->id).')','class' => 'bg-warning-600 dark:bg-warning-700 hover:bg-warning-700 dark:hover:bg-warning-800']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'handlePendingPayPalPayment('.e($invoice->id).')','class' => 'bg-warning-600 dark:bg-warning-700 hover:bg-warning-700 dark:hover:bg-warning-800']); ?>
                                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            <?php echo e(t('continue_payment', 'Continue Payment')); ?>

                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                                    <?php else: ?>
                                        
                                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['href' => ''.e(tenant_route('tenant.checkout.resume', ['id' => $invoice->id])).'','class' => 'bg-success-600 dark:bg-success-700 hover:bg-success-700 dark:hover:bg-success-800']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(tenant_route('tenant.checkout.resume', ['id' => $invoice->id])).'','class' => 'bg-success-600 dark:bg-success-700 hover:bg-success-700 dark:hover:bg-success-800']); ?>
                                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3 3v8a3 3 0 003 3z">
                                                </path>
                                            </svg>
                                            <?php echo e($invoice->status === 'pending' ? t('complete_payment', 'Complete Payment') : t('pay_now')); ?>

                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php else: ?>
                                <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.pdf', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.pdf', ['id' => $invoice->id])).'','target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.pdf', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.pdf', ['id' => $invoice->id])).'','target' => '_blank']); ?>
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e(t('view_pdf')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $attributes = $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $component = $__componentOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.download', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.download', ['id' => $invoice->id])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(auth()->user()->user_type === 'admin'
                                            ? route('admin.invoices.download', ['id' => $invoice->id])
                                            : tenant_route('tenant.invoices.download', ['id' => $invoice->id])).'']); ?>
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-down-tray'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    <?php echo e(t('download')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Body -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- From/To Section with enhanced styling -->
                    <div class="bg-primary-50/30 dark:bg-gray-700 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">From</h3>
                        <?php
                        $systemSettings = get_batch_settings([
                        'system.company_name',
                        'system.company_address',
                        'system.company_city',
                        'system.company_state',
                        'system.company_zip_code',
                        'system.company_country_id',
                        'system.company_email',
                        ]);
                        ?>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p class="font-medium">
                                        <?php echo e($systemSettings['system.company_name'] ?? config('app.name')); ?>

                                    </p>
                                    <p><?php echo e($systemSettings['system.company_address']); ?></p>
                                    <p><?php echo e($systemSettings['system.company_city']); ?>

                                        <?php echo e($systemSettings['system.company_state']); ?>

                                        <?php echo e($systemSettings['system.company_zip_code']); ?></p>
                                    <p><?php echo e(get_country_name($systemSettings['system.company_country_id'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p class="mt-1"><?php echo e($systemSettings['system.company_email']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p class="mt-1"><?php echo e($systemSettings['system.company_email']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <?php
                                        do_action('invoice_view_company_info', $invoice,'');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary-50/30 dark:bg-gray-700 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">To</h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p class="font-medium">
                                        <?php echo e($tenant->billing_name ? $tenant->billing_name : $tenant->company_name); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p><?php echo e($tenant->billing_address); ?></p>
                                    <p><?php echo e($tenant->billing_city); ?> <?php echo e($tenant->billing_state); ?>

                                        <?php echo e($tenant->billing_zip_code); ?>

                                    </p>
                                    <p><?php echo e(get_country_name($tenant->billing_country)); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p class="mt-1"><?php echo e($tenant->billing_email); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <?php
                                        do_action('custom_invoice_additional_billing_info', $tenant);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Summary Card -->
                <div class="lg:col-span-2 mt-6">
                    <div class="bg-primary-50/30 dark:bg-gray-700 rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(t('invoice_summary')); ?>

                            </h3>
                        </div>

                        <!-- Items Table with enhanced styling -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-primary-50/30 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <?php echo e(t('description')); ?></th>
                                        <th scope="col"
                                            class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <?php echo e(t('price')); ?></th>
                                        <th scope="col"
                                            class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <?php echo e(t('qty')); ?></th>
                                        <th scope="col"
                                            class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <?php echo e(t('amount')); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-normal">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo e($item->title); ?>

                                            </div>
                                            <?php if($item->description): ?>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                <?php echo e($item->description); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            <?php echo e($invoice->formatAmount($item->amount)); ?>

                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            <?php echo e($item->quantity); ?>

                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white text-right">
                                            <?php echo e($invoice->formatAmount($item->amount * $item->quantity)); ?>

                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals Section with better organization -->
                        <div class="p-6 bg-primary-50/30 dark:bg-gray-700 border-t dark:border-gray-600">
                            <div class="flex flex-col items-end">
                                <div class="w-full max-w-xs">
                                    <?php
                                    $subtotal = $invoice->subTotal();
                                    $taxDetails = $invoice->getTaxDetails();

                                    // Create price breakdown display
                                    $priceBreakdown = $invoice->formatAmount($subtotal);
                                    $taxBreakdown = [];
                                    foreach ($taxDetails as $tax) {
                                        $taxBreakdown[] = $tax['formatted_rate'] . ' ' . $tax['name'];
                                    }
                                    ?>

                                    <div class="flex justify-between py-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-300"><?php echo e(t('subtotal')); ?></span>
                                        <span class="text-sm text-gray-900 dark:text-white"><?php echo e($invoice->formatAmount($subtotal)); ?></span>
                                    </div>

                                    <?php if(count($taxDetails) > 0): ?>


                                    <?php $__currentLoopData = $taxDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    // Calculate tax amount based on rate and subtotal if it's showing as 0
                                    $taxAmount = $tax['amount'];
                                    if ($taxAmount <= 0 && $tax['rate']> 0) {
                                        $taxAmount = $subtotal * ($tax['rate'] / 100);
                                        $formattedTaxAmount = $invoice->formatAmount($taxAmount);
                                        } else {
                                        $formattedTaxAmount = $tax['formatted_amount'];
                                        }
                                        ?>
                                        <div class="flex justify-between py-2">
                                            <span class="text-sm text-gray-600 dark:text-gray-300"><?php echo e($tax['name']); ?>

                                                (<?php echo e($tax['formatted_rate']); ?>)
                                                :</span>
                                            <span class="text-sm text-gray-900 dark:text-white"><?php echo e($formattedTaxAmount); ?></span>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                        <div class="flex justify-between py-2">
                                            <span class="text-sm text-gray-600 dark:text-gray-300"><?php echo e(t('tax')); ?>

                                                (0%):</span>
                                            <span class="text-sm text-gray-900 dark:text-white"><?php echo e($invoice->formatAmount(0)); ?></span>
                                        </div>
                                        <?php endif; ?>

                                        <?php if($invoice->fee > 0): ?>
                                        <div class="flex justify-between py-2">
                                            <span class="text-sm text-gray-600 dark:text-gray-300"><?php echo e(t('fee')); ?>:</span>
                                            <span class="text-sm text-gray-900 dark:text-white"><?php echo e($invoice->formatAmount($invoice->fee)); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php
                                        // Ensure we calculate and display the correct total with tax
                                        $taxAmount = 0;

                                        // Calculate actual tax amount if needed
                                        foreach ($taxDetails as $tax) {
                                        $amount = $tax['amount'];
                                        if ($amount <= 0 && $tax['rate']> 0) {
                                            $amount = $subtotal * ($tax['rate'] / 100);
                                            }
                                            $taxAmount += $amount;
                                            }

                                            $fee = $invoice->fee ?: 0;
                                            $calculatedTotal = $subtotal + $taxAmount + $fee;

                                            // Use calculated total if different from invoice total
                                            if (abs($calculatedTotal - $invoice->total()) > 0.01) {
                                            $totalDisplay = $invoice->formatAmount($calculatedTotal);
                                            } else {
                                            $totalDisplay = $invoice->formattedTotal();
                                            }
                                            ?>

                                            <?php if(count($creditTransactions ?? []) > 0): ?>
                                            <div
                                                class="flex justify-between py-3 border-t border-gray-200 dark:border-gray-600 mt-2">
                                                <span class="text-gray-900 dark:text-white"><?php echo e(t('credit_applied')); ?>:</span>
                                                <span class="text-gray-900 dark:text-white"><?php echo e($invoice->formatAmount($creditTransactions->sum('amount'))); ?></span>
                                            </div>
                                            <div
                                                class="flex justify-between py-3 border-t border-gray-200 dark:border-gray-600 mt-2">
                                                <span class="text-gray-900 dark:text-white"><?php echo e($invoice->status ==
                                                    'paid' ? t('amount_paid') : t('amount_due')); ?>:</span>
                                                <?php
                                                $final = $calculatedTotal - $creditTransactions->sum('amount');
                                                ?>
                                                <span class="text-gray-900 dark:text-white"><?php echo e($invoice->formatAmount($final ?? 0)); ?></span>
                                            </div>
                                            <?php endif; ?>

                                            <div
                                                class="flex justify-between py-3 font-bold border-t border-gray-200 dark:border-gray-600 mt-2">
                                                <span class="text-gray-900 dark:text-white"><?php echo e(t('total')); ?>:</span>

                                                <span class="text-gray-900 dark:text-white"><?php echo e($totalDisplay); ?></span>
                                            </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History with modern styling -->
                <?php if($invoice->transactions->count() > 0): ?>
                <div class="mt-10">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">
                        <?php echo e(t('transaction_history')); ?></h2>
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
                            <thead class="bg-primary-50/30 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <?php echo e(t('date')); ?></th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <?php echo e(t('type')); ?></th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <?php echo e(t('status')); ?></th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <?php echo e(t('amount')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php $__currentLoopData = $invoice->transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e(format_date_time($transaction->created_at)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e(ucfirst($transaction->type)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        <?php echo e($transaction->status === 'success'
                                                            ? 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-100'
                                                            : ($transaction->status === 'pending'
                                                                ? 'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100'
                                                                : 'bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-100')); ?>">
                                            <?php echo e(ucfirst($transaction->status)); ?>

                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right font-medium">
                                        <?php echo e($transaction->formattedAmount()); ?>

                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                 <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
    </div>

    <script>
        function handlePendingPayPalPayment(invoiceId) {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

            // Call the retry endpoint
            <?php if(auth()->user()->user_type === 'admin'): ?>
                fetch(`<?php echo e(route('tenant.payment.paypal.retry', ['subdomain' => $invoice->tenant->subdomain, 'invoice' => $invoice->id])); ?>`, {
            <?php else: ?>
                fetch(`<?php echo e(tenant_route('tenant.payment.paypal.retry', ['invoice' => $invoice->id])); ?>`, {
            <?php endif; ?>
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.id) {
                    // Get the correct PayPal URL based on mode
                    const paypalMode = '<?php echo e(get_setting("payment.paypal_mode", "sandbox")); ?>';
                    const paypalUrl = paypalMode === 'sandbox'
                        ? 'https://www.sandbox.paypal.com/checkoutnow'
                        : 'https://www.paypal.com/checkoutnow';

                    if (data.action === 'resume') {
                        // Resume existing PayPal order
                        window.location.href = `${paypalUrl}?token=${data.id}`;
                    } else {
                        // New PayPal order created
                        window.location.href = `${paypalUrl}?token=${data.id}`;
                    }
                } else {
                    alert('Error processing payment: ' + (data.message || 'Unknown error'));
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Payment error:', error);
                alert('Error processing payment. Please try again.');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/invoices/show.blade.php ENDPATH**/ ?>