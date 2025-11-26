<div class="relative">
    <x-slot:title>
        {{ t('status') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('status')],
    ]" />

    <div class="flex flex-col sm:flex-row gap-4 justify-between mb-3 items-start">
        @if(checkPermission('tenant.status.create'))
        <x-button.primary wire:click="createStatusPage" wire:loading.attr="disabled">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_status') }}
        </x-button.primary>
        @endif
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.status-table />
            </div>
        </x-slot:content>
    </x-card>

    <x-modal.custom-modal :id="'status-modal'" :maxWidth="'2xl'" wire:model.defer="showStatusModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('status') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="status.name" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('name') }}
                        </x-label>
                    </div>
                    <x-input wire:model.defer="status.name" type="text" id="status.name" class="w-full" />
                    <x-input-error for="status.name" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="status.color" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('color') }}
                        </x-label>
                    </div>
                    <div class="group relative" x-data="{ color: @entangle('status.color') }">
                        <div class="flex items-center gap-3">
                            <x-input wire:model.defer="status.color" type="text" id="status-color-text" x-model="color"
                                class="w-full pl-11 pr-4 py-2.5" placeholder="{{ t('status_color_placeholder') }}" />
                            <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                <x-label for="status-color-picker" class="cursor-pointer" />
                                <div class="w-6 h-6 rounded-md border-2 border-slate-200 shadow-sm overflow-hidden transition-transform hover:scale-105 dark:border-slate-600"
                                    :style="`background-color: ${color}`">
                                    <x-input id="status-color-picker" type="color" x-model="color"
                                        wire:model.defer="status.color"
                                        class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" />
                                </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <x-input-error for="status.color" class="mt-2" />
                </div>
            </div>
            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showStatusModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-status-modal'" title="{{ t('delete_status_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" wire:loading.attr="disabled" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>