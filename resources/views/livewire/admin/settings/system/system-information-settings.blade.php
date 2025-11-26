<div>
    <x-slot:title>
        {{ t('system_details_and_diagnostics') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>
            {{ t('system_settings') }}
        </x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-system-settings-navigation wire:ignore />
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <x-card class="rounded-lg" id="capture-area">
                <x-slot:header class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="flex flex-col ">
                        <x-settings-heading>
                            <span>{{ t('system_details_and_diagnostics') }}</span>
                        </x-settings-heading>
                        <x-settings-description>
                            <span>View comprehensive information about your application's configuration, version,
                                and current system status.</span>
                        </x-settings-description>
                    </div>

                    <div class="flex flex-col items-start md:items-end mt-4 md:mt-0">
                        <button data-tippy-content="it helps to check your server and application configurations"
                            x-on:click="window.captureScreenshot('capture-area')" class="p-2 rounded-md transition duration-300 ease-in-out
                                 bg-info-600 dark:bg-info-700/65 hover:bg-info-700 hover:text-info-800
                                 dark:hover:bg-gray-700">
                            <x-heroicon-o-camera class="w-6 h-6 text-white dark:text-slate-300 font-medium" />
                        </button>

                        <p class="text-sm mt-1 text-slate-500 dark:text-slate-400  md:text-right sm:text-left w-full">
                            Capture Screenshot
                        </p>
                    </div>
                </x-slot:header>
                <x-slot:content>
                    <div class="max-w-7xl mx-auto p-1">
                        <div class="max-w-7xl mx-auto p-1">
                            <!-- Quick Stats Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2  2xl:grid-cols-4 gap-5 mb-6">
                                <!-- PHP Version -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-600 dark:text-slate-400"> PHP Version:
                                            </p>
                                            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-300 mt-1">
                                                {{ $server['php']['version'] }}</h3>
                                        </div>
                                        <div class="p-3 bg-info-50 dark:bg-info-900/20 rounded-lg">
                                            <svg class="w-6 h-6 text-info-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Environment -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Environment</p>
                                            <h3
                                                class="text-xl font-semibold text-slate-900 dark:text-slate-300 mt-1 capitalize">
                                                {{ $server['laravel']['environment'] }}</h3>
                                        </div>
                                        <div class="p-3 bg-success-50 dark:bg-success-900/20 rounded-lg">
                                            <svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Memory Usage -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                                PHP Memory Limit: </p>
                                            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-300 mt-1">
                                                {{ $server['php']['memory_limit'] }}</h3>
                                        </div>
                                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- SSL Status -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">SSL Status
                                            </p>
                                            <div class="mt-1 flex items-center gap-2">
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full
                              {{ $server['server']['ssl'] ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' }}">
                                                    {{ $server['server']['ssl'] ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            class="p-3 {{ $server['server']['ssl'] ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20' }} rounded-lg">
                                            <svg class="w-6 h-6 {{ $server['server']['ssl'] ? 'text-success-500' : 'text-danger-500' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="max-w-7xl mx-auto p-1">
                                <!-- Top Stats Row  -->
                                <div class="grid grid-cols-12 gap-5 mb-5">
                                    <!-- Laravel Environment -->
                                    <div class="col-span-12 xl:col-span-4">
                                        <div
                                            class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 shadow-sm h-full">
                                            <div class="p-6">
                                                <h2
                                                    class="text-lg font-semibold text-slate-900 dark:text-slate-300 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-danger-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                                    </svg>
                                                    Laravel Environment
                                                </h2>
                                                <div class="mt-6 space-y-4">
                                                    @foreach (['version', 'environment', 'debug', 'maintenance',
                                                    'timezone', 'locale', 'cache_driver', 'log_channel', 'queue_driver',
                                                    'session_driver', 'storage_path'] as $key)
                                                    <div
                                                        class="flex gap-6 items-center justify-between py-2 border-b border-gray-100 dark:border-slate-700/50">
                                                        <span class="text-sm text-slate-600 dark:text-slate-400">{{
                                                            t(Str::title(str_replace('_', ' ', $key))) }}</span>
                                                        @if (in_array($key, ['debug', 'maintenance']))
                                                        <span
                                                            class="px-2 py-1 text-xs font-semibold rounded-full
                              {{ $server['laravel'][$key] ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' }}">
                                                            {{ $server['laravel'][$key] ? 'Enabled' : 'Disabled' }}
                                                        </span>
                                                        @else
                                                        <span
                                                            class="text-sm font-medium text-slate-900 dark:text-slate-300 break-all">{{
                                                            $server['laravel'][$key] }}</span>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PHP Environment -->
                                    <div class="col-span-12 xl:col-span-4">
                                        <div
                                            class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 shadow-sm h-full">
                                            <div class="p-6">
                                                <h2
                                                    class="text-lg font-semibold text-slate-900 dark:text-slate-300 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-info-500" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                    PHP Environment
                                                </h2>
                                                <!-- PHP Content -->
                                                <div class="mt-6 space-y-4">
                                                    @foreach (['version', 'interface', 'memory_limit',
                                                    'max_execution_time', 'upload_max_filesize', 'post_max_size',
                                                    'max_input_vars', 'display_errors', 'error_reporting',
                                                    'opcache_enabled', 'timezone'] as $key)
                                                    <div
                                                        class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-slate-700/50">
                                                        <span class="text-sm text-slate-600 dark:text-slate-400">{{
                                                            t(Str::title(str_replace('_', ' ', $key))) }}</span>
                                                        @if (in_array($key, ['display_errors', 'opcache_enabled']))
                                                        <span
                                                            class="px-2 py-1 text-xs font-semibold rounded-full
                              {{ $server['php'][$key] ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' : 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400' }}">
                                                            {{ $server['php'][$key] ? 'Enabled' : 'Disabled' }}
                                                        </span>
                                                        @else
                                                        <span
                                                            class="text-sm font-medium text-slate-900 dark:text-slate-300">{{
                                                            $server['php'][$key] }}</span>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Server Environment -->
                                    <div class="col-span-12 xl:col-span-4">
                                        <div
                                            class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 shadow-sm h-full">
                                            <div class="p-6">
                                                <h2
                                                    class="text-lg font-semibold text-slate-900 dark:text-slate-300 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-primary-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                                    </svg>
                                                    Server Environment
                                                </h2>
                                                <!-- Server Content -->
                                                <div class="mt-6 space-y-4">
                                                    @foreach (['software', 'os', 'architecture', 'hostname', 'ssl',
                                                    'ip', 'port', 'total_disk_space', 'free_disk_space', 'cpu_cores',
                                                    'total_ram'] as $key)
                                                    <div
                                                        class="flex gap-4 items-center justify-between py-2 border-b border-gray-100 dark:border-slate-700/50">
                                                        <span class="text-sm text-slate-600 dark:text-slate-400">{{
                                                            t(Str::title(str_replace('_', ' ', $key))) }}</span>
                                                        @if ($key === 'ssl')
                                                        <span
                                                            class="px-2 py-1 text-xs font-semibold rounded-full
                                                             {{ $server['server'][$key] ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' }}">
                                                            {{ $server['server'][$key] ? 'Enabled' : 'Disabled' }}
                                                        </span>
                                                        @else
                                                        <span
                                                            class="text-sm font-medium text-slate-900 dark:text-slate-300">{{
                                                            $server['server'][$key] }}</span>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Database Environment  -->
                                <div class="grid grid-cols-12 gap-5 mb-5">
                                    <div class="col-span-12">
                                        <div
                                            class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 shadow-sm">
                                            <div class="p-6">
                                                <h2
                                                    class="text-lg font-semibold text-slate-900 dark:text-slate-300 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-success-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                                    </svg>
                                                    Database Environment
                                                </h2>
                                                <!-- Database Content -->
                                                @if (isset($server['database']['status']))
                                                <div
                                                    class="mt-4 p-4 bg-danger-50 dark:bg-danger-900/10 text-danger-600 dark:text-danger-400 rounded-lg">
                                                    {{ $server['database']['status'] }}
                                                </div>
                                                @else
                                                <div
                                                    class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-x-8 gap-y-3">
                                                    @foreach (['version', 'max_connections', 'timezone',
                                                    'character_set', 'collation', 'wait_timeout', 'max_packet_size',
                                                    'buffer_pool_size'] as $key)
                                                    <div
                                                        class="flex xl:flex-col justify-between gap-1 border-b dark:border-slate-700/50 pb-4">
                                                        <span
                                                            class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                                            {{ t(Str::title(str_replace('_', ' ', $key))) }}
                                                        </span>
                                                        <span
                                                            class="text-sm truncate break-words break-all whitespace-normal text-slate-900 dark:text-slate-300">
                                                            {{ $server['database'][$key] }}
                                                        </span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <div class="flex mt-6 flex-col gap-1 ">
                                                    <span
                                                        class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                                        {{ t(Str::title(str_replace('_', ' ', 'sql_mode'))) }}
                                                    </span>
                                                    <span
                                                        class="text-sm truncate break-words break-all whitespace-normal text-slate-900 dark:text-slate-300">
                                                        {{ $server['database']['sql_mode'] }}
                                                    </span>
                                                </div>

                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PHP Extensions -->
                                <div class="grid grid-cols-12 gap-5">
                                    <div class="col-span-12">
                                        <div
                                            class="bg-white dark:bg-slate-800 rounded-xl border dark:border-neutral-500/30 shadow-sm">
                                            <div class="p-6">
                                                <h2
                                                    class="text-lg font-semibold text-slate-900 dark:text-slate-300 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-purple-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                                    </svg>
                                                    PHP Extensions
                                                </h2>
                                                <div
                                                    class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                                    @foreach ($server['extensions'] as $extension => $info)
                                                    <div
                                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-700/30 rounded-lg">
                                                        <span class="text-sm text-slate-700 dark:text-slate-300">
                                                            {{ ucfirst($extension) }}
                                                            @if ($info['installed'] && $info['version'])
                                                            <span class="text-xs text-slate-500 dark:text-slate-400">({{
                                                                $info['version'] }})</span>
                                                            @endif
                                                        </span>
                                                        @if ($info['installed'])
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Active
                                                        </span>
                                                        @else
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Missing
                                                        </span>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                </x-slot:content>
            </x-card>
        </div>
    </div>
</div>