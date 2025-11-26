@php
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
@endphp

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $seoTitle }}</title>

<!-- Meta SEO -->
<meta name="title" content="{{ $seoTitle }}">
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="index, follow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="language" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="author" content="{{ $authorName }}">

<!-- Social media share -->
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:url" content="{{ $siteUrl }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:type" content="website">
<meta property="og:image" content="{{ $siteLogo }}">
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="{{ env('APP_NAME') }}" />
<meta name="twitter:creator" content="{{ $authorName }}" />

<!-- Favicon -->

<link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconPath }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconPath }}">
<link rel="apple-touch-icon" href="{{ $faviconPath }}">

<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap">
<link id="theme-style-css" rel="stylesheet" href="{{ route('theme-style-css') }}">
<!-- Styles / Scripts -->
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
@endif
@if (!empty($customCss))
    <style>
        {!! $customCss !!}
    </style>
@endif
@if (!empty($headerJs))
    {!! $headerJs !!}
@endif
@livewireStyles
