<div class="mx-auto px-4 md:px-0">
    <x-slot:title>
        {{ t('api_integration_and_access') }}
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
                                                :value="config('app.url') . '/api/admin/v1/'" readonly x-ref="apiUrl" />
                                            <x-button.secondary x-on:click="copyApiUrl()" class="w-full sm:w-auto">
                                                <span x-text="copiedApiUrl ? 'Copied' : 'Copy'">{{ t('copy') }}</span>
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
                                            endpointPath: '/plans',
                                            get fullEndpoint() {
                                                return '{{ config('app.url') }}/api/admin/v1' + this.endpointPath;
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
                                                {{ t('plan') }}
                                            </h4>
                                            <div class="flex flex-wrap gap-2 px-5 pb-3">
                                                <span @click="$dispatch('set-endpoint', '/plan')"
                                                    class="cursor-pointer px-2 py-1 rounded text-xs font-medium bg-success-100 text-success-800 hover:bg-success-200 transition-colors">
                                                    {{ t('plans_read') }}
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