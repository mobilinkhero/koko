<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ theme: $persist('light') }"
    x-bind:class="{
        'dark': theme === 'dark' || (theme === 'system' && window.matchMedia(
                '(prefers-color-scheme: dark)')
            .matches)
    }">

<head>
    @include(request()->routeIs('tenant.*') ? 'components.tenant-head-section' : 'components.admin-head-section')
    @vite(['resources/css/app.css', request()->routeIs('tenant.*') ? 'resources/css/tenant-app.css' : 'resources/css/admin-app.css'])
</head>

<body>
    <div class="bg-gray-50 font-sans">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div
                class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-500 opacity-100">
                <div class="px-8 pt-8 pb-6 text-center">
                    <!-- Lock Icon -->
                    <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-primary-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <h1 class="text-2xl font-bold text-gray-800 mb-3">{{ t('registrations_are_temporarily_closed') }}
                    </h1>
                    <p class="text-gray-600 mb-8">{{ t('we_are_not_accepting_new_tenant_registration') }}</p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 justify-center">
                        <a href="/"
                            class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 hover:shadow-lg transform transition-all duration-300 hover:-translate-y-1">
                            {{ t('go_back_home') }}
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                @php
                
                    $settings = get_batch_settings(['email.sender_email']);
                    $senderEmail = $settings['email.sender_email'] ?? '';
                @endphp

                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-500">
                        {{ t('for_support_you_can_contact_the_admin_at') }}
                        <a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
