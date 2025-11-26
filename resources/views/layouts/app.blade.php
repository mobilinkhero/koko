<!DOCTYPE html>
@php
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
@endphp
<html lang="{{ $locale }}" class="h-full" x-data="{ theme: $persist('light') }"
    x-bind:class="{
        'dark': theme === 'dark' || (theme === 'system' && window.matchMedia(
                '(prefers-color-scheme: dark)')
            .matches)
    }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $metaTitle . $pageTitle }}
    </title>

    <meta name="description" content="{{ $metaDescription . $pageTitle }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $favicon ?? url('./img/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $favicon ?? url('./img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ $favicon ?? url('./img/favicon-192x192.png') }}">
    <link rel="apple-touch-icon" href="{{ $favicon ?? url('./img/apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Theme Style CSS -->
    <link id="theme-style-css" rel="stylesheet" href="{{ route('theme-style-css') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap">

    @livewireStyles
    @include(request()->routeIs('tenant.*') ? 'components.tenant-head-section' : 'components.admin-head-section')
    @vite(['resources/css/app.css', request()->routeIs('tenant.*') ? 'resources/css/tenant-app.css' : 'resources/css/admin-app.css'])
    @vite(\Nwidart\Modules\Module::getAssets())


    {{-- JS leaflet --}}
    <script>
        window.pusherConfig = {
            key: '{{ $pusherSettings['app_key'] ?? '' }}',
            cluster: '{{ $pusherSettings['cluster'] ?? '' }}',
            notification_enabled: {{ !empty($pusherSettings['real_time_notify']) ? 'true' : 'false' }},
            desktop_notification: {{ !empty($pusherSettings['desk_notify']) ? 'true' : 'false' }},
            auto_dismiss_notification: {{ !empty($pusherSettings['dismiss_desk_notification']) ? $pusherSettings['dismiss_desk_notification'] : 0 }},
            notification_icon: '{{ $favicon ?? url('./img/wm-notification.png') }}'
        };

        // Make date/time settings available to JavaScript
        window.dateTimeSettings = @json($dateTimeSettings);
        var date_format = window.dateTimeSettings.dateFormat;
        var is24Hour = window.dateTimeSettings.is24Hour;
        var time_format = window.dateTimeSettings.is24Hour ? 'h:i' : 'h:i K';
        var defaultCountryCode = '{{ $countryCode['iso2'] ?? 'in' }}';
        var tenantId = "{{ $tenantId }}";
        var tenantSubdomain = "{{ $tenantSubdomain }}";
        var flow_create_permission =
            {{ checkPermission('tenant.bot_flow.create') || checkPermission('tenant.bot_flow.edit') ? 'true' : 'false' }};
    </script>

    @stack('styles')
</head>

<body class="h-full antialiased bg-gray-50 font-sans dark:bg-slate-800" x-data="{ theme: $persist('light') }" x-init="if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}">

    <div id="main" x-data="{
        open: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
    }" x-init=" @if (request()->routeIs('admin.*')) sidebarCollapsed = false;

        @else
            sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true'; @endif window.addEventListener('sidebar-state-changed', (e) => {
         sidebarCollapsed = e.detail.collapsed;
     });" @keydown.window.escape="open = false"
        class="min-h-full flex" x-cloak>
        @if (request()->routeIs('tenant.*'))
            <livewire:tenant.partials.tenant-sidebar-navigation />
        @else
            <livewire:admin.partials.admin-sidebar-navigation />
        @endif

        <!-- Page Content -->
        <div class=" flex flex-col w-0 flex-1 transition-all duration-200"
            :class="sidebarCollapsed ? 'lg:pl-[4.7rem]' : 'lg:pl-[15rem]'">
            {{-- Header --}}
            @if (request()->routeIs('tenant.*'))
                <livewire:tenant.partials.tenant-header-navigation />
            @else
                <livewire:admin.partials.admin-header-navigation />
            @endif
            <div id="apps">
                @if (request()->routeIs('tenant.chat'))
                    <div class="p-2 ">
                        {{ $slot }}
                    </div>
                @else
                    <div class="p-6 " :class="sidebarCollapsed ? 'md:px-6' : 'md:px-6'">
                        {{ $slot }}
                    </div>
                @endif
            </div>
            </main>
        </div>
        <x-notification />
    </div>

    <!-- Scripts -->
    @livewireScripts
    <!-- Pass translations to JavaScript -->
    <script>
        window.translations = @json($translations ?? getLanguageJson(app('App\Services\LanguageService')->resolveLanguage()));
        window.appLocale = @json($locale ?? app('App\Services\LanguageService')->resolveLanguage());
        window.subdomain = @json(tenant_subdomain() ?? 'admin');
        window.isTenant = @json(tenant_check());
        window.isSuperadmin = @json(check_is_superadmin());
    </script>

    @vite(['resources/js/app.js', request()->routeIs('tenant.*') ? 'resources/js/tenant-app.js' : 'resources/js/admin-app.js'])
    @stack('scripts')
</body>

</html>
