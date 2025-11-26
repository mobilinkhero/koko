<div class="mx-auto px-4 md:px-0">
    <x-slot:title>
        {{ t('api_integration_and_access') }}
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
                            {{ t('api_integration_and_access') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('api_integration_and_access_description') }}
                        </x-settings-description>
                    </x-slot:header>

                    <x-slot:content>
                        <div class="space-y-6">
                            <!-- Enable API Access -->
                            <div>
                                <h3 class="text-base font-medium text-secondary-900 dark:text-white">
                                    {{ t('enable_api_access') }}</h3>

                                <div class="mt-2">
                                    <x-toggle :value="$isEnabled"
                                        @toggle-changed.window="$wire.toggleApiAccess($event.detail.value)" />
                                </div>
                            </div>

                            <!-- API Token -->
                            <div
                                class="bg-white dark:bg-secondary-800 rounded-lg border border-secondary-200 dark:border-secondary-700 p-5">
                                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">
                                    {{ t('api_token') }}</h3>
                                <div class="mt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2" x-data="{
                                        copied: false,
                                        copyText() {
                                            const text = $refs.currentToken?.value;
                                            if (!text) {
                                                showNotification('No text found to copy', 'danger');
                                                return;
                                            }
                                            copyToClipboard(text);
                                            this.copied = true;
                                            setTimeout(() => this.copied = false, 2000);
                                        }
                                    }">

                                    <!-- Input Field -->
                                    <x-input id="token" type="text" class="flex-1 w-full" :value="$currentToken"
                                        readonly x-ref="currentToken" />

                                    <!-- Buttons -->
                                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                        <x-button.secondary type="button" wire:click="generateNewToken"
                                            class="w-full sm:w-auto">
                                            {{ t('generate_new_token') }}
                                        </x-button.secondary>

                                        @if ($currentToken)
                                        <x-button.secondary x-on:click="copyText()" class="w-full sm:w-auto">
                                            <span x-text="copied ? 'Copied' : 'Copy'">{{ t('copy') }}</span>
                                        </x-button.secondary>
                                        @endif
                                    </div>
                                </div>

                                @if ($newTokenGenerated)
                                <p class="mt-2 text-sm text-warning-600 dark:text-warning-500">
                                    {{ t('please_copy_your_new_api_token_now') }}
                                </p>
                                @endif
                            </div>

                            <!-- API Endpoint Information -->
                            <div
                                class="bg-white dark:bg-secondary-800 rounded-lg border border-secondary-200 dark:border-secondary-700 p-5 mt-6 space-y-6">
                                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">
                                    {{ t('api_endpoint_information') }}</h3>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-2">
                                    <!-- API Base URL -->
                                    <div class="">
                                        <h4
                                            class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3">
                                            {{ t('api_base_url') }}
                                        </h4>
                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2"
                                            x-data="{
                                                copiedApiUrl: false,
                                                copyApiUrl() {
                                                    const text = $refs.apiUrl?.value;
                                                    if (!text) {
                                                        showNotification('No text found to copy', 'danger');
                                                        return;
                                                    }
                                                    copyToClipboard(text);
                                                    this.copiedApiUrl = true;
                                                    setTimeout(() => this.copiedApiUrl = false, 2000);
                                                }
                                            }">
                                            <x-input id="api_url" type="text" class="flex-1 w-full"
                                                :value="config('app.url') . '/api/v1/'" readonly x-ref="apiUrl" />
                                            <x-button.secondary x-on:click="copyApiUrl()" class="w-full sm:w-auto">
                                                <span x-text="copiedApiUrl ? 'Copied' : 'Copy'">{{ t('copy') }}</span>
                                            </x-button.secondary>
                                        </div>
                                    </div>

                                    <!-- Tenant Subdomain -->
                                    <div>
                                        <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                                            {{ t('tenant_subdomain') }}
                                        </h4>
                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2"
                                            x-data="{
                                                copiedSubdomain: false,
                                                copySubdomain() {
                                                    const text = $refs.tenantSubdomain?.value;
                                                    if (!text) {
                                                        showNotification('No text found to copy', 'danger');
                                                        return;
                                                    }
                                                    copyToClipboard(text);
                                                    this.copiedSubdomain = true;
                                                    setTimeout(() => this.copiedSubdomain = false, 2000);
                                                }
                                            }">
                                            <x-input id="tenant_subdomain" type="text" class="flex-1 w-full"
                                                :value="$subdomain" readonly x-ref="tenantSubdomain" />
                                            <x-button.secondary x-on:click="copySubdomain()" class="w-full sm:w-auto">
                                                <span x-text="copiedSubdomain ? 'Copied' : 'Copy'">{{ t('copy')
                                                    }}</span>
                                            </x-button.secondary>
                                        </div>
                                    </div>
                                </div>


                                <!-- Example API Endpoint Section - UPDATED EVENT LISTENER -->
                                <div class="mt-6">
                                    <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                                        {{ t('example_api_endpoint') }}</h4>
                                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2" x-data="{
                                            copiedExample: false,
                                            endpointPath: '/templates',
                                            get fullEndpoint() {
                                                return '{{ config('app.url') }}/api/v1/{{ $subdomain }}' + this.endpointPath;
                                            },
                                            setEndpoint(path) {
                                                this.endpointPath = path;
                                            },
                                            copyExample() {
                                                const text = this.fullEndpoint;
                                                if (!text) {
                                                    showNotification('No text found to copy', 'danger');
                                                    return;
                                                }
                                                copyToClipboard(text);
                                                this.copiedExample = true;
                                                setTimeout(() => this.copiedExample = false, 2000);
                                            }
                                        }" @set-endpoint.window="setEndpoint($event.detail)">
                                        <x-input id="example_endpoint" type="text" class="flex-1 w-full"
                                            x-bind:value="fullEndpoint" readonly x-ref="exampleEndpoint" />
                                        <x-button.secondary x-on:click="copyExample()" class="w-full sm:w-auto">
                                            <span x-text="copiedExample ? 'Copied' : 'Copy'">{{ t('copy') }}</span>
                                        </x-button.secondary>
                                    </div>
                                </div>
                            </div>
                            <!-- Token Abilities Section - UPDATE ALL CLICKABLE SPANS -->
                            <x-card>
                                <x-slot:content>
                                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-2">
                                        {{ t('token_abilities') }}</h3>
                                    <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-6">
                                        {{ t('these_are_the_default_permissions_for_api_access') }}
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-4 border-b dark:border-secondary-700 p-3">
                                                {{ t('contacts') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/contacts')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-primary-100 text-primary-800 hover:bg-primary-200 transition-colors">
                                                    {{ t('contacts_create') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/contacts')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('contacts_read') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/contacts/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-warning-100 text-warning-800 hover:bg-warning-200 transition-colors">
                                                    {{ t('contacts_update') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/contacts/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-danger-100 text-danger-800 hover:bg-danger-200 transition-colors">
                                                    {{ t('contacts_delete') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3">
                                                {{ t('statuses') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/statuses')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-primary-100 text-primary-800 hover:bg-primary-200 transition-colors">
                                                    {{ t('status_create') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/statuses')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('status_read') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/statuses/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-warning-100 text-warning-800 hover:bg-warning-200 transition-colors">
                                                    {{ t('status_update') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/statuses/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-danger-100 text-danger-800 hover:bg-danger-200 transition-colors">
                                                    {{ t('status_delete') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3">
                                                {{ t('sources') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/sources')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-primary-100 text-primary-800 hover:bg-primary-200 transition-colors">
                                                    {{ t('source_create') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/sources')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('source_read') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/sources/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-warning-100 text-warning-800 hover:bg-warning-200 transition-colors">
                                                    {{ t('source_update') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/sources/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-danger-100 text-danger-800 hover:bg-danger-200 transition-colors">
                                                    {{ t('source_delete') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3">
                                                {{ t('templates') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/templates')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('template_read') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3">
                                                {{ t('message_bots') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/message-bots')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('message_bot_read') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3">
                                                {{ t('template_bots') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/template-bots')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('template_bot_read') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3">
                                                {{ t('groups') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/groups')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-primary-100 text-primary-800 hover:bg-primary-200 transition-colors">
                                                    {{ t('group_create') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/groups')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('group_read') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/groups/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-warning-100 text-warning-800 hover:bg-warning-200 transition-colors">
                                                    {{ t('group_update') }}
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/groups/{id}')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-danger-100 text-danger-800 hover:bg-danger-200 transition-colors">
                                                    {{ t('group_delete') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-secondary-800 border dark:border-secondary-700 rounded-lg shadow-sm">
                                            <h4
                                                class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-3 border-b p-3 flex items-center gap-1">
                                                {{ t('message_sending') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/messages/send')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-primary-100 text-primary-800 hover:bg-primary-200 transition-colors flex items-center gap-1">
                                                    <x-heroicon-o-question-mark-circle class="w-4 h-4 cursor-pointer"
                                                        data-tippy-content="{{ t('sending_message_limit_alert') }}" />
                                                    <span>{{ t('simple_message_send') }} </span>
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/messages/media')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors flex items-center gap-1">
                                                    <x-heroicon-o-question-mark-circle class="w-4 h-4 cursor-pointer"
                                                        data-tippy-content="{{ t('sending_message_limit_alert') }}" />
                                                    <span>{{ t('media_message_send') }} </span>
                                                </span>
                                                <span @click="$dispatch('set-endpoint', '/messages/template')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-warning-100 text-warning-800 hover:bg-warning-200 transition-colors">
                                                    {{ t('template_message_send') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </x-slot:content>
                            </x-card>

                            @if (session()->has('success'))
                            <div class="rounded-md bg-success-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-success-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
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