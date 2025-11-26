<div class="relative" x-init="getObserver()">
    <x-slot:title>
        {{ t('staff') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('staff')],
    ]" />

    <div class="flex flex-col sm:flex-row gap-4 justify-between mb-4 items-start">
        <x-button.primary wire:click="createStaff">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('add_staff') }}
        </x-button.primary>
        <!-- Feature Limit Badge -->
        <div>
            <!-- Feature Limit Badge with improved styling -->

            @if (isset($this->isUnlimited) && $this->isUnlimited)
                <x-unlimited-badge>
                    {{ t('unlimited') }}
                </x-unlimited-badge>
            @elseif(isset($this->remainingLimit))
                <x-remaining-limit-badge label="{{ t('remaining') }}" :value="$this->remainingLimit" :count="$this->totalLimit" />
            @endif

        </div>
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.staff-table />
            </div>
        </x-slot:content>
    </x-card>


    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-staff-modal'" title="{{ t('delete_staff_title') }}"
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
