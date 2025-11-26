<div>
    <x-slot:title>
        {{ t('tenant_details') }} - {{ $tenant->company_name }}
    </x-slot:title>
    <x-card class="mb-6">
        <x-slot:header class="flex justify-between items-start flex-col sm:flex-row gap-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-11 w-11 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-primary-600 font-semibold">{{ substr($tenant->company_name, 0, 1) }}</span>
                </div>
                <div class="ml-3">
                    <h1 class="font-semibold text-primary-600">{{ $tenant->company_name }}</h1>
                    <div class="flex items-center">
                        <span
                            class="text-primary-600 break-all">{{ config('app.url') . '/' . $tenant->subdomain }}</span>
                        <a href="{{ $tenant->url }}" target="_blank" class="ml-2 text-primary-600">
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <!-- Status Badge -->
                <div class="flex items-center">
                    @switch($tenant->status)
                        @case('active')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300">
                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1" />
                                {{ t('active') }}
                            </span>
                        @break

                        @case('deactive')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300">
                                <x-heroicon-s-pause-circle class="w-4 h-4 mr-1" />
                                {{ t('deactive') }}
                            </span>
                        @break

                        @case('suspended')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300">
                                <x-heroicon-s-x-circle class="w-4 h-4 mr-1" />
                                {{ t('suspended') }}
                            </span>
                        @break

                        @default
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ ucfirst($tenant->status) }}
                            </span>
                    @endswitch
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-row gap-2">
                    @if (checkPermission('admin.tenants.edit'))
                        <x-button.secondary href="{{ route('admin.tenants.save', ['tenantId' => $tenant->id]) }}">
                            <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" />
                            {{ t('edit') }}
                        </x-button.secondary>
                    @endif

                    @if (checkPermission('admin.tenants.edit'))
                        @if ($tenant->status === 'active')
                            <x-button.danger wire:click="confirmStatusChange('deactive')">
                                <x-heroicon-o-pause-circle class="w-4 h-4 mr-1" />
                                {{ t('deactivate') }}
                            </x-button.danger>
                        @else
                            <x-button.primary wire:click="confirmStatusChange('active')">
                                <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                                {{ t('activate') }}
                            </x-button.primary>
                        @endif
                    @endif
                </div>
            </div>
        </x-slot:header>
        <x-slot:content>
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Active Subscriptions -->
                <div
                    class="bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg p-4 border border-primary-200 dark:border-primary-700/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                {{ $subscriptionSummary['active_subscriptions'] }}
                            </div>
                            <div class="text-sm text-primary-600/70 dark:text-primary-400/70 font-medium">
                                {{ t('active_subscriptions') }}</div>
                        </div>
                        <div class="p-2 bg-primary-100 dark:bg-primary-800/50 rounded-lg">
                            <x-heroicon-s-calendar-days class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>
                </div>

                <!-- Total Invoices -->
                <div
                    class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-lg p-4 border border-emerald-200 dark:border-emerald-700/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $subscriptionSummary['total_invoices'] }}
                            </div>
                            <div class="text-sm text-emerald-600/70 dark:text-emerald-400/70 font-medium">
                                {{ t('total_invoices') }}</div>
                        </div>
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-800/50 rounded-lg">
                            <x-heroicon-s-document-text class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                    </div>
                </div>

                <!-- Paid Invoices -->
                <div
                    class="bg-gradient-to-br from-success-50 to-success-100 dark:from-success-900/20 dark:to-success-800/20 rounded-lg p-4 border border-success-200 dark:border-success-700/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                                {{ $subscriptionSummary['paid_invoices'] }}
                            </div>
                            <div class="text-sm text-success-600/70 dark:text-success-400/70 font-medium">
                                {{ t('paid_invoices') }}</div>
                        </div>
                        <div class="p-2 bg-success-100 dark:bg-success-800/50 rounded-lg">
                            <x-heroicon-s-check-circle class="h-5 w-5 text-success-600 dark:text-success-400" />
                        </div>
                    </div>
                </div>

                <!-- Successful Payments -->
                <div
                    class="bg-gradient-to-br from-info-50 to-info-100 dark:from-info-900/20 dark:to-info-800/20 rounded-lg p-4 border border-info-200 dark:border-info-700/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-info-600 dark:text-info-400">
                                {{ $subscriptionSummary['successful_transactions'] }}
                            </div>
                            <div class="text-sm text-info-600/70 dark:text-info-400/70 font-medium">
                                {{ t('successful_payments') }}</div>
                        </div>
                        <div class="p-2 bg-info-100 dark:bg-info-800/50 rounded-lg">
                            <x-heroicon-s-credit-card class="h-5 w-5 text-info-600 dark:text-info-400" />
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div
                    class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $users->total() }}
                            </div>
                            <div class="text-sm text-purple-600/70 dark:text-purple-400/70 font-medium">
                                {{ t('total_users') }}</div>
                        </div>
                        <div class="p-2 bg-purple-100 dark:bg-purple-800/50 rounded-lg">
                            <x-heroicon-s-users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <!-- Total Tickets -->
                <div
                    class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg p-4 border border-orange-200 dark:border-orange-700/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                {{ $subscriptionSummary['total_tickets'] }}
                            </div>
                            <div class="text-sm text-orange-600/70 dark:text-orange-400/70 font-medium">
                                {{ t('total_tickets') }}</div>
                        </div>
                        <div class="p-2 bg-orange-100 dark:bg-orange-800/50 rounded-lg">
                            <x-heroicon-s-ticket class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:content>
    </x-card>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <x-card class="mb-6 w-full self-start">
            <x-slot:header>
                <h3 class=" font-medium text-gray-900 dark:text-white">{{ t('tenant_information') }}</h3>
            </x-slot:header>
            <x-slot:content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dl class="space-y-4">
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('company_name') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $tenant->company_name ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('subdomain') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    <span class=" bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">
                                        {{ $tenant->subdomain }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('address') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white break-words">
                                    {{ $tenant->address ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('country') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ get_country_name($tenant->country_id) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('phone') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $tenant->adminUser?->phone ?: 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-4">
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('status') }}</dt>
                                <dd class="mt-1">
                                    @switch($tenant->status)
                                        @case('active')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300">
                                                {{ t('active') }}
                                            </span>
                                        @break

                                        @case('deactive')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300">
                                                {{ t('deactive') }}
                                            </span>
                                        @break

                                        @case('suspended')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300">
                                                {{ t('suspended') }}
                                            </span>
                                        @break

                                        @default
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ ucfirst($tenant->status) }}
                                            </span>
                                    @endswitch
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('created_at') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ format_date_time($tenant->created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('timezone') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $tenant->timezone ?: config('app.timezone') }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('email') }}</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white break-words">
                                    {{ $tenant->adminUser?->email ?: 'N/A' }}</dd>
                            </div>
                            @if ($tenant->expires_at)
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('expires_at') }}</dt>
                                    <dd class="mt-1">
                                        <span
                                            class="{{ $tenant->isExpired() ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">
                                            {{ format_date_time($tenant->expires_at) }}
                                            @if ($tenant->isExpired())
                                                <span class="text-xs">({{ t('expired') }})</span>
                                            @endif
                                        </span>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </x-slot:content>
        </x-card>

        {{ do_action('tenant.billing_information', $tenant) }}

        <!-- Current Subscription Details -->
        <x-card class="mb-6 w-full self-start">
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class=" font-medium text-gray-900 dark:text-white">{{ t('current_subscription') }}
                    </h3>
                    @if (checkPermission('admin.subscription.view'))
                        <a href="{{ route('admin.subscription.list') }}"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:hover:bg-primary-800">
                            <x-heroicon-o-cog-6-tooth class="h-4 w-4 mr-1" />
                            {{ t('manage') }}
                        </a>
                    @endif
                </div>
            </x-slot:header>
            <x-slot:content>
                @if ($subscription->exists)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Plan Information -->
                        <div class="rounded-lg">
                            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                                {{ t('plan_details') }}</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('plan') }}</dt>
                                    <dd class=" font-semibold text-gray-900 dark:text-white">
                                        {{ $subscription->plan->name }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('description') }}</dt>
                                    <dd class="text-gray-700 dark:text-gray-300 truncate">
                                        {{ $subscription->plan->description ?: 'No description available' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('price') }}</dt>
                                    <dd class=" font-semibold text-gray-900 dark:text-white">
                                        {{ $currency->format($subscription->plan->price) }} /
                                        {{ $subscription->plan->billing_period }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Subscription Status -->
                        <div class="rounded-lg">
                            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                                {{ t('subscription_status') }}</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('status') }}</dt>
                                    <dd class="mt-1">
                                        @switch($subscription->status)
                                            @case(\App\Models\Subscription::STATUS_ACTIVE)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300">
                                                    {{ t('active') }}
                                                </span>
                                            @break

                                            @case(\App\Models\Subscription::STATUS_TRIAL)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-300">
                                                    {{ t('trial') }}
                                                </span>
                                            @break

                                            @default
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ ucfirst($subscription->status) }}
                                                </span>
                                        @endswitch
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('started_at') }}</dt>
                                    <dd class="text-gray-900 dark:text-white">
                                        {{ format_date_time($subscription->created_at) }}</dd>
                                </div>
                                @if ($subscription->current_period_ends_at)
                                    <div>
                                        <dt class="font-medium text-gray-500 dark:text-gray-400">
                                            {{ t('ends_at') }}</dt>
                                        <dd class="">
                                            <span
                                                class="{{ $subscription->current_period_ends_at->isPast() ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">
                                                {{ format_date_time($subscription->current_period_ends_at) }}
                                                @if ($subscription->current_period_ends_at->isPast())
                                                    <span class="text-xs">({{ t('expired') }})</span>
                                                @endif
                                            </span>
                                        </dd>
                                    </div>
                                @endif
                                @if ($subscription->trial_ends_at)
                                    <div>
                                        <dt class="font-medium text-gray-500 dark:text-gray-400">
                                            {{ t('trial_ends') }}</dt>
                                        <dd class="text-gray-900 dark:text-white">
                                            {{ format_date_time($subscription->trial_ends_at) }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('auto_renewal') }}</dt>
                                    <dd class="">
                                        @if ($subscription->is_recurring)
                                            <span
                                                class="text-success-600 dark:text-success-400">{{ t('enabled') }}</span>
                                        @else
                                            <span
                                                class="text-danger-600 dark:text-danger-400">{{ t('disabled') }}</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-exclamation-circle class="h-12 w-12 text-warning-500 mx-auto mb-4" />
                        <h3 class=" font-medium text-gray-900 dark:text-white mb-2">
                            {{ t('no_active_subscription') }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">{{ t('tenant_has_no_subscription') }}</p>
                    </div>
                @endif
            </x-slot:content>
        </x-card>

        <!-- Recent Invoices -->
        @if ($subscription->exists && $subscription->invoices->count() > 0)
            <x-card class="mb-6 w-full self-start">
                <x-slot:header>
                    <h3 class=" font-medium text-gray-900 dark:text-white">{{ t('recent_invoices') }}</h3>
                </x-slot:header>
                <x-slot:content>
                    <div class="overflow-y-auto">
                        <ul class="">
                            @foreach ($subscription->invoices->take(5) as $invoice)
                                <li class="mb-6">
                                    <div class="flex flex-col sm:flex-row gap-4 justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if ($invoice->isPaid())
                                                    <div
                                                        class="h-8 w-8 bg-success-100 dark:bg-success-900 rounded-full flex items-center justify-center">
                                                        <x-heroicon-s-check
                                                            class="h-5 w-5 text-success-600 dark:text-success-400" />
                                                    </div>
                                                @else
                                                    <div
                                                        class="h-8 w-8 bg-warning-100 dark:bg-warning-900 rounded-full flex items-center justify-center">
                                                        <x-heroicon-s-clock
                                                            class="h-5 w-5 text-warning-600 dark:text-warning-400" />
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        {{ $invoice->invoice_number ?? format_draft_invoice_number() }}
                                                    </p>
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->isPaid() ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300' : 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300' }}">
                                                        {{ $invoice->isPaid() ? t('paid') : t('pending') }}
                                                    </span>
                                                </div>
                                                <p class="text-gray-500 dark:text-gray-400">
                                                    {{ format_date_time($invoice->created_at) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between space-x-4">
                                            <span
                                                class="font-medium text-gray-900 dark:text-white">{{ $invoice->formattedTotal() }}</span>
                                            @if (checkPermission('admin.invoices.view'))
                                                <x-button.secondary wire:click="downloadInvoice({{ $invoice->id }})"
                                                    class="text-xs">
                                                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                                </x-button.secondary>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-slot:content>
            </x-card>
        @endif
    </div>

    <!-- Status Change Confirmation Modal -->
    <x-modal.confirm-box wire:model.live="confirmingStatusChange" :maxWidth="'lg'">
        <x-slot:title>
            {{ t('change_tenant_status') }}
        </x-slot:title>

        <x-slot:content>
            @if ($newStatus === 'active')
                <p class="text-gray-700 dark:text-gray-300">{{ t('confirm_activate_tenant') }}</p>
                <div
                    class="mt-3 p-3 bg-success-50 dark:bg-success-900/20 rounded text-success-800 dark:text-success-300">
                    {{ t('activate_tenant_description') }}
                </div>
            @elseif($newStatus === 'deactive')
                <p class="text-gray-700 dark:text-gray-300">{{ t('confirm_deactivate_tenant') }}</p>
                <div
                    class="mt-3 p-3 bg-warning-50 dark:bg-warning-900/20 rounded text-warning-800 dark:text-warning-300">
                    {{ t('deactivate_tenant_description') }}
                </div>
            @elseif($newStatus === 'suspended')
                <p class="text-gray-700 dark:text-gray-300">{{ t('confirm_suspend_tenant') }}</p>
                <div class="mt-3 p-3 bg-danger-50 dark:bg-danger-900/20 rounded text-danger-800 dark:text-danger-300">
                    <strong>{{ t('warning') }}:</strong> {{ t('suspend_tenant_description') }}
                </div>
            @endif
        </x-slot:content>

        <x-slot:footer>
            <x-button.cancel-button wire:click="$set('confirmingStatusChange', false)" wire:loading.attr="disabled">
                {{ t('cancel') }}
            </x-button.cancel-button>

            <button type="button" wire:click="updateStatus" wire:loading.attr="disabled"
                class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm font-medium rounded-md
                {{ $newStatus === 'active'
                    ? 'text-white bg-success-600 hover:bg-success-700'
                    : ($newStatus === 'deactive'
                        ? 'text-white bg-warning-600 hover:bg-warning-700'
                        : 'text-white bg-danger-600 hover:bg-danger-700') }}">
                {{ t('confirm') }}
            </button>
        </x-slot:footer>
    </x-modal.confirm-box>
</div>
