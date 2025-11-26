<div class="mx-auto">
    <x-slot:title>
        {{ t('theme_management') }}
    </x-slot:title>
    <div class="pb-6">
        <x-settings-heading>{{ t('theme_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-system-settings-navigation wire:ignore />
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
        </div>
    </div>
</div>