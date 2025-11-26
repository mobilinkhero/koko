<div >
    <x-slot:title>
        {{ t('website_seo_settings') }}
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
        <div class="flex-1 space-y-5">
            <form wire:submit.prevent="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <!-- SEO Settings -->
                        <x-settings-heading>
                            {{ t('website_seo_and_og') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('update_your_seo_settings') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Author Name -->
                            <div>
                                <x-label for="author_name" :value="t('author_name')" />
                                <x-input id="author_name" type="text" class="mt-1 block w-full"
                                    wire:model="author_name" />
                                <x-input-error for="author_name" class="mt-2" />
                            </div>

                            <!-- Meta Title -->
                            <div>
                                <x-label for="seo_meta_title" :value="t('seo_meta_title')" />
                                <x-input id="seo_meta_title" type="text" class="mt-1 block w-full"
                                    wire:model="seo_meta_title" />
                                <x-input-error for="seo_meta_title" class="mt-2" />
                            </div>

                            <!-- Meta Keywords -->
                            <div>
                                <x-label for="seo_meta_keywords" :value="t('seo_meta_keywords')" />
                                <x-input id="seo_meta_keywords" type="text" class="mt-1 block w-full"
                                    wire:model="seo_meta_keywords" placeholder="keyword1, keyword2, keyword3" />
                                <x-input-error for="seo_meta_keywords" class="mt-2" />
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <x-label for="seo_meta_description" :value="t('seo_meta_description')" />
                                <x-textarea id="seo_meta_description" class="mt-1 block w-full"
                                    wire:model="seo_meta_description" rows="3" />
                                <x-input-error for="seo_meta_description" class="mt-2" />
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- OG Settings -->
                        <x-settings-heading>
                            {{ t('og_settings') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('update_your_open_graph_settings') }}
                        </x-settings-description>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 pt-4">
                            <!-- OG Title -->
                            <div class="sm:col-span-2">
                                <x-label for="og_title" :value="t('og_title')" />
                                <x-input id="og_title" type="text" class="mt-1 block w-full"
                                    wire:model="og_title" />
                                <x-input-error for="og_title" class="mt-2" />
                            </div>

                            <!-- OG Description -->
                            <div class="sm:col-span-2">
                                <x-label for="og_description" :value="t('og_description')" />
                                <x-textarea id="og_description" class="mt-1 block w-full" wire:model="og_description"
                                    rows="3" />
                                <x-input-error for="og_description" class="mt-2" />
                            </div>
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
