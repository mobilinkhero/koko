<div class="relative">
    <x-slot:title>
        {{ t('department') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('department')],
    ]" />

    <!-- Alpine JS component for multiselect -->
    <script>
        function assigneeMultiselect({
            selected = [],
            options = []
        }) {
            return {
                open: false,
                selected,
                options,

                get selectedOptions() {
                    return this.options.filter(opt => this.selected.includes(opt.id));
                },

                get availableOptions() {
                    return this.options.filter(opt => !this.selected.includes(opt.id));
                },

                toggle(id) {
                    if (this.selected.includes(id)) {
                        this.selected = this.selected.filter(i => i !== id);
                    } else {
                        this.selected.push(id);
                    }
                },

                remove(id) {
                    this.selected = this.selected.filter(i => i !== id);
                },
            }
        }
    </script>

    <!-- Department Statistics Dashboard -->
    <div class="mb-6">
        <livewire:admin.department.department-stats />
    </div>

    @if (checkPermission('admin.department.create'))
    <div class="flex justify-start mb-3  items-center gap-2">
        <x-button.primary wire:click="createDepartment" wire:loading.attr="disabled">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_department') }}
        </x-button.primary>
    </div>
    @endif

    <x-card class="rounded-lg">
        <x-slot:content>

            <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
                <livewire:admin.tables.department-table />
            </div>
        </x-slot:content>
    </x-card>

    <x-modal.custom-modal :id="'department-modal'" :maxWidth="'2xl'" wire:model.defer="showDepartmentModal">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 ">
            <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                {{ t('department') }}
            </h1>
        </div>

        <form wire:submit.prevent="save" class="mt-4">
            <div class="px-6 space-y-3">
                <div>
                    <div class="flex item-centar justify-start gap-1">
                        <span class="text-danger-500">*</span>
                        <x-label for="name" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                            {{ t('name') }}
                        </x-label>
                    </div>
                    <x-input wire:model.defer="name" type="text" id="name" class="w-full" autocomplete="off" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <x-label for="description" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                        {{ t('description') }}
                    </x-label>
                    <x-textarea wire:model.defer="description" id="description" rows="3"
                        placeholder="Enter department description (optional)" />
                    <x-input-error for="description" class="mt-2" />
                </div>


                <div>
                    <x-label for="assignee_ids" class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                        {{ t('assign_to_users') }}
                    </x-label>

                    <div x-data="assigneeMultiselect({
                        selected: @entangle('assignee_ids'),
                        options: {{ json_encode(collect($users)->map(function($u) {
                            return [
                                'id' => $u['id'],
                                'label' => $u['name']
                            ];
                        })->values()) }},
                    })">
                        <!-- Selected users -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="(item, index) in selectedOptions" :key="item.id">
                                <span
                                    class="flex items-center bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-primary-900 dark:text-primary-300">
                                    <span x-text="item.label"></span>
                                    <button @click="remove(item.id)"
                                        class="ml-1 text-primary-500 hover:text-danger-500 focus:outline-none text-xs">×</button>
                                </span>
                            </template>
                        </div>

                        <!-- Trigger -->
                        <div class="relative mt-2">
                            <button type="button" @click="open = !open"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                {{ t('select_assignees') }}
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <template x-for="option in availableOptions" :key="option.id">
                                    <div class="cursor-pointer select-none relative py-2 pl-10 pr-4 hover:bg-primary-100 dark:hover:bg-primary-600 text-gray-900 dark:text-white"
                                        @click="toggle(option.id)">
                                        <span class="block truncate" x-text="option.label"></span>
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <input type="checkbox"
                                                class="form-checkbox text-primary-600 border-gray-300 rounded"
                                                :checked="selected.includes(option.id)" readonly>
                                        </span>
                                    </div>
                                </template>
                                <template x-if="availableOptions.length === 0">
                                    <div class="py-2 px-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('no_more_assignees_to_select') }}
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <x-input-error for="assignee_ids" class="mt-2" />
                </div>
            </div>

            <div
                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                <x-button.secondary wire:click="$set('showDepartmentModal', false)">
                    {{ t('cancel') }}
                </x-button.secondary>
                <x-button.loading-button type="submit" target="save">
                    {{ t('submit') }}
                </x-button.loading-button>
            </div>
        </form>
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-department-modal'" title="{{ t('delete_department_title') }}"
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