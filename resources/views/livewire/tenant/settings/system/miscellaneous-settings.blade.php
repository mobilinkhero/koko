<div>
    <x-slot:title>
        {{ t('miscellaneous') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="flex justify-between">
        <div class="pb-6">
            <x-settings-heading>{{ t('miscellaneous') }}</x-settings-heading>
        </div>
    </div>
    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-tenant-system-settings-navigation wire:ignore />
        </div>
        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                    <x-slot:header class="pb-3 border-b border-slate-200 dark:border-slate-700">
                        <x-settings-heading class="text-xl font-semibold text-slate-900 dark:text-white">
                            {{ t('miscellaneous') }}
                        </x-settings-heading>
                        <x-settings-description class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ t('miscellaneous_description') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid grid-cols-1 sm:grid-cols-4 z-0">
                            <div wire:ignore>
                                <x-label for="tables_pagination_limit" :value="t('tables_pagination_limit')" />
                                <x-input type="number" wire:model="tables_pagination_limit"
                                    name="tables_pagination_limit" id="tables_pagination_limit"
                                    placeholder="Enter pagination limit" />
                                <x-input-error for="tables_pagination_limit" class="mt-2" />
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
