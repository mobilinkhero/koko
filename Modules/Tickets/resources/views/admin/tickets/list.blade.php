<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ t('support_tickets') }}</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.tickets.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <x-heroicon-o-plus-circle class="-ml-1 mr-2 h-5 w-5" />
                        {{ t('new_ticket') }}
                    </a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-primary-500 rounded-md p-3">
                                <x-heroicon-o-ticket class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{
                                        t('total_tickets') }}</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">{{
                                            $stats['total'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-success-500 rounded-md p-3">
                                <x-heroicon-o-arrow-path class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{
                                        t('open_tickets') }}</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['open']
                                            }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-warning-500 rounded-md p-3">
                                <x-heroicon-o-clock class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{
                                        t('pending_tickets') }}</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">{{
                                            $stats['pending'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-info-500 rounded-md p-3">
                                <x-heroicon-o-chat-bubble-left-right class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{
                                        t('answered_tickets') }}</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">{{
                                            $stats['answered'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-500 rounded-md p-3">
                                <x-heroicon-o-archive-box-x-mark class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{
                                        t('closed_tickets') }}</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">{{
                                            $stats['closed'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-danger-500 rounded-md p-3">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{
                                        t('high_priority') }}</dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">{{
                                            $stats['high_priority'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="mt-8 bg-white dark:bg-slate-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">{{ t('tickets') }}</h3>
                    <div class="flex space-x-3">
                        <button
                            class="bg-primary-100 text-primary-700 dark:bg-primary-700 dark:text-primary-100 px-3 py-1 rounded-full text-sm font-medium">
                            {{ t('all') }}
                        </button>
                        <button
                            class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 px-3 py-1 rounded-full text-sm font-medium hover:bg-primary-100 hover:text-primary-700 dark:hover:bg-primary-700 dark:hover:text-primary-100">
                            {{ t('open') }}
                        </button>
                        <button
                            class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 px-3 py-1 rounded-full text-sm font-medium hover:bg-primary-100 hover:text-primary-700 dark:hover:bg-primary-700 dark:hover:text-primary-100">
                            {{ t('pending') }}
                        </button>
                        <button
                            class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 px-3 py-1 rounded-full text-sm font-medium hover:bg-primary-100 hover:text-primary-700 dark:hover:bg-primary-700 dark:hover:text-primary-100">
                            {{ t('closed') }}
                        </button>
                    </div>
                </div>
                <livewire:tickets::admin.tickets-table />
            </div>
        </div>
    </div>

</x-app-layout>