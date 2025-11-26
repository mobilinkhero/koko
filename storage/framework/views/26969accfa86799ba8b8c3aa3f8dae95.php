<div x-data="formComponent()">
     <?php $__env->slot('title', null, []); ?> <?php echo e(t('theme_settings')); ?> <?php $__env->endSlot(); ?>

    <div class="pb-6">
        <?php if (isset($component)) { $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings-heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(t('website_settings')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $attributes = $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $component = $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <?php if (isset($component)) { $__componentOriginal3878671f99e639f5e5475bae517c6ecf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3878671f99e639f5e5475bae517c6ecf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-website-settings-navigation','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-website-settings-navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3878671f99e639f5e5475bae517c6ecf)): ?>
<?php $attributes = $__attributesOriginal3878671f99e639f5e5475bae517c6ecf; ?>
<?php unset($__attributesOriginal3878671f99e639f5e5475bae517c6ecf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3878671f99e639f5e5475bae517c6ecf)): ?>
<?php $component = $__componentOriginal3878671f99e639f5e5475bae517c6ecf; ?>
<?php unset($__componentOriginal3878671f99e639f5e5475bae517c6ecf); ?>
<?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-6">
            <form x-on:submit.prevent="validateAndSubmit">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'rounded-lg']); ?>
                     <?php $__env->slot('header', null, []); ?> 
                        <?php if (isset($component)) { $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings-heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(t('theme_settings')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $attributes = $__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__attributesOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff)): ?>
<?php $component = $__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff; ?>
<?php unset($__componentOriginal32b3aedb79dcb21d2517daf1cd4b81ff); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginald4840e1146262bfa3abec1048daf8661 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4840e1146262bfa3abec1048daf8661 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings-description','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings-description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php echo e(t('theme_settings_description')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4840e1146262bfa3abec1048daf8661)): ?>
<?php $attributes = $__attributesOriginald4840e1146262bfa3abec1048daf8661; ?>
<?php unset($__attributesOriginald4840e1146262bfa3abec1048daf8661); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4840e1146262bfa3abec1048daf8661)): ?>
<?php $component = $__componentOriginald4840e1146262bfa3abec1048daf8661; ?>
<?php unset($__componentOriginald4840e1146262bfa3abec1048daf8661); ?>
<?php endif; ?>
                     <?php $__env->endSlot(); ?>

                     <?php $__env->slot('content', null, []); ?> 
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Image Upload Components -->
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['site_logo' => '22px x 40px', 'dark_logo' => '22px x 40px', 'favicon' => '12px x
                            12px', 'cover_page_image' => '729px x 152px']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="w-full upload-section" x-data="fileUploader('<?php echo e($key); ?>')">
                                <?php if (isset($component)) { $__componentOriginald8ba2b4c22a13c55321e34443c386276 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8ba2b4c22a13c55321e34443c386276 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.label','data' => ['for' => ''.e($key).'','value' => new \Illuminate\Support\HtmlString(
                                        t($key) . '<em class=\'text-primary-600\'> (recommended ' . $size . ')</em>',
                                    ),'class' => 'mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($key).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(new \Illuminate\Support\HtmlString(
                                        t($key) . '<em class=\'text-primary-600\'> (recommended ' . $size . ')</em>',
                                    )),'class' => 'mb-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $attributes = $__attributesOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__attributesOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $component = $__componentOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__componentOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>

                                <div class="relative p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-info-500 transition duration-300"
                                    x-on:click="$refs.imageInput.click()">
                                    <!-- Image Preview -->
                                    <template x-if="preview">
                                        <div class="relative inline-block">
                                            <img :src="preview" alt="Image Preview"
                                                class="object-contain rounded-lg shadow-md"
                                                :class="key === 'favicon' ? 'h-12 w-12' : 'h-24 w-48'" />
                                            <button type="button"
                                                class="absolute -top-4 -right-4 bg-danger-500 text-white rounded-full shadow-lg hover:bg-danger-600"
                                                x-on:click.stop="confirmDelete($event, () => { clearImage(); $wire.set('<?php echo e($key); ?>', ''); })">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-x-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
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
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Existing Image Preview -->
                                    <template x-if="!preview && '<?php echo e($themeSettings['theme.' . $key]); ?>'">
                                        <div class="relative inline-block">
                                            <img src="<?php echo e(asset('storage/' . $themeSettings['theme.' . $key])); ?>"
                                                alt="<?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>"
                                                class="object-contain rounded-lg shadow-md"
                                                :class="key === 'favicon' ? 'h-12 w-12' : 'h-24 w-48'" />
                                            <button type="button"
                                                class="absolute -top-4 -right-4 bg-danger-500 text-white rounded-full shadow-lg hover:bg-danger-600"
                                                x-on:click.stop="confirmDelete($event, () => { clearImage(); $wire.removeSetting('<?php echo e($key); ?>'); setTimeout(() => { location.reload(); }, 1500);})">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-x-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
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
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Placeholder if No Image -->
                                    <template x-if="!preview && !'<?php echo e($themeSettings['theme.' . $key]); ?>'">
                                        <div class="text-center">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-document-arrow-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-12 w-12 text-gray-400 mx-auto']); ?>
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
                                            <p class="mt-2 text-sm text-gray-600">
                                                <?php echo e('Select or browse to upload ' . str_replace('_', ' ', $key)); ?>

                                            </p>
                                        </div>
                                    </template>

                                    <!-- Progress Bar (New) -->
                                    <template x-if="isUploading">
                                        <div class="w-full mt-3">
                                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-info-500 rounded-full"
                                                    :style="`width: ${progress}%`"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 text-center mt-1"
                                                x-text="`Uploading: ${progress}%`"></p>
                                        </div>
                                    </template>

                                    <!-- File Input -->
                                    <input x-ref="imageInput" type="file" class="hidden" :accept="imageExtensions"
                                        x-on:change="handleFileChange" wire:model="<?php echo e($key); ?>"
                                        x-on:livewire-upload-start="uploadStarted()"
                                        x-on:livewire-upload-finish="uploadFinished()"
                                        x-on:livewire-upload-error="uploadError()"
                                        x-on:livewire-upload-progress="uploadProgress($event.detail.progress)"
                                        wire:ignore />
                                </div>

                                <!-- Error Message -->
                                <!--[if BLOCK]><![endif]--><?php if($errors->any()): ?>
                                <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['for' => ''.e($key).'','class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($key).'','class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                                <?php else: ?>
                                <p x-show="errorMessage" class="text-danger-500 text-sm mt-2" x-text="errorMessage">
                                </p>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                            <!-- Delete Confirmation Modal -->
                            <div x-data="confirmationModal()" x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto"
                                style="display: none;">
                                <div
                                    class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Overlay -->
                                    <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity"
                                        aria-hidden="true">
                                        <!-- Gradient Overlay -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-br from-gray-500/60 to-gray-700/60 dark:from-slate-900/80 dark:to-slate-800/80 backdrop-blur-sm">
                                        </div>
                                    </div>

                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                        aria-hidden="true">&#8203;</span>

                                    <!-- Modal Container -->
                                    <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        class="inline-block align-bottom bg-white dark:bg-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <div class="p-6">
                                            <div class="flex items-start space-x-4">
                                                <!-- Icon Container -->
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="h-12 w-12 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                                                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-exclamation-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-7 w-7 text-danger-600 dark:text-danger-400']); ?>
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

                                                <!-- Content -->
                                                <div class="flex-1">
                                                    <h3
                                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                                        <?php echo e(t('delete_image')); ?>

                                                    </h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                                        <?php echo e(t('delete_image_description')); ?>

                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div
                                            class="bg-gray-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end space-x-3">
                                            <button @click="closeModal()" type="button"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                <?php echo e(t('cancel')); ?>

                                            </button>
                                            <button @click="confirmAndClose()" type="button"
                                                class="px-4 py-2 text-sm font-medium text-white bg-danger-600 dark:bg-danger-700 rounded-lg hover:bg-danger-700 dark:hover:bg-danger-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500">
                                                <?php echo e(t('delete')); ?>

                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <!--[if BLOCK]><![endif]--><?php if(checkPermission('admin.website_settings.edit')): ?>
                             <?php $__env->slot('footer', null, ['class' => 'bg-slate-50 dark:bg-transparent rounded-b-lg p-4']); ?> 
                                <div class="flex  justify-end ">
                                    <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['type' => 'submit','size' => 'md','class' => 'inline-flex items-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'md','class' => 'inline-flex items-center']); ?>
                                        <?php echo e(t('save_changes')); ?>

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
                                </div>
                             <?php $__env->endSlot(); ?>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
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
            </form>
        </div>
    </div>
</div>
<!-- File Handling Logic -->
<script>
    function formComponent() {
        return {
            validateAndSubmit() {
                let hasErrors = false;
                const uploadSections = document.querySelectorAll('.upload-section');
                uploadSections.forEach(section => {
                    const data = Alpine.$data(section);
                    if (data.errorMessage) {
                        hasErrors = true;
                        section.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });

                if (hasErrors) {
                    alert("<?php echo e(t('fix_error_discription')); ?>");
                    return;
                }

                this.$wire.save();
            }
        };
    }

    function fileUploader(initialKey) {
        return {
            key: initialKey,
            file: null,
            preview: null,
            errorMessage: '',
            imageExtensions: '.png,.jpg,.jpeg',
            isUploading: false,
            progress: 0,

            init() {
                this.imageExtensions = this.imageExtensions.split(',')
                    .map(ext => ext.trim())
                    .join(', ');
            },

            handleFileChange(event) {
                this.errorMessage = '';
                this.file = event.target.files[0];

                if (!this.file) return;

                const fileExt = '.' + this.file.name.split('.').pop().toLowerCase();

                // Get allowed extensions with proper trimming
                const allowedExtensions = this.imageExtensions.split(',')
                    .map(ext => ext.trim());

                // Validate file extension
                if (!allowedExtensions.includes(fileExt)) {
                    this.errorMessage = "<?php echo e(t('invalid_file_type')); ?>" + " " + allowedExtensions.join(', ');
                    this.clearImage();
                    return;
                }

                // Validate file size (5 MB = 5 * 1024 * 1024 bytes)
                const maxFileSize = 5 * 1024 * 1024;
                if (this.file.size > maxFileSize) {
                    this.errorMessage = `<?php echo e(t('file_size_exceeds')); ?> ${this.formatFileSize(maxFileSize)}`;
                    this.clearImage();
                    return;
                }

                // Validate dimensions for cover_page_image
                if (this.key === 'cover_page_image') {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            if (img.width !== 729 || img.height !== 152) {
                                this.errorMessage = "";
                                this.clearImage();
                                return;
                            }
                            this.preview = e.target.result;
                        };
                        img.onerror = () => {
                            this.errorMessage = "Invalid image file.";
                            this.clearImage();
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(this.file);
                    return; // Prevent further execution until dimension check is done
                }

                // Preview the image for other keys
                const reader = new FileReader();
                reader.onload = (e) => this.preview = e.target.result;
                reader.readAsDataURL(this.file);
            },
            formatFileSize(bytes) {
                if (bytes < 1024) return `${bytes} bytes`;
                if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(2)} KB`;
                return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
            },

            clearImage() {
                this.preview = null;
                this.file = null;
                event.target.value = '';
                if (this.$refs.imageInput) this.$refs.imageInput.value = '';
            },

            // New progress tracking methods
            uploadStarted() {
                this.isUploading = true;
                this.progress = 0;
            },

            uploadFinished() {
                this.isUploading = false;
                this.progress = 100;
                // Reset progress after a short delay
                setTimeout(() => {
                    this.progress = 0;
                }, 1000);
            },

            uploadError() {
                this.isUploading = false;
                this.errorMessage = "<?php echo e(t('upload_failed_try_again')); ?>";
            },

            uploadProgress(progress) {
                this.progress = progress;
            },

            // Delete confirmation
            confirmDelete(event, callback) {
                event.stopPropagation();
                const modal = Alpine.$data(document.querySelector('[x-data="confirmationModal()"]'));
                modal.openModal(callback);
            }
        };
    }

    // New confirmation modal component
    function confirmationModal() {
        return {
            isOpen: false,
            confirmCallback: null,

            openModal(callback) {
                this.isOpen = true;
                this.confirmCallback = callback;
            },

            closeModal() {
                this.isOpen = false;
            },

            confirmAndClose() {
                if (typeof this.confirmCallback === 'function') {
                    this.confirmCallback();
                }
                this.closeModal();
            }
        };
    }
</script><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/livewire/admin/settings/website/theme-settings.blade.php ENDPATH**/ ?>