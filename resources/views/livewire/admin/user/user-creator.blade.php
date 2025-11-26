<div x-data="{
    fileTypes: {{ json_encode(get_whatsmark_allowed_extension()) }},
    imageExtensions: '',
    file: null,
    preview: null,
    errorMessage: '',
    isAdmin: @entangle('is_admin'),

    init() {
        if (this.fileTypes?.file_types?.extension) {
            let allExtensions = this.fileTypes.file_types.extension.split(',').map(ext => ext.trim());
            this.imageExtensions = allExtensions.slice(0, 3).join(', '); // First 3 extensions
        }
    },

    handleFileChange(event) {
        this.errorMessage = ''; // Clear previous errors
        this.file = event.target.files[0];

        if (!this.file) return;

        let fileExt = '.' + this.file.name.split('.').pop().toLowerCase();
        let allowedExtensions = this.imageExtensions.split(', ');

        if (!allowedExtensions.includes(fileExt)) {
            this.errorMessage = `Invalid file type. Allowed: ${this.imageExtensions}`;
            this.resetFile(event);
            return;
        }

        if (this.file.size > 2 * 1024 * 1024) { // 2MB limit
            this.errorMessage = `The selected file is too large. Maximum allowed size is 2MB.`;
            this.resetFile(event);
            return;
        }

        let reader = new FileReader();
        reader.onload = (e) => {
            this.preview = e.target.result;
        };
        reader.readAsDataURL(this.file);
    },

    resetFile(event) {
        this.file = null;
        this.preview = null;
        event.target.value = ''; // Reset input
    }


}">
    <x-slot:title>
        {{ t('create_user') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('users'), 'route' => route('admin.users.list')],
        ['label' => t('create_user')]
]" />

    <form wire:submit.prevent="save">
        <div class="flex flex-col lg:flex-row gap-6 items-start mb-20">
            <!-- Left Column (Personal Information) -->
            <div class="w-full lg:w-2/5">
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:header>
                        <div class="flex items-center">
                            <x-heroicon-o-user-circle class="w-8 h-8 mr-2 text-primary-600" />
                            <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('personal_information') }}
                            </h1>
                        </div>
                    </x-slot:header>

                    <x-slot:content>
                        <!-- Profile Image -->
                        <div class="mb-6">
                            <x-label for="user.avatar" class="font-medium" :value="t('profile_image')" />

                            <div class="mt-2 flex flex-col sm:flex-row items-center gap-4">
                                <!-- Image Container -->
                                <div class="flex justify-center">
                                    <!-- Existing Profile Image -->
                                    <div x-show="!preview"
                                        class="h-16 w-16 sm:h-16 sm:w-16 rounded-full overflow-hidden border-2 border-primary-100 dark:border-slate-600 flex-shrink-0">
                                        <img src="{{ $this->user->avatar && Storage::disk('public')->exists($this->user->avatar) ? asset('storage/' . $this->user->avatar) : asset('img/user-placeholder.jpg') }}"
                                            alt="{{ $this->user->firstname }}" class="h-full w-full object-cover">
                                    </div>

                                    <!-- Image Preview -->
                                    <div x-show="preview" x-cloak
                                        class="h-16 w-16 sm:h-16 sm:w-16 rounded-full overflow-hidden border-2 border-primary-100 dark:border-slate-600 flex-shrink-0">
                                        <span class="block h-full w-full bg-cover bg-center bg-no-repeat"
                                            x-bind:style="'background-image: url(\'' + preview + '\');'">
                                        </span>
                                    </div>
                                </div>

                                <!-- Buttons Container -->
                                <div class="flex flex-wrap justify-center sm:justify-start gap-3 mt-4 sm:mt-0">
                                    <div>
                                        <input x-ref="photo" type="file" class="hidden" :accept="imageExtensions"
                                            x-on:change="handleFileChange" wire:model="user.avatar" wire:ignore>

                                        <x-button.secondary class="text-sm flex items-center whitespace-nowrap"
                                            x-on:click="$refs.photo.click();">
                                            <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1" />
                                            {{ t('change') }}
                                        </x-button.secondary>
                                    </div>

                                    @if ($this->user->avatar && Storage::disk('public')->exists($this->user->avatar))
                                        <div>
                                            <x-button.secondary class="text-sm flex items-center whitespace-nowrap"
                                                wire:click="removeProfileImage">
                                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                                {{ t('remove') }}
                                            </x-button.secondary>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!-- Error message -->
                            <p x-show="errorMessage" class="text-danger-500 text-sm mt-2 text-center sm:text-left"
                                x-text="errorMessage"></p>
                        </div>

                        <div class="space-y-3">

                            <div class="gap-3 grid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <!-- First Name -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.firstname" :value="t('firstname')" />
                                    </div>
                                    <x-input wire:model.defer="user.firstname" id="user.firstname"
                                        placeholder="{{ t('enter_first_name') }}" class="w-full mt-1" autocomplete="off"/>
                                    <x-input-error for="user.firstname" class="mt-1" />
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.lastname" :value="t('lastname')" />
                                    </div>
                                    <x-input wire:model.defer="user.lastname" id="user.lastname"
                                        placeholder="{{ t('enter_last_name') }}" class="w-full mt-1" autocomplete="off"/>
                                    <x-input-error for="user.lastname" class="mt-1" />
                                </div>

                            </div>

                            <!-- Email -->
                            <div class="gap-3 grid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.email" :value="t('email')" />
                                    </div>
                                    <div class="relative mt-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <x-heroicon-o-envelope class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <x-input wire:model.defer="user.email" id="user.email"
                                            placeholder="{{ t('enter_email_address') }}" class="w-full pl-10" autocomplete="off" />
                                    </div>
                                    <x-input-error for="user.email" class="mt-1" />
                                </div>

                                <!-- Phone -->
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="user.phone" :value="t('phone')" />
                                    </div>
                                    <div wire:ignore x-data="{ phone: @entangle('user.phone'), errorMessage: '' }">
                                        <x-input class="phone-input mt-[2px]" x-ref="phone" id="phone"
                                            placeholder="{{ t('enter_phone_number') }}" type="tel"
                                            wire:model.defer="user.phone" maxlength="18" x-model="phone" autocomplete="off"
                                            x-on:change="
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

                            <!-- Default Language -->
                            <div class="sm:col-span-2">
                                <x-label for="user.default_language" :value="t('default_language')" />
                                <div wire:ignore>
                                    <x-select wire:model.defer="user.default_language" id="user.default_language"
                                        class="block w-full mt-1 tom-select">
                                        <option value="">{{ t('select_default_language') }}</option>
                                        @foreach (getLanguage(null, ['code', 'name']) as $language)
                                            <option value="{{ $language->code }}"
                                                {{ $language->code == old('user.default_language', $user->default_language) ? 'selected' : '' }}>
                                                {{ $language->name }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="user.default_language" class="mt-1" />
                            </div>

                            <div class="sm:col-span-2">
                                <div wire:ignore>
                                    <x-label for="user.country_id" :value="t('country')" />
                                    <x-select wire:model.defer="user.country_id" id="user.country_id"
                                        class="block w-full mt-1 tom-select">
                                        <option value="">{{ t('country_select') }}</option>
                                        @foreach ($this->countries as $country)
                                            <option value="{{ $country['id'] }}">
                                                {{ $country['short_name'] }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="user.country_id" class="mt-1" />
                            </div>

                            {{-- address --}}
                            <div class="sm:col-span-2">
                                <div class="mb-6">
                                    <x-label for="user.address" :value="t('address')" />
                                    <x-textarea wire:model.defer="user.address" id="user.address" rows="3"
                                        class="block w-full mt-1" autocomplete="off"/>
                                    <x-input-error for="user.address" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        @if (checkPermission(['admin.user.edit', 'admin.user.create']))
                        @php
                            $authUser = auth()->user();
                            $isAdmin = $authUser->is_admin == 1;
                            $isEditingNonAdmin = isset($this->user) && $this->user->id && $this->user->is_admin == 0;
                            $canSeePasswordFields = $isAdmin || ($isEditingNonAdmin && !$isAdmin);
                        @endphp
                        <!-- Show password fields only when creating a new user OR when editing a non-admin user -->
                        @if (!$this->user->id || $canSeePasswordFields)
                            <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Password -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="password" :value="t('password')" />
                                    </div>
                                    <div class="relative mt-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <x-heroicon-o-lock-closed class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <x-input wire:model.defer="password" type="password" id="password" autocomplete="off"
                                            placeholder="{{ $this->user->id ? t('leave_blank_to_keep_current_password') : t('enter_password') }}"
                                            class="w-full pl-10" />
                                    </div>
                                    <x-input-error for="password" class="mt-1" />
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="password_confirmation" class="font-medium">
                                            {{ t('confirm_password') }}
                                        </x-label>
                                    </div>
                                    <div class="relative mt-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <x-heroicon-o-lock-closed class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <x-input wire:model.defer="password_confirmation" type="password"
                                            id="password_confirmation"
                                            placeholder="{{ t('enter_confirm_password') }}" class="w-full pl-10" />
                                    </div>
                                    <x-input-error for="password_confirmation" class="mt-1" />
                                </div>
                            </div>
                        @endif
                        @endif
                        <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

                        <div class="@if (!$this->user->id) grid @endif gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            <!-- Send Welcome Email Toggle -->
                            @if (!$this->user->id)
                            <div x-data="{ 'sendWelcomeMail': @entangle('sendWelcomeMail') }">
                                <div class="flex justify-between items-center">
                                    <x-label for="send_welcome_mail" class="font-medium">
                                        {{ t('send_welcome_mail') }}
                                    </x-label>
                                    <div @toggle-changed="sendWelcomeMail = $event.detail.value">
                                        <x-toggle id="send_welcome_mail" name="send_welcome_mail" :value="$sendWelcomeMail" x-model="sendWelcomeMail" />
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ t('sends_welcome_email_to_new_users') }}</p>
                            </div>
                            @endif
                            <!-- Is Verified Toggle -->
                            @if (!$isVerified)
                            <div x-data="{ 'isVerified': @entangle('isVerified') }">
                                <div class="flex justify-between items-center">
                                    <x-label for="is_verified" class="font-medium">
                                        {{ t('send_verification_mail') }}
                                    </x-label>
                                    <x-toggle id="is_verified" name="is_verified" :value="$isVerified" wire:model="isVerified" />
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ t('mark_email_as_verified_or_send_verification') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </x-slot:content>
                </x-card>
            </div>

            <!-- Right Column (Roles & Permissions) -->
            <div class="w-full lg:w-3/5">
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:header>
                        <div class="flex items-center">
                            <x-heroicon-o-shield-check class="w-8 h-8 mr-2 text-primary-600" />
                            <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('roles_and_permissions') }}
                            </h1>
                        </div>
                    </x-slot:header>

                    <x-slot:content>
                        <!-- Admin Switch -->
                        @if (auth()->user()->is_admin)
                            <div x-data="{
                                isAdmin: @entangle('is_admin'),
                                isAdminVisible: true,
                                resetPermissions() {
                                    if (this.isAdmin) {
                                        $wire.set('selectedPermissions', []);
                                        $wire.set('role_id', null);
                                    }
                                }
                            }" class="mb-6">
                                <div class="flex justify-between items-center">
                                    <x-label for="is_admin" class="text-base font-medium">
                                        {{ t('administrator_access') }}
                                    </x-label>
                                    <div @toggle-changed="isAdmin = $event.detail.value; resetPermissions()">
                                        <x-toggle id="is_admin" name="is_admin" :value="$is_admin" x-model="isAdmin" />
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-6">
                                    {{ t('admin_user_has_full_access_to_all_features') }}
                                </p>
                            </div>
                        @endif

                        <!-- Role Dropdown -->
                        <div x-show="!isAdmin" class="mb-6">
                            <div class="flex items-center gap-1">
                                <span class="text-danger-500">*</span>
                                <x-label for="role_id" class="font-medium">
                                    {{ t('role') }}
                                </x-label>
                            </div>
                            <div class="mt-1">
                                <x-select id="role_id" wire:model="role_id"
                                    wire:change="$set('role_id', $event.target.value)"
                                    class="block w-full tom-select">
                                    <option value="">{{ t('select_role') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $role_id == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                                <x-input-error for="role_id" class="mt-1" />
                            </div>
                        </div>

                        <!-- Permissions -->

                        <div x-show="!isAdmin"
                            class="border rounded-lg border-gray-300 dark:border-gray-700 overflow-hidden shadow-sm">

                            <div
                                class="sticky top-0 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-b border-gray-300 dark:border-gray-600">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-0">
                                    <div class="md:col-span-4 p-3 md:p-4 font-semibold text-sm flex items-center">
                                        <x-heroicon-o-puzzle-piece
                                            class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                                        <span>{{ t('features') }}</span>
                                    </div>
                                    <div class="hidden md:block">
                                        <div class="md:col-span-8 p-3 md:p-4 font-semibold text-sm flex items-center ">
                                            <x-heroicon-o-key
                                                class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                                            <span>{{ t('capabilities') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="max-h-[33.7rem] overflow-y-auto" id="permissions-container">
                                @foreach ($permissions as $group => $groupPermissions)
                                    <div
                                        class="border-b border-gray-200 dark:border-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-0 break-all">
                                            <div
                                                class="md:col-span-3 p-4 font-medium bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 md:border-r border-gray-200 dark:border-gray-700">
                                                <div class="flex justify-between items-center">
                                                    <span>{{ Str::of($group)->replace('_', ' ')->ucfirst() }}</span>
                                                    <x-heroicon-m-chevron-down
                                                        class="md:hidden w-5 h-5 text-gray-400" />
                                                </div>
                                            </div>

                                            <div class="md:col-span-9 p-4">
                                                <div class="flex flex-wrap gap-x-5 gap-y-3 items-center">

                                                    @foreach ($groupPermissions as $permission)
                                                        <div
                                                            class="flex items-center space-x-2 px-3 py-2 rounded-md border shadow-sm transition duration-150 ease-in-out bg-white dark:bg-gray-700 border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">

                                                            <x-checkbox id="permission_{{ $permission['id'] }}"
                                                                wire:model.defer="selectedPermissions"
                                                                value="{{ $permission['name'] }}"
                                                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:focus:ring-primary-400" />

                                                            <label for="permission_{{ $permission['id'] }}"
                                                                class="font-medium text-gray-700 dark:text-gray-300">
                                                                {{ ucfirst(str_replace('_', ' ', Str::afterLast($permission['name'], '.'))) }}
                                                            </label>

                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Admin message when admin is selected -->
                        <div x-show="isAdmin"
                            class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-heroicon-s-information-circle class="h-5 w-5 text-primary-500" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        {{ t('administrator_info') }}</h3>
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <p>{{ t('administrators_full_access_features') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot:content>
                </x-card>
            </div>

            <!-- Footer Actions Bar -->
            <div
                class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 z-10">
                <div class="flex justify-end px-6 py-3">
                    <x-button.secondary class="mx-2" wire:click="cancel">
                        {{ t('cancel') }}
                    </x-button.secondary>
                    <x-button.loading-button type="submit" target="save">
                        {{ $user->exists ? t('update_button') : t('add_button') }}
                    </x-button.loading-button>
                </div>
            </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initTomSelect();

        Livewire.hook('message.processed', (message, component) => {
            initTomSelect();
        });

        function initTomSelect() {
            // Initialize Tom Select for all elements with tom-select class
            document.querySelectorAll('.tom-select').forEach(el => {
                if (!el.tomselect) {
                    new TomSelect(el, {
                        placeholder: el.getAttribute('placeholder') || 'Select an option...',
                        plugins: ['clear_button'],
                        onInitialize: function() {
                            // Custom initialization if needed
                        }
                    });
                }
            });
        }
    });
</script>
