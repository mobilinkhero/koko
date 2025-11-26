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
                            <h1 class="text-4xl font-bold text-white mb-2">{{ t('password_recovery') }} </h1>
                            <p class="text-white/80 text-lg">{{ t('help_you_reset_your_password') }} </p>
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
                                    <x-heroicon-o-shield-check class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <p class="text-white font-medium"> {{ t('forgot_password') }} </p>
                                    <p class="text-white/70 text-sm">{{ t('link_to_reset_your_password') }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-2/5 p-6 lg:p-12 flex items-center justify-center">
                    <div class="w-full max-w-md mx-auto">
                        <!-- Logo/Header -->
                        <div class="text-center ">
                            <div class="flex justify-center mb-3">
                                <div
                                    class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <x-heroicon-o-key class="h-7 w-7 text-primary-600 dark:text-primary-400" />
                                </div>
                            </div>
                        </div>

                        <div class="lg:w-full md:w-1/2">
                            <div class="mb-4 bg-warning-50 border-l-4 rounded border-warning-300 text-warning-800 px-2 py-3 mt-5 dark:bg-gray-800 dark:border-warning-800 dark:text-warning-300"
                                role="alert">
                                <div class="flex justify-start items-center gap-2">
                                    <p class="text-sm">
                                        {{ t('auth_forgot_password') }}
                                    </p>
                                </div>
                            </div>
                            <!-- Button Container -->
                            <div class="gap-4">

                                <form method="POST" action="{{ route('password.store') }}" x-data="{ loading: false }"
                                    @submit="loading = true; $el.submit();">
                                    @csrf

                                    <!-- Password Reset Token -->
                                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                    <!-- Email Address -->
                                    <div>
                                        <x-label for="email" :value="t('email')" />
                                        <x-text-input id="email" class="block mt-1 w-full" type="text" name="email"
                                            :value="old('email', $request->email)" autofocus autocomplete="username" />
                                        <x-input-error :messages="$errors->first('email')" class="mt-2" for="email" />
                                    </div>

                                    <!-- Password -->
                                    <div class="mt-4">
                                        <x-label for="password" :value="t('password')" />
                                        <x-text-input id="password" class="block mt-1 w-full" type="password"
                                            name="password" autocomplete="new-password" />
                                        <x-input-error :messages="$errors->first('password')" class="mt-2"
                                            for="password" />
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="mt-4">
                                        <x-label for="password_confirmation" :value="t('confirm_password')" />
                                        <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                            type="password" name="password_confirmation" autocomplete="new-password" />
                                        <x-input-error :messages="$errors->first('password_confirmation')" class="mt-2"
                                            for="password_confirmation" />
                                    </div>

                                    <div class="flex items-center justify-end mt-4">
                                        <button type="submit" x-bind:disabled="loading"
                                            class="w-full text-white bg-[#4f46e5] hover:bg-[#6366f1] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 px-4 py-2 rounded-md flex justify-center items-center text-sm min-w-[100px]">
                                            <span>{{ t('reset_password') }}</span>
                                            <span x-show="loading">
                                                <svg class="animate-spin h-5 w-5 ml-2 text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>