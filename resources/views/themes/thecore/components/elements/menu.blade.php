@php
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
@endphp

@if ($announcement->isEnable)
<div class="py-3 {{ !$announcement->background_color ? $defaultBg : '' }}" style="{{ $bgStyle }}">
    <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-4">
        <p class="font-medium text-center {{ !$announcement->message_color ? $defaultTextColor : '' }}"
            style="{{ $textColor }}">

            {{ $announcement->message }}
        </p>
        @if ($announcement->link)
        <a href="{{ $announcement->link }}"
            class="px-4 py-1.5 text-sm font-semibold rounded-full {{ !$announcement->link_text_color ? $defaultlinkColor : '' }} bg-white shadow-md hover:shadow-lg transition-all transform hover:scale-105"
            style="{{ $linktextColor }}">
            {{ $announcement->link_text }}
        </a>
        @endif
    </div>
</div>
@endif

<header class="{{ $announcement->isEnable ? '' : 'fixed' }} w-full z-[100]">
    <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900" x-data="{ open: false }">
        <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
            <a href="{{ env('APP_URL') }}" class="flex items-center">
                <img src="{{ $siteLogo }}" class="w-24 sm:w-28 md:w-32 lg:w-40 xl:w-60 h-auto" />
            </a>
            <div class="flex items-center lg:order-2">
                <div class="hidden mt-2 mr-4 sm:inline-block">

                </div>
                <div class="flex items-center gap-3">
                    @if (!Auth::check())
                    <a href="{{ route('login') }}">
                        <x-button.primary>{{ t('login') }} </x-button.primary>
                    </a>
                    <a href="{{ route('register') }}">
                        <x-button.primary>{{ t('register') }} </x-button.primary>
                    </a>
                    @else
                    <a
                        href="{{ Auth::user()->user_type === 'tenant' ? tenant_route('tenant.dashboard') : route('admin.dashboard') }}">
                        <x-button.primary>{{ t('dashboard') }} </x-button.primary>
                    </a>
                    @endif
                </div>
                <button x-on:click="open = !open" type="button"
                    class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="mobile-menu-2" aria-expanded="false">
                    <x-heroicon-o-bars-3 class="h-6 w-6 font-semibold" />
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
                    <x-frontend-menu />
                </ul>
            </div>
        </div>
    </nav>
</header>