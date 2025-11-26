<div>
    <x-slot:title>
        {{ t('system_core_settings') }}
    </x-slot:title>

    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('system_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-tenant-system-settings-navigation wire:ignore />
        </div>

        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6" x-data x-init="window.initTomSelect('.tom-select')">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>{{ t('system_core_settings') }}</x-settings-heading>
                        <x-settings-description>
                            {{ t('system_core_settings_description') }}
                        </x-settings-description>
                    </x-slot:header>

                    <x-slot:content>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-slate-300">
                            {{ t('localization') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 z-0">
                            <div wire:ignore>
                                <x-label for="timezone" :value="t('timezone')" />
                                <x-select id="timezone" class="mt-1 block w-full tom-select" wire:model.defer="timezone"
                                    wire:change="$set('timezone', $event.target.value)">
                                    <option>{{ t('select_timezone') }}</option>
                                    @foreach ($timezone_list as $tz)
                                    <option value="{{ $tz }}" {{ $tz==$timezone ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                    @endforeach
                                </x-select>
                                <x-input-error for="timezone" class="mt-2" />
                            </div>

                            <div wire:ignore>
                                <x-label for="date_format" :value="t('date_format')" />
                                <x-select id="date_format" class="mt-1 block w-full tom-select"
                                    wire:model.defer="date_format"
                                    wire:change="$set('date_format', $event.target.value)">
                                    <option>{{ t('select_date_format') }}</option>
                                    @foreach ($date_formats as $format => $example)
                                    <option value="{{ $format }}" {{ $format==$date_format ? 'selected' : '' }}>
                                        {{ $example }}
                                    </option>
                                    @endforeach
                                </x-select>
                                <x-input-error for="date_format" class="mt-2" />
                            </div>

                            <div wire:ignore>
                                <x-label for="time_format" :value="t('time_format')" />
                                <x-select id="time_format" class="mt-1 block w-full tom-select"
                                    wire:model.defer="time_format"
                                    wire:change="$set('time_format', $event.target.value)">
                                    <option>{{ t('select_time_format') }}</option>
                                    <option value="24" {{ $time_format=='24' ? 'selected' : '' }}>
                                        {{ t('24_hours') }}
                                    </option>
                                    <option value="12" {{ $time_format=='12' ? 'selected' : '' }}>
                                        {{ t('12_hours') }}
                                    </option>
                                </x-select>
                                <x-input-error for="time_format" class="mt-2" />
                            </div>

                            <div wire:ignore>
                                <x-label for="active_language" :value="t('default_language')" />
                                <x-select id="active_language" class="mt-1 block w-full tom-select"
                                    wire:model.defer="active_language"
                                    wire:change="$set('active_language', $event.target.value)">
                                    <option>{{ t('select_language') }}</option>
                                    @foreach (getLanguage(null, ['code', 'name']) as $language)
                                    <option value="{{ $language->code }}" {{ $language->code == $active_language ?
                                        'selected' : '' }}>
                                        {{ $language->name }}
                                    </option>
                                    @endforeach
                                </x-select>
                                <x-input-error for="active_language" class="mt-2" />
                            </div>
                        </div>
                    </x-slot:content>
                    @if (checkPermission('tenant.system_settings.edit'))
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