<!DOCTYPE html>
<?php
$isTenant = tenant_check();
if ($isTenant) {
$systemSettings = tenant_settings_by_group('system');
$pusherSettings = tenant_settings_by_group('pusher');
} else {
$themeSettings = get_batch_settings([
'system.active_language',
'theme.seo_meta_title',
'theme.seo_meta_description',
'theme.favicon',
]);
}
$countryCode = get_setting('system.default_country_code');
$locale = Auth::check()
? Session::get('locale', config('app.locale'))
: ($isTenant
? $systemSettings['active_language']
: $themeSettings['system.active_language'] ?? config('app.locale'));

$metaTitle =
$themeSettings['theme.seo_meta_title'] ??
t('app_name');

$metaDescription =
$themeSettings['theme.seo_meta_description'] ??
t('app_description');

$favicon = $isTenant
? (isset($systemSettings['favicon'])
? Storage::url($systemSettings['favicon'])
: null)
: ($themeSettings['theme.favicon']
? Storage::url($themeSettings['theme.favicon'])
: null);

$pageTitle = isset($title) ? " - $title" : '';
?>
<html lang="<?php echo e($locale); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>
        <?php echo e($metaTitle . $pageTitle); ?>

    </title>

    <meta name="description" content="<?php echo e($metaDescription . $pageTitle); ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->


    <link id="theme-style-css" rel="stylesheet" href="<?php echo e(route('theme-style-css')); ?>">
    <!-- Styles -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(\Nwidart\Modules\Module::getAssets()); ?>
    <script>
        var defaultCountryCode = '<?php echo e($countryCode['iso2'] ?? 'in'); ?>';
    </script>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div <?php echo e($attributes->merge(['class' => ' bg-primary-50 dark:bg-gray-800 overflow-hidden w-full'])); ?>>
            <?php echo e($slot); ?>

        </div>
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

    <!-- Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <?php echo app('Illuminate\Foundation\Vite')('resources/js/app.js'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/layouts/guest.blade.php ENDPATH**/ ?>