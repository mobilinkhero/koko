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
            <x-admin-system-settings-navigation wire:ignore />
        </div>

        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6"
                x-data="{ 'is_enable_landing_page': @entangle('is_enable_landing_page') }">
                <x-card class="rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                    <x-slot:header class="pb-3 border-b border-slate-200 dark:border-slate-700">
                        <x-settings-heading class="text-xl font-semibold text-slate-900 dark:text-white">
                            {{ t('miscellaneous') }}
                        </x-settings-heading>
                        <x-settings-description class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ t('miscellaneous_description') }}
                        </x-settings-description>
                    </x-slot:header>

                    <x-slot:content class="space-y-6 py-6">
                        <!-- First Row - 3 columns -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Tables Pagination Limit Input -->
                            <div
                                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 transition-all duration-150 hover:shadow-sm">
                                <div wire:ignore>
                                    <x-label for="tables_pagination_limit" :value="t('tables_pagination_limit')"
                                        class="block text-sm font-medium text-slate-900 dark:text-white mb-2" />
                                    <x-input type="number" wire:model="tables_pagination_limit"
                                        name="tables_pagination_limit" id="tables_pagination_limit"
                                        placeholder="Enter pagination limit" class="w-full" />
                                    <x-input-error for="tables_pagination_limit" class="mt-2" />
                                </div>
                            </div>

                            <!-- Max Queue Jobs Input -->
                            <div
                                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 transition-all duration-150 hover:shadow-sm">
                                <div>
                                    <x-label for="max_queue_jobs" :value="t('max_queue_jobs')"
                                        class="block text-sm font-medium text-slate-900 dark:text-white mb-2" />
                                    <x-input type="number" wire:model="max_queue_jobs"
                                        name="max_queue_jobs" id="max_queue_jobs"
                                        placeholder="Enter max queue jobs (100-1000)" class="w-full"
                                        min="100" max="1000" />
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {{ t('max_queue_jobs_help') }}
                                    </p>
                                    <x-input-error for="max_queue_jobs" class="mt-2" />
                                </div>
                            </div>

                            <!-- Landing Page Toggle -->
                            <div
                                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 transition-all duration-150 hover:shadow-sm">
                                <div class="flex items-start justify-between h-full">
                                    <div class="flex items-start gap-3 flex-1">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <x-heroicon-o-newspaper class="h-6 w-6 text-primary-500" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3
                                                class="text-sm font-medium text-slate-900 dark:text-white leading-tight">
                                                {{ t('is_enable_landing_page') }}
                                            </h3>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                                {{ t('allow_visitors_to_see_landing_page') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-3">
                                        <x-toggle id="landing_page" name="landing_page" :value="$is_enable_landing_page"
                                            x-on:toggle-changed="is_enable_landing_page = $event.detail.value" />
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
