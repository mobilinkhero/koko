<div>
    <!-- Stripe webhook management interface -->
    <div class="max-w-full">
        <!-- Webhook Management Card -->
        <x-card>
            <x-slot:content>
                <div x-data="{ selectedEvents: @entangle('selectedEvents') }">

                    <x-slot:header>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ t('configure_stripe_webhooks') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ t('setup_webhook_receive_real_time_notification') }}
                        </p>
                    </x-slot:header>
                    <form wire:submit.prevent="configureWebhook">
                        <!-- URL Input -->
                        <div class="mb-5">
                            <label for="customUrl"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('webhook_endpoint_url_stripe') }}
                            </label>
                            <div class="mt-1 relative">
                                <input type="url" id="customUrl" wire:model="customUrl"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    placeholder="https://example.com/webhooks/stripe" required readonly />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ t('this_url_must_be_publicly_accessible_for_stripe_to_send_webhook_events') }}
                            </p>
                        </div>



                        <!-- Submit Button -->
                        <x-slot:footer class="bg-gray-50 dark:bg-transparent px-6 py-3">
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 disabled:opacity-50"
                                    wire:loading.attr="disabled" wire:click="configureWebhook">
                                    <span wire:loading.remove wire:target="configureWebhook">
                                        {{ t('configure_webhook') }}
                                    </span>
                                    <span wire:loading wire:target="configureWebhook">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ t('configuring') }}
                                    </span>
                                </button>
                            </div>
                        </x-slot:footer>
                    </form>
                </div>
            </x-slot:content>
        </x-card>

        <!-- Local Webhooks Card -->
        <x-card class="mt-6">
            <x-slot:header>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('local_webhook_records') }}</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ t('webhook_configurations_stored_in_your_application_database') }}
                </p>
            </x-slot:header>
            <x-slot:content>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('id') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('provider') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('webhook_id') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('endpoint_url') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('status') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('last_activity') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($localWebhooks as $webhook)
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $webhook->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($webhook->provider) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-mono">{{ $webhook->webhook_id }}</span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $webhook->endpoint_url }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $webhook->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300' : 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300' }}">
                                        {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $webhook->last_pinged_at ? $webhook->last_pinged_at->diffForHumans() : 'Never' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    {{ t('no_local_webhook_records_found') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile responsive table alternative -->
                <div class="sm:hidden border-t border-gray-200 dark:border-gray-700">
                    @forelse ($localWebhooks as $webhook)
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">ID:
                                {{ $webhook->id }}</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $webhook->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300' : 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300' }}">
                                {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                            <span class="font-medium">{{ t('Provider') }}:</span> {{ ucfirst($webhook->provider) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1 truncate">
                            <span class="font-medium">{{ t('webhook_id') }}:</span>
                            <span class="font-mono">{{ $webhook->webhook_id }}</span>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1 truncate">
                            <span class="font-medium">{{ t('url') }}:</span> {{ $webhook->endpoint_url }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-medium">{{ t('last_activity') }}:</span>
                            {{ $webhook->last_pinged_at ? $webhook->last_pinged_at->diffForHumans() : 'Never' }}
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                        {{ t('no_local_webhook_records_found') }}
                    </div>
                    @endforelse
                </div>
            </x-slot:content>
        </x-card>

        <!-- Stripe Webhook Details Card -->
        <x-card class="mt-6">
            <x-slot:header>
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-0 sm:items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                            <x-heroicon-o-exclamation-circle class="w-5 h-5 mr-2 text-primary-500" />
                            {{ t('stripe_webhook_details') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ t('information_about_your_configured_stripe_webhooks') }}
                        </p>
                    </div>
                    <button wire:click="loadWebhooks"
                        class="inline-flex items-center text-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 transition-colors duration-150 ">
                        <x-heroicon-o-arrow-path class="w-4 h-4 mr-1.5" />
                        {{ t('refresh') }}
                    </button>
                </div>
            </x-slot:header>
            <x-slot:content>
                <!-- Loading State -->
                <div wire:loading.flex wire:target="loadWebhooks" class="items-center justify-center p-6">
                    <svg class="animate-spin h-6 w-6 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ t('loading_webhook_details')
                        }}</span>
                </div>
                <!-- Webhook Details Content -->
                <div wire:loading.remove wire:target="loadWebhooks">
                    @if (!empty($stripeWebhooks))
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <div class="p-4 sm:px-6">
                            <div class="flex flex-wrap items-center justify-between mb-1">
                                <div class="flex items-center mb-2 sm:mb-0">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white mr-2">{{
                                        t('webhook_id_stripe') }}</span>
                                    <code
                                        class="text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded font-mono">{{ $stripeWebhooks['id'] }}</code>
                                </div>
                                <div class="flex items-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stripeWebhooks['status'] === 'enabled' ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300' : 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300' }}">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 {{ $stripeWebhooks['status'] === 'enabled' ? 'text-success-400' : 'text-warning-400' }}"
                                            fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{ ucfirst($stripeWebhooks['status'] ?? 'Unknown') }}
                                    </span>
                                    <button wire:click="confirmDeleteWebhook('{{ $stripeWebhooks['id'] }}')"
                                        class="ml-2 inline-flex items-center text-sm text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        <span class="ml-1 hidden sm:inline">{{ t('delete') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Webhook Details Grid -->
                        <div class="px-4 sm:px-6 pb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- URL Section -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{
                                        t('endpoint_url')}}</h4>
                                    <div class="flex items-center break-all">
                                        <code
                                            class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded font-mono">{{ $stripeWebhooks['url'] }}</code>
                                    </div>
                                </div>

                                <!-- API Version Section -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API
                                        {{ t('version') }}</h4>
                                    <div class="flex items-center">
                                        <code
                                            class="text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded font-mono">{{ $stripeWebhooks['api_version'] ?? '2023-10-16' }}</code>
                                    </div>
                                </div>
                            </div>

                            <!-- Events Section -->
                            <div
                                class="mt-4 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{
                                    t('enabled_events')}}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach ($stripeWebhooks['enabled_events'] ?? [] as $event)
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs truncate">{{ $event }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ t('no_webhooks_found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ t('no_stripe_webhooks_are_currently_configured') }}
                        </p>
                    </div>
                    @endif
                </div>
            </x-slot:content>
        </x-card>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" class="fixed inset-0 overflow-y-auto z-50"
        x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-danger-100 dark:bg-danger-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-danger-600 dark:text-danger-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                {{ t('delete_webhook') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('delete_webhook_confirmation') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="deleteWebhook" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-danger-600 text-base font-medium text-white hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ t('delete') }}
                    </button>
                    <button wire:click="cancelDelete" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ t('cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>