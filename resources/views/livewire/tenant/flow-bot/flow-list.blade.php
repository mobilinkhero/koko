<div class="relative">
    <x-slot:title>
        {{ t('bot_flow') }}
    </x-slot:title>

       <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('bot_flow')],
    ]" />

    <div class="flex flex-col sm:flex-row gap-2 justify-between mb-4">
        @if (checkPermission('tenant.bot_flow.create'))
        <x-button.primary wire:click="createBotFlow" wire:loading.attr="disabled">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('bot_flow') }}
        </x-button.primary>
        @endif

        <!-- Feature Limit Badge -->
        <div>
            @if (isset($this->isUnlimited) && $this->isUnlimited)
            <x-unlimited-badge>
                {{ t('unlimited') }}
            </x-unlimited-badge>
            @elseif(isset($this->remainingLimit) && isset($this->totalLimit))
            <x-remaining-limit-badge label="{{ t('remaining') }}" :value="$this->remainingLimit"
                :count="$this->totalLimit" />
            @endif
        </div>
    </div>


    <x-card class="mx-4 lg:mx-0 rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0">
                <livewire:tenant.tables.flow-bot-table />
            </div>
        </x-slot:content>
    </x-card>

    <x-modal.custom-modal :id="'source-modal'" :maxWidth="'2xl'" wire:model.defer="showFlowModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('bot_flow') }}
            </h1>
        </div>
        <!-- Feature Limit Warning  -->
        @if (!$botFlow->exists && isset($this->hasReachedLimit) && $this->hasReachedLimit)
        <div class="px-6 pt-4">
            <div class="rounded-md bg-warning-50 dark:bg-warning-900/30 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-warning-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                            {{ t('flow_limit_reached') }}</h3>
                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                            <p>{{ t('bot_flow_limit_reached_message') }} <a
                                    href="{{ tenant_route('tenant.subscription') }}" class="font-medium underline">{{
                                    t('upgrade_plan') }}</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('name') }}
                        </x-label>
                    </div>
                    <x-input wire:model.defer="botFlow.name" type="text" id="name" class="w-full" />
                    <x-input-error for="botFlow.name" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">

                        <x-label for="page.description"
                            class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('description') }}
                        </x-label>
                    </div>
                    <x-textarea wire:model.defer="botFlow.description" rows="4"></x-textarea>
                    <x-input-error for="botFlow.description" class="mt-2" />
                </div>

            </div>

            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showFlowModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-source-modal'" title="{{ t('delete_bot_flow') }}"
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