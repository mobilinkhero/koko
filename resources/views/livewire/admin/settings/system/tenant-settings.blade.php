<div class="mx-auto">
    <x-slot:title>
        {{ t('tenant_settings') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="flex justify-between">
        <div class="pb-6">
            <x-settings-heading>{{ t('system_settings') }}</x-settings-heading>
        </div>
    </div>
    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-system-settings-navigation wire:ignore />
        </div>

        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6" x-data="{ 'isRegistrationEnable': @entangle('isRegistrationEnabled'), 'isVerificationEnabled': @entangle('isVerificationEnabled'), 'isEmailConfirmationEnabled': @entangle('isEmailConfirmationEnabled'), 'isEnableWelcomeEmail' : @entangle('isEnableWelcomeEmail'), 'set_default_tenant_language': @entangle('set_default_tenant_language') }">
                <x-card class="rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                    <x-slot:header class="pb-3 border-b border-slate-200 dark:border-slate-700">
                        <x-settings-heading class="text-xl font-semibold text-slate-900 dark:text-white">
                            {{ t('allow_tenant_registration') }}
                        </x-settings-heading>
                        <x-settings-description class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ t('enable_or_disable_public_tenant_registration_if_disabled') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content class="space-y-4 py-5">
                        <!-- Registration Toggle -->
                        <div
                            class="p-4 transition duration-150 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <x-heroicon-o-user-plus class="h-6 w-6 text-primary-500" />
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ t('enable_registration') }}</h3>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ t('allow_new_tenants_to_register_on_your_platform') }}</p>
                                    </div>
                                </div>
                                <div>
                                    <x-toggle
                                        id="isRegistrationEnable"
                                        name="isRegistrationEnable"
                                        :value="$isRegistrationEnabled"
                                        x-model="isRegistrationEnable"
                                        x-on:toggle-changed="isRegistrationEnable = $event.detail.value"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Email Verification Toggle -->
                        <div
                            class="p-4 transition duration-150 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <x-carbon-security class="h-6 w-6 text-primary-500" />

                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ t('enable_email_verification') }}</h3>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ t('require_email_verification_before_tenants_can_access_their_accounts') }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <x-toggle
                                        id="isVerificationEnabled"
                                        name="isVerificationEnabled"
                                        :value="$isVerificationEnabled"
                                        x-model="isVerificationEnabled"
                                        x-on:toggle-changed="isVerificationEnabled = $event.detail.value"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Verify Tenant bu superadmin --}}

                        <div
                            class="p-4 transition duration-150 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <x-carbon-mail-all class="h-6 w-6 text-primary-500" />

                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ t('enable_email_confirmation_from_administrator') }}</h3>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ t('confirmation_from_administrator_after_tenant_register') }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <x-toggle
                                        id="isEmailConfirmationEnabled"
                                        name="isEmailConfirmationEnabled"
                                        :value="$isEmailConfirmationEnabled"
                                        x-model="isEmailConfirmationEnabled"
                                        x-on:toggle-changed="isEmailConfirmationEnabled = $event.detail.value"
                                    />
                                </div>
                            </div>
                        </div>
                        {{-- Enable Welcome Email --}}
                        <div
                            class="p-4 transition duration-150 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <x-carbon-email-new class="h-6 w-6 text-primary-500" />

                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ t('enable_send_welcome_mail') }}</h3>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ t('notify_administrator_when_new_tenant_register') }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <x-toggle
                                        id="isEnableWelcomeEmail"
                                        name="isEnableWelcomeEmail"
                                        :value="$isEnableWelcomeEmail"
                                        x-model="isEnableWelcomeEmail"
                                        x-on:toggle-changed="isEnableWelcomeEmail = $event.detail.value"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Default Tenant Language --}}
                        <div
                            class="p-4 transition duration-150 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <x-heroicon-o-language class="h-6 w-6 text-primary-500" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-slate-900 dark:text-white mb-2">
                                        {{ t('default_tenant_language') }}</h3>
                                    <p class="mt-1 mb-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ t('set_default_language_for_new_tenants') }}
                                    </p>
                                    <div wire:ignore class="grid grid-cols-3 gap-2">
                                        <x-select
                                            id="set_default_tenant_language"
                                            class="mt-1 block w-full tom-select"
                                            wire:model.defer="set_default_tenant_language"
                                            x-model="set_default_tenant_language">
                                            @foreach (getTenantDefaultLanguage() as $language)
                                            <option value="{{ $language['code'] }}" {{ $language['code'] == $set_default_tenant_language ? 'selected' : '' }}>
                                                {{ $language['name'] }}
                                            </option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot:content>
                    @if(checkPermission('admin.system_settings.edit'))
                    <x-slot:footer
                        class="bg-slate-50 dark:bg-slate-800/50 px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                        <x-button.loading-button type="submit" target="save">
                            {{ t('save_changes') }}
                        </x-button.loading-button>
                    </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>
