<x-app-layout>
    <x-slot:title>
        {{ t('tickets') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('tickets')],
    ]" />

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ t('all_tickets') }}
        </h2>
        <div class="flex justify-start mb-3 px-5 lg:px-0 items-center gap-2">
            <x-button.primary href="{{ tenant_route('tenant.tickets.create') }}">
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('create_ticket') }}
            </x-button.primary>
        </div>
    </div>


    <div>
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
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('all_time') }}</div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                    <x-heroicon-s-shield-exclamation
                                        class="w-5 h-5 text-primary-600 dark:text-primary-400" />

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
                                <div class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['open']
                                    }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('active_tickets') }}
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-success-100 dark:bg-success-900 rounded-lg flex items-center justify-center">
                                    <x-heroicon-s-folder class="w-5 h-5 text-success-600 dark:text-success-400" />

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
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('replied_to') }}</div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-info-100 dark:bg-info-900 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-arrow-uturn-left class="w-5 h-5 text-info-600 dark:text-info-400" />

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
                                    Closed</div>
                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['closed'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('resolved') }}</div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <x-heroicon-s-check class="w-5 h-5 text-gray-600 dark:text-gray-400" />

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
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('urgent_attention') }}
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-danger-100 dark:bg-danger-900 rounded-lg flex items-center justify-center">
                                    <x-heroicon-s-exclamation-triangle
                                        class="w-5 h-5 text-danger-600 dark:text-danger-400" />

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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ t('all_tickets') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{
                                t('Comprehensive_view_support_tickets') }}</p>
                        </div>
                    </div>
                </x-slot:header>
                <x-slot:content>

                    <livewire:tickets::client.tickets-list />
                </x-slot:content>
            </x-card>
        </div>
    </div>
</x-app-layout>