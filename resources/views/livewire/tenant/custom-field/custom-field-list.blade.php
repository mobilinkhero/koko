<div class="relative">
    <x-slot:title>
        {{ t('custom_field') }}
    </x-slot:title>

        <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('custom_field')],
    ]" />


    <div class="flex flex-col sm:flex-row gap-4 justify-between mb-3 items-start">
        @if ($this->canCreate)
            <x-button.primary href="{{ tenant_route('tenant.custom-fields.create') }}">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                {{ t('new_custom_field') }}
            </x-button.primary>
        @endif
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshList">
                <livewire:tenant.tables.custom-field-table />
            </div>
        </x-slot:content>
    </x-card>

    <!-- Delete Confirmation Modal -->
    @if ($confirmingDeletion)
        <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-custom-field-modal'" title="{{ t('delete_custom_field') }}"
            wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }}">
            <div class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700">
                <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                    {{ t('cancel') }}
                </x-button.cancel-button>
                <x-button.delete-button wire:click="delete" wire:loading.attr="disabled" class="mt-3 sm:mt-0">
                    {{ t('delete') }}
                </x-button.delete-button>
            </div>
        </x-modal.confirm-box>
    @endif
</div>
