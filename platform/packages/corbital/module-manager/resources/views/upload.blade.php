<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-secondary-800 dark:text-secondary-200">
                {{ __('Module Installation') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-secondary-800 sm:rounded-lg">
                <!-- Back Navigation -->
                <div class="border-b border-secondary-200 bg-secondary-50/80 px-6 py-4 dark:border-secondary-700 dark:bg-secondary-800/60">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.modules.index') }}"
                            class="flex items-center text-sm font-medium text-secondary-600 transition-colors dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400">
                            <x-heroicon-o-arrow-left class="mr-1 h-4 w-4" />
                            {{ __('Back to Module Manager') }}
                        </a>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-6">
                    @if (session('error'))
                    <div class="relative mb-6 rounded-lg border border-danger-400 bg-danger-50 px-4 py-3 text-danger-700 dark:border-danger-700 dark:bg-danger-900/30 dark:text-danger-300"
                        role="alert">
                        <div class="flex items-center">
                            <x-heroicon-o-exclamation-triangle class="mr-2 h-5 w-5 text-danger-500" />
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- Module Upload Section -->
                    <div class="mb-8 overflow-hidden rounded-lg border border-secondary-200 dark:border-secondary-700">
                        <div class="border-b border-secondary-200 bg-white px-6 py-4 dark:border-secondary-700 dark:bg-secondary-800">
                            <div class="flex items-center space-x-3">
                                <div class="rounded-lg bg-primary-100 p-2 dark:bg-primary-900/30">
                                    <x-heroicon-o-arrow-up-tray class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-secondary-900 dark:text-white">
                                        {{ __('Install New Module') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                                        {{ __('Upload and install a module package to extend system capabilities') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 dark:bg-secondary-800">
                            <form action="{{ route('admin.modules.upload.process') }}" method="POST"
                                enctype="multipart/form-data" class="space-y-8">
                                @csrf

                                <div class="relative">
                                    <div class="rounded-xl border-2 border-dashed border-secondary-300 p-12 text-center transition-all duration-200 cursor-pointer hover:border-primary-400 hover:bg-primary-50/50 dark:border-secondary-600 dark:hover:border-primary-500 dark:hover:bg-primary-900/10"
                                         onclick="document.getElementById('module_file').click()">
                                        <div class="flex flex-col items-center justify-center space-y-6">
                                            <div class="rounded-full bg-primary-50 p-6 dark:bg-primary-900/30">
                                                <x-heroicon-o-cloud-arrow-up class="h-12 w-12 text-primary-500 dark:text-primary-400" />
                                            </div>

                                            <div class="space-y-3">
                                                <p class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">
                                                    {{ __('Drag and drop your module package here') }}
                                                </p>
                                                <p class="text-sm text-secondary-500 dark:text-secondary-400">
                                                    {{ __('ZIP file up to 10MB') }} • <span class="font-medium text-primary-600 dark:text-primary-400">{{ __('or click to browse') }}</span>
                                                </p>
                                            </div>

                                            <div class="pt-2">
                                                <button type="button"
                                                    class="inline-flex items-center gap-x-3 rounded-lg border border-secondary-300 bg-white px-6 py-3 text-sm font-semibold text-secondary-700 shadow-sm transition-all hover:bg-secondary-50 hover:border-secondary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-300 dark:hover:bg-secondary-700">
                                                    <x-heroicon-o-folder class="h-5 w-5" />
                                                    <span id="file-label">{{ __('Choose File') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="file" name="module_file" id="module_file" required
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                        accept=".zip"
                                        onchange="updateFileName(this)">
                                </div>

                                <script>
                                function updateFileName(input) {
                                    const label = document.getElementById('file-label');
                                    if (input.files.length > 0) {
                                        label.textContent = input.files[0].name;
                                    } else {
                                        label.textContent = '{{ __('Choose File') }}';
                                    }
                                }
                                </script>

                                @error('module_file')
                                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror

                                <div class="flex justify-center pt-2">
                                    <x-button.primary type="submit" class="mr-4">
                                        <x-heroicon-o-arrow-up-tray class="h-6 w-6" />
                                         {{ __('Install Module') }}
                                    </x-button.primary>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Module Requirements -->
                        <div class="overflow-hidden rounded-xl border border-secondary-200 shadow-sm dark:border-secondary-700">
                            <div class="border-b border-secondary-200 bg-white px-6 py-4 dark:border-secondary-700 dark:bg-secondary-800">
                                <div class="flex items-center space-x-3">
                                    <div class="rounded-lg bg-primary-100 p-2.5 dark:bg-primary-900/30">
                                        <x-heroicon-o-information-circle class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                                    </div>
                                    <h3 class="text-base font-semibold text-secondary-900 dark:text-white">
                                        {{ __('Module Requirements') }}
                                    </h3>
                                </div>
                            </div>

                            <div class="bg-white p-6 dark:bg-secondary-800">
                                <ul class="space-y-4">
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-success-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('Valid module structure with proper namespaces') }}
                                        </span>
                                    </li>
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-success-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('module.json file with name, description, version, and author details') }}
                                        </span>
                                    </li>
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-success-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('Service provider in providers directory with proper registration') }}
                                        </span>
                                    </li>
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-success-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('Compatible with WhatsApp Marketing Platform v2.0+') }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Module Update Process -->
                        <div class="overflow-hidden rounded-xl border border-secondary-200 shadow-sm dark:border-secondary-700">
                            <div class="border-b border-secondary-200 bg-white px-6 py-4 dark:border-secondary-700 dark:bg-secondary-800">
                                <div class="flex items-center space-x-3">
                                    <div class="rounded-lg bg-amber-100 p-2.5 dark:bg-amber-900/30">
                                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <h3 class="text-base font-semibold text-secondary-900 dark:text-white">
                                        {{ __('Module Update Process') }}
                                    </h3>
                                </div>
                            </div>

                            <div class="bg-white p-6 dark:bg-secondary-800">
                                <ul class="space-y-4">
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('System creates automatic backup before updating existing modules') }}
                                        </span>
                                    </li>
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('Modules are automatically deactivated during update and reactivated upon completion') }}
                                        </span>
                                    </li>
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('Database migrations run automatically to ensure data integrity') }}
                                        </span>
                                    </li>
                                    <li class="flex items-start space-x-3">
                                        <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-danger-500" />
                                        <span class="text-sm leading-relaxed text-secondary-700 dark:text-secondary-300">
                                            {{ __('Core system modules cannot be updated through this interface') }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-8 rounded-xl border border-secondary-200 bg-secondary-50 p-6 shadow-sm dark:border-secondary-700 dark:bg-secondary-800/50">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="rounded-lg bg-success-100 p-3 dark:bg-success-900/30">
                                    <x-heroicon-o-shield-check class="h-6 w-6 text-success-600 dark:text-success-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-base font-semibold text-secondary-900 dark:text-secondary-100">
                                    {{ __('Security Notice') }}
                                </h4>
                                <p class="mt-2 text-sm leading-relaxed text-secondary-600 dark:text-secondary-400">
                                    {{ __('Only install modules from trusted sources. Third-party modules might have access to your system data and functionality. For official modules, please visit our marketplace.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>