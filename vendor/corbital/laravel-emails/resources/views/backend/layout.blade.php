<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Email Management') - Laravel Emails</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-100 min-h-screen">
    <div x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-800 overflow-y-auto transition duration-300 transform"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
            @click.away="sidebarOpen = false">

            <div class="flex items-center justify-between px-4 py-5">
                <div class="flex items-center">
                    <span class="text-white font-bold text-xl">Laravel Emails</span>
                </div>
                <button @click="sidebarOpen = false" class="text-gray-300 hover:text-white md:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <nav class="mt-5 px-2">
                <a href="{{ route('laravel-emails.layouts.index') }}"
                    class="group flex items-center px-4 py-2 mt-1 text-base font-medium rounded-md {{ request()->routeIs('laravel-emails.layouts.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('laravel-emails.layouts.*') ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300' }}"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                    Email Layouts
                </a>

                <a href="{{ route('laravel-emails.templates.index') }}"
                    class="group flex items-center px-4 py-2 mt-1 text-base font-medium rounded-md {{ request()->routeIs('laravel-emails.templates.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('laravel-emails.templates.*') ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300' }}"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Email Templates
                </a>

                <a href="{{ route('laravel-emails.logs.index') }}"
                    class="group flex items-center px-4 py-2 mt-1 text-base font-medium rounded-md {{ request()->routeIs('laravel-emails.logs.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('laravel-emails.logs.*') ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300' }}"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Email Logs
                </a>

                <a href="{{ route('laravel-emails.test.index') }}"
                    class="group flex items-center px-4 py-2 mt-1 text-base font-medium rounded-md {{ request()->routeIs('laravel-emails.test.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('laravel-emails.test.*') ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300' }}"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Test Email
                </a>
            </nav>

            <div class="px-2 mt-6">
                <div class="bg-gray-700 rounded-md p-3">
                    <h3 class="text-sm font-medium text-gray-300 mb-2">Documentation</h3>
                    <ul class="space-y-1 text-sm">
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white">Getting Started</a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white">Usage Examples</a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white">Configuration</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Header and Main Content -->
        <div class="md:pl-64 flex flex-col flex-1">
            <header class="sticky top-0 z-10 bg-white shadow">
                <div class="flex justify-between items-center px-6 py-3 md:px-8">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true" class="md:hidden text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Header title -->
                    <h2 class="text-lg font-medium text-gray-700">@yield('title', 'Email Management')</h2>

                    <!-- User menu (if needed) -->
                    <div>
                        <!-- Placeholder for user menu -->
                    </div>
                </div>
            </header>

            <!-- Main content -->
            <main class="flex-1 p-6 md:px-8 py-6">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t p-4 text-center text-sm text-gray-500">
                Laravel Emails Package &copy; {{ date('Y') }} |
                <a href="#" class="text-info-600 hover:underline">Documentation</a>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>

</html>