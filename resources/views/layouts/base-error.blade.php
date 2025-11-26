<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  x-data="{ theme: $persist('light') }"
    x-bind:class="{
        'dark': theme === 'dark' || (theme === 'system' && window.matchMedia(
                '(prefers-color-scheme: dark)')
            .matches)
    }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('error_page_title') }}</title>

    <meta name="description" content="{{ t('app_description') }}" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    @php

        $settings = get_batch_settings(['theme.favicon']);
        // Get the favicon path from settings
        $faviconPath = $settings['theme.favicon'] ? Storage::url($settings['theme.favicon']) : asset('img/favicon.png');
    @endphp

    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconPath }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconPath }}">
    <link rel="apple-touch-icon" href="{{ $faviconPath }}">

    <!-- Styles -->
    @livewireStyles
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div {{ $attributes->merge(['class' => 'w-full max-w-md text-center']) }}>
            {{ $slot }}
        </div>
    </div>
    <!-- Scripts -->
    @livewireScripts

    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>
