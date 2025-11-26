<div>
    <x-slot:title>
        {{ t('email_settings') }}
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
        <div class="flex-1 space-y-5 ">
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('email_settings') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('email_settings_description') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>

                        <!-- Information Section -->
                        <div class="mb-5">
                            @if (session()->has('error'))
                                <x-dynamic-alert type="danger">
                                    <x-slot:title class="mb-1">{{ ucfirst(t('error')) }}</x-slot:title>
                                    {{ session('error') }}
                                </x-dynamic-alert>
                            @else
                                <x-dynamic-alert type="primary">
                                    <x-slot:title>{{ t('important_information') }}</x-slot:title>
                                    {{ t('imp_info_description') }}
                                </x-dynamic-alert>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 ">
                            <!-- SMTP Protocol -->
                            <div wire:ignore>
                                <x-label for="mailer" :value="t('smtp_protocol')" class="mb-1" />
                                <x-select wire:model.defer="mailer" id="mailer" class="tom-select">
                                    <option value="">{{ t('select_smtp_protocol') }}</option>
                                    <option value="smtp">{{ t('smtp') }}</option>
                                </x-select>
                                <x-input-error for="mailer" class="mt-2" />
                            </div>

                            <!-- SMTP Host -->
                            <div>
                                <x-label for="smtp_host" :value="t('smtp_host')" class="mb-1" />
                                <x-input wire:model.defer="smtp_host" id="smtp_host" type="text"
                                    placeholder="{{ t('smtp.gmail.com') }}" autocomplete="off" />
                                <x-input-error for="smtp_host" class="mt-2" />
                            </div>

                            <!-- SMTP Port -->
                            <div>
                                <x-label for="smtp_port" :value="t('smtp_port')" class="mb-1" />
                                <x-input wire:model.defer="smtp_port" id="smtp_port" type="text"
                                    placeholder="{{ t('587') }}" autocomplete="off" />
                                <x-input-error for="smtp_port" class="mt-2" />
                            </div>

                            <!-- SMTP UserName -->
                            <div>
                                <x-label for="smtp_username" :value="t('smtp_username')" class="mb-1" />
                                <x-input wire:model.defer="smtp_username" id="smtp_username" type="text"
                                    placeholder="e32103318e100b8" autocomplete="off" />
                                <x-input-error for="smtp_username" class="mt-2" />
                            </div>

                            <!-- SMTP Password -->
                            <div>
                                <x-label for="smtp_password" :value="t('smtp_password')" class="mb-1" />
                                <x-input wire:model.defer="smtp_password" id="smtp_password" type="password"
                                    placeholder="{{ t('********') }}" autocomplete="off" />
                                <x-input-error for="smtp_password" class="mt-2" />
                            </div>

                            <!-- SMTP Encryption -->
                            <div wire:ignore>
                                <x-label for="smtp_encryption" :value="t('smtp_encryption')" class="mb-1 " />
                                <x-select wire:model.defer="smtp_encryption" class="tom-select">
                                    <option value="">{{ t('select_smtp_encryption') }}</option>
                                    <option value="SSL">{{ t('ssl') }}</option>
                                    <option value="TLS">{{ t('tls') }}</option>
                                </x-select>
                                <x-input-error for="smtp_encryption" class="mt-2" class="mb-1" />
                            </div>

                            <!-- Sender Name -->
                            <div>
                                <x-label for="sender_name" :value="t('sender_name')" class="mb-1" />
                                <x-input wire:model.defer="sender_name" id="sender_name" type="text"
                                    placeholder="{{ t('your_company_name') }}" autocomplete="off" />
                                <x-input-error for="sender_name" class="mt-2" />
                            </div>

                            <!-- Sender Email -->
                            <div>
                                <x-label for="sender_email" :value="t('sender_email')" class="mb-1" />
                                <x-input wire:model.defer="sender_email" id="sender_email" type="text"
                                    placeholder="{{ t('example@example.com') }}" autocomplete="off" />
                                <x-input-error for="sender_email" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex items-center gap-2 justify-start w-full border-t pt-3 mt-5">
                            <div class="w-full">
                                <h3 class="text-lg text-slate-700 dark:text-slate-200">{{ t('send_test_email') }}
                                </h3>
                                <span class="text-sm text-slate-700 dark:text-slate-200">
                                    {{ t('send_test_email_description') }}
                                </span>
                                <div class="flex items-center gap-2 mt-1">
                                    <x-input id="test_mail" type="text" placeholder="{{ t('test') }}"
                                        autocomplete="off" x-ref="emailInput" class="w-full" />
                                    <x-button.primary wire:click.prevent="sendTestEmail($refs.emailInput.value)"
                                        wire:loading.attr="disabled" class="whitespace-nowrap">
                                        <span wire:loading.remove
                                            wire:target="sendTestEmail">{{ t('test') }}</span>
                                        <span wire:loading wire:target="sendTestEmail">{{ t('sending') }}</span>
                                    </x-button.primary>
                                </div>
                                <x-input-error for="test_mail" class="mt-1" />
                            </div>
                        </div>
                    </x-slot:content>

                    <!-- Submit Button -->
                    @if(checkPermission('admin.system_settings.edit'))
                    <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg p-4">
                        <div class="flex  justify-end ">
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
