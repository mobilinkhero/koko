<div>
    <x-slot:title>
        {{ t('admin_dashboard') }} - {{ $adminName }}
    </x-slot:title>

    {{-- version update alert --}}
    @php
        $settings = get_batch_settings(['whats-mark.whatsmark_latest_version', 'whats-mark.wm_version']);
    @endphp
    @if (
        $settings['whats-mark.whatsmark_latest_version'] != null &&
            $settings['whats-mark.whatsmark_latest_version'] != $settings['whats-mark.wm_version'] &&
            $settings['whats-mark.wm_version'] <= $settings['whats-mark.whatsmark_latest_version']
    )
        <div class="mb-3">
            <div>
                <x-dynamic-alert type="primary">
                    <p>{{ t('new_update_available_alert') }} <a href="/admin/system-update"
                            class="alert-link underline font-semibold">{{ t('click_here') }}</a>{{ t('to_update_version') }}
                    </p>
                </x-dynamic-alert>
            </div>
        </div>
    @endif
    @if (!env('APP_PREVIOUS_KEYS'))
        <div class="mb-3 mt-3">
            <div>
                <x-dynamic-alert type="danger">
                    <x-slot:title class="mb-3">{{ t('configuration_sync_required') }}</x-slot:title>
                    <p>{{ t('current_system_requirements') }} <a wire:click="updateEnv()"
                            class="alert-link cursor-pointer font-semibold underline">{{ t('click_here') }}</a>
                        {{ t('current_system_requirements_contenant_2') }}</p>
                </x-dynamic-alert>
            </div>
        </div>
    @endif
    <div class="mb-3" x-cloak x-data="{
        appMode: '{{ app()->environment() }}',
        appDebug: @json(config('app.debug')),
        isVisible() {
            return this.appMode === 'local' && this.appDebug;
        }
    }" x-bind:class="{ 'hidden': !isVisible() }">
        <div x-show="isVisible()">
            <x-dynamic-alert type="warning">
                <x-slot:title class="mb-3">{{ t('development_warning_title') }}</x-slot:title>

                {{ t('development_warning_content') }}
                <ul>
                    <li><strong>{{ t('app_env') }}</strong> <span>{{ t('production') }}</span></li>
                    <li><strong>{{ t('app_debug') }}</strong> <span>{{ t('debug_false') }}</span></li>
                </ul>

                {{ t('development_warning_details') }}
                {{ t('performance_security_tip') }}
            </x-dynamic-alert>
        </div>
    </div>
    <!-- Dashboard Header -->
    <div class="mb-4 bg-white dark:bg-slate-800 px-4 py-3 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                    {{ t('hello') }}, {{ $adminName }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ t('welcome_to_dashboard') }}
                    <span class="ml-2 inline-flex items-center">
                        <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ t('last_updated') }} <span
                            class="font-medium text-slate-600 dark:text-slate-300">{{ $lastUpdated }}</span>
                    </span>
                </p>
            </div>
            <button wire:click="refreshDashboardData" wire:loading.class="opacity-75 cursor-wait"
                wire:loading.attr="disabled" wire:target="refreshDashboardData"
                class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 px-2.5 py-1.5 rounded-md transition-colors flex items-center text-xs">
                <svg wire:loading.remove wire:target="refreshDashboardData" class="w-3.5 h-3.5 mr-1" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="refreshDashboardData" class="animate-spin w-3.5 h-3.5 mr-1"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="refreshDashboardData">{{ t('refresh') }}</span>
                <span wire:loading wire:target="refreshDashboardData">{{ t('refresh') }}...</span>
            </button>
        </div>
    </div>

    <!-- Statistics Cards Section -->
    <x-card>
        <x-slot:header>
            <h2 class="text-lg font-medium text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                {{ t('system_statistics') }}
            </h2>
        </x-slot:header>
        <x-slot:content>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Active Subscriptions -->
                <x-dashboard.stats-card title="{{ t('total_subscriptions') }}" :value="$activeSubscriptions"
                    subtitle="Since Last Month: {{ $activeSubscriptionsChange >= 0 ? '+' : '' }}{{ $activeSubscriptionsChange }}"
                    color="indigo" :bg="true">
                    <x-slot:icon>
                        <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-9.618 5.04L2 8.5V14c0 4.97 4.03 9 9 9a9 9 0 009-9V8.5l-.382-.516z" />
                        </svg>
                    </x-slot:icon>
                </x-dashboard.stats-card>

                <!-- Total Earnings -->
                <x-dashboard.stats-card title="{{ t('total_earnings') }}" :value="get_base_currency()->format($totalEarnings)"
                    subtitle="Since Last Month: {{ $totalEarningsChange >= 0 ? '+' : '' }}{{ get_base_currency()->format($totalEarningsChange) }}"
                    color="emerald" :bg="true">
                    <x-slot:icon>
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-dashboard.stats-card>

                <!-- Total Clients -->
                <x-dashboard.stats-card title="{{ t('total_clients') }}" :value="$totalClients"
                    subtitle="Since Last Month: {{ $totalClientsChange >= 0 ? '+' : '' }}{{ $totalClientsChange }}"
                    color="blue" :bg="true">
                    <x-slot:icon>
                        <svg class="h-6 w-6 text-info-600 dark:text-info-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-dashboard.stats-card>

                <!-- Total Campaigns -->
                <x-dashboard.stats-card title="{{ t('total_campaigns') }}" :value="$totalCampaigns"
                    subtitle="Since Last Month: {{ $totalCampaignsChange >= 0 ? '+' : '' }}{{ $totalCampaignsChange }}"
                    color="purple" :bg="true">
                    <x-slot:icon>
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </x-slot:icon>
                </x-dashboard.stats-card>
            </div>
        </x-slot:content>
    </x-card>

    <!-- Charts Section -->
    <div class="mt-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <!-- Earnings Report - 60% width -->
            <div class="w-full sm:w-3/5">
                <x-card>
                    <x-slot:header>
                        <h2
                            class="text-lg font-medium text-slate-700 dark:text-slate-300  border-slate-200 dark:border-slate-700">
                            {{ t('earnings_report') }}
                        </h2>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="h-72" wire:ignore>
                            <canvas id="earningsChart"></canvas>
                        </div>
                    </x-slot:content>
                </x-card>
            </div>

            <!-- Plan Distribution - 40% width -->
            <div class="w-full sm:w-2/5">
                <x-card>
                    <x-slot:header>
                        <h2
                            class="text-lg font-medium text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700">
                            {{ t('best_selling_plan') }}
                        </h2>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="space-y-3" id="plan-cards">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg text-center">
                                <p class="text-gray-500 dark:text-gray-400">{{ t('loading_plan_data') }}</p>
                            </div>
                        </div>
                    </x-slot:content>
                </x-card>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Charts Initialization -->
<script>
    document.addEventListener('livewire:initialized', function() {
        // Wait for DOM to be fully rendered before initializing charts
        setTimeout(() => {
            initEarningsChart();
            initPlanDistributionChart(); // Call function to initialize plan cards
        }, 100);

        Livewire.on('chartDataUpdated', function() {
            setTimeout(() => {
                initEarningsChart();
                initPlanDistributionChart(); // Call function to refresh plan cards
            }, 100);
        });

        Livewire.on('reload-page', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1000); // 1 second delay
        });
    });

    function initEarningsChart() {
        const earningsData = @js($earningsData);
        const ctx = document.getElementById('earningsChart');
        const currencyFormat = @json($currencyFormat);
        const baseCurrency = @json($baseCurrency);

        // Destroy existing chart if it exists
        if (window.earningsChart instanceof Chart) {
            window.earningsChart.destroy();
        }

        window.earningsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: earningsData.labels,
                datasets: earningsData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 12
                        },
                        bodyFont: {
                            size: 11
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    const value = context.parsed.y.toLocaleString();
                                    label += currencyFormat === 'before_amount' ? baseCurrency + value :
                                        value + baseCurrency;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(200, 200, 200, 0.2)'
                        },
                        ticks: {
                            callback: function(value) {
                                return currencyFormat == 'before_amount' ? baseCurrency + value
                                    .toLocaleString() : value.toLocaleString() + baseCurrency;
                            },
                            font: {
                                size: 10
                            },
                            padding: 5
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 3,
                        hoverRadius: 5
                    },
                    line: {
                        tension: 0.4,
                        borderWidth: 2
                    }
                }
            }
        });
    }

    function initPlanDistributionChart() {
        // Plan distribution now uses cards instead of charts
        // We're using JavaScript to render the cards dynamically
        const planData = @js($planDistributionData);
        const planCardsContainer = document.getElementById('plan-cards');

        if (!planCardsContainer) {
            console.error('Plan cards container not found');
            return;
        }

        // Clear existing content
        planCardsContainer.innerHTML = '';

        // Check if we have plans data
        if (planData && planData.plans && planData.plans.length > 0) {
            // Render each plan as a card
            planData.plans.forEach(plan => {
                const color = plan.color || '#6b7280';
                const name = plan.name || 'Unknown Plan';
                const count = plan.count || 0;
                const price = plan.price || '0.00';
                const currency = '{{ $baseCurrency }}';

                const cardHtml = `
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="h-full">
                                <div class="h-full w-1.5 rounded-full" style="background-color: ${color}"></div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900 dark:text-white">${name}</p>
                                    <p class="font-medium text-gray-900 dark:text-white">${count}</p>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">${price}</p>
                            </div>
                        </div>
                    </div>
                `;

                planCardsContainer.innerHTML += cardHtml;
            });
        } else {
            // No plans available
            planCardsContainer.innerHTML = `
                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg text-center">
                    <p class="text-gray-500 dark:text-gray-400">No active subscriptions</p>
                </div>
            `;
        }
    }
</script>
