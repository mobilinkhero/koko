<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('E-commerce Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Manage your WhatsApp e-commerce automation
                </p>
            </div>
            
            @if($isConfigured)
                <div class="flex gap-3">
                    <button wire:click="syncNow" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sync Now
                    </button>
                    
                    @if(($stats['total_products'] ?? 0) > 0)
                        <button wire:click="clearAllProducts" 
                                wire:confirm="Are you sure you want to clear all {{ $stats['total_products'] }} products? This action cannot be undone. You'll need to sync again to restore products from your sheet."
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear Products
                        </button>
                    @endif
                    
                    <a href="{{ tenant_route('tenant.ecommerce.setup') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    @if(!$isConfigured)
        <!-- Setup Required -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-8 border border-blue-200 dark:border-blue-800">
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-800/50 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Welcome to WhatsApp E-commerce!
                    </h2>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-8 text-lg leading-relaxed max-w-2xl mx-auto">
                        Transform your WhatsApp into a powerful sales platform. Automate orders, manage inventory with Google Sheets, 
                        and boost sales with AI-powered recommendations and upselling.
                    </p>

                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-800/50 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Google Sheets Integration</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Sync products and orders with your Google Sheets in real-time</p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-800/50 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">AI-Powered Sales</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Smart product recommendations and automated upselling</p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-800/50 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Automated Payments</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Multiple payment methods and automated order tracking</p>
                        </div>
                    </div>

                    <button wire:click="redirectToSetup" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        🚀 Start E-commerce Setup
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Dashboard Content -->
        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Products Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Products</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_products'] ?? 0) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800/50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">
                            <span class="text-green-600 font-medium">{{ $stats['active_products'] ?? 0 }}</span> active
                            @if(($stats['low_stock_products'] ?? 0) > 0)
                                • <span class="text-red-600 font-medium">{{ $stats['low_stock_products'] }}</span> low stock
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Orders Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Orders</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-800/50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">
                            <span class="text-orange-600 font-medium">{{ $stats['pending_orders'] ?? 0 }}</span> pending
                            • <span class="text-green-600 font-medium">{{ $stats['completed_orders'] ?? 0 }}</span> completed
                        </p>
                    </div>
                </div>

                <!-- Revenue Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-800/50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">
                            This month: <span class="text-green-600 font-medium">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</span>
                        </p>
                    </div>
                </div>

                <!-- Sync Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sync Status</p>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                @if($config && $config->last_sync_at)
                                    Synced
                                @else
                                    Never synced
                                @endif
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-800/50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                    </div>
                    @if($config && $config->last_sync_at)
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">{{ $config->last_sync_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions & Recent Orders -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ tenant_route('tenant.ecommerce.products') }}" 
                           class="block w-full text-left px-4 py-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-800/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Manage Products</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">View and edit product catalog</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ tenant_route('tenant.ecommerce.orders') }}" 
                           class="block w-full text-left px-4 py-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-800/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Process Orders</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage customer orders</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ tenant_route('tenant.ecommerce.analytics') }}" 
                           class="block w-full text-left px-4 py-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-800/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">View Analytics</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Sales reports & insights</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                        <a href="{{ tenant_route('tenant.ecommerce.orders') }}" 
                           class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium">
                            View All
                        </a>
                    </div>

                    @if(empty($stats['recent_orders']) || count($stats['recent_orders']) == 0)
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No orders yet</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Orders will appear here once customers start purchasing</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($stats['recent_orders'] as $order)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800/50 rounded-lg flex items-center justify-center">
                                            <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">#{{ substr($order->order_number, -4) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $order->customer_name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->created_at->format('M j, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900 dark:text-white">${{ number_format($order->total_amount, 2) }}</p>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full 
                                                   @if($order->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                                                   @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300
                                                   @elseif($order->status === 'delivered') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300
                                                   @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
