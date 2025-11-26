<div>
    
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row gap-4 justify-between ">
             <x-breadcrumb :items="[
            ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
            ['label' => t('credit_list'), 'route' => route('admin.credit-management.list') ],
            ['label' => t('credit_details')],   
        ]" />
        </div>
        <x-card>
            <!-- Header Section -->
            <x-slot:header>
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-heroicon-o-banknotes class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                            {{ t('credit_details') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ t('manage_credit_balance_for_tenant') }}
                        </p>
                    </div>
                </div>
            </x-slot:header>

            <x-slot:content>
                <!-- Tenant Info Card -->
                <div class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600 mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                    <span class="text-lg font-medium text-primary-600 dark:text-primary-300">
                                        {{ strtoupper(substr($tenant->company_name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    {{ $tenant->company_name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $tenant->subdomain }}.{{ config('app.domain') }}
                                </p>
                            </div>
                            <div class="ml-auto">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $tenant->status === 'active' ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200' : 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200' }}">
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Credit Balances -->
                    <div class="lg:col-span-1">
                        <div class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                    {{ t('credit_balances') }}
                                </h3>

                                @if ($creditBalances->count() > 0)
                                <div class="space-y-4">
                                    @foreach ($creditBalances as $balance)
                                    <div
                                        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $balance->currency->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $balance->currency->code }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $balance->currency->symbol }}{{ number_format($balance->balance, 2)
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center py-6">
                                    <x-heroicon-o-banknotes class="mx-auto h-8 w-8 text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('no_credit_balance') }}
                                    </p>
                                </div>
                                @endif


                            </div>
                        </div>
                    </div>

                    <!-- Credit History -->
                    <div class="lg:col-span-2">
                        <div class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                    {{ t('credit_transaction_history') }}
                                </h3>

                                @if ($creditHistory->count() > 0)
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        @foreach ($creditHistory as $index => $transaction)
                                        <li>
                                            <div class="relative pb-8">
                                                @if (!$loop->last)
                                                <span
                                                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-600"
                                                    aria-hidden="true"></span>
                                                @endif

                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span
                                                            class="h-8 w-8 rounded-full flex items-center justify-center
                                                        {{ $transaction->type === 'credit' ? 'bg-success-500' : 'bg-danger-500' }}">
                                                            @if ($transaction->type === 'credit')
                                                            <x-heroicon-s-plus class="w-5 h-5 text-white" />
                                                            @else
                                                            <x-heroicon-s-minus class="w-5 h-5 text-white" />
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between">
                                                            <div class="text-sm">
                                                                <span class="font-medium text-gray-900 dark:text-white">
                                                                    {{ $transaction->type === 'credit' ?
                                                                    t('credit_added') : t('credit_deducted') }}
                                                                </span>
                                                            </div>
                                                            <div class="text-right text-sm">
                                                                <span
                                                                    class="font-semibold {{ $transaction->type === 'credit' ? 'text-success-600' : 'text-danger-600' }}">
                                                                    {{ $transaction->type === 'credit' ? '+' : '-' }}{{
                                                                    $transaction->currency->symbol }}{{
                                                                    number_format($transaction->amount, 2) }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $transaction->description }}
                                                        </div>

                                                        @if ($transaction->invoice)
                                                        <div class="mt-1">
                                                            <a href="{{ route('admin.invoices.show', $transaction->invoice->id) }}"
                                                                class="text-xs text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                                                {{ t('invoice') }}
                                                                #{{ $transaction->invoice->invoice_number }}
                                                            </a>
                                                        </div>
                                                        @endif

                                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $transaction->created_at->format('M j, Y \a\t g:i A') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @else
                                <div class="text-center py-8">
                                    <x-heroicon-o-clock class="mx-auto h-8 w-8 text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('no_credit_transactions') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</div>