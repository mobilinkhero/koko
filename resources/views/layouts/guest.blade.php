<!DOCTYPE html>
@php
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
@endphp
<html lang="{{ $locale }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $metaTitle . $pageTitle }}
    </title>

    <meta name="description" content="{{ $metaDescription . $pageTitle }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->


    <link id="theme-style-css" rel="stylesheet" href="{{ route('theme-style-css') }}">
    <!-- Styles -->
    @livewireStyles
    @vite('resources/css/app.css')
    @vite(\Nwidart\Modules\Module::getAssets())
    <script>
        var defaultCountryCode = '{{ $countryCode['iso2'] ?? 'in' }}';
    </script>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div {{ $attributes->merge(['class' => ' bg-primary-50 dark:bg-gray-800 overflow-hidden w-full']) }}>
            {{ $slot }}
        </div>
    </div>
    <x-notification />

    <!-- Scripts -->
    @livewireScripts

    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>