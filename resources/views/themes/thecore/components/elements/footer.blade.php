@php
    // Batch load site logo setting to avoid multiple database queries
    $themeSettings = get_batch_settings(['theme.dark_logo', 'theme.custom_js_footer', 'theme.description']);
    $siteLogo = $themeSettings['theme.dark_logo']
        ? Storage::url($themeSettings['theme.dark_logo'])
        : asset('img/dark_logo.png');
    $footerJs = $themeSettings['theme.custom_js_footer'] ?? '';
@endphp

<div>
    <footer
        style="background-image: url('{{ asset('img/landingpage-image/testimonial-section/testimonial-grid.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
        class=" text-white relative overflow-hidden bg-primary-600">

        <!-- Main Footer Content -->
        <div class="relative max-w-screen-xl px-4 py-12 mx-auto lg:py-16 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">

                <!-- Brand Section -->
                <div class="lg:col-span-2">
                    <a href="{{ env('APP_URL') }}" class="flex items-center mb-6">
                        <img src="{{ $siteLogo }}" alt="Application Logo" class="h-12" />
                    </a>
                    <p class="text-white text-sm leading-relaxed mb-6 max-w-md">
                        {!! $themeSettings['theme.description'] !!}
                    </p>
                    <!-- Social Media Links -->
                    <div class="flex space-x-4">
                        <a href="#"
                            class="w-10 h-10 bg-primary-500 hover:bg-primary-600 rounded-full flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-primary-500 hover:bg-primary-600 rounded-full flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-primary-500 hover:bg-primary-600 rounded-full flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.742.099.12.112.225.085.347-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165C4.575 16.983 3.6 14.671 3.6 11.854c0-3.996 2.906-7.666 8.379-7.666 4.394 0 7.806 3.131 7.806 7.315 0 4.365-2.753 7.877-6.579 7.877-1.283 0-2.494-.668-2.906-1.466l-.789 3.006c-.286 1.102-1.056 2.482-1.573 3.32C9.736 23.679 10.856 24 12.017 24c6.624 0 11.99-5.367 11.99-11.987C24.007 5.367 18.641.001 12.017.001z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">Home</a>
                        </li>
                        <li><a href="#features"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">Features</a>
                        </li>
                        <li><a href="#pricing"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">Pricing</a>
                        </li>
                        <li><a href="#faq"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">FAQs</a>
                        </li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Legal</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('privacy.policy') }}"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">Privacy
                                Policy</a></li>
                        <li><a href="{{ route('terms.conditions') }}"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">Terms &
                                Conditions</a></li>
                        <li><a href="#"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">Cookie
                                Policy</a></li>
                        <li><a href="#"
                                class="text-white hover:text-primary-100 transition-colors duration-200 text-sm">GDPR</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="pt-8 border-t border-white">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <span x-data="{ year: '' }" x-init="const timezone = '{{ get_setting('system.timezone') }}';
                        const now = new Date().toLocaleString('en-US', { timeZone: timezone });
                        year = new Date(now).getFullYear();" class="text-sm text-white">
                            © <span x-text="year"></span>-<span x-text="year + 1"></span>
                            {{ get_setting('system.company_name') }}. All Rights Reserved.
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">Made with ❤️ for better WhatsApp marketing</span>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($footerJs))
            {!! $footerJs !!}
        @endif
    </footer>
</div>
