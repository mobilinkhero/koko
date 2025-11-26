<div class="relative">
    <x-slot:title>
        {{ t('groups') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('groups')],
    ]" />

    <div class="flex flex-col sm:flex-row justify-between mb-3 items-start">
        @if(checkPermission('tenant.group.create'))
        <div class="flex items-center gap-2">
            <x-button.primary wire:click="createGroupPage" wire:loading.attr="disabled">
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_group') }}
            </x-button.primary>
        </div>
        @endif
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.groups-table />
            </div>
        </x-slot:content>
    </x-card>

    <x-modal.custom-modal :id="'group-modal'" :maxWidth="'2xl'" wire:model.defer="showGroupModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('group') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="group.name" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('name') }}
                        </x-label>
                    </div>
                    <x-input wire:model.defer="group.name" type="text" id="group.name" class="w-full" />
                    <x-input-error for="group.name" class="mt-2" />
                </div>
            </div>

            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showGroupModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-group-modal'" title="{{ t('delete_group_title') }}"
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