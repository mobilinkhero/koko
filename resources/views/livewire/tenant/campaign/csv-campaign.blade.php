<div>
    <x-slot:title>
        {{ t('bulk_campaign') }}
    </x-slot:title>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
       <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('campaign_for_csv_file')],
    ]" />
    </div>
    
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-start" x-data="{
            scheduledDate: false,
            campaignsSelected: false,
            campaignsTypeSelected: false,
            isDisabled: false,
            campaignHeader: '',
            campaignBody: '',
            campaignFooter: '',
            fileError: '',
            buttons: [],
            inputType: 'text',
            inputAccept: '',
            headerInputs: @entangle('headerInputs'),
            bodyInputs: @entangle('bodyInputs'),
            footerInputs: @entangle('footerInputs'),
            mergeFields: @entangle('mergeFields'),
            headerInputErrors: [],
            bodyInputErrors: [],
            footerInputErrors: [],
            headerParamsCount: 0,
            bodyParamsCount: 0,
            footerParamsCount: 0,
            relType: '',
            previewUrl: '', // Added for preview
            previewType: '', // Store file type (image, video, document)
            previewFileName: '',
            processingProgress: 0,
            metaExtensions: {{ json_encode(get_meta_allowed_extension()) }},
            isUploading: false,
            progress: 0,
            uploadStarted() {
                this.isUploading = true;
                this.progress = 0;
                $dispatch('upload-started');
            },
            uploadFinished() {
                this.isUploading = false;
                this.progress = 100;
                $dispatch('upload-finished');
            },
            updateProgress(progress) {
                this.progress = progress;
            },
            handleCampaignChange(event) {
                const selectedOption = event.target.selectedOptions[0];
                this.campaignsSelected = event.target.value !== '';
                this.campaignHeader = selectedOption ? selectedOption.dataset.header : '';
                this.campaignBody = selectedOption ? selectedOption.dataset.body : '';
                this.campaignFooter = selectedOption ? selectedOption.dataset.footer : '';
                this.buttons = selectedOption ? JSON.parse(selectedOption.dataset.buttons || '[]') : [];
                this.inputType = selectedOption ? selectedOption.dataset.headerFormat || 'text' : 'text';
                this.headerParamsCount = selectedOption ? parseInt(selectedOption.dataset.headerParamsCount || 0) : 0;
                this.bodyParamsCount = selectedOption ? parseInt(selectedOption.dataset.bodyParamsCount || 0) : 0;
                this.footerParamsCount = selectedOption ? parseInt(selectedOption.dataset.footerParamsCount || 0) : 0;
                this.previewUrl = '';
                this.previewType = '';

                if (selectedOption) {
                    const format = selectedOption.dataset.headerFormat || 'text';
                    this.inputAccept =
                        format == 'IMAGE' ? 'image/*' :
                        format == 'DOCUMENT' ? '.pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx' :
                        format == 'VIDEO' ? 'video/*' : '';
                }

                if (this.metaExtensions[this.inputType.toLowerCase()]) {
                    this.inputAccept = this.metaExtensions[this.inputType.toLowerCase()].extension;
                } else {
                    this.inputAccept = ''; // Default if type not found
                }

                if (selectedOption && selectedOption.value != this.editTemplateId) {
                    this.previewUrl = '';
                    this.previewFileName = '';
                    this.bodyInputs = [];
                    this.footerInputs = [];
                    this.headerInputs = [];
                }
            },
            handleTributeEvent() {
                setTimeout(() => {
                    if (typeof window.Tribute === 'undefined') {
                        return;
                    }
                    let tribute = new window.Tribute({
                        trigger: '@',
                        values: JSON.parse(this.mergeFields),
                    });
                    document.querySelectorAll('.mentionable').forEach((el) => {
                        if (!el.hasAttribute('data-tribute')) {
                            tribute.attach(el);
                            el.setAttribute('data-tribute', 'true'); // Mark as initialized
                        }
                    });
                }, 500);
            },
            initTribute() {
                this.$watch('mergeFields', (newValue) => {
                    this.handleTributeEvent();
                });
                this.handleTributeEvent();
            },
            handleRelTypechange(e) {
                this.campaignsTypeSelected = e.target.value !== '';
            },
            replaceVariables(template, inputs) {
                if (!template || !inputs) return ''; // Prevent undefined error
                return template.replace(/\{\{(\d+)\}\}/g, (match, p1) => {
                    const index = parseInt(p1, 10) - 1;
                    return `<span class='text-primary-600'>${inputs[index] || match}</span>`;
                });
            },
            handleFilePreview(event) {
                const file = event.target.files[0];
                this.fileError = null; // Clear previous errors
                if (!file) return;

                // Get allowed extensions and max size from metaExtensions
                const typeKey = this.inputType.toLowerCase();
                const metaData = this.metaExtensions[typeKey];

                // Validate configuration exists for this file type
                if (!metaData) {
                    this.fileError = 'File upload configuration error. Please try another format.';
                    return;
                }

                const allowedExtensions = metaData.extension.split(',').map(ext => ext.trim());
                const maxSizeMB = metaData.size || 0;
                const maxSizeBytes = maxSizeMB * 1024 * 1024;

                // Handle files with multiple/non-standard extensions
                const fileNameParts = file.name.split('.');
                const fileExtension = fileNameParts.length > 1 ?
                    '.' + fileNameParts.pop().toLowerCase() :
                    '';

                // Validate file extension
                if (!allowedExtensions.includes(fileExtension)) {
                    this.fileError = `Invalid file type. Allowed types: ${allowedExtensions.join(', ')}`;
                    return;
                }

                // Validate MIME type based on category
                const fileType = file.type;
                let isValidMime = true;

                switch (this.inputType) {
                    case 'DOCUMENT':
                        isValidMime = [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'text/plain'
                        ].includes(fileType);
                        break;
                    case 'IMAGE':
                        isValidMime = fileType.startsWith('image/');
                        break;
                    case 'VIDEO':
                        isValidMime = fileType.startsWith('video/');
                        break;
                }
                if (!isValidMime) {
                    this.fileError = `Invalid ${this.inputType.toLowerCase()} file format.`;
                    return;
                }
                // Validate file size only if size limit is specified
                if (maxSizeMB > 0 && file.size > maxSizeBytes) {
                    this.fileError = `File size exceeds ${maxSizeMB} MB (${(file.size/1024/1024).toFixed(2)} MB uploaded).`;
                    return;
                }
                URL.revokeObjectURL(this.previewUrl);

                // Create new preview
                this.previewUrl = URL.createObjectURL(file);
                this.previewFileName = file.name;
            },
            setupProgressEvents() {
                window.addEventListener('importStarted', () => {
                    this.processingProgress = 0;
                });

                window.addEventListener('importProgress', (e) => {
                    this.processingProgress = e.detail.percent;
                });

                window.addEventListener('importComplete', () => {
                    this.processingProgress = 100;
                    setTimeout(() => {
                        this.processingProgress = 0;
                    }, 2000);
                });

                window.addEventListener('importFailed', () => {
                    this.processingProgress = 0;
                });
            },
            validateInputs() {
                const hasTextInputs = this.headerParamsCount > 0 || this.bodyParamsCount > 0 || this.footerInputs.length > 0;
                const hasFileInput = ['IMAGE', 'VIDEO', 'DOCUMENT', 'AUDIO'].includes(this.inputType);

                if (!hasTextInputs && !hasFileInput) return true;

                const invalidPatterns = [
                    /(?<!@)\{[^}]*?\}|\[.*?\]/s, // JSON-like structures (excluding @{name})
                    /('|\')\s*:\s*('|\')/, // Key-value pairs
                    /<script\b[^>]*>(.*?)<\/script>/is, // Inline script injection
                    /<[^>]*>/g, // **NEW: Blocks any HTML tags like <div>, <p>, <input>**
                    /\\\\'/, // Excessive escaping
                    /(\\\u[0-9a-fA-F]{4})/, // Unicode escapes
                ];

                const isInvalidContent = (value) => {
                    if (!value || typeof value !== 'string') return false;

                    // Check for dangerous patterns
                    if (invalidPatterns.some(pattern => pattern.test(value))) return true;

                    // Normalize input for case-insensitive matching
                    const upperValue = value.toUpperCase();

                    // Detect SQL Injection patterns
                    const sqlInjectionPatterns = [
                        /(;|\-\-|\#)/, // Detects comment markers (e.g., -- or # to ignore parts of a query)
                        /\b(SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|EXEC|UNION)\b/i, // SQL Keywords
                    ];

                    if (sqlInjectionPatterns.some(pattern => pattern.test(upperValue))) {
                        return true;
                    }

                    return false;
                };

                const validateInputGroup = (inputs, paramsCount) => {
                    return [...inputs, ...Array(Math.max(0, paramsCount - inputs.length)).fill('')].map(value =>
                        value.trim() === '' ? '{{ t('this_field_is_required') }}' :
                        isInvalidContent(value) ? '{{ t('dynamic_input_error') }}' :
                        ''
                    );
                };

                this.headerInputErrors = validateInputGroup(this.headerInputs, this.headerParamsCount);
                this.bodyInputErrors = validateInputGroup(this.bodyInputs, this.bodyParamsCount);
                this.footerInputErrors = validateInputGroup(this.footerInputs, this.footerInputs.length);

                this.fileError = hasFileInput && !this.previewFileName ? '{{ t('this_field_is_required') }}' : '';

                return [this.headerInputErrors, this.bodyInputErrors, this.footerInputErrors].every(errors => errors.every(error => error === '')) && !this.fileError;
            },
            handleSave() {
                const isValid = this.validateInputs();

                if (!isValid) {
                    return;
                }

                $dispatch('open-loading-modal');
                $wire.save();
            }

        }" x-init="setupProgressEvents()" x-on:livewire-upload-start="uploadStarted()"
            x-on:livewire-upload-finish="uploadFinished()" x-on:livewire-upload-error="isUploading = false"
            x-on:livewire-upload-progress="updateProgress($event.detail.progress)">
            <x-card class="rounded-lg">
                <x-slot:header>
                    <div class="flex items-center">
                        <x-heroicon-o-megaphone class="w-6 h-6 mr-2 text-primary-600 dark:text-primary-400" />
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300 ">
                            {{ t('campaign') }}
                        </h1>
                    </div>
                </x-slot:header>
                <x-slot:content>

                    <div class="col-span-3">
                        <div class="flex items-center justify-start gap-1">
                            <span class="text-danger-500">*</span>
                            <x-label class="mt-[2px]" for="csv_campaign_name" :value="t('campaign_name')" />
                        </div>
                        <x-input wire:model.defer="csv_campaign_name" id="csv_campaign_name" type="text"
                            class="block w-full" autocomplete="off" />
                        <x-input-error for="csv_campaign_name" class="mt-2" />
                    </div>
                    <div class="col-span-3 mt-3">
                        <div class="flex flex-col 2xl:flex-row 2xl:items-center 2xl:justify-between">
                            <x-label class="mt-[2px]" :value="t('choose_csv_file')" class="mt-[2px]" />
                            <p class="text-sm cursor-pointer text-info-500 hover:underline"
                                x-on:click="$dispatch('open-modal', 'csv-campaign-modal')">
                                {{ t('csv_sample_file_download') }}
                            </p>
                        </div>
                        <div x-data="{
                            fileState: @entangle('csvFile'),
                            isDragging: false,
                            csvindicatior: false,
                            csvprogress: 0,
                        }" x-on:livewire-upload-start="csvindicatior = true"
                            x-on:livewire-upload-finish="csvindicatior = false"
                            x-on:livewire-upload-error="csvindicatior = false"
                            x-on:livewire-upload-progress="csvprogress = $event.detail.progress"
                            class="mt-1 w-full relative">
                            <div x-ref="dropZone"
                                class="relative text-gray-400 border-2 border-dashed rounded-lg cursor-pointer transition-all duration-200"
                                :class="{
                                    'border-gray-300 dark:border-gray-600': !isDragging,
                                    'border-info-500 bg-info-50 dark:border-info-400 dark:bg-info-900/20': isDragging
                                }" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="isDragging = false">
                                <input type="file" wire:model="csvFile" accept=".csv"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />

                                <div class="flex flex-col items-center justify-center py-10 text-center">
                                    <template x-if="!fileState">
                                        <div>
                                            <x-heroicon-o-computer-desktop class="mx-auto h-10 w-10 text-gray-400" />
                                            <p class="mt-2 text-sm text-gray-500">{{ t('drag_and_drop_description') }}
                                            </p>
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
                            <div x-show="csvindicatior" class="w-full h-3 bg-gray-200 rounded-md overflow-hidden mt-2"
                                x-cloak>
                                <div class="h-full bg-info-500 transition-all duration-300"
                                    :style="`width: ${csvprogress}%`"></div>
                            </div>

                        </div>

                        <x-input-error for="csvFile" class="mt-2" />
                        <x-input name="json_file_path" id="json_file_path" type="hidden" class="block w-full"
                            value="{{ $json_file_path }}" />

                        <div x-data="{ disabled: @entangle('totalRecords') }"
                            class="dark:bg-transparent rounded-b-lg flex justify-end mt-3">
                            <x-button.primary wire:click.prevent="processImportCsv" wire:loading.attr="disabled"
                                x-bind:disabled="disabled || $wire.importInProgress || processingProgress > 0"
                                @class(['opacity-50 cursor-not-allowed'=> $importInProgress])
                                :disabled="$importInProgress">

                                <span wire:loading.remove wire:target="processImportCsv">
                                    {{ t('upload') }}
                                </span>

                                <span wire:loading wire:target="processImportCsv"
                                    class="flex items-center justify-center min-w-12 min-h-2">
                                    <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 my-1 ms-3.5" />
                                </span>
                            </x-button.primary>
                        </div>

                        @if ($json_file_path)
                        <div class="mt-3">
                            <x-dynamic-alert type="primary">
                                <x-slot:title> {{ t('note') }} </x-slot:title>
                                {{ t('out_of_the') . ' ' . $totalRecords }}
                                {{ t('records_in_your_csv_file') . ' ' . $validRecords }}
                                {{ t('records_are_valid') }}
                                {{ t('campaign_successfully_sent_to_these') . ' ' . $validRecords }}
                                {{ t('user') }}
                            </x-dynamic-alert>
                        </div>
                        @endif
                    </div>

                    {{-- template_name --}}
                    @if ($json_file_path)
                    <div class="mt-4" x-data x-init="window.initTomSelect('#basic-select')">
                        <div class="flex items-center justify-start">
                            <span class="text-danger-500 me-1 ">*</span>
                            <x-label for="template_name" :value="t('template')" />
                        </div>
                        <div wire:ignore>
                            <x-select id="basic-select" class="mt-1 block w-full" x-ref="campaignsChange"
                                x-on:change="handleCampaignChange({ target: $refs.campaignsChange })"
                                x-init="handleCampaignChange({ target: $refs.campaignsChange })"
                                wire:model="template_name" wire:change="$set('template_name', $event.target.value)">
                                <option value="" selected>{{ t('nothing_selected') }}</option>
                                @foreach ($templates as $template)
                                <option value="{{ $template['template_id'] }}"
                                    data-header="{{ $template['header_data_text'] }}"
                                    data-body="{{ $template['body_data'] }}"
                                    data-footer="{{ $template['footer_data'] }}"
                                    data-buttons="{{ $template['buttons_data'] }}"
                                    data-header-format="{{ $template['header_data_format'] }}"
                                    data-header-params-count="{{ $template['header_params_count'] }}"
                                    data-body-params-count="{{ $template['body_params_count'] }}"
                                    data-footer-params-count="{{ $template['footer_params_count'] }}">
                                    {{ $template['template_name'] . ' (' . $template['language'] . ')' }}
                                </option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-input-error for="template_name" class="mt-2" />
                    </div>
                    @endif
                </x-slot:content>
            </x-card>

            {{-- Variables --}}
            <x-card class="rounded-lg shadow-sm" x-show="campaignsSelected" x-cloak>
                <x-slot:header>
                    <div class="flex items-center">
                        <x-heroicon-o-variable class="w-6 h-6 mr-2 text-primary-600 dark:text-primary-400" />
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300 ">
                            {{ t('variables') }}
                        </h1>
                    </div>
                </x-slot:header>

                <x-slot:content>
                    <!-- Alert for missing variables -->
                    <div x-show="((inputType == 'TEXT' || inputType == '') && headerParamsCount === 0) && bodyParamsCount === 0 && footerParamsCount === 0"
                        class="bg-warning-50 border-l-4 border-warning-500 text-warning-800 px-4 py-3 rounded-md dark:bg-slate-800/60 dark:border-warning-600 dark:text-warning-300"
                        role="alert">
                        <div class="flex items-center">
                            <x-heroicon-s-exclamation-triangle
                                class="w-5 h-5 mr-2 text-warning-500 dark:text-warning-400" />
                            <p class="text-sm font-medium">
                                {{ t('variable_not_available_for_this_template') }}
                            </p>
                        </div>
                    </div>

                    <!-- Header / Media Section -->
                    <div x-show="inputType !== 'TEXT' || headerParamsCount > 0">
                        <div class="flex items-center mb-3">
                            <!-- Icon based on type -->
                            <template x-if="inputType == 'TEXT'">
                                <x-heroicon-o-document-text
                                    class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                            </template>
                            <template x-if="inputType == 'IMAGE'">
                                <x-heroicon-o-photo class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                            </template>
                            <template x-if="inputType == 'DOCUMENT'">
                                <x-heroicon-o-document class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                            </template>
                            <template x-if="inputType == 'VIDEO'">
                                <x-heroicon-o-film class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                            </template>

                            <!-- Type Label -->
                            <h3 class="font-semibold text-slate-700 dark:text-slate-300">
                                <template x-if="inputType == 'TEXT' && headerParamsCount > 0">
                                    <span>{{ t('header') }}</span>
                                </template>
                                <template x-if="inputType == 'IMAGE'">
                                    <span>{{ t('image') }}</span>
                                </template>
                                <template x-if="inputType == 'DOCUMENT'">
                                    <span>{{ t('document') }}</span>
                                </template>
                                <template x-if="inputType == 'VIDEO'">
                                    <span>{{ t('video') }}</span>
                                </template>
                            </h3>
                        </div>

                        <!-- TEXT type inputs -->
                        <div x-show="inputType == 'TEXT'" class="space-y-4">
                            <template x-for="(value, index) in headerParamsCount" :key="index">
                                <x-card>
                                    <x-slot:content>
                                        <div class="flex justify-start gap-1 mb-1">
                                            <span class="text-danger-500">*</span>
                                            <label :for="'header_name_' + index"
                                                class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                                {{ t('variable') }} <span x-text="index + 1"></span>
                                            </label>
                                        </div>
                                        <input x-bind:type="inputType" :id="'header_name_' + index"
                                            x-model="headerInputs[index]" x-init='initTribute()'
                                            class="mentionable block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            autocomplete="off" />
                                        <p x-show="headerInputErrors[index]" x-text="headerInputErrors[index]"
                                            class="text-danger-500 text-sm mt-1"></p>
                                    </x-slot:content>
                                </x-card>
                            </template>
                            @if ($errors->has('headerInputs.*'))
                            <x-dynamic-alert type="danger" :message="$errors->first('headerInputs.*')" class="mt-2">
                            </x-dynamic-alert>
                            @endif
                        </div>

                        <!-- Document Upload -->
                        <div x-show="inputType == 'DOCUMENT'" class="mt-2">
                            <div
                                class="bg-white dark:bg-slate-800 p-4 rounded-md border border-slate-200 dark:border-slate-700">
                                <div class="flex justify-between items-center mb-2">
                                    <label for="document_upload"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ t('select_document') }}
                                    </label>
                                    <span class="text-xs text-slate-500 dark:text-slate-400"
                                        x-text="metaExtensions.document ? metaExtensions.document.extension : ''"></span>
                                </div>

                                <div class="relative mt-1 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-500 rounded-lg transition-colors duration-200"
                                    x-on:click="$refs.documentUpload.click()">

                                    <!-- Content when no file is selected -->
                                    <div x-show="!previewFileName" class="p-6 text-center">
                                        <x-heroicon-o-document class="h-12 w-12 text-slate-400 mx-auto" />
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                                            {{ t('select_or_browse_to') }}
                                            <span class="text-primary-600 dark:text-primary-400 font-medium">{{
                                                t('document') }}</span>
                                        </p>
                                    </div>

                                    <!-- Content when file is selected -->
                                    <div x-show="previewFileName" class="p-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <x-heroicon-o-document-text class="h-8 w-8 text-primary-500" />
                                            <span
                                                class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate max-w-xs"
                                                x-text="previewFileName"></span>
                                        </div>
                                        <button type="button"
                                            class="mt-2 inline-flex items-center px-2 py-1 text-xs font-medium text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300"
                                            x-on:click.stop="previewUrl = ''; previewFileName = '';">
                                            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                            {{ t('remove') }}
                                        </button>
                                    </div>

                                    <input type="file" x-ref="documentUpload" id="document_upload"
                                        x-bind:accept="inputAccept" wire:model.defer="file"
                                        x-on:change="handleFilePreview($event)" class="hidden" />
                                </div>

                                <x-input-error for="file" class="mt-2" />
                                <p x-show="fileError" class="text-danger-500 text-sm mt-2" x-text="fileError"></p>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div x-show="inputType == 'IMAGE'" class="mt-2">
                            <div
                                class="bg-white dark:bg-slate-800 p-4 rounded-md border border-slate-200 dark:border-slate-700">
                                <div class="flex justify-between items-center mb-2">
                                    <label for="image_upload"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ t('select_image') }}
                                    </label>
                                    <span class="text-xs text-slate-500 dark:text-slate-400"
                                        x-text="metaExtensions.image ? metaExtensions.image.extension : ''"></span>
                                </div>

                                <div class="relative mt-1 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-500 rounded-lg transition-colors duration-200"
                                    x-on:click="$refs.imageUpload.click()">

                                    <!-- No Image Selected -->
                                    <div x-show="!previewFileName" class="p-6 text-center">
                                        <x-heroicon-o-photo class="h-12 w-12 text-slate-400 mx-auto" />
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                                            {{ t('select_or_browse_to') }}
                                            <span class="text-primary-600 dark:text-primary-400 font-medium">{{
                                                t('image') }}</span>
                                        </p>
                                    </div>

                                    <!-- Image Preview -->
                                    <div x-show="previewFileName" class="p-4 text-center">
                                        <img :src="previewUrl" class="mx-auto max-h-32 rounded shadow-sm" />
                                        <button type="button"
                                            class="mt-2 inline-flex items-center px-2 py-1 text-xs font-medium text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300"
                                            x-on:click.stop="previewUrl = ''; previewFileName = '';">
                                            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                            {{ t('remove') }}
                                        </button>
                                    </div>

                                    <input type="file" id="image_upload" x-ref="imageUpload" x-bind:accept="inputAccept"
                                        wire:model.defer="file" x-on:change="handleFilePreview($event)"
                                        class="hidden" />
                                </div>

                                <x-input-error for="file" class="mt-2" />
                                <p x-show="fileError" class="text-danger-500 text-sm mt-2" x-text="fileError"></p>
                            </div>
                        </div>

                        <!-- Video Upload -->
                        <div x-show="inputType == 'VIDEO'" class="mt-2">
                            <div
                                class="bg-white dark:bg-slate-800 p-4 rounded-md border border-slate-200 dark:border-slate-700">
                                <div class="flex justify-between items-center mb-2">
                                    <label for="video_upload"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ t('select_video') }}
                                    </label>
                                    <span class="text-xs text-slate-500 dark:text-slate-400"
                                        x-text="metaExtensions.video ? metaExtensions.video.extension : ''"></span>
                                </div>

                                <div class="relative mt-1 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-500 rounded-lg transition-colors duration-200"
                                    x-on:click="$refs.videoUpload.click()">

                                    <!-- No Video Selected -->
                                    <div x-show="!previewFileName" class="p-6 text-center">
                                        <x-heroicon-o-film class="h-12 w-12 text-slate-400 mx-auto" />
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                                            {{ t('select_or_browse_to') }}
                                            <span class="text-primary-600 dark:text-primary-400 font-medium">{{
                                                t('video') }}</span>
                                        </p>
                                    </div>

                                    <!-- Video Preview -->
                                    <div x-show="previewFileName" class="p-4 text-center">
                                        <video :src="previewUrl" controls
                                            class="mx-auto max-h-32 rounded shadow-sm"></video>
                                        <button type="button"
                                            class="mt-2 inline-flex items-center px-2 py-1 text-xs font-medium text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300"
                                            x-on:click.stop="previewUrl = ''; previewFileName = '';">
                                            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                            {{ t('remove') }}
                                        </button>
                                    </div>

                                    <input type="file" id="video_upload" x-ref="videoUpload" x-bind:accept="inputAccept"
                                        wire:model.defer="file" x-on:change="handleFilePreview($event)"
                                        class="hidden" />
                                </div>

                                <x-input-error for="file" class="mt-2" />
                                <p x-show="fileError" class="text-danger-500 text-sm mt-2" x-text="fileError"></p>
                            </div>
                        </div>

                        <!-- Upload Progress Bar -->
                        <div x-show="isUploading" class="mt-4">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ t('uploading')
                                    }}...</span>
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400"
                                    x-text="progress + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 dark:bg-slate-700">
                                <div class="bg-primary-600 h-2 rounded-full transition-all duration-300 dark:bg-primary-500"
                                    :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Body Section -->
                    <div x-show="bodyParamsCount > 0" class="mt-4 border-slate-200 dark:border-slate-700">
                        <div class="flex items-center mb-3">
                            <x-heroicon-o-document-text class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                            <h3 class="font-semibold text-slate-700 dark:text-slate-300">{{ t('body') }}</h3>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(value, index) in bodyParamsCount" :key="index">
                                <div
                                    class="bg-white dark:bg-slate-800 p-3 rounded-md border border-slate-200 dark:border-slate-700">
                                    <div class="flex justify-start gap-1 mb-1">
                                        <span class="text-danger-500">*</span>
                                        <label :for="'body_name_' + index"
                                            class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ t('variable') }} <span x-text="index + 1"></span>
                                        </label>
                                    </div>
                                    <input type="text" :id="'body_name_' + index" x-model="bodyInputs[index]"
                                        x-init='initTribute()'
                                        class="mentionable block w-full border-slate-300 rounded-md shadow-sm text-slate-900 focus:ring-primary-500 focus:border-primary-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        autocomplete="off" />
                                    <p x-show="bodyInputErrors[index]" x-text="bodyInputErrors[index]"
                                        class="text-danger-500 text-sm mt-1">
                                    </p>
                                </div>
                            </template>

                            @if ($errors->has('bodyInputs.*'))
                            <x-dynamic-alert type="danger" :message="$errors->first('bodyInputs.*')" class="mt-2">
                            </x-dynamic-alert>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div x-show="footerParamsCount > 0"
                        class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex items-center mb-3">
                            <x-heroicon-o-bars-arrow-down class="w-6 h-6 mr-2 text-primary-600 dark:text-primary-400" />
                            <h3 class="font-semibold text-slate-700 dark:text-slate-300">{{ t('footer') }}</h3>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(value, index) in footerInputs" :key="index">
                                <div
                                    class="bg-white dark:bg-slate-800 p-3 rounded-md border border-slate-200 dark:border-slate-700">
                                    <div class="flex justify-start gap-1 mb-1">
                                        <span class="text-danger-500">*</span>
                                        <label :for="'footer_name_' + index"
                                            class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ t('variable') }} <span x-text="index"></span>
                                        </label>
                                    </div>
                                    <input type="text" :id="'footer_name_' + index" x-model="footerInputs[index]"
                                        class="mentionable block w-full border-slate-300 rounded-md shadow-sm text-slate-900 focus:ring-primary-500 focus:border-primary-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        autocomplete="off" />
                                    <p x-show="footerInputErrors[index]" x-text="footerInputErrors[index]"
                                        class="text-danger-500 text-sm mt-1"></p>
                                </div>
                            </template>

                            @if ($errors->has('footerInputs.*'))
                            <x-dynamic-alert type="danger" :message="$errors->first('footerInputs.*')" class="mt-2">
                            </x-dynamic-alert>
                            @endif
                        </div>
                    </div>
                </x-slot:content>
            </x-card>

            <div x-show="campaignsSelected" x-cloak>
                {{-- Preview --}}
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <div class="flex items-center">
                            <x-carbon-view class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                            <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('preview') }}
                            </h1>
                        </div>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="w-full p-6 border border-gray-200 rounded shadow-sm dark:border-gray-700 chat-conversation-box"
                            style="background-image: url('{{ asset('img/chat/whatsapp_light_bg.png') }}');">

                            <!-- File Preview Section -->
                            <div class="mb-1" x-show="previewUrl">
                                <!-- Image Preview -->
                                <a x-show="inputType === 'IMAGE'" :href="previewUrl" class="glightbox"
                                    x-effect="if (previewUrl) { setTimeout(() => initGLightbox(), 100); }">
                                    <img x-show="inputType === 'IMAGE'" :src="previewUrl"
                                        class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800 cursor-pointer" />
                                </a>
                                <!-- Video Preview -->
                                <video x-show="inputType === 'VIDEO'" :src="previewUrl" controls
                                    class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800"></video>

                                <!-- Document Preview -->
                                <div x-show="inputType === 'DOCUMENT'"
                                    class="p-4 border border-gray-300 bg-white dark:bg-gray-800 rounded-lg">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('document_uploaded') }}
                                        <a :href="previewUrl" target="_blank" class="text-info-500 underline"
                                            x-text="previewFileName"></a>
                                    </p>
                                </div>
                            </div>
                            <div class="p-6 bg-white rounded-lg dark:bg-gray-800 dark:text-white">
                                <p class="mb-3 font-semibold text-gray-800 break-all dark:text-gray-400"
                                    x-html="replaceVariables(campaignHeader, headerInputs)"></p>
                                <p class="mb-3 font-normal text-gray-500 break-all dark:text-gray-400"
                                    x-html="replaceVariables(campaignBody, bodyInputs)"></p>
                                <div class="mt-4">
                                    <p class="font-normal text-xs break-all text-gray-500 dark:text-gray-400"
                                        x-html="campaignFooter">
                                    </p>
                                </div>
                            </div>
                            <template x-if="buttons && buttons.length > 0"
                                class="bg-white rounded-lg py-2 dark:bg-gray-800 dark:text-white">
                                <div class="space-y-1">
                                    <template x-for="(button, index) in buttons" :key="index">
                                        <div
                                            class="w-full px-4 py-2 bg-white text-gray-900 rounded-md dark:bg-gray-700 dark:text-white">
                                            <span x-text="button.text" class="text-sm block text-center"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </x-slot:content>
                </x-card>
                {{-- Send campaigns --}}
                <x-card class="rounded-lg mt-8">
                    <x-slot:header>
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('send_campaign') }}
                        </h1>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="flex flex-wrap sm:flex-nowrap items-center justify-center sm:justify-between gap-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 text-center sm:text-left">
                                {{ t('sending_to') }} <span class="font-semibold">{{ $validRecords }}</span>
                                {{ t('recipients') }}
                            </div>
                            <div class="w-full sm:w-auto flex justify-center sm:justify-end">
                                <x-button.loading-button type="button" target="save" x-on:click="handleSave()"
                                    wire:loading.attr="disabled" x-bind:disabled="isUploading"
                                    x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }">
                                    {{ t('send_campaign') }}
                                </x-button.loading-button>
                            </div>
                        </div>
                    </x-slot:content>
                </x-card>

            </div>
        </div>
    </form>

    {{-- Download campaign sample file modal : Start --}}
    <x-modal name="csv-campaign-modal" :show="false" maxWidth="5xl">
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
                    <p class="text-xl text-slate-700 dark:text-slate-200">{{ t('campaign') }}</p>
                    <p wire:click="sampledownload"
                        class="px-4 py-2 bg-gradient-to-r from-success-500  to-success-500 text-white rounded-md cursor-pointer transition duration-150 ease-in-out dark:bg-gradient-to-r dark:from-success-800  dark:to-success-800">
                        {{ t('download_sample') }}
                    </p>
                </div>

                <div class="relative overflow-x-auto border border-3 rounded-sm my-4">
                    <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="border-r px-4 py-2"><span class="text-danger-500 me-1">*</span>{{
                                    t('firstname')
                                    }}</th>
                                <th class="border-r px-4 py-2"><span class="text-danger-500 me-1">*</span>{{
                                    t('lastname')
                                    }}</th>
                                <th class="border-r px-4 py-2">
                                    <span class="text-danger-500 me-1">*</span>{{ t('phone') }}
                                </th>
                                <th class="border-r px-4 py-2">{{ t('email') }}</th>
                                <th class="px-4 py-2">{{ t('country') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="border-r border-t px-4 py-2">{{ t('sample_data') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('sample_data') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('phone_sample') }}</td>
                                <td class="border-r border-t px-4 py-2">{{ t('abc@gmail.com') }}</td>
                                <td class="px-4 border-t py-2">{{ t('sample_data') }}</td>
                            </tr>
                            <tr class="bg-gray-50 border-b dark:bg-gray-900 dark:border-gray-700">
                                <!-- Additional rows as needed -->
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-slot:content>
            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button.secondary x-on:click="$dispatch('close-modal', 'csv-campaign-modal')">
                        {{ t('cancel') }}
                    </x-button.secondary>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    {{-- Download campaign sample file modal : Over --}}

    <!-- Loading Modal -->
    <div x-data="{ isOpen: false }">
        <div x-on:open-loading-modal.window="isOpen = true" x-on:close-loading-modal.window="isOpen = false"
            x-show="isOpen" class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            style="display: none;">

            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-11/12 sm:w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg text-center">
                <!-- Loading Spinner -->
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-gray-300 dark:border-gray-600 border-t-primary-500 dark:border-t-primary-400 rounded-full animate-spin mx-auto">
                </div>

                <!-- Message -->
                <p class="mt-4 text-base sm:text-lg font-medium text-gray-700 dark:text-gray-200">
                    {{ t('sending_campaign') }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('this_may_take_a_few_moments') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Error message toast -->
    <div x-data="{ errorMessage: '', showError: false }"
        x-on:campaign-error.window="errorMessage = $event.detail.message; showError = true; setTimeout(() => showError = false, 5000)"
        x-show="showError" x-transition.opacity
        class="fixed bottom-4 right-4 bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 rounded shadow-lg"
        style="display: none; max-width: 400px; z-index: 9999;">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-danger-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm" x-text="errorMessage"></p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button x-on:click="showError = false"
                        class="inline-flex rounded-md p-1.5 text-danger-500 hover:bg-danger-200 focus:outline-none focus:ring-2 focus:ring-danger-600 focus:ring-offset-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>