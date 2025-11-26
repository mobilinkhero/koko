<div class="relative">
    <x-slot:title>
        {{ t('users') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('users')],
    ]" />

    <div class="flex justify-start mb-3  items-center gap-2">
        <a href="{{ route('admin.users.save') }}">
            <x-button.primary>
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_user') }}
            </x-button.primary>
        </a>
    </div>

    <x-card class=" rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.user-table />
            </div>
        </x-slot:content>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-user-modal'" title="{{ t('delete_user_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)" class="">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
