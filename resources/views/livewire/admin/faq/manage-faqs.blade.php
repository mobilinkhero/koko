<div class="relative">
    <x-slot:title>
        {{ t('faqs') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('faqs')],
    ]" />

    <div class="flex flex-col sm:flex-row flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            @if(checkPermission('admin.faq.create'))
            <x-button.primary wire:click="createFaq">
                <x-heroicon-m-plus class="w-4 h-4 mr-2" />{{ t('add_new_faq') }}
            </x-button.primary>
            @endif
        </div>
        <div class="w-full sm:w-2/3 lg:w-1/2 xl:w-1/3">
            <x-text-input id="searchFaq" type="text" wire:model.live="search" placeholder="Search FAQs..."
                class="w-full" />
        </div>
    </div>

    <!-- FAQs List -->
    <div class="space-y-4" wire:sortable="reorder">
        @forelse($this->faqs as $faq)
        <x-card class="p-0" wire:sortable.item="{{ $faq->id }}" wire:key="faq-{{ $faq->id }}">
            <x-slot:content>
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center space-x-4">
                        <!-- Drag Handle -->
                        <div wire:sortable.handle class="cursor-grab text-gray-400">
                            <x-heroicon-m-arrows-pointing-out class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <div>
                            <h3 class="font-medium dark:text-slate-300">{{ $faq->question }}</h3>
                            <p class="text-gray-600 text-sm">{{ Str::limit($faq->answer, 100) }}</p>
                        </div>
                    </div>
                    <!-- Responsive Actions -->
                    <div class="flex items-center space-x-3">
                        <!-- Visibility Toggle - Balanced size -->
                        @if (checkPermission('admin.faq.edit'))
                        <x-toggle :id="'faq-visible-'.$faq->id" :name="'faq-visible-'.$faq->id"
                            :value="$faq->is_visible" wire:change="toggleVisibility({{ $faq->id }})"
                            class="mt-0 p-1.5" />
                        @endif
                        <!-- Action Buttons -->
                        <!-- Edit Button -->
                        @if (checkPermission('admin.faq.edit'))
                        <button wire:click="editFaq({{ $faq->id }})"
                            class="p-1.5 text-info-600 hover:bg-info-100 rounded-full transition-colors duration-200 dark:text-info-400 dark:hover:bg-info-900/30"
                            title="{{ t('edit') }}">
                            <x-heroicon-o-pencil-square class="w-6 h-6" />
                        </button>
                        @endif
                        <!-- Delete Button -->
                        @if (checkPermission('admin.faq.delete'))
                        <button wire:click="confirmDelete({{ $faq->id }})"
                            class="p-1.5 text-danger-600 hover:bg-danger-100 rounded-full transition-colors duration-200 dark:text-danger-400 dark:hover:bg-danger-900/30"
                            title="{{ t('delete') }}">
                            <x-heroicon-o-trash class="w-6 h-6" />
                        </button>
                        @endif
                    </div>
                </div>
            </x-slot:content>
        </x-card>
        @empty
        <!-- No FAQs Message -->
        <x-card>
            <x-slot:content>
                <div class="text-center">
                    <x-carbon-warning class="w-12 h-12 mx-auto text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ t('no_faqs_found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('get_started_by_creating_new_faq') }}
                    </p>
                </div>
            </x-slot:content>
        </x-card>
        @endforelse
    </div>

    <x-modal.custom-modal :id="'faq-modal'" :maxWidth="'2xl'" wire:model.defer="showFaqModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('faqs') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">

            <div class="px-6 py-4 space-y-4">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="faq.question" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('question') }}</x-label>
                    </div>
                    <x-input type="text" wire:model.defer="faq.question" id="faq.question"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    <x-input-error for="faq.question" class="mt-2" />
                </div>

                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="faq.answer" class="dark:text-gray-300 block text-sm font-medium text-gray-700">{{
                            t('answer') }}</x-label>
                    </div>
                    <x-textarea wire:model.defer="faq.answer" id="faq.answer" rows="4"></x-textarea>
                    <x-input-error for="faq.answer" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <x-toggle :id="'faq-visible-modal'" :name="'faq-visible-modal'"
                        :value="(bool)($faq['is_visible'] ?? false)" wire:model.defer="faq.is_visible"
                        class="mt-0 p-1.5" />
                    <x-label class="dark:text-gray-300 ml-2 text-sm text-gray-700">{{ t('visible') }}</x-label>
                </div>
            </div>

            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showFaqModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->

    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-faq-modal'" title="{{ t('delete_faq_title') }}"
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