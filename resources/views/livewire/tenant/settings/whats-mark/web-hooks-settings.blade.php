<div class="mx-auto ">
    <x-slot:title>
        {{ t('web_hooks') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('application_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-tenant-whatsmark-settings-navigation wire:ignore />
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit.prevent="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('web_hooks') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('manage_web_hooks') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data="{ 'enable_webhook_resend': @entangle('enable_webhook_resend') }">
                            <div x-data="{ enable_webhook_resend: @entangle('enable_webhook_resend').defer }">
                                <x-label :value="t('enable_webhooks_resend')" />

                                <!-- Re-usable toggle component -->
                                <x-toggle id="webhook-resend-toggle" name="enable_webhook_resend" :value="$enable_webhook_resend"
                                    wire:model="enable_webhook_resend" />
                            </div>

                            <div class="flex flex-col">
                                <div wire:ignore>
                                    <div class="flex items-center">
                                        <!-- Add x-cloak to hide by default -->
                                        <span x-show="enable_webhook_resend" x-cloak
                                            class="text-danger-500 mr-1">*</span>
                                        <x-label for="webhook_resend_method" :value="t('webhook_resend_method')" />
                                    </div>
                                    <x-select wire:model.defer="webhook_resend_method" id="webhook_resend_method"
                                        class="mt-1 block w-full tom-select">
                                        <option>{{ t('select_resend_method') }}</option>
                                        <option value="GET">{{ t('get') }}</option>
                                        <option value="POST">{{ t('post') }}</option>
                                    </x-select>
                                </div>
                                <x-input-error for="webhook_resend_method" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4 mb-4 sm:mt-2" x-data="{ 'enable_webhook_resend': @entangle('enable_webhook_resend') }">
                            <div class="flex items-center">
                                <span x-show="enable_webhook_resend" x-cloak class="text-danger-500 mr-1">*</span>
                                <x-label for="whatsapp_data_resend_to" :value="t('whatsapp_received_data_resend_to')" />
                            </div>
                            <x-input wire:model.defer="whatsapp_data_resend_to" id="whatsapp_data_resend_to"
                                class="w-full mt-1" placeholder="https://" />
                            <x-input-error for="whatsapp_data_resend_to" class="mt-2" />
                        </div>

                        <!-- Webhook Event Fields - Half Width -->
                        <div class="flex flex-col">
                            <x-label for="webhook_event_fields" :value="t('webhook_event_fields')" />
                            <p class="text-sm text-gray-500 mt-1 mb-2">{{ t('select_events_to_subscribe') }}</p>

                            <div x-data="webhookSelector()" x-init="init()" @click.away="open = false">
                                <!-- Selected Tags Display -->
                                <div x-show="getSelectedCount() > 0" class="mb-3">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="field in getSelectedFields()" :key="field.value">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <span x-text="field.label"></span>
                                                <button type="button" @click="removeField(field.value)"
                                                    class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-600 hover:bg-blue-200 hover:text-blue-800 focus:outline-none">
                                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Dropdown Button -->
                                <div class="relative">
                                    <button type="button" @click="open = !open"
                                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <span class="block truncate" x-text="getButtonText()"></span>
                                        <span
                                            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" :class="{ 'rotate-180': open }"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Dropdown Panel -->
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">

                                        <!-- Search Input -->
                                        <div class="px-3 py-2 border-b border-gray-200">
                                            <input type="text" x-model="search"
                                                placeholder="{{ t('search_events') }}"
                                                class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        </div>

                                        <!-- Quick Actions -->
                                        <div class="px-3 py-2 border-b border-gray-200 bg-gray-50">
                                            <div class="flex justify-between text-xs">
                                                <button type="button" @click="selectAll()"
                                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                                    {{ t('select_all') }}
                                                </button>
                                                <button type="button" @click="clearAll()"
                                                    x-show="getSelectedCount() > 0"
                                                    class="text-gray-600 hover:text-gray-800">
                                                    {{ t('clear_all') }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Options List -->
                                        <template x-for="field in getFilteredFields()" :key="field.value">
                                            <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                                                @click="toggleField(field.value)">
                                                <div class="flex items-center">
                                                    <input type="checkbox" :checked="isSelected(field.value)"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-3">
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-gray-900"
                                                            x-text="field.label"></div>
                                                        <div class="text-xs text-gray-500" x-text="field.value"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- No Results -->
                                        <div x-show="getFilteredFields().length === 0"
                                            class="px-3 py-4 text-center text-sm text-gray-500">
                                            {{ t('no_events_found') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <x-input-error for="webhook_selected_fields" class="mt-2" />
                        </div>
                    </x-slot:content>
                    @if (checkPermission('tenant.whatsmark_settings.edit'))
                        <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
                            <div class="flex justify-end">
                                <x-button.loading-button type="submit" target="save">
                                    {{ t('save_changes') }}
                                </x-button.loading-button>
                            </div>
                        </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>
<script>
    function webhookSelector() {
        return {
            open: false,
            search: '',
            availableFields: @json(collect($this->availableFields)->map(fn($f) => [
                            'value' => $f['value'],
                            'label' => t($f['label']),
                        ])->values()),

            init() {
                // Watch for Livewire changes
                this.$watch('$wire.webhook_selected_fields', () => {
                    // React to external changes if needed
                });
            },

            getSelectedFields() {
                const selected = this.$wire.webhook_selected_fields || [];
                return this.availableFields.filter(field => selected.includes(field.value));
            },

            getSelectedCount() {
                return (this.$wire.webhook_selected_fields || []).length;
            },

            getButtonText() {
                const count = this.getSelectedCount();
                if (count === 0) {
                    return '{{ t('select_events_to_subscribe') }}';
                }
                return count + ' events selected';
            },

            getFilteredFields() {
                if (!this.search.trim()) {
                    return this.availableFields;
                }
                const searchLower = this.search.toLowerCase();
                return this.availableFields.filter(field =>
                    field.label.toLowerCase().includes(searchLower) ||
                    field.value.toLowerCase().includes(searchLower)
                );
            },

            isSelected(fieldValue) {
                return (this.$wire.webhook_selected_fields || []).includes(fieldValue);
            },

            toggleField(fieldValue) {
                const currentFields = [...(this.$wire.webhook_selected_fields || [])];
                const index = currentFields.indexOf(fieldValue);

                if (index > -1) {
                    currentFields.splice(index, 1);
                } else {
                    currentFields.push(fieldValue);
                }

                this.$wire.set('webhook_selected_fields', currentFields);
            },

            removeField(fieldValue) {
                const currentFields = (this.$wire.webhook_selected_fields || []).filter(value => value !== fieldValue);
                this.$wire.set('webhook_selected_fields', currentFields);
            },

            selectAll() {
                this.$wire.set('webhook_selected_fields', this.availableFields.map(f => f.value));
            },

            clearAll() {
                this.$wire.set('webhook_selected_fields', []);
                this.search = '';
            }
        }
    }
</script>
