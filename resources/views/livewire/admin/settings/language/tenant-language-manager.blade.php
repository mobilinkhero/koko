<div class="relative">
  <x-slot:title>
    {{ t('tenant_languages') }}
  </x-slot:title>

   <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('tenant_languages')],
    ]" />

  <div class="flex justify-start mb-3 items-center gap-2">
    <x-button.primary wire:click="createLanguage">
      <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('add_tenant_language') }}
    </x-button.primary>

    <x-button.primary wire:click="syncTranslations" wire:loading.attr="disabled"
      wire:loading.class="opacity-75 cursor-not-allowed animate-pulse"
      class="transition-all duration-200 active:scale-95">
      <x-heroicon-s-arrow-path class="w-4 h-4 mr-1 transition-transform duration-500"
        wire:loading.class="animate-spin" />
      <span wire:loading.remove wire:target="syncTranslations">{{ t('sync_languages') }}</span>
      <span wire:loading wire:target="syncTranslations" class="flex items-center">
        <span class="animate-pulse">{{ t('syncing') }}</span>
        <span class="ml-1 animate-bounce">...</span>
      </span>
    </x-button.primary>
  </div>

  <x-card class="rounded-lg">
    <x-slot:content>
      <div class="mt-8 lg:mt-0" wire:poll.30s="refreshTable">
        <livewire:admin.tables.tenant-language-table />
      </div>
    </x-slot:content>
  </x-card>

  {{-- Add Tenant Language Modal --}}
  <x-modal.custom-modal :id="'showLanguageModal'" :maxWidth="'3xl'" wire:model="showLanguageModal">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30">
      <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
        {{ t('add_tenant_language') }}
      </h1>
    </div>

    <form wire:submit.prevent="save" class="mt-4">
      <div class="px-6 space-y-3">
        <div>
          <div class="flex item-center justify-start gap-1">
            <span class="text-danger-500">*</span>
            <label class="dark:text-secondary-300 block text-sm font-medium text-secondary-700">{{ t('language_name') }}</label>
          </div>
          <x-input wire:model.defer="name" type="text" id="name" class="w-full" placeholder="e.g., French" />
          <x-input-error for="name" class="mt-2" />
        </div>

        <div>
          <div class="flex item-center justify-start gap-1">
            <span class="text-danger-500">*</span>
            <label class="dark:text-secondary-300 block text-sm font-medium text-secondary-700">{{ t('language_code') }}</label>
          </div>
          <x-input wire:model.defer="code" type="text" id="code" class="w-full" placeholder="e.g., fr" />
          <x-input-error for="code" class="mt-2" />
        </div>
      </div>

      <div class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30 mt-5 px-6">
        <x-button.secondary wire:click="$set('showLanguageModal', false)">
          {{ t('cancel') }}
        </x-button.secondary>
        <x-button.loading-button type="submit" target="save">
          {{ t('submit') }}
        </x-button.loading-button>
      </div>
    </form>
  </x-modal.custom-modal>

  {{-- Edit Tenant Language Modal --}}
  <x-modal.custom-modal :id="'showEditModal'" :maxWidth="'4xl'" wire:model="showEditModal">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30">
      <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
        {{ t('edit_tenant_language') }}
      </h1>
    </div>

    <form wire:submit.prevent="updateLanguage" class="mt-4">
      <div class="px-6 space-y-6">
        {{-- Basic Language Info --}}
        <div class="space-y-3">
          <div>
            <div class="flex item-center justify-start gap-1">
              <span class="text-danger-500">*</span>
              <label class="dark:text-secondary-300 block text-sm font-medium text-secondary-700">{{ t('language_name') }}</label>
            </div>
            <x-input wire:model.defer="name" type="text" id="edit_name" class="w-full" placeholder="e.g., French" />
            <x-input-error for="name" class="mt-2" />
          </div>

          <div>
            <div class="flex item-center justify-start gap-1">
              <span class="text-danger-500">*</span>
              <label class="dark:text-secondary-300 block text-sm font-medium text-secondary-700">{{ t('language_code') }}</label>
            </div>
            <x-input wire:model.defer="code" type="text" id="edit_code" class="w-full" placeholder="e.g., fr" />
            <x-input-error for="code" class="mt-2" />
          </div>
        </div>

        {{-- Upload Section --}}
        <div class="border-t border-neutral-200 dark:border-neutral-500/30 pt-6">
          <h3 class="text-lg font-medium text-slate-800 dark:text-slate-300 mb-4">
            {{ t('upload_language_file') }}
          </h3>
          <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-4">
            Upload a JSON translation file. Files will be saved to public/lang/ directory.
          </p>

          {{-- File Name Requirement Note --}}
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
            <div class="flex items-start space-x-2">
              <x-heroicon-m-information-circle class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
              <div>
                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                  {{ t('required_file_name') ?? 'Required File Name' }}
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                  <code class="bg-blue-100 dark:bg-blue-800 px-2 py-0.5 rounded text-xs font-mono">
                    tenant_{{ $code ?? 'code' }}.json
                  </code>
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                  {{ t('file_name_must_match_pattern') ?? 'The uploaded file name must match this exact pattern.' }}
                </p>
              </div>
            </div>
          </div>

          {{-- File Upload Area --}}
          <div class="space-y-4">
            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">
              {{ t('select_file') }} (Optional)
            </label>

            {{-- Drag & Drop Zone --}}
            <div
              x-data="{ isDragging: false }"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="isDragging = false; $refs.editFileInput.files = $event.dataTransfer.files; $refs.editFileInput.dispatchEvent(new Event('change', { bubbles: true }));"
              :class="{ 'border-primary-400 bg-primary-50 dark:bg-primary-900/20': isDragging }"
              class="relative border-2 border-dashed border-secondary-300 dark:border-secondary-600 rounded-lg p-6 text-center hover:border-primary-400 transition-colors duration-200"
            >
              @if($editFilePreview)
                {{-- File Preview --}}
                <div class="space-y-3">
                  <div class="flex items-center justify-center">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                      <x-heroicon-s-document-text class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                  </div>

                  <div class="space-y-2">
                    <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">{{ $editFilePreview['name'] }}</p>
                    <p class="text-xs text-secondary-500 dark:text-secondary-400">{{ $editFilePreview['size'] }}</p>

                    @if(isset($editFilePreview['key_count']) && $editFilePreview['is_valid_json'])
                      <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                        <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                        {{ $editFilePreview['key_count'] }} keys
                      </div>

                      @if(!empty($editFilePreview['sample_keys']))
                        <div class="text-xs text-secondary-600 dark:text-secondary-400">
                          {{ t('sample_keys') . ':' . implode(', ', $editFilePreview['sample_keys']) }}...
                        </div>
                      @endif
                    @elseif(isset($editFilePreview['error']))
                      <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/50 dark:text-danger-300">
                        <x-heroicon-m-x-circle class="w-3 h-3 mr-1" />
                        {{ $editFilePreview['error'] }}
                      </div>
                    @endif
                  </div>

                  <button
                    type="button"
                    wire:click="resetEditUploadState"
                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                  >
                    {{ t('choose_different_file')  }}
                  </button>
                </div>
              @else
                {{-- Upload Prompt --}}
                <div class="space-y-3">
                  <div class="flex items-center justify-center">
                    <div class="w-12 h-12 bg-secondary-100 dark:bg-secondary-800 rounded-lg flex items-center justify-center">
                      <x-heroicon-o-cloud-arrow-up class="w-6 h-6 text-secondary-400" />
                    </div>
                  </div>

                  <div class="space-y-1">
                    <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">
                      {{ t('drag_and_drop_file')}}
                    </p>
                    <p class="text-xs text-secondary-500 dark:text-secondary-400">
                      {{ t('upload_maximum_file_size') }} <code class="bg-secondary-100 dark:bg-secondary-700 px-1 rounded">tenant_{{ $code ?? 'code' }}.json</code>
                    </p>
                    </p>
                  </div>
                </div>
              @endif

              {{-- Hidden File Input --}}
              <input
                type="file"
                wire:model="editUploadFile"
                accept=".json,application/json"
                x-ref="editFileInput"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
              >
            </div>

            <x-input-error for="editUploadFile" class="mt-2" />
          </div>

          {{-- Upload Progress --}}
          @if($editIsUploading)
            <div class="space-y-2 mt-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-secondary-600 dark:text-secondary-400">Uploading...</span>
                <span class="text-secondary-600 dark:text-secondary-400">{{ $editUploadProgress }}%</span>
              </div>
              <div class="w-full bg-secondary-200 rounded-full h-2 dark:bg-secondary-700">
                <div
                  class="bg-primary-600 h-2 rounded-full transition-all duration-300 ease-out"
                  style="width: {{ $editUploadProgress }}%"
                ></div>
              </div>
            </div>
          @endif

          {{-- Validation Results --}}
          @if($editUploadResults && !$editIsUploading)
            <div class="mt-4 p-4 {{ $editUploadResults['valid'] ? 'bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800' : 'bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800' }} rounded-lg">
              <div class="flex items-start space-x-3">
                @if($editUploadResults['valid'])
                  <x-heroicon-s-check-circle class="w-5 h-5 text-success-500 mt-0.5" />
                  <div class="space-y-2">
                    <p class="text-sm font-medium text-success-700 dark:text-success-300">
                      {{ t('file_validation_successful') }}
                    </p>
                    <div class="text-xs text-success-600 dark:text-success-400 space-y-1">
                      <p>• {{ $editUploadResults['key_count'] }} {{ t('translation_keys_found') }}</p>
                      <p>• {{ t('file_size') }}: {{ number_format($editUploadResults['file_size'] / 1024, 2) }} KB</p>
                    </div>

                    <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded">
                      <p class="text-xs text-blue-700 dark:text-blue-300">
                        <x-heroicon-m-information-circle class="w-3 h-3 inline mr-1" />
                        Ready to upload to public/lang/ directory. Existing files will be backed up.
                      </p>
                    </div>
                  </div>
                @else
                  <x-heroicon-s-x-circle class="w-5 h-5 text-danger-500 mt-0.5" />
                  <div>
                    <p class="text-sm font-medium text-danger-700 dark:text-danger-300">
                      {{ t('file_validation_failed') }}
                    </p>
                    <div class="text-xs text-danger-600 dark:text-danger-400 mt-1 space-y-1">
                      @foreach($editUploadResults['errors'] as $error)
                        <p>• {{ $error }}</p>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </div>
          @endif
        </div>
      </div>

      <div class="py-4 flex justify-end items-center border-t border-neutral-200 dark:border-neutral-500/30 mt-6 px-6">
        <div class="flex items-center space-x-3">
          <x-button.secondary wire:click="$set('showEditModal', false)">
            {{ t('cancel') }}
          </x-button.secondary>

          @if($editUploadResults && $editUploadResults['valid'])
            <x-button.primary
              type="button"
              wire:click="processEditUpload"
              :disabled="$editIsUploading"
              wire:loading.attr="disabled"
              wire:loading.class="opacity-75 cursor-not-allowed"
              class="inline-flex items-center justify-center min-w-[120px]"
            >
              <span wire:loading.remove wire:target="processEditUpload" class="inline-flex items-center">
                <x-heroicon-m-arrow-up-tray class="w-4 h-4 mr-1.5" />
                {{ t('upload_file') }}
              </span>
              <span wire:loading wire:target="processEditUpload" class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ t('uploading') }}...
              </span>
            </x-button.primary>
          @endif

          <x-button.loading-button
            type="submit"
            target="updateLanguage"
            class="min-w-[100px] justify-center"
          >
            {{ t('update_button') }}
          </x-button.loading-button>
        </div>
      </div>
    </form>
  </x-modal.custom-modal>

  {{-- Delete Language Modal --}}
  <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-language-modal'" title="{{ t('delete_tenant_language') }}"
    wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }}">
    <div class="flex justify-end items-center space-x-3 bg-secondary-100 dark:bg-secondary-700">
      <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
        {{ t('cancel') }}
      </x-button.cancel-button>
      <x-button.delete-button wire:click.debounce.200="delete" class="mt-3 sm:mt-0">
        {{ t('delete') }}
      </x-button.delete-button>
    </div>
  </x-modal.confirm-box>

</div>
