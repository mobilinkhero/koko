
<div>
    <!-- Start block  -->
    <section class="bg-white dark:bg-gray-900">
        <div
            class="grid justify-center  max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="mr-auto place-self-center lg:col-span-7">

                <div class="px-5 flex items-center mb-4 h-[70px]">
                    <h1
                        class="sm:py-[1px] font-sans px-2 flex items-center  font-semibold bg-cyan-50 border-white/70 border-2 text-neutral-600 rounded-2xl ">
                         <?php echo e($themeSettings['theme.hero_heading']); ?></h1>
                </div>

                <h1 id="heroSectionText"
                    class="mb-4 inline lg:text-3xl text-2xl min-h-[65px] font-bold font-display leading-none tracking-tight md:text-5xl xl:text-6xl dark:text-white">
                    <?php echo e($themeSettings['theme.title']); ?>

                </h1>
                <!--[if BLOCK]><![endif]--><?php if($themeSettings['theme.description']): ?>
                <p class="max-w-2xl my-4  text-gray-600 lg:mb-8 md:text-lg lg:text-lg dark:text-gray-500">
                    <?php echo $themeSettings['theme.description']; ?>

                </p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <div class="space-y-4 sm:space-y-0 ">
                    <!--[if BLOCK]><![endif]--><?php if($themeSettings['theme.primary_button_text']): ?>
                    <!--[if BLOCK]><![endif]--><?php if($themeSettings['theme.primary_button_type'] === 'outline'): ?>
                    <?php if (isset($component)) { $__componentOriginal66275c102063acc01ab84433ad21897e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal66275c102063acc01ab84433ad21897e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.outline','data' => ['href' => ''.e($themeSettings['theme.primary_button_url']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.outline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($themeSettings['theme.primary_button_url']).'']); ?>
                        <?php echo e($themeSettings['theme.primary_button_text']); ?>

                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-up-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-5 ml-2']); ?>
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
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal66275c102063acc01ab84433ad21897e)): ?>
<?php $attributes = $__attributesOriginal66275c102063acc01ab84433ad21897e; ?>
<?php unset($__attributesOriginal66275c102063acc01ab84433ad21897e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal66275c102063acc01ab84433ad21897e)): ?>
<?php $component = $__componentOriginal66275c102063acc01ab84433ad21897e; ?>
<?php unset($__componentOriginal66275c102063acc01ab84433ad21897e); ?>
<?php endif; ?>
                    <?php else: ?>
                    <a href="<?php echo e($themeSettings['theme.primary_button_url']); ?>">
                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['class' => 'sm:w-auto w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'sm:w-auto w-full']); ?>
                            <?php echo e($themeSettings['theme.primary_button_text']); ?>

                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-up-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-5 ml-2']); ?>
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
                    </a>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!--[if BLOCK]><![endif]--><?php if($themeSettings['theme.secondary_button_text']): ?>
                    <!--[if BLOCK]><![endif]--><?php if($themeSettings['theme.secondary_button_type'] === 'outline'): ?>
                    <?php if (isset($component)) { $__componentOriginal66275c102063acc01ab84433ad21897e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal66275c102063acc01ab84433ad21897e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.outline','data' => ['href' => ''.e($themeSettings['theme.secondary_button_url']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.outline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($themeSettings['theme.secondary_button_url']).'']); ?>
                        <?php echo e($themeSettings['theme.secondary_button_text']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal66275c102063acc01ab84433ad21897e)): ?>
<?php $attributes = $__attributesOriginal66275c102063acc01ab84433ad21897e; ?>
<?php unset($__attributesOriginal66275c102063acc01ab84433ad21897e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal66275c102063acc01ab84433ad21897e)): ?>
<?php $component = $__componentOriginal66275c102063acc01ab84433ad21897e; ?>
<?php unset($__componentOriginal66275c102063acc01ab84433ad21897e); ?>
<?php endif; ?>
                    <?php else: ?>
                    <a href="<?php echo e($themeSettings['theme.secondary_button_url']); ?>">
                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['class' => 'sm:w-auto w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'sm:w-auto w-full']); ?>
                            <?php echo e($themeSettings['theme.secondary_button_text']); ?>

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
                    </a>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <div class="hidden lg:mt-0 lg:col-span-5 lg:flex w-[590px] justify-center items-center">
                <?php
                // Get the image path from settings
                $imagePath = $themeSettings['theme.image_path']
                ? Storage::url($themeSettings['theme.image_path'])
                : asset('img/dummy-image/dummy_450x400.png');
                ?>
                <img src="<?php echo e($imagePath); ?>" data-aos="fade-down" data-aos-once="true" data-aos-duration="3000"
                    class="max-w-full h-auto object-contain"
                    alt="<?php echo e($themeSettings['theme.image_alt_text'] ?? 'hero image'); ?>" />
            </div>

        </div>
    </section>
    <!-- End block -->
    <!--[if BLOCK]><![endif]--><?php if(empty($themeSettings['theme.title']) &&
    empty($themeSettings['theme.primary_button_text']) &&
    empty($themeSettings['theme.secondary_button_text']) &&
    empty($themeSettings['theme.image_path'])): ?>
    <!-- Fallback when no hero section is active -->
    <section class="bg-white dark:bg-gray-900">
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-16">
            <div class="text-center">
                <p class="text-gray-500 dark:text-gray-400">No hero section available</p>
            </div>
        </div>
    </section>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/livewire/frontend/hero-section.blade.php ENDPATH**/ ?>