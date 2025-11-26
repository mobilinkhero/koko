<div>

        <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('custom_field'), 'route' => tenant_route('tenant.custom-fields.list')],
        ['label' => $isEditMode ? t('edit_custom_field') : t('create_custom_field')]
    ]" />

    <!-- Main Content Layout -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Form Section (Main Content) -->
        <div class="flex-grow lg:w-2/3">
            <x-card>
                <x-slot:header>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('field_configuration') }}</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ t('configure_custom_field_settings') }}
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <form wire:submit="save" id="custom-field-form" class="space-y-6">
                        <!-- Field Label -->
                        <div>
                            <label for="field_label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('custom_field_name') }} <span class="text-red-500">*</span>
                            </label>
                            <x-input wire:model.live="field_label"
                                    id="field_label"
                                    type="text"
                                    placeholder="{{ t('custom_field_name_placeholder') }}"
                                    class="@error('field_label') border-red-500 @enderror" />
                            <x-input-error for="field_label" />
                        </div>

                        <!-- Field Name -->
                        <div>
                            <label for="field_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('field_name') }} <span class="text-red-500">*</span>
                            </label>
                            <x-input wire:model.live="field_name"
                                    id="field_name"
                                    type="text"
                                    placeholder="{{ t('field_name_placeholder') }}"
                                    class="@error('field_name') border-red-500 @enderror" />
                            <x-input-error for="field_name" />
                        </div>

                        <!-- Field Type -->
                        <div>
                            <label for="field_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('custom_field_type') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="field_type"
                                    id="field_type"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('field_type') border-red-500 @enderror">
                                @foreach ($this->fieldTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="field_type" />
                        </div>

                        <!-- Dropdown Options (only show for dropdown type) -->
                        @if ($field_type === 'dropdown' || $field_type === 'checkbox')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    {{ t($field_type === 'dropdown' ? 'dropdown_options' : 'checkbox_options') }} <span class="text-red-500">*</span>
                                </label>

                                <!-- Add New Option -->
                                <div class="flex space-x-2 mb-4">
                                    <x-input wire:model="newOption"
                                            wire:keydown.enter="addOption"
                                            type="text"
                                            placeholder="{{ t('enter_option_value') }}"
                                            class="flex-1 @error('newOption') border-red-500 @enderror" />
                                    <x-button.primary type="button" wire:click="addOption">
                                        {{ t('add') }}
                                    </x-button.primary>
                                </div>

                                <x-input-error for="newOption" />

                                <!-- Options List -->
                                @if (!empty($field_options))
                                    <div class="space-y-2">
                                        @foreach ($field_options as $index => $option)
                                            <div class="flex items-center space-x-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <span class="flex-1 text-sm text-gray-900 dark:text-white">{{ $option }}</span>
                                                <div class="flex space-x-1">
                                                    @if ($index > 0)
                                                        <button type="button" wire:click="moveOptionUp({{ $index }})"
                                                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                                            title="Move Up">
                                                            <x-heroicon-o-chevron-up class="w-4 h-4" />
                                                        </button>
                                                    @endif

                                                    @if ($index < count($field_options) - 1)
                                                        <button type="button" wire:click="moveOptionDown({{ $index }})"
                                                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                                            title="Move Down">
                                                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                                                        </button>
                                                    @endif

                                                    <button type="button" wire:click="removeOption({{ $index }})"
                                                        class="p-1 text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors"
                                                        title="Remove">
                                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">{{ t('no_options_added') }}</p>
                                @endif

                                <x-input-error for="field_options" />
                            </div>
                        @endif

                        <!-- Placeholder -->
                        <div>
                            <label for="placeholder" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('custom_field_placeholder') }}
                            </label>
                            <x-input wire:model="placeholder"
                                    id="placeholder"
                                    type="text"
                                    placeholder="{{ t('custom_field_placeholder_placeholder') }}"
                                    class="@error('placeholder') border-red-500 @enderror" />
                            <x-input-error for="placeholder" />
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('custom_field_description') }}
                            </label>
                            <x-textarea wire:model="description"
                                        id="description"
                                        rows="3"
                                        placeholder="{{ t('custom_field_description_placeholder') }}"
                                        class="@error('description') border-red-500 @enderror" />
                            <x-input-error for="description" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                               {{ t('optional_description_help_text') }}
                            </p>
                        </div>

                        <!-- Default Value -->
                        <div>
                            <label for="default_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('default_value') }}
                            </label>
                            @if ($field_type === 'dropdown' && !empty($field_options))
                                <select wire:model="default_value"
                                        id="default_value"
                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('default_value') border-red-500 @enderror">
                                    <option value="">{{ t('no_default_value') }}</option>
                                    @foreach ($field_options as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif ($field_type === 'checkbox' && !empty($field_options))
                                <div class="space-y-2">
                                    @foreach ($field_options as $option)
                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                   wire:model.defer="default_value.{{ $option }}"
                                                   value="1"
                                                   id="default_{{ $option }}"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                                            <label for="default_{{ $option }}" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <x-input wire:model="default_value"
                                        type="{{ $field_type === 'number' ? 'number' : ($field_type === 'date' ? 'date' : 'text') }}"
                                        id="default_value"
                                        placeholder="{{ t('enter_default_value') }}"
                                        class="@error('default_value') border-red-500 @enderror" />
                            @endif
                            <x-input-error for="default_value" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ t('default_value_help') }}
                            </p>
                        </div>

                        <!-- Checkboxes -->
                        <div class="space-y-4">
                            <!-- Required -->
                            <div class="flex items-start">
                                <input wire:model="is_required"
                                    type="checkbox"
                                    id="is_required"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded mt-1">
                                <div class="ml-3">
                                    <label for="is_required" class="block text-sm text-gray-900 dark:text-white font-medium">
                                        {{ t('custom_field_required') }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ t('make_field_mandatory') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Active -->
                            <div class="flex items-start">
                                <input wire:model="is_active"
                                    type="checkbox"
                                    id="is_active"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded mt-1">
                                <div class="ml-3">
                                    <label for="is_active" class="block text-sm text-gray-900 dark:text-white font-medium">
                                        {{ t('active_field') }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ t('only_active_fields_displayed') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Show on Table -->
                            <div class="flex items-start">
                                <input wire:model="show_on_table"
                                    type="checkbox"
                                    id="show_on_table"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded mt-1">
                                <div class="ml-3">
                                    <label for="show_on_table" class="block text-sm text-gray-900 dark:text-white font-medium">
                                        {{ t('show_on_table') }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ t('show_on_table_help') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </x-slot:content>

        <!-- Footer -->
            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button.secondary type="button" class="mx-2" wire:click="cancel">
                        {{ t('cancel') }}
                    </x-button.secondary>

                    <x-button.primary type="submit" form="custom-field-form">
                        {{ t('save') }}
                    </x-button.primary>
                </div>
            </x-slot:footer>
    </x-card>
</div>

<!-- Preview Sidebar -->
<div class="lg:w-1/3">
    <div class="lg:sticky lg:top-6">
        <!-- Field Preview Card -->
        <x-card class="h-fit">
            <x-slot:header>
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-eye class="w-5 h-5 text-gray-500" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('live_preview_custom_field') }}</h3>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ t('live_preview_description') }}
                </p>
            </x-slot:header>

            <x-slot:content>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4">
                    <!-- Preview Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ $field_label ?: t('field_label') }}
                            @if ($is_required)
                                <span class="text-red-500 ml-1">*</span>
                            @endif
                        </label>

                        @if ($field_type === 'text')
                            <x-input type="text"
                                    placeholder="{{ $placeholder ?: t('enter_text') }}"
                                    value="{{ $default_value }}"
                                    disabled
                                    class="opacity-80 cursor-not-allowed" />
                        @elseif($field_type === 'textarea')
                            <x-textarea placeholder="{{ $placeholder ?: t('enter_description') }}"
                                        disabled
                                        rows="2"
                                        class="opacity-80 cursor-not-allowed">{{ $default_value }}</x-textarea>
                        @elseif($field_type === 'number')
                            <x-input type="number"
                                    placeholder="{{ $placeholder ?: t('enter_number') }}"
                                    value="{{ $default_value }}"
                                    disabled
                                    class="opacity-80 cursor-not-allowed" />
                        @elseif($field_type === 'date')
                            <x-input type="date"
                                    value="{{ $default_value }}"
                                    disabled
                                    class="opacity-80 cursor-not-allowed" />
                        @elseif($field_type === 'dropdown')
                            <select disabled
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white opacity-80 cursor-not-allowed">
                                <option value="">{{ $placeholder ?: t('please_select') }}</option>
                                @foreach ($field_options as $option)
                                    <option value="{{ $option }}"
                                            {{ $default_value === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($field_type === 'checkbox')
                            <div class="space-y-2">
                                @if (!empty($field_options))
                                    @foreach ($field_options as $option)
                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                   disabled
                                                   {{ isset($default_value[$option]) && $default_value[$option] ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded cursor-not-allowed opacity-80">
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $option }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               disabled
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded cursor-not-allowed opacity-80">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $placeholder ?: t('checkbox_label') }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-center h-10 bg-gray-200 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                <span class="text-xs text-gray-500 dark:text-gray-400 text-center px-2">
                                    {{ t('select_field_type_preview') }}
                                </span>
                            </div>
                        @endif

                        @if ($description)
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                                <x-heroicon-o-information-circle class="w-3 h-3 inline mr-1" />
                                {{ $description }}
                            </p>
                        @endif
                    </div>
                </div>                <!-- Preview Info -->
                <div class="mt-4 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-sparkles class="w-4 h-4 mr-1" />
                    {{ t('live_preview_updates') }}
                </div>
            </x-slot:content>
        </x-card>

        <!-- Instructions Card -->
        <x-card class="mt-6">
            <x-slot:header>
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-gray-500" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('field_instructions') }}</h3>
                </div>
            </x-slot:header>

            <x-slot:content>
                <div class="space-y-6">
                    <!-- Field Name Instructions -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
                        <h4 class="flex items-center text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">
                            <x-heroicon-o-document-text class="w-5 h-5 mr-2" />
                            {{ t('field_name_guidelines') }}
                        </h4>
                        <ul class="ml-5 list-disc text-sm text-blue-700 dark:text-blue-400 space-y-1.5">
                            <li>{{ t('use_alphanumeric_characters') }}</li>
                            <li>{{ t('no_spaces_allowed') }} <code class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300">user_name</code></li>
                            <li>{{ t('underscore_recommended') }} <code class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300">first_name</code></li>
                            <li>{{ t('field_name_help') }}</li>
                        </ul>
                    </div>

                    <!-- Field Types Guide -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-100 dark:border-purple-800">
                        <h4 class="flex items-center text-sm font-medium text-purple-800 dark:text-purple-300 mb-2">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5 mr-2" />
                            {{ t('field_types_explained') }}
                        </h4>
                        <div class="space-y-3 ml-5">
                            <div class="flex items-start space-x-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-purple-400"></div>
                                <div>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ t('text_field_label') }}:</span>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ t('text_field_description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-purple-400"></div>
                                <div>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ t('textarea_field_label') }}:</span>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ t('textarea_field_description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-purple-400"></div>
                                <div>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ t('number_field_label') }}:</span>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ t('number_field_description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-purple-400"></div>
                                <div>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ t('date_field_label') }}:</span>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ t('date_field_description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-purple-400"></div>
                                <div>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ t('dropdown_field_label') }}:</span>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ t('dropdown_field_description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-purple-400"></div>
                                <div>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ t('checkbox_field_label') }}:</span>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ t('checkbox_field_description') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</div>
</div>
</div>