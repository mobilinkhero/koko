<div>
    <x-slot:title>
        {{ t('Partner_logo_settings') }}
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
            <form x-data="{
                hasFrontendErrors() {
                        return [...document.querySelectorAll('[x-data]')].some(el => {
                            const component = Alpine.$data(el);
                            return component?.error;
                        });
                    },
                    onSubmit() {
                        if (this.hasFrontendErrors()) {
                            alert('Please fix the upload errors before saving.');
                            return false;
                        }
                        $wire.save();
                    }
            }" @submit.prevent="onSubmit">


                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('partner_logos') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('configure_partner_logos_for_your_site') }}
                        </x-settings-description>
                    </x-slot:header>

                    <x-slot:content class="space-y-6 p-6">


                        <!-- Logo items management -->
                        <div class="grid gap-6">
                            @foreach ($logoItems as $index => $item)
                            <div class="group relative overflow-hidden transition-all duration-300  {{ $item['marked_for_removal'] ?? false ? 'ring-2 ring-danger-200 bg-gradient-to-r from-danger-50 to-danger-100/50' : 'bg-white dark:bg-slate-800/50 ' }} rounded-lg border "
                                x-data="{
                                        preview: null,
                                        error: null,
                                        existingImageUrl: '{{ isset($item['path']) ? asset('storage/' . $item['path']) : '' }}',
                                        previewImage(event) {
                                            const fileInput = event.target;
                                            const file = fileInput.files[0];
                                            this.error = null;
                                            this.preview = null;

                                            if (!file) return;

                                            // Check 1: File type
                                            if (!file.type.startsWith('image/')) {
                                                this.error = 'The file must be an image.';
                                                fileInput.value = '';
                                                return;
                                            }

                                            const fileExtension = file.name.split('.').pop().toLowerCase();
                                            if (fileExtension === 'jfif' || fileExtension === 'webp' || fileExtension === 'avif') {
                                                this.error = 'Invalid file type. Allowed: .png, .jpg, .jpeg';
                                                fileInput.value = '';
                                                return;
                                            }

                                            // Check 2: File size (<= 1MB)
                                            const maxSize = 1024 * 1024; // 1MB
                                            if (file.size > maxSize) {
                                                this.error = 'The image must not be larger than 1MB.';
                                                fileInput.value = '';
                                                return;
                                            }

                                            // Check 3: Image dimensions (≤ 800x200)
                                            const img = new Image();
                                            img.onload = () => {

                                                if (img.width > 800 || img.height > 200) {
                                                    this.error = 'The image dimensions should not exceed 800x200 pixels.';
                                                    fileInput.value = '';
                                                    return;
                                                }

                                                const reader = new FileReader();
                                                reader.onload = (e) => {
                                                    this.preview = e.target.result;
                                                };
                                                reader.readAsDataURL(file);
                                            };
                                            img.src = URL.createObjectURL(file);
                                        }
                                    }">

                                <!-- Gradient overlay for non-removed items -->
                                @if (!($item['marked_for_removal'] ?? false))
                                <div class="absolute ">
                                </div>
                                @endif

                                <div class="relative p-6">
                                    <!-- Item Header -->
                                    <div class="flex justify-between items-start mb-6">
                                        <div class="flex items-center space-x-3">
                                            @if ($item['marked_for_removal'] ?? false)
                                            <div
                                                class="w-8 h-8 bg-danger-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-danger-700 text-lg">
                                                    {{ t('logo_to_be_removed') }}
                                                </h3>
                                                <p class="text-danger-600 text-sm font-medium">#{{ $index + 1 }}
                                                </p>
                                            </div>
                                            @else
                                            <div
                                                class="w-8 h-8 bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{ $index + 1 }}</span>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-lg">
                                                    {{ t('logo') }} #{{ $index + 1 }}
                                                </h3>
                                                <p class="text-slate-500 dark:text-slate-400 text-sm">Brand
                                                    identity asset</p>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Action buttons -->
                                        <div class="flex items-center space-x-2">
                                            @if ($item['marked_for_removal'] ?? false)
                                            <button type="button" wire:click="restoreItem({{ $index }})"
                                                class="group/btn relative p-3 bg-success-100 hover:bg-success-200 text-success-700 hover:text-success-800 rounded-xl transition-all duration-200 hover:shadow-lg hover:scale-105"
                                                title="{{ t('restore') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                                <div
                                                    class="absolute -top-2 -right-2 w-3 h-3 bg-success-500 rounded-full opacity-0 group-hover/btn:opacity-100 transition-opacity">
                                                </div>
                                            </button>
                                            @else
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                class="group/btn relative p-2 bg-danger-100 hover:bg-danger-200 text-danger-600 hover:text-danger-700 rounded-md transition-all duration-200 "
                                                title="{{ t('remove') }}">
                                                <svg class="w-5 h-5 " fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>

                                            </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if (!($item['marked_for_removal'] ?? false))
                                    <div class="space-y-6">
                                        <!-- Logo Upload Section -->
                                        <div class="relative">
                                            <label for="image_{{ $index }}"
                                                class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                                <span class="flex items-center space-x-2">
                                                    <svg class="w-8 h-8 md:w-4 md:h-4 text-primary-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                        </path>
                                                    </svg>
                                                    <span>{{ t('logo_image') }}</span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                                        {{ t('recommended_size') }}: 128×36px
                                                    </span>
                                                </span>
                                            </label>


                                            <div class="relative group/upload">
                                                <input type="file" id="image_{{ $index }}"
                                                    wire:model="logoItems.{{ $index }}.image"
                                                    @change="previewImage($event)"
                                                    class="absolute inset-0 opacity-0 cursor-pointer"
                                                    accept="image/*" />

                                                <div
                                                    class="border-2 border-dashed rounded-xl p-8 text-center hover:border-info-500 ">

                                                    <div class="flex flex-col items-center space-y-3">
                                                        <div
                                                            class="w-12 h-12 bg-primary-100 dark:bg-primary-900/50 rounded-full flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-primary-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                                                Click to upload or drag and drop</p>
                                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                                PNG, JPG, JPEG up to 1MB</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- Loading state -->
                                            <div wire:loading wire:target="logoItems.{{ $index }}.image" class="mt-3">
                                                <div class="flex items-center space-x-2 text-primary-600">
                                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4">
                                                        </circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    <span class="text-sm font-medium">{{ t('uploading') }}...</span>
                                                </div>
                                            </div>
                                            <!-- Frontend dimension error -->
                                            <template x-if="error">
                                                <div class="mt-3 p-3 bg-danger-50 border border-danger-200 rounded-lg">
                                                    <span class="text-danger-700 text-sm font-medium"
                                                        x-text="error"></span>
                                                </div>
                                            </template>

                                        </div>

                                        <!-- Image Preview Section -->
                                        <div class="space-y-4">
                                            <!-- New uploaded image preview -->
                                            <template x-if="preview">
                                                <div class="relative">
                                                    <div class="flex items-center space-x-2 mb-3">
                                                        <div class="w-2 h-2 bg-success-500 rounded-full animate-pulse">
                                                        </div>
                                                        <span
                                                            class="text-sm font-semibold text-slate-700 dark:text-slate-300">New
                                                            Upload Preview</span>
                                                    </div>
                                                    <div class="relative inline-block group/preview">
                                                        <div
                                                            class="absolute inset-0 bg-gradient-to-r from-primary-500/20 to-purple-500/20 rounded-xl blur-xl opacity-0 group-hover/preview:opacity-100 transition-opacity">
                                                        </div>
                                                        <div
                                                            class="relative bg-white dark:bg-slate-800 p-4 rounded-md border-2 border-slate-200 dark:border-slate-700 ">
                                                            <img x-bind:src="preview"
                                                                class="h-20 max-w-xs object-contain" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Existing image preview -->
                                            <template
                                                x-if="!preview && existingImageUrl && '{{ isset($item['existing']) && $item['existing'] ? 'true' : 'false' }}' === 'true'">
                                                <div class="relative">
                                                    <div class="flex items-center space-x-2 mb-3">
                                                        <div class="w-2 h-2 bg-info-500 rounded-full"></div>
                                                        <span
                                                            class="text-sm font-semibold text-slate-700 dark:text-slate-300">Current
                                                            Logo</span>
                                                    </div>
                                                    <div class="relative inline-block group/preview">
                                                        <div
                                                            class="absolute inset-0 opacity-0 group-hover/preview:opacity-100 transition-opacity">
                                                        </div>
                                                        <div
                                                            class="relative bg-white dark:bg-slate-800 p-4 rounded-md border-2 border-slate-200 dark:border-slate-700 ">
                                                            <img x-bind:src="existingImageUrl"
                                                                class="h-20 max-w-xs object-contain" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                        </div>
                                    </div>
                                    @else
                                    <!-- Removal notice -->
                                    <div
                                        class="bg-gradient-to-r from-danger-50 to-danger-100/50 border border-danger-200 px-6 py-4 rounded-xl">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-6 h-6 text-danger-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            <div>
                                                <p class="font-semibold text-danger-800">Scheduled for Removal</p>
                                                <p class="text-sm text-danger-600">
                                                    {{ t('this_logo_will_be_removed_after_saving') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Add New Logo Button -->
                        <div class="flex justify-center pt-8">

                            <x-button.primary type="button" wire:click="addItem">
                                <svg class="w-6 h-6 md:w-4 md:h-4 me-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                {{ t('add_another_logo') }}
                            </x-button.primary>

                        </div>

                        <!-- Footer info -->
                        <div class="mt-12 text-center">
                            <div
                                class="inline-flex items-center space-x-2 px-4 py-2 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 rounded-lg text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ t('changes_applied_after_saving') }}</span>
                            </div>
                        </div>
                    </x-slot:content>

                    @if(checkPermission('admin.website_settings.edit'))
                    <x-slot:footer class="bg-gray-50 rounded-b-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                {{ $activeItemsCount }} {{ $activeItemsCount === 1 ? t('logo') : t('logos') }}
                                {{ t('will_be_displayed') }}
                            </div>
                            <x-button.loading-button type="submit" target="save">
                                {{ t('save_changes') }}
                            </x-button.loading-button>

                        </div>
                    </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>