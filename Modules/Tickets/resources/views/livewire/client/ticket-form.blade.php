<div x-data="{
    // Moved from attachments section to parent
    files: [],
    allowedExtensions: {{ json_encode(array_map('trim', explode(',', get_whatsmark_allowed_extension()['file_types']['extension']))) }},
    maxFileSize: 10 * 1024 * 1024, // 10MB in bytes
    maxFiles: 5, // Maximum number of files allowed
    errors: [],
    isDragging: false,

    // Added: computed property to check if form can be submitted
    get canSubmit() {
        return this.errors.length === 0;
    },

    validateFile(file, currentCount) {
        if (currentCount >= this.maxFiles) {
            this.errors.push(`Maximum ${this.maxFiles} files are allowed.`);
            return false;
        }

        if (file.size > this.maxFileSize) {
            this.errors.push(`${file.name} is too large. Maximum size is 10MB.`);
            return false;
        }

        // Check file extension
        const ext = '.' + file.name.split('.').pop().toLowerCase();
        if (!this.allowedExtensions.includes(ext)) {
            this.errors.push(`${file.name} has an invalid extension. Allowed types: ${this.allowedExtensions.join(', ')}`);
            return false;
        }

        return true;
    },

    handleFiles(event) {
        this.errors = []; // Clear previous errors
        const fileList = event.target.files || (event.dataTransfer && event.dataTransfer.files);
        if (!fileList) return;

        // Get current number of attachments
        const currentAttachments = document.querySelectorAll('.attachment-preview').length;

        // Group errors by type to avoid duplicates
        const errorTypes = {
            maxFiles: false,
            invalidExtension: false,
            oversized: []
        };

        let validFiles = [];
        Array.from(fileList).forEach(file => {
            const currentCount = currentAttachments + validFiles.length;

            // Check max files (only once)
            if (currentCount >= this.maxFiles) {
                if (!errorTypes.maxFiles) {
                    this.errors.push(`Maximum ${this.maxFiles} files are allowed.`);
                    errorTypes.maxFiles = true;
                }
                return;
            }

            // Check file size
            if (file.size > this.maxFileSize) {
                errorTypes.oversized.push(file.name);
                return;
            }

            // Check file extension
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!this.allowedExtensions.includes(ext)) {
                if (!errorTypes.invalidExtension) {
                    this.errors.push(`Invalid file extension. Allowed types: ${this.allowedExtensions.join(', ')}`);
                    errorTypes.invalidExtension = true;
                }
                return;
            }

            validFiles.push(file);
        });

        // Add oversized files error (grouped)
        if (errorTypes.oversized.length > 0) {
            if (errorTypes.oversized.length === 1) {
                this.errors.push(`${errorTypes.oversized[0]} is too large. Maximum size is 10MB.`);
            } else {
                this.errors.push(`${errorTypes.oversized.length} files are too large. Maximum size is 10MB per file.`);
            }
        }

        if (validFiles.length > 0) {
            @this.uploadMultiple('attachments', validFiles);
        } else {
            // Clear the input if no valid files
            event.target.value = '';
        }
    },
    clearDepartmentDropdown() {
        const select = document.getElementById('department_id');
        if (select && select.tomselect) {
            select.tomselect.clear();

            select.tomselect.addOption({
                value: '',
                text: '{{ t('select_a_department') }}'
            });

            // Set the value to empty (select the default option)
            select.tomselect.setValue('');
        }
    }
}">
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Subject Field -->
                <div>
                    <label for="subject"
                        class="flex items-center space-x-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span class="text-danger-500">*</span>
                        <span>{{ t('subject') }}</span>
                    </label>

                    <input type="text" wire:model="subject"
                        class="block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                        id="subject" placeholder="Subject for your ticket">

                    @error('subject')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department Field -->
                <div>
                    <label for="department_id"
                        class="flex items-center space-x-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span class="text-danger-500">*</span>
                        <span>{{ t('department') }}</span>
                    </label>
                    <div wire:ignore>
                        <x-select class="tom-select block w-full" id="department_id" wire:model.live="department_id">
                            <option value="">{{ t('select_a_department') }}</option>

                            @foreach ($departments as $dept)
                            <option value="{{ $dept['id'] }}"
                                data-assignee-id="{{ json_encode($dept['assignee_id'] ?? []) }}">
                                {{ $dept['name'] }}
                            </option>
                            @endforeach
                        </x-select>
                    </div>
                    @error('department_id')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                    @if ($this->department)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                        <x-heroicon-o-exclamation-circle class="w-4 h-4 mr-1 text-primary-500" />
                        {{ $this->department['description'] ?? t('selected_department_handle_request') }}
                    </p>
                    @if (!empty($autoAssignedUsers))
                    <div class="mt-2">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ t('ticket_will_be_assigned_to') }}</p>
                        <div class="mt-1 space-y-1">
                            @foreach ($autoAssignedUsers as $assignedUser)
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $assignedUser['name'] }}
                                @if (!empty($assignedUser['email']))
                                ({{ $assignedUser['email'] }})
                                @endif
                            </p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Priority Field -->
            <div>
                <label for="priority"
                    class="flex items-center space-x-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <span class="text-danger-500">*</span>
                    <span>{{ t('priority') }}</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($priorityOptions as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" class="sr-only" name="priority" id="priority_{{ $value }}"
                            value="{{ $value }}" wire:model.live="priority">
                        <div
                            class="border border-gray-300 dark:border-gray-600 dark:text-gray-400 rounded-lg p-1 text-center transition-all duration-200 hover:border-gray-400 dark:hover:border-gray-500 @if ($priority === $value) {{ $value === 'low' ? 'border-success-500 bg-success-50 dark:bg-success-900/20 text-success-700 dark:text-success-300' : ($value === 'medium' ? 'border-warning-500 bg-warning-50 dark:bg-warning-900/20 text-warning-700 dark:text-warning-300' : ($value === 'high' ? 'border-danger-500 bg-danger-50 dark:bg-danger-900/20 text-danger-700 dark:text-danger-300' : 'border-gray-900 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100')) }} @endif">
                            <span class="text-sm">{{ $label }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('priority')
                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Message Field -->
            <div x-data="{ body: @entangle('body'), max: 1000 }">
                <label for="body"
                    class="flex items-center space-x-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <span class="text-danger-500">*</span>
                    <span>{{ t('message') }}</span>
                </label>

                <x-textarea x-model="body" @input="body = body.slice(0, max)" wire:model="body" id="body" rows="6"
                    placeholder="{{ t('provide_detailed_information_about_issue') }}" />

                @error('body')
                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="body.length"></span>/1000 {{ t('characters') }}
                </p>
            </div>

            <!-- Attachments Section -->
            <div>
                <label for="attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ t('attachments') }}
                    <span class="text-gray-500 text-sm">{{ t('optional') }}</span>
                </label>

                <!-- Existing Attachments (only show when editing) -->
                @if (!empty($uploadedFiles))
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ t('existing_attachments') }}</h4>
                    <div class="space-y-2">
                        @foreach ($uploadedFiles as $key => $file)
                        <div
                            class="flex items-center justify-between p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 mr-2 text-info-500 dark:text-info-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                    </path>
                                </svg>
                                <span class="text-sm text-info-700 dark:text-info-300 font-medium">
                                    {{ is_array($file) ? $file['filename'] ?? ($file['name'] ?? 'Unknown file') : $file
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" wire:click="removeUploadedFile({{ $key }})"
                                    class="text-danger-500 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- New Attachments Upload Area -->
                <div class="mt-1 flex justify-center items-center px-6 py-4 border-2 rounded-md transition-colors cursor-pointer hover:border-primary-400 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    @click="$refs.fileInput.click()" :class="{
                        'border-gray-300 dark:border-gray-700 border-dashed': !isDragging,
                        'border-primary-500 border-solid bg-primary-50 dark:bg-primary-900/20': isDragging
                    }">

                    <div class="flex flex-col items-center justify-center gap-1 text-center">
                        <!-- Icon -->
                        <svg class="h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"
                            aria-hidden="true">
                            <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <!-- Upload Label -->
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <label for="file-upload"
                                class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                <span>{{ t('upload_files') }}</span>
                                <input x-ref="fileInput" id="file-upload" type="file" class="sr-only"
                                    @change="handleFiles($event)" multiple
                                    accept="{{ get_whatsmark_allowed_extension()['file_types']['extension'] }}">
                            </label>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ t('maximum_size_per_file_10mb') }}
                        </p>
                    </div>
                </div>

                <x-input-error for="attachments" class="mt-1" />

                <!-- Validation Errors -->
                <div x-show="errors.length > 0" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100" class="mt-2" x-cloak>
                    <div
                        class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-md p-3">
                        <div class="flex">
                            <svg class="w-5 h-5 text-danger-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                    {{ t('file_validation_errors') }}
                                </h3>
                                <div class="mt-1 space-y-1">
                                    <template x-for="(error,index) in errors" :key="index">
                                        <p class="text-sm text-danger-700 dark:text-danger-300" x-text="error"></p>
                                    </template>
                                </div>
                            </div>
                            <button type="button" @click="errors = []"
                                class="ml-2 text-danger-400 hover:text-danger-600 focus:outline-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Server-side validation errors -->
                    @error('attachments')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                    @error('attachments.*')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New File Preview -->
                @if (!empty($attachments))
                <div class="mt-2">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ t('new_attachments') }}</h4>
                    <div class="space-y-2">
                        @foreach ($attachments as $key => $attachment)
                        @if ($attachment && is_object($attachment) && method_exists($attachment,
                        'getClientOriginalName'))
                        <div
                            class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded attachment-preview">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500 dark:text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                    </path>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-xs">{{
                                    $attachment->getClientOriginalName() }}</span>
                            </div>
                            <button type="button" wire:click="removeAttachment({{ $key }})"
                                class="text-danger-500 hover:text-danger-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Form Actions - ONLY CHANGED THIS PART -->
        <div
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-t bg-slate-50 dark:bg-transparent rounded-b-lg border-slate-300 px-4 py-3 sm:px-6 dark:border-slate-600">
            <div>
                <button type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:click="resetForm" x-on:click="clearDepartmentDropdown()">
                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                    {{ t('reset_form') }}
                </button>
            </div>
            <div>
                <button type="submit" :disabled="!canSubmit"
                    :class="canSubmit ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-400 cursor-not-allowed'"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    {{ t('create_ticket') }}
                </button>
            </div>
        </div>
    </form>
</div>