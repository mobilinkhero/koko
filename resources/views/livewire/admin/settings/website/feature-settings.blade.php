@php
$featureSettings = get_batch_settings([
'theme.feature_image',
]);
@endphp

<div x-data="formComponent()">
    <x-slot:title>
        {{ t('feature_settings') }}
    </x-slot:title>

    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('website_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-website-settings-navigation />
        </div>

        <div class="flex-1 space-y-6">
            <form x-on:submit.prevent="validateAndSubmit" class="space-y-8">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('feature') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_the_feature_for_your_website') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content class="space-y-4">
                        <!-- Feature Title -->
                        <div>
                            <x-label for="feature_title" :value="t('feature_title')" />
                            <x-input id="feature_title" type="text" class="mt-1 block w-full" wire:model="feature_title"
                                autocomplete="off" />
                            <x-input-error for="feature_title" class="mt-2" />
                        </div>

                        <!--  Feature Subtitle -->
                        <div>
                            <x-label for="feature_subtitle" :value="t('feature_subtitle')" />
                            <x-input id="feature_subtitle" type="text" class="mt-1 block w-full"
                                wire:model="feature_subtitle" autocomplete="off" />
                            <x-input-error for="feature_subtitle" class="mt-2" />
                        </div>

                        <!--  Feature Description -->
                        <div>
                            <x-label for="feature_description" :value="t('feature_description')" />
                            <x-textarea id="feature_description" rows="4" class="mt-1 block w-full"
                                wire:model="feature_description"></x-textarea>
                            <x-input-error for="feature_description" class="mt-2" />
                        </div>

                        <!-- Dynamic List -->
                        <div>
                            <x-label :value="t('feature_list')" />
                            <div class="space-y-2">
                                @foreach ($feature_list as $index => $listItem)
                                <div class="flex items-center gap-4">
                                    <x-input type="text" class="block w-full" wire:model="feature_list.{{ $index }}"
                                        autocomplete="off" />
                                    <x-button.secondary type="button" wire:click="removeList({{ $index }})">
                                        <x-heroicon-s-minus class="h-4 w-4" />
                                    </x-button.secondary>
                                </div>
                                @endforeach
                                <x-button.primary type="button" wire:click="addList">
                                    <x-heroicon-s-plus class="h-4 w-4" />
                                </x-button.primary>
                            </div>
                            <x-input-error for="feature_list" class="mt-2" />
                        </div>

                        <!--  Feature Image -->
                        <div class="w-full pt-4 upload-section" x-data="fileUploader('feature_image')">
                            <x-label for="feature_image" :value="t('feature_image')" class="mb-2" />

                            <div class="relative p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-info-500 transition duration-300"
                                x-on:click="$refs.imagePathInput.click()">

                                <!-- Image Preview -->
                                <template x-if="preview">
                                    <div class="relative inline-block">
                                        <img :src="preview" alt="Image Preview"
                                            class="h-24 w-48 object-contain rounded-lg shadow-md" />
                                        <button type="button"
                                            class="absolute -top-4 -right-4 bg-danger-500 text-white rounded-full shadow-lg hover:bg-danger-600"
                                            x-on:click.stop="confirmDelete($event, () => { clearImage(); $wire.set('feature_image', ''); })">
                                            <x-heroicon-s-x-circle class="h-5 w-5" />
                                        </button>
                                    </div>
                                </template>

                                <!-- Show Saved Image from Database -->
                                <template x-if="!preview && '{{ $featureSettings['theme.feature_image'] }}'">
                                    <div class="relative inline-block">
                                        <img src="{{ asset('storage/' . $featureSettings['theme.feature_image']) }}"
                                            alt="Saved Image Preview"
                                            class="h-24 w-48 object-contain rounded-lg shadow-md" />
                                        <button type="button"
                                            class="absolute -top-4 -right-4 bg-danger-500 text-white rounded-full shadow-lg hover:bg-danger-600"
                                            x-on:click.stop="confirmDelete($event, () => { clearImage(); $wire.removeFeatureImage(); setTimeout(() => { location.reload(); }, 1500);})">
                                            <x-heroicon-s-x-circle class="h-5 w-5" />
                                        </button>
                                    </div>
                                </template>

                                <!-- Placeholder if No Image -->
                                <template x-if="!preview && !'{{ $featureSettings['theme.feature_image'] }}'">
                                    <div class="text-center">
                                        <x-heroicon-s-photo class="h-12 w-12 text-gray-400 mx-auto" />
                                        <p class="mt-2 text-sm text-gray-600">{!! t('select_or_browse_image') !!}</p>
                                    </div>
                                </template>

                                <!-- Progress Bar -->
                                <template x-if="isUploading">
                                    <div class="w-full mt-3">
                                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-info-500 rounded-full" :style="`width: ${progress}%`">
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center mt-1"
                                            x-text="`Uploading: ${progress}%`"></p>
                                    </div>
                                </template>

                                <!-- Hidden File Input -->
                                <input x-ref="imagePathInput" type="file" class="hidden" accept=".png,.jpg,.jpeg"
                                    x-on:change="handleFileChange" wire:model="feature_image"
                                    x-on:livewire-upload-start="uploadStarted()"
                                    x-on:livewire-upload-finish="uploadFinished()"
                                    x-on:livewire-upload-error="uploadError()"
                                    x-on:livewire-upload-progress="uploadProgress($event.detail.progress)"
                                    wire:ignore />
                            </div>

                            <!-- Error Message -->
                            @if ($errors->any())
                            <x-input-error for="feature_image" class="mt-2" />
                            @else
                            <p x-show="errorMessage" class="text-danger-500 text-sm mt-2" x-text="errorMessage"></p>
                            @endif
                        </div>

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
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                                    {{ t('delete_image') }}
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                                    {{ t('delete_image_description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="bg-gray-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end space-x-3">
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

                    </x-slot:content>

                    @if(checkPermission('admin.website_settings.edit'))
                    <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
                        <div class="flex justify-end">
                            <x-button.loading-button type="submit" target="save">{{ t('save_changes') }}
                            </x-button.loading-button>
                        </div>
                    </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>
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

    function fileUploader(initialKey) {
        return {
            key: initialKey,
            file: null,
            preview: null,
            errorMessage: '',
            imageExtensions: '.png,.jpg,.jpeg',
            isUploading: false,
            progress: 0,

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


                // Original file size check
                if (this.file.size > maxFileSize) {
                    this.errorMessage = `{{ t('file_size_exceeds') }} ${this.formatFileSize(maxFileSize)}`;
                    this.clearImage();
                    return;
                }


                // Preview the image
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

    window.addEventListener('setting-saved', (event) => {
        setTimeout(() => {
            this.location.reload();
        }, 1000);
    });
</script>