<!DOCTYPE html>
<?php
    $isTenant = tenant_check();
    $tenantId = tenant_id();
    $tenantSubdomain = tenant_subdomain();
    if ($isTenant) {
        $systemSettings = tenant_settings_by_group('system');
        $pusherSettings = tenant_settings_by_group('pusher');
    }
    $themeSettings = get_batch_settings([
        'system.active_language',
        'theme.seo_meta_title',
        'theme.seo_meta_description',
        'theme.favicon',
    ]);
    $countryCode = get_setting('system.default_country_code');
    $locale = Auth::check()
        ? Session::get('locale', config('app.locale'))
        : ($isTenant
            ? $systemSettings['active_language']
            : $themeSettings['system.active_language'] ?? config('app.locale'));

    $metaTitle = !empty($themeSettings['theme.seo_meta_title'])
        ? $themeSettings['theme.seo_meta_title']
        : t('app_name');

    $metaDescription = $themeSettings['theme.seo_meta_description'] ?? t('app_description');

    $favicon = $themeSettings['theme.favicon'] ? Storage::url($themeSettings['theme.favicon']) : null;

    $pageTitle = isset($title) ? " - $title" : '';
?>
<html lang="<?php echo e($locale); ?>" class="h-full" x-data="{ theme: $persist('light') }"
    x-bind:class="{
        'dark': theme === 'dark' || (theme === 'system' && window.matchMedia(
                '(prefers-color-scheme: dark)')
            .matches)
    }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>
        <?php echo e($metaTitle . $pageTitle); ?>

    </title>

    <meta name="description" content="<?php echo e($metaDescription . $pageTitle); ?>" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e($favicon ?? url('./img/favicon-16x16.png')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e($favicon ?? url('./img/favicon-32x32.png')); ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo e($favicon ?? url('./img/favicon-192x192.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e($favicon ?? url('./img/apple-touch-icon.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Theme Style CSS -->
    <link id="theme-style-css" rel="stylesheet" href="<?php echo e(route('theme-style-css')); ?>">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap">

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo $__env->make(request()->routeIs('tenant.*') ? 'components.tenant-head-section' : 'components.admin-head-section', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', request()->routeIs('tenant.*') ? 'resources/css/tenant-app.css' : 'resources/css/admin-app.css']); ?>
    <?php echo app('Illuminate\Foundation\Vite')(\Nwidart\Modules\Module::getAssets()); ?>


    
    <script>
        window.pusherConfig = {
            key: '<?php echo e($pusherSettings['app_key'] ?? ''); ?>',
            cluster: '<?php echo e($pusherSettings['cluster'] ?? ''); ?>',
            notification_enabled: <?php echo e(!empty($pusherSettings['real_time_notify']) ? 'true' : 'false'); ?>,
            desktop_notification: <?php echo e(!empty($pusherSettings['desk_notify']) ? 'true' : 'false'); ?>,
            auto_dismiss_notification: <?php echo e(!empty($pusherSettings['dismiss_desk_notification']) ? $pusherSettings['dismiss_desk_notification'] : 0); ?>,
            notification_icon: '<?php echo e($favicon ?? url('./img/wm-notification.png')); ?>'
        };

        // Make date/time settings available to JavaScript
        window.dateTimeSettings = <?php echo json_encode($dateTimeSettings, 15, 512) ?>;
        var date_format = window.dateTimeSettings.dateFormat;
        var is24Hour = window.dateTimeSettings.is24Hour;
        var time_format = window.dateTimeSettings.is24Hour ? 'h:i' : 'h:i K';
        var defaultCountryCode = '<?php echo e($countryCode['iso2'] ?? 'in'); ?>';
        var tenantId = "<?php echo e($tenantId); ?>";
        var tenantSubdomain = "<?php echo e($tenantSubdomain); ?>";
        var flow_create_permission =
            <?php echo e(checkPermission('tenant.bot_flow.create') || checkPermission('tenant.bot_flow.edit') ? 'true' : 'false'); ?>;
    </script>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="h-full antialiased bg-gray-50 font-sans dark:bg-slate-800" x-data="{ theme: $persist('light') }" x-init="if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}">

    <div id="main" x-data="{
        open: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
    }" x-init=" <?php if(request()->routeIs('admin.*')): ?> sidebarCollapsed = false;

        <?php else: ?>
            sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true'; <?php endif; ?> window.addEventListener('sidebar-state-changed', (e) => {
         sidebarCollapsed = e.detail.collapsed;
     });" @keydown.window.escape="open = false"
        class="min-h-full flex" x-cloak>
        <?php if(request()->routeIs('tenant.*')): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tenant.partials.tenant-sidebar-navigation', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1163859718-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php else: ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('admin.partials.admin-sidebar-navigation', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1163859718-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php endif; ?>

        <!-- Page Content -->
        <div class=" flex flex-col w-0 flex-1 transition-all duration-200"
            :class="sidebarCollapsed ? 'lg:pl-[4.7rem]' : 'lg:pl-[15rem]'">
            
            <?php if(request()->routeIs('tenant.*')): ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tenant.partials.tenant-header-navigation', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1163859718-2', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php else: ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('admin.partials.admin-header-navigation', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1163859718-3', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php endif; ?>
            <div id="apps">
                <?php if(request()->routeIs('tenant.chat')): ?>
                    <div class="p-2 ">
                        <?php echo e($slot); ?>

                    </div>
                <?php else: ?>
                    <div class="p-6 " :class="sidebarCollapsed ? 'md:px-6' : 'md:px-6'">
                        <?php echo e($slot); ?>

                    </div>
                <?php endif; ?>
            </div>
            </main>
        </div>
        <?php if (isset($component)) { $__componentOriginal0d8d3c14ebd2b92d484be47e6c018839 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839)): ?>
<?php $attributes = $__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839; ?>
<?php unset($__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d8d3c14ebd2b92d484be47e6c018839)): ?>
<?php $component = $__componentOriginal0d8d3c14ebd2b92d484be47e6c018839; ?>
<?php unset($__componentOriginal0d8d3c14ebd2b92d484be47e6c018839); ?>
<?php endif; ?>
    </div>

    <!-- Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <!-- Pass translations to JavaScript -->
    <script>
        window.translations = <?php echo json_encode($translations ?? getLanguageJson(app('App\Services\LanguageService')->resolveLanguage()), 15, 512) ?>;
        window.appLocale = <?php echo json_encode($locale ?? app('App\Services\LanguageService')->resolveLanguage(), 15, 512) ?>;
        window.subdomain = <?php echo json_encode(tenant_subdomain() ?? 'admin', 15, 512) ?>;
        window.isTenant = <?php echo json_encode(tenant_check(), 15, 512) ?>;
        window.isSuperadmin = <?php echo json_encode(check_is_superadmin(), 15, 512) ?>;
    </script>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js', request()->routeIs('tenant.*') ? 'resources/js/tenant-app.js' : 'resources/js/admin-app.js']); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/layouts/app.blade.php ENDPATH**/ ?>