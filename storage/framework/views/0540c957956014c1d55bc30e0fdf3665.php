<?php
    // Batch load all theme settings to avoid multiple database queries
    $themeSettings = get_batch_settings([
        'theme.seo_meta_title',
        'theme.seo_meta_description',
        'theme.author_name',
        'theme.og_title',
        'theme.og_description',
        'theme.site_logo',
        'theme.favicon',
        'system.site_name',
        'system.site_url',
        'theme.customCss',
        'theme.custom_js_header',
    ]);

    // Use cached settings with fallbacks
    $seoTitle = $themeSettings['theme.seo_meta_title'] ?? config('app.name');
    $seoDescription = $themeSettings['theme.seo_meta_description'] ?? 'Default description for the website.';
    $authorName = $themeSettings['theme.author_name'] ?? config('app.name');
    $ogTitle = $themeSettings['theme.og_title'] ?? config('app.name');
    $ogDescription = $themeSettings['theme.og_description'] ?? 'Default description for the website.';
    $siteLogo = $themeSettings['theme.site_logo']
        ? Storage::url($themeSettings['theme.site_logo'])
        : asset('img/light_logo.png');
    $faviconPath = $themeSettings['theme.favicon']
        ? Storage::url($themeSettings['theme.favicon'])
        : asset('img/favicon.png');
    $siteName = $themeSettings['system.site_name'] ?? config('app.name');
    $siteUrl = $themeSettings['system.site_url'] ?? env('APP_URL');
    $customCss = $themeSettings['theme.customCss'];
    $headerJs = $themeSettings['theme.custom_js_header'];
?>

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($seoTitle); ?></title>

<!-- Meta SEO -->
<meta name="title" content="<?php echo e($seoTitle); ?>">
<meta name="description" content="<?php echo e($seoDescription); ?>">
<meta name="robots" content="index, follow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="language" content="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<meta name="author" content="<?php echo e($authorName); ?>">

<!-- Social media share -->
<meta property="og:title" content="<?php echo e($ogTitle); ?>">
<meta property="og:site_name" content="<?php echo e($siteName); ?>">
<meta property="og:url" content="<?php echo e($siteUrl); ?>">
<meta property="og:description" content="<?php echo e($ogDescription); ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="<?php echo e($siteLogo); ?>">
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="<?php echo e(env('APP_NAME')); ?>" />
<meta name="twitter:creator" content="<?php echo e($authorName); ?>" />

<!-- Favicon -->

<link rel="icon" type="image/png" sizes="32x32" href="<?php echo e($faviconPath); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo e($faviconPath); ?>">
<link rel="apple-touch-icon" href="<?php echo e($faviconPath); ?>">

<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap">
<link id="theme-style-css" rel="stylesheet" href="<?php echo e(route('theme-style-css')); ?>">
<!-- Styles / Scripts -->
<?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php else: ?>
<?php endif; ?>
<?php if(!empty($customCss)): ?>
    <style>
        <?php echo $customCss; ?>

    </style>
<?php endif; ?>
<?php if(!empty($headerJs)): ?>
    <?php echo $headerJs; ?>

<?php endif; ?>
<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

<?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/themes/thecore/components/elements/head.blade.php ENDPATH**/ ?>