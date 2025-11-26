<x-guest-layout>
    <div class="mx-auto px-4 py-6 bg-gray-100">

        <div class="max-w-3xl mx-auto">
            <!-- Main Card -->
            <x-card class="mb-8 overflow-hidden">
                <!-- Card Header -->
                <x-slot:header class="text-white bg-primary-500 px-6 py-4">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Verify Your Purchase
                    </h3>
                </x-slot:header>

                <!-- Card Content -->
                <x-slot:content class="p-6">
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Please enter your Envato username and purchase code to validate your license.
                        This verification is required to continue with the installation process.
                    </p>

                    <!-- Info Alert -->
                    <div
                        class="flex p-4 mb-6 text-sm rounded-lg bg-info-50 dark:bg-info-900/30 border border-info-200 dark:border-info-800">
                        <svg class="flex-shrink-0 w-5 h-5 text-info-600 dark:text-info-400 mt-0.5"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="ml-3 text-info-700 dark:text-info-300">
                            <span class="font-medium block mb-1">Need help finding your purchase code?</span>
                            <p>You can find your purchase code in your Envato dashboard under Downloads > View Purchase
                                Code.</p>
                            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"
                                class="inline-flex items-center mt-2 text-info-600 dark:text-info-400 hover:underline font-medium"
                                target="_blank">
                                Learn more
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <form action="{{ route('validate.license') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <!-- Username Field -->
                            <div>
                                <label for="username"
                                    class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    <span class="text-danger-500">*</span> Envato Username
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="username" name="username"
                                        value="{{ old('username', $username) }}"
                                        class="w-full pl-10 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors dark:text-white"
                                        placeholder="Enter your Envato username" autocomplete="off">
                                </div>
                                <x-input-error :messages="$errors->first('username')" class="mt-2" for="username" />
                            </div>

                            <!-- Purchase Code Field -->
                            <div>
                                <label for="purchase_code"
                                    class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    <span class="text-danger-500">*</span> Purchase Code
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="purchase_code" name="purchase_code"
                                        value="{{ old('purchase_code', $purchase_code) }}"
                                        class="w-full pl-10 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors dark:text-white"
                                        placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" autocomplete="off">
                                </div>
                                <x-input-error :messages="$errors->first('purchase_code')" class="mt-2"
                                    for="purchase_code" />
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8">
                            <x-button.primary type="submit" class="w-full sm:w-auto">
                                Verify License
                                <svg class="w-5 h-5 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </x-button.primary>
                        </div>
                    </form>
                </x-slot:content>
            </x-card>
        </div>
    </div>
</x-guest-layout>