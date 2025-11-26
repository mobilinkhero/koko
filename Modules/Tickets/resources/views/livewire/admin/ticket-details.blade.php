<div>
    @if ($ticket)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Ticket Header -->
            <x-card class="rounded-lg shadow-sm mb-6">
                <x-slot:header>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-4 sm:mb-0">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                <span class="font-mono text-primary-600 dark:text-primary-400 font-bold">#{{
                                    $ticket->ticket_id }}</span>
                                {{ $ticket->subject }}
                            </h5>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ t('Created') }} {{ $ticket->created_at->diffForHumans() }} {{ t('by') }}
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    @if ($ticket->tenantStaff)
                                    <a href="{{ route('admin.tenants.view', ['tenantId' => $ticket->tenantStaff->tenant_id]) }}"
                                        class="text-primary-700 underline">
                                        {{ $ticket->tenantStaff->firstname . ' ' . $ticket->tenantStaff->lastname }}
                                    </a>
                                    @if ($ticket->tenant)
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ t('on behalf of') }}
                                        <a href="{{ route('admin.tenants.view', ['tenantId' => $ticket->tenant->id]) }}"
                                            class="text-primary-700 underline">
                                            {{ $ticket->tenant->company_name }}
                                        </a>
                                    </span>
                                    @endif

                                    @elseif ($ticket->tenant && $ticket->tenant->adminUser)
                                    <a href="{{ route('admin.tenants.view', ['tenantId' => $ticket->tenant->id]) }}"
                                        class="text-primary-700 underline">
                                        {{ $ticket->tenant->adminUser->firstname . ' ' .
                                        $ticket->tenant->adminUser->lastname }}
                                    </a>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ t('on_behalf_of') }}
                                        <a href="{{ route('admin.tenants.view', ['tenantId' => $ticket->tenant->id]) }}"
                                            class="hover:underline">
                                            {{ $ticket->tenant->company_name }}
                                        </a>
                                    </span>

                                    @elseif($ticket->tenant)
                                    <a href="{{ route('admin.tenants.view', ['tenantId' => $ticket->tenant->id]) }}"
                                        class="hover:underline">
                                        {{ $ticket->tenant->company_name }}
                                    </a>

                                    @else
                                    {{ t('unknown_tenant') }}
                                    @endif
                                </span>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            {!! $this->getStatusBadge($ticket->status) !!}
                            {!! $this->getPriorityBadge($ticket->priority) !!}
                        </div>
                    </div>
                </x-slot:header>
                <x-slot:content>
                    <div>
                        <div class=" max-w-none text-gray-800 dark:text-gray-300 ">
                            {!! nl2br(e($ticket->body)) !!}
                        </div>


                        @if ($ticket->attachments && count($ticket->attachments) > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ t('attachments')
                                }}
                            </h6>
                            <div class="space-y-2">
                                @foreach ($ticket->attachments as $attachment)
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                        </path>
                                    </svg>

                                    @if (is_array($attachment))
                                    {{-- New format: array with filename, path, size --}}
                                    <a href="{{ route('admin.tickets.download-attachment', [
                                                        'ticket' => $ticket->id,
                                                        'file' => $attachment['filename'],
                                                    ]) }}"
                                        class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-xs sm:text-sm truncate">
                                        {{ $attachment['filename'] }}
                                    </a>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 flex-none">
                                        ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                                    </span>
                                    @else
                                    {{-- Legacy format: just filename string --}}
                                    <a href="{{ route('admin.tickets.download-attachment', [
                                                        'ticket' => $ticket->id,
                                                        'file' => $attachment,
                                                    ]) }}"
                                        class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-xs sm:text-sm truncate">
                                        {{ $attachment }}
                                    </a>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 flex-none">{{ t('file')
                                        }}</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </x-slot:content>
            </x-card>

            <!-- Replies -->
            @if ($ticket->replies && count($ticket->replies) > 0)
            <x-card class="rounded-lg shadow-sm mb-6">
                <x-slot:header>
                    <h6 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <x-heroicon-o-chat-bubble-oval-left-ellipsis class="w-6 h-6 mr-2 text-primary-600" />

                        {{ t('replies') }} ({{ count($ticket->replies) }})
                    </h6>
                </x-slot:header>
                <x-slot:content>
                    <div
                        class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg">
                        @foreach ($ticket->replies as $reply)
                        <div class="py-5 px-4 sm:px-6">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center space-x-3">
                                    @if ($reply->user_type === 'admin')
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-primary-500" />
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{
                                            $reply->user->name ?? 'Admin' }}</span>
                                    </div>
                                    @elseif($reply->user_type === 'system')
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ t('system')
                                            }}</span>
                                    </div>
                                    @else
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{
                                            $reply->user->name ?? 'Tenant' }}</span>
                                    </div>
                                    @endif

                                    @if ($reply->user_type === 'system')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-800 dark:text-info-100">
                                        {{ t('system') }}
                                    </span>
                                    @endif
                                </div>



                                <div class="flex items-center space-x-6">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{
                                        $reply->created_at->diffForHumans() }}</span>
                                    @if (Auth::user()->is_admin &&
                                    Auth::user()->user_type === 'admin' &&
                                    $reply->user_type === 'admin' &&
                                    $reply->created_at->diffInMinutes(now()) <=
                                        \Modules\Tickets\Models\TicketReply::DELETION_WINDOW && $ticket->status !==
                                        'closed')
                                        <button wire:click="deleteReply({{ $reply->id }})" class="group relative">
                                            <x-heroicon-o-trash class="w-5 h-5 text-danger-500" />
                                        </button>
                                        @endif
                                </div>
                            </div>

                            <div class="mt-3">
                                <div
                                    class="bg-gray-50 dark:bg-gray-800 p-3 rounded-md shadow-sm text-sm sm:text-base text-gray-800 dark:text-gray-300 break-words">
                                    {!! nl2br(e($reply->content)) !!}
                                </div>
                            </div>

                            @if ($reply->attachments && count($reply->attachments) > 0)
                            <div class="mt-4">
                                <div
                                    class="bg-primary-50 dark:bg-gray-700/30 p-3 rounded-md border border-primary-200 dark:border-primary-500">
                                    <h6 class="text-sm font-semibold text-primary-800 dark:text-primary-200 mb-2">
                                        {{ t('attachments') }}</h6>
                                    <div class="space-y-2">
                                        @foreach ($reply->attachments as $attachment)
                                        <div
                                            class="flex items-center gap-2 p-2 bg-white dark:bg-gray-700 rounded shadow-sm">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>

                                            @if (is_array($attachment))
                                            {{-- New format: array with filename, path, size --}}
                                            <a href="{{ route('admin.tickets.download-attachment', [
                                                                        'ticket' => $ticket->id,
                                                                        'file' => $attachment['filename'],
                                                                    ]) }}"
                                                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-xs sm:text-sm truncate">
                                                {{ $attachment['filename'] }}
                                            </a>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-none">
                                                ({{ number_format($attachment['size'] / 1024, 1) }}
                                                KB)
                                            </span>
                                            @else
                                            <a href="{{ route('admin.tickets.download-attachment', [
                                                                        'ticket' => $ticket->id,
                                                                        'file' => $attachment,
                                                                    ]) }}"
                                                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-xs sm:text-sm truncate">
                                                {{ $attachment }} </a>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </x-slot:content>
            </x-card>
            @endif

            <!-- Reply Form -->
            @if ($ticket->status !== 'closed')
            <x-card class="rounded-lg shadow-sm mb-6">
                <x-slot:header>
                    <h6 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <x-heroicon-o-arrow-uturn-left class="w-5 h-5 mr-2 text-primary-500" />
                        {{ t('add_reply') }}
                    </h6>
                </x-slot:header>
                <x-slot:content>
                    <form wire:submit="addReply" class="space-y-6">
                        <!-- Message Field -->
                        <div class="group">
                            <label for="replyContent"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                {{ t('message') }}
                            </label>
                            <x-textarea wire:model="replyContent" id="replyContent" rows="4"
                                placeholder="Type your reply here..." :disabled="$ticket->status === 'closed'"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 transition-colors duration-200" />
                            @error('replyContent')
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

                        <div class="group" x-cloak x-data="{
                                    files: [],
                                    allowedExtensions: {{ json_encode(array_map('trim', explode(',', get_whatsmark_allowed_extension()['file_types']['extension']))) }}, // Example extensions
                                    maxFileSize: 10 * 1024 * 1024, // 10MB in bytes
                                    maxFiles: 5, // Maximum number of files allowed
                                    errors: [],
                                    attachments: $wire.entangle('attachments'),
                                    isDragging: false,
                                    get canSubmit() {
                                        return this.errors.length === 0 && {{ $ticket->status === 'closed' ? 'false' : 'true' }};
                                    },
                                    validateFile(file, currentCount) {
                                        const newErrors = [];

                                        // Check maximum files limit (only add this error once)

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
                                        // Check file size
                                        if (file.size > this.maxFileSize) {
                                            newErrors.push(`${file.name} is too large. Maximum size is 10MB.`);
                                        }


                                        // Add new errors to the main errors array
                                        this.errors = [...this.errors, ...newErrors];

                                        return newErrors.length === 0;
                                    },

                                    handleFiles(event) {
                                        this.errors = []; // Clear previous errors
                                        const fileList = event.target.files || (event.dataTransfer && event.dataTransfer.files);
                                        if (!fileList) return;


                                        this.files = []; // This is the KEY to achieve your goal!


                                        // Get current number of attachments
                                        const currentAttachments = this.files.length;

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
                                            this.files = [...this.files, ...validFiles];
                                        } else if (this.errors.length > 0) {
                                            // Clear the input if no valid files and there are errors
                                            event.target.value = '';
                                        }
                                    },

                                    removeFile(index) {
                                        this.files.splice(index, 1);
                                        this.errors = [];
                                    },

                                    // Auto-clear when backend clears attachments
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

                                    // Manual reset method
                                    reset() {
                                        this.files = [];
                                        this.errors = [];
                                        this.attachments = []; // This will trigger backend clear via entangle
                                        const fileInput = this.$refs.fileInput;
                                        if (fileInput) {
                                            fileInput.value = '';
                                        }
                                    },
                                    init() {
                                        this.$watch('attachments', (value) => {

                                            // If backend attachments become empty, clear frontend
                                            if (!Array.isArray(value) || value.length === 0) {
                                                this.clearFileInput();
                                            }
                                        });
                                    }
                                }">

                            <label for="attachments"
                                class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 transition-colors duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                {{ t('attachments') }} <span
                                    class="text-gray-500 dark:text-gray-400 font-normal ml-1">{{ t('optional') }}</span>
                            </label>

                            <!-- Drag & Drop Upload Area -->
                            <div class="relative" @click="$refs.fileInput.click()" :class="{
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
                                    <p class="text-sm font-medium text-primary-700 dark:text-primary-300">{{
                                        t('upload_files') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('click_to_browse') }}</p>
                                </div>

                                <!-- Hidden File Input -->
                                <input x-ref="fileInput" type="file" wire:model="attachments" class="hidden"
                                    id="attachments" multiple
                                    accept="{{ get_whatsmark_allowed_extension()['file_types']['extension'] }}"
                                    @change="handleFiles($event)">
                            </div>

                            <!-- File Info -->
                            <div class="mt-2 flex items-start space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p>{{ t('maximum_5_files') }}</p>

                                </div>
                            </div>

                            <!-- Validation Errors -->
                            <div x-show="errors.length > 0" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100" class="mt-2" x-cloak>
                                <div
                                    class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-md p-3">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-danger-400 mr-2 mt-0.5 flex-shrink-0"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">{{
                                                t('file_validation_errors') }}</h3>
                                            <div class="mt-1 space-y-1">
                                                <template x-for="(error, index) in errors" :key="index">
                                                    <p class="text-sm text-danger-700 dark:text-danger-300"
                                                        x-text="error">
                                                    </p>
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
                            <div x-show="files.length > 0" x-transition class="mt-4">
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
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
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
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('file_uploads_disabled') }}
                                    </p>
                                </div>
                            </div>
                            @endif


                            <!-- Actions Row -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
                                <!-- Email Notification Checkbox (Left side on desktop) -->
                                <div class="flex items-center order-2 sm:order-1">
                                    <input
                                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded
                                            dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-primary-500 dark:focus:ring-offset-gray-800
                                            disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                        type="checkbox" wire:model="send_notification" id="sendNotification" {{
                                        $ticket->status === 'closed' ? 'disabled' : '' }} checked>
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none"
                                        for="send_notification">
                                        {{ t('send_email_notification') }}
                                    </label>
                                </div>

                                <div class="flex items-center space-x-3 order-1 sm:order-2">
                                    <!-- ✅ Send Reply Button - Converted to regular button -->
                                    <button type="submit"
                                        class="flex items-center justify-center space-x-2 w-[200px] px-4 py-2 text-sm font-medium rounded-md border border-transparent transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                        :class="{
                                                    'opacity-50 cursor-not-allowed bg-gray-400 text-gray-600': !
                                                        canSubmit,
                                                    'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500': canSubmit
                                                }" :disabled="!canSubmit" wire:loading.attr="disabled">

                                        <!-- Normal state -->
                                        <span wire:loading.remove class="flex items-center space-x-2">
                                            <span>{{ t('send_reply') }}</span>
                                        </span>

                                        <!-- Loading state -->
                                        <span wire:loading class="flex items-center space-x-2">
                                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />
                                        </span>
                                    </button>

                                    <!-- ✅ Reply & Close Button - Updated with Alpine.js directives -->
                                    <button type="button"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm border border-transparent rounded-md font-medium transition-all duration-200 shadow-sm hover:shadow-md whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-offset-2"
                                        :class="{
                                                    'opacity-50 cursor-not-allowed bg-gray-400 text-gray-600': !
                                                        canSubmit,
                                                    'text-white bg-success-600 hover:bg-success-700 focus:ring-success-500 dark:hover:bg-success-500 dark:focus:ring-offset-slate-800': canSubmit
                                                }" :disabled="!canSubmit" wire:click="addReplyAndClose"
                                        wire:loading.attr="disabled">

                                        <x-heroicon-o-check-circle class="w-4 h-4 mr-2 flex-shrink-0" />
                                        {{ t('reply_and_close') }}
                                    </button>
                                </div>
                            </div>


                            <!-- Closed Ticket Notice -->
                            @if ($ticket->status === 'closed')
                            <div
                                class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800/50 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-warning-600 dark:text-warning-400 mt-0.5 flex-shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                            {{ t('ticket_closed') }}</h4>
                                        <p class="text-sm text-warning-700 dark:text-warning-300 mt-1">
                                            {{ t('ticket_is_closed_message') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>
                </x-slot:content>
            </x-card>
            @else
            <div class="bg-info-50 border border-info-200 rounded-md p-4 dark:bg-info-900/20 dark:border-info-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-info-700 dark:text-info-300">
                            {{ t('ticket_closed_reopen') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        {{ t('ticket_not_found') }}
    </div>
    @endif
</div>