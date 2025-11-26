<div>
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'route' => route('admin.dashboard')],
        ['label' => t('coupons')]
    ]" />

    <!-- Header with Action Buttons -->
    <div class="mb-3">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <x-settings-heading>
                {{ t('manage_coupons') }}
            </x-settings-heading>

            <div class="flex space-x-3 mt-4 md:mt-0">
                <x-button.primary href="{{ route('admin.coupons.create') }}">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    {{ t('create_coupon') }}
                </x-button.primary>
            </div>
        </div>
    </div>


    <!-- PowerGrid Table -->
    <x-card>
        <div wire:poll.30s="refreshTable">
            <x-slot:content>
                <livewire:admin.tables.coupon-table/>
            </x-slot:content>

        </div>
    </x-card>

    <!-- Confirm Delete Modal -->
    <x-modal.confirm-box
        :id="'confirm-delete-modal'"
        title="Delete Coupon"
        description="Are you sure you want to delete this coupon? This action cannot be undone."
        wire:model.defer="confirmingDeletion"
    >
        <div class="flex justify-end items-center space-x-3 bg-gray-100 dark:bg-gray-700">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="deleteCoupon" wire:loading.attr="disabled" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>

    <!-- Coupon Usage Drawer -->
    <livewire:admin.coupon.coupon-usage-drawer />
</div>
