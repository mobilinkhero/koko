<div class="mx-auto ">
    <x-slot:title>
        {{ t('notification_sound') }}
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
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('notification_sound') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('customize_notification_sound') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div x-data="{ 'enable_chat_notification_sound': @entangle('enable_chat_notification_sound') }">
                            <x-label for="enable_chat_notification_sound" :value="t('enable_whatsapp_chat_notification_sound')" />
                            <x-toggle id="chat-sound-toggle" name="enable_chat_notification_sound" :value="$enable_chat_notification_sound"
                                wire:model="enable_chat_notification_sound" />
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
