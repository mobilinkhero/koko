<div>
    <x-slot:title>
        {{ t('ticket_details') }}
    </x-slot:title>
    <!-- Replies Section -->
    <x-card class="rounded-lg shadow-sm mb-6">
        <x-slot:header>
            <h3 class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                {{ t('conversation') }} ({{ $ticket->replies->count() }} replies)
            </h3>
        </x-slot:header>
        <x-slot:content>
            @forelse($ticket->replies->sortBy('created_at') as $reply)
            <div
                class="divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-600 rounded-md p-4 mb-3 ">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center">
                        <div
                            class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-full {{ $reply->user_type === 'tenant' ? 'bg-success-100 dark:bg-success-400 text-success-600 border border-success-300 dark:border-success-600' : 'bg-primary-100 dark:bg-primary-400 text-primary-600 border border-primary-300 dark:border-primary-600' }} ">
                            {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $reply->user->name ?? 'Unknown User' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $reply->user_type === 'tenant' ? 'tenant' : 'Support Agent' }} •
                                {{ format_date_time($reply->created_at) }}
                            </div>
                        </div>
                    </div>
                    @if ($reply->user_type === 'system')
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200">
                        {{ t('system_message') }}
                    </span>
                    @endif
                </div>

                <div
                    class="bg-gray-50 dark:bg-gray-800 p-3 rounded-md shadow-sm text-sm sm:text-base text-gray-800 dark:text-gray-300 break-words">
                    {!! nl2br(e($reply->content)) !!}
                </div>

                @if ($reply->attachments && count($reply->attachments) > 0)
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ t('attachments') }}
                    </h6>
                    <div class="space-y-2">
                        @foreach ($reply->attachments as $attachment)
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                </path>
                            </svg>

                            @if (is_array($attachment))
                            <a href="{{ tenant_route('tenant.tickets.download', [
                                                'ticket' => $ticket->id,
                                                'filename' => $attachment['filename'],
                                            ]) }}" target="_blank"
                                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-xs sm:text-sm truncate">
                                {{ $attachment['filename'] }}
                            </a>

                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-none">
                                ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                            </span>
                            @else
                            <a href="{{ tenant_route('tenant.tickets.download', [
                                                'ticket' => $ticket->id,
                                                'file' => basename($attachment),
                                            ]) }}" target="_blank"
                                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-xs sm:text-sm truncate">
                                {{ basename($attachment) }}
                            </a>
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-none">{{ t('file') }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ t('no_replies_yet_first_add_reply') }}</p>
            </div>
            @endforelse
        </x-slot:content>
    </x-card>

    <!-- Reply Form -->
    @if ($showReplyForm)
    <x-card id="reply-form" class="rounded-lg shadow-sm mb-6">
        <x-slot:header>
            <h3 class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                {{ t('add_reply') }}
            </h3>
        </x-slot:header>
        <x-slot:content>
            <form wire:submit="submitReply">
                <div class="group">
                    <label for="content"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        {{ t('message') }}
                    </label>
                    <x-textarea wire:model="content" id="content" rows="4" placeholder="Type your reply here..."
                        :disabled="$ticket->status === 'closed'"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 transition-colors duration-200" />
                    @error('content')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>
                <!-- Enhanced File Upload -->

                <div class="group" x-data="{
                        files: [],
                        allowedExtensions: {{ json_encode(array_map('trim', explode(',', get_whatsmark_allowed_extension()['file_types']['extension']))) }},
                        maxFileSize: 10 * 1024 * 1024, // 10MB
                        maxFiles: 5,
                        errors: [],
                        attachments: $wire.entangle('attachments'),
                        isDragging: false,
                        get canSubmit() {
                            return this.errors.length === 0;
                        },
                        validateFile(file, currentCount) {
                            const newErrors = [];
                            if (currentCount >= this.maxFiles && !this.errors.some(err => err.includes('Maximum'))) {
                                newErrors.push(`Maximum ${this.maxFiles} files are allowed.`);
                            }
                            // Check file extension
                            const ext = '.' + file.name.split('.').pop().toLowerCase();
                            if (!this.allowedExtensions.includes(ext.replace('.', ''))) {
                                // Only add extension error if it's not already present
                                const extensionErrorExists = this.errors.some(err => err.includes('invalid extension'));
                                if (!extensionErrorExists) {
                                    newErrors.push(`Invalid file extension. Allowed types: ${this.allowedExtensions.join(', ')}`);
                                } else {
                                    // Just add the specific file name to show which files are invalid
                                    newErrors.push(`${file.name} has invalid extension.`);
                                }
                            }
                            if (file.size > this.maxFileSize) {
                                newErrors.push(`${file.name} is too large. Maximum size is 10MB.`);
                            }
                            this.errors = [...this.errors, ...newErrors];
                            return newErrors.length === 0;
                        },

                        handleFiles(event) {
                            this.errors = [];
                            const fileList = event.target.files || (event.dataTransfer && event.dataTransfer.files);
                            if (!fileList) return;

                            const currentAttachments = this.files.length;

                            const errorTypes = { maxFiles: false, invalidExtension: false, oversized: [] };
                            let validFiles = [];

                            Array.from(fileList).forEach(file => {
                                const currentCount = currentAttachments + validFiles.length;
                                if (currentCount >= this.maxFiles) {
                                    if (!errorTypes.maxFiles) {
                                        this.errors.push(`Maximum ${this.maxFiles} files are allowed.`);
                                        errorTypes.maxFiles = true;
                                    }
                                    return;
                                }
                                if (file.size > this.maxFileSize) {
                                    errorTypes.oversized.push(file.name);
                                    return;
                                }


                                validFiles.push(file);
                            });

                            if (errorTypes.oversized.length > 0) {
                                if (errorTypes.oversized.length === 1) {
                                    this.errors.push(`${errorTypes.oversized[0]} is too large. Maximum size is 10MB.`);
                                } else {
                                    this.errors.push(`${errorTypes.oversized.length} files are too large. Maximum size is 10MB per file.`);
                                }
                            }

                            if (validFiles.length > 0) {
                                this.files = [...this.files, ...validFiles];
                            } else if (this.errors.length > 0) {
                                event.target.value = '';
                            }
                        },

                        removeFile(index) {
                            this.files.splice(index, 1);
                            this.errors = [];
                        },

                        clearFileInput() {
                            if (!Array.isArray(this.attachments) || this.attachments.length === 0) {
                                this.files = [];
                                this.errors = [];
                                const fileInput = this.$refs.fileInput;
                                if (fileInput) {
                                    fileInput.value = '';
                                }
                            }
                        },

                        reset() {
                            this.files = [];
                            this.errors = [];
                            this.attachments = [];
                            const fileInput = this.$refs.fileInput;
                            if (fileInput) {
                                fileInput.value = '';
                            }
                        },

                        init() {
                            this.$watch('attachments', (value) => {
                                if (!Array.isArray(value) || value.length === 0) {
                                    this.clearFileInput();
                                }
                            });
                        }
                    }">

                    <label for="attachments"
                        class=" text-sm my-2 font-medium text-gray-700 dark:text-gray-300  flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 transition-colors duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        {{ t('attachments') }} <span class="text-gray-500 dark:text-gray-400 font-normal ml-1">{{
                            t('optional') }}</span>
                    </label>

                    <!-- Drag & Drop Upload Area -->
                    <div class="relative w-full" @click="$refs.fileInput.click()" :class="{
                                'border-gray-300 dark:border-gray-600 border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50':
                                    !isDragging,
                                'border-primary-500 border-2 border-solid bg-primary-50 dark:bg-primary-900/20 hover:bg-gray-50 dark:hover:bg-gray-700/50': isDragging
                            }"
                        class="rounded-lg p-6 cursor-pointer hover:border-primary-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200">

                        <div class="text-center p-4" x-show="!isDragging">
                            <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="text-sm font-medium text-primary-700 dark:text-primary-300">{{ t('upload_files')
                                }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('click_to_browse') }} </p>
                        </div>



                        <!-- Hidden File Input -->
                        <input x-ref="fileInput" type="file" wire:model="attachments" class="hidden" id="attachments"
                            multiple accept="{{ get_whatsmark_allowed_extension()['file_types']['extension'] }}"
                            @change="handleFiles($event)" {{ $ticket->status === 'closed' ? 'disabled' : '' }}>


                    </div>

                    <!-- File Info -->
                    <div class="mt-2 flex items-start space-x-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p>{{ t('maximum_files_10mb_each') }}</p>
                        </div>
                    </div>

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
                                    <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">{{
                                        t('file_validation_errors') }}</h3>
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
                    </div>

                    <!-- Server-side Error Messages -->
                    @error('attachments.*')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror

                    <!-- File Preview -->
                    <div x-show="files.length > 0" x-transition class="mt-4" @submit.window="files = []">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-primary-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ t('selected_files') }} (<span x-text="files.length"></span>)
                        </h4>
                        <div class="space-y-2">
                            <template x-for="(file, index) in files" :key="index">
                                <div
                                    class="flex items-center justify-between p-2 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg attachment-preview">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <svg class="h-4 w-4 mr-2 text-primary-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-primary-700 dark:text-primary-300 truncate"
                                                x-text="file.name"></p>
                                            <p class="text-xs text-primary-600 dark:text-primary-400"
                                                x-text="(file.size / (1024 * 1024)).toFixed(2) + ' MB'">
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeFile(index)"
                                        class="ml-2 p-1 text-danger-500 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300 hover:bg-danger-100 dark:hover:bg-danger-800/50 rounded transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Closed Ticket Notice -->
                    @if ($ticket->status === 'closed')
                    <div
                        class="mt-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('ticket_is_closed') }}</p>
                        </div>
                    </div>
                    @endif


                    <div class="flex justify-between items-center sm:flex-row flex-col gap-4 sm:items-center mt-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ t('reply_visible_support_agents') }}
                        </div>
                        <button type="submit" :class="{
                                    'opacity-50 cursor-not-allowed bg-gray-400 text-gray-600': !
                                        canSubmit,
                                    'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500': canSubmit
                                }" :disabled="!canSubmit"
                            class="inline-flex items-center justify-center px-4 py-2 bg-info-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-info-700 focus:bg-info-700 active:bg-info-900 focus:outline-none focus:ring-2 focus:ring-info-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            wire:loading.attr="disabled">
                            <span class="flex items-center">
                                <div wire:loading class="mr-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                                <div wire:loading.remove>
                                    <x-heroicon-o-paper-airplane class="w-4 h-4 mr-2 flex-shrink-0" />
                                </div>
                                <span wire:loading.remove>{{ t('send_reply') }}</span>
                                <span wire:loading>{{ t('submitting') }}</span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </x-slot:content>
    </x-card>
    @endif
</div>