<div class="relative">
    <x-slot:title>
        {{ t('message_bots') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('message_bots')],
    ]" />

    <div class="flex flex-col sm:flex-row gap-2 justify-between mb-4">
        @if(checkPermission('tenant.message_bot.create'))
        <a href="{{ tenant_route('tenant.messagebot.create') }}">
            <x-button.primary>
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('message_bot') }}
            </x-button.primary>
        </a>
        @endif

        <!-- Feature Limit Badge -->
        <div>
            @if (isset($this->isUnlimited) && $this->isUnlimited)
                <x-unlimited-badge>
                    {{ t('unlimited') }}
                </x-unlimited-badge>
            @elseif(isset($this->remainingLimit) && isset($this->totalLimit))
                <x-remaining-limit-badge label="{{ t('remaining') }}" :value="$this->remainingLimit" :count="$this->totalLimit" />
            @endif
        </div>
    </div>


    <x-card >
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.message-bot-table />
            </div>
        </x-slot:content>
    </x-card>

    {{-- Delete confirmation --}}
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-messagebot-modal'" title="{{ t('delete_message_bot') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200  flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
