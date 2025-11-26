<div class="relative">
    <x-slot:title>
        {{ t('ai_prompt') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('ai_prompt')],
    ]" />

    <div class="flex flex-col sm:flex-row gap-4 justify-between mb-3 items-start">
        @if(checkPermission('tenant.ai_prompt.create'))
        <x-button.primary wire:click="createAiPrompt" wire:loading.attr="disabled">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('ai_prompt') }}
        </x-button.primary>
        @endif
        <!-- Feature Limit Badge -->
        <div>
            <!-- Feature Limit Badge with improved styling -->

            @if (isset($this->isUnlimited) && $this->isUnlimited)
            <x-unlimited-badge>
                {{ t('unlimited') }}
            </x-unlimited-badge>
            @elseif(isset($this->remainingLimit))
            <x-remaining-limit-badge label="{{ t('remaining') }}" :value="$this->remainingLimit"
                :count="$this->totalLimit" />
            @endif

        </div>
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:tenant.tables.ai-prompt-table />
            </div>
        </x-slot:content>
    </x-card>

    <x-modal.custom-modal :id="'ai_prompt-modal'" :maxWidth="'2xl'" wire:model="showAiPromptModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('ai_prompt') }}
            </h1>
        </div>
        <!-- Feature Limit Warning  -->

        @if (!$prompt && isset($this->hasReachedLimit) && $this->hasReachedLimit)
        <div class="px-6 pt-4">
            <div class="rounded-md bg-warning-50 dark:bg-warning-900/30 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-warning-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                            {{ t('ai_prompt_limit_reached') }}</h3>
                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                            <p>{{ t('ai_prompt_limit_reached_message') }} <a
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
                        <x-label for="prompt.name" class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{
                            t('name') }}</x-label>
                    </div>
                    <x-input wire:model.defer="prompt.name" type="text" id="prompt.name" class="w-full" />
                    <x-input-error for="prompt.name" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="prompt.action" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('action') }}
                        </x-label>
                    </div>
                    <x-textarea wire:model.defer="prompt.action" wire:blur="validatePromtAction" rows="3"></x-textarea>
                    <x-input-error for="prompt.action" class="mt-2" />
                </div>

            </div>
            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showAiPromptModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>

        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-ai_prompt-modal'" title="{{ t('delete_ai_prompts_title') }}"
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