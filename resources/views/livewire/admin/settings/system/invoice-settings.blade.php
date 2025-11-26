<div>
    <x-slot:title>
        {{ t('invoice_settings') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="flex justify-between">
        <div class="pb-6">
            <x-settings-heading>{{ t('system_settings') }}</x-settings-heading>
        </div>
    </div>
    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-system-settings-navigation wire:ignore />
        </div>

        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                    <x-slot:header class="pb-3 border-b border-slate-200 dark:border-slate-700">
                        <x-settings-heading class="text-xl font-semibold text-slate-900 dark:text-white">
                            {{ t('invoice_settings') }}
                        </x-settings-heading>
                        <x-settings-description class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ t('set_invoice_and_payment_details') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content class="space-y-4 py-5">
                        <!-- Invoice Numbering -->
                        <div>
                            <h4 class="text-base font-medium text-gray-900 dark:text-white">
                                {{ t('invoice_details') }}
                            </h4>
                            <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="prefix"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('prefix') }}</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model="prefix" name="prefix" id="prefix"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors duration-150">
                                        <x-input-error for="prefix" class="mt-2" />
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('text_to_add_before_invoice_number') }}</p>
                                </div>
                            </div>
                            <!-- Invoice Footer Text -->
                            <div class="pt-6">
                                <div>
                                    <label for="prefix"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('invoice_footer_text') }}</label>
                                    <x-textarea wire:model="footer_text" id="footer_text" name="footer_text" rows="3"
                                        placeholder="{{ t('add_custom_footer_text_for_all_invoices') }}" />
                                    <x-input-error for="footer_text" class="mt-2" />
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('this_text_appears_at_the_bottom_of_all_invoices') }}</p>
                            </div>

                            <div class="pt-6" x-data="taxMultiselect({
                                selected: @entangle('default_taxes'),
                                options: {{ $available_taxes->map(fn($t) => ['id' => $t->id, 'label' => $t->name . ' (' . number_format($t->rate, 2) . '%)'])->values() }},
                            })">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('default_taxes') }}
                                </label>

                                <!-- Selected tags -->
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
                                        {{ t('select_taxes') }}
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
                                                {{ t('no_more_taxes_to_select') }}
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <x-input-error for="default_taxes" class="mt-2" />

                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('select_default_taxes_applied_to_invoices') }}
                                </p>
                            </div>
                        </div>

                        <!-- Bank Details Section -->
                        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <h4 class="text-base font-medium text-gray-900 dark:text-white">
                                    {{ t('bank_details') }}
                                </h4>
                                <div
                                    class="ml-2 bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                    {{ t('for_offline_payments') }}
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ t('offline_payment_display_details') }}
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="bank_name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('bank_name') }}
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" wire:model="bank_name" id="bank_name" name="bank_name"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors duration-150">
                                        <x-input-error for="bank_name" class="mt-2" />
                                    </div>
                                </div>



                                <div class="sm:col-span-3">
                                    <label for="account_name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('account_name') }}
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" wire:model="account_name" id="account_name"
                                            name="account_name"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors duration-150">
                                        <x-input-error for="account_name" class="mt-2" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="account_number"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('account_number') }}
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" wire:model="account_number" id="account_number"
                                            name="account_number"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors duration-150">
                                        <x-input-error for="account_number" class="mt-2" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="ifsc_code"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('ifsc_code') }}
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" wire:model="ifsc_code" id="ifsc_code" name="ifsc_code"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors duration-150">
                                        <x-input-error for="ifsc_code" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot:content>
                    @if (checkPermission('admin.system_settings.edit'))
                        <x-slot:footer
                            class="bg-slate-50 dark:bg-slate-800/50 px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                            <x-button.loading-button type="submit" target="save">
                                {{ t('save_changes') }}
                            </x-button.loading-button>
                        </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>
<!-- Alpine Component -->
<script>
    function taxMultiselect({
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
