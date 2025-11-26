<div>
    <x-slot:title>
        {{ $campaign->name }} - {{ t('campaign_details') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('campaigns'), 'route' => tenant_route('tenant.campaigns.list')],
        ['label' => t('campaign_details')],
    ]" />

    <section class="bg-gray-50 dark:bg-slate-800">

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ $campaign->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $totalCount }}
                        {{ t($campaign->rel_type) }}</span> •
                    <span>{{ $totalCampaignsPercent }}% {{ t('of_total_leads') }}</span>
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if ($campaign->pause_campaign)
                    <x-button.green wire:click="resumeCampaign" class="flex items-center justify-center">
                        <x-heroicon-o-play class="h-4 w-4 mr-2" />
                        {{ t('resume_campaign') }}
                    </x-button.green>
                @else
                    <x-button.danger wire:click="resumeCampaign" class="flex items-center justify-center">
                        <x-heroicon-o-pause class="h-4 w-4 mr-2" />
                        {{ t('pause_campaign') }}
                    </x-button.danger>
                @endif

                @if ($isRetryAble)
                    <x-button.green wire:click="retryCampaign" class="flex items-center justify-center">
                        <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                        {{ t('resend_campaign') }}
                    </x-button.green>
                @endif

                @if (checkPermission('tenant.campaigns.create'))
                    <x-button.primary wire:click="createCampaign" class="flex items-center justify-center">
                        <x-heroicon-o-plus class="h-5 w-5 mr-2" />
                        {{ t('create_new_campaign') }}
                    </x-button.primary>
                @endif
            </div>
        </div>

        {{-- Campaign Info Panel --}}
        {{-- Campaign Info Panel - Improved --}}
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 dark:bg-slate-800 dark:border-slate-700">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                <!-- Status -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        @php
                            $iconBgClasses = match ($campaignStatus) {
                                'fail' => 'bg-red-100 dark:bg-red-900/30',
                                'pending' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                'sent' => 'bg-green-100 dark:bg-green-900/30',
                                'executed' => 'bg-blue-100 dark:bg-blue-900/30',
                                default => 'bg-gray-100 dark:bg-gray-900/30',
                            };

                            $iconClasses = match ($campaignStatus) {
                                'fail' => 'text-red-600 dark:text-red-400',
                                'pending' => 'text-yellow-600 dark:text-yellow-400',
                                'sent' => 'text-green-600 dark:text-green-400',
                                'executed' => 'text-blue-600 dark:text-blue-400',
                                default => 'text-gray-600 dark:text-gray-400',
                            };

                            $badgeClasses = match ($campaignStatus) {
                                'fail' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                'sent' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'executed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                            };

                            $statusLabel = match ($campaignStatus) {
                                'fail' => t('failed'),
                                'pending' => t('in_progress'),
                                'sent' => t('success'),
                                'executed' => t('executed'),
                                default => 'Unknown',
                            };

                            $statusIcon = match ($campaignStatus) {
                                'fail' => 'heroicon-o-x-circle',
                                'pending' => 'heroicon-o-clock',
                                'sent' => 'heroicon-o-check-circle',
                                'executed' => 'heroicon-o-check-badge',
                                default => 'heroicon-o-question-mark-circle',
                            };
                        @endphp

                        <div class="w-12 h-12 {{ $iconBgClasses }} rounded-lg flex items-center justify-center">
                            <x-dynamic-component :component="$statusIcon" class="h-6 w-6 {{ $iconClasses }}" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">
                            {{ t('status_capital') }}
                        </p>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium {{ $badgeClasses }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <!-- Template -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-document-text class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">
                            {{ t('template_capital') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate"
                            title="{{ $template_name }}">
                            {{ $template_name }}
                        </p>
                    </div>
                </div>

                <!-- Scheduled At -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-calendar class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">
                            {{ t('scheduled_at_capital') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ format_date_time($campaign->scheduled_send_time) }}
                        </p>
                    </div>
                </div>

                <!-- Campaign Name -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-megaphone class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">
                            {{ t('campaign_name_capital') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate"
                            title="{{ $campaign->name }}">
                            {{ $campaign->name }}
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Statistics Cards with Modern Design --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-6 mb-6">

            <!-- Total Sent -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide cursor-help"
                           data-tippy-content="{{ t('total_sent_tooltip') }}">
                            {{ t('total_sent') }}
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-paper-airplane class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                            {{ ($sentCount ?? 0) + ($deliverCount ?? 0) + ($readCount ?? 0) + ($failedCount ?? 0) }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('messages') }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('total_messages_sent') }}
                    </p>
                </div>
            </div>

            <!-- Total Delivered -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide cursor-help"
                           data-tippy-content="{{ t('total_delivered_tooltip') }}">
                            {{ t('total_delivered') }}
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <span
                            class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $totalDeliveredPercent }}%</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('rate') }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $deliverCount }} {{ t('messages_delivered') }}
                    </p>
                </div>
            </div>

            <!-- Total Read -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide cursor-help"
                           data-tippy-content="{{ t('total_read_tooltip') }}">
                            {{ t('total_read') }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-eye class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <span
                            class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $totalReadPercent }}%</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('rate') }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $readCount }} {{ t('total_read_messages') }}
                    </p>
                </div>
            </div>

            <!-- Total Failed -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide cursor-help"
                           data-tippy-content="{{ t('total_failed_tooltip') }}">
                            {{ t('total_failed') }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <span
                            class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $totalFailedPercent }}%</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('rate') }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $failedCount }} {{ t('total_fail') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Tabs Section --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 dark:bg-slate-800"
            x-data="{
                activeTab: @js(in_array($campaignStatus, ['sent', 'executed']) ? 'executed' : 'queue')
            }" x-cloak>

            <div class="border-b border-gray-200 dark:border-slate-700">
                <div class="flex space-x-8 px-6">
                    <button x-on:click="activeTab = 'queue'"
                        :class="{
                            'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'queue',
                            'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'queue'
                        }"
                        class="py-4 px-2 font-medium text-sm focus:outline-none transition-colors">
                        {{ t('queue') }}
                    </button>
                    <button x-on:click="activeTab = 'executed'"
                        :class="{
                            'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'executed',
                            'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'executed'
                        }"
                        class="py-4 px-2 font-medium text-sm focus:outline-none transition-colors">
                        {{ t('executed') }}
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div x-show="activeTab === 'queue'" class="text-gray-500" wire:poll.30s="refreshTable">
                    <livewire:tenant.tables.campaign-detail-table />
                </div>
                <div x-show="activeTab === 'executed'" class="text-gray-500" wire:poll.30s="refreshTable">
                    <livewire:tenant.tables.campaign-executed-table />
                </div>
            </div>
        </div>

    </section>
</div>
