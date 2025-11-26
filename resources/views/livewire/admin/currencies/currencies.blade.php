<div class="relative">
    <x-slot:title>
        {{ t('currencies') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('currencies')],
    ]" />

    <x-dynamic-alert type="warning" class="mb-4">
        <div class="space-y-1">
            <p class="text-warning-700 dark:text-warning-300">
                <strong>{{ t('before_start_using_subscription') }}</strong><br>
               {{ t('about_base_currency') }} <br>
                <span class="block mt-1">{{ t('once_active_subscription') }} <strong>{{ t('cannot_chnage') }}</strong> {{ t('the_base_currency') }}</span>
                <span class="block mt-1">{{ t('currency_issue_instruction') }}</span>
            </p>
        </div>
    </x-dynamic-alert>

    @if (checkPermission('admin.currency.create'))
    <div class="flex justify-start mb-3 items-center gap-2">
        <x-button.primary wire:click="createCurrencies">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('currencies') }}
        </x-button.primary>
    </div>
    @endif

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.currency-table />
            </div>
        </x-slot:content>
    </x-card>

    {{-- Delete Currency Modal --}}
    <x-modal.confirm-box :id="'delete-currency-modal'" title="{{ t('delete_currencies') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click.debounce.200="delete" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>

    {{-- Add Currency Modal --}}
    <x-modal.custom-modal :id="'showCurrenciesModal'" :maxWidth="'3xl'" wire:model="showCurrenciesModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('currencies') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">

                <x-dynamic-alert type="warning">
                    <div class="space-y-1">
                        <p class="font-medium text-warning-800 dark:text-warning-200">{{ t('valid_currency_alert') }}
                        </p>
                        <p class="text-warning-700 dark:text-warning-300">{{ t('iso_currency_codes') }}
                        </p>
                        <p class="text-warning-700 dark:text-warning-300">{{ t('set_base_currency') }}</p>
                    </div>
                </x-dynamic-alert>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span> <label
                            class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('currency_code')
                            }}</label>
                    </div>
                    <x-input wire:model.defer="name" type="text" id="name" class="w-full" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Code input field -->
                    <div class="w-full">
                        <div class="flex item-centar justify-start gap-1">
                            <span class="text-danger-500">*</span> <label
                                class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('code')
                                }}</label>
                        </div>
                        <x-input wire:model.defer="code" type="text" id="code" class="w-full" />
                        <x-input-error for="code" class="mt-2" />
                    </div>

                    <!-- Symbol input field -->
                    <div class="w-full">
                        <div class="flex item-centar justify-start gap-1">
                            <span class="text-danger-500">*</span> <label
                                class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('symbol')
                                }}</label>
                        </div>
                        <x-input wire:model.defer="symbol" type="text" id="symbol" class="w-full" />
                        <x-input-error for="symbol" class="mt-2" />
                    </div>
                </div>

                <div class="w-full sm:flex-1">
                    <div class="flex items-center justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="format" :value="t('currency_placement')" />
                    </div>
                    <div class="flex flex-col sm:flex-row mt-1 sm:space-x-4 space-y-2 sm:space-y-0">
                        <label class="relative flex items-center">
                            <input type="radio" value="before_amount" wire:model.defer="format" checked
                                class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">{{ t('before_amount') }}</span>
                        </label>
                        <label class="relative flex items-center">
                            <input type="radio" value="after_amount" wire:model.defer="format"
                                class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">{{ t('after_amount') }}</span>
                        </label>
                    </div>
                    <x-input-error for="format" class="mt-2" />
                </div>
                <div
                    class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                    <x-button.secondary wire:click="$set('showCurrenciesModal', false)">
                        {{ t('cancel') }}
                    </x-button.secondary>
                    <x-button.loading-button type="submit" target="save">
                        {{ t('submit') }}
                    </x-button.loading-button>
                </div>

        </form>
    </x-modal.custom-modal>

</div>