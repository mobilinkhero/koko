<div x-data="{
    handleKeydown(e) {
        if (e.key === 'Escape' && $wire.open) {
            $wire.closeDrawer()
        }
    }
}" x-on:keydown.window="handleKeydown($event)">
    <!-- Overlay -->
    <div x-show="$wire.open" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-60 z-40" wire:click="closeDrawer">
    </div>

    <!-- Drawer -->
    <div x-show="$wire.open" x-transition:enter="transform transition ease-in-out duration-500"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-500" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 h-full w-full sm:w-96 lg:w-1/3 xl:w-1/4 bg-white dark:bg-gray-800 shadow-xl z-50 overflow-y-auto">

        <!-- Header -->
        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('coupon_usage_details') }}
                    </h3>
                    @if ($coupon)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $coupon->name }} ({{ $coupon->code }})
                        </p>
                    @endif
                </div>
                <button wire:click="closeDrawer"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            @if ($coupon)
                <!-- Coupon Summary -->
                <div class="mb-6">
                    <x-card>
                        <x-slot:content>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('type') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-2">
                                        {{ ucfirst(str_replace('_', ' ', $coupon->type)) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('value') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-2">
                                        @if ($coupon->type === 'percentage')
                                            {{ $coupon->value }}%
                                        @else
                                            {{ get_base_currency()->format($coupon->value) }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('total_usage') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-2">
                                        {{ $coupon->usage_count }}
                                        @if ($coupon->usage_limit)
                                            / {{ $coupon->usage_limit }}
                                        @else
                                            / ∞
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('status') }}:</span>
                                    <span class="ml-2">
                                        @if ($coupon->is_active)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                {{ t('active') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                {{ t('inactive') }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>
                </div>

                <!-- Usage Statistics -->
                @if (count($usageDetails) > 0)
                    <div class="mb-6">
                        <x-card>
                            <x-slot:content>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div class="text-center">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ count($usageDetails) }}
                                        </div>
                                        <div class="text-gray-600 dark:text-gray-400">{{ t('total_uses') }}</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ get_base_currency()->format(collect($usageDetails)->sum('discount_amount')) }}
                                        </div>
                                        <div class="text-gray-600 dark:text-gray-400">{{ t('total_savings') }}</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ collect($usageDetails)->pluck('tenant_name')->unique()->count() }}
                                        </div>
                                        <div class="text-gray-600 dark:text-gray-400">{{ t('unique_tenants') }}</div>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-card>
                    </div>
                @endif

                <!-- Usage History -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                        {{ t('usage_history') }} ({{ count($usageDetails) }} {{ t('uses') }})
                    </h4>

                    @if (count($usageDetails) > 0)
                        <div class="space-y-4">
                            @foreach ($usageDetails as $usage)
                                <x-card>
                                    <x-slot:content class="py-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <x-heroicon-o-building-office class="w-4 h-4 text-gray-400" />
                                                    <span class="font-medium text-gray-900 dark:text-white">
                                                        {{ $usage['tenant_name'] }}
                                                    </span>
                                                    @if ($usage['tenant_domain'])
                                                        <span
                                                            class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                                            {{ $usage['tenant_domain'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                                    <div class="flex items-center space-x-4">
                                                        <div class="flex items-center space-x-1">
                                                            <x-heroicon-o-currency-dollar class="w-4 h-4" />
                                                            <span>{{ t('discount') }}: {{ $usage['formatted_discount'] }}</span>
                                                        </div>
                                                        @if ($usage['invoice_total'] !== 'N/A')
                                                            <div class="flex items-center space-x-1">
                                                                <x-heroicon-o-document-text class="w-4 h-4" />
                                                                <span>{{ t('total') }}: {{ $usage['invoice_total'] }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if ($usage['plan_name'] !== 'N/A')
                                                        <div class="flex items-center space-x-1">
                                                            <x-heroicon-o-cube class="w-4 h-4" />
                                                            <span>{{ t('plan') }}: {{ $usage['plan_name'] }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($usage['invoice_id'])
                                                        <div class="flex items-center space-x-1">
                                                            <x-heroicon-o-hashtag class="w-4 h-4" />
                                                            <span>{{ t('invoice') }}: #{{ $usage['invoice_id'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $usage['created_at'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-inbox class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                {{ t('coupon_not_used_yet') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
