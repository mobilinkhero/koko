<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                {{ t('department_statistics') }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ t('overview_of_department_metrics') }}
            </p>
        </div>

    </div>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Statistics Cards -->
        <x-card class="rounded-lg shadow-sm ">
            <x-slot:content class="p-6">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Total Departments -->
                    <div
                        class="group relative overflow-hidden text-center p-4 bg-gradient-to-br from-info-50 to-primary-50 dark:from-info-900/20 dark:to-primary-900/20 rounded-xl border border-info-100 dark:border-info-800/30 hover:shadow-md transition-all duration-200">
                        <div class="absolute top-2 right-2">
                            <x-heroicon-o-building-office-2
                                class="w-4 h-4 text-info-400 dark:text-info-500 opacity-60" />
                        </div>
                        <div class="text-xl font-bold text-info-900 dark:text-info-100 mb-1">
                            {{ $stats['total_departments'] }}
                        </div>
                        <div class="text-xs font-medium text-info-700 dark:text-info-300">
                            {{ t('total_departments') }}
                        </div>
                    </div>

                    <!-- Active Departments -->
                    <div
                        class="group relative overflow-hidden text-center p-4 bg-gradient-to-br from-emerald-50 to-success-50 dark:from-emerald-900/20 dark:to-success-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800/30 hover:shadow-md transition-all duration-200">
                        <div class="absolute top-2 right-2">
                            <x-heroicon-o-check-circle
                                class="w-4 h-4 text-emerald-400 dark:text-emerald-500 opacity-60" />
                        </div>
                        <div class="text-xl font-bold text-emerald-900 dark:text-emerald-100 mb-1">
                            {{ $stats['active_departments'] }}
                        </div>
                        <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                            {{ t('active_departments') }}
                        </div>
                        @if($stats['total_departments'] > 0)
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                            {{ round(($stats['active_departments'] / $stats['total_departments']) * 100) }}%
                        </div>
                        @endif
                    </div>

                    <!-- Inactive Departments -->
                    <div
                        class="group relative overflow-hidden text-center p-4 bg-gradient-to-br from-danger-50 to-rose-50 dark:from-danger-900/20 dark:to-rose-900/20 rounded-xl border border-danger-100 dark:border-danger-800/30 hover:shadow-md transition-all duration-200">
                        <div class="absolute top-2 right-2">
                            <x-heroicon-o-x-circle class="w-4 h-4 text-danger-400 dark:text-danger-500 opacity-60" />
                        </div>
                        <div class="text-xl font-bold text-danger-900 dark:text-danger-100 mb-1">
                            {{ $stats['inactive_departments'] }}
                        </div>
                        <div class="text-xs font-medium text-danger-700 dark:text-danger-300">
                            {{ t('inactive_departments') }}
                        </div>
                        @if($stats['total_departments'] > 0)
                        <div class="text-xs text-danger-600 dark:text-danger-400 mt-1">
                            {{ round(($stats['inactive_departments'] / $stats['total_departments']) * 100) }}%
                        </div>
                        @endif
                    </div>

                    <!-- With Tickets -->
                    <div
                        class="group relative overflow-hidden text-center p-4 bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-xl border border-purple-100 dark:border-purple-800/30 hover:shadow-md transition-all duration-200">
                        <div class="absolute top-2 right-2">
                            <x-heroicon-o-inbox-stack class="w-4 h-4 text-purple-400 dark:text-purple-500 opacity-60" />
                        </div>
                        <div class="text-xl font-bold text-purple-900 dark:text-purple-100 mb-1">
                            {{ $stats['departments_with_tickets'] }}
                        </div>
                        <div class="text-xs font-medium text-purple-700 dark:text-purple-300">
                            {{ t('with_tickets') }}
                        </div>
                    </div>

                    <!-- Without Tickets -->
                    <div
                        class="group relative overflow-hidden text-center p-4 bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-800/50 dark:to-gray-800/50 rounded-xl border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-200">
                        <div class="absolute top-2 right-2">
                            <x-heroicon-o-inbox class="w-4 h-4 text-slate-400 dark:text-slate-500 opacity-60" />
                        </div>
                        <div class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-1">
                            {{ $stats['departments_without_tickets'] }}
                        </div>
                        <div class="text-xs font-medium text-slate-600 dark:text-slate-400">
                            {{ t('without_tickets') }}
                        </div>
                    </div>

                    <!-- Total Tickets -->
                    <div
                        class="group relative overflow-hidden text-center p-4 bg-gradient-to-br from-warning-50 to-orange-50 dark:from-warning-900/20 dark:to-orange-900/20 rounded-xl border border-warning-100 dark:border-warning-800/30 hover:shadow-md transition-all duration-200">
                        <div class="absolute top-2 right-2">
                            <x-heroicon-o-ticket class="w-4 h-4 text-warning-400 dark:text-warning-500 opacity-60" />
                        </div>
                        <div class="text-xl font-bold text-warning-900 dark:text-warning-100 mb-1">
                            {{ number_format($stats['total_tickets']) }}
                        </div>
                        <div class="text-xs font-medium text-warning-700 dark:text-warning-300">
                            {{ t('total_tickets') }}
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>

        <!-- Top Departments -->
        @if($stats['tickets_by_department']->isNotEmpty())
        <x-card class="rounded-lg shadow-sm ">
            <x-slot:header class="pb-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900/40 dark:to-purple-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-trophy class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                {{ t('top_departments') }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t('ranked_by_activity') }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300">
                        {{ t('top') }} {{ $stats['tickets_by_department']->count() }}
                    </span>
                </div>
            </x-slot:header>

            <x-slot:content>
                <div>
                    @foreach($stats['tickets_by_department'] as $index => $department)
                    <div
                        class="flex justify-between items-center p-4 mb-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 bg-gradient-to-br from-info-50 to-primary-50 dark:from-info-900/20 dark:to-primary-900/20 transition-colors duration-150 group">
                        <div class="flex items-center space-x-4">
                            <!-- Enhanced Rank Badge -->
                            <div class="flex-shrink-0">
                                @if($index === 0)
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-sm font-bold text-info-600 rounded-md bg-gradient-to-r from-info-100 to-info-100">
                                    1
                                </span>
                                @elseif($index === 1)
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-sm font-bold text-warning-600 rounded-md bg-gradient-to-r from-warning-100 to-warning-100">
                                    2
                                </span>
                                @elseif($index === 2)
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-sm font-bold text-orange-600 rounded-md bg-gradient-to-r from-orange-100 to-orange-100">
                                    3
                                </span>

                                @endif
                            </div>

                            <!-- Department Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4
                                        class="text-sm font-semibold text-slate-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                        {{ $department->name }}
                                    </h4>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                                {{ $department->status ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' }}">
                                        <div
                                            class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $department->status ? 'bg-success-500' : 'bg-danger-500' }}">
                                        </div>
                                        {{ $department->status ? t('active') : t('inactive') }}
                                    </span>
                                </div>
                                @if($department->description)
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    {{ Str::limit($department->description, 60) }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <!-- Ticket Count with Achievement Badge -->
                        <div class="flex items-center space-x-3 flex-shrink-0">
                            <div class="text-right">
                                <div class="text-lg font-bold text-slate-900 dark:text-white">
                                    {{ number_format($department->tickets_count) }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $department->tickets_count === 1 ? t('ticket') : t('tickets') }}
                                </div>
                            </div>

                            @if($index < 3) <div class="flex-shrink-0">
                                <div
                                    class="w-6 h-6 rounded-full flex items-center justify-center
                                                {{ $index === 0 ? 'bg-info-100 dark:bg-info-900/30' :
                                                   ($index === 1 ? 'bg-warning-100 dark:bg-warning-700' : 'bg-orange-100 dark:bg-orange-900/30') }}">
                                    <x-heroicon-m-trophy
                                        class="w-4 h-4 {{ $index === 0 ? 'text-info-600 dark:text-info-400' : ($index === 1 ? 'text-warning-500 dark:text-warning-400' : 'text-orange-600 dark:text-orange-400') }}" />
                                </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
    </div>
    </x-slot:content>
    </x-card>
    @else
    <x-card class="rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
        <x-slot:content class="p-8 text-center">
            <div
                class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-slate-400" />
            </div>
            <h3 class="text-sm font-medium text-slate-900 dark:text-white mb-2">
                {{ t('no_department_data') }}
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ t('no_departments_with_tickets_found') }}
            </p>
        </x-slot:content>
    </x-card>
    @endif
</div>
</div>