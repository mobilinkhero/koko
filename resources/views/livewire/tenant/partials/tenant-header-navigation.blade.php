<div
    class="bg-white sticky top-0 z-20 flex-shrink-0 flex h-16 border-b border-slate-200 dark:border-slate-600 dark:bg-slate-800">
    <button x-on:click="open = !open" type="button"
        class="px-4 border-r border-slate-200 text-slate-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-slate-900 lg:hidden dark:border-slate-600">
        <span class="sr-only">{{ t('open_sidebar') }}</span>
        <x-heroicon-o-bars-3-bottom-left class="h-6 w-6" />
    </button>

    <div class="flex-1 px-4 flex justify-between">

        <div class="flex-1 flex ">
        </div>

        <div class="flex items-center">
            <x-button size="sm"
                class=" items-center justify-center px-3 py-2 text-sm leading-4 border border-transparent rounded-md font-medium  transition bg-slate-200 text-slate-700 truncate  dark:bg-slate-700 dark:border-slate-500 dark:text-slate-200 mr-4 hidden lg:inline-flex"
                wire:navigate href="{{ route('home') }}">
                {{ t('visit_site') }}
            </x-button>
            <x-button size="sm"
                class=" items-center justify-center px-3 py-2 text-sm leading-4 border border-transparent rounded-md font-medium  transition bg-slate-200 text-slate-700 truncate  dark:bg-slate-700 dark:border-slate-500 dark:text-slate-200 mr-4 hidden lg:inline-flex"
                wire:click="tenant_cache({{ tenant_id() }})">
                <x-heroicon-o-trash class="w-4 h-4 mr-1" />{{ t('clear_cache') }}
            </x-button>

            <x-dropdown>
                <x-slot:trigger>
                    <button
                        class="inline-flex items-center bg-white-600 py-2 text-sm font-medium text-gray-400 hover:text-slate-500">
                        <x-carbon-settings class="-ml-1 mr-2 w-6 h-6" />
                    </button>
                </x-slot:trigger>
                <x-slot:content>
                    <!-- Application Settings -->
                    @if (checkPermission('whatsmark_settings.view'))
                    <a wire:navigate href="{{ tenant_route('tenant.settings.whatsapp-auto-lead') }}"
                        class="group flex items-center px-5 py-2 text-sm font-medium rounded-r-none
                {{ in_array(request()->route()->getName(), [
                    'tenant.settings.whatsapp-auto-lead',
                    'tenant.settings.stop-bot',
                    'tenant.settings.web-hooks',
                    'tenant.settings.support-agent',
                    'tenant.settings.notification-sound',
                    'tenant.settings.ai-integration',
                    'tenant.settings.auto-clear-chat-history',
                ])
                    ? ' bg-primary-50  dark:border-primary-600 text-primary-700 dark:bg-slate-900 language'
                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white' }}">
                        <x-heroicon-o-wrench-screwdriver
                            class="mr-2 flex-shrink-0 h-6 w-6
                    {{ in_array(request()->route()->getName(), [
                        'tenant.settings.whatsapp-auto-lead',
                        'tenant.settings.stop-bot',
                        'tenant.settings.web-hooks',
                        'tenant.settings.support-agent',
                        'tenant.settings.notification-sound',
                        'tenant.settings.ai-integration',
                        'tenant.settings.auto-clear-chat-history',
                    ])
                        ? 'text-primary-600 dark:text-slate-300'
                        : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300' }}" aria-hidden="true" />
                        {{ t('application') }}
                    </a>
                    @endif

                    @if (checkPermission(['system_settings.view', 'system_settings.edit']))
                    <a wire:navigate href="{{ tenant_route('tenant.settings.general') }}"
                        class="group flex items-center px-5 py-2 text-sm font-medium  rounded-r-none
                {{ in_array(request()->route()->getName(), [
                    'tenant.settings.general',
                    'tenant.settings.email',
                    'tenant.settings.pusher',
                ])
                    ? '  bg-primary-50  dark:border-primary-600 text-primary-700 dark:bg-slate-900 dark:text-white'
                    : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white' }}">
                        <x-heroicon-o-cog
                            class="mr-2 flex-shrink-0 h-6 w-6
                    {{ in_array(request()->route()->getName(), [
                        'tenant.settings.general',
                        'tenant.settings.email',
                        'tenant.settings.pusher',
                    ])
                        ? 'text-primary-600 dark:text-slate-300'
                        : 'text-gray-500 group-hover:text-primary-700 dark:text-slate-400 group-hover:dark:text-slate-300' }}"
                            aria-hidden="true" />
                        {{ t('system') }}
                    </a>
                    @endif
                </x-slot:content>
            </x-dropdown>

            {{-- language dropdown : Start --}}
            <livewire:language-switcher />
            {{-- language dropdown : Over --}}

            <!-- Theme switcher -->
            <div class="relative mr-2">
                <x-dropdown>
                    <x-slot:trigger>
                        <button type="button"
                            class="p-1 px-3 text-slate-400 rounded-full hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500 dark:text-slate-300 dark:hover:text-slate-200 dark:focus:ring-offset-slate-800">
                            <template x-if="theme === 'light'">
                                <x-heroicon-o-sun class="w-6 h-6" />
                            </template>
                            <template x-if="theme === 'dark'">
                                <x-heroicon-o-moon class="w-6 h-6" />
                            </template>
                            <template x-if="theme === 'system'">
                                <x-heroicon-o-computer-desktop class="w-6 h-6" />
                            </template>
                        </button>
                    </x-slot:trigger>
                    <x-slot:content>
                        <x-dropdown-link x-on:click="theme = 'light'; document.documentElement.classList.remove('dark');
                        document.documentElement.classList.add('light');" role="button"
                            class="flex items-center space-x-2">
                            <x-heroicon-m-sun class="w-5 h-5" />
                            <span>{{ t('light') }}</span>
                        </x-dropdown-link>
                        <x-dropdown-link x-on:click="theme = 'dark'; document.documentElement.classList.remove('light');
                        document.documentElement.classList.add('dark');" role="button"
                            class="flex items-center space-x-2">
                            <x-heroicon-m-moon class="w-5 h-5" />
                            <span>{{ t('dark') }}</span>
                        </x-dropdown-link>
                        <x-dropdown-link x-on:click="theme = 'system'; if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            document.documentElement.classList.add('dark');
                            document.documentElement.classList.remove('light');
                        } else {
                            document.documentElement.classList.add('light');
                            document.documentElement.classList.remove('dark');
                        }" role="button" class="flex items-center space-x-2">
                            <x-heroicon-m-computer-desktop class="w-5 h-5" />
                            <span>{{ t('system') }}</span>
                        </x-dropdown-link>
                    </x-slot:content>
                </x-dropdown>
            </div>
            <!-- Profile dropdown -->
            <div class="relative">
                <x-dropdown>
                    <x-slot:trigger>
                        <button type="button"
                            class="max-w-xs flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500 dark:focus:ring-offset-slate-800"
                            aria-expanded="false" aria-haspopup="true"
                            data-tippy-content="{{ Auth()->user()->firstname .' ' . Auth()->user()->lastname }}">
                            <span class="sr-only">{{ 'open_user_menu' }}</span>
                            <img src="{{ Auth::user()?->avatar && Storage::disk('public')->exists(Auth::user()->avatar)
                                ? asset('storage/' . Auth::user()->avatar)
                                : asset('img/user-placeholder.jpg') }}" alt="{{ t('avatar') }}"
                                class="w-9 h-9 rounded-full object-cover">
                        </button>
                    </x-slot:trigger>
                    <x-slot:content>
                        <x-dropdown-link href="{{ tenant_route('tenant.profile') }}">
                            {{ t('account_profile') }}
                        </x-dropdown-link>
                        @if(tenant_on_active_plan())
                        <x-dropdown-link href="{{ tenant_route('tenant.subscription') }}">
                            {{ t('available_plans') }}
                        </x-dropdown-link>
                        @endif
                        @if(session()->has('admin_id'))
                        <x-dropdown-link href="{{ route('back.to.admin') }}">
                            {{ t('back_to_admin') }}
                        </x-dropdown-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                        this.closest('form').submit();">
                                {{ t('logout') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot:content>
                </x-dropdown>
            </div>
        </div>
    </div>
</div>