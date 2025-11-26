<div >
    <x-slot:title>
        {{ t('custom_css_settings') }}
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
                            {{ t('custom_css') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_the_custom_css_for_your_site') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div>
                            <x-label for="customCss" :value="t('custom_css')" class="pb-2" />
                            <x-textarea id="customCss" wire:model.defer="customCss" class="h-20"
                                placeholder="
                                body{ background-color: red !important; }" />
                            <x-input-error for="customCss" />
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
