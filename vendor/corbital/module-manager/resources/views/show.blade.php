<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
                {{ t('module_details') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <x-button.secondary href="{{ route('admin.modules.index') }}" class="mb-6">
            <x-heroicon-o-arrow-small-left class="w-4 h-4 mr-2" />
            {{ t('back_to_modules') }}
        </x-button.secondary>

        <x-card class="overflow-hidden">
            <!-- Module Header Section -->
            <x-slot:header>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Module Icon/Logo -->
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-primary-600 to-purple-600 text-white shadow-lg">
                            <span class="text-2xl font-bold">{{ substr($module['name'], 0, 1) }}</span>
                        </div>

                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ $module['name'] }}
                                </h1>
                                <x-badge
                                    :color="$module['info']['type'] === 'core' ? 'info' : ($module['info']['type'] === 'custom' ? 'primary' : 'secondary')"
                                    x-data="{}"
                                    x-tooltip="'{{ $module['info']['type'] === 'core' ? 'Core modules cannot be deactivated or removed' : ($module['info']['type'] === 'custom' ? 'Custom modules can be deactivated and removed' : 'Addon modules can be deactivated and removed') }}'">
                                    {{ ucfirst($module['info']['type'] ?? 'addon') }}
                                </x-badge>
                            </div>

                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                                {{ $module['info']['description'] ?? 'No description available' }}
                            </p>

                            <div class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400">
                                <div class="flex items-center">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">Version:</span>
                                    <x-badge color="secondary" size="xs" class="ml-2">
                                        {{ $module['info']['version'] ?? '1.0.0' }}
                                    </x-badge>
                                </div>
                                <div class="flex items-center">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">Author:</span>
                                    <span class="ml-2">{{ $module['info']['author'] ?? 'Unknown' }}</span>
                                </div>
                                @if (isset($module['info']['url']) && $module['info']['url'])
                                <div class="flex items-center">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">Website:</span>
                                    <a href="{{ $module['info']['url'] }}" target="_blank"
                                        class="ml-2 text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 inline-flex items-center">
                                        Visit website
                                        <x-heroicon-o-arrow-top-right-on-square class="ml-1 h-3 w-3" />
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status & Actions -->
                    <div class="flex items-center gap-3">
                        <!-- Module Status -->
                        <x-badge
                            :color="$module['active'] ? 'success' : 'warning'"
                            size="md"
                            x-data="{ status: '{{ $module['active'] ? 'active' : 'inactive' }}' }"
                            class="flex items-center gap-2">
                            <span
                                class="h-2 w-2 rounded-full"
                                :class="status === 'active' ? 'bg-success-500 animate-pulse' : 'bg-warning-500'">
                            </span>
                            <span x-text="status === 'active' ? 'Active' : 'Inactive'"></span>
                        </x-badge>

                        @if ($module['info']['type'] === 'core')
                        <x-badge color="info" size="md" class="flex items-center gap-2">
                            <x-heroicon-o-information-circle class="h-4 w-4" />
                            Core Module
                        </x-badge>
                        @endif
                    </div>
                </div>
            </x-slot:header>

            <!-- Core Module Message -->
            @if ($module['info']['type'] === 'core')
            <div class="mx-6 mt-6 flex items-center rounded-lg border border-info-200 bg-info-50 px-4 py-3 dark:border-info-800 dark:bg-info-900/20">
                <div class="mr-3 flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-info-500" />
                </div>
                <div>
                    <p class="text-sm text-info-800 dark:text-info-300">
                        <strong>This is a core module and cannot be deactivated or removed.</strong> Core
                        modules provide essential functionality for the application.
                    </p>
                </div>
            </div>
            @endif

        </x-card>

        <!-- Module Details with tabbed interface -->
        <x-card class="mt-6" x-data="{ activeTab: 'info' }">
            <x-slot:header>
                <div class="flex items-center border-b border-slate-200 dark:border-slate-700 -mb-px">
                    <button @click="activeTab = 'info'"
                        :class="activeTab === 'info' ?
                            'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-400' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-600'"
                        class="inline-flex items-center border-b-2 py-4 px-4 text-sm font-medium mr-8">
                        <x-heroicon-o-information-circle class="mr-2 h-4 w-4" />
                        Information
                    </button>
                    <button @click="activeTab = 'dependencies'"
                        :class="activeTab === 'dependencies' ?
                            'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-400' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-600'"
                        class="inline-flex items-center border-b-2 py-4 px-4 text-sm font-medium">
                        <x-heroicon-o-puzzle-piece class="mr-2 h-4 w-4" />
                        Dependencies & Conflicts
                    </button>
                </div>
            </x-slot:header>

            <x-slot:content>
                <!-- Info Tab -->
                <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-4">Basic Information</h4>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Module Name</dt>
                                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $module['name'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Version</dt>
                                        <dd class="mt-1">
                                            <x-badge color="secondary" size="sm">{{ $module['info']['version'] ?? '1.0.0' }}</x-badge>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Author</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $module['info']['author'] ?? 'Unknown' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Type</dt>
                                        <dd class="mt-1">
                                            <x-badge :color="$module['info']['type'] === 'core' ? 'info' : ($module['info']['type'] === 'custom' ? 'primary' : 'secondary')">
                                                {{ ucfirst($module['info']['type'] ?? 'addon') }}
                                            </x-badge>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-4">Description</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    {{ $module['info']['description'] ?? 'No description available' }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-4">Module Path</h4>
                                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4">
                                    <code class="text-sm text-slate-900 dark:text-slate-200 font-mono break-all">
                                        {{ $module['path'] }}
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dependencies & Conflicts Tab -->
                <div x-show="activeTab === 'dependencies'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Dependencies Card -->
                        <div>
                            <div class="flex items-center mb-4">
                                <x-heroicon-o-puzzle-piece class="mr-2 h-4 w-4 text-success-500" />
                                <h3 class="text-sm font-medium text-slate-900 dark:text-white">Required Modules</h3>
                            </div>

                            @if (!empty($module['info']['require']))
                            <div class="space-y-3">
                                @foreach ($module['info']['require'] as $dependency => $version)
                                @php
                                    $hasModule = ModuleManager::has($dependency);
                                    $isActive = $hasModule && ModuleManager::isActive($dependency);
                                    $statusColor = $hasModule ? ($isActive ? 'success' : 'warning') : 'danger';
                                    $statusText = $hasModule ? ($isActive ? 'Active' : 'Inactive') : 'Missing';
                                @endphp

                                <div class="flex items-center justify-between p-4 rounded-lg border {{ $hasModule ? ($isActive ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-900/10' : 'border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-900/10') : 'border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-900/10' }}">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if($isActive)
                                                <x-heroicon-o-check-circle class="h-5 w-5 text-success-600 dark:text-success-400" />
                                            @elseif($hasModule)
                                                <x-heroicon-o-clock class="h-5 w-5 text-warning-600 dark:text-warning-400" />
                                            @else
                                                <x-heroicon-o-x-circle class="h-5 w-5 text-danger-600 dark:text-danger-400" />
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">{{ $dependency }}</div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">Version: {{ $version }}</div>
                                        </div>
                                    </div>
                                    <div>
                                        <x-badge :color="$statusColor" size="sm">{{ $statusText }}</x-badge>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="flex items-center justify-center rounded-lg bg-slate-50 p-4 dark:bg-slate-700/30">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    This module has no dependencies.
                                </p>
                            </div>
                            @endif
                        </div>

                        <!-- Conflicts Card -->
                        <div>
                            <div class="flex items-center mb-4">
                                <x-heroicon-o-exclamation-triangle class="mr-2 h-4 w-4 text-danger-500" />
                                <h3 class="text-sm font-medium text-slate-900 dark:text-white">Conflicts</h3>
                            </div>

                            @if (!empty($module['info']['conflicts']))
                            <div class="space-y-3">
                                @foreach ($module['info']['conflicts'] as $conflict)
                                <div class="flex items-center justify-between p-4 rounded-lg border border-danger-200 bg-danger-50 dark:border-danger-900 dark:bg-danger-900/10">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-danger-600 dark:text-danger-400" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">{{ $conflict }}</div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                                Cannot be active together with this module
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <x-badge color="danger" size="sm">Conflict</x-badge>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="flex items-center justify-center rounded-lg bg-slate-50 p-4 dark:bg-slate-700/30">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    This module has no conflicts with other modules.
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</x-app-layout>