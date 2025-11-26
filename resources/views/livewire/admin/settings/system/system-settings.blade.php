<div>
    <style>
        .iti__country-container {
            display: none
        }
    </style>
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
            <x-admin-system-settings-navigation wire:ignore />
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
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-3 lg:col-span-1">
                                <x-label for="site_name" :value="t('site_name')" />
                                <x-input wire:model.defer="site_name" type="text" id="site_name"
                                    class="mt-1 block w-full" />
                                <x-input-error for="site_name" class="mt-2" />
                            </div>

                            <div class="sm:col-span-3 lg:col-span-2">
                                <x-label for="site_description" :value="t('site_description')" />
                                <x-input wire:model.defer="site_description" id="site_description"
                                    class="mt-1 block w-full" />
                                <x-input-error for="site_description" class="mt-2" />
                            </div>
                        </div>

                        <h3 class="text-lg font-medium text-gray-900 mb-2 mt-4 dark:text-slate-300">
                            {{ t('localization') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-5 z-0">
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

                            <div x-data="countryDropdownLivewire()" x-init="init()" class="relative w-full">
                                <x-label for="default_country_code" :value="t('default_country_code')" />

                                <!-- Selected Button -->
                                <button @click="open = !open" type="button"
                                    class="w-full bg-white border border-gray-300 px-4 py-2 text-left flex items-center justify-between rounded mt-1">
                                    <div class="flex items-center gap-2">
                                        <span :class="'iti__flag iti__' + selected.iso2"></span>

                                    </div>
                                    <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown -->
                                <div x-show="open" @click.outside="open = false"
                                    class="absolute z-10 w-full bg-white border mt-1 rounded shadow max-h-72 overflow-hidden">

                                    <!-- Search -->
                                    <div class="p-2 border-b">
                                        <input x-model="search" type="text" placeholder="Search country or code..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring">
                                    </div>

                                    <!-- Filtered List -->
                                    <div class="max-h-60 overflow-y-auto">
                                        <template x-for="country in filteredCountries" :key="country.iso2">
                                            <div @click="select(country)"
                                                class="cursor-pointer px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                                                <span :class="'iti__flag iti__' + country.iso2"></span>

                                                <span class="ml-auto text-sm text-gray-500 truncate"
                                                    x-text="country.name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error for="default_country_code" class="mt-2" />
                                <input type="hidden" class="hidden all-country-loader" />
                            </div>

                        </div>

                        <h3 class="text-lg font-medium text-gray-900 mb-2 mt-4 dark:text-slate-300">
                            {{ t('company_information') }}
                        </h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <x-label for="company_name" :value="t('company_name')" />
                                <x-input wire:model.defer="company_name" type="text" id="company_name"
                                    class="mt-1 block w-full" />
                                <x-input-error for="company_name" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="company_country_id" :value="t('country')" />
                                <div wire:ignore>
                                    <x-select wire:model.defer="company_country_id" id="country_id"
                                        class="block w-full mt-1 tom-select">
                                        <option value="">{{ t('country_select') }}</option>
                                        @foreach ($this->countries as $country)
                                        <option value="{{ $country['id'] }}" @if ($company_country_id==$country['id'])
                                            selected @endif>
                                            {{ $country['short_name'] }}
                                        </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="company_country_id" class="mt-1" />
                            </div>
                            <div>
                                <x-label for="company_email" :value="t('company_email')" />
                                <x-input wire:model.defer="company_email" type="email" id="company_email"
                                    class="mt-1 block w-full" />
                                <x-input-error for="company_email" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="company_city" :value="t('company_city')" />
                                <x-input wire:model.defer="company_city" type="text" id="company_city"
                                    class="mt-1 block w-full" />
                                <x-input-error for="company_city" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="company_state" :value="t('company_state')" />
                                <x-input wire:model.defer="company_state" type="text" id="company_state"
                                    class="mt-1 block w-full" />
                                <x-input-error for="company_state" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="company_zip_code" :value="t('company_zip_code')" />
                                <x-input wire:model.defer="company_zip_code" type="text" id="company_zip_code"
                                    class="mt-1 block w-full" />
                                <x-input-error for="company_zip_code" class="mt-2" />
                            </div>
                            <div class="sm:col-span-3">
                                <x-label for="company_address" :value="t('company_address')" />
                                <x-textarea wire:model.defer="company_address" rows="3" id="company_address"
                                    class="mt-1 block w-full" />
                                <x-input-error for="company_address" class="mt-2" />
                            </div>
                        </div>
                    </x-slot:content>

                    @if(checkPermission('admin.system_settings.edit'))
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
<script>
    function countryDropdownLivewire() {
    return {
        open: false,
        countries: [],
        selected: @entangle('default_country_code'),
        search: "",
     
        
        init() {
            // Wait for countries to load
            const checkReady = setInterval(() => {
                if (window.AllCountryList && window.AllCountryList.length > 0) {
                    clearInterval(checkReady);
                    this.countries = window.AllCountryList;
                   
                }
            }, 100);

           
        },

       
        get filteredCountries() {
            if (!this.search) return this.countries;
            return this.countries.filter(c =>
                c.name.toLowerCase().includes(this.search.toLowerCase()) ||
                c.dialCode.includes(this.search)
            );
        },

        select(country) {
            this.selected = country;
            this.open = false;
            this.search = "";
            
            console.log("Selected Country:", country.name, "+", country.dialCode, country.iso2);
        }
    };
}
</script>