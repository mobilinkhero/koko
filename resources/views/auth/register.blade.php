<x-guest-layout>
    <x-slot:title>
        {{ t('register') }}
    </x-slot:title>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-900 flex items-center justify-center relative">
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 lg:top-8 lg:right-10 z-20">
            <livewire:language-switcher />
        </div>
        <div class="container mx-auto px-4 py-8">
            <div
                class="flex flex-col lg:flex-row w-full overflow-hidden bg-white dark:bg-slate-800 rounded-xl shadow-2xl">

                <!-- Image Section -->
                <div class="hidden lg:block lg:w-2/5 relative bg-gradient-to-br from-primary-600 to-purple-700">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="relative h-full p-12 flex flex-col justify-between z-10">
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-2">{{ t('join_us') }}</h1>
                            <p class="text-white/80 text-lg">{{ t('create_account_message') }}</p>
                        </div>
                        <div class="flex items-center justify-center h-full">
                            @php
                            $settings = get_batch_settings(['theme.cover_page_image']);
                            $cover_page_image = $settings['theme.cover_page_image'];
                            // Get the image path from settings
                            $imagePath = $cover_page_image
                            ? Storage::url($cover_page_image)
                            : url('./img/coverpage.png');
                            @endphp
                            <img src="{{ $imagePath }}" alt="Cover Page Image"
                                class="object-contain max-h-full max-w-full">
                        </div>
                        <div class="mt-auto">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                    <x-heroicon-o-user-plus class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ t('get_started') }}</p>
                                    <p class="text-white/70 text-sm">{{ t('quick_setup') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Register Section -->
                <div class="w-full lg:w-3/5 p-6 lg:p-8 flex items-center justify-center">
                    <div class="w-full max-w-3xl mx-auto">
                        <!-- Logo/Header -->
                        <div class="text-center mb-6">
                            <div class="flex justify-center mb-3">
                                <div
                                    class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <x-heroicon-o-user-plus class="h-7 w-7 text-primary-600 dark:text-primary-400" />
                                </div>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">{{ t('register') }}</h2>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                {{ t('create_account') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('register') }}" x-data="{
                            currentTab: 'personal',
                            loading: false,
                            companyName: '{{ old('company_name') }}',
                            tenantName: '{{ old('subdomain') }}',
                            manuallyEdited: false,

                            slugify(str) {
                                return str
                                    .toLowerCase()
                                    .replace(/\s+/g, '') // Remove all spaces
                                    .replace(/[^a-z0-9]/g, '') // Remove all non-alphanumeric characters (including dashes)
                            },

                            sanitizeTenantName() {
                                this.tenantName = this.tenantName
                                    .toLowerCase()
                                    .replace(/[^a-z0-9]/g, ''); // Only allow a-z, 0-9, and dash
                            },

                            nextTab() {
                                if (this.currentTab === 'personal') this.currentTab = 'company';
                                else if (this.currentTab === 'company') this.currentTab = 'security';
                            },

                            prevTab() {
                                if (this.currentTab === 'security') this.currentTab = 'company';
                                else if (this.currentTab === 'company') this.currentTab = 'personal';
                            },

                            isTabComplete(tab) {
                                // This function would validate each tab's required fields
                                return true; // For now, always allow tab switching
                            }
                        }" x-init="$watch('companyName', value => {
                                if (!manuallyEdited) tenantName = slugify(value);
                            })" x-on:submit="loading = true">
                            @csrf

                            <!-- Tab Navigation -->
                            <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                                <nav class="flex -mb-px space-x-4 md:space-x-8 overflow-auto sm:overflow-visible"
                                    aria-label="Tabs">
                                    <button type="button" @click="currentTab = 'personal'"
                                        :class="{
                                            'border-primary-500 text-primary-600 dark:text-primary-400': currentTab === 'personal',
                                            'border-danger-500 text-danger-600 dark:text-danger-400': currentTab !== 'personal' && {{ $errors->hasAny(['firstname', 'lastname', 'email', 'phone']) ? 'true' : 'false' }},
                                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': currentTab !== 'personal' && {{ $errors->hasAny(['firstname', 'lastname', 'email', 'phone']) ? 'false' : 'true' }}                          }"
                                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                                        <x-heroicon-o-user-circle class="h-5 w-5 mr-2" />
                                        <span>{{ t('personal_information') }}</span>
                                    </button>

                                    <button type="button" @click="currentTab = 'company'"
                                        :class="{
                                            'border-primary-500 text-primary-600 dark:text-primary-400': currentTab === 'company',
                                            'border-danger-500 text-danger-600 dark:text-danger-400': currentTab !== 'company' && {{ $errors->hasAny(['company_name', 'subdomain', 'address', 'country_id']) ? 'true' : 'false' }},
                                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': currentTab !== 'company' && {{ $errors->hasAny(['company_name', 'subdomain', 'address', 'country_id']) ? 'false' : 'true' }}                          }"
                                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                                        <x-heroicon-o-building-office class="h-5 w-5 mr-2" />
                                        <span>{{ t('company_information') }}</span>
                                    </button>

                                      {{ do_action('registration.billing_tab_button') }}

                                    <button type="button" @click="currentTab = 'security'"
                                        :class="{
                                            'border-primary-500 text-primary-600 dark:text-primary-400': currentTab === 'security',
                                            'border-danger-500 text-danger-600 dark:text-danger-400': currentTab !== 'security' && {{ $errors->hasAny(['password', 'password_confirmation','g-recaptcha-response']) ? 'true' : 'false' }},
                                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': currentTab !== 'security' && {{ $errors->hasAny(['password', 'password_confirmation','g-recaptcha-response']) ? 'false' : 'true' }}                          }"
                                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                                        <x-heroicon-o-lock-closed class="h-5 w-5 mr-2" />
                                        <span>{{ t('security') }}</span>
                                    </button>
                                </nav>
                            </div>

                            <!-- Personal Information Tab -->
                            <div x-show="currentTab === 'personal'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                class="min-h-[400px] flex flex-col" x-cloak>
                                <div class="space-y-4 flex-grow">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- First Name -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="firstname" :value="t('first_name')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-user class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="firstname"
                                                    class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    type="text" name="firstname" :value="old('firstname')" autofocus
                                                    autocomplete="off" placeholder="{{ t('john') }}" />
                                            </div>
                                            <x-input-error class="mt-2" for="firstname" />
                                        </div>

                                        <!-- Last Name -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="lastname" :value="t('last_name')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-user class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="lastname"
                                                    class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    type="text" name="lastname" :value="old('lastname')"
                                                    autocomplete="off" placeholder="{{ t('doe') }}" />
                                            </div>
                                            <x-input-error class="mt-2" for="lastname" />
                                        </div>

                                        <!-- Email Address -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="email" :value="t('email')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-envelope class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="email"
                                                    class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    type="email" name="email" :value="old('email')" autocomplete="off"
                                                    placeholder="your@email.com" />
                                            </div>
                                            <x-input-error class="mt-2" for="email" />
                                        </div>

                                        <!-- Phone -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="phone" :value="t('phone')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div x-data="{
                                                phone: '',
                                                errorMessage: '',
                                                validatePhone() {
                                                    if (this.phone.length > 18) {
                                                        this.errorMessage = 'You can only enter up to 18 digits';
                                                        this.phone = this.phone.slice(0, 18);
                                                    } else {
                                                        this.errorMessage = '';
                                                    }
                                                }
                                            }">
                                                <x-input x-model="phone" x-ref="phone" x-on:input="validatePhone"
                                                    name="phone" type="tel" maxlength="18" id="phone"
                                                    placeholder="{{ t('enter_phone_number') }}" :value="old('phone')"
                                                    class="phone-input mt-[2px] block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    autocomplete="off" />
                                                <p x-show="errorMessage" x-text="errorMessage"
                                                    class="text-sm text-danger-600 dark:text-danger-400 mt-1"></p>
                                            </div>
                                            <x-input-error class="mt-2" for="phone" />
                                        </div>

                                        <!-- Country -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <x-label for="country_id" :value="t('country')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-globe-alt class="h-6 w-6 text-gray-400" />
                                                </div>
                                                <div wire:ignore>
                                                    <x-select name="country_id" id="country_id"
                                                        class="w-full mt-1 tom-select">
                                                        <option value="">{{ t('select_country') }}</option>
                                                        @foreach (get_country_list() as $country)
                                                        <option value="{{ $country['id'] }}" {{
                                                            old('country_id')==$country['id'] ? 'selected' : '' }}>
                                                            {{ $country['short_name'] }}
                                                        </option>
                                                        @endforeach
                                                    </x-select>
                                                </div>
                                            </div>
                                            <x-input-error class="mt-2" for="country_id" />
                                        </div>

                                        {{-- Plan --}}
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="plan_id" :value="t('plan')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>

                                            <div class="relative">
                                                @if ($plan)
                                                <input type="hidden" name="plan_id" value="{{ $plan->id }}" />
                                                <x-input id="plan_id" name="plan_id"
                                                    value="{{ $plan->name }} - {{ get_base_currency()->format($plan->price) }}"
                                                    disabled />
                                                @else
                                                <!-- Show dropdown for available plans -->
                                                <div wire:ignore>
                                                    <x-select name="plan_id" id="plan_id"
                                                        class="block w-full tom-select rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                                        <option value="">{{ t('select_a_plan') }}</option>
                                                        @foreach ($plans as $p)
                                                        <option value="{{ $p->id }}" {{ old('plan_id')==$p->id ?
                                                            'selected' : '' }}>
                                                            {{ $p->name }} - {{ get_base_currency()->format($p->price)
                                                            }}
                                                        </option>
                                                        @endforeach
                                                    </x-select>
                                                </div>
                                                @endif
                                            </div>
                                            <x-input-error class="mt-2" for="plan_id" />
                                        </div>
                                         
                                    </div>
                                      
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="flex justify-end mt-auto pt-6">
                                    <button type="button" @click="nextTab()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        {{ t('next') }}
                                        <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Company Information Tab -->
                            <div x-show="currentTab === 'company'" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                class="min-h-[400px] flex flex-col" x-cloak>
                                <div class="space-y-4 flex-grow">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Company Name -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="company_name" :value="t('company_name')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-building-office class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="company_name"
                                                    class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    name="company_name" x-model="companyName" type="text"
                                                    autocomplete="off" :value="old('company_name')"
                                                    placeholder="{{ t('your_company_name') }}" />
                                            </div>
                                            <x-input-error class="mt-2" for="company_name" />
                                        </div>

                                        <!-- Tenant Name (subdomain) -->
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="subdomain" :value="t('tenant_name')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-s-globe-alt class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="subdomain"
                                                    class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    autocomplete="off" name="subdomain" x-model="tenantName" x-on:input="
                                                            sanitizeTenantName();
                                                            if (!manuallyEdited) manuallyEdited = true;" x-on:blur="
                                                            if (window.existingSubdomains.includes(tenantName.toLowerCase())) {
                                                                window.dispatchEvent(new CustomEvent('notify', {
                                                                    detail: {
                                                                    type: 'danger',
                                                                    message: 'Company name already exists. Please choose another.'
                                                                    }
                                                                }));
                                                            }" type="text" :value="old('subdomain')"
                                                    placeholder="{{ t('tenant_name') }}" />
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                Your URL: <span
                                                    class="font-medium text-primary-600 dark:text-primary-400"
                                                    x-text="tenantName ? `{{ config('app.url') }}/${tenantName}` : '{{ config('app.url') }}/___'"></span>
                                            </div>
                                            <x-input-error class="mt-2" for="subdomain" />
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="md:col-span-2">
                                        <div class="flex items-center mb-1">
                                            <x-label for="address" :value="t('address')"
                                                class="text-gray-700 dark:text-gray-300 font-medium" />
                                        </div>
                                        <div class="relative">
                                            <x-textarea id="address" rows="3" name="address" :value="old('address')"
                                                autocomplete="off" placeholder="{{ t('address_desc') }}" />
                                        </div>
                                        <x-input-error class="mt-2" for="address" />
                                    </div>

                                    {{ do_action('registration.business_identification_fileds')}}
                                  
                                </div>


                                <!-- Navigation Buttons -->
                                <div class="flex justify-between mt-auto pt-6">
                                    <button type="button" @click="prevTab()"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                                        <x-heroicon-o-arrow-left class="mr-2 h-4 w-4" />
                                        {{ t('previous') }}
                                    </button>
                                    <button type="button" @click="nextTab()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        {{ t('next') }}
                                        <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                             {{-- Billing Tab --}}
                               {{ do_action('registration.billing_tab') }}

                            <!-- Security Tab -->
                            <div x-show="currentTab === 'security'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                class="min-h-[400px] flex flex-col space-y-4" x-cloak>

                                <!-- Content section that grows -->
                                <div class="flex-grow">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Password -->
                                        <div x-data="{ showPassword: false }">
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="password" :value="t('password')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-lock-closed class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="password"
                                                    class="block w-full pl-10 pr-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    x-bind:type="showPassword ? 'text' : 'password'" name="password"
                                                    autocomplete="off" placeholder="••••••••" />
                                                <!-- Eye Icon Button -->
                                                <button type="button"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                                                    x-on:click="showPassword = !showPassword">
                                                    <x-heroicon-m-eye x-show="showPassword"
                                                        class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                                                    <x-heroicon-m-eye-slash x-show="!showPassword"
                                                        class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                                                </button>
                                            </div>
                                            <x-input-error class="mt-2" for="password" />
                                        </div>

                                        <!-- Confirm Password -->
                                        <div x-data="{ showPassword: false }">
                                            <div class="flex items-center mb-1">
                                                <span class="text-danger-500 text-sm mr-1">*</span>
                                                <x-label for="password_confirmation" :value="t('confirm_password')"
                                                    class="text-gray-700 dark:text-gray-300 font-medium" />
                                            </div>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <x-heroicon-o-key class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <x-input id="password_confirmation"
                                                    class="block w-full pl-10 pr-10 rounded-lg border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                                    x-bind:type="showPassword ? 'text' : 'password'"
                                                    name="password_confirmation" autocomplete="off"
                                                    placeholder="••••••••" />
                                                <!-- Eye Icon Button -->
                                                <button type="button"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                                                    x-on:click="showPassword = !showPassword">
                                                    <x-heroicon-m-eye x-show="showPassword"
                                                        class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                                                    <x-heroicon-m-eye-slash x-show="!showPassword"
                                                        class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                                                </button>
                                            </div>
                                            <x-input-error class="mt-2" for="password_confirmation" />
                                        </div>
                                    </div>
                                   
                                </div>
                                        {{ do_action('registration.legal_agreemnet_checkbox')}}
                               
                                @php
                                $settings = get_batch_settings(['re-captcha.isReCaptchaEnable']);
                                @endphp
                                @if ($settings['re-captcha.isReCaptchaEnable'])
                                <div class="mb-5">
                                    <div class="bg-slate-100 p-4 rounded-md text-sm text-slate-600">
                                        {{ t('site_protected_by_recaptcha') }}
                                        <a href="https://policies.google.com/privacy" class="hover:text-slate-500"
                                            tabindex="-1">{{ t('privacy_policy') }}</a> {{ t('and') }}
                                        <a href="https://policies.google.com/terms" class="hover:text-slate-500"
                                            tabindex="-1">{{ t('terms_of_service') }}</a> apply.
                                    </div>
                                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                                    <x-input-error :messages="$errors->first('g-recaptcha-response')" class="mt-2"
                                        for="g-recaptcha-response" />
                                </div>
                                @endif

                                <!-- Navigation Buttons -->
                                <div class="flex justify-between mt-auto pt-6">
                                    <button type="button" x-on:click="prevTab()"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                                        <x-heroicon-o-arrow-left class="mr-2 h-4 w-4" />
                                        {{ t('previous') }}
                                    </button>

                                    <!-- Register Button -->
                                    <button type="submit"
                                        class="relative flex justify-center w-48 items-center px-4 py-2 text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
                                        :disabled="loading">

                                        <!-- Loading spinner -->
                                        <template x-if="loading">
                                            <div class="flex items-center">
                                                <x-heroicon-o-arrow-path class="animate-spin w-5 h-5" />
                                            </div>
                                        </template>

                                        <!-- Regular content with invisible class instead of x-show -->
                                        <template x-if="!loading">
                                            <div class="flex items-center">
                                                <x-heroicon-o-user-plus class="w-5 h-5 mr-2" />
                                                <span>{{ t('create_account_btn') }}</span>
                                            </div>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <!-- Already have an account link -->
                            <div class="text-center mt-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ t('already_registered') }}
                                    <a href="{{ route('login', $plan ? ['plan_id' => $plan->id] : []) }}"
                                        class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-medium ml-1">
                                        {{ t('login') }}
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
@php
$settings = get_batch_settings(['re-captcha.isReCaptchaEnable', 're-captcha.site_key']);
@endphp

@if (!empty($settings['re-captcha.isReCaptchaEnable']))
<script src="https://www.google.com/recaptcha/api.js?render={{ $settings['re-captcha.site_key'] }}"></script>
<script>
    grecaptcha.ready(function() {
            grecaptcha.execute('{{ $settings['re-captcha.site_key'] }}', {
                action: 'login'
            }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
</script>
@endif
<script>
    window.existingSubdomains = @json($subdomains);
</script>