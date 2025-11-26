<div class="mx-auto ">
    <x-slot:title>
        {{ t('support_agent') }}
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
            <form wire:submit="save" x-data="{ 'only_agents_can_chat': @entangle('only_agents_can_chat') }" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('support_agent') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_support_agent') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <x-label for="message" :value="t('restrict_chat_access')" />

                        <div class="flex justify-start items-center mt-2">
                            <x-toggle id="agents-chat-toggle" name="only_agents_can_chat" :value="$only_agents_can_chat"
                                wire:model="only_agents_can_chat" />
                        </div>

                        <div class="mt-4">
                            <x-dynamic-alert type="warning">
                                <b>{{ t('note') }}</b>
                                {{ t('support_agent_feature_info') }}
                            </x-dynamic-alert>
                        </div>
                    </x-slot:content>

                    <!-- Submit Button -->
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
