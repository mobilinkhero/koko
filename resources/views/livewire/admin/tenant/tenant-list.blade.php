<div class="relative">
    <x-slot:title>
        {{ t('tenants') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('tenants')],
    ]" />

    <div class="flex justify-start mb-3 lg:px-0 items-center gap-2">
        @if(checkPermission('admin.tenants.create'))
        <a href="{{ route('admin.tenants.save') }}">
            <x-button.primary>
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_tenant') }}
            </x-button.primary>
        </a>
        @endif
    </div>

    <x-card class="lg:mx-0 rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.tenant-table />
            </div>
        </x-slot:content>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'2xl'" :id="'delete-tenant-modal'" title="{{ t('delete_tenant_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_tenant_description') }}">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)" class="">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" class="mt-3 sm:mt-0">
                {{ t('mark_tenant_for_deletion') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
