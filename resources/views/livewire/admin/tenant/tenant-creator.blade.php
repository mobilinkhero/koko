<div x-data="{
    currentTab: 'personal',
    loading: false,
    companyName: '',
    tenantName: '',

    manuallyEdited: false,

    slugify(str) {
        if (!str) return '';
        return str
            .toLowerCase()
            .replace(/\s+/g, '') // Remove all spaces (no dash)
            .replace(/[^a-z0-9]/g, '') // Remove all non-alphanumeric characters
    },

    sanitizeTenantName() {
        this.tenantName = this.tenantName
            .toLowerCase()
            .replace(/[^a-z0-9]/g, ''); // Only allow a-z, 0-9
    },
}" x-init="$watch('companyName', value => {
    if (!manuallyEdited) {
        tenantName = slugify(value);
        $wire.set('tenant.subdomain', tenantName);
    }
})" class="md:px-0">

    
    <x-slot:title>
        {{ $tenant->exists ? t('edit_tenant_title') : t('add_tenant_title') }}
    </x-slot:title>

    <div class="relative rounded-lg lg:w-3/4">
    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('tenants'), 'route' => route('admin.tenants.list')],
        ['label' => $tenant->exists ? t('edit_tenant_title') : t('add_tenant_title')]
]" />
</div>
    <div>
        <form wire:submit.prevent="save">
           

            <x-card class="relative rounded-lg lg:w-3/4">
                  <x-slot:header>
                    <h2
                        class="text-md font-medium text-slate-700 dark:text-slate-300  border-slate-200 dark:border-slate-700">
                        {{ t('personal_information') }}
                    </h2>
                </x-slot:header>
                <x-slot:content>
                    <!-- Form Content -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information Section -->
                        <div class="space-y-6 md:col-span-2">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.firstname" :value="t('firstname')" />
                                    </div>
                                    <x-input type="text" id="user.firstname" wire:model.defer="user.firstname" />
                                    <x-input-error for="user.firstname" class="mt-1" />
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.lastname" :value="t('lastname')" />
                                    </div>
                                    <x-input type="text" id="user.lastname" wire:model.defer="user.lastname" />
                                    <x-input-error for="user.lastname" class="mt-1" />
                                </div>

                                <!-- Email -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.email" :value="t('email')" />
                                    </div>
                                    <x-input id="user.email" wire:model.defer="user.email" />
                                    <x-input-error for="user.email" class="mt-1" />
                                </div>

                                <!-- Phone -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.phone" class="font-medium">
                                            {{ t('phone') }}
                                        </x-label>
                                    </div>
                                    <div wire:ignore x-data="{ phone: @entangle('user.phone'), errorMessage: '' }">
                                        <x-input class="phone-input mt-[2px]" x-ref="phone" id="phone" type="tel"
                                            wire:model.defer="user.phone" maxlength="18" x-model="phone" x-on:change="
                                                    if (phone.length == 18) {
                                                        errorMessage = 'You can only enter up to 18 digits';
                                                        phone = phone.slice(0, 18);
                                                    } else {
                                                        errorMessage = '';
                                                    }
                                                " />
                                        <p x-show="errorMessage" class="text-sm text-danger-600 dark:text-danger-400 mt-1"
                                            x-text="errorMessage"></p>
                                    </div>
                                    <x-input-error for="user.phone" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <!-- Company Information Section -->
                        <div class="space-y-6 md:col-span-2">
                            <h3
                                class="text-md font-medium text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 pb-2">
                                {{ t('company_information') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @if (! $tenant->exists)
                                    <!-- Company Name -->
                                    <div>
                                        <div class="flex items-center mb-1">
                                            <span class="text-danger-500 text-sm mr-1">*</span>
                                            <x-label for="tenant.company_name" :value="t('company_name')"
                                                class="text-gray-700 dark:text-gray-300 font-medium" />
                                        </div>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <x-heroicon-o-building-office class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <x-input id="company_name"
                                                class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                name="tenant.company_name" x-model="companyName" wire:model="tenant.company_name" type="text" :value="old('tenant.company_name')"
                                                placeholder="{{ t('your_company_name') }}" />
                                        </div>
                                        <x-input-error class="mt-2" for="tenant.company_name" />
                                    </div>

                                    <!-- Tenant Name (subdomain) -->
                                    <div>
                                        <div class="flex items-center mb-1">
                                            <span class="text-danger-500 text-sm mr-1">*</span>
                                            <x-label for="tenant.subdomain" :value="t('tenant_name')"
                                                class="text-gray-700 dark:text-gray-300 font-medium" />
                                        </div>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <x-heroicon-s-globe-alt class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <x-input id="tenant.subdomain"
                                                class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                name="tenant.subdomain" wire:model="tenant.subdomain" x-model="tenantName" x-on:input="
                                                                                        sanitizeTenantName();
                                                                                        if (!manuallyEdited) manuallyEdited = true;" x-on:blur="
                                                                                        if (window.existingSubdomains.includes(tenantName.toLowerCase())) {
                                                                                            window.dispatchEvent(new CustomEvent('notify', {
                                                                                                detail: {
                                                                                                type: 'danger',
                                                                                                message: 'Tenant already exists. Please choose another.'
                                                                                                }
                                                                                            }));
                                                                                        }" type="text" :value="old('tenant.subdomain')"
                                                placeholder="{{ t('tenant_name') }}" />

                                        </div>
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Your URL: <span class="font-medium text-primary-600 dark:text-primary-400"
                                                x-text="tenantName ? `{{ config('app.url') }}/${tenantName}` : '{{ config('app.url') }}/___'"></span>
                                        </div>
                                        <x-input-error class="mt-2" for="tenant.subdomain" />
                                    </div>
                                @endif
                                <!-- Country -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <x-label for="tenant.country_id" :value="t('country')" />
                                    </div>
                                    <div wire:ignore>
                                        <x-select id="tenant.country_id" wire:model.defer="tenant.country_id"
                                            class="block w-full mt-2 tom-select">
                                            <option value="">{{ t('country_select') }}</option>
                                            @foreach ($this->countries as $country)
                                            <option value="{{ $country['id'] }}">
                                                {{ $country['short_name'] }}
                                            </option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                    <x-input-error for="tenant.country_id" class="mt-1" />
                                </div>
                                <!-- Timezone -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <x-label for="tenant.timezone" :value="t('timezone')" />
                                    </div>
                                    <div wire:ignore>
                                        <x-select id="tenant.timezone" wire:model.defer="tenant.timezone"
                                            class="block w-full mt-2 tom-select">
                                            <option value="">{{ t('select_timezone') }}</option>
                                            @foreach (timezone_identifiers_list() as $tz)
                                            <option value="{{ $tz }}">{{ $tz }}</option>
                                            @endforeach
                                        </x-select>
                                        <x-input-error for="tenant.timezone" class="mt-1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <x-label for="tenant.address" :value="t('address')" />
                            <x-textarea id="tenant.address" wire:model.defer="tenant.address" rows="3" />
                            <x-input-error for="tenant.address" class="mt-1" />
                        </div>
                        <!-- Account Security Section -->
                        <div class="space-y-6 md:col-span-2">
                            <h3
                                class="text-md font-medium text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 pb-2">
                                {{ t('account_security') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Password -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="password" :value="t('password')" />
                                    </div>
                                    <x-input type="password" id="password" wire:model.defer="password" />
                                    <x-input-error for="password" class="mt-1" />
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="password_confirmation" :value="t('confirm_password')" />
                                    </div>
                                    <x-input type="password" id="password_confirmation"
                                        wire:model.defer="password_confirmation" />
                                    <x-input-error for="password_confirmation" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>

                </x-slot:content>

                <!-- Submit Button -->
                <x-slot:footer class="rounded-b-lg">
                    <div class="flex justify-end space-x-3">
                        <x-button.secondary class="mx-2" onclick="window.history.back()">
                            {{ t('cancel') }}
                        </x-button.secondary>
                        <x-button.loading-button type="submit" target="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                {{ $tenant->exists ? t('update_button') : t('add_button') }}
                            </span>
                        </x-button.loading-button>
                    </div>
                </x-slot:footer>
            </x-card>
        </form>
    </div>
    <script>
        window.existingSubdomains = @json($subdomains);
    </script>
</div>
