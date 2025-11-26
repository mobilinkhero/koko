<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'user' => null,
'collapsed' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
'user' => null,
'collapsed' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
$user = $user ?? auth()->user();
$adminThemeSettings = get_batch_settings(['theme.site_logo', 'theme.favicon', 'theme.dark_logo']);

// Helper functions for logo URLs
$getLogo = function() use ($adminThemeSettings) {
return $adminThemeSettings['theme.site_logo']
? Storage::url($adminThemeSettings['theme.site_logo'])
: url('./img/light_logo.png');
};

$getFavicon = function() use ($adminThemeSettings) {
return $adminThemeSettings['theme.favicon']
? Storage::url($adminThemeSettings['theme.favicon'])
: url('./img/favicon-32x32.png');
};
?>

<div style="color-scheme: dark;" x-data="{
    isCollapsed: <?php echo e($collapsed ? 'true' : 'false'); ?>,
    setupMenu: false,
    mobileOpen: false,
    toggleSidebar() {
        this.isCollapsed = !this.isCollapsed;
        // Store state in localStorage
        localStorage.setItem('sidebar_collapsed', this.isCollapsed ? '1' : '0');
    },
    init() {
        // Restore state from localStorage
        const stored = localStorage.getItem('sidebar_collapsed');
        if (stored !== null) {
            this.isCollapsed = stored === '1';
        }
    }
}">
    
    <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
    <div x-cloak x-show="open" class="relative z-40 lg:hidden" role="dialog" aria-modal="true"
        x-data="{ mobileOpen: false }">
        <div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-on:click="open = false"
            class="fixed inset-0 bg-slate-600 bg-opacity-75"></div>

            <?php
                $urls = [
                    'admin.users.*',
                    'admin.roles.*',
                    'admin.department.*',
                    'admin.languages',
                    'admin.currencies.*',
                    'admin.taxes.*',
                    'admin.faqs',
                    'admin.languages',
                    'admin.pages',
                    'admin.email-template.*',
                    'admin.logs.index',
                    'admin.modules.*',
                    'admin.theme',
                    'admin.coupons.*',
                ];

                $activeSetupMenus = apply_filters('active_admin_setup_menus', $urls);
            ?>
        <div class="fixed inset-0 flex z-40">
            <!-- Mobile Menu (Overlapping Open Menu) -->
            <div x-show="mobileOpen"
                class="absolute top-0 left-0 z-50 lg:hidden sm:w-80 w-60 h-full bg-white dark:bg-slate-800 "
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform -translate-x-full" x-init="mobileOpen = <?php echo e(json_encode(
                    request()->routeIs(...$activeSetupMenus),
                )); ?>">

                <!-- Close Button -->
                <div class="flex justify-between items-center py-4 flex-shrink-0 px-5 bg-white dark:bg-slate-800">
                    <span class="text-lg font-semibold text-gray-600 dark:text-slate-300">
                        <?php echo e(t('setup')); ?>

                    </span>
                    <button x-on:click.stop="mobileOpen = false" class="text-gray-500 dark:text-slate-400">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
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

                <div class="flex-1 flex flex-col overflow-y-auto">
                    <nav class="flex-1 px-2">
                        <?php echo e($mobileSetupMenu ?? ''); ?>

                    </nav>
                </div>
            </div>

            <!-- Regular sidebar for mobile -->
            <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                class="relative flex-1 flex flex-col max-w-xs w-full bg-white dark:bg-slate-800"
                x-on:click.away="open = false">


                <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                    <div class="flex-shrink-0 flex items-center px-4">
                        <img class="h-8 w-auto" src="<?php echo e($getLogo()); ?>" alt="<?php echo e(config('app.name')); ?>">
                    </div>
                    <nav class="mt-5 px-2 space-y-1">
                        <?php echo e($mobileSidebar ?? $slot); ?>

                    </nav>
                </div>
            </div>

            <div class="flex-shrink-0 w-14"></div>
        </div>
    </div>

    <!-- Setup Menu Overlay -->
    <div x-show="setupMenu" x-cloak x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in-out duration-200 transform"
        x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-full opacity-0"
        class="lg:flex lg:fixed lg:inset-y-0 z-40 hidden" :class="{ 'lg:w-16': isCollapsed, 'lg:w-60': !isCollapsed }" x-init="setupMenu = <?php echo e(json_encode(
        request()->routeIs(...$activeSetupMenus),
    )); ?>">

        <div
            class="flex-1 flex flex-col min-h-0 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 ">
            <div class="flex items-center justify-between px-4 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" x-show="!isCollapsed"><?php echo e(t('setup')); ?>

                </h3>
                <button x-on:click="setupMenu = false"
                    class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6']); ?>
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
            <div class="flex-1 flex flex-col overflow-y-auto">
                <nav class="mt-2 flex-1 px-2 bg-white dark:bg-slate-800 space-y-2">
                    <?php echo e($mobileSetupMenu ?? ''); ?>

                </nav>
            </div>
        </div>
    </div>

    <!-- Main Sidebar -->
    <div class="hidden lg:flex lg:w-60 lg:flex-col lg:fixed lg:inset-y-0 z-20"
        :class="{ 'lg:w-16': isCollapsed, 'lg:w-60': !isCollapsed }" x-show="!setupMenu">
        <div
            class="flex-1 flex flex-col min-h-0 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700">

            <!-- Logo Section -->
            <div class="flex justify-center transition-all duration-300 ease-in-out">
               <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center bg-white dark:bg-slate-800 pt-2">
                        <img x-bind:src="theme === 'light' || (theme === 'system' && window.matchMedia(
                                    '(prefers-color-scheme: light)')
                                .matches) ?
                            '<?php echo e(!empty($adminThemeSettings['theme.site_logo']) ? Storage::url($adminThemeSettings['theme.site_logo']) : url('./img/light_logo.png')); ?>' :
                            '<?php echo e(!empty($adminThemeSettings['theme.dark_logo']) ? Storage::url($adminThemeSettings['theme.dark_logo']) : url('./img/dark_logo.png')); ?>'"
                           alt="<?php echo e(config('app.name')); ?>" class="h-14 my-1 object-contain" x-cloak>
                    </a>
                <img x-show="isCollapsed" class="h-16 object-contain mx-auto" src="<?php echo e($getFavicon()); ?>"
                    alt="<?php echo e(config('app.name')); ?>">
            </div>

            <!-- Scrollable Navigation -->
            <nav class="flex-1 overflow-y-auto p-2 bg-white dark:bg-slate-800 space-y-1 scrollbar-visible text-primary-500">
                <?php echo e($slot); ?>

            </nav>

        </div>
    </div>

</div><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/components/admin/sidebar-layout.blade.php ENDPATH**/ ?>