<div x-data="{
    view: localStorage.getItem('contacts_view') || 'list',
    init() {
      // Persist to localStorage
      this.$watch('view', v => localStorage.setItem('contacts_view', v));

      // Init tooltip on the stable element (the button)
      if (window.tippy && $refs.viewToggle) {
        tippy($refs.viewToggle, { allowHTML: true, content: this.view === 'list' ? '{{ t('swith_to_kanban') }}' : '{{ t('swith_to_list') }}' });
      }
    }
  }" x-init="init()" class="relative space-y-4">
  <x-slot:title>
    {{ t('contact') }}
  </x-slot:title>

       <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('contact')],
    ]" />

  <div class="flex flex-col sm:flex-row justify-between items-start lg:items-center gap-2">
    <div class="flex flex-col sm:flex-row justify-between items-start gap-2 mb-3 lg:mb-2">
      @if (checkPermission('tenant.contact.create'))
      <x-button.primary href="{{ tenant_route('tenant.contacts.save') }}" wire:click="createContact">
        <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('new_contact_button') }}
      </x-button.primary>
      @endif

      @if (checkPermission('tenant.contact.bulk_import'))
      <x-button.primary href="{{ tenant_route('tenant.contacts.import_log') }}" wire:click="importContact">
        <x-heroicon-m-plus class="w-4 h-4 mr-1" />{{ t('import_contact') }}
      </x-button.primary>
      @endif

      <!-- View toggle -->
      <div class="flex justify-end relative group">
        <button x-ref="viewToggle" @click="
            view = (view === 'list') ? 'kanban' : 'list';
            $nextTick(() => { if ($refs.viewToggle && $refs.viewToggle._tippy) { $refs.viewToggle._tippy.setContent(view === 'list' ? '{{ t('swith_to_kanban') }}' : '{{ t('swith_to_list') }}'); }});
          " class="inline-flex items-center justify-center px-4 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition text-white bg-primary-600 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          aria-label="{{ t('toggle_view') }}">
          <!-- List Icon -->
          <x-heroicon-o-bars-3 x-show="view === 'list'" x-cloak class="w-5 h-5 text-white dark:text-gray-300" />
          <!-- Kanban Icon -->
          <x-heroicon-o-view-columns x-show="view === 'kanban'" x-cloak
            class="w-5 h-5 text-white dark:text-gray-300" />
        </button>
        <!-- CSS Tooltip -->
        <div
          class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
          <span
            x-text="view === 'list' ? '{{ t('switch_to_kanban') ?? 'Switch to Kanban' }}' : '{{ t('switch_to_list') ?? 'Switch to List' }}'"></span>
          <!-- Tooltip Arrow -->
          <div
            class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-900 dark:border-t-gray-700">
          </div>
        </div>
      </div>
    </div>

    <!-- Feature Limit Badge -->
    <div class="mb-2">
      @if (isset($this->isUnlimited) && $this->isUnlimited)
      <x-unlimited-badge>{{ t('unlimited') }}</x-unlimited-badge>
      @elseif(isset($this->remainingLimit) && isset($this->totalLimit))
      <x-remaining-limit-badge label="{{ t('remaining') }}" :value="$this->remainingLimit" :count="$this->totalLimit" />
      @endif
    </div>
  </div>

  <x-card x-show="view === 'list'" class="rounded-lg">
    <x-slot:content>
      <!-- List View -->
      <div x-show="view === 'list'" x-cloak class="lg:mt-0" wire:poll.30s="refreshTable">
        <livewire:tenant.tables.contact-table />
      </div>
    </x-slot:content>
  </x-card>
  <div x-show="view === 'kanban'" x-cloak class="lg:mt-0">
    {{-- Use a stable key to avoid remounts on every render --}}
    <livewire:tenant.contact.contact-kanban :key="'kanban'" />
  </div>
  <!-- Delete Confirmation Modal -->
  <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-contact-modal'" title="{{ t('delete_contact_title') }}"
    wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
    <div
      class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
      <x-button.cancel-button wire:click="$set('confirmingDeletion', false)" class="">
        {{ t('cancel') }}
      </x-button.cancel-button>
      <x-button.delete-button wire:click="delete" class="mt-3 sm:mt-0">
        {{ t('delete') }}
      </x-button.delete-button>
    </div>
  </x-modal.confirm-box>

  {{-- View Contact Modal --}}
  <x-modal.custom-modal :id="'view-contact-modal'" :maxWidth="'5xl'" wire:model.defer="viewContactModal">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 flex justify-between">
      <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
        {{ $contact ? "#{$contact->id} - {$contact->firstname} {$contact->lastname}" : t('contact_details') }}
      </h1>
      <button class="text-gray-500 hover:text-gray-700 text-2xl dark:hover:text-gray-300"
        wire:click="$set('viewContactModal', false)">
        &times;
      </button>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'profile' }">
      <div
        class="bg-gray-100 border-b border-neutral-200 dark:bg-gray-800 dark:border-neutral-500/30 gap-2 grid  grid-cols-3 mt-5 mx-5 px-2 py-1.5 rounded-md">

        <!-- Profile Tab -->
        <button class="px-4 py-2 text-sm font-medium rounded-md flex items-center justify-center space-x-2" :class="activeTab === 'profile'
                        ?
                        'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400' :
                        'text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400'"
          x-on:click="activeTab = 'profile'">
          <x-heroicon-o-user class="hidden md:inline w-6 h-6" />
          <span> {{ t('profile') }} </span>
        </button>

        <!-- Other Information Tab -->
        <button class="px-4 py-2 text-sm font-medium rounded-md flex items-center justify-center space-x-2" :class="activeTab === 'other'
                        ?
                        'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400' :
                        'text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400'"
          x-on:click="activeTab = 'other'">
          <x-heroicon-o-information-circle class="hidden md:inline w-6 h-6" />
          <span> {{ t('other_information_contact') }} </span>
        </button>

        <!-- Notes Tab -->
        <button class="px-4 py-2 text-sm font-medium rounded-md flex items-center justify-center space-x-2" :class="activeTab === 'notes'
                        ?
                        'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400' :
                        'text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400'"
          x-on:click="activeTab = 'notes'">
          <x-heroicon-o-document-text class="hidden md:inline w-6 h-6" />
          <span> {{ t('notes_title') }} </span>
        </button>
      </div>

      <div class="p-4">
        <div x-show="activeTab === 'profile'">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4 p-4 rounded-lg break-words">
            <div class="space-y-4">
              <!-- Name -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400">{{ t('name') }}</span>
                <p class="text-sm text-slate-700 dark:text-slate-300 tesxt-wrap">
                  {{ $contact ? "{$contact->firstname} {$contact->lastname}" : '-' }}
                </p>
              </div>

              <!-- Status -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('status') }}
                </span>
                <div>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    style="background-color: {{ $contact->status->color ?? '#ccc' }}20; color: {{ $contact->status->color ?? '#333' }};">
                    {{ $contact->status->name ?? '-' }}
                  </span>
                </div>
              </div>

              <!-- Source -->
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400"> {{ t('source') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ $contact->source->name ?? '-' }}</p>
              </div>

              <!-- Assigned -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('assigned') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ $contact && $contact->user ? "{$contact->user->firstname}
                  {$contact->user->lastname}" : '-' }}
                </p>
              </div>

              <!-- Company -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('company') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ isset($contact) && $contact->company ? $contact->company : '-' }}
                </p>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Type -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('type') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ ucfirst($contact->type ?? '-') }}</p>
              </div>

              <!-- Email -->
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400"> {{ t('email') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300 ">
                  {{ isset($contact) && $contact->email ? $contact->email : '-' }}</p>
              </div>

              <!-- Phone -->
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400">{{ t('phone') }}</span>
                <p>
                  <a href='tel:{{ $contact->phone ?? ' -' }}' class="text-info-600 text-sm">
                    {{ $contact->phone ?? '-' }}
                  </a>
                </p>
              </div>

              <!-- Website -->
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400"> {{ t('website') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ isset($contact) && $contact->website ? $contact->website : '-' }}</p>

              </div>

            </div>
          </div>

          <!-- Custom Fields Section -->
          @if($customFields && $customFields->count() > 0)
          <div class="mt-4 pt-6 border-t border-gray-200 dark:border-gray-600 px-4">
            <div class="items-center px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 mb-4 text-center">
              <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ t('custom_fields') }}</h3>
            </div>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 break-words">
              @foreach($customFields as $customFieldData)
                @php
                  $field = $customFieldData['field'];
                  $value = $customFieldData['display_value'];
                @endphp
                <div>
                  <span class="text-sm text-slate-400 dark:text-slate-400">{{ $field->field_label }}</span>
                  <p class="text-sm text-slate-700 dark:text-slate-300 break-words">
                    @if($field->field_type === 'checkbox')
                      @if($value)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                          {{ t('yes') }}
                        </span>
                      @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                          {{ t('no') }}
                        </span>
                      @endif
                    @elseif($field->field_type === 'date' && $value)
                      {{ \Carbon\Carbon::parse($value)->format('M d, Y') }}
                    @elseif($value)
                      {{ $value }}
                    @else
                      <span class="text-gray-400 dark:text-gray-500">-</span>
                    @endif
                  </p>
                </div>
              @endforeach
            </div>
          </div>
          @endif
        </div>

        <div x-show="activeTab === 'other'">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4 p-4 rounded-lg">
            <div class="space-y-4">
              <!-- City -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('city') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ isset($contact) && $contact->city ? $contact->city : '-' }}
                </p>
              </div>

              <!-- State -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('state') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ isset($contact) && $contact->state ? $contact->state : '-' }}
                </p>
              </div>

              <!-- Country -->
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400"> {{ t('country') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ get_country_name($contact->country_id) ? get_country_name($contact->country_id) :
                  '-' }}
                </p>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Zip Code -->
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400"> {{ t('zip_code') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                  {{ isset($contact) && $contact->zip ? $contact->zip : '-' }}
                </p>
              </div>
              <div>
                <span class=" text-sm text-slate-400 dark:text-slate-400"> {{ t('description') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300 break-words">
                  {{ isset($contact) && $contact->description ? $contact->description : '-' }}
                </p>
              </div>

              <!-- Address -->
              <div>
                <span class="text-sm text-slate-400 dark:text-slate-400"> {{ t('address') }}
                </span>
                <p class="text-sm text-slate-700 dark:text-slate-300 break-words">
                  {{ isset($contact) && $contact->address ? $contact->address : '-' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div x-show="activeTab === 'notes'">
          <div class="col-span-1">
            <div>
              <div
                class="mt-4 relative px-4 h-80 overflow-y-auto scrollbar-thin scrollbar-track-gray-200 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
                <ol class="relative border-s border-gray-300 dark:border-gray-700">
                  @forelse($notes as $note)
                  <li class="mb-6 ms-4 relative">
                    <div class="absolute w-2 h-2 bg-primary-600 dark:bg-primary-400 rounded-full -left-5 top-4">
                    </div>

                    <div class="flex-1 p-2 border-b border-gray-300 dark:border-gray-600 text-sm space-y-1">

                      <span class="text-xs text-gray-500 dark:text-gray-400 block relative"
                        data-tippy-content="{{ format_date_time($note['created_at']) }}"
                        style="cursor: pointer; display: inline-block; text-decoration: underline dotted;">
                        {{ \Carbon\Carbon::parse($note['created_at'])->diffForHumans(['options'
                        => \Carbon\Carbon::JUST_NOW]) }}
                      </span>
                      <div class="flex justify-between items-start flex-nowrap">
                        <span class="text-gray-800 dark:text-gray-200 flex-1">
                          {{ $note['notes_description'] }}
                        </span>
                      </div>
                    </div>
                  </li>
                  @empty
                  <p class="text-gray-500 dark:text-gray-400 text-center">
                    {{ t('no_notes_available') }} </p>
                  @endforelse
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </x-modal.custom-modal>
  <!-- intial chat Modal -->
  <div x-data="{
      modalSize: 'max-w-6xl',
      isOpen: @entangle('showInitiateChatModal'),
      campaignsSelected: false,
      fileError: null,
      isDisabled: false,
      campaignHeader: '',
      isSaving: false,
      campaignBody: '',
      campaignFooter: '',
      buttons: [],
      inputType: 'text',
      inputAccept: '',
      headerInputs: @entangle('headerInputs'),
      bodyInputs: @entangle('bodyInputs'),
      footerInputs: @entangle('footerInputs'),
      mergeFields: @entangle('mergeFields'),
      editTemplateId: @entangle('template_id'),
      headerInputErrors: [],
      bodyInputErrors: [],
      footerInputErrors: [],
      headerParamsCount: 0,
      bodyParamsCount: 0,
      footerParamsCount: 0,
      selectedCount: 0,
      relType: '',
      previewUrl: '{{ !empty($filename) ? asset('storage/' . $filename) : '' }}', // Added for preview
      previewType: '', // Store file type (image, video, document)
      previewFileName: '{{ !empty($filename) ? basename($filename) : '' }}',
      filteredContacts: @entangle('contacts'),
      metaExtensions: {{ json_encode(get_meta_allowed_extension()) }},
      isUploading: false,
      progress: 0,
      resetModal() {
          // Reset all preview and form data
          if (document.getElementById('basic-select')) {
              document.getElementById('basic-select').value = '';
          }
          // Reset Alpine data
          this.previewUrl = '';
          this.previewFileName = '';
          this.fileError = null;
          this.campaignsSelected = false;
          this.campaignHeader = '';
          this.campaignBody = '';
          this.campaignFooter = '';
          this.buttons = [];
          this.headerInputs = [];
          this.bodyInputs = [];
          this.footerInputs = [];

          // Reset Livewire data
          @this.set('template_id', '');
          @this.set('headerInputs', []);
          @this.set('bodyInputs', []);
          @this.set('footerInputs', []);
          @this.set('file', null);
          @this.set('filename', null);
      },
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
          }, 2000);
      },
      initTribute() {
          this.$watch('mergeFields', (newValue) => {
              this.handleTributeEvent();
          });
          this.handleTributeEvent();
      },
      handleCampaignChange(event) {
          const selectedOption = event.target.selectedOptions[0];
          this.campaignsSelected = event.target.value !== '';
          this.campaignHeader = selectedOption?.dataset.header || '';
          this.campaignBody = selectedOption?.dataset.body || '';
          this.campaignFooter = selectedOption?.dataset.footer || '';
          this.buttons = selectedOption ? JSON.parse(selectedOption.dataset.buttons || '[]') : [];
          this.inputType = selectedOption?.dataset.headerFormat || 'text';
          this.headerParamsCount = parseInt(selectedOption?.dataset.headerParamsCount || 0);
          this.bodyParamsCount = parseInt(selectedOption?.dataset.bodyParamsCount || 0);
          this.footerParamsCount = parseInt(selectedOption?.dataset.footerParamsCount || 0);

          if (!selectedOption || !this.previewUrl.includes('{{ $filename ?? '' }}')) {
              this.previewUrl = '';
              this.previewFileName = '';
          }

          const format = selectedOption?.dataset.headerFormat || 'text';
          this.inputAccept = this.metaExtensions[format.toLowerCase()]?.extension || '';

          if (selectedOption?.value != this.editTemplateId) {
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
              const index = parseInt(p1, 10) - 1;
              return `<span class='text-indigo-600'>${inputs[index] || match}</span>`;
          });
      },
      handleFilePreview(event) {
          const file = event.target.files[0];
          this.fileError = null; // Clear previous errors

          if (!file) {
              return;
          }

          // Get allowed extensions and max size from metaExtensions
          const typeKey = this.inputType.toLowerCase(); // Convert to lowercase for consistency
          const metaData = this.metaExtensions[typeKey];


          const allowedExtensions = metaData.extension.split(',').map(ext => ext.trim());
          const maxSizeMB = metaData.size || 0; // Default to 0 if not set
          const maxSizeBytes = maxSizeMB * 1024 * 1024; // Convert MB to bytes

          // Extract file extension
          const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

          // Validate file extension (from metaExtensions)
          if (!allowedExtensions.includes(fileExtension)) {
              this.fileError = `Invalid file type. Allowed types: ${allowedExtensions.join(', ')}`;
              return;
          }

          // MIME type validation (strict check)
          const fileType = file.type.split('/')[0];

          if (this.inputType === 'DOCUMENT' && !['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain'].includes(file.type)) {
              this.fileError = 'Invalid document type. Please upload a valid document.';
              return;
          }

          if (this.inputType === 'IMAGE' && !file.type.startsWith('image/')) {
              this.fileError = 'Invalid image file. Please upload an image.';
              return;
          }

          if (this.inputType === 'VIDEO' && !file.type.startsWith('video/')) {
              this.fileError = 'Invalid video file. Please upload a video.';
              return;
          }

          if (this.inputType === 'AUDIO' && !file.type.startsWith('audio/')) {
              this.fileError = 'Invalid audio file. Please upload an audio file.';
              return;
          }

          if (this.inputType === 'STICKER' && file.type !== 'image/webp') {
              this.fileError = 'Invalid sticker file. Only .webp format is allowed.';
              return;
          }

          // Validate file size
          if (file.size > maxSizeBytes) {
              this.fileError = `File size exceeds ${maxSizeMB} MB. Please upload a smaller file.`;
              return;
          }

          // If validation passes, handle the file preview
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
          setTimeout(() => {
              @this.set('template_id', '');
              @this.set('headerInputs', []);
              @this.set('bodyInputs', []);
              @this.set('footerInputs', []);
              @this.set('file', null);
              @this.set('filename', null);
          }, 500)


      }

   }" x-init="$nextTick(() => {
      const select = $el.querySelector('#basic-select');

      if (select?.value) {
          handleCampaignChange({ target: select });
      }
   })" x-on:open-modal.window="isOpen = true" x-on:keydown.escape.window="isOpen = false;resetModal()"
    x-effect="modalSize = campaignsSelected ? 'max-w-6xl' : 'max-w-2xl'">
    <template x-if="isOpen">
      <div class="fixed inset-0 z-50 overflow-y-auto" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <!-- Backdrop with Gradient - with click handler to close modal -->
        <div class="fixed inset-0 backdrop-blur-sm bg-gradient-to-br from-black/30 to-black/60"
          x-on:click="isOpen = false;resetModal()" x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0">
        </div>

        <!-- Modal Container with Animation - slide from top -->
        <div class="flex items-start justify-center p-4 pt-20">
          <div x-show="isOpen" @click.stop x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-10"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-10" :class="modalSize"
            class="relative w-full overflow-hidden rounded-2xl bg-white/95 dark:bg-slate-800/95 shadow-2xl ring-1 ring-black/5 dark:ring-white/5 transition-all duration-300">
            <!-- Added transition -->

            <!-- Gradient Background Accent -->
            <div
              class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 via-transparent to-purple-50/50 dark:from-indigo-900/10 dark:to-purple-900/10">
            </div>

            <!-- Content Container -->
            <div class="relative">
              <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30 flex justify-between">
                <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                  {{ t('initiate_chat') }}
                </h1>

                <button class="text-gray-500 hover:text-gray-700 text-2xl dark:hover:text-gray-300"
                  x-on:click="isOpen = false;resetModal()">
                  &times;
                </button>
              </div>
              <div class="px-6 py-4">
                <form wire:submit.prevent="save">
                  <!-- Template selection first - always visible -->
                  <div class="mb-6">
                    <div class="flex item-centar justify-start">
                      <span class="text-red-500 me-1 ">*</span>
                      <x-label for="template_id" :value="t('template')" />
                    </div>
                    <div wire:ignore x-cloak>
                      <x-select id="basic-select" class="tom-select mt-1 block w-full " wire:model.defer="template_id"
                        x-ref="campaignsChange" x-on:change="handleCampaignChange({ target: $refs.campaignsChange });"
                        x-init="() => {
                            handleCampaignChange({ target: $refs.campaignsChange });
                        }">
                        <option value="" selected>{{ t('nothing_selected') }}
                        </option>
                        @foreach ($this->templates as $template)
                        <option value="{{ $template['template_id'] }}" data-header="{{ $template['header_data_text'] }}"
                          data-body="{{ $template['body_data'] }}" data-footer="{{ $template['footer_data'] }}"
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

                  <!-- Two-column layout when template is selected -->
                  <div x-show="campaignsSelected" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left column: Variables -->
                    <div>
                      <x-card class="rounded-lg">
                        <x-slot:header>
                          <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('variables') }}
                          </h1>
                        </x-slot:header>
                        <x-slot:content>
                          <div>
                            <!-- Alert for missing variables -->
                            <div
                              x-show="((inputType == 'TEXT' || inputType == '') && headerParamsCount === 0) && bodyParamsCount === 0 && footerParamsCount === 0"
                              class="bg-red-100 border-l-4 rounded border-red-500 text-red-800 px-2 py-3 dark:bg-gray-800 dark:border-red-800 dark:text-red-300"
                              role="alert">
                              <div class="flex justify-start items-center gap-2">
                                <p class="font-base text-sm">
                                  {{ t('variable_not_available_for_this_template') }}
                                </p>
                              </div>
                            </div>

                            {{-- Header section --}}
                            <div x-show="inputType !== 'TEXT' || headerParamsCount > 0">
                              <div class="flex items-center justify-start">
                                <label for="dynamic_input" class="block font-medium text-slate-700 dark:text-slate-200">
                                  <template x-if="inputType == 'TEXT' && headerParamsCount > 0">
                                    <span class="text-lg font-semibold">{{ t('header') }}</span>
                                  </template>
                                  <template x-if="inputType == 'IMAGE'">
                                    <span class="text-lg font-semibold">{{ t('image') }}</span>
                                  </template>
                                  <template x-if="inputType == 'DOCUMENT'">
                                    <span class="text-lg font-semibold">{{ t('document') }}</span>
                                  </template>
                                  <template x-if="inputType == 'VIDEO'">
                                    <span class="text-lg font-semibold">{{ t('video') }}</span>
                                  </template>
                                </label>
                              </div>

                              <div>
                                <!-- Standard Input with Tailwind CSS -->
                                <template x-if="inputType == 'TEXT'">
                                  <template x-for="(value, index) in headerParamsCount" :key="index">
                                    <div class="mt-2">
                                      <div class="flex justify-start gap-1">
                                        <span class="text-red-500">*</span>
                                        <label :for="'header_name_' + index"
                                          class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                          {{ t('variable') }} <span x-text="index + 1"></span>
                                        </label>
                                      </div>
                                      <input x-bind:type="inputType" :id="'header_name_' + index"
                                        x-model="headerInputs[index]" x-init="initTribute()"
                                        class="mentionable block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:placeholder-slate-600"
                                        autocomplete="off" />
                                      <p x-show="headerInputErrors[index]" x-text="headerInputErrors[index]"
                                        class="text-red-500 text-sm mt-1"></p>
                                    </div>
                                  </template>
                                </template>
                                @if ($errors->has('headerInputs.*'))
                                <x-dynamic-alert type="danger" :message="$errors->first('headerInputs.*')" class="mt-4">
                                </x-dynamic-alert>
                                @endif

                                <!-- File upload sections -->
                                <!-- For DOCUMENT input type (file upload) -->
                                <template x-if="inputType == 'DOCUMENT'">
                                  <div>
                                    <label for="document_upload"
                                      class="block text-sm font-medium text-gray-800 dark:text-gray-300">
                                      {{ t('select_document') }}
                                      <span x-text="metaExtensions.document.extension"></span>
                                    </label>

                                    <div
                                      class="relative mt-1 p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition duration-300"
                                      x-on:click="$refs.documentUpload.click()">
                                      <div class="text-center">
                                        <x-heroicon-s-photo class="h-12 w-12 text-gray-400 mx-auto" />
                                        <p class="mt-2 text-sm text-gray-600">
                                          {{ t('select_or_browse_to') }}
                                          <span class="text-blue-600 underline">{{ t('document') }}</span>
                                        </p>
                                      </div>
                                      <input type="file" x-ref="documentUpload" id="document_upload"
                                        x-bind:accept="inputAccept" wire:model="file"
                                        x-on:change="handleFilePreview($event)" class="hidden" />
                                    </div>
                                    <template x-if="fileError">
                                      <p class="text-red-500 text-sm mt-2" x-text="fileError"></p>
                                    </template>
                                  </div>
                                </template>

                                <!-- For IMAGE input type (image file upload) -->
                                <template x-if="inputType === 'IMAGE'">
                                  <div>
                                    <label for="image_upload"
                                      class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                      {{ t('select_image') }}
                                      <span x-text="metaExtensions.image.extension"></span>
                                    </label>
                                    <div
                                      class="relative mt-1 p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition duration-300"
                                      x-on:click="$refs.imageUpload.click()">
                                      <div class="text-center">
                                        <x-heroicon-s-photo class="h-12 w-12 text-gray-400 mx-auto" />
                                        <p class="mt-2 text-sm text-gray-600">
                                          {{ t('select_or_browse_to') }}
                                          <span class="text-blue-600 underline">{{ t('image') }}</span>
                                        </p>
                                      </div>
                                      <input type="file" id="image_upload" x-ref="imageUpload"
                                        x-bind:accept="inputAccept" wire:model="file"
                                        x-on:change="handleFilePreview($event)" class="hidden"
                                        x-on:livewire-upload-start="uploadStarted()"
                                        x-on:livewire-upload-finish="uploadFinished()"
                                        x-on:livewire-upload-error="isUploading = false"
                                        x-on:livewire-upload-progress="updateProgress($event.detail.progress)" />
                                    </div>

                                    @if ($errors->has('file'))
                                    <x-input-error class="mt-2" for="file" />
                                    @else
                                    <template x-if="fileError">
                                      <p class="text-red-500 text-sm mt-2" x-text="fileError">
                                      </p>
                                    </template>
                                    @endif
                                  </div>
                                </template>

                                <!-- For VIDEO input type (video file upload) -->
                                <template x-if="inputType == 'VIDEO'">
                                  <div>
                                    <label for="video_upload"
                                      class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                      {{ t('select_video') }}
                                    </label>
                                    <span x-text="metaExtensions.video.extension"></span>
                                    <div
                                      class="relative mt-1 p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition duration-300"
                                      x-on:click="$refs.videoUpload.click()">
                                      <div class="text-center">
                                        <x-heroicon-s-photo class="h-12 w-12 text-gray-400 mx-auto" />
                                        <p class="mt-2 text-sm text-gray-600">
                                          {{ t('select_or_browse_to') }}
                                          <span class="text-blue-600 underline">{{ t('video') }}</span>
                                        </p>
                                      </div>
                                      <input type="file" id="video_upload" x-ref="videoUpload"
                                        x-bind:accept="inputAccept" wire:model.defer="file"
                                        x-on:change="handleFilePreview($event)" class="hidden" />
                                    </div>
                                    <template x-if="fileError">
                                      <p class="text-red-500 text-sm mt-2" x-text="fileError"></p>
                                    </template>
                                  </div>
                                </template>
                                <div x-show="isUploading" class="relative mt-2">
                                  <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                      :style="'width: ' + progress + '%'"></div>
                                  </div>
                                </div>
                              </div>
                            </div>

                            {{-- Body section --}}
                            <div x-show="bodyParamsCount > 0">
                              <div class="flex items-center justify-start mt-4">
                                <label for="dynamic_input" class="block font-medium text-slate-700 dark:text-slate-200">
                                  <span class="text-lg font-semibold">{{ t('body') }}</span>
                                </label>
                              </div>

                              <div>
                                <template x-for="(value, index) in bodyParamsCount" :key="index">
                                  <div class="mt-2">
                                    <div class="flex justify-start gap-1">
                                      <span class="text-red-500">*</span>
                                      <label :for="'body_name_' + index"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('variable') }} <span x-text="index + 1"></span>
                                      </label>
                                    </div>
                                    <input type="text" :id="'body_name_' + index" x-model="bodyInputs[index]"
                                      x-init='initTribute()'
                                      class="mentionable block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:placeholder-slate-600"
                                      autocomplete="off" />
                                    <p x-show="bodyInputErrors[index]" x-text="bodyInputErrors[index]"
                                      class="text-red-500 text-sm mt-1"></p>
                                  </div>
                                </template>
                                @if ($errors->has('bodyInputs.*'))
                                <x-dynamic-alert type="danger" :message="$errors->first('bodyInputs.*')" class="mt-4">
                                </x-dynamic-alert>
                                @endif
                              </div>
                            </div>

                            {{-- Footer section --}}
                            <div x-show="footerParamsCount > 0">
                              <div
                                class="text-gray-600 dark:text-gray-400 border-b mt-6 mb-4 border-gray-300 dark:border-gray-600">
                              </div>

                              <div class="flex items-center justify-start">
                                <label for="dynamic_input" class="block font-medium text-slate-700 dark:text-slate-200">
                                  <span class="text-lg font-semibold">{{ t('footer') }}</span>
                                </label>
                              </div>

                              <div>
                                <template x-for="(value, index) in footerInputs" :key="index">
                                  <div class="mt-2">
                                    <div class="flex justify-start gap-1">
                                      <span class="text-red-500">*</span>
                                      <label :for="'footer_name_' + index"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('variable') }} <span x-text="index"></span>
                                      </label>
                                    </div>
                                    <input type="text" :id="'footer_name_' + index" x-model="footerInputs[index]"
                                      class="mentionable block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:placeholder-slate-600"
                                      autocomplete="off" />
                                    <p x-show="footerInputErrors[index]" x-text="footerInputErrors[index]"
                                      class="text-red-500 text-sm mt-1"></p>
                                  </div>
                                </template>
                                @if ($errors->has('footerInputs.*'))
                                <x-dynamic-alert type="danger" :message="$errors->first('footerInputs.*')" class="mt-4">
                                </x-dynamic-alert>
                                @endif
                              </div>
                            </div>
                          </div>
                        </x-slot:content>
                      </x-card>
                    </div>

                    <!-- Right column: Preview -->
                    <div class="h-full">
                      <x-card class="rounded-lg h-full">
                        <x-slot:header>
                          <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                            {{ t('preview') }}
                          </h1>
                        </x-slot:header>
                        <x-slot:content>
                          <div class="w-full p-6 border border-gray-200 rounded shadow-sm dark:border-gray-700"
                            style="background-image: url('{{ asset('img/chat/whatsapp_light_bg.png') }}');">
                            <!-- File Preview Section -->
                            <div class="mb-1" x-show="previewUrl">
                              <!-- Image Preview -->
                              <a x-show="inputType === 'IMAGE'" :href="previewUrl" class="glightbox"
                                x-effect="if (previewUrl) { setTimeout(() => initGLightbox(), 100); }">
                                <img x-show="inputType === 'IMAGE'" :src="previewUrl"
                                  class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800" />
                              </a>

                              <!-- Video Preview -->
                              <video x-show="inputType === 'VIDEO'" :src="previewUrl" controls
                                class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800 glightbox cursor-pointer"></video>

                              <!-- Document Preview -->
                              <div x-show="inputType === 'DOCUMENT'"
                                class="p-4 border border-gray-300 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                  {{ t('document_uploaded') }}
                                  <a :href="previewUrl" target="_blank"
                                    class="text-blue-500 underline break-all inline-block" x-text="previewFileName"></a>
                                </p>
                              </div>
                            </div>

                            <!-- Campaign Text Section -->
                            <div class="p-6 bg-white rounded-lg dark:bg-gray-800 dark:text-white">
                              <p class="mb-3 font-meduim text-gray-800 dark:text-gray-400"
                                x-html="replaceVariables(campaignHeader, headerInputs)">
                              </p>
                              <p class="mb-3 font-normal text-sm text-gray-500 dark:text-gray-400"
                                x-html="replaceVariables(campaignBody, bodyInputs)">
                              </p>
                              <div class="mt-4">
                                <p class="font-normal text-xs text-gray-500 dark:text-gray-400" x-text="campaignFooter">
                                </p>
                              </div>
                            </div>

                            <template x-if="buttons && buttons.length > 0"
                              class="bg-white rounded-lg py-2 dark:bg-gray-800 dark:text-white">
                              <!-- Check if buttons is defined and not empty -->
                              <div class="space-y-1">
                                <!-- Use space-y-2 for vertical spacing between buttons -->
                                <template x-for="(button, index) in buttons" :key="index">
                                  <div
                                    class="w-full px-4 py-2 bg-white text-gray-900 rounded-md dark:bg-gray-700 dark:text-white">
                                    <span x-text="button.text" class="text-sm block text-center"></span>
                                    <!-- Center the text inside the button -->
                                  </div>
                                </template>
                              </div>
                            </template>
                          </div>
                        </x-slot:content>
                      </x-card>
                    </div>
                  </div>

                  <!-- Buttons at the bottom -->
                  <div x-show="campaignsSelected" x-cloak
                    class="mt-6 py-4 border-t border-black/5 dark:border-white/5 bg-gradient-to-b from-transparent to-gray-50 dark:to-slate-800/50">
                    <div class="flex justify-end gap-4">
                      <button type="button" x-on:click="isOpen = false;resetModal()"
                        class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700/50 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/5 hover:bg-gray-50 dark:hover:bg-slate-700 hover:shadow-md transition-all">
                        {{ t('close') }}
                      </button>
                      <x-button.loading-button type="button" target="save" x-on:click="handleSave()"
                        x-bind:disabled="isUploading" x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }">
                        {{ t('submit') }}
                      </x-button.loading-button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>

</div>
