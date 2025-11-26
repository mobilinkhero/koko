<div class="relative">
    <x-slot:title>
        {{ t('template_bot') }}
    </x-slot:title>

   <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('template_bot')],
    ]" />

    <div class="flex flex-col sm:flex-row justify-between mb-4 gap-4 lg:px-0">
        @if(checkPermission('tenant.template_bot.create'))
        <a href="{{ tenant_route('tenant.templatebot.create') }}">
            <x-button.primary>
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('template_bot') }}
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


    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.template-bot-table />
            </div>
        </x-slot:content>
    </x-card>

    {{-- Delete confirmation --}}
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-tempbot-modal'" title="{{ t('delete_templatebot') }}"
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
