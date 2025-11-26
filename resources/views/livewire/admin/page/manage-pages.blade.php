<div class="relative">
    <x-slot:title>
        {{ t('pages') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('pages')],
    ]" />

    @if(checkPermission('admin.pages.create'))
    <div class="flex justify-start mb-3 items-center gap-2">
        <x-button.primary wire:click="createPage">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('create_new_page') }}
        </x-button.primary>
    </div>
    @endif

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.page-table />
            </div>
        </x-slot:content>
    </x-card>


    <x-modal.custom-modal :id="'page-modal'" :maxWidth="'3xl'" wire:model="showPageModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('page') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="page.title" :value="t('title')" />
                    </div>
                    <x-input wire:model="page.title" type="text" id="page.title" class="w-full" />
                    <x-input-error for="page.title" class="mt-2" />
                </div>

                <div x-data="{
                        slugify() {
                            const title = $wire.page?.title || '';
                            const slug = this.slugifyValue(title);
                            $wire.set('page.slug', slug);
                        },
                        slugifyValue(text) {
                            return text
                                .toString()
                                .toLowerCase()
                                .trim()
                                .replace(/\s+/g, '-')      // Replace spaces with -
                                .replace(/[^\w\-]+/g, '')  // Remove all non-word chars
                                .replace(/\-\-+/g, '-')    // Replace multiple - with single -
                                .replace(/^-+/, '')        // Trim - from start
                                .replace(/-+$/, '');       // Trim - from end
                        }
                    }">

                    <div class="flex item-centar justify-between gap-1">
                        <div class="flex justify-start items-center gap-1">
                            <span class="text-danger-500">*</span>
                            <x-label for="page.slug" :value="t('slug')" />
                        </div>
                        <button type="button" x-on:click="slugify()"
                            class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                            {{ t('generate_from_title') }}
                        </button>
                    </div>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center mt-1 px-3 rounded-l-md border border-r-0 dark:bg-slate-900 dark:border-slate-700 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            {{ '/' }}
                        </span>
                        <x-input wire:model="page.slug" type="text" id="page.slug" />
                    </div>
                    <x-input-error for="page.slug" class="mt-2" />
                </div>
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <x-label for="page.description" :value="t('description')" />
                    </div>
                    <div wire:ignore>
                        <div x-data x-ref="editor" x-init="const quill = new Quill($refs.editor, { theme: 'snow' });

                        let timeout = null; // Declare timeout for debouncing

                        quill.on('text-change', () => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => {
                                $wire.set('page.description', quill.root.innerHTML);
                            }, 500); // Debounce for 500ms
                        });

                        $watch('$wire.page.description', (value) => {
                            if (quill.root.innerHTML !== value) {
                                quill.root.innerHTML = value || ''; // Update Quill content
                            }
                        });

                        $watch('$wire.showPageModal', (isVisible) => {
                            if (isVisible) {
                                quill.root.innerHTML = $wire.page.description || ''; // Update Quill content
                            }
                        });" class="rounded-b-md">
                            {!! $description !!}
                        </div>
                    </div>
                    <x-input-error for="page.description" class="mt-2" />

                </div>
                <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="w-full" wire:key="parent-dropdown-{{ $this->parentPagesHash }}">
                        <x-label for="page.parent_id" :value="t('parent_page')" />
                        <x-select wire:model="page.parent_id" id="page.parent_id" class="block w-full tom-select mt-1">
                            <option value="">{{ t('none') }}</option>
                            @foreach ($this->parentPages as $parentPage)
                            <option value="{{ $parentPage->id }}">{{ $parentPage->title }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="w-full">
                        <x-label for="page.order" :value="t('order')" />
                        <x-input wire:model="page.order" type="number" id="page.order" class="w-full mt-1" />
                    </div>
                </div>
                <div class="flex items-center space-x-8">
                    <div class="flex items-center">
                        <x-toggle wire:model="page.show_in_menu" :value="(bool)($page['show_in_menu'] ?? false)" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ t('show_in_menu') }}</span>
                    </div>

                    <div class="flex items-center">
                        <x-toggle wire:model="page.status" :value="(bool)($page['status'] ?? false)" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ t('active') }}</span>
                    </div>
                </div>
            </div>
            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showPageModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-faq-modal'" title="{{ t('delete_page_title') }}"
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