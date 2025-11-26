<div class="relative">
    <x-slot:title>
        {{ t('languages') }}
    </x-slot:title>

        <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('languages')],
    ]" />

    <div class="flex justify-start mb-3 px-4 lg:px-0 items-center gap-2">
        <x-button.primary wire:click="createLanguage">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('languages') }}
        </x-button.primary>
        <x-button.primary wire:click="syncTenantLanguage" wire:loading.attr="disabled"
            wire:loading.class="opacity-75 cursor-not-allowed animate-pulse"
            class="transition-all duration-200 active:scale-95">
            <x-heroicon-s-arrow-path class="w-4 h-4 mr-1 transition-transform duration-500"
                wire:loading.class="animate-spin" />
            <span wire:loading.remove wire:target="syncTenantLanguage">{{ t('sync_languages') }}</span>
            <span wire:loading wire:target="syncTenantLanguage" class="flex items-center">
                <span class="animate-pulse">{{ t('syncing') }}</span>
                <span class="ml-1 animate-bounce">...</span>
            </span>
        </x-button.primary>
    </div>

    <x-card class="mx-4 lg:mx-0 rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.tenant-language-table />
            </div>
        </x-slot:content>
    </x-card>

    {{-- Add Language Modal --}}
    <x-modal.custom-modal :id="'showLanguageModal'" :maxWidth="'3xl'" wire:model="showLanguageModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('languages') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span> <label
                            class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('language_name')
                            }}</label>
                    </div>
                    <x-input wire:model.defer="name" type="text" id="name" class="w-full" placeholder="e.g., French" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span> <label
                            class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('language_code')
                            }}</label>
                    </div>
                    <x-input wire:model.defer="code" type="text" id="code" class="w-full" placeholder="e.g., fra" />
                    <x-input-error for="code" class="mt-2" />
                </div>
            </div>
            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showLanguageModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>

        </form>
    </x-modal.custom-modal>

    {{-- Delete Language Modal --}}
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-language-modal'" title="{{ t('delete_language') }}"
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


</div>