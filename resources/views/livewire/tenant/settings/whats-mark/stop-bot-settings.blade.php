<div class="mx-auto ">
    <x-slot:title>
        {{ t('stop_bot') }}
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
                            {{ t('stop_bot') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_stop_bot') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid md:grid-cols-2 gap-3">
                            <!-- Stop Bots Keyword -->
                            <div class="col-span-3">
                                <div class="flex items-center justify-start gap-1">
                                    <span class="text-danger-500">*</span>
                                    <x-label class="mt-[2px]" for="stop_bots_keyword" :value="t('stop_bots_keyword')" />
                                </div>
                                <div x-data="{ tags: @entangle('stop_bots_keyword'), newTag: '' }">
                                    <x-input type="text" x-model="newTag"
                                        x-on:keydown.enter.prevent="if(newTag) { tags.push(newTag); newTag = ''; }"
                                        x-on:keydown.space.prevent="if(newTag) { tags.push(newTag); newTag = ''; }"
                                        x-on:blur="if(newTag) { tags.push(newTag); newTag = ''; }"
                                        placeholder="{{ t('type_and_press_enter') }}"
                                        class="block w-full mt-1 border p-2" />

                                    <div class="mt-2">
                                        <template x-for="(tag, index) in tags" :key="index">
                                            <span
                                                class="bg-primary-500 dark:bg-gray-700 text-white mb-2 dark:text-gray-100 rounded-xl px-2 py-1 text-sm mr-2 inline-flex items-center">
                                                <span x-text="tag"></span>
                                                <button x-on:click="tags.splice(index, 1)"
                                                    class="ml-2 text-white dark:text-gray-100">&times;</button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error for="stop_bots_keyword" class="mt-2" />
                            </div>

                            <!-- Restart Bots After -->
                            <div class="col-span-3 md:col-span-1">
                                <div class="flex items-center justify-start gap-1">
                                    <label for="restart_bots_after"
                                        class="block font-medium text-sm text-slate-700 dark:text-slate-200">
                                        {{ t('restart_bots_after') }}
                                    </label>
                                </div>
                                <div
                                    class="flex mt-1 items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-800">
                                    <input @keydown.enter.prevent type="number" wire:model.defer="restart_bots_after"
                                        id="restart_bots_after"
                                        class="block w-full border-0 text-slate-900 sm:text-sm disabled:opacity-50 dark:bg-slate-800
                                      dark:placeholder-slate-500 dark:text-slate-200 dark:focus:placeholder-slate-600 px-3 py-2
                                      border-r border-gray-300 focus:outline-none focus:ring-0 focus:border-transparent">

                                    <span class="px-3 border-gray-300 text-gray-600 dark:text-gray-400">
                                        {{ t('hours') }}
                                    </span>
                                </div>
                                <x-input-error for="restart_bots_after" class="mt-2" />
                            </div>
                        </div>
                    </x-slot:content>
                    @if(checkPermission('tenant.whatsmark_settings.edit'))
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
