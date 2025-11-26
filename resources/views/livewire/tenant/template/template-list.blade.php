<div class="relative" x-init="getObserver()">
    <x-slot:title>
        {{ t('whatsapp_template') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('whatsapp_template')],
    ]" />

    <x-dynamic-alert type="warning" class="mb-4">
        <div class="space-y-1">
            <p class="text-warning-700 dark:text-warning-300">
                <strong>{{ t('imp_real_time_template_updates') }} </strong><br>

                {{ t('in_your')}}<strong>{{ t('meta_whatsapp_api_configuration') }}</strong>{{ t('if_you_have_subscribe_to_event') }}
                <code>message_template_status_update</code> {{ t('and') }} <code>template_category_update</code>{{ t('template_status_or_category_update_alert') }}<strong>{{ t('real_time') }}</strong>.<br>

                <span class="block mt-1">
                    {{ t('if_you_are') }}<strong>{{ t('not_subscribed') }}</strong>{{ t('template_changes_will_not_update_automatically') }}<strong>{{ t('manually_reload') }}</strong>{{ t('get_latest_status_and_category_information') }}
                </span>

                <span class="block mt-1">
                    {{ t('after_any') }}<strong>{{ t('template_status_update') }}</strong>{{ t('please_verify') }}
                    <strong>{{ t('configured_template_bots') }}</strong> {{ t('and') }} <strong>{{ t('scheduled_campaigns') }}</strong>
                    {{ t('validation_ensure') }}
                </span>
            </p>
        </div>
    </x-dynamic-alert>

    <div class="flex flex-col sm:flex-row justify-start items-start lg:items-center gap-2 mb-4">
        @if (checkPermission('tenant.template.load_template'))
        @if (get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected') != 0)
        <x-button.loading-button type="button" target="loadTemplate" wire:click="loadTemplate"
            class="whitespace-nowrap px-4 py-2">
            {{ t('load_template') }}
        </x-button.loading-button>
        @endif
        @endif

        <a href="https://business.facebook.com/wa/manage/message-templates/" target="_blank" rel="noopener noreferrer">
            <x-button.primary class="whitespace-nowrap px-4 py-2">
                {{ t('template_management') }}
            </x-button.primary>
        </a>
        @if (checkPermission('tenant.template.create'))
        @if (get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected') != 0)
        <x-button.primary class="whitespace-nowrap px-4 py-2"
            href="{{ tenant_route('tenant.dynamic-template.index') }}">
            <button type="button" class="relative hover:text-primary-500 text-gray-500 dark:text-slate-400 mr-2">
                <!-- Status Indicator -->
                <span class="flex items-center justify-center">
                    <span class="absolute h-3 w-3 rounded-full opacity-75 bg-gray-200 animate-ping"></span>
                    <x-heroicon-m-plus class="w-4 h-4 text-white" />
                </span>
            </button>
            {{ t('create_template') }}
        </x-button.primary>
        @endif
        @endif
    </div>

    <x-card class="rounded-lg">
        <x-slot:content>
            <div class="lg:mt-0" wire:poll.10s="refreshTable">
                <livewire:tenant.tables.whatspp-template-table />
            </div>
        </x-slot:content>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-template-modal'" title="{{ t('delete_template') }}"
        wire:model="showDeleteConfirmation">
        <x-slot:description>
            <div class="space-y-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('template_delete_confirmation') }}
                    @if ($templateToDelete)
                    <strong class="text-gray-900 dark:text-white">{{ $templateToDelete['name'] }}</strong>
                    @endif
                </p>
                <div class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-warning-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-warning-700 dark:text-warning-200">
                                {{ t('template_delete_warning') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:description>

        <x-button.delete-button wire:click="confirmDelete" wire:loading.attr="disabled" class="sm:ml-3">
            <span wire:loading.remove wire:target="confirmDelete">
                {{ t('delete') }}
            </span>
            <span wire:loading wire:target="confirmDelete" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                {{ t('deleting') }}...
            </span>
        </x-button.delete-button>

        <x-button.cancel-button wire:click="cancelDelete" wire:loading.attr="disabled">
            {{ t('cancel') }}
        </x-button.cancel-button>
    </x-modal.confirm-box>
</div>