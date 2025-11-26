<x-base-error>
    <x-slot:title>
        {{ t('404_error') }}
    </x-slot:title>
    <div class="flex min-h-full flex-col items-center justify-center px-4 sm:px-6 lg:px-8">


        <div class="w-full max-w-md text-center">
            <!-- 404 Illustration -->
            <div class="mb-8">
                <div class="relative">
                    <!-- Large 404 Text -->
                    <h1
                        class="text-9xl font-bold text-primary-600 opacity-20 select-none sm:text-[12rem] dark:text-primary-400">
                        404</h1>
                    <!-- Floating Elements -->
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <h2 class="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-gray-300">Oops! Page Not Found
                </h2>
                <p class="mb-2 text-lg text-gray-600 sm:text-xl dark:text-gray-300">The page you're looking for doesn't
                    exist.</p>
                <p class="text-base text-gray-500 dark:text-gray-400">It might have been moved, deleted, or you entered
                    the wrong URL.</p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4 sm:flex sm:justify-center sm:space-y-0 sm:space-x-4">
                <button onclick="window.history.back()"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-primary-600 bg-white px-6 py-3 text-base font-medium text-primary-600 shadow-md transition-all duration-200 hover:scale-105 hover:bg-primary-50 hover:shadow-lg sm:w-auto dark:border-primary-500 dark:bg-gray-800 dark:text-primary-400 dark:hover:bg-gray-700">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Go Back
                </button>

                <a href="{{ !Auth::check() ? route('home') : (Auth::user()->user_type === 'tenant' ? tenant_route('tenant.dashboard') : route('admin.dashboard')) }}"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-transparent bg-primary-600 px-6 py-3 text-base font-medium text-white shadow-md transition-all duration-200 hover:scale-105 hover:bg-primary-700 hover:shadow-lg sm:w-auto dark:bg-primary-500 dark:hover:bg-primary-600">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</x-base-error>
