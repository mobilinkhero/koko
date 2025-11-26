<div >
    <x-slot:title>
        {{ t('section_title_subtitle_settings') }}
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
                        <x-settings-heading>
                            {{ t('section_title_subtitle_settings') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_the_section_title_subtitle_for_your_site') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pricing Section Title -->
                            <div>
                                <x-label for="pricing_section_title" value="{{ t('pricing_section_title') }}" />
                                <x-input id="pricing_section_title" type="text" wire:model="pricing_section_title"
                                    class="mt-1 block w-full" autocomplete="off" />
                                <x-input-error for="pricing_section_title" class="mt-2" />
                            </div>

                            <!-- Pricing Section Subtitle -->
                            <div>
                                <x-label for="pricing_section_subtitle" value="{{ t('pricing_section_subtitle') }}" />
                                <x-input id="pricing_section_subtitle" type="text"
                                    wire:model="pricing_section_subtitle" class="mt-1 block w-full"
                                    autocomplete="off" />
                                <x-input-error for="pricing_section_subtitle" class="mt-2" />
                            </div>

                            <!-- FAQ Section Title -->
                            <div>
                                <x-label for="faq_section_title" value="{{ t('faq_section_title') }}" />
                                <x-input id="faq_section_title" type="text" wire:model="faq_section_title"
                                    class="mt-1 block w-full" autocomplete="off" />
                                <x-input-error for="faq_section_title" class="mt-2" />
                            </div>

                            <!-- FAQ Section Subtitle -->
                            <div>
                                <x-label for="faq_section_subtitle" value="{{ t('faq_section_subtitle') }}" />
                                <x-input id="faq_section_subtitle" type="text" wire:model="faq_section_subtitle"
                                    class="mt-1 block w-full" autocomplete="off" />
                                <x-input-error for="faq_section_subtitle" class="mt-2" />
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
