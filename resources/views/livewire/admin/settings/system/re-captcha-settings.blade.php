<div>
    <x-slot:title>
        {{ t('bot_protection') }}
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
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6" x-data="{ 'isReCaptchaEnable': @entangle('isReCaptchaEnable') }">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('bot_protection') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('bot_protection_description') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="flex flex-col justify-start items-start gap-2">
                            <!-- Toggle Switch -->
                            <div class="flex justify-start items-center">
                                <x-toggle id="recaptcha-toggle" name="recaptcha-toggle" :value="$isReCaptchaEnable"
                                    x-on:toggle-changed="isReCaptchaEnable = $event.detail.value" />
                                <span class="ms-3 text-sm font-medium text-slate-900 dark:text-slate-300">
                                    {{ t('enable_recaptcha') }}
                                </span>
                            </div>

                            <!-- Conditionally Displayed Section -->

                            <div x-show="isReCaptchaEnable" class="w-full" x-cloak>
                                <div class="mt-5 w-full">
                                    <div class="flex items-center">
                                        <span x-show="isReCaptchaEnable" class="text-danger-500 mr-1">*</span>
                                        <x-label for="site_key" :value="t('recaptcha_site_key')" />
                                    </div>
                                    <x-input wire:model="site_key" id="site_key" class="block mt-1 w-full" type="text"
                                        name="site_key" placeholder="{{ t('recaptcha_site_key') }}" />
                                    <x-input-error class="mt-2" for="site_key" />
                                </div>
                                <div class="mt-5 w-full">
                                    <div class="flex items-center">
                                        <span x-show="isReCaptchaEnable" class="text-danger-500 mr-1">*</span>
                                        <x-label for="secret_key" :value="t('recaptcha_site_secret')" />
                                    </div>
                                    <x-input wire:model="secret_key" id="secret_key" class="block mt-1 w-full"
                                        type="text" name="secret_key" placeholder="{{ t('recaptcha_site_secret') }}" />
                                    <x-input-error class="mt-2" for="secret_key" />
                                    <p class="text-xs mt-2 dark:text-slate-300">
                                        {{ t('obtain_credential') }}
                                        <a href="https://www.google.com/recaptcha/admin" target="_blank"
                                            class="hover:underline text-success-500 underline">
                                            {{ t('here') }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-dynamic-alert type="warning" class="w-full">
                                <div class="flex items-center gap-2 w-full">
                                    <x-heroicon-o-information-circle
                                        class="w-6 h-6 min-w-6 min-h-6 dark:text-warning-400 flex-shrink-0" />
                                    <p class="font-medium text-sm">
                                        {{ t('v3_setup_description') }}
                                    </p>
                                </div>
                            </x-dynamic-alert>
                        </div>
                    </x-slot:content>

                    <!-- Submit Button -->
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