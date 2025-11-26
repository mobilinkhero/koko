<div class="relative">
    <x-slot:title>
        {{ t('role') }}
    </x-slot:title>

       <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('role')],
    ]" />

    @if (checkPermission('tenant.role.create'))
    <div class="flex justify-start mb-3 px-4 lg:px-0 items-center gap-2">
        <a href="{{ tenant_route('tenant.roles.save') }}">
            <x-button.primary>
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_role') }}
            </x-button.primary>
        </a>
    </div>
    @endif

    <x-card class="mx-4 lg:mx-0 rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
               <livewire:tenant.tables.tenant-role-table />
            </div>
        </x-slot:content>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-role-modal'" title="{{ t('delete_role') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
