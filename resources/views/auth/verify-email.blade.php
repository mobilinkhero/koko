<x-guest-layout>
    <x-slot:title>
        {{ t('login') }}
    </x-slot:title>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-900 flex items-center justify-center relative">
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 lg:top-8 lg:right-10 z-20">
            <livewire:language-switcher />
        </div>
        <div class="container mx-auto px-4 py-8">
            <div
                class="flex flex-col lg:flex-row w-full overflow-hidden bg-white dark:bg-slate-800 rounded-xl shadow-2xl">
                <!-- Image Section -->
                <div class="hidden lg:block lg:w-3/5 relative bg-gradient-to-br from-primary-600 to-purple-700">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="relative h-full p-12 flex flex-col justify-between z-10">
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-2">{{ t('welcome_back') }} </h1>
                            <p class="text-white/80 text-lg">{{ t('sign_in_message') }} </p>
                        </div>
                        <div class="flex items-center justify-center h-full">

                            @php
                            $settings = get_batch_settings(['theme.cover_page_image']);
                            $cover_page_image = $settings['theme.cover_page_image'];
                            // Get the image path from settings
                            $imagePath = $cover_page_image
                            ? Storage::url($cover_page_image)
                            : url('./img/coverpage.png');
                            @endphp

                            <img src="{{ $imagePath }}" alt="Cover Page Image"
                                class="object-contain max-h-full max-w-full">
                        </div>
                        <div class="mt-auto">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <p class="text-white font-medium"> {{ t('effortless_marketing') }} </p>
                                    <p class="text-white/70 text-sm">{{ t('engage_tenants') }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Login Section -->
                <div class="w-full lg:w-2/5 p-6 lg:p-12 flex items-center justify-center">
                    <div class="w-full max-w-md mx-auto">
                        <!-- Logo/Header -->
                        <div class="text-center">
                            <div class="flex justify-center mb-3">
                                <div
                                    class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <x-heroicon-o-chat-bubble-left-right
                                        class="h-7 w-7 text-primary-600 dark:text-primary-400" />
                                </div>
                            </div>
                        </div>

                        <div class="lg:w-full md:w-1/2">
                            <!-- Title -->
                            <div class="w-full flex items-center my-2 justify-center p-6">
                                <h1 class="text-center text-2xl font-bold">{{ t('email_veri') }}</h1>
                            </div>
                            <!-- Status Messages -->
                            <x-auth-session-status class="mb-4" x-show="!showInfo" x-init="showInfo = false" />

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                {{ t('verify_email_text') }}
                            </p>

                            <!-- Button Container -->
                            <div class="flex gap-4">
                                <!-- Verification Form -->
                                <form method="POST" action="{{ route('email.verified') }}" x-data="{ loading: false }"
                                    @submit="loading = true" class="flex-1">
                                    @csrf
                                    <button type="submit" x-bind:disabled="loading"
                                        class="w-full text-white bg-[#4f46e5] hover:bg-[#6366f1] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 px-4 py-2 rounded-md flex justify-center items-center text-sm min-w-[100px]">
                                        <span>{{ t('verify_email') }}</span>
                                        <span x-show="loading">
                                            <svg class="animate-spin h-5 w-5 ml-2 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </span>
                                    </button>
                                </form>

                                <!-- Logout Form -->
                                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-danger-700 dark:text-danger-400 bg-danger-200 hover:bg-danger-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 px-4 py-2 rounded-md flex justify-center items-center text-sm min-w-[100px] dark:bg-slate-700 dark:hover:bg-slate-600 dark:hover:text-danger-500 dark:focus:ring-offset-slate-800">
                                        {{ t('logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</x-guest-layout>