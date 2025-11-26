<div class="mx-auto px-4 md:px-0">
    <x-slot:title>
        {{ t('webhook_integrations') }}
    </x-slot:title>

    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('system_setting') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-tenant-system-settings-navigation wire:ignore />
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('webhook_integrations') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('webhook_integrations_description') }}
                        </x-settings-description>
                    </x-slot:header>

                    <x-slot:content>
                        <div class="space-y-4">
                            <!-- Enable Webhook Toggle -->
                            <div class="pt-4">
                                <h3 class="text-base font-medium text-secondary-900 dark:text-white">
                                    {{ t('enable_webhook_access') }}</h3>
                                <x-toggle wire:model="webhook_enabled" :value="$webhook_enabled" class="mt-2" />
                            </div>

                            <!-- Webhook URL -->
                            <div x-data="{ webhook_enabled: @entangle('webhook_enabled') }">
                                <div class="flex items-center">
                                    <span x-show="webhook_enabled" class="text-danger-500 mr-1">*</span>
                                    <h3 class="text-base font-medium text-secondary-900 dark:text-white">
                                        {{ t('webhook_url') }}
                                    </h3>
                                </div>

                                <div class="mt-2 flex rounded-md">
                                    <x-input type="url" wire:model="webhook_url" placeholder="https://your-domain.com/webhook" for="webhook_url"></x-input>
                                </div>
                                <x-input-error for="webhook_url" />
                            </div>

                            <!-- Webhook Abilities -->
                            <div class="pt-4">
                                <h3 class="text-base font-medium text-secondary-900 dark:text-white">
                                    {{ t('webhook_abilities') }}</h3>
                                <p class="mt-1 text-sm text-secondary-500 dark:text-secondary-400">
                                    {{ t('default_permissions_for_webhook_access') }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                    <!-- Contacts Section -->
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                            <h4 class="text-sm font-semibold text-secondary-900 dark:text-white flex items-center">
                                                <x-heroicon-o-users class="w-4 h-4 mr-2 text-secondary-500" />
                                                {{ t('contacts') }}
                                            </h4>
                                        </div>
                                        <div class="p-4 space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-primary-800 dark:text-primary-300">
                                                        {{ t('contacts_create') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="contacts_actions.create"
                                                    :value="isset($contacts_actions['create']) ? $contacts_actions['create'] : false"
                                                    size="xs"
                                                />
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-warning-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-warning-800 dark:text-warning-300">
                                                        {{ t('contacts_update') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="contacts_actions.update"
                                                    :value="isset($contacts_actions['update']) ? $contacts_actions['update'] : false"
                                                    size="xs"
                                                />
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-danger-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-danger-800 dark:text-danger-300">
                                                        {{ t('contacts_delete') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="contacts_actions.delete"
                                                    :value="isset($contacts_actions['delete']) ? $contacts_actions['delete'] : false"
                                                    size="xs"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Section -->
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                            <h4 class="text-sm font-semibold text-secondary-900 dark:text-white flex items-center">
                                                <x-heroicon-o-check-circle class="w-4 h-4 mr-2 text-secondary-500" />
                                                {{ t('statuses') }}
                                            </h4>
                                        </div>
                                        <div class="p-4 space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-primary-800 dark:text-primary-300">
                                                        {{ t('status_create') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="status_actions.create"
                                                    :value="isset($status_actions['create']) ? $status_actions['create'] : false"
                                                    size="xs"
                                                />
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-warning-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-warning-800 dark:text-warning-300">
                                                        {{ t('status_update') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="status_actions.update"
                                                    :value="isset($status_actions['update']) ? $status_actions['update'] : false"
                                                    size="xs"
                                                />
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-danger-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-danger-800 dark:text-danger-300">
                                                        {{ t('status_delete') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="status_actions.delete"
                                                    :value="isset($status_actions['delete']) ? $status_actions['delete'] : false"
                                                    size="xs"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sources Section -->
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                            <h4 class="text-sm font-semibold text-secondary-900 dark:text-white flex items-center">
                                                <x-heroicon-o-folder class="w-4 h-4 mr-2 text-secondary-500" />
                                                {{ t('sources') }}
                                            </h4>
                                        </div>
                                        <div class="p-4 space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-primary-800 dark:text-primary-300">
                                                        {{ t('source_create') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="source_actions.create"
                                                    :value="isset($source_actions['create']) ? $source_actions['create'] : false"
                                                    size="xs"
                                                />
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-warning-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-warning-800 dark:text-warning-300">
                                                        {{ t('source_update') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="source_actions.update"
                                                    :value="isset($source_actions['update']) ? $source_actions['update'] : false"
                                                    size="xs"
                                                />
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-danger-500 rounded-full"></div>
                                                    <span class="text-sm font-medium text-danger-800 dark:text-danger-300">
                                                        {{ t('source_delete') }}
                                                    </span>
                                                </div>
                                                <x-toggle
                                                    wire:model="source_actions.delete"
                                                    :value="isset($source_actions['delete']) ? $source_actions['delete'] : false"
                                                    size="xs"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (session()->has('success'))
                                <div class="rounded-md bg-success-50 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <x-heroicon-s-check-circle class="h-5 w-5 text-success-400" />
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-success-800">
                                                {{ session('Success') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-slot:content>

                    @if (checkPermission('system_settings.edit'))
                        <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg p-4">
                            <div class="flex justify-end items-center">
                                <x-button.loading-button type="submit" target="save">
                                    {{ t('save_changes_button') }}
                                </x-button.loading-button>
                            </div>
                        </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>