<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Order Management') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Manage customer orders and track status
                </p>
            </div>
            
            <div class="flex gap-3">
                <button wire:click="createOrder" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    ➕ Create Order
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-800/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Confirmed</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['confirmed'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-800/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Delivered</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['delivered'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-800/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-800/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text" 
                       wire:model.debounce.300ms="search"
                       placeholder="Order #, customer name..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select wire:model="statusFilter" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">All Status</option>
                    @foreach($orderStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment</label>
                <select wire:model="paymentStatusFilter" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">All Payments</option>
                    @foreach($paymentStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                <select wire:model="dateRange" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                <select wire:model="sortBy" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="created_at">Date</option>
                    <option value="order_number">Order Number</option>
                    <option value="total_amount">Total Amount</option>
                    <option value="customer_name">Customer</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->order_number ?: 'Draft' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('M j, Y g:i A') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->customer_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $order->customer_phone }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ count($order->items ?? []) }} item(s)
                                    @if(count($order->items ?? []) > 0)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            @foreach(collect($order->items)->take(2) as $item)
                                                {{ $item['quantity'] }}x {{ $item['product_name'] }}<br>
                                            @endforeach
                                            @if(count($order->items) > 2)
                                                +{{ count($order->items) - 2 }} more...
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($order->total_amount, 2) }}</div>
                                @if($order->currency !== 'USD')
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->currency }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select wire:change="updateOrderStatus({{ $order->id }}, $event.target.value)" 
                                        class="text-sm rounded-full px-2 py-1 font-medium border-0 focus:ring-2 focus:ring-blue-500
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/50 dark:text-yellow-200' : '' }}
                                            {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800/50 dark:text-blue-200' : '' }}
                                            {{ $order->status === 'processing' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800/50 dark:text-indigo-200' : '' }}
                                            {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800 dark:bg-purple-800/50 dark:text-purple-200' : '' }}
                                            {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-800/50 dark:text-green-200' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-800/50 dark:text-red-200' : '' }}">
                                    @foreach($orderStatuses as $value => $label)
                                        <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select wire:change="updatePaymentStatus({{ $order->id }}, $event.target.value)" 
                                        class="text-sm rounded-full px-2 py-1 font-medium border-0 focus:ring-2 focus:ring-blue-500
                                            {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/50 dark:text-yellow-200' : '' }}
                                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-800/50 dark:text-green-200' : '' }}
                                            {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-800/50 dark:text-red-200' : '' }}
                                            {{ $order->payment_status === 'refunded' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800/50 dark:text-gray-200' : '' }}">
                                    @foreach($paymentStatuses as $value => $label)
                                        <option value="{{ $value }}" {{ $order->payment_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="viewOrder({{ $order->id }})" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">No orders found</p>
                                <button wire:click="createOrder" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Create First Order
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Order Details Modal -->
    @if($showOrderModal && $viewingOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Order Details</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $viewingOrder->order_number ?: 'Draft Order' }}</p>
                            </div>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Customer Info -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Customer Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Name:</span> {{ $viewingOrder->customer_name }}</p>
                                    <p><span class="font-medium">Phone:</span> {{ $viewingOrder->customer_phone }}</p>
                                    @if($viewingOrder->customer_email)
                                        <p><span class="font-medium">Email:</span> {{ $viewingOrder->customer_email }}</p>
                                    @endif
                                    @if($viewingOrder->customer_address)
                                        <p><span class="font-medium">Address:</span> {{ $viewingOrder->customer_address }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Order Info -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Order Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Date:</span> {{ $viewingOrder->created_at->format('M j, Y g:i A') }}</p>
                                    <p><span class="font-medium">Status:</span> 
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800/50 dark:text-blue-200">
                                            {{ ucfirst($viewingOrder->status) }}
                                        </span>
                                    </p>
                                    <p><span class="font-medium">Payment:</span> 
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/50 dark:text-green-200">
                                            {{ ucfirst($viewingOrder->payment_status) }}
                                        </span>
                                    </p>
                                    <p><span class="font-medium">Payment Method:</span> {{ ucfirst(str_replace('_', ' ', $viewingOrder->payment_method)) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mt-6">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Order Items</h4>
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        @foreach($viewingOrder->items ?? [] as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $item['product_name'] }}
                                                    @if(isset($item['sku']))
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $item['sku'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{ number_format($item['price'], 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item['quantity'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${{ number_format($item['total'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Order Totals -->
                            <div class="mt-4 flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Subtotal:</span>
                                        <span>${{ number_format($viewingOrder->subtotal, 2) }}</span>
                                    </div>
                                    @if($viewingOrder->tax_amount > 0)
                                        <div class="flex justify-between text-sm">
                                            <span>Tax:</span>
                                            <span>${{ number_format($viewingOrder->tax_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($viewingOrder->shipping_amount > 0)
                                        <div class="flex justify-between text-sm">
                                            <span>Shipping:</span>
                                            <span>${{ number_format($viewingOrder->shipping_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between text-lg font-medium border-t pt-2">
                                        <span>Total:</span>
                                        <span>${{ number_format($viewingOrder->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($viewingOrder->notes)
                            <div class="mt-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Notes</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">{{ $viewingOrder->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
