<div>
    <x-slot:title>
        {{ empty($this->template_bot->id) ? t('create_template_bot') : t('edit_template_bot') }}
    </x-slot:title>
      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('template_bot'), 'route' => tenant_route('tenant.templatebot.list')],
        ['label' => empty($this->template_bot->id) ? t('create_template_bot') : t('edit_template_bot')  ]
    ]" />

    <!-- Feature Limit Warning  -->
    @if (!$template_bot->exists && isset($this->hasReachedLimit) && $this->hasReachedLimit)
    <div class="pb-3">
        <div class="rounded-md bg-warning-50 dark:bg-warning-900/30 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-warning-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                        {{ t('template_bot_limit_reached') }}</h3>
                    <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                        <p>{{ t('template_bot_limit_reached_message') }} <a
                                href="{{ tenant_route('tenant.subscription') }}" class="font-medium underline">{{
                                t('upgrade_plan') }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div x-data="{
        templateSelected: false,
        fileError: null,
        templateHeader: '',
        templateBody: '',
        headerInputErrors: [],
        bodyInputErrors: [],
        footerInputErrors: [],
        templateFooter: '',
        buttons: [],
        previewUrl: '{{ !empty($filename) ? asset('storage/' . $filename) : '' }}',
        previewType: '',
        previewFileName: '{{ !empty($filename) ? basename($filename) : '' }}',
        inputType: 'text',
        inputAccept: '',
        headerInputs: @entangle('headerInputs'),
        bodyInputs: @entangle('bodyInputs'),
        footerInputs: @entangle('footerInputs'),
        mergeFields: @entangle('mergeFields'),
        editTemplateId: @entangle('template_id'),
        headerParamsCount: 0,
        bodyParamsCount: 0,
        footerParamsCount: 0,
        metaExtensions: {{ json_encode(get_meta_allowed_extension()) }},
        tags: @entangle('trigger'),
        newTag: '',
        errorMessage: '',

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
        purifyInput(input) {
            let tempDiv = document.createElement('div');
            tempDiv.textContent = input; // Remove potential HTML tags
            return tempDiv.innerHTML.trim();
        },
        initTriggers() {
            if (!Array.isArray(this.tags)) {
                this.tags = [];
            }
        },
        addTag() {
            let tag = this.purifyInput(this.newTag);
            // Prevent empty input
            if (!tag) return;

            // Patterns for SQL & JSON injection
            let upper = tag.toUpperCase();
            let sqlKeywords = ['SELECT', 'INSERT', 'DELETE', 'DROP', 'UNION', 'WHERE', 'HAVING'];
            let injectionPattern = /(\{.*\}|\[.*\])|[\<\>\&\'\\\;]/;

            // Check for SQL injection, JSON injection, or unsafe characters
            if (sqlKeywords.some(k => upper.includes(k)) || injectionPattern.test(tag)) {
                this.errorMessage = '{{ t('sql_injection_error') }}';
                return;
            }

            // Prevent duplicate entries
            if (this.tags.includes(tag)) {
                this.errorMessage = '{{ t('this_trigger_already_exists') }}';
                return;
            }

            // Add the valid tag
            this.tags.push(tag);
            this.errorMessage = '';
            this.newTag = '';
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

        handleTemplateChange(event) {
            const selectedOption = event.target.selectedOptions[0];
            this.templateSelected = event.target.value !== '';
            this.templateHeader = selectedOption ? selectedOption.dataset.header : '';
            this.templateBody = selectedOption ? selectedOption.dataset.body : '';
            this.templateFooter = selectedOption ? selectedOption.dataset.footer : '';
            this.buttons = selectedOption ? JSON.parse(selectedOption.dataset.buttons || '[]') : [];
            this.inputType = selectedOption ? selectedOption.dataset.headerFormat || 'text' : 'text';
            this.headerParamsCount = selectedOption ? parseInt(selectedOption.dataset.headerParamsCount || 0) : 0;
            this.bodyParamsCount = selectedOption ? parseInt(selectedOption.dataset.bodyParamsCount || 0) : 0;
            this.footerParamsCount = selectedOption ? parseInt(selectedOption.dataset.footerParamsCount || 0) : 0;
            // Don't clear previewUrl if we're in edit mode and have an existing file
            if (!selectedOption || (selectedOption && !this.previewUrl.includes('{{ $filename }}'))) {
                this.previewUrl = '';
                this.previewFileName = '';
            }
            if (selectedOption) {
                const format = selectedOption.dataset.headerFormat || 'text';
                this.inputAccept =
                    format == 'IMAGE' ? 'image/*' :
                    format == 'DOCUMENT' ? '.pdf,.doc,.docx,.txt' :
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
        replaceVariables(template, inputs) {
            if (!template || !inputs) return ''; // Prevent undefined error
            return template.replace(/\{\{(\d+)\}\}/g, (match, p1) => {
                const index = parseInt(p1, 10) - 1; // Convert to zero-based index
                const value = inputs[index] || match; // Use existing value or keep the placeholder
                return `<span class='text-primary-600 dark:text-primary-400'>${value}</span>`;
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
                case 'AUDIO':
                    isValidMime = fileType.startsWith('audio/');
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
        validateInputs() {
            const hasTextInputs = this.headerParamsCount > 0 || this.bodyParamsCount > 0 || this.footerInputs.length > 0;
            const hasFileInput = ['IMAGE', 'VIDEO', 'DOCUMENT', 'AUDIO'].includes(this.inputType);

            if (!hasTextInputs && !hasFileInput) {
                return true;
            }
            const validateInputGroup = (inputs, paramsCount) => {
                // Ensure inputs is a properly unwrapped array
                const unwrappedInputs = inputs ? JSON.parse(JSON.stringify(inputs)) : [];

                // Ensure length matches paramsCount by filling missing values with empty strings
                while (unwrappedInputs.length < paramsCount) {
                    unwrappedInputs.push('');
                }

                // Return errors if inputs are empty
                return unwrappedInputs.map(value =>
                    value.trim() === '' ? '{{ t('this_field_is_required') }}' : ''
                );
            };

            // Validate text inputs
            this.headerInputErrors = validateInputGroup(this.headerInputs, this.headerParamsCount);
            this.bodyInputErrors = validateInputGroup(this.bodyInputs, this.bodyParamsCount);
            this.footerInputErrors = validateInputGroup(this.footerInputs, this.footerInputs.length);

            if (hasFileInput && !this.previewFileName) {
                this.fileError = '{{ t('this_field_is_required') }}';
            } else {
                this.fileError = ''; // Reset file error if not needed
            }

            // Check if all inputs are valid
            const isTextValid = [this.headerInputErrors, this.bodyInputErrors, this.footerInputErrors]
                .every(errors => errors.length === 0 || errors.every(error => error === ''));

            const isFileValid = !this.fileError; // No error means file validation passed

            return isTextValid && isFileValid;
        },
        handleSave() {
            const isValid = this.validateInputs();

            if (!isValid) return; // Stop if validation fails

            $wire.save();
        }
    }" x-on:livewire-upload-start="uploadStarted()" x-on:livewire-upload-finish="uploadFinished()"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="updateProgress($event.detail.progress)">

        <!-- Main Form with 3-column grid layout -->
        <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-start">
            <!-- Template Bot Configuration Card -->
            <x-card class="rounded-lg shadow-sm">
                <x-slot:header>
                    <div class="flex items-center">
                        <x-heroicon-o-chat-bubble-left-right
                            class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('template_bot') }}
                        </h1>
                    </div>
                </x-slot:header>

                <x-slot:content>
                    <!-- Bot Name -->
                    <div>
                        <div class="flex items-center justify-start mb-1">
                            <span class="text-danger-500 mr-1">*</span>
                            <x-label for="name" :value="t('bot_name')" />
                        </div>
                        <x-input wire:model.defer="template_name" type="text" id="name" class="mt-1 block w-full"
                            autocomplete="off" placeholder="{{ t('enter_bot_name') }}" />
                        <x-input-error for="template_name" class="mt-2" />
                    </div>

                    <!-- Relation Type -->
                    <div class="mt-4">
                        <div class="flex items-center justify-start mb-1">
                            <span class="text-danger-500 mr-1">*</span>
                            <x-label for="rel_type" :value="t('relation_type')" />
                        </div>
                        <div wire:ignore>
                            <x-select id="rel_type" class="mt-1 block w-full tom-select-two"
                                x-on:change="handleTributeEvent()" wire:model="rel_type"
                                wire:change="$set('rel_type', $event.target.value)">
                                <option value="">{{ t('nothing_selected') }}</option>
                                @foreach (\App\Enum\Tenant\WhatsAppTemplateRelationType::getRelationtype() as $key =>
                                $relationType)
                                <option value="{{ $key }}">{{ ucfirst($relationType) }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-input-error for="rel_type" class="mt-2" />
                    </div>

                    <!-- Template -->
                    <div class="mt-4">
                        <div class="flex items-center justify-start mb-1">
                            <span class="text-danger-500 mr-1">*</span>
                            <x-label for="template_id" :value="t('template')" />
                        </div>
                        <div wire:ignore>
                            <x-select id="template_id" class="tom-select mt-1 block w-full"
                                wire:model.defer="template_id" x-ref="templateSelect"
                                x-on:change="handleTemplateChange($event)"
                                x-init="handleTemplateChange({ target: $refs.templateSelect })">
                                <option value="" selected>{{ t('nothing_selected') }}</option>
                                @foreach ($this->templates as $template)
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
                        <x-input-error for="template_id" class="mt-2" />
                    </div>

                    <!-- Reply Type -->
                    <div x-data="{ selectedOption: @entangle('reply_type') }" class="mt-4">
                        <div class="flex items-center justify-start mb-1">
                            <span class="text-danger-500 mr-1">*</span>
                            <x-label for="reply_type" :value="t('reply_type')" />
                        </div>

                        <div wire:ignore>
                            <x-select x-ref="select" x-model="selectedOption"
                                x-init="selectedOption = $refs.select.value" id="reply_type"
                                class="mt-1 block w-full subtext-select">
                                @foreach (\App\Enum\Tenant\WhatsAppTemplateRelationType::getReplyType() as $key =>
                                $replyType)
                                <option value="{{ $key }}" data-subtext="{{ $replyType['subtext'] }}" {{
                                    $reply_type==$key ? 'selected' : '' }}>
                                    {{ $replyType['label'] }}
                                </option>
                                @endforeach
                            </x-select>
                        </div>

                        <x-input-error for="reply_type" class="mt-2" />

                        <!-- Trigger Keywords Section -->
                        <div x-show="selectedOption == 1 || selectedOption == 2" x-transition
                            class="mt-4 dark:bg-slate-700/30 rounded-md">
                            <div class="flex items-center justify-start gap-1 ">
                                <span class="text-danger-500">*</span>
                                <x-label class="font-medium" for="trigger" :value="t('trigger_keyword')" />
                            </div>

                            <div x-init="initTriggers()">
                                <div class="flex gap-2">
                                    <x-input type="text" x-model="newTag" x-on:keydown.enter.prevent="addTag()"
                                        x-on:blur="addTag()" class="block w-full"
                                        placeholder="{{ t('type_and_press_enter') }}" autocomplete="off" />
                                </div>

                                <!-- Tag List -->
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="(tag, index) in tags" :key="index">
                                        <div
                                            class="bg-primary-500 dark:bg-primary-800 text-white mb-2 dark:text-gray-100 rounded-xl px-2 py-1 text-sm mr-2 inline-flex items-center">
                                            <span x-text="tag" class="mr-1"></span>
                                            <button x-on:click="tags.splice(index, 1)" type="button"
                                                class="ml-2 text-white dark:text-gray-100">
                                                <x-heroicon-o-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <p x-show="errorMessage" class="text-danger-500 text-sm mt-2" x-text="errorMessage"></p>
                            </div>

                            @if ($errors->has('trigger.*'))
                            <x-input-error for="trigger.*" />
                            @else
                            <x-input-error for="trigger" class="mt-2" />
                            @endif
                        </div>

                        <!-- Webhook Note -->
                        <div x-show="selectedOption==4" x-transition>
                            <x-dynamic-alert type="warning" class="w-full mt-3">
                                <p class="text-sm">
                                    <span class="font-medium">{{ t('note') }}</span>
                                    {{ t('increase_webhook_note') }}
                                    <a href="https://docs.corbitaltech.dev/products/whatsmark-saas/" target="blank"
                                        class="underline text-primary-600 dark:text-primary-400">
                                        {{ t('link') }}
                                    </a>
                                </p>
                            </x-dynamic-alert>
                        </div>
                    </div>
                </x-slot:content>
            </x-card>

            <!-- Variables Card -->
            <x-card class="rounded-lg shadow-sm " x-show="templateSelected" x-cloak>
                <x-slot:header>
                    <div class="flex items-center">
                        <x-heroicon-o-variable class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
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
                                <div
                                    class="bg-white dark:bg-slate-800 p-3 rounded-md border border-slate-200 dark:border-slate-700">
                                    <div class="flex justify-start gap-1 mb-1">
                                        <span class="text-danger-500">*</span>
                                        <label :for="'header_name_' + index"
                                            class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ t('variable') }} <span x-text="index + 1"></span>
                                        </label>
                                    </div>
                                    <input x-bind:type="inputType" :id="'header_name_' + index"
                                        x-model="headerInputs[index]" x-init='handleTributeEvent()'
                                        class="mentionable block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        autocomplete="off" />
                                    <p x-show="headerInputErrors[index]" x-text="headerInputErrors[index]"
                                        class="text-danger-500 text-sm mt-1"></p>
                                </div>
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
                                        x-init='handleTributeEvent()'
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
                            <x-heroicon-o-bars-arrow-down class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
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

            {{-- preview --}}
            <x-card class="rounded-lg" x-show="templateSelected" x-cloak>
                <x-slot:header>
                    <div class="flex items-center">
                        <x-carbon-view class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" />
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('preview') }}
                        </h1>
                    </div>
                </x-slot:header>


                <x-slot:content>
                    <div class="w-full p-6 border border-gray-200 rounded shadow-sm dark:border-gray-700"
                        style="background-image: url('{{ asset('img/chat/whatsapp_light_bg.png') }}');">
                        <div class="mb-1" x-show="previewUrl">

                            <!-- Image Preview -->
                            <a x-show="inputType === 'IMAGE'" :href="previewUrl" class="glightbox">
                                <img x-show="inputType === 'IMAGE'" :src="previewUrl"
                                    class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800" />
                            </a>
                            <!-- Video Preview -->
                            <video x-show="inputType === 'VIDEO'" :src="previewUrl" controls
                                class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800"></video>

                            <!-- Document Preview -->
                            <div x-show="inputType === 'DOCUMENT'"
                                class="p-4 border border-gray-300 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-sm text-gray-500 dark:text-gray-400"> {{ t('document_uploaded') }} <a
                                        :href="previewUrl" target="_blank"
                                        class="text-info-500 underline break-all inline-flex "
                                        x-text="previewFileName"></a>
                                </p>
                            </div>
                        </div>
                        <div class="p-6 bg-white rounded-lg dark:bg-gray-800 dark:text-white">
                            <p class="mb-3 font-meduim text-gray-800 dark:text-gray-400"
                                x-html="replaceVariables(templateHeader, headerInputs)"></p>
                            <p class="mb-3 font-normal text-sm text-gray-500 dark:text-gray-400"
                                x-html="replaceVariables(templateBody, bodyInputs)"></p>
                            <div class="mt-4">
                                <p class="font-normal text-xs text-gray-500 dark:text-gray-400" x-text="templateFooter">
                                </p>
                            </div>
                        </div>

                        <template x-if="buttons && buttons.length > 0"
                            class="bg-white rounded-lg py-2 dark:bg-gray-800 dark:text-white">
                            <!-- Check if buttons is defined and not empty -->
                            <div class="space-y-1">
                                <template x-for="(button, index) in buttons" :key="index">
                                    <div
                                        class="w-full px-4 py-2 bg-white text-gray-900 rounded-md dark:bg-gray-700 dark:text-white">
                                        <span x-html="button.text" class="text-sm block text-center"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </x-slot:content>
                <!-- Submit Button -->
                <x-slot:footer class="rounded-b-lg">
                    <div class="flex justify-end">
                        <x-button.loading-button type="button" target="save" x-on:click="handleSave()"
                            x-bind:disabled="isUploading"
                            x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }">
                            {{ empty($this->template_bot->id) ? t('save') : t('update_button') }}
                        </x-button.loading-button>
                    </div>
                </x-slot:footer>
            </x-card>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let selectElement = document.querySelector(".subtext-select");

        if (selectElement) {
            window.initTomSelect(".subtext-select", {
                allowEmptyOption: true,
                render: {
                    option: function(data, escape) {
                        return `
                        <div>
                            <span class="font-medium text-sm">${escape(data.text)}</span>
                            <div class="text-gray-500 text-xs">${escape(data.subtext || "")}</div>
                        </div>
                    `;
                    },
                    item: function(data, escape) {
                        return `<div>${escape(data.text)}</div>`;
                    }
                }
            });
        }
        Livewire.on("templates-updated", (templateId) => {

            let childEl = document.querySelector(".tom-select");

            if (!childEl) return;

            if (childEl.tomselect) {
                childEl.tomselect.destroy();
            }

            setTimeout(() => {
                if (templateId) {
                    childEl.value = templateId;
                    childEl.dispatchEvent(new Event("change"));
                }
                new TomSelect(childEl);
            }, 100);
        });

    });
</script>
