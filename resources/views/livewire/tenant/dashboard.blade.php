<div x-data="contactSourcesChart()">
    <x-slot:title>
        {{ t('dashboard') }}
    </x-slot:title>

    {{-- conversation limit alert --}}
    @if (
        $conversationLimit != -1 &&
            ($totalConversations == $conversationLimit || $totalConversations >= $conversationLimit))
        <div class="mb-3">
            <div>
                <x-dynamic-alert type="primary">
                    <p><strong>{{ t('conversation_limit_reached') }}!</strong></p>
                    <p>{{ t('conversation_limit_upgrade_message') }}.</p>
                    <ul class="mb-2">
                        <li>{{ t('campaign_sending_paused') }}</li>
                        <li>{{ t('chat_reinitialization_disabled') }}</li>
                        <li>{{ t('new_conversation_blocked_until_limit_reset') }}</li>
                    </ul>
                    <p>{{ t('please') }} <a href="{{ tenant_route('tenant.subscription') }}"
                            class="text-info-600 underline">{{ t('click_here_to_update_plan') }}</a>
                        {{ t('and_restore_full_access') }}.</p>
                </x-dynamic-alert>
            </div>
        </div>
    @endif

    {{-- Dashboard Template Change Alert --}}
    @if (get_tenant_setting_from_db('whats-mark', 'is_templates_changed', '0') == '1')
        <div class="mb-4">
            <div
                class="bg-warning-100 border-l-4 rounded-r-md border-warning-500 dark:bg-gray-700 dark:border-warning-300 dark:text-warning-300 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-warning-600" />
                    </div>
                    <div class="ml-3 flex-1">
                        <!-- Alert Header -->
                        <h3 class="text-sm font-medium text-warning-700">
                            {{ t('whatsapp_template_status_change_detected') }}
                        </h3>

                        <!-- Alert Description -->
                        <div class="mt-2 text-sm text-warning-700">
                            <p>
                                {{ t('we_have_received') }} <strong>{{ t('webhook_events_from_meta') }}</strong>
                                {{ t('whatsapp_messsage_template_changed_alert') }}
                            </p>
                        </div>

                        <!-- Warning Points -->
                        <div class="mt-4">
                            <div class="flex flex-col space-y-2">
                                <div class="flex items-start text-sm text-warning-700">
                                    <x-heroicon-o-exclamation-triangle
                                        class="h-4 w-4 mr-2 mt-0.5 text-warning-600 flex-shrink-0" />
                                    <span>{{ t('warning_point_1') }}</span>
                                </div>
                                <div class="flex items-start text-sm text-warning-700">
                                    <x-heroicon-o-exclamation-triangle
                                        class="h-4 w-4 mr-2 mt-0.5 text-warning-600 flex-shrink-0" />
                                    <span>{{ t('warning_point_2') }}</span>
                                </div>
                                <div class="flex items-start text-sm text-warning-700">
                                    <x-heroicon-o-check-circle
                                        class="h-4 w-4 mr-2 mt-0.5 text-warning-500 flex-shrink-0" />
                                    <span>{{ t('warning_point_3') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Section -->
                        <div class="flex justify-between items-center space-x-3 mt-4">
                            <div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <a href="{{ tenant_route('tenant.template.list') }}"
                                        class="inline-flex items-center justify-center px-4 py-2 border border-warning-300 shadow-sm text-sm font-medium rounded-md text-warning-700 bg-white hover:bg-warning-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500 transition-all duration-200 hover:shadow-md">
                                        <x-heroicon-o-document-text class="h-4 w-4 mr-2" />
                                        {{ t('review_templates') }}
                                    </a>
                                    <a href="{{ tenant_route('tenant.templatebot.list') }}"
                                        class="inline-flex items-center justify-center px-4 py-2 border border-warning-300 shadow-sm text-sm font-medium rounded-md text-warning-700 bg-white hover:bg-warning-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500 transition-all duration-200 hover:shadow-md">
                                        <x-heroicon-o-beaker class="h-4 w-4 mr-2" />
                                        {{ t('check_template_bots') }}
                                    </a>
                                    <a href="{{ tenant_route('tenant.campaigns.list') }}"
                                        class="inline-flex items-center justify-center px-4 py-2 border border-warning-300 shadow-sm text-sm font-medium rounded-md text-warning-700 bg-white hover:bg-warning-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500 transition-all duration-200 hover:shadow-md sm:col-span-2 lg:col-span-1">
                                        <x-heroicon-o-paper-airplane class="h-4 w-4 mr-2" />
                                        {{ t('review_campaigns') }}
                                    </a>
                                </div>
                            </div>

                            <!-- Acknowledge Button Section -->
                            <div class="border-warning-200">
                                <button type="submit" wire:click="updateTemplateSettings"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-warning-600 hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500 transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                                    <x-heroicon-o-check-circle class="h-4 w-4 mr-2" />
                                    {{ t('i_acknowledge') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- Dashboard Header -->
    <div class="mb-6 bg-white dark:bg-slate-800 p-4 rounded-xl ring-1 ring-slate-300 dark:ring-slate-600">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ t('hello') }}
                        {{ $tenantUser ? $tenantUser->firstname . ' ' . $tenantUser->lastname : t('user') }},
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 mt-1">{{ t('empower_your_business') }}
                        {{ $appName }}</p>
                </div>
            </div>
            <div class="mt-6 md:mt-0">
                <div class="bg-slate-100 dark:bg-slate-700/50 px-6 py-4 rounded-lg flex flex-wrap gap-6">
                    <!-- Plan Information -->
                    <div class="flex items-center space-x-4">
                        <div class="bg-primary-100 dark:bg-primary-900/30 p-2 rounded-lg">
                            <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-9.618 5.04L2 8.5V14c0 4.97 4.03 9 9 9a9 9 0 009-9V8.5l-.382-.516z"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                                {{ $planName }}
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                @if ($nextBillingDate)
                                    @if ($activeSubscription && $activeSubscription->status === 'trial')
                                        {{ t('trial_ends') . ': ' . $nextBillingDate }}
                                        @if ($daysUntilBilling !== null)
                                            ({{ $daysUntilBilling > 0 ? $daysUntilBilling . ' ' . t('days_left') : t('expired') }})
                                        @endif
                                    @else
                                        {{ t('next_billing') }} {{ $nextBillingDate }}
                                        @if ($daysUntilBilling !== null && $daysUntilBilling > 0)
                                            ({{ $daysUntilBilling }} {{ t('days') }})
                                        @endif
                                    @endif
                                @else
                                    {{ t('no_active_subscription') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Manage Subscription Button -->
                    <div class="flex flex-col sm:flex-row gap-3 items-center space-x-3">
                        <button wire:click="refreshDashboardData" x-on:click="loadData"
                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 px-4 py-2 rounded-lg transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ t('refresh') }}
                        </button>
                        <button wire:click="redirectToSubscriptions"
                            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                            {{ t('manage_subscription') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage & Limits Section -->
    <div class="mb-6">
        <x-card>
            <x-slot:header>
                <h2
                    class="text-lg font-semibold text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                    {{ t('usage_limits') }}
                </h2>
            </x-slot:header>
            <x-slot:content>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Total Contacts -->
                    <x-dashboard.stats-card title="{{ t('contacts') }}" :value="$totalContacts" :limit="$contactLimit"
                        subtitle="{{ t('contacts') }}" action="{{ t('view') }}" color="amber" :bg="true"
                        href="{{ tenant_route('tenant.contacts.list') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- Template Bots -->
                    <x-dashboard.stats-card title="{{ t('template_bots') }}" :value="$totalTemplateBots" :limit="$templateBotLimit"
                        subtitle="{{ t('automated_templates') }}" action="{{ t('manage') }}" color="purple"
                        :bg="true" href="{{ tenant_route('tenant.templatebot.list') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- Message Bots -->
                    <x-dashboard.stats-card title="{{ t('message_bots') }}" :value="$totalMessageBots" :limit="$messageBotLimit"
                        subtitle="{{ t('auto_responders') }}" action="{{ t('manage') }}" color="cyan"
                        :bg="true" href="{{ tenant_route('tenant.messagebot.list') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- Campaigns -->
                    <x-dashboard.stats-card title="{{ t('campaigns') }}" :value="$totalCampaigns" :limit="$campaignLimit"
                        subtitle="{{ t('marketing_campaigns') }}" action="{{ t('view') }}" color="emerald"
                        :bg="true" href="{{ tenant_route('tenant.campaigns.list') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- AI Prompts -->
                    <x-dashboard.stats-card title="{{ t('ai_prompts') }}" :value="$totalAiPrompts" :limit="$aiPromptLimit"
                        subtitle="{{ t('smart_automation') }}" action="{{ t('manage') }}" color="rose"
                        :bg="true" href="{{ tenant_route('tenant.ai-prompt') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- Canned Replies -->
                    <x-dashboard.stats-card title="{{ t('canned_replies') }}" :value="$totalCannedReplies" :limit="$cannedReplyLimit"
                        subtitle="{{ t('quick_responses') }}" action="{{ t('manage') }}" color="orange"
                        :bg="true" href="{{ tenant_route('tenant.canned-reply') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- Staff -->
                    <x-dashboard.stats-card title="{{ t('staff') }}" :value="$teamMemberCount" :limit="$teamMemberLimit"
                        subtitle="{{ t('team_members') }}" action="{{ t('manage') }}" color="indigo"
                        :bg="true" href="{{ tenant_route('tenant.staff.list') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <x-dashboard.stats-card title="{{ t('bot_flow') }}" :value="$totalFlowsLimit" :limit="$totalFlowsUsage"
                        subtitle="{{ t('quick_bot_replies') }}" action="{{ t('manage') }}" color="indigo"
                        :bg="true" href="{{ tenant_route('tenant.bot-flow_list') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v16h16M4 12h2m2 0h2m2 0h2m2 0h2M9 8h2m2 0h2M7 16h2m2 0h2m2 0h2" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    <!-- Conversations -->
                    <x-dashboard.stats-card title="{{ t('conversation') }}" :value="is_numeric($totalConversations)
                        ? ($totalConversations >= 1000
                            ? number_format($totalConversations / 1000, 1)
                            : $totalConversations)
                        : t('unlimited')" :suffix="is_numeric($totalConversations)
                        ? ($totalConversations >= 1000
                            ? 'K'
                            : '')
                        : t('unlimited')"
                        :limit="is_numeric($conversationLimit)
                            ? ($conversationLimit >= 1000
                                ? number_format($conversationLimit / 1000, 0)
                                : $conversationLimit)
                            : t('unlimited')" :suffix_limit="is_numeric($conversationLimit) ? ($conversationLimit >= 1000 ? 'K' : '') : ''" subtitle="" action="{{ t('open_chat') }}"
                        color="blue" :bg="true" href="{{ tenant_route('tenant.chat') }}">
                        <x-slot:icon>
                            <svg class="h-6 w-6 text-info-600 dark:text-info-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </x-slot:icon>
                    </x-dashboard.stats-card>

                    {{ do_action('after_dashboard_stats_card') }}

                </div>
            </x-slot:content>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 rounded-xl ">
            <div x-data="audienceGrowthChart()" x-init="audienceData = @js($audienceGrowthData)">
                <x-card>
                    <x-slot:header>
                        <div class="flex justify-between items-center ">
                            <h2
                                class="text-lg font-semibold text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                                {{ t('audience_growth') }}
                            </h2>
                            <div class="flex space-x-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                                <button @click="switchChartMode('combination')"
                                    :class="currentMode === 'combination' ? 'bg-white dark:bg-slate-600 shadow-sm' : ''"
                                    class="px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 rounded-md transition-all duration-150 hover:bg-white dark:hover:bg-slate-600"
                                    title="Lines + Bars">
                                    {{ t('mixed') }}
                                </button>
                                <button @click="switchChartMode('stacked')"
                                    :class="currentMode === 'stacked' ? 'bg-white dark:bg-slate-600 shadow-sm' : ''"
                                    class="px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 rounded-md transition-all duration-150 hover:bg-white dark:hover:bg-slate-600"
                                    title="Stacked Area Chart">
                                    {{ t('stacked') }}
                                </button>
                            </div>
                        </div>
                    </x-slot:header>
                    <x-slot:content>

                        <div class="h-64">
                            <canvas x-ref="audienceChart"></canvas>
                        </div>

                    </x-slot:content>
                </x-card>
            </div>
        </div>

        <div class="rounded-xl">
            <x-card>
                <x-slot:header>
                    <h2
                        class="text-lg font-semibold text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                        {{ t('contact_sources') }}
                    </h2>
                </x-slot:header>
                <x-slot:content>
                    <div class="h-48 flex items-center justify-center mb-4" x-cloak>
                        <canvas x-ref="sourcesChart"></canvas>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, index) in legendItems" :key="index">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full" :style="`background-color: ${item.color}`"></div>
                                    <span class="text-sm text-slate-600 dark:text-slate-400"
                                        x-text="item.label"></span>
                                </div>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100"
                                    x-text="item.percentage + '%'"></span>
                            </div>
                        </template>
                    </div>
                </x-slot:content>
            </x-card>
        </div>
    </div>

    <!-- Performance Metrics Section -->

    <div class="flex flex-col lg:flex-row gap-6 mb-8">
        <!-- Weekly Message Volume Chart -->
        <div class="w-full lg:w-1/2 lg:flex-shrink-0 rounded-xl">
            <x-card>
                <x-slot:header>
                    <h2
                        class="text-lg font-semibold text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                        {{ t('weekly_message_volume') }}
                    </h2>
                    <h1>

                    </h1>
                </x-slot:header>
                <x-slot:content>
                    <div class="h-80" x-data="weeklyMessageChart()">
                        <canvas x-ref="weeklyChart"></canvas>
                    </div>
                </x-slot:content>
            </x-card>
        </div>

        <!-- Campaign Statistic Chart -->
        <div class="w-full lg:flex-1 rounded-xl ">
            <div x-data="campaignStatChart()" x-init="campaignData = @js($campaignStatisticsData)">
                <x-card>
                    <x-slot:header>
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('campaign_statistic') }}
                            </h2>
                            <div class="flex space-x-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                                <button @click="switchChartType('line')"
                                    :class="currentType === 'line' ? 'bg-white dark:bg-slate-600 shadow-sm' : ''"
                                    class="px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 rounded-md transition-all duration-150 hover:bg-white dark:hover:bg-slate-600"
                                    title="{{ t('line_chart') }}">
                                    {{ t('line') }}
                                </button>
                                <button @click="switchChartType('bar')"
                                    :class="currentType === 'bar' ? 'bg-white dark:bg-slate-600 shadow-sm' : ''"
                                    class="px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 rounded-md transition-all duration-150 hover:bg-white dark:hover:bg-slate-600"
                                    title="{{ t('bar_chart') }}">
                                    {{ t('bar') }}
                                </button>
                            </div>
                        </div>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="h-80">
                            <canvas x-ref="campaignChart"></canvas>
                        </div>
                    </x-slot:content>
                </x-card>

            </div>
        </div>
    </div>
</div>

<script>
    function audienceGrowthChart() {
        return {
            audienceData: {},
            chartInstance: null,
            currentMode: 'combination',
            init() {
                this.initChart();

                // Listen for Livewire updates
                Livewire.on('chartDataUpdated', () => {
                    this.audienceData = @this.audienceGrowthData || {};
                    this.initChart();
                });
            },
            switchChartMode(mode) {
                if (mode !== 'combination' && mode !== 'stacked') {
                    console.warn('Invalid chart mode:', mode);
                    return;
                }

                this.currentMode = mode;
                try {
                    this.initChart();
                } catch (error) {
                    console.error('Error switching chart mode:', error);
                }
            },
            getChartConfig() {
                try {
                    if (this.currentMode === 'stacked') {
                        return this.getStackedConfig();
                    } else {
                        return this.getCombinationConfig();
                    }
                } catch (error) {
                    console.error('Error getting chart config:', error);
                    // Return a simple fallback configuration
                    return {
                        type: 'line',
                        data: {
                            labels: this.audienceData.labels || ['No Data'],
                            datasets: [{
                                label: 'Audience',
                                data: [0],
                                borderColor: '#4F46E5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 2,
                                pointRadius: 3,
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    };
                }
            },
            getCombinationConfig() {
                // Ensure we have all required datasets
                if (!this.audienceData.datasets || this.audienceData.datasets.length < 4) {
                    console.warn('Insufficient dataset length for combination chart');
                    return this.getStackedConfig(); // Fallback to stacked
                }

                return {
                    type: 'line',
                    data: {
                        labels: this.audienceData.labels,
                        datasets: [
                            // Line charts for cumulative totals
                            {
                                ...this.audienceData.datasets[0], // Total Contacts
                                type: 'line',
                                yAxisID: 'y',
                                borderWidth: 3,
                                pointRadius: 5,
                                pointBackgroundColor: this.audienceData.datasets[0].borderColor,
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: false,
                                tension: 0.3
                            },
                            {
                                ...this.audienceData.datasets[2], // Total Leads
                                type: 'line',
                                yAxisID: 'y',
                                borderWidth: 3,
                                pointRadius: 5,
                                pointBackgroundColor: this.audienceData.datasets[2].borderColor,
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: false,
                                tension: 0.3
                            },
                            // Bar charts for new additions
                            {
                                ...this.audienceData.datasets[1], // New Contacts
                                type: 'bar',
                                yAxisID: 'y1',
                                borderWidth: 0,
                                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderRadius: 4,
                                borderSkipped: false,
                            },
                            {
                                ...this.audienceData.datasets[3], // New Leads
                                type: 'bar',
                                yAxisID: 'y1',
                                borderWidth: 0,
                                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderRadius: 4,
                                borderSkipped: false,
                            }
                        ]
                    },
                    options: this.getCommonOptions()
                };
            },
            getStackedConfig() {
                // Ensure we have sufficient datasets
                if (!this.audienceData.datasets || this.audienceData.datasets.length < 2) {
                    console.warn('Insufficient dataset length for stacked chart');
                    // Create a simple fallback
                    return {
                        type: 'line',
                        data: {
                            labels: this.audienceData.labels || ['No Data'],
                            datasets: [{
                                label: 'Total Audience',
                                data: [0],
                                fill: 'origin',
                                backgroundColor: 'rgba(79, 70, 229, 0.3)',
                                borderColor: '#4F46E5',
                                borderWidth: 2,
                                pointRadius: 3,
                                tension: 0.4,
                            }]
                        },
                        options: this.getCommonOptions()
                    };
                }

                return {
                    type: 'line',
                    data: {
                        labels: this.audienceData.labels,
                        datasets: [{
                                ...this.audienceData.datasets[0], // Total Contacts
                                fill: 'origin',
                                backgroundColor: 'rgba(79, 70, 229, 0.3)',
                                borderColor: '#4F46E5',
                                borderWidth: 2,
                                pointRadius: 3,
                                tension: 0.4,
                            },
                            {
                                ...(this.audienceData.datasets[2] || this.audienceData.datasets[
                                    1]), // Total Leads or fallback
                                fill: '-1',
                                backgroundColor: 'rgba(245, 158, 11, 0.3)',
                                borderColor: '#F59E0B',
                                borderWidth: 2,
                                pointRadius: 3,
                                tension: 0.4,
                            }
                        ]
                    },
                    options: {
                        ...this.getCommonOptions(),
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Total Audience (Stacked)',
                                    color: '#64748B',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(100, 116, 139, 0.1)',
                                    borderColor: 'rgba(100, 116, 139, 0.2)'
                                },
                                ticks: {
                                    color: '#64748B',
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#64748B',
                                    maxRotation: 45,
                                    minRotation: 45,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                };
            },
            getCommonOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Audience (Cumulative)',
                                color: '#64748B',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(100, 116, 139, 0.1)',
                                borderColor: 'rgba(100, 116, 139, 0.2)'
                            },
                            ticks: {
                                color: '#64748B',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Monthly New Additions',
                                color: '#64748B',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                color: '#64748B',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748B',
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'center',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                padding: 20,
                                color: '#374151',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 13,
                                    weight: '500'
                                },
                                generateLabels: function(chart) {
                                    const labels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                    labels.forEach((label, index) => {
                                        const dataset = chart.data.datasets[index];
                                        if (dataset && dataset.type === 'line') {
                                            // Line series (total counts)
                                            label.pointStyle = 'line';
                                            label.lineWidth = 4;
                                            label.strokeStyle = dataset.borderColor;
                                            label.fillStyle = dataset.borderColor;
                                        } else {
                                            // Bar series (new additions)
                                            label.pointStyle = 'rectRounded';
                                            label.fillStyle = dataset.backgroundColor;
                                        }
                                    });
                                    return labels;
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#F9FAFB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    try {
                                        const datasetLabel = context.dataset.label || 'Unknown';
                                        const value = context.parsed.y || 0;
                                        const isTotal = datasetLabel.includes('Total');
                                        const isContact = datasetLabel.includes('Contact');
                                        const type = isContact ? 'Contact' : 'Lead';
                                        const category = isTotal ? 'Total' : 'New';

                                        return `${category} ${type}s: ${value.toLocaleString()}`;
                                    } catch (error) {
                                        console.error('Tooltip error:', error);
                                        return 'Data unavailable';
                                    }
                                },
                                afterBody: function(tooltipItems) {
                                    try {
                                        if (tooltipItems && tooltipItems.length > 0) {
                                            const month = tooltipItems[0].label || 'Unknown';
                                            return [``, `Period: ${month}`];
                                        }
                                        return [];
                                    } catch (error) {
                                        console.error('Tooltip afterBody error:', error);
                                        return [];
                                    }
                                }
                            }
                        }
                    }
                };
            },
            initChart() {
                // Destroy existing chart if it exists
                if (this.chartInstance) {
                    this.chartInstance.destroy();
                    this.chartInstance = null;
                }

                // Check if we have valid data
                if (!this.audienceData || !this.audienceData.labels || this.audienceData.labels.length === 0) {
                    console.warn('No valid audience data found, using fallback');
                    this.audienceData = {
                        labels: ['No Data'],
                        datasets: [{
                            label: 'No data available',
                            data: [0],
                            borderColor: '#9CA3AF',
                            backgroundColor: 'rgba(156, 163, 175, 0.1)',
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.4,
                            fill: true
                        }]
                    };
                }

                this.$nextTick(() => {
                    if (this.$refs.audienceChart) {
                        try {
                            const ctx = this.$refs.audienceChart.getContext('2d');
                            const config = this.getChartConfig();
                            this.chartInstance = new Chart(ctx, config);
                        } catch (error) {
                            console.error('Error creating chart:', error);
                        }
                    } else {
                        console.error('Canvas element not found');
                    }
                });
            }
        };
    }

    function campaignStatChart() {
        return {
            campaignData: {},
            chartInstance: null,
            currentType: 'bar',
            init() {
                this.initChart();

                // Listen for Livewire updates
                Livewire.on('chartDataUpdated', () => {
                    this.campaignData = @this.campaignStatisticsData || {};
                    this.initChart();
                });
            },
            switchChartType(type) {
                if (type !== 'line' && type !== 'bar') {
                    console.warn('Invalid chart type:', type);
                    return;
                }

                this.currentType = type;
                try {
                    this.initChart();
                } catch (error) {
                    console.error('Error switching campaign chart type:', error);
                }
            },
            getChartConfig() {
                try {
                    // Check if we have valid data
                    if (!this.campaignData || !this.campaignData.labels || this.campaignData.labels.length === 0) {
                        console.warn('No valid campaign data found, using fallback');
                        return this.getFallbackConfig();
                    }

                    if (this.currentType === 'line') {
                        return this.getLineConfig();
                    } else {
                        return this.getBarConfig();
                    }
                } catch (error) {
                    console.error('Error getting campaign chart config:', error);
                    return this.getFallbackConfig();
                }
            },
            getFallbackConfig() {
                return {
                    type: 'bar',
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            label: 'No data available',
                            data: [0],
                            backgroundColor: '#9CA3AF',
                            borderColor: '#9CA3AF',
                            borderWidth: 2,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };
            },
            getLineConfig() {
                return {
                    type: 'line',
                    data: {
                        labels: this.campaignData.labels,
                        datasets: this.campaignData.datasets.map(dataset => ({
                            ...dataset,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointBackgroundColor: dataset.borderColor,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            fill: false,
                            tension: 0.3
                        }))
                    },
                    options: this.getCommonOptions()
                };
            },
            getBarConfig() {
                return {
                    type: 'bar',
                    data: {
                        labels: this.campaignData.labels,
                        datasets: this.campaignData.datasets.map(dataset => ({
                            ...dataset,
                            borderWidth: 0,
                            borderRadius: 4,
                            borderSkipped: false,
                            // Use the border color as background for bars
                            backgroundColor: dataset.borderColor,
                        }))
                    },
                    options: this.getCommonOptions()
                };
            },
            getCommonOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Campaign Metrics',
                                color: '#64748B',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(100, 116, 139, 0.1)',
                                borderColor: 'rgba(100, 116, 139, 0.2)'
                            },
                            ticks: {
                                color: '#64748B',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748B',
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'center',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                padding: 20,
                                color: '#374151',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#F9FAFB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    try {
                                        const datasetLabel = context.dataset.label || 'Unknown';
                                        const value = context.parsed.y || 0;

                                        // Format the label based on dataset type
                                        if (datasetLabel.includes('Created')) {
                                            return `${datasetLabel}: ${value.toLocaleString()} campaigns`;
                                        } else if (datasetLabel.includes('Sent')) {
                                            return `${datasetLabel}: ${value.toLocaleString()} campaigns`;
                                        } else if (datasetLabel.includes('Delivered')) {
                                            return `${datasetLabel}: ${value.toLocaleString()} messages`;
                                        } else if (datasetLabel.includes('Read')) {
                                            return `${datasetLabel}: ${value.toLocaleString()} messages`;
                                        } else {
                                            return `${datasetLabel}: ${value.toLocaleString()}`;
                                        }
                                    } catch (error) {
                                        console.error('Campaign tooltip error:', error);
                                        return 'Data unavailable';
                                    }
                                },
                                afterBody: function(tooltipItems) {
                                    try {
                                        if (tooltipItems && tooltipItems.length > 0) {
                                            const month = tooltipItems[0].label || 'Unknown';
                                            return [``, `Period: ${month}`];
                                        }
                                        return [];
                                    } catch (error) {
                                        console.error('Campaign tooltip afterBody error:', error);
                                        return [];
                                    }
                                }
                            }
                        }
                    }
                };
            },
            initChart() {
                // Destroy existing chart if it exists
                if (this.chartInstance) {
                    this.chartInstance.destroy();
                    this.chartInstance = null;
                }

                // Check if we have valid data
                if (!this.campaignData || !this.campaignData.labels || this.campaignData.labels.length === 0) {
                    console.warn('No valid campaign data found, using fallback');
                    this.campaignData = {
                        labels: ['No Data'],
                        datasets: [{
                            label: 'No data available',
                            data: [0],
                            borderColor: '#9CA3AF',
                            backgroundColor: 'rgba(156, 163, 175, 0.1)',
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.4,
                            fill: true
                        }]
                    };
                }

                this.$nextTick(() => {
                    if (this.$refs.campaignChart) {
                        try {
                            const ctx = this.$refs.campaignChart.getContext('2d');
                            const config = this.getChartConfig();
                            this.chartInstance = new Chart(ctx, config);
                        } catch (error) {
                            console.error('Error creating campaign chart:', error);
                        }
                    } else {
                        console.error('Campaign chart canvas element not found');
                    }
                });
            }
        };
    }

    function weeklyMessageChart() {
        return {
            chartInstance: null,
            currentData: @entangle('weeklyMessageData'),
            init() {
                this.initChart();
                // Listen for chart data updates
                this.$wire.on('chartDataUpdated', () => {
                    this.refreshChart();
                });
            },
            initChart() {
                try {
                    const parsedData = typeof this.currentData === 'string' ?
                        JSON.parse(this.currentData) :
                        this.currentData;

                    if (!parsedData || !parsedData.labels || !parsedData.datasets) {
                        console.warn('Invalid weekly message data:', parsedData);
                        return;
                    }

                    this.$nextTick(() => {
                        if (this.$refs.weeklyChart) {
                            // Destroy existing chart if it exists
                            if (this.chartInstance) {
                                this.chartInstance.destroy();
                            }

                            const ctx = this.$refs.weeklyChart.getContext('2d');
                            this.chartInstance = new Chart(ctx, {
                                type: 'line',
                                data: parsedData,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            grid: {
                                                color: 'rgba(0, 0, 0, 0.05)',
                                                borderColor: 'rgba(0, 0, 0, 0.1)'
                                            },
                                            ticks: {
                                                color: '#64748B'
                                            }
                                        },
                                        x: {
                                            grid: {
                                                display: false
                                            },
                                            ticks: {
                                                color: '#64748B'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                            labels: {
                                                usePointStyle: true,
                                                color: '#64748B'
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error creating weekly message chart:', error);
                }
            },
            refreshChart() {
                this.initChart();
            }
        };
    }

    function contactSourcesChart() {
        return {
            legendItems: [],
            chartInstance: null, // Add chart instance tracking
            currentData: @entangle('contactSourcesData'), // Use entangle for Livewire data binding
            init() {
                this.initChart();
            },
            loadData() {
                setTimeout(() => {
                    this.initChart();
                }, 500);
                // Reinitialize chart with new data
            },
            initChart() {
                // Get dynamic data from Livewire backend
                const sourcesDataRaw = JSON.parse(this.currentData);

                // Parse if it's a string, otherwise use directly
                const sourcesData = typeof sourcesDataRaw === 'string' ? JSON.parse(sourcesDataRaw) : sourcesDataRaw;

                // Create dynamic color palette
                const colors = [
                    '#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444',
                    '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
                ];

                // Prepare chart data with fallbacks
                const labels = sourcesData.labels || ['No Data'];
                const data = sourcesData.data || [1];
                const backgroundColors = labels.map((_, index) => colors[index % colors.length]);

                const chartData = {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                };

                // Calculate total for percentages
                const total = data.reduce((sum, value) => sum + value, 0);

                // Generate legend items dynamically
                this.legendItems = labels.map((label, index) => ({
                    label: label,
                    value: data[index],
                    percentage: total > 0 ? ((data[index] / total) * 100).toFixed(1) : '0.0',
                    color: backgroundColors[index]
                }));

                this.$nextTick(() => {
                    if (this.$refs.sourcesChart) {
                        // Destroy existing chart if it exists
                        if (this.chartInstance) {
                            this.chartInstance.destroy();
                        }

                        const ctx = this.$refs.sourcesChart.getContext('2d');
                        this.chartInstance = new Chart(ctx, {
                            type: 'pie',
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false // Hide chart.js legend since we're using custom legend
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed;
                                                const total = context.dataset.data.reduce((a, b) =>
                                                    a + b, 0);
                                                const percentage = total > 0 ? ((value / total) *
                                                    100).toFixed(1) : '0.0';
                                                return `${label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            },


        };
    }
</script>
