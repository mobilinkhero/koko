@php
    // Batch load theme settings to avoid multiple database queries
    $themeSettings = get_batch_settings([
        'theme.site_logo',
        //'website.seo_meta_title'
    ]);
    $siteLogo = $themeSettings['theme.site_logo'] ? Storage::url($themeSettings['theme.site_logo']) : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('themes.thecore.components.elements.head')
</head>

<body class="flex flex-col min-h-screen">

        <header class="fixed w-full">
            <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900" x-data="{ open: false, activeSection: '' }">
                <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
                    <a href="{{ env('APP_URL') }}" class="flex items-center">
                        <img src="{{ $siteLogo ?: asset('img/light_logo.png') }}"
                            class="w-24 sm:w-28 md:w-32 lg:w-40 xl:w-60 h-auto" />
                    </a>
                    <div class="flex items-center lg:order-2">
                        <div class="hidden mt-2 mr-4 sm:inline-block">

                        </div>

                        <div class="flex items-center gap-3">
                            @if (!Auth::check())
                                <a href="{{ route('login') }}">
                                    <x-button.primary>{{ t('login') }} </x-button.primary></a>
                                <a href="{{ route('register') }}">
                                    <x-button.primary>{{ t('register') }} </x-button.primary></a>
                            @else
                                <a
                                    href="{{ Auth::user()->user_type === 'tenant' ? tenant_route('tenant.dashboard') : route('admin.dashboard') }}">
                                    <x-button.primary>{{ t('dashboard') }} </x-button.primary></a>
                            @endif
                        </div>

                        <button x-on:click="open = !open" type="button"
                            class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                            aria-controls="mobile-menu-2" aria-expanded="false">
                            <x-heroicon-o-bars-3 class="h-6 w-6 font-semibold" />
                        </button>
                    </div>
                    <div :class="{ 'block': open, 'hidden': !open }"
                        class="items-center justify-between hidden w-full lg:flex lg:w-auto lg:order-1"
                        id="mobile-menu-2">
                        <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-12 lg:mt-0">
                            <li>
                                <a href="{{ route('home') . '#home' }}"
                                    :class="activeSection === 'home'
                                        ?
                                        'block py-2 pl-3 pr-4 rounded bg-[#4f46e5] text-white lg:bg-transparent lg:text-primary-600 lg:p-0 dark:text-white' :
                                        'block py-2 pl-3 pr-4 rounded text-gray-700 border-b border-gray-100 hover:bg-gray-50 lg:hover:bg-transparent lg:border-0 lg:hover:text-primary-600 lg:p-0 dark:text-gray-400 lg:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white lg:dark:hover:bg-transparent dark:border-gray-700'">
                                    {{ t('home') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('home') . '#features' }}"
                                    :class="activeSection === 'features'
                                        ?
                                        'block py-2 pl-3 pr-4 rounded bg-[#4f46e5] text-white lg:bg-transparent lg:text-primary-600 lg:p-0 dark:text-white' :
                                        'block py-2 pl-3 pr-4 rounded text-gray-700 border-b border-gray-100 hover:bg-gray-50 lg:hover:bg-transparent lg:border-0 lg:hover:text-primary-600 lg:p-0 dark:text-gray-400 lg:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white lg:dark:hover:bg-transparent dark:border-gray-700'">
                                    {{ t('features') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('home') . '#pricing' }}"
                                    :class="activeSection === 'pricing'
                                        ?
                                        'block py-2 pl-3 pr-4 rounded bg-[#4f46e5] text-white lg:bg-transparent lg:text-primary-600 lg:p-0 dark:text-white' :
                                        'block py-2 pl-3 pr-4 rounded text-gray-700 border-b border-gray-100 hover:bg-gray-50 lg:hover:bg-transparent lg:border-0 lg:hover:text-primary-600 lg:p-0 dark:text-gray-400 lg:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white lg:dark:hover:bg-transparent dark:border-gray-700'">
                                    {{ t('pricing') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('home') . '#faq' }}"
                                    :class="activeSection === 'faq'
                                        ?
                                        'block py-2 pl-3 pr-4 rounded bg-[#4f46e5] text-white lg:bg-transparent lg:text-primary-600 lg:p-0 dark:text-white' :
                                        'block py-2 pl-3 pr-4 rounded text-gray-700 border-b border-gray-100 hover:bg-gray-50 lg:hover:bg-transparent lg:border-0 lg:hover:text-primary-600 lg:p-0 dark:text-gray-400 lg:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white lg:dark:hover:bg-transparent dark:border-gray-700'">
                                    {{ t('faq') }}
                                </a>
                            </li>
                            <x-frontend-menu />
                        </ul>
                    </div>

                </div>
            </nav>

        </header>
  
    <!-- Page Content -->
    <main class="flex-1 p-4 pt-[70px]">
        <!-- Main Content -->
        @yield('content')
    </main>
    @php
        // Batch load site logo setting to avoid multiple database queries
        $themeSettings = get_batch_settings(['theme.site_logo']);
        $siteLogo = $themeSettings['theme.site_logo']
            ? Storage::url($themeSettings['theme.site_logo'])
            : asset('img/light_logo.png');
    @endphp

     <!-- Footer Start block -->
    @include('themes.thecore.components.elements.footer')
    <!-- Footer End block -->

    @livewireScripts
</body>

</html>
