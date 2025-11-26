<div x-data="formComponent()">
    <x-slot:title>{{ t('theme_settings') }}</x-slot:title>

    <div class="pb-6">
        <x-settings-heading>{{ t('website_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-website-settings-navigation />
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-6">
            <form x-on:submit.prevent="validateAndSubmit">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>{{ t('theme_settings') }}</x-settings-heading>
                        <x-settings-description>
                            {{ t('theme_settings_description') }}
                        </x-settings-description>
                    </x-slot:header>

                    <x-slot:content>
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Image Upload Components -->
                            @foreach (['site_logo' => '22px x 40px', 'dark_logo' => '22px x 40px', 'favicon' => '12px x
                            12px', 'cover_page_image' => '729px x 152px'] as $key => $size)
                            @php
                                $hasImage = !empty($themeSettings['theme.' . $key]);
                                $imagePath = $hasImage ? asset('storage/' . $themeSettings['theme.' . $key]) : null;
                            @endphp
                            <div class="w-full upload-section" x-data="fileUploader('{{ $key }}', {{ $hasImage ? 'true' : 'false' }}, @js($imagePath))">
                                <x-label for="{{ $key }}" :value="new \Illuminate\Support\HtmlString(
                                        t($key) . '<em class=\'text-primary-600\'> (recommended ' . $size . ')</em>',
                                    )" class="mb-2" />

                                <div class="relative p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-info-500 transition duration-300 bg-gray-50 dark:bg-gray-800"
                                    x-on:click="$refs.imageInput.click()">
                                    <!-- Image Preview (New Upload) -->
                                    <div class="relative inline-block" x-show="preview" x-cloak>
                                        <img :src="preview" alt="Image Preview"
                                            class="object-contain rounded-lg shadow-md {{ $key === 'favicon' ? 'h-12 w-12' : ($key === 'cover_page_image' ? 'h-32 w-full max-w-md' : 'h-24 w-48') }}" />
                                        <button type="button"
                                            class="absolute -top-4 -right-4 bg-danger-500 text-white rounded-full shadow-lg hover:bg-danger-600 p-1 z-10"
                                            x-on:click.stop="confirmDelete($event, () => { clearImage(); $wire.set('{{ $key }}', ''); })">
                                            <x-heroicon-s-x-circle class="h-5 w-5" />
                                        </button>
                                    </div>

                                    <!-- Existing Image Preview -->
                                    <div class="relative inline-block" x-show="!preview && hasExistingImage && existingImagePath" x-cloak>
                                        <img :src="existingImagePath"
                                            alt="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                                            class="object-contain rounded-lg shadow-md {{ $key === 'favicon' ? 'h-12 w-12' : ($key === 'cover_page_image' ? 'h-32 w-full max-w-md' : 'h-24 w-48') }}" 
                                            onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23f3f4f6\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-size=\'12\'%3EImage%3C/text%3E%3C/svg%3E';" />
                                        <button type="button"
                                            class="absolute -top-4 -right-4 bg-danger-500 text-white rounded-full shadow-lg hover:bg-danger-600 p-1 z-10"
                                            x-on:click.stop="confirmDelete($event, () => { hasExistingImage = false; existingImagePath = null; clearImage(); $wire.removeSetting('{{ $key }}'); setTimeout(() => { location.reload(); }, 1500);})">
                                            <x-heroicon-s-x-circle class="h-5 w-5" />
                                        </button>
                                    </div>

                                    <!-- Placeholder if No Image -->
                                    <div class="text-center" x-show="!preview && !hasExistingImage" x-cloak>
                                        <x-heroicon-o-photo class="h-12 w-12 text-gray-400 dark:text-gray-500 mx-auto" />
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ 'Select or browse to upload ' . str_replace('_', ' ', $key) }}
                                        </p>
                                    </div>

                                    <!-- Progress Bar (New) -->
                                    <div class="w-full mt-3" x-show="isUploading" x-cloak>
                                        <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-info-500 rounded-full transition-all duration-300"
                                                :style="`width: ${progress}%`"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-1"
                                            x-text="`Uploading: ${progress}%`"></p>
                                    </div>

                                    <!-- File Input -->
                                    <input x-ref="imageInput" type="file" class="hidden" :accept="imageExtensions"
                                        x-on:change="handleFileChange" wire:model="{{ $key }}"
                                        x-on:livewire-upload-start="uploadStarted()"
                                        x-on:livewire-upload-finish="uploadFinished()"
                                        x-on:livewire-upload-error="uploadError()"
                                        x-on:livewire-upload-progress="uploadProgress($event.detail.progress)"
                                        wire:ignore />
                                </div>

                                <!-- Error Message -->
                                @if ($errors->any())
                                <x-input-error for="{{ $key }}" class="mt-2" />
                                @else
                                <p x-show="errorMessage" class="text-danger-500 text-sm mt-2" x-text="errorMessage">
                                </p>
                                @endif
                            </div>
                            @endforeach

                            <!-- Delete Confirmation Modal -->
                            <div x-data="confirmationModal()" x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto"
                                style="display: none;">
                                <div
                                    class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Overlay -->
                                    <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity"
                                        aria-hidden="true">
                                        <!-- Gradient Overlay -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-br from-gray-500/60 to-gray-700/60 dark:from-slate-900/80 dark:to-slate-800/80 backdrop-blur-sm">
                                        </div>
                                    </div>

                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                        aria-hidden="true">&#8203;</span>

                                    <!-- Modal Container -->
                                    <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        class="inline-block align-bottom bg-white dark:bg-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <div class="p-6">
                                            <div class="flex items-start space-x-4">
                                                <!-- Icon Container -->
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="h-12 w-12 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                                                        <x-heroicon-c-exclamation-circle
                                                            class="h-7 w-7 text-danger-600 dark:text-danger-400" />
                                                    </div>
                                                </div>

                                                <!-- Content -->
                                                <div class="flex-1">
                                                    <h3
                                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                                        {{ t('delete_image') }}
                                                    </h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                                        {{ t('delete_image_description') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div
                                            class="bg-gray-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end space-x-3">
                                            <button @click="closeModal()" type="button"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                {{ t('cancel') }}
                                            </button>
                                            <button @click="confirmAndClose()" type="button"
                                                class="px-4 py-2 text-sm font-medium text-white bg-danger-600 dark:bg-danger-700 rounded-lg hover:bg-danger-700 dark:hover:bg-danger-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500">
                                                {{ t('delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            @if(checkPermission('admin.website_settings.edit'))
                            <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg p-4">
                                <div class="flex  justify-end ">
                                    <x-button.primary type="submit" size="md" class="inline-flex items-center">
                                        {{ t('save_changes') }}
                                    </x-button.primary>
                                </div>
                            </x-slot:footer>
                            @endif
                        </div>
                    </x-slot:content>
                </x-card>
            </form>
        </div>
    </div>
</div>
<!-- File Handling Logic -->
<script>
    function formComponent() {
        return {
            validateAndSubmit() {
                let hasErrors = false;
                const uploadSections = document.querySelectorAll('.upload-section');
                uploadSections.forEach(section => {
                    const data = Alpine.$data(section);
                    if (data.errorMessage) {
                        hasErrors = true;
                        section.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });

                if (hasErrors) {
                    alert("{{ t('fix_error_discription') }}");
                    return;
                }

                this.$wire.save();
            }
        };
    }

    function fileUploader(initialKey, hasExistingImage, existingImagePath) {
        return {
            key: initialKey,
            file: null,
            preview: null,
            errorMessage: '',
            imageExtensions: '.png,.jpg,.jpeg',
            isUploading: false,
            progress: 0,
            hasExistingImage: hasExistingImage,
            existingImagePath: existingImagePath,

            init() {
                this.imageExtensions = this.imageExtensions.split(',')
                    .map(ext => ext.trim())
                    .join(', ');
            },

            handleFileChange(event) {
                this.errorMessage = '';
                this.file = event.target.files[0];

                if (!this.file) return;

                const fileExt = '.' + this.file.name.split('.').pop().toLowerCase();

                // Get allowed extensions with proper trimming
                const allowedExtensions = this.imageExtensions.split(',')
                    .map(ext => ext.trim());

                // Validate file extension
                if (!allowedExtensions.includes(fileExt)) {
                    this.errorMessage = "{{ t('invalid_file_type') }}" + " " + allowedExtensions.join(', ');
                    this.clearImage();
                    return;
                }

                // Validate file size (5 MB = 5 * 1024 * 1024 bytes)
                const maxFileSize = 5 * 1024 * 1024;
                if (this.file.size > maxFileSize) {
                    this.errorMessage = `{{ t('file_size_exceeds') }} ${this.formatFileSize(maxFileSize)}`;
                    this.clearImage();
                    return;
                }

                // Validate dimensions for cover_page_image
                if (this.key === 'cover_page_image') {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            if (img.width !== 729 || img.height !== 152) {
                                this.errorMessage = "";
                                this.clearImage();
                                return;
                            }
                            this.preview = e.target.result;
                        };
                        img.onerror = () => {
                            this.errorMessage = "Invalid image file.";
                            this.clearImage();
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(this.file);
                    return; // Prevent further execution until dimension check is done
                }

                // Preview the image for other keys
                const reader = new FileReader();
                reader.onload = (e) => this.preview = e.target.result;
                reader.readAsDataURL(this.file);
            },
            formatFileSize(bytes) {
                if (bytes < 1024) return `${bytes} bytes`;
                if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(2)} KB`;
                return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
            },

            clearImage() {
                this.preview = null;
                this.file = null;
                event.target.value = '';
                if (this.$refs.imageInput) this.$refs.imageInput.value = '';
            },

            // New progress tracking methods
            uploadStarted() {
                this.isUploading = true;
                this.progress = 0;
            },

            uploadFinished() {
                this.isUploading = false;
                this.progress = 100;
                // Reset progress after a short delay
                setTimeout(() => {
                    this.progress = 0;
                    // After upload finishes, the page will reload to show the new image
                    // So we don't need to update hasExistingImage here
                }, 1000);
            },

            uploadError() {
                this.isUploading = false;
                this.errorMessage = "{{ t('upload_failed_try_again') }}";
            },

            uploadProgress(progress) {
                this.progress = progress;
            },

            // Delete confirmation
            confirmDelete(event, callback) {
                event.stopPropagation();
                const modal = Alpine.$data(document.querySelector('[x-data="confirmationModal()"]'));
                modal.openModal(callback);
            }
        };
    }

    // New confirmation modal component
    function confirmationModal() {
        return {
            isOpen: false,
            confirmCallback: null,

            openModal(callback) {
                this.isOpen = true;
                this.confirmCallback = callback;
            },

            closeModal() {
                this.isOpen = false;
            },

            confirmAndClose() {
                if (typeof this.confirmCallback === 'function') {
                    this.confirmCallback();
                }
                this.closeModal();
            }
        };
    }
</script>