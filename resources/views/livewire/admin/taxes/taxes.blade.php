<div class="relative">
    <x-slot:title>
        {{ t('taxes') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('taxes')],
    ]" />

    <div class="flex justify-start mb-3 items-center gap-2">
        <x-button.primary wire:click="createTaxes">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('taxes') }}
        </x-button.primary>
    </div>


    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.tax-table />
            </div>
        </x-slot:content>
    </x-card>

    {{-- Delete Tax Modal --}}
    <x-modal.confirm-box :id="'delete-tax-modal'" title="{{ t('delete_taxes') }}" wire:model.defer="confirmingDeletion"
        description="{{ t('delete_message') }} ">
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

    {{-- Add Tax Modal --}}
    <x-modal.custom-modal :id="'showTaxesModal'" :maxWidth="'3xl'" wire:model="showTaxesModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('taxes') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span> <label
                            class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('tax_name')
                            }}</label>
                    </div>
                    <x-input wire:model.defer="name" type="text" id="name" class="w-full" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span> <label
                            class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('rate') }}</label>
                    </div>
                    <div class="relative">
                        <x-input wire:model.defer="rate" type="number" step="0.01" min="0" max="100" id="rate"
                            class="w-full pr-8" />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500">%</span>
                        </div>
                    </div>
                    <x-input-error for="rate" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <label class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{ t('description')
                            }}</label>
                    </div>
                    <x-textarea wire:model.defer="description" id="description" class="w-full" rows="3"></x-textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>

                <div
                    class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                    <x-button.secondary wire:click="$set('showTaxesModal', false)">
                        {{ t('cancel') }}
                    </x-button.secondary>
                    <x-button.loading-button type="submit" target="save">
                        {{ t('submit') }}
                    </x-button.loading-button>
                </div>
            </div>
        </form>
    </x-modal.custom-modal>
</div>