<x-app-layout>
    <x-slot:title>
        {{ t('tickets') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('tickets')],
    ]" />

    <div>
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ t('all_tickets') }}
                </h2>
                <div class="flex justify-start mb-3 px-5 lg:px-0 items-center gap-2">
                    <x-button.primary href="{{ route('admin.tickets.create') }}">
                        <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('create_ticket') }}
                    </x-button.primary>
                </div>
            </div>

            <div class="mx-auto space-y-6">
                <!-- Overview Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <!-- Total Tickets -->
                    <div
                        class="bg-primary-50 dark:bg-info-800/20 overflow-hidden shadow sm:rounded-md border-l-4 border-primary-500">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ t('total_tickets') }}</div>
                                    <div class="text-2xl font-bold text-primary-600 dark:text-gray-100">
                                        {{ $stats['total'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('all_time') }}</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Open Tickets -->
                    <div
                        class="bg-success-50 dark:bg-emerald-900/30 overflow-hidden shadow sm:rounded-lg border-l-4 border-success-500">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ t('open') }}</div>
                                    <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                                        {{ $stats['open'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('active_tickets') }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 bg-success-100 dark:bg-success-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Answered Tickets -->
                    <div
                        class="bg-info-50 dark:bg-info-900/50 overflow-hidden shadow sm:rounded-lg border-l-4 border-info-500">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ t('answered') }}</div>
                                    <div class="text-2xl font-bold text-info-600 dark:text-info-400">
                                        {{ $stats['answered'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('replied_to') }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 bg-info-100 dark:bg-info-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-info-600 dark:text-info-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Closed Tickets -->
                    <div
                        class="bg-gray-50 dark:bg-gray-900/50 overflow-hidden shadow sm:rounded-lg border-l-4 border-gray-500">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ t('closed') }}</div>
                                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                                        {{ $stats['closed'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('resolved') }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- High Priority -->
                    <div
                        class="bg-danger-50 dark:bg-danger-900/50 overflow-hidden shadow sm:rounded-lg border-l-4 border-danger-500">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ t('high_priority') }}</div>
                                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                                        {{ $stats['high_priority'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ t('urgent_attention') }}</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 bg-danger-100 dark:bg-danger-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-danger-600 dark:text-danger-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:header>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ t('all_tickets') }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('comprehensive_view_of_all_support_tickets') }}</p>
                            </div>
                        </div>
                    </x-slot:header>
                    <x-slot:content>
                        <livewire:tickets::admin.tickets-list />

                    </x-slot:content>
                </x-card>
            </div>
        </div>


</x-app-layout>