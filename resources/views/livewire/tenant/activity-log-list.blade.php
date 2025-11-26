<div class="relative">
    <x-slot:title>
        {{ t('activity_log_list') }}
    </x-slot:title>

    <div class="flex justify-start mb-3 items-center gap-2">
      
        @if(checkPermission('tenant.activity_log.delete'))
            <x-button.danger wire:click="confirmDelete">
                <x-heroicon-c-trash class="w-4 h-4 mr-1" />{{ t('clear_log') }}
            </x-button.danger>
        @endif
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.wm-activity-table />
            </div>
        </x-slot:content>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-activity-modal'" title="{{ t('delete_activity_log_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click.debounce.300ms="delete" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
