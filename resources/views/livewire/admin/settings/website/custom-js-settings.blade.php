<div >
    <x-slot:title>
        {{ t('custom_js_settings') }}
    </x-slot:title>

    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('website_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-website-settings-navigation />
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-6">
            <form wire:submit.prevent="save" class="space-y-8">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('custom_js') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_the_custom_js_for_your_site') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div>
                            <x-label for="custom_js_header" :value="t('custom_js_header')" class="pb-2" />
                            <x-textarea id="custom_js_header" wire:model.defer="custom_js_header" class="h-20"
                                placeholder="<script>
                                    ...
                                </script>" />
                            <x-input-error for="custom_js_header" />
                        </div>
                        <div class="mt-5">
                            <x-label for="custom_js_footer" :value="t('custom_js_footer')" class="pb-2" />
                            <x-textarea id="custom_js_footer" wire:model.defer="custom_js_footer" class="h-20"
                                placeholder="<script>
                                    ...
                                </script>" />
                            <x-input-error for="custom_js_footer" />
                        </div>
                    </x-slot:content>
                    <!-- Submit Button -->
                    @if(checkPermission('admin.website_settings.edit'))
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
