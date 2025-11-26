<div>
    <x-slot:title>
        {{ t('credit_list') }}
    </x-slot:title>
      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('credit_list')],
    ]" />
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8 ">

        <div class="bg-white dark:bg-info-800/20 overflow-hidden shadow sm:rounded-md border-l-4 border-primary-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('total_credits') }}</div>
                        <div class="text-2xl font-bold text-primary-600 dark:text-gray-100">
                            {{ get_base_currency()->format($creditBalances->sum(function ($balance) {return
                            $balance->balance;})) }}
                        </div>

                    </div>
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-s-banknotes class="h-5 w-5 text-primary-400" />

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Accounts -->
        <div class="bg-white dark:bg-info-800/20 overflow-hidden shadow sm:rounded-md border-l-4 border-info-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('accounts_with_credit') }}</div>
                        <div class="text-2xl font-bold text-info-600 dark:text-gray-100">
                            {{ $creditBalances->count() }}
                        </div>

                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-info-100 dark:bg-info-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-s-users class="h-6 w-6 text-info-400" />

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Highest Credit -->

        <div class="bg-white dark:bg-info-800/20 overflow-hidden shadow sm:rounded-md border-l-4 border-warning-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('highest_credit') }}</div>
                        <div class="text-2xl font-bold text-warning-600 dark:text-gray-100">
                            {{ $creditBalances->isNotEmpty() ?
                            get_base_currency()->format($creditBalances->max('balance')) :
                            get_base_currency()->format(0) }}
                        </div>

                    </div>
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-warning-100 dark:bg-warning-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-s-trophy class="h-6 w-6 text-warning-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Average Credit -->
        <div class="bg-white dark:bg-info-800/20 overflow-hidden shadow sm:rounded-md border-l-4 border-purple-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('average_credit') }}</div>
                        <div class="text-2xl font-bold text-purple-600 dark:text-gray-100">
                            {{ $creditBalances->isNotEmpty() ?
                            get_base_currency()->format($creditBalances->avg('balance')) :
                            get_base_currency()->format(0) }}
                        </div>

                    </div>
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-s-chart-bar class="h-6 w-6 text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.credit-table />
            </div>
        </x-slot:content>
    </x-card>
</div>