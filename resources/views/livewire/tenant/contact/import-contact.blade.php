<div>
    <x-slot:title>
        {{ t('import_contact') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('contacts'), 'route' => tenant_route('tenant.contacts.list')],
        ['label' => t('import_contacts'), 'route' => tenant_route('tenant.contacts.import_log')],
        ['label' => t('import_contact_from_csv_file')],
    ]" />

    <div class="flex flex-col sm:flex-row justify-between items-start lg:items-center gap-4 mb-4">
        <div class="font-display">
            <x-settings-heading>{{ t('import_contact_from_csv_file') }}</x-settings-heading>
        </div>
        <!-- Feature Limit Badge -->
        <div class="mb-2">
            @if (isset($this->isUnlimited) && $this->isUnlimited)
                <x-unlimited-badge>
                    {{ t('unlimited') }}
                </x-unlimited-badge>
            @elseif(isset($this->remainingLimit) && isset($this->totalLimit))
                <x-remaining-limit-badge label="{{ t('remaining') }}" :value="$this->remainingLimit" :count="$this->totalLimit" />
            @endif
        </div>
    </div>

    <!-- Limit reached warning -->
    @if ($hasReachedLimit)
        <div
            class="mb-4 p-3 bg-danger-50 dark:bg-danger-900/20 text-danger-700 dark:text-danger-300 border border-danger-200 dark:border-danger-700 rounded-md">
            <p class="flex items-center text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                {{ t('contact_limit_reached_upgrade_plan') }}
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        <!-- Import Contact Card -->
        <x-card class="rounded-lg xl:col-span-1">
            <x-slot:header>
                <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                    {{ t('import_contact_camel') }}
                </h1>
            </x-slot:header>

            <x-slot:content>
                <!-- File Upload Section -->
                <div class="col-span-3">
                    <div class="flex flex-col 2xl:flex-row 2xl:items-center 2xl:justify-between">
                        <x-label class="mt-[2px]" :value="t('choose_csv_file')" />
                        <p class="text-sm cursor-pointer text-info-500 hover:underline"
                            x-on:click="$dispatch('open-modal', 'example-modal')">
                            {{ t('csv_sample_file_download') }}
                        </p>
                    </div>

                    <div x-data="{
                        fileState: @entangle('csvFile'),
                        isDragging: false,
                        showProgress: false,
                        progress: 0,
                    }" x-on:livewire-upload-start="showProgress = true"
                        x-on:livewire-upload-finish="showProgress = false"
                        x-on:livewire-upload-error="showProgress = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        x-on:livewire-upload-start="progress = 0" x-on:livewire-upload-finish="progress = 100"
                        class="mt-1 w-full relative">
                        <div x-ref="dropZone"
                            class="relative text-gray-400 border-2 border-dashed rounded-lg cursor-pointer transition-all duration-200"
                            :class="{
                                'border-gray-300 dark:border-gray-600': !isDragging,
                                'border-info-500 bg-info-50 dark:border-info-400 dark:bg-info-900/20': isDragging
                            }"
                            @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false">
                            <input type="file" wire:model="csvFile" accept=".csv"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />

                            <div class="flex flex-col items-center justify-center py-10 text-center">
                                <template x-if="!fileState">
                                    <div>
                                        <x-heroicon-o-computer-desktop class="mx-auto h-10 w-10 text-gray-400" />
                                        <p class="mt-2 text-sm text-gray-500">{{ t('drag_and_drop_description') }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ t('csv_file_only') }}</p>
                                    </div>
                                </template>
                                <template x-if="fileState">
                                    <div class="text-center">
                                        <x-heroicon-o-document-text class="mx-auto h-10 w-10 text-info-500" />
                                        <p class="mt-2 text-sm text-gray-900 dark:text-gray-100">
                                            {{ t('file_selected') }}
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div x-show="showProgress" class="relative mt-2" x-cloak>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-info-600 h-2.5 rounded-full transition-all duration-300"
                                    :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>
                    </div>
                    <x-input-error for="csvFile" class="mt-2" />
                </div>

                @if ($csvFile)
                    <!-- Progress Bar -->
                    <div x-data="{
                        processed: @entangle('processedRecords'),
                        total: @entangle('totalRecords')
                    }" x-show="total > 0" class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                            <div :style="`width: ${total > 0 ? (processed/total)*100 : 0}%`"
                                class="bg-primary-600 h-4 rounded-full"></div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                            <span x-text="processed"></span> / <span x-text="total"></span>
                            {{ t('records_processed') }}
                            (<span x-text="total > 0 ? Math.round((processed/total)*100) : 0"></span>%)
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ t('record_successfully_inserted') }}
                            <span class="font-semibold">{{ $validRecords }}</span>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ t('records_with_error') }}
                            <span class="font-semibold">{{ $invalidRecords }}</span>
                        </p>

                        <!-- Add skipped due to limit counter -->
                        @if (isset($skippedDueToLimit) && $skippedDueToLimit > 0)
                            <p class="text-sm text-warning-600 dark:text-warning-400 font-medium">
                                {{ t('records_skipped_due_to_limit') }}
                                <span class="font-semibold">{{ $skippedDueToLimit }}</span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ t('upgrade_plan_message_for_more_contacts') }}
                            </p>
                        @endif
                    </div>
                @endif

                @if (!empty($errorMessages))
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4"> {{ t('import_error') }} </h3>
                        <div class="space-y-4 max-h-96 overflow-y-auto px-1">
                            @foreach ($errorMessages as $error)
                                <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-danger-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                                Row {{ $error['row'] }}
                                            </h3>
                                            <div class="mt-2 text-sm text-danger-700 dark:text-danger-300">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($error['errors'] as $field => $messages)
                                                        @foreach ($messages as $message)
                                                            <li>{{ ucfirst($field) }}: {{ $message }}</li>
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-slot:content>

            <x-slot:footer>
                <div class="flex justify-end space-x-3">
                    <x-button.secondary wire:click="$set('csvFile', null)">
                        {{ t('cancel') }}
                    </x-button.secondary>

                    @if ($processedRecords)
                        <x-button.primary x-on:click="window.location.reload()">
                            <span wire:loading.remove> {{ t('reset') }} </span>
                        </x-button.primary>
                    @else
                        <x-button.primary wire:click="processImport" wire:loading.attr="disabled"
                            @class([
                                'opacity-50 cursor-not-allowed' => $importInProgress || $hasReachedLimit,
                            ]) :disabled="$importInProgress || $hasReachedLimit">
                            <span wire:loading.remove wire:target="processImport">
                                {{ t('upload') }}
                            </span>
                            <span wire:loading wire:target="processImport"
                                class="flex items-center justify-center min-w-12 min-h-2">
                                <x-heroicon-s-arrow-path class="animate-spin w-4 h-4 my-1 ms-3.5" />
                            </span>
                        </x-button.primary>
                    @endif
                </div>
            </x-slot:footer>
        </x-card>

        <!-- Reference Data Cards -->
        <div class="xl:col-span-2 space-y-4">
            <!-- Staff Members Card -->
            <x-card class="rounded-lg mb-6">
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('staff_members') }} ({{ t('assigned_id') }})
                        </h2>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 bg-info-50 dark:bg-info-900/20 px-2 py-1 rounded">
                            {{ t('use_for_assigned_id_column') }}
                        </span>
                    </div>
                </x-slot:header>

                <x-slot:content>
                    @if ($this->staffMembers && $this->staffMembers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ t('id') }}
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ t('name') }}
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ t('email') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-gray-500/10 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($this->staffMembers as $staff)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm font-medium text-info-600 dark:text-info-400">
                                                {{ $staff->id }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $staff->firstname }} {{ $staff->lastname }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $staff->email }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('no_staff_members_found') }}
                            </p>
                        </div>
                    @endif
                </x-slot:content>
            </x-card>

            <!-- Statuses and Sources Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Contact Statuses Card -->
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('contact_statuses') }}
                            </h2>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 bg-success-50 dark:bg-success-900/20 px-2 py-1 rounded">
                                status_id
                            </span>
                        </div>
                    </x-slot:header>

                    <x-slot:content>
                        @if ($this->contactStatuses && $this->contactStatuses->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @foreach ($this->contactStatuses as $status)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors overflow-y-auto">
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white"
                                                style="background-color: {{ $status->color ?? '#6B7280' }};">
                                                {{ $status->id }}
                                            </span>
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                                {{ $status->name }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <x-heroicon-o-tag class="mx-auto h-12 w-12 text-gray-400" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('no_statuses_found') }}
                                </p>
                            </div>
                        @endif
                    </x-slot:content>
                </x-card>

                <!-- Lead Sources Card -->
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('lead_sources') }}
                            </h2>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 bg-purple-50 dark:bg-purple-900/20 px-2 py-1 rounded">
                                source_id
                            </span>
                        </div>
                    </x-slot:header>

                    <x-slot:content>
                        @if ($this->leadSources && $this->leadSources->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @foreach ($this->leadSources as $source)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="flex-shrink-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                                {{ $source->id }}
                                            </span>
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                                {{ $source->name }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <x-heroicon-o-link class="mx-auto h-12 w-12 text-gray-400" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('no_sources_found') }}
                                </p>
                            </div>
                        @endif
                    </x-slot:content>
                </x-card>

                <!-- Contact Group Card -->
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('contact_groups') }}
                            </h2>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 bg-purple-50 dark:bg-purple-900/20 px-2 py-1 rounded">
                                group_id
                            </span>
                        </div>
                    </x-slot:header>

                    <x-slot:content>
                        @if ($this->contactGroups && $this->contactGroups->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @foreach ($this->contactGroups as $group)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="flex-shrink-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                                {{ $group->id }}
                                            </span>
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                                {{ $group->name }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <x-heroicon-o-link class="mx-auto h-12 w-12 text-gray-400" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('no_groups_found') }}
                                </p>
                            </div>
                        @endif
                    </x-slot:content>
                </x-card>
            </div>

        </div>
    </div>

    <!-- Sample File Modal -->
    <x-modal name="example-modal" :show="false" maxWidth="5xl">
        <x-card>
            <x-slot:header>
                <div>
                    <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                        {{ t('download_sample') }}
                    </h1>
                </div>
            </x-slot:header>

            <x-slot:content>
                <div class="mt-3">
                    <x-dynamic-alert type="primary">
                        <span class="font-base font-semibold">{{ t('phone_requirement_column') }}</span>
                        {{ t('phone_req_description') }}
                    </x-dynamic-alert>

                    <x-dynamic-alert type="primary">
                        <span class="font-base font-semibold">{{ t('csv_encoding_format') }}</span>
                        {{ t('csv_encoding_description') }}
                    </x-dynamic-alert>
                </div>

                <div class="flex justify-between my-7 items-center">
                    <p class="text-xl text-slate-700 dark:text-slate-200">{{ t('contact') }}</p>
                    <button wire:click="downloadSample"
                        class="px-4 py-2 bg-gradient-to-r from-success-500 to-success-500 text-white rounded-md cursor-pointer transition duration-150 ease-in-out dark:bg-gradient-to-r dark:from-success-800 dark:to-success-800">
                        {{ t('download_sample') }}
                    </button>
                </div>

                <!-- Sample Table structure -->
                <div class="relative overflow-x-auto border border-3 rounded-sm my-4">
                    <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
                        <thead class="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="border-r px-4 py-2 uppercase"><span
                                        class="text-danger-500 me-1">*</span>{{ t('status_id') }}</th>
                                <th class="border-r px-4 py-2 uppercase"><span
                                        class="text-danger-500 me-1">*</span>{{ t('source_id') }}</th>
                                <th class="border-r px-4 py-2 uppercase">{{ t('assigned_id') }}</th>
                                <th class="border-r px-4 py-2 uppercase"><span
                                        class="text-danger-500 me-1">*</span>{{ t('firstname') }}</th>
                                <th class="border-r px-4 py-2 uppercase"><span
                                        class="text-danger-500 me-1">*</span>{{ t('lastname') }}</th>
                                <th class="border-r px-4 py-2 uppercase">{{ t('company') }}</th>
                                <th class="border-r px-4 py-2 uppercase"><span
                                        class="text-danger-500 me-1">*</span>{{ t('type') }}</th>
                                <th class="border-r px-4 py-2 uppercase">{{ t('email') }}</th>
                                <th class="border-r px-4 py-2 uppercase"><span
                                        class="text-danger-500 me-1">*</span>{{ t('phone') }}</th>
                                <th class="border-r px-4 py-2 uppercase">{{ t('group_id') }}</th>
                                @foreach($customFields as $field)
                                    <th class="border-r px-4 py-2 uppercase">
                                        @if($field->is_required)
                                            <span class="text-danger-500 me-1">*</span>
                                        @endif
                                        {{ $field->field_label }}
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">({{ $field->field_name }})</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="border-r border-t px-4 py-2">{{ t('2') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('4') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('1') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('sample_data') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('sample_data') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('sample_data') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('lead/customer') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('abc_mail') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('phone_sample') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('group_sample') }}</td>
                                @foreach($customFields as $field)
                                    <td class="border-r border-t px-4 py-2">
                                        @switch($field->field_type)
                                            @case('number')
                                                42
                                                @break
                                            @case('date')
                                                {{ date('Y-m-d') }}
                                                @break
                                            @case('checkbox')
                                                Yes/No
                                                @break
                                            @case('dropdown')
                                                {{ $field->field_options[0] ?? 'Option 1' }}
                                                @break
                                            @case('textarea')
                                                Sample text...
                                                @break
                                            @default
                                                Sample value
                                        @endswitch
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button.secondary x-on:click="$dispatch('close-modal', 'example-modal')">
                        {{ t('cancel') }}
                    </x-button.secondary>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
