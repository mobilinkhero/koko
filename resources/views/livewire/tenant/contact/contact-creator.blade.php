<div>
    <x-slot:title>
        {{ $contact->exists ? t('edit_contact_title') : t('add_contact_title') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('contacts'), 'route' => tenant_route('tenant.contacts.list')],
        ['label' => $contact->exists ? t('edit_contact_title') : t('add_contact_title')]
    ]" />

    <div>
        <!-- Alpine JS component for multiselect -->
        <script>
            function groupMultiselect({
                selected = [],
                options = []
            }) {
                return {
                    open: false,
                    selected,
                    options,

                    get selectedOptions() {
                        return this.options.filter(opt => this.selected.includes(opt.id));
                    },

                    get availableOptions() {
                        return this.options.filter(opt => !this.selected.includes(opt.id));
                    },

                    toggle(id) {
                        if (this.selected.includes(id)) {
                            this.selected = this.selected.filter(i => i !== id);
                        } else {
                            this.selected.push(id);
                        }
                    },

                    remove(id) {
                        this.selected = this.selected.filter(i => i !== id);
                    },
                }
            }
        </script>

        <form wire:submit.prevent="save">
            <div class="pb-3 font-display">
                <x-page-heading>
                    {{ $contact->exists ? t('edit_contact_title') : t('add_contact_title') }}
                </x-page-heading>
            </div>

            <x-card class="relative rounded-lg lg:w-3/4" x-cloak>
                <x-slot:content>
                    <!-- Feature Limit Warning  -->
                    @if (!$contact->exists && isset($this->hasReachedLimit) && $this->hasReachedLimit)
                    <div class="px-6 pt-4">
                        <div class="rounded-md bg-warning-50 dark:bg-warning-900/30 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-warning-400" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                        {{ t('contact_limit_reached') }}</h3>
                                    <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                                        <p>{{ t('contact_limit_reached_upgrade_plan') }} <a
                                                href="{{ tenant_route('tenant.subscription') }}"
                                                class="font-medium underline">{{ t('upgrade_plan') }}</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div x-data="{ tab: @entangle('tab') }">
                        <div class="grid grid-cols-4 lg:grid-cols-4 gap-2 border-b dark:border-slate-500 w-full">
                            <button type="button" x-on:click="tab = 'contact-details'" :class="{
                                    'border-b-2 border-primary-500 text-primary-500 dark:text-primary-400': tab === 'contact-details',
                                    'border-b-2 border-danger-500 text-danger-600 dark:text-danger-500': tab !== 'contact-details' &&
                                        {{ $errors->hasAny([
                                            'contact.firstname',
                                            'contact.lastname',
                                            'contact.company',
                                            'contact.email',
                                            'contact.phone',
                                            'contact.type',
                                            'contact.status_id',
                                            'contact.source_id',
                                            'contact.assigned_id',
                                            'contact.website',
                                        ])
                                            ? 'true'
                                            : 'false' }},
                                    'dark:text-slate-200': tab !== 'contact-details'
                                }" class="lg:text-base px-4 py-2 sm:px-6 sm:py-4 text-sm w-full">
                                {{ t('contact_details') }}
                            </button>

                            <button type="button" x-on:click="tab = 'other-details'" :class="{
                                    'border-b-2 border-primary-500 text-primary-500 dark:text-primary-400': tab === 'other-details',
                                    'border-b-2 border-danger-500 text-danger-600 dark:text-danger-500': tab !== 'other-details' &&
                                        {{ $errors->hasAny([
                                            'contact.city',
                                            'contact.state',
                                            'contact.country_id',
                                            'contact.address',
                                            'contact.zip',
                                            'contact.description',
                                        ])
                                            ? 'true'
                                            : 'false' }},
                                    'dark:text-slate-200': tab !== 'other-details'
                                }" class="lg:text-base px-4 py-2 sm:px-6 sm:py-4 text-sm w-full">
                                {{ t('other_details') }}
                            </button>

                            <button type="button" x-on:click="tab = 'notes'" :class="{
                                    'border-b-2 border-primary-500 text-primary-500 dark:text-primary-400': tab === 'notes',
                                    'border-b-2 border-danger-500 text-danger-600 dark:text-danger-500': tab !== 'notes' &&
                                        {{ $errors->hasAny(['notes']) ? 'true' : 'false' }},
                                    'dark:text-slate-200': tab !== 'notes'
                                }" class="lg:text-base px-4 py-2 sm:px-6 sm:py-4 text-sm w-full">
                                {{ t('notes_title') }}
                            </button>

                            @if($this->hasCustomFields)
                            <button type="button" x-on:click="tab = 'custom-fields'" :class="{
                                    'border-b-2 border-primary-500 text-primary-500 dark:text-primary-400': tab === 'custom-fields',
                                    'border-b-2 border-danger-500 text-danger-600 dark:text-danger-500': tab !== 'custom-fields' &&
                                        {{ collect($this->customFields)->pluck('field_name')->map(function($name) {
                                            return 'customFieldsData.'.$name;
                                        })->filter(function($field) use ($errors) {
                                            return $errors->has($field);
                                        })->isNotEmpty() ? 'true' : 'false' }},
                                    'dark:text-slate-200': tab !== 'custom-fields'
                                }" class="lg:text-base px-4 py-2 sm:px-6 sm:py-4 text-sm w-full">
                                {{ t('custom_fields') }}
                            </button>
                            @endif
                        </div>

                        <div x-show="tab === 'contact-details'" class="mt-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
                                {{-- Status, Source, and Assigned --}}
                                <div class="col-span-1">
                                    <div wire:ignore>
                                        <div class="flex items-center gap-1">
                                            <span class="text-danger-500">*</span>
                                            <x-label for="contact.status_id" :value="t('status')" />
                                        </div>
                                        <x-select wire:model.defer="contact.status_id" id="contact.status_id"
                                            class="block w-full mt-1 tom-select">
                                            <option value="">{{ t('select_status') }}</option>
                                            @foreach ($this->statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                    <x-input-error for="contact.status_id" class="mt-1" />
                                </div>

                                <div class="col-span-1">
                                    <div wire:ignore>
                                        <div class="flex items-center gap-1">
                                            <span class="text-danger-500">*</span>
                                            <x-label for="contact.source_id" :value="t('source')" />
                                        </div>
                                        <x-select wire:model.defer="contact.source_id" id="contact.source_id"
                                            class="block w-full mt-1 tom-select">
                                            <option value="">{{ t('select_source') }}</option>
                                            @foreach ($this->sources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                    <x-input-error for="contact.source_id" class="mt-1" />
                                </div>

                                <div class="col-span-1">
                                    <div wire:ignore>
                                        <x-label for="contact.assigned_id" :value="t('assigned')" class="mb-2" />
                                        <x-select wire:model.defer="contact.assigned_id" id="contact.assigned_id"
                                            class="block w-full tom-select">
                                            <option value="">{{ t('select_assign') }}</option>
                                            @foreach ($this->users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->firstname }}
                                            </option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                    <x-input-error for="contact.assigned_id" class="mt-1" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:gap-4 sm:grid-cols-2">
                                {{-- First Name and Last Name --}}
                                <div class="col-span-1 mb-6">
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="contact.firstname" :value="t('firstname')" />
                                    </div>
                                    <x-input wire:model.defer="contact.firstname" type="text" id="contact.firstname"
                                        class="block w-full mt-1" autocomplete="off" />
                                    <x-input-error for="contact.firstname" class="mt-1" />
                                </div>

                                <div class="col-span-1 mb-6">
                                    <div class="flex items-center gap-1">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="contact.lastname" :value="t('lastname')" />
                                    </div>
                                    <x-input wire:model.defer="contact.lastname" type="text" id="contact.lastname"
                                        autocomplete="off" class="block w-full mt-1" />
                                    <x-input-error for="contact.lastname" class="mt-1" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                                {{-- Company and Type --}}
                                <div class="col-span-1">
                                    <x-label for="contact.company" :value="t('company')" />
                                    <x-input wire:model.defer="contact.company" type="text" id="contact.company"
                                        autocomplete="off" class="mt-1 block w-full" />
                                    <x-input-error for="contact.company" class="mt-2" />
                                </div>

                                <div class="col-span-1">
                                    <div wire:ignore>
                                        <div class="flex items-center gap-1">
                                            <span class="text-danger-500">*</span>
                                            <x-label for="contact.type" :value="t('type')" />
                                        </div>
                                        <div class="relative max-w-full">
                                            <x-select class="tom-select" wire:model.defer="contact.type"
                                                id="contact.type">
                                                <option value="">{{ t('select_type') }}</option>
                                                <option value="lead">{{ t('type_lead') }}</option>
                                                <option value="customer">{{ t('type_customer') }}</option>
                                            </x-select>
                                        </div>
                                    </div>
                                    <x-input-error for="contact.type" class="mt-1" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                                {{-- Email and Phone --}}
                                <div class="col-span-1">
                                    <x-label for="contact.email" :value="t('email')" />
                                    <x-input wire:model.defer="contact.email" id="contact.email" class="block w-full"
                                        autocomplete="off" />
                                    <x-input-error for="contact.email" class="mt-1" />
                                </div>
                                <div class="col-span-1">
                                    <div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-danger-500">*</span>
                                            <x-label for="phoneNumberInput" :value="t('phone')" />
                                        </div>
                                        <div wire:ignore
                                            x-data="{ phone: @entangle('contact.phone'), errorMessage: '' }">
                                            <x-input class="phone-input mt-[2px]" x-ref="phone" id="phone" type="tel"
                                                wire:model.defer="contact.phone" maxlength="18" autocomplete="off"
                                                x-model="phone" x-on:change="
                                                      if (phone.length == 18) {
                                                          errorMessage = 'You can only enter up to 18 digits';
                                                          phone = phone.slice(0, 18);
                                                      } else {
                                                          errorMessage = '';
                                                      }
                                                  " />
                                            <p x-show="errorMessage"
                                                class="text-sm text-danger-600 dark:text-danger-400 mt-1"
                                                x-text="errorMessage"></p>
                                        </div>
                                        <x-input-error class="mt-1" for="contact.phone" />
                                    </div>
                                </div>
                            </div>

                            {{-- Default Language & Website --}}
                            <div class=" mb-6">
                                <div>
                                    <x-label for="contact.website" :value="t('website')" />
                                    <x-input wire:model.defer="contact.website" type="text" id="contact.website"
                                        autocomplete="off" class="mt-1 block w-full" />
                                    <x-input-error for="contact.website" class="mt-2" />
                                </div>
                            </div>

                            <div class="mb-6">
                                <div>
                                    <x-label for="group_ids"
                                        class="dark:text-gray-300 block text-sm font-medium text-gray-700 mb-2">
                                        {{ t('assign_to_groups') }}
                                    </x-label>

                                    <div x-data="groupMultiselect({
                                        selected: @entangle('group_ids'),
                                        options: {{ json_encode(
                                            collect($groups)->map(function ($u) {
                                                    return [
                                                        'id' => $u['id'],
                                                        'label' => $u['name'],
                                                    ];
                                                })->values(),
                                        ) }},
                                    })">

                                        <!-- Selected groups -->
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="(item, index) in selectedOptions" :key="item.id">
                                                <span
                                                    class="flex items-center bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300">
                                                    <span x-text="item.label"></span>
                                                    <button @click="remove(item.id)"
                                                        class="ml-1 text-indigo-500 hover:text-danger-500 focus:outline-none text-xs">×</button>
                                                </span>
                                            </template>
                                        </div>

                                        <!-- Trigger -->
                                        <div class="relative mt-2">
                                            <button type="button" @click="open = !open"
                                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-info-500 focus:border-info-500 sm:text-sm">
                                                {{ t('select_groups') }}
                                            </button>

                                            <!-- Dropdown -->
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                                <template x-for="option in availableOptions" :key="option.id">
                                                    <div class="cursor-pointer select-none relative py-2 pl-10 pr-4 hover:bg-indigo-100 dark:hover:bg-indigo-600 text-gray-900 dark:text-white"
                                                        @click="toggle(option.id)">
                                                        <span class="block truncate" x-text="option.label"></span>
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                                            <input type="checkbox"
                                                                class="form-checkbox text-indigo-600 border-gray-300 rounded"
                                                                :checked="selected.includes(option.id)" readonly>
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="availableOptions.length === 0">
                                                    <div class="py-2 px-4 text-sm text-gray-500 dark:text-gray-400">
                                                        {{ t('no_more_groups_to_select') }}
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <x-input-error for="group_ids" class="mt-2" />
                                </div>

                            </div>
                        </div>

                        <div x-show="tab === 'other-details'" class="mt-6">
                            {{-- City & State --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                                <div>
                                    <x-label for="contact.city" :value="t('city')" />
                                    <x-input wire:model.defer="contact.city" type="text" id="contact.city"
                                        class="block w-full mt-1" autocomplete="off" />
                                    <x-input-error for="contact.city" class="mt-1" />
                                </div>

                                <div>
                                    <x-label for="contact.state" :value="t('state')" />
                                    <x-input wire:model.defer="contact.state" type="text" id="contact.state"
                                        class="block w-full mt-1" autocomplete="off" />
                                    <x-input-error for="contact.state" class="mt-2" />
                                </div>
                            </div>
                            {{-- Zip Code & Country --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                                <div>
                                    <x-label for="contact.country_id" :value="t('country')" />
                                    <div wire:ignore>
                                        <x-select wire:model.defer="contact.country_id" id="contact.country_id"
                                            class="block w-full mt-1 tom-select">
                                            <option value="">{{ t('country_select') }}</option>
                                            @foreach ($this->countries as $country)
                                            <option value="{{ $country['id'] }}">
                                                {{ $country['short_name'] }}
                                            </option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                    <x-input-error for="contact.country_id" class="mt-1" />
                                </div>
                                <div>
                                    <x-label for="contact.zip" :value="t('zip_code')" />
                                    <x-input wire:model.defer="contact.zip" type="text" id="contact.zip"
                                        class="block w-full mt-1" autocomplete="off" />
                                    <x-input-error for="contact.zip" class="mt-1" />
                                </div>
                            </div>

                            {{-- Description & Address --}}
                            <div class="mb-6">
                                <x-label for="contact.address" :value="t('address')" />
                                <x-textarea wire:model.defer="contact.address" id="contact.address" rows="3"
                                    class="block w-full mt-1" autocomplete="off" />
                                <x-input-error for="contact.address" class="mt-1" />
                            </div>
                            <div class="mb-6">
                                <x-label for="contact.description" :value="t('description')" />
                                <x-textarea wire:model.defer="contact.description" id="contact.description" rows="3"
                                    class="block w-full mt-1" autocomplete="off" />
                                <x-input-error for="contact.description" class="mt-1" />
                            </div>
                        </div>

                        <div x-show="tab === 'notes'" class="mt-6">
                            @if (!$contact->exists)
                            <div
                                class="text-slate-700 dark:text-slate-300 text-sm p-4 border border-gray-300 dark:border-gray-600 rounded-md">
                                {{ t('note_will_be_available_in_contact') }}
                            </div>
                            @else
                            <div class="col-span-1">
                                <div>
                                    <x-label for="notes_description" :value="t('add_notes_title')" />
                                    <div class="flex space-x-3 items-start">
                                        <x-textarea wire:model.defer="notes_description" id="notes_description"
                                            wire:blur="validateNotesDescription" class="block w-full" rows="3"
                                            autocomplete="off" />
                                    </div>
                                    <x-input-error for="notes_description" class="mt-1" />
                                    <div class="flex justify-end">
                                        <x-button.primary class="mt-3 flex-shrink-0" wire:click.prevent="addNote">
                                            {{ t('add') }}
                                        </x-button.primary>
                                    </div>
                                    <div
                                        class="mt-4 relative px-1 h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
                                        <ol class="relative border-s border-gray-300 dark:border-gray-700">
                                            @foreach ($notes as $note)
                                            <li class="mb-6 ms-4 relative">
                                                <div
                                                    class="absolute w-2 h-2 bg-primary-600 dark:bg-primary-400 rounded-full -left-5 top-4">
                                                </div>

                                                <div
                                                    class="flex-1 p-2 border-b border-gray-300 dark:border-gray-600 text-sm space-y-1 ml-4">
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 block relative"
                                                        data-tippy-content="{{ format_date_time($note['created_at']) }}"
                                                        style="cursor: pointer; display: inline-block; text-decoration: underline dotted;">
                                                        {{
                                                        \Carbon\Carbon::parse($note['created_at'])->diffForHumans(['options'
                                                        => \Carbon\Carbon::JUST_NOW]) }}
                                                    </span>
                                                    <div class="flex justify-between items-start flex-nowrap">
                                                        <span
                                                            class="text-gray-800 dark:text-gray-200 flex-1 break-words">
                                                            {{ $note['notes_description'] }}
                                                        </span>
                                                        <x-heroicon-s-trash
                                                            class="text-danger-400 dark:text-danger-300 cursor-pointer h-7 w-7 min-w-7 min-h-7 p-1 shrink-0 ml-2"
                                                            wire:click="confirmDelete({{ $note['id'] }})" />
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Custom Fields Tab Content --}}
                        @if($this->hasCustomFields)
                        <div x-show="tab === 'custom-fields'" class="mt-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @foreach ($this->customFields as $field)
                                    <div class="col-span-1">
                                        <div class="flex items-center gap-1">
                                            @if($field['is_required'])
                                                <span class="text-danger-500">*</span>
                                            @endif
                                            <x-label :for="'customFieldsData.' . $field['field_name']" :value="$field['field_label']" />
                                        </div>

                                        @if($field['field_type'] === 'text')
                                            <x-input
                                                wire:model.defer="customFieldsData.{{ $field['field_name'] }}"
                                                :id="'customFieldsData.' . $field['field_name']"
                                                type="text"
                                                class="block w-full mt-1"
                                                :placeholder="$field['placeholder'] ?? ''"
                                            />
                                        @elseif($field['field_type'] === 'textarea')
                                            <x-textarea
                                                wire:model.defer="customFieldsData.{{ $field['field_name'] }}"
                                                :id="'customFieldsData.' . $field['field_name']"
                                                rows="3"
                                                class="block w-full mt-1"
                                                :placeholder="$field['placeholder'] ?? ''"
                                            ></x-textarea>
                                        @elseif($field['field_type'] === 'number')
                                            <x-input
                                                wire:model.defer="customFieldsData.{{ $field['field_name'] }}"
                                                :id="'customFieldsData.' . $field['field_name']"
                                                type="number"
                                                class="block w-full mt-1"
                                                :placeholder="$field['placeholder'] ?? ''"
                                            />
                                        @elseif($field['field_type'] === 'date')
                                            <x-input
                                                wire:model.defer="customFieldsData.{{ $field['field_name'] }}"
                                                :id="'customFieldsData.' . $field['field_name']"
                                                type="date"
                                                class="block w-full mt-1"
                                            />
                                        @elseif($field['field_type'] === 'dropdown' && !empty($field['field_options']))
                                            <select
                                                wire:model.defer="customFieldsData.{{ $field['field_name'] }}"
                                                :id="'customFieldsData.' . $field['field_name']"
                                                class="block w-full mt-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                            >
                                                <option value="">{{ t('select_option') }}</option>
                                                @foreach($field['field_options'] as $option)
                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($field['field_type'] === 'checkbox' && !empty($field['field_options']))
                                            <div class="mt-2 space-y-2">
                                                @foreach($field['field_options'] as $option)
                                                    <div class="flex items-center">
                                                        <input
                                                            type="checkbox"
                                                            wire:model.defer="customFieldsData.{{ $field['field_name'] }}"
                                                            :id="'customFieldsData.{{ $field['field_name'] }}.{{ $option }}'"
                                                            value="{{ $option }}"
                                                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded"
                                                        >
                                                        <label :for="'customFieldsData.{{ $field['field_name'] }}.{{ $option }}'" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <x-input-error :for="'customFieldsData.' . $field['field_name']" class="mt-1" />

                                        @if($field['description'])
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $field['description'] }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </x-slot:content>

                <!-- Submit Button -->
                <x-slot:footer class="rounded-b-lg">
                    <div class="flex justify-end space-x-3">
                        <x-button.secondary wire:click="cancel">
                            {{ t('cancel') }}
                        </x-button.secondary>
                        <x-button.loading-button type="submit" target="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                {{ $contact->exists ? t('update_button') : t('add_button') }}
                            </span>
                        </x-button.loading-button>
                    </div>
                </x-slot:footer>
            </x-card>
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'x-button.delete-button-note-modal'"
        title="{{ t('delete_notes_title') }}" wire:model.defer="confirmingDeletion"
        description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-200 border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="removeNote" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>