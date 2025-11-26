<div>
    <x-slot:title>
        {{ t('import_contact') }}
    </x-slot:title>
    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('contact'), 'route' => tenant_route('tenant.contacts.list')],
        ['label' => t('import_contact')]
    ]" />
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ t('import_logs') }}
            </h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                {{ t('import_logs_description') }}
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ tenant_route('tenant.contacts.imports') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                {{ t('import') }}
            </a>
        </div>
    </div>

    <!-- PowerGrid Table -->
    <x-card>
        <x-slot:content>
            <livewire:tenant.tables.import-contacts-logs />
        </x-slot:content>
    </x-card>

    <!-- Import Details Modal -->
    <x-modal.custom-modal :id="'import-details-modal'" :maxWidth="'5xl'" wire:model.defer="showDetailsModal">
        @if ($selectedImport)
            <div class="flex flex-col h-full max-h-[80vh]">
                <!-- Modal Header -->
                <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ t('import_details') }} #{{ $selectedImport->id }}
                        </h2>
                        <button wire:click="closeDetailsModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Modal Content - Scrollable -->
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $selectedImport->total_records }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ t('total') }}</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $selectedImport->valid_records }}
                            </div>
                            <div class="text-sm text-green-600 dark:text-green-400">{{ t('valid') }}</div>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ $selectedImport->invalid_records }}
                            </div>
                            <div class="text-sm text-red-600 dark:text-red-400">{{ t('invalid') }}</div>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                {{ $selectedImport->skipped_records }}
                            </div>
                            <div class="text-sm text-yellow-600 dark:text-yellow-400">{{ t('skipped') }}</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if ($selectedImport->total_records > 0)
                        <div class="mb-6">
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <span>{{ t('progress') }}</span>
                                <span>{{ $selectedImport->processed_records }}/{{ $selectedImport->total_records }}
                                    ({{ number_format(($selectedImport->processed_records / $selectedImport->total_records) * 100, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div class="h-3 rounded-full
                                    @if ($selectedImport->status === \App\Models\Tenant\ContactImport::STATUS_COMPLETED) bg-green-600
                                    @elseif($selectedImport->status === \App\Models\Tenant\ContactImport::STATUS_FAILED) bg-red-600
                                    @else bg-blue-600 @endif"
                                    style="width: {{ ($selectedImport->processed_records / $selectedImport->total_records) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Button -->
                    @if ($selectedImport->error_messages && count($selectedImport->error_messages) > 0)
                        <div class="mb-6">
                            <button wire:click="showErrorsModal" wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md hover:bg-red-100 dark:hover:bg-red-900/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                <div wire:loading wire:target="showErrorsModal" class="animate-spin mr-2">
                                    <x-heroicon-o-arrow-path class="w-5 h-5" />
                                </div>
                                <x-heroicon-o-exclamation-triangle wire:loading.remove wire:target="showErrorsModal"
                                    class="w-5 h-5 mr-2" />
                                <span>{{ t('view_errors') }} ({{ count($selectedImport->error_messages) }})</span>
                            </button>
                        </div>
                    @endif

                    <!-- Import Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong class="text-gray-900 dark:text-gray-100">{{ t('file_path') }}:</strong>
                            <div class="flex items-center space-x-2">
                                <div class="text-gray-600 dark:text-gray-400">
                                    {{ basename($selectedImport->file_path) }}
                                </div>
                                @if ($selectedImport->file_path)
                                    <button wire:click="downloadFile({{ $selectedImport->id }})"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded hover:bg-primary-100 dark:hover:bg-primary-900/30">
                                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
                                        {{ t('download') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div>
                            <strong class="text-gray-900 dark:text-gray-100">{{ t('status') }}:</strong>
                            <div class="text-gray-600 dark:text-gray-400">{{ ucfirst($selectedImport->status) }}</div>
                        </div>
                        <div>
                            <strong class="text-gray-900 dark:text-gray-100">{{ t('created_at') }}:</strong>
                            <div class="text-gray-600 dark:text-gray-400">
                                {{ format_date_time($selectedImport->created_at) }}</div>
                        </div>
                        <div>
                            <strong class="text-gray-900 dark:text-gray-100">{{ t('updated_at') }}:</strong>
                            <div class="text-gray-600 dark:text-gray-400">
                                {{ format_date_time($selectedImport->updated_at) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex-shrink-0 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    @if ($selectedImport->status === \App\Models\Tenant\ContactImport::STATUS_FAILED)
                        <button wire:click="retryImport({{ $selectedImport->id }})"
                            onclick="confirm('{{ t('confirm_retry_import') }}') || event.stopImmediatePropagation()"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-yellow-700 dark:text-yellow-300 bg-yellow-100 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-md hover:bg-yellow-200 dark:hover:bg-yellow-900/30">
                            <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                            {{ t('retry_import') }}
                        </button>
                    @endif
                    <button wire:click="closeDetailsModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ t('close') }}
                    </button>
                </div>
            </div>
        @endif
    </x-modal.custom-modal>

    <!-- Error Details Modal with Scrollbar -->
    <x-modal.custom-modal :id="'error-details-modal'" :maxWidth="'5xl'" wire:model.defer="showErrorModal">
        @if ($selectedImport)
            <div class="flex flex-col h-full max-h-[90vh]">
                <!-- Modal Header -->
                <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ t('error_details') }} - {{ t('import') }} #{{ $selectedImport->id }}
                        </h2>
                        <button wire:click="closeErrorModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @if ($errorMessages)
                            {{ count($errorMessages) }} {{ t('errors_found') }}
                        @endif
                    </div>
                </div>

                <!-- Modal Content - Scrollable Error List -->
                <div
                    class="flex-1 overflow-auto scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-200 dark:scrollbar-track-gray-800">
                    <div class="px-6 py-4 min-w-[600px]">
                        @if ($errorMessages)
                            <div class="space-y-3">
                                @foreach ($errorMessages as $index => $error)
                                    <div
                                        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                @if (isset($error['row']))
                                                    <div
                                                        class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">
                                                        {{ t('row') }} {{ $error['row'] }}
                                                    </div>
                                                @endif

                                                <div class="text-sm text-red-700 dark:text-red-300">
                                                    @if (isset($error['errors']))
                                                        @foreach ($error['errors'] as $field => $messages)
                                                            <div class="mb-1">
                                                                <span class="font-medium">{{ ucfirst($field) }}:</span>
                                                                @if (is_array($messages))
                                                                    <ul class="list-disc list-inside ml-2">
                                                                        @foreach ($messages as $message)
                                                                            <li>{{ $message }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    <span>{{ $messages }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @elseif(isset($error['system']))
                                                        <div class="mb-1">
                                                            <span class="font-medium">{{ t('system_error') }}:</span>
                                                            @if (is_array($error['system']))
                                                                <ul class="list-disc list-inside ml-2">
                                                                    @foreach ($error['system'] as $message)
                                                                        <li>{{ $message }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <span>{{ $error['system'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $index + 1 }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex-shrink-0 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @if ($errorMessages)
                            {{ t('showing') }} {{ count($errorMessages) }} {{ t('errors') }}
                        @endif
                    </div>
                    <button wire:click="closeErrorModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ t('close') }}
                    </button>
                </div>
            </div>
        @endif
    </x-modal.custom-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-import-modal'" title="{{ t('delete_import_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_import_message') }}">
        <div class="flex justify-end items-center space-x-3 bg-gray-100 dark:bg-gray-700 px-6 py-3">
            <x-button.cancel-button wire:click="cancelDeletion">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="deleteImport">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>
