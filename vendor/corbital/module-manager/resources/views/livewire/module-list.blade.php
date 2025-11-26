<div>
    <!-- Alpine.js initialization for modals -->
    <div x-data="{ showModalId: null }" x-on:close-modals.window="showModalId = null"
        x-on:open-modal.window="showModalId = $event.detail">

        <x-card class="overflow-hidden">
            <x-slot name="header">
                <!-- Header with filters and search -->
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-cube class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">
                                    Modules Management
                                </h3>
                                <p class="text-xs text-secondary-600 dark:text-secondary-400">
                                    Manage your modules, activate or deactivate features
                                </p>
                            </div>
                        </div>
                    </div>
                    <x-button.primary href="{{ route('admin.modules.upload') }}" class="inline-flex items-center" size="sm">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                        Upload Module
                    </x-button.primary>
                </div>
                <div class="mt-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0 sm:space-x-3">
                        <!-- Search - improved styling -->
                        <div class="relative sm:max-w-sm w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-secondary-500 dark:text-secondary-400" />
                            </div>
                            <input wire:model.debounce.300ms="search" type="search"
                                class="bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 text-secondary-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-9 p-2 placeholder-secondary-500 dark:placeholder-secondary-400"
                                placeholder="Search modules...">
                        </div>
                        <!-- Filters - improved spacing and styling -->
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                            <select wire:model.live="type" id="type"
                                class="bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 text-secondary-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 min-w-[110px]">
                                <option value="">All Types</option>
                                <option value="core">Core</option>
                                <option value="addon">Addon</option>
                                <option value="custom">Custom</option>
                            </select>
                            <select wire:model.live="status" id="status"
                                class="bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 text-secondary-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 min-w-[110px]">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <select wire:model.live="perPage" id="perPage"
                                class="bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 text-secondary-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 min-w-[110px]">
                                <option value="5">5 per page</option>
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="content">

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-secondary-500 dark:text-secondary-400">
                        <thead
                            class="text-xs text-secondary-700 dark:text-secondary-300 uppercase bg-secondary-50 dark:bg-secondary-700/50">
                            <tr>
                                <th scope="col" class="px-4 py-2">
                                    <div class="flex items-center cursor-pointer group"
                                        wire:click="sortBy('name')">
                                        <span class="group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Name</span>
                                        @if ($sortField === 'name')
                                        <x-heroicon-s-chevron-up-down class="w-3 h-3 ml-1 text-primary-500" />
                                        @else
                                        <x-heroicon-o-chevron-up-down class="w-3 h-3 ml-1 text-secondary-400 group-hover:text-primary-500 transition-colors" />
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-2">
                                    <div class="flex items-center cursor-pointer group"
                                        wire:click="sortBy('type')">
                                        <span class="group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Type</span>
                                        @if ($sortField === 'type')
                                        <x-heroicon-s-chevron-up-down class="w-3 h-3 ml-1 text-primary-500" />
                                        @else
                                        <x-heroicon-o-chevron-up-down class="w-3 h-3 ml-1 text-secondary-400 group-hover:text-primary-500 transition-colors" />
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-2">
                                    <div class="flex items-center cursor-pointer group"
                                        wire:click="sortBy('status')">
                                        <span class="group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Status</span>
                                        @if ($sortField === 'status')
                                        <x-heroicon-s-chevron-up-down class="w-3 h-3 ml-1 text-primary-500" />
                                        @else
                                        <x-heroicon-o-chevron-up-down class="w-3 h-3 ml-1 text-secondary-400 group-hover:text-primary-500 transition-colors" />
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-2 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($modules as $key => $module)
                        <tr class="bg-white dark:bg-secondary-800 hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors"
                            wire:key="{{ $module['name'] }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <!-- Module icon -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ substr($module['name'], 0, 1) }}
                                        </div>
                                    </div>
                                    <!-- Module info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.modules.show', $module['name']) }}"
                                                class="text-base font-medium text-secondary-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                                {{ $module['name'] }}
                                            </a>
                                        </div>
                                        <p class="text-xs text-secondary-600 dark:text-secondary-400 line-clamp-1">
                                            {{ $module['info']['description'] ?? 'No description available' }}
                                        </p>
                                        <div class="mt-1 flex items-center space-x-3 text-xs text-secondary-500 dark:text-secondary-400">
                                            <span class="flex items-center">
                                                <x-heroicon-o-tag class="w-3 h-3 mr-1" />
                                                v{{ $module['info']['version'] ?? '1.0.0' }}
                                            </span>
                                            <span class="flex items-center">
                                                <x-heroicon-o-user class="w-3 h-3 mr-1" />
                                                {{ $module['info']['author'] ?? 'Unknown' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ isset($module['info']['type']) && $module['info']['type'] === 'core' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : ($module['info']['type'] === 'custom' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300') }}">
                                    {{ isset($module['info']['type']) && $module['info']['type'] === 'core' ? 'Core' : ($module['info']['type'] === 'custom' ? 'Custom' : 'Addon') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $module['active'] ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300' : 'bg-secondary-100 text-secondary-800 dark:bg-secondary-700 dark:text-secondary-300' }}">
                                    <span class="w-1.5 h-1.5 mr-1 rounded-full {{ $module['active'] ? 'bg-success-400' : 'bg-secondary-400' }}"></span>
                                    {{ $module['active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                $versionRequirementMet = is_minimum_version_requirement_met($module['name']);
                                @endphp
                                @if (isset($module['info']['type']) && $module['info']['type'] === 'core')
                                <span class="text-secondary-500 dark:text-secondary-400 text-sm italic">Core module</span>
                                @else
                                <div class="flex items-center justify-end space-x-1">
                                    @if ($module['active'] && $versionRequirementMet)
                                        @if (isset($module['info']['license_product_id']))
                                            @if(!empty(get_module($module['info']['license_product_id'])))
                                                <x-button.secondary wire:click="checkUpdate('{{ $module['info']['license_product_id'] }}')"
                                                    size="xs">
                                                    <x-heroicon-o-arrow-path class="w-3 h-3 mr-1" />
                                                    Update
                                                </x-button.secondary>
                                            @endif
                                        @endif
                                    <x-button.secondary @click="showModalId = 'confirm-module-deactivation-{{ $module['name'] }}'"
                                        wire:key="deactivate-{{ $module['name'] }}" wire:loading.class="opacity-50"
                                        class="text-amber-700 bg-amber-100 hover:bg-amber-200 border-amber-300 dark:bg-amber-900 dark:text-amber-300 dark:hover:bg-amber-800"
                                        size="xs">
                                        <x-heroicon-o-pause class="w-3 h-3 mr-1" />
                                        Deactivate
                                    </x-button.secondary>
                                    @else
                                    <x-button.primary @click="showModalId = 'confirm-module-activation-{{ $module['name'] }}'"
                                        wire:key="activate-{{ $module['name'] }}" wire:loading.class="opacity-50"
                                        size="xs">
                                        <x-heroicon-o-play class="w-3 h-3 mr-1" />
                                        Activate
                                    </x-button.primary>
                                    @endif
                                    @if(!$module['active'])
                                    <x-button.danger @click="showModalId = 'confirm-module-deletion-{{ $module['name'] }}'"
                                        size="xs">
                                        <x-heroicon-o-trash class="w-3 h-3 mr-1" />
                                        Remove
                                    </x-button.danger>
                                    @endif
                                    @if (isset($module['info']['license_product_id']))
                                        @php
                                            $currentVersion = get_module($module['info']['license_product_id']);
                                            $latestVersion =  $module['info']['version'];
                                        @endphp
                                        @if (!is_null($currentVersion) && $currentVersion['version'] != $latestVersion)
                                            <x-button.secondary wire:click="upgradeVersion('{{ $module['info']['license_product_id'] }}', '{{ $module['info']['version'] }}')"
                                                class="text-info-700 bg-info-100 hover:bg-info-200 border-info-300 dark:bg-info-900 dark:text-info-300 dark:hover:bg-info-800"
                                                size="xs">
                                                <x-heroicon-o-arrow-up class="w-3 h-3 mr-1" />
                                                Upgrade
                                            </x-button.secondary>
                                        @endif
                                    @endif
                                </div>

                                    <!-- Module Activation Confirmation Modal -->
                                    <div x-show="showModalId === 'confirm-module-activation-{{ $module['name'] }}'"
                                        x-cloak x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto">
                                        <div
                                            class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div x-show="showModalId === 'confirm-module-activation-{{ $module['name'] }}'"
                                                class="fixed inset-0 bg-black bg-opacity-50"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-75"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-75" x-transition:leave-end="opacity-0"
                                                @click="showModalId = null">
                                                <div
                                                    class="absolute inset-0 bg-secondary-900 dark:bg-secondary-900 opacity-75">
                                                </div>
                                            </div>

                                            <span
                                                class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                                            <div x-show="showModalId === 'confirm-module-activation-{{ $module['name'] }}'"
                                                x-cloak x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="relative inline-block align-bottom bg-white dark:bg-secondary-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="bg-white dark:bg-secondary-800">
                                                    <div class="w-full flex p-6 flex-col sm:flex-row">
                                                        <div
                                                            class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full {{ !$versionRequirementMet ? 'bg-danger-100 dark:bg-danger-900' : 'bg-success-100 dark:bg-success-900'}} sm:mx-0 sm:size-10">
                                                            @if (!$versionRequirementMet)
                                                            <x-heroicon-o-exclamation-triangle
                                                                class="w-6 h-6 text-danger-600 dark:text-danger-400" />
                                                            @else
                                                            <x-heroicon-o-check-circle
                                                                class="w-6 h-6 text-success-600 dark:text-success-400" />
                                                            @endif
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white"
                                                                id="modal-title">
                                                                Activate Module</h3>
                                                            <div class="mt-2">
                                                                @if (!$versionRequirementMet &&
                                                                isset($module['info']['requires_at']))
                                                                <p class="text-sm text-danger-600 dark:text-danger-400">
                                                                    The {{ $module['name'] }} module
                                                                    requires WhatsMark-SaaS version
                                                                    {{ e($module['info']['requires_at']) }}
                                                                </p>
                                                                @else
                                                                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                                                    Are you sure you want to activate "{{
                                                                    $module['name'] }}"?
                                                                    This will enable all features of this module.
                                                                </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="bg-secondary-50 dark:bg-secondary-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-3 sm:space-x-reverse">
                                                    @if ($versionRequirementMet)
                                                    <x-button.primary wire:click="activateModule('{{ $module['name'] }}')"
                                                        @click="showModalId = null">
                                                        <x-heroicon-o-play class="w-4 h-4 mr-2" />
                                                        Activate
                                                    </x-button.primary>
                                                    @endif
                                                    <x-button.secondary @click="showModalId = null">
                                                        Cancel
                                                    </x-button.secondary>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Module Deactivation Confirmation Modal -->
                                    <div x-show="showModalId === 'confirm-module-deactivation-{{ $module['name'] }}'"
                                        x-cloak x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto">
                                        <div
                                            class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div x-show="showModalId === 'confirm-module-deactivation-{{ $module['name'] }}'"
                                                class="fixed inset-0 bg-black bg-opacity-50"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-75"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-75" x-transition:leave-end="opacity-0"
                                                @click="showModalId = null">
                                                <div
                                                    class="absolute inset-0 bg-secondary-900 dark:bg-secondary-900 opacity-75">
                                                </div>
                                            </div>

                                            <span
                                                class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                                            <div x-show="showModalId === 'confirm-module-deactivation-{{ $module['name'] }}'"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block align-bottom bg-white dark:bg-secondary-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="bg-white dark:bg-secondary-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div
                                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900 sm:mx-0 sm:h-10 sm:w-10">
                                                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                                                        </div>
                                                        <div class="mt-3 sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3
                                                                class="text-lg leading-6 font-medium text-secondary-900 dark:text-white">
                                                                Deactivate Module</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                                                    Are you sure you want to deactivate "{{
                                                                    $module['name'] }}"? This will disable all features
                                                                    provided by this module.
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="bg-secondary-50 dark:bg-secondary-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-3 sm:space-x-reverse">
                                                    <x-button.secondary wire:click="deactivateModule('{{ $module['name'] }}')"
                                                        @click="showModalId = null"
                                                        class="text-amber-700 bg-amber-100 hover:bg-amber-200 border-amber-300 dark:bg-amber-900 dark:text-amber-300 dark:hover:bg-amber-800">
                                                        <x-heroicon-o-pause class="w-4 h-4 mr-2" />
                                                        Deactivate
                                                    </x-button.secondary>
                                                    <x-button.secondary @click="showModalId = null">
                                                        Cancel
                                                    </x-button.secondary>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Module Deletion Confirmation Modal -->
                                    <div x-show="showModalId === 'confirm-module-deletion-{{ $module['name'] }}'"
                                        x-cloak x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto">
                                        <div
                                            class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div x-show="showModalId === 'confirm-module-deletion-{{ $module['name'] }}'"
                                                class="fixed inset-0 bg-black bg-opacity-50"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-75"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-75" x-transition:leave-end="opacity-0"
                                                @click="showModalId = null">
                                                <div
                                                    class="absolute inset-0 bg-secondary-900 dark:bg-secondary-900 opacity-75">
                                                </div>
                                            </div>

                                            <span
                                                class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                                            <div x-show="showModalId === 'confirm-module-deletion-{{ $module['name'] }}'"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block align-bottom bg-white dark:bg-secondary-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                                                <div class="bg-white dark:bg-secondary-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div
                                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-danger-100 dark:bg-danger-900 sm:mx-0 sm:h-10 sm:w-10">
                                                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3
                                                                class="text-lg leading-6 font-medium text-secondary-900 dark:text-white">
                                                                Remove Module</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                                                    Are you sure you want to remove this module?
                                                                    This action cannot be undone.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="bg-secondary-50 dark:bg-secondary-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-3 sm:space-x-reverse">
                                                    <x-button.danger wire:click="removeModule('{{ $module['name'] }}')"
                                                        @click="showModalId = null">
                                                        <x-heroicon-o-trash class="w-4 h-4 mr-2" />
                                                        Remove
                                                    </x-button.danger>
                                                    <x-button.secondary @click="showModalId = null">
                                                        Cancel
                                                    </x-button.secondary>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-12 h-12 bg-secondary-100 dark:bg-secondary-700 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-cube class="w-6 h-6 text-secondary-400 dark:text-secondary-500" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-secondary-900 dark:text-white mb-1">No modules found</h3>
                                        <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                            @if($search || $type || $status)
                                                Try adjusting your search or filter criteria
                                            @else
                                                Get started by uploading your first module
                                            @endif
                                        </p>
                                    </div>
                                    @if(!$search && !$type && !$status)
                                    <x-button.primary href="{{ route('admin.modules.upload') }}" class="mt-2" size="sm">
                                        <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                                        Upload Module
                                    </x-button.primary>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-slot>

            @if ($totalModules > 0)
            <x-slot name="footer">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 py-2">
                    <div class="text-sm text-secondary-700 dark:text-secondary-300">
                        Showing <span class="font-medium">{{ $firstItem }}</span> to <span class="font-medium">{{
                            $lastItem }}</span> of <span class="font-medium">{{ $totalModules }}</span> modules
                    </div>
                    <div class="flex items-center space-x-1">
                        <x-button.secondary wire:click="previousPage" wire:loading.attr="disabled"
                            :disabled="$page === 1"
                            class="inline-flex items-center" size="sm">
                            <x-heroicon-o-chevron-left class="w-4 h-4 mr-1" />
                            Previous
                        </x-button.secondary>
                        <x-button.secondary wire:click="nextPage" wire:loading.attr="disabled"
                            :disabled="$page === $lastPage"
                            class="inline-flex items-center" size="sm">
                            Next
                            <x-heroicon-o-chevron-right class="w-4 h-4 ml-1" />
                        </x-button.secondary>
                    </div>
                </div>
            </x-slot>
            @endif
        </x-card>
    </div>

    <!-- Include the Envato validation modal -->
    @include('modules::components.envato-validation-modal')
</div>