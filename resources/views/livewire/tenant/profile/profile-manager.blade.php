<div class="max-w-5xl mx-auto">
    <x-slot:title>
        {{ t('my_profile') }}
    </x-slot:title>
    <x-card x-data="{ activeTab: 'personal', errorMessage: '' }">
        <x-slot:header>
            <!-- Profile Title Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-slate-200 mb-2">{{ t('my_profile') }}</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">
                        {{ t('manage_account_information_settings') }}
                    </p>
                </div>
            </div>
        </x-slot:header>

        <x-slot:content>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-slate-700 ">
                <div class="grid grid-cols-3 gap-0 text-center text-sm font-medium">
                    <button @click="activeTab = 'personal'" :class="{
                        'text-primary-600 border-b-2 border-primary-500 font-semibold': activeTab === 'personal',
                        'text-gray-500 dark:text-slate-300': activeTab !== 'personal'
                    }" class="py-3 transition-all duration-200 ease-in-out">
                        <div class="flex justify-center items-center gap-2">
                            <x-heroicon-o-user
                                :class="{ 'text-primary-600': activeTab === 'personal', 'text-gray-400': activeTab !== 'personal' }"
                                class="h-4 w-4 hidden sm:inline-block" />
                            {{ t('personal_information') }}
                        </div>
                    </button>

                    <button @click="activeTab = 'security'" :class="{
                        'text-primary-600 border-b-2 border-primary-500 font-semibold': activeTab === 'security',
                        'text-gray-500 dark:text-slate-300': activeTab !== 'security'
                    }" class="py-3 transition-all duration-200 ease-in-out">
                        <div class="flex justify-center items-center gap-2">
                            <x-carbon-locked
                                :class="{ 'text-primary-600': activeTab === 'security', 'text-gray-400': activeTab !== 'security' }"
                                class="h-4 w-4 hidden sm:inline-block" />
                            {{ t('security') }}
                        </div>
                    </button>

                    <button @click="activeTab = 'billing'" :class="{
                        'text-primary-600 border-b-2 border-primary-500 font-semibold': activeTab === 'billing',
                        'text-gray-500 dark:text-slate-300': activeTab !== 'billing'
                    }" class="py-3 transition-all duration-200 ease-in-out ">
                        <div class="flex justify-center items-center gap-2">
                            {{ get_base_currency()->symbol . ' ' .t('billing_information') }}
                        </div>
                    </button>
                </div>
            </div>

            <div class="mt-6">
                <!-- Personal Information Tab -->
                <div x-show="activeTab === 'personal'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
                    <form id="personal-form" wire:submit.prevent="updatePersonalInfo" class="space-y-6">
                        <div>
                            <!-- Avatar Section -->
                            <div class="flex flex-col sm:flex-row items-center sm:items-center gap-6">
                                <div x-data="{
                                    photoName: null,
                                    photoPreview: null,
                                    maxSizeMB: 5,
                                    allowedTypes: ['jpeg', 'png'],
                                    errorMessage: '',
                                    validateFile(event) {
                                        this.errorMessage = ''; // Clear previous errors
                                        let file = event.target.files[0];

                                        if (!file) return;

                                        // Check file extension
                                        let fileExtension = file.name.split('.').pop().toLowerCase();
                                        if (!this.allowedTypes.includes(fileExtension)) {
                                            this.errorMessage = `Invalid file type. Allowed: ${this.allowedTypes.join(', ')}`;
                                            event.target.value = ''; // Clear the input
                                            return;
                                        }

                                        // Check file size (5MB = 5 * 1024 * 1024 bytes)
                                        const maxSizeBytes = this.maxSizeMB * 1024 * 1024;
                                        if (file.size > maxSizeBytes) {
                                            this.errorMessage = `File size exceeds ${this.maxSizeMB}MB limit`;
                                            event.target.value = ''; // Clear the input
                                            return;
                                        }

                                        this.photoName = file.name;
                                        const reader = new FileReader();
                                        reader.onload = (e) => this.photoPreview = e.target.result;
                                        reader.readAsDataURL(file);
                                    }
                                }" class="flex flex-col sm:flex-row items-center gap-4">
                                    <!-- Avatar Display -->
                                    <div class="relative group">
                                        <div x-show="!photoPreview" class="relative">
                                            <img src="{{ file_exists(public_path('storage/' . $user->avatar)) && $user->avatar ? asset('storage/' . $user->avatar) : asset('img/user-placeholder.jpg') }}"
                                                alt="{{ $user->firstname }} {{ $user->lastname }}"
                                                class="h-20 w-20 rounded-full object-cover border-4 border-white dark:border-slate-600 shadow-lg glightbox cursor-pointer transition-all duration-200 group-hover:shadow-xl">
                                            <div
                                                class="absolute inset-0 rounded-full bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                                    </path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div x-cloak x-show="photoPreview" class="relative">
                                            <span
                                                class="block h-20 w-20 rounded-full bg-cover bg-center border-4 border-white dark:border-slate-600 shadow-lg"
                                                x-bind:style="'background-image: url(' + photoPreview + ');'"></span>
                                        </div>
                                    </div>

                                    <!-- Avatar Controls -->
                                    <div class="flex flex-col gap-3">
                                        <div>
                                            <x-label class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                                for="avatar" :value="t('profile_image')" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ t('allowed_fromats_5') }}</p>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <input wire:model="avatar_upload" x-ref="photo"
                                                x-on:change="validateFile($event)" type="file" class="hidden"
                                                accept=".jpg,.jpeg,.png" />
                                            <x-button.secondary x-on:click="$refs.photo.click();"
                                                class="inline-flex items-center px-3 py-2 text-sm">
                                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                {{ t('change') }}
                                            </x-button.secondary>
                                            @if (file_exists(public_path('storage/' . $user->avatar)) && $user->avatar)
                                            <x-button.text wire:click="removeProfileImage"
                                                class="inline-flex items-center px-3 py-2 text-sm text-danger-600 hover:text-danger-700">
                                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                {{ t('remove_img') }}
                                            </x-button.text>
                                            @endif
                                        </div>

                                        <!-- Error Message -->
                                        <div x-show="errorMessage" class="text-danger-500 text-sm"
                                            x-text="errorMessage">
                                        </div>
                                        <x-input-error class="text-sm" for="avatar" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group">
                                <x-label for="firstname" class="group-hover:text-primary-600">{{ t('firstname') }}
                                </x-label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <x-input type="text" wire:model="firstname" id="firstname" autocomplete="off" />
                                </div>
                                @error('firstname')
                                <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="group">
                                <x-label for="lastname" class="group-hover:text-primary-600">{{ t('lastname') }}
                                </x-label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <x-input type="text" wire:model="lastname" id="lastname" autocomplete="off" />
                                </div>
                                @error('lastname')
                                <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div class=" ">
                                <x-label for="email" class="group-hover:text-primary-600">{{ t('email') }}</x-label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <x-input type="email" wire:model="email" id="email" autocomplete="off" />
                                </div>
                                @error('email')
                                <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="" wire:ignore>
                                <x-label for="default_language" class="group-hover:text-primary-600"
                                    :value="t('default_language')" />
                                <x-select wire:model.defer="default_language" id="default_language"
                                    class="block w-full mt-1 tom-select">
                                    <option value="">{{ t('select_language') }}</option>
                                    @foreach (getLanguage(null, ['code', 'name']) as $language)
                                    <option value="{{ $language->code }}"> {{ $language->name }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="" x-data="{ errorMessage: '' }">
                                <x-label for="phone" class="group-hover:text-primary-600 ">{{ t('phone') }}</x-label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div wire:ignore x-data="{ phone: @entangle('phone'), }">
                                        <x-input class="phone-input mt-[2px]" x-ref="phone" id="phone"
                                            placeholder="{{ t('enter_phone_number') }}" type="tel"
                                            wire:model.defer="phone" maxlength="18" x-model="phone" wire:model="phone"
                                            autocomplete="off" x-on:change="
                                                                                                        if (phone.length == 18) {
                                                                                                            errorMessage = 'You can only enter up to 18 digits';
                                                                                                            phone = phone.slice(0, 18);
                                                                                                        } else {
                                                                                                            errorMessage = '';
                                                                                                        }
                                                                                                    " />
                                    </div>
                                    <p x-show="errorMessage" class="text-sm text-danger-600 dark:text-danger-400 mt-1"
                                        x-text="errorMessage"></p>
                                    <x-input-error for="phone" class="mt-1" />
                                </div>
                            </div>
                        </div>


                        <div class="">
                            <x-label for="address" class="group-hover:text-primary-600 ">{{ t('address') }}</x-label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <x-textarea wire:model="address" id="address" rows="3" autocomplete="off"></x-textarea>
                            </div>
                            @error('address')
                            <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex justify-end pt-4">
                            <x-button.loading-button type="submit" target="updatePersonalInfo"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePersonalInfo">
                                    {{ t('save_changes') }}
                                </span>
                            </x-button.loading-button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab -->
                <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0" class="space-y-6" x-cloak>
                    <form id="security-form" wire:submit.prevent="updatePassword" class="space-y-6">
                        <div>
                            <div class="space-y-4">
                                <div class="group">
                                    <x-label for="current_password" class="group-hover:text-primary-600">{{
                                        t('current_password') }}
                                    </x-label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <x-input type="password" wire:model="current_password" id="current_password"
                                            autocomplete="off" />
                                    </div>
                                    @error('current_password')
                                    <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="group">
                                    <x-label for="password" class="group-hover:text-primary-600 ">{{ t('new_password')
                                        }}
                                    </x-label>
                                    <div class="mt-1 relative rounded-md shadow-sm">

                                        <x-input type="password" wire:model="password" id="password"
                                            autocomplete="off" />
                                    </div>
                                    @error('password')
                                    <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="group">
                                    <x-label for="password_confirmation" class="group-hover:text-primary-600 ">{{
                                        t('confirm_password') }}</x-label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <x-input type="password" wire:model="password_confirmation"
                                            id="password_confirmation" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <x-button.loading-button type="submit" target="updatePassword" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePassword">
                                    {{ t('update_password') }}
                                </span>
                            </x-button.loading-button>
                        </div>
                    </form>
                </div>

                <!-- Billing Tab -->
                <div x-show="activeTab === 'billing'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
                    <form id="billing-form" wire:submit.prevent="updateBilling" class="space-y-6">
                        <div>
                            <!-- Billing Contact Information -->
                            <div class="mb-8">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-slate-200  flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 mr-2 text-primary-600 dark:text-primary-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ t('billing_contact') }}
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group">
                                        <x-label for="billing_name" class="group-hover:text-primary-600 ">{{
                                            t('billing_name') }}</x-label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <x-input type="text" wire:model="billing_name" id="billing_name"
                                                autocomplete="off" />
                                        </div>
                                        @error('billing_name')
                                        <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="group">
                                        <x-label for="billing_email" class="group-hover:text-primary-600 ">{{
                                            t('billing_email') }}</x-label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <x-input type="email" wire:model="billing_email" id="billing_email"
                                                autocomplete="off" />
                                        </div>
                                        @error('billing_email')
                                        <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="group">
                                        <x-label for="billing_phone" class="group-hover:text-primary-600">{{
                                            t('billing_phone') }}</x-label>
                                        <div wire:ignore x-data="{ phone: @entangle('billing_phone'), }">
                                            <x-input class="phone-input mt-[2px]" x-ref="phone" id="billing_phone"
                                                placeholder="{{ t('enter_phone_number') }}" type="tel"
                                                wire:model.defer="billing_phone" maxlength="18" x-model="phone"
                                                autocomplete="off" wire:model="billing_phone" x-on:change="
                                             if (phone.length == 18) {
                                            errorMessage = 'You can onlyenter up to 18 digits';
                                            phone = phone.slice(0, 18);
                                            } else {
                                        errorMessage = '';
                                    }" />
                                        </div>
                                        @error('billing_phone')
                                        <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    {{ do_action('billing.business_identification_fileds')}}
                                </div>
                                  
                            </div>

                            <!-- Billing Address -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-slate-200 flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ t('biling_address') }}
                                </h3>

                                <div class="space-y-4">
                                    <div class="group">
                                        <x-label for="billing_address" class="group-hover:text-primary-600">{{
                                            t('address') }}</x-label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <x-textarea wire:model="billing_address" id="billing_address" rows="3"
                                                autocomplete="off">
                                            </x-textarea>
                                        </div>
                                        @error('billing_address')
                                        <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="group">
                                            <x-label for="billing_city" class="group-hover:text-primary-600 ">{{
                                                t('city') }}</x-label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <x-input type="text" wire:model="billing_city" id="billing_city"
                                                    autocomplete="off" />
                                            </div>
                                            @error('billing_city')
                                            <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="group">
                                            <x-label for="billing_state" class="group-hover:text-primary-600">{{
                                                t('state') }}</x-label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <x-input type="text" wire:model="billing_state" id="billing_state"
                                                    autocomplete="off" />
                                            </div>
                                            @error('billing_state')
                                            <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="group">
                                            <x-label for="billing_zip_code" class="group-hover:text-primary-600">
                                                {{ t('zip_code') }}</x-label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <x-input type="text" wire:model="billing_zip_code" autocomplete="off"
                                                    id="billing_zip_code" />
                                            </div>
                                            @error('billing_zip_code')
                                            <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="group">
                                            <x-label for="billing_country" class="group-hover:text-primary-600">{{
                                                t('country') }}</x-label>
                                            <div class="mt-1 relative rounded-md shadow-sm" wire:ignore>
                                                <x-select wire:model="billing_country" id="billing_country"
                                                    class="tom-select ">
                                                    <option value="">{{ t('country_select') }}</option>
                                                    @foreach ($this->countries as $country)
                                                    <option value="{{ $country['id'] }}">
                                                        {{ $country['short_name'] }}
                                                    </option>
                                                    @endforeach
                                                </x-select>
                                            </div>
                                            @error('billing_country')
                                            <span class="text-danger-500 text-xs mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end pt-4">
                            <x-button.loading-button type="submit" target="updateBilling" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateBilling">
                                    {{ t('save_changes') }}
                                </span>
                            </x-button.loading-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-slot:content>
    </x-card>
</div>