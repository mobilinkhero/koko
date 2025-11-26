<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Installation</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script src="
    https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js
    "></script>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 md:p-6">
    <div class="w-full max-w-5xl">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <div class="text-3xl font-bold text-indigo-600">
                {{ config('app.name', 'Laravel') }} Installation
            </div>
        </div>

        <!-- Installation Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Progress Steps -->
            <div class="p-4 md:p-6">
                <div class="flex justify-between">
                    <div class="flex items-center flex-1">
                        <div
                            class="@if (Route::is('install.index') ||
                                    Route::is('install.requirements') ||
                                    Route::is('install.permissions') ||
                                    Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) bg-indigo-600 text-white @else ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            @if (Route::is('install.requirements') ||
                            Route::is('install.permissions') ||
                            Route::is('install.license') ||
                            Route::is('install.setup') ||
                            Route::is('install.user') ||
                            Route::is('install.finished'))
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            1
                            @endif
                        </div>
                        <div class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.index') ||
                                    Route::is('install.requirements') ||
                                    Route::is('install.permissions') ||
                                    Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) text-indigo-600 @else text-gray-500 @endif">
                            Welcome
                        </div>
                        <div
                            class="flex-1 border-t-2 @if (Route::is('install.requirements') ||
                                    Route::is('install.permissions') ||
                                    Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) border-indigo-600 @else border-gray-200 @endif mx-4">
                        </div>
                    </div>

                    <div class="flex items-center flex-1">
                        <div
                            class="@if (Route::is('install.requirements')) bg-indigo-600 text-white @elseif(Route::is('install.permissions') ||
                                    Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) bg-indigo-600 text-white @else @if (Route::is('install.index')) ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @else bg-gray-100 text-gray-400 @endif @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            @if (Route::is('install.permissions') ||
                            Route::is('install.license') ||
                            Route::is('install.setup') ||
                            Route::is('install.user') ||
                            Route::is('install.finished'))
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            2
                            @endif
                        </div>
                        <div
                            class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.requirements')) text-indigo-600 @elseif(Route::is('install.permissions') ||
                                    Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) text-indigo-600 @else @if (Route::is('install.index')) text-gray-500 @else text-gray-400 @endif @endif">
                            Requirements
                        </div>
                        <div
                            class="flex-1 border-t-2  @if (Route::is('install.permissions') ||
                                    Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) border-indigo-600 @else border-gray-200 @endif mx-4">
                        </div>
                    </div>

                    <div class="flex items-center flex-1">
                        <div
                            class="@if (Route::is('install.permissions')) bg-indigo-600 text-white @elseif(Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) bg-indigo-600 text-white @else @if (Route::is('install.requirements')) ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @else bg-gray-100 text-gray-400 @endif @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            @if (Route::is('install.license') ||
                            Route::is('install.setup') ||
                            Route::is('install.user') ||
                            Route::is('install.finished'))
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            3
                            @endif
                        </div>
                        <div
                            class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.permissions')) text-indigo-600 @elseif(Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) text-indigo-600 @else @if (Route::is('install.requirements')) text-gray-500 @else text-gray-400 @endif @endif">
                            Permissions
                        </div>
                        <div
                            class="flex-1 border-t-2 @if (Route::is('install.license') ||
                                    Route::is('install.setup') ||
                                    Route::is('install.user') ||
                                    Route::is('install.finished')) border-indigo-600 @else border-gray-200 @endif mx-4">
                        </div>
                    </div>

                    <!-- LICENSE STEP (NEW) -->
                    <div class="flex items-center flex-1">
                        <div
                            class="@if (Route::is('install.setup')) bg-indigo-600 text-white @elseif(Route::is('install.license') || Route::is('install.user') || Route::is('install.finished')) bg-indigo-600 text-white @else @if (Route::is('install.permissions')) ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @else bg-gray-100 text-gray-400 @endif @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            @if (Route::is('install.license') || Route::is('install.user') ||
                            Route::is('install.finished'))
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            4
                            @endif
                        </div>
                        <div
                            class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.setup')) text-indigo-600 @elseif(Route::is('install.license') || Route::is('install.user') || Route::is('install.finished')) text-indigo-600 @else @if (Route::is('install.permissions')) text-gray-500 @else text-gray-400 @endif @endif">
                            Setup
                        </div>
                        <div
                            class="flex-1 border-t-2 @if (Route::is('install.license') || Route::is('install.user') || Route::is('install.finished')) border-indigo-600 @else border-gray-200 @endif mx-4">
                        </div>
                    </div>

                    <div class="flex items-center flex-1">
                        <div
                            class="@if (Route::is('install.license')) bg-indigo-600 text-white @elseif(Route::is('install.user') || Route::is('install.finished')) bg-indigo-600 text-white @else @if (Route::is('install.setup')) ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @else bg-gray-100 text-gray-400 @endif @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            @if (Route::is('install.user') || Route::is('install.finished'))
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            5
                            @endif
                        </div>
                        <div
                            class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.license')) text-indigo-600 @elseif(Route::is('install.user') || Route::is('install.finished')) text-indigo-600 @else @if (Route::is('install.setup')) text-gray-500 @else text-gray-400 @endif @endif">
                            License
                        </div>
                        <div
                            class="flex-1 border-t-2 @if (Route::is('install.user') || Route::is('install.finished')) border-indigo-600 @else border-gray-200 @endif mx-4">
                        </div>
                    </div>

                    <div class="flex items-center flex-1">
                        <div
                            class="@if (Route::is('install.user')) bg-indigo-600 text-white @elseif(Route::is('install.finished')) bg-indigo-600 text-white @else @if (Route::is('install.license')) ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @else bg-gray-100 text-gray-400 @endif @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            @if (Route::is('install.finished'))
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            6
                            @endif
                        </div>
                        <div
                            class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.user')) text-indigo-600 @elseif(Route::is('install.finished')) text-indigo-600 @else @if (Route::is('install.license')) text-gray-500 @else text-gray-400 @endif @endif">
                            Admin
                        </div>
                        <div
                            class="flex-1 border-t-2 @if (Route::is('install.finished')) border-indigo-600 @else border-gray-200 @endif mx-4">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div
                            class="@if (Route::is('install.finished')) bg-indigo-600 text-white @else @if (Route::is('install.user')) ring-2 ring-indigo-600 ring-offset-2 bg-white text-indigo-600 @else bg-gray-100 text-gray-400 @endif @endif flex items-center justify-center w-8 h-8 rounded-full font-medium text-sm">
                            7
                        </div>
                        <div
                            class="ml-2 text-sm font-medium hidden sm:block @if (Route::is('install.finished')) text-indigo-600 @else @if (Route::is('install.user')) text-gray-500 @else text-gray-400 @endif @endif">
                            Finish
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- Content Area -->
            <div class="p-4 md:p-6">
                @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
        </div>
    </div>


    @stack('scripts')
</body>

</html>