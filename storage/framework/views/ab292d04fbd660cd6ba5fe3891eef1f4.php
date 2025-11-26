<?php
$announcement = get_settings_by_group('announcement');

// Batch load theme settings to avoid multiple database queries
$themeSettings = get_batch_settings(['theme.site_logo']);
$siteLogo = $themeSettings['theme.site_logo'] ? Storage::url($themeSettings['theme.site_logo']) :
asset('img/light_logo.png');

$defaultBg = 'bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500';
$bgStyle = $announcement->background_color ? "background-color: {$announcement->background_color};" : '';
$defaultTextColor = 'text-white';
$textColor = $announcement->message_color ? "color: {$announcement->message_color};" : '';
$defaultlinkColor = 'text-purple-500';
$linktextColor = $announcement->link_text_color ? "color: {$announcement->link_text_color};" : '';
?>

<?php if($announcement->isEnable): ?>
<div class="py-3 <?php echo e(!$announcement->background_color ? $defaultBg : ''); ?>" style="<?php echo e($bgStyle); ?>">
    <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-4">
        <p class="font-medium text-center <?php echo e(!$announcement->message_color ? $defaultTextColor : ''); ?>"
            style="<?php echo e($textColor); ?>">

            <?php echo e($announcement->message); ?>

        </p>
        <?php if($announcement->link): ?>
        <a href="<?php echo e($announcement->link); ?>"
            class="px-4 py-1.5 text-sm font-semibold rounded-full <?php echo e(!$announcement->link_text_color ? $defaultlinkColor : ''); ?> bg-white shadow-md hover:shadow-lg transition-all transform hover:scale-105"
            style="<?php echo e($linktextColor); ?>">
            <?php echo e($announcement->link_text); ?>

        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<header class="<?php echo e($announcement->isEnable ? '' : 'fixed'); ?> w-full z-[100]">
    <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900" x-data="{ open: false }">
        <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
            <a href="<?php echo e(env('APP_URL')); ?>" class="flex items-center">
                <img src="<?php echo e($siteLogo); ?>" class="w-24 sm:w-28 md:w-32 lg:w-40 xl:w-60 h-auto" />
            </a>
            <div class="flex items-center lg:order-2">
                <div class="hidden mt-2 mr-4 sm:inline-block">

                </div>
                <div class="flex items-center gap-3">
                    <?php if(!Auth::check()): ?>
                    <a href="<?php echo e(route('login')); ?>">
                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(t('login')); ?>  <?php echo $__env->renderComponent(); ?>
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
                    <a href="<?php echo e(route('register')); ?>">
                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(t('register')); ?>  <?php echo $__env->renderComponent(); ?>
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
                    <?php else: ?>
                    <a
                        href="<?php echo e(Auth::user()->user_type === 'tenant' ? tenant_route('tenant.dashboard') : route('admin.dashboard')); ?>">
                        <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(t('dashboard')); ?>  <?php echo $__env->renderComponent(); ?>
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
                    <?php endif; ?>
                </div>
                <button x-on:click="open = !open" type="button"
                    class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="mobile-menu-2" aria-expanded="false">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-bars-3'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 font-semibold']); ?>
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
            <div :class="{ 'block': open, 'hidden': !open }"
                class="items-center justify-between hidden w-full lg:flex lg:w-auto lg:order-1" id="mobile-menu-2">
                <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-12 lg:mt-0">
                    <li>
                        <a href="#home" x-on:click="activeSection = 'home'"
                            :class="{ 'bg-primary-500 text-white lg:bg-transparent lg:text-primary-500': activeSection === 'home' }"
                            class="block py-2 pl-3 pr-4 rounded lg:p-0">Home</a>
                    </li>
                    <li>
                        <a href="#features" x-on:click="activeSection = 'features'"
                            :class="{ 'bg-primary-500 text-white lg:bg-transparent lg:text-primary-500': activeSection === 'features' }"
                            class="block py-2 pl-3 pr-4 rounded lg:p-0">Features</a>
                    </li>
                    <li>
                        <a href="#pricing" x-on:click="activeSection = 'pricing'"
                            :class="{ 'bg-primary-500 text-white lg:bg-transparent lg:text-primary-500': activeSection === 'pricing' }"
                            class="block py-2 pl-3 pr-4 rounded lg:p-0">Pricing</a>
                    </li>
                    <li>
                        <a href="#faq" x-on:click="activeSection = 'faq'"
                            :class="{ 'bg-primary-500 text-white lg:bg-transparent lg:text-primary-500': activeSection === 'faq' }"
                            class="block py-2 pl-3 pr-4 rounded lg:p-0">FAQs</a>
                    </li>
                    <?php if (isset($component)) { $__componentOriginal9919cf73e94d78a2db6c95b3213e460d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9919cf73e94d78a2db6c95b3213e460d = $attributes; } ?>
<?php $component = App\View\Components\FrontendMenu::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FrontendMenu::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9919cf73e94d78a2db6c95b3213e460d)): ?>
<?php $attributes = $__attributesOriginal9919cf73e94d78a2db6c95b3213e460d; ?>
<?php unset($__attributesOriginal9919cf73e94d78a2db6c95b3213e460d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9919cf73e94d78a2db6c95b3213e460d)): ?>
<?php $component = $__componentOriginal9919cf73e94d78a2db6c95b3213e460d; ?>
<?php unset($__componentOriginal9919cf73e94d78a2db6c95b3213e460d); ?>
<?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header><?php /**PATH /home/adphwiudpl/mark.chatvoo.com/resources/views/themes/thecore/components/elements/menu.blade.php ENDPATH**/ ?>