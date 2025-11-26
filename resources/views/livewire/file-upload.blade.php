<div x-data="{
    meta: {{ json_encode(get_meta_allowed_extension()) }},
    type: @entangle('type'),
    error: null,
    isDragging: false,
    isUploading: false,
    fileSelected: false,
    tempFile: null,
    fileName: '',
    previewUrl: null,
    progressPercent: 0,
    uploadInterval: null,
    maxWidth: @entangle('maxWidth'),
    getMaxWidthClass() {
        const sizes = {
            'sm': 'sm:max-w-sm',
            'md': 'sm:max-w-md',
            'lg': 'sm:max-w-lg',
            'xl': 'sm:max-w-xl',
            '2xl': 'sm:max-w-2xl',
            '3xl': 'sm:max-w-3xl',
            '4xl': 'sm:max-w-4xl',
        };
        return sizes[this.maxWidth] || 'sm:max-w-2xl';
    },
    validateFile(event) {
        this.error = null;
        this.previewUrl = null;
        const file = event.target.files ? event.target.files[0] : null;
        if (!file) return;

        const ext = '.' + file.name.split('.').pop().toLowerCase();
        const allowedExt = this.meta[this.type]?.extension?.split(',').map(e => e.trim()) || [];
        const maxSizeMB = this.meta[this.type]?.size || 5;
        const maxSizeBytes = maxSizeMB * 1024 * 1024;

        if (!allowedExt.includes(ext)) {
            this.error = `Only ${allowedExt.join(', ')} files allowed.`;
            event.target.value = '';
            this.fileSelected = false;
            this.fileName = '';
            return;
        }

        if (file.size > maxSizeBytes) {
            this.error = `File exceeds ${maxSizeMB}MB limit.`;
            event.target.value = '';
            this.fileSelected = false;
            this.fileName = '';
            return;
        }

        // File is valid, store it temporarily without setting the Livewire property
        this.tempFile = file;
        this.fileSelected = true;
        this.fileName = file.name;

        // Create preview for the file
        this.createPreview(file);
    },
    createPreview(file) {
        if (!file) return;

        // Create FileReader instance
        const reader = new FileReader();

        // Set up the FileReader onload event
        reader.onload = (e) => {
            // For images, videos, and audio we can show a direct preview
            if (this.type === 'image' || this.type === 'video' || this.type === 'audio') {
                this.previewUrl = e.target.result;
            } else {
                // For documents, we'll just indicate that a file is selected
                // We could display an icon based on file type here
                this.previewUrl = 'document';
            }
        };

        // Read the file as a data URL (base64 encoded string)
        reader.readAsDataURL(file);
    },
    uploadFile() {
        if (!this.fileSelected || !this.tempFile) return;

        // Start upload process
        this.isUploading = true;

        // Create a FormData object to simulate a file upload
        const formData = new FormData();
        formData.append('file', this.tempFile);

        // Now set the Livewire file property which will trigger the updatedFile method
        @this.upload('file', this.tempFile, (event) => {
            // This callback runs during upload progress
            this.progressPercent = event.detail.progress;
        }, () => {
            // This callback runs when upload is finished
            this.isUploading = false;
            this.fileSelected = false;
            this.tempFile = null;
            this.fileName = '';
            this.previewUrl = null; // Clear the local preview
            this.progressPercent = 100;

            // Reset progress after a short delay
            setTimeout(() => {
                this.progressPercent = 0;
            }, 500);
        });
    },
    isTypeSelectorDisabled() {
        return this.isUploading || this.fileSelected || this.$wire.fileUrl;
    },
    removePreview() {
        this.error = null;
        this.isUploading = false;
        this.fileSelected = false;
        this.tempFile = null;
        this.fileName = '';
        this.previewUrl = null; // Clear the preview URL
        this.progressPercent = 0;
        // Check if fileInput exists before trying to clear it
        if (this.$refs.fileInput) {
            this.$refs.fileInput.value = '';
        }
        @this.call('removeFile') // Trigger the Livewire method
    },
    handleDrop(event) {
        this.isDragging = false;
        const dt = event.dataTransfer;
        if (dt.files.length) {
            this.$refs.fileInput.files = dt.files;
            this.validateFile({ target: this.$refs.fileInput });
        }
    }
}" class="w-full p-4 space-y-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700"
    :class="getMaxWidthClass()" x-cloak>

    <!-- Type Selector -->
    <div class="mb-4">
        @if ($showTypeSelector)
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            {{ t('file_type') }}
        </label>
        <div class="relative">
            <select wire:model="type" :disabled="isTypeSelectorDisabled()" class="block w-full tom-select">
                <option value="image">
                    <span class="flex items-center">
                        <x-heroicon-o-photo class="w-5 h-5 mr-2" />
                        {{ t('image') }}
                    </span>
                </option>
                <option value="video">
                    <span class="flex items-center">
                        <x-heroicon-o-film class="w-5 h-5 mr-2" />
                        {{ t('video') }}
                    </span>
                </option>
                <option value="document">
                    <span class="flex items-center">
                        <x-heroicon-o-document class="w-5 h-5 mr-2" />
                        {{ t('document') }}
                    </span>
                </option>
                <option value="audio">
                    <span class="flex items-center">
                        <x-heroicon-o-musical-note class="w-5 h-5 mr-2" />
                        {{ t('audio') }}
                    </span>

                </option>
            </select>
        </div>
        @else
        <div class="flex items-center space-x-2 text-sm font-medium text-slate-700 dark:text-slate-300">
            @if ($type === 'image')
            <x-heroicon-o-photo class="w-5 h-5" />
            @elseif ($type === 'video')
            <x-heroicon-o-film class="w-5 h-5" />
            @elseif ($type === 'document')
            <x-heroicon-o-document class="w-5 h-5" />
            @elseif ($type === 'audio')
            <x-heroicon-o-musical-note class="w-5 h-5" />
            @endif
            <span class="capitalize">{{ $type }}</span>
        </div>
        @endif
    </div>

    <!-- File Input with Drag & Drop (only show if no preview and no fileUrl) -->
    <div x-show="!previewUrl && !$wire.fileUrl" x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false" x-on:drop.prevent="handleDrop($event)" :class="{
            'border-info-500 bg-info-50 dark:bg-info-900/20': isDragging,
            'border-slate-300 dark:border-slate-600': !isDragging
        }"
        class="relative flex flex-col items-center justify-center h-64 px-4 py-6 transition-colors duration-150 border-2 border-dashed rounded-lg cursor-pointer group">

        <!-- Icon based on file type -->
        <div
            class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 group-hover:text-info-500 dark:group-hover:text-info-400 transition-colors duration-200">
            <template x-if="type === 'image'">
                <x-heroicon-o-photo class="w-8 h-8" />
            </template>
            <template x-if="type === 'video'">
                <x-heroicon-o-film class="w-8 h-8" />
            </template>
            <template x-if="type === 'document'">
                <x-heroicon-o-document class="w-8 h-8" />
            </template>
            <template x-if="type === 'audio'">
                <x-heroicon-o-musical-note class="w-8 h-8" />
            </template>
        </div>

        <div class="text-center space-y-1">
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    <span class="text-info-600 dark:text-info-400">{{ t('click_to_select') }}</span> {{
                    t('or_drag_and_drop') }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span x-text="meta[type]?.extension || '.jpg, .png, .pdf'"></span>
                    <span>{{ t('up_to') }} </span>
                    <span x-text="meta[type]?.size || 5"></span>MB
                </p>
            </div>
        </div>

        <input x-ref="fileInput" type="file" :accept="meta[type]?.extension" x-on:change="validateFile($event)"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
    </div>

    <!-- File Preview (Local Preview) -->
    <div x-show="previewUrl && !$wire.fileUrl" x-cloak
        class="mt-4 p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ t('file_preview') }}
            </h3>
            <button type="button" x-on:click="removePreview"
                class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-500 dark:text-slate-400 hover:text-danger-500 dark:hover:text-danger-400">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>
        </div>

        <div class="rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700 shadow-inner">
            <!-- Image Preview -->
            <template x-if="type === 'image' && previewUrl">
                <div class="relative pb-[56.25%] bg-slate-800">
                    <img :src="previewUrl" class="absolute inset-0 w-full h-full object-contain"
                        alt="{{ t('uploaded_image_preview') }}">
                </div>
            </template>

            <!-- Video Preview -->
            <template x-if="type === 'video' && previewUrl">
                <div class="relative pb-[56.25%] bg-slate-800">
                    <video controls class="absolute inset-0 w-full h-full">
                        <source :src="previewUrl" type="video/mp4">
                        {{ t('browser_does_not_support_video') }}
                    </video>
                </div>
            </template>

            <!-- Audio Preview -->
            <template x-if="type === 'audio' && previewUrl">
                <div class="p-4">
                    <audio controls class="w-full">
                        <source :src="previewUrl" type="audio/mpeg">
                        {{ t('browser_not_support_audio') }}
                    </audio>
                </div>
            </template>

            <!-- Document Preview (just show an icon and filename) -->
            <template x-if="type === 'document' && previewUrl === 'document'">
                <div class="flex items-center justify-center p-4">
                    <div
                        class="flex items-center p-3 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-600 group">
                        <x-heroicon-o-document class="w-6 h-6 mr-2 text-info-500" />
                        <span class="text-slate-700 dark:text-slate-300 max-w-xs truncate" x-text="fileName"></span>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 flex items-center">
            <x-heroicon-o-check-circle class="w-4 h-4 mr-1 text-success-500" />
            <span>{{ t('file_selected_ready_upload') }}</span>
        </div>

        <!-- Upload Button -->
        <div class="mt-3 flex justify-center">
            <button type="button" x-on:click="uploadFile"
                class="px-4 py-2 font-medium text-white bg-info-600 rounded-lg hover:bg-info-700 focus:outline-none focus:ring-2 focus:ring-info-500 focus:ring-offset-2 transition-colors duration-200 flex items-center space-x-2"
                :disabled="isUploading">
                <x-heroicon-o-cloud-arrow-up class="w-5 h-5" />
                <span>{{ t('upload_file') }}</span>
            </button>
        </div>
    </div>

    <!-- Error Messages -->
    <div x-show="error" x-cloak
        class="p-3 rounded-md bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 mb-2">
        <div class="flex">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 text-danger-500 mr-2 flex-shrink-0" />
            <p class="text-sm text-danger-600 dark:text-danger-400" x-text="error"></p>
        </div>
    </div>

    @error('file')
    <div class="p-3 rounded-md bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 mb-2">
        <div class="flex">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 text-danger-500 mr-2 flex-shrink-0" />
            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
        </div>
    </div>
    @enderror

    <!-- Server File Preview (from Database) -->
    @if ($fileUrl)
    <div class="mt-4 p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ t('file_preview') }}
            </h3>
            <button type="button" x-on:click="removePreview"
                class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-500 dark:text-slate-400 hover:text-danger-500 dark:hover:text-danger-400">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>
        </div>

        <div class="rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700 shadow-inner">
            @if ($type === 'image')
            <div class="relative pb-[56.25%] bg-slate-800">
                <img src="{{ $fileUrl }}" class="absolute inset-0 w-full h-full object-contain"
                    alt="Uploaded image preview">
            </div>
            @elseif ($type === 'video')
            <div class="relative pb-[56.25%] bg-slate-800">
                <video controls class="absolute inset-0 w-full h-full">
                    <source src="{{ $fileUrl }}" type="video/mp4">
                    {{ t('browser_does_not_support_video') }}
                </video>
            </div>
            @elseif ($type === 'audio')
            <div class="p-4">
                <audio controls class="w-full">
                    <source src="{{ $fileUrl }}" type="audio/mpeg">
                    {{ t('browser_not_support_audio') }}
                </audio>
            </div>
            @else
            <div class="flex items-center justify-center p-4">
                <a href="{{ $fileUrl }}" target="_blank"
                    class="flex items-center p-3 bg-white dark:bg-slate-800 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-slate-200 dark:border-slate-600 group">
                    <x-heroicon-o-document class="w-6 h-6 mr-2 text-info-500 group-hover:text-info-600" />
                    <span
                        class="text-slate-700 dark:text-slate-300 group-hover:text-info-600 dark:group-hover:text-info-400">{{
                        t('view') }}
                        {{ t('document') }}</span>
                    <x-heroicon-o-arrow-top-right-on-square
                        class="w-4 h-4 ml-2 text-slate-400 group-hover:text-info-500" />
                </a>
            </div>
            @endif
        </div>

        <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 flex items-center">
            <x-heroicon-o-check-circle class="w-4 h-4 mr-1 text-success-500" />
            <span>{{ t('file_uploaded_successfully') }}</span>
        </div>
    </div>
    @endif
</div>