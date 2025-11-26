<x-app-layout>
    <div>
        @if (empty(get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected')) ||
        empty(get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number')))
        <x-disconnected-account />
        @else
        <div class="mx-auto" x-data="campaignForm()" x-init="init()" @dragover.prevent="handleDragOver($event)"
            @drop.prevent="handleDrop($event)">

            <!-- Hero Section with Progress -->
            <div class="mb-6">
                <x-breadcrumb :items="[
                        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
                        ['label' => t('campaigns'), 'route' => tenant_route('tenant.campaigns.list')],
                        ['label' => isset($campaign->id) ? t('edit_campaign') : t('create_campaign')]
                    ]" />
                <div class="mb-6 flex flex-col xl:flex-row justify-between items-start gap-4">
                    <x-settings-heading class="font-display">
                        {{ isset($campaign->id) ? t('edit_campaign') : t('create_campaign') }}
                    </x-settings-heading>

                    <!-- Summary Card (Responsive) -->
                    <div>
                        <div class="flex flex-wrap gap-2 md:gap-4">
                            <!-- Recipients -->
                            <div
                                class="flex items-center space-x-1 justify-center rounded-lg border border-primary-600 bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200 dark:border-primary-500 px-3 py-1 w-full sm:w-[230px]">
                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="font-semibold text-sm">{{ t('recipients') }}:</span>
                                <span class="font-bold" x-text="recipientCount"></span>
                            </div>

                            <!-- Template -->
                            <div
                                class="flex items-center justify-center space-x-1 rounded-lg border border-purple-600 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200 dark:border-purple-500 px-3 py-1 w-full sm:w-[230px]">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="font-semibold text-sm">{{ t('template') }}:</span>
                                <span class="truncate max-w-24"
                                    x-text="templateData?.name || '{{ t('not_selected') }}'"></span>
                            </div>

                            <!-- Status -->
                            <div
                                class="flex items-center justify-center space-x-1 rounded-lg border border-warning-600 bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200 dark:border-warning-500 px-3 py-1 w-full sm:w-[230px]">
                                <svg class="w-4 h-4 text-warning-600 dark:text-warning-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold text-sm">{{ t('status') }}:</span>
                                <span>{{ t('draft') }}</span>
                            </div>

                            <!-- Send Time -->
                            <div
                                class="flex justify-center items-center space-x-1 rounded-lg border border-success-600 bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-200 dark:border-success-500 px-3 py-1 w-full sm:w-[230px]">
                                <svg class="w-4 h-4 text-success-600 dark:text-success-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold text-sm">{{ t('send_time') }}:</span>
                                <span>
                                    <template x-if="formData.send_now">
                                        <span class="text-success-600 dark:text-success-300">{{ t('immediately')
                                            }}</span>
                                    </template>
                                    <template x-if="!formData.send_now">
                                        <span
                                            x-text="formData.scheduled_send_time ? new Date(formData.scheduled_send_time).toLocaleDateString() : '{{ t('not_set') }}'"></span>
                                    </template>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @if (!isset($campaign) && isset($hasReachedLimit) && $hasReachedLimit)
            <div class="px-6 py-4">
                <div
                    class="space-x-1 rounded-lg border border-warning-600 bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200 dark:border-warning-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-warning-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                {{ t('campaign_limit_reached') }}</h3>
                            <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                                <p>{{ t('campaign_limit_reached_upgrade_plan') }} <a
                                        href="{{ tenant_route('tenant.subscription') }}"
                                        class="font-medium underline">{{ t('upgrade_plan') }}</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- Main Form -->
            <form id="campaign-form" method="POST" :action="formAction" enctype="multipart/form-data"
                @submit.prevent="handleSubmit">
                @csrf
                @if (isset($campaign))
                @method('PUT')
                @endif


                <div class="grid grid-cols-1 xl:grid-cols-6 gap-8 mb-20">
                    <!-- Main Form Column -->
                    <x-card class="rounded-lg shadow-sm w-full xl:col-span-4 self-start">
                        <x-slot:header>
                            <!-- Steps Section (Tabs Style) -->
                            <div class="flex gap-4 items-center overflow-x-auto">
                                <template x-for="(step, index) in steps" :key="index">
                                    <button @click="currentStep = index + 1" type="button"
                                        class="flex-1 min-w-max md:w-auto text-sm font-semibold rounded-lg border p-2 md:px-4 md:py-2 transition-all"
                                        :class="{
                                            'bg-primary-600 text-white border-primary-600 dark:bg-primary-500 dark:text-white dark:border-primary-500': index +
                                                1 === currentStep,
                                            'bg-primary-100 text-primary-700 border-primary-300 dark:bg-primary-900 dark:text-primary-200 dark:border-primary-700': index +
                                                1 < currentStep,
                                            'bg-white text-gray-600 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700': index +
                                                1 > currentStep
                                        }">
                                        <template x-if="index + 1 < currentStep">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </template>
                                        <span class="ml-1" x-text="step.title"></span>
                                    </button>
                                </template>
                            </div>
                        </x-slot:header>

                        <x-slot:content>
                            <!-- Step 1: Basic Information -->
                            <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0">

                                <x-card class="rounded-lg shadow-sm">
                                    <!-- Header -->
                                    <x-slot:header>
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-primary-600 " fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                                    {{ t('basic_information') }}
                                                </h2>
                                                <p class="text-sm text-gray-500 dark:text-gray-300">
                                                    {{ t('enter_campaign_details') }}</p>
                                            </div>
                                        </div>
                                    </x-slot:header>
                                    <x-slot:content>

                                        <!-- Content -->
                                        <div class="space-y-6">
                                            <!-- Campaign Name -->
                                            <div class="relative">

                                                <label for="campaign_name"
                                                    class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                                    <span class="text-danger-500">*</span>
                                                    <span>{{ t('campaign_name') }}</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="text" id="campaign_name" name="campaign_name"
                                                        x-model="formData.campaign_name" autocomplete="off"
                                                        class="block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                                        placeholder="{{ t('enter_campaign_name') }}" required>

                                                </div>
                                                <div x-show="errors.campaign_name"
                                                    class="mt-2 text-sm text-danger-500 flex items-center space-x-1">
                                                    <span x-text="errors.campaign_name"></span>
                                                </div>
                                            </div>

                                            <!-- Relation Type and Template in Grid -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <!-- Relation Type -->
                                                <div class="relative">
                                                    <label for="rel_type"
                                                        class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        <span class="text-danger-500">*</span>
                                                        <span>{{ t('relation_type') }}</span>
                                                    </label>
                                                    <select id="rel_type" name="rel_type" x-model="formData.rel_type"
                                                        @change="handleRelTypeChange"
                                                        class="tom-select block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                                        required>
                                                        <option value="">{{ t('select_relation_type') }}
                                                        </option>
                                                        @foreach (\App\Enum\Tenant\WhatsAppTemplateRelationType::getRelationtype()
                                                        as $key => $value)
                                                        <option value="{{ $key }}">
                                                            {{ ucfirst($value) }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div x-show="errors.rel_type"
                                                        class="mt-2 text-sm text-danger-600 flex items-center space-x-1">
                                                        <span x-text="errors.rel_type"></span>
                                                    </div>
                                                </div>

                                                <!-- Template Selection -->
                                                <div class="relative">
                                                    <label for="template_id"
                                                        class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        <span class="text-danger-500">*</span>
                                                        <span>{{ t('template') }}</span>
                                                    </label>
                                                    <div class="relative">
                                                        <select id="template_id" name="template_id"
                                                            x-model="formData.template_id"
                                                            @change="handleTemplateChange"
                                                            class="tom-select block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                                            required>
                                                            <option value="">{{ t('select_template') }}
                                                            </option>
                                                            @foreach ($templates as $template)
                                                            <option value="{{ $template->template_id }}"
                                                                data-header-format="{{ $template->header_data_format }}"
                                                                data-header-text="{{ $template->header_data_text }}"
                                                                data-body-text="{{ $template->body_data }}"
                                                                data-footer-text="{{ $template->footer_data }}"
                                                                data-header-params="{{ $template->header_params_count }}"
                                                                data-body-params="{{ $template->body_params_count }}"
                                                                data-footer-params="{{ $template->footer_params_count }}"
                                                                data-buttons="{{ $template->buttons_data }}">
                                                                {{ $template->template_name }}
                                                                ({{ $template->language }})
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <div x-show="loading.template"
                                                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                            <svg class="animate-spin h-5 w-5 text-primary-500"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                    stroke="currentColor" stroke-width="4">
                                                                </circle>
                                                                <path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div x-show="errors.template_id"
                                                        class="mt-2 text-sm text-danger-600 flex items-center space-x-1">

                                                        <span x-text="errors.template_id"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            </div>

                            <!-- Step 2: Contact Selection -->
                            <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0">

                                <x-card class="rounded-lg shadow-sm">
                                    <!-- Header -->
                                    <x-slot:header>
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-primary-600 " fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                                    {{ t('contact_selection') }}
                                                </h2>
                                                <p class="text-sm text-gray-500 dark:text-gray-300">
                                                    {{ t('choose_your_target_audience') }}</p>
                                            </div>
                                        </div>
                                    </x-slot:header>

                                    <x-slot:content>
                                        <!-- Content -->
                                        <div class="space-y-6">
                                            <!-- Select All Toggle with Enhanced Design -->
                                            <div class="rounded-lg p-3 border dark:border-slate-600"
                                                :class="{ 'opacity-50 pointer-events-none': hasAnyFilterSelected }">
                                                <div
                                                    class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="relative">
                                                            <input type="checkbox" id="select_all" name="select_all"
                                                                x-model="formData.select_all"
                                                                @change="handleSelectAllChange"
                                                                class="w-5 h-5 text-primary-600 border-2 border-gray-300 rounded focus:ring-primary-500">
                                                        </div>
                                                        <div>
                                                            <label for="select_all"
                                                                class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                                {{ t('select_all_contacts') }}
                                                            </label>
                                                            <p class="text-sm text-gray-500">
                                                                {{ t('automatically_include_contacts') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-center sm:text-right" x-cloak>
                                                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-500"
                                                            x-text="recipientCount">
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-300">
                                                            {{ t('contacts') }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Filters -->
                                            <!-- Filters -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <!-- Status Filter -->
                                                <div class="relative">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">
                                                        {{ t('filter_by_status') }}
                                                    </label>

                                                    <!-- Dropdown Button -->
                                                    <button type="button" @click="toggleDropdown('status')"
                                                        :disabled="formData.select_all"
                                                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                                        <span class="block truncate"
                                                            x-text="getDisplayText('status')"></span>
                                                        <span
                                                            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                            <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200"
                                                                :class="{'rotate-180': dropdownStates.status.open}"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </button>

                                                    <!-- Dropdown Panel -->
                                                    <div x-show="dropdownStates.status.open"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="transform opacity-0 scale-95"
                                                        x-transition:enter-end="transform opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="transform opacity-100 scale-100"
                                                        x-transition:leave-end="transform opacity-0 scale-95"
                                                        @click.away="closeDropdown('status')"
                                                        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">

                                                        <!-- Search Input -->
                                                        <div
                                                            class="px-3 py-2 border-b border-gray-200 dark:border-gray-600">
                                                            <input type="text" x-model="dropdownStates.status.search"
                                                                placeholder="{{ t('search_statuses') }}"
                                                                class="w-full px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-200">
                                                        </div>

                                                        <!-- Quick Actions -->
                                                        <div
                                                            class="px-3 py-2 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                                                            <div class="flex justify-between text-xs">
                                                                <button type="button"
                                                                    @click="selectAllFilters('status')"
                                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                                    {{ t('select_all') }}
                                                                </button>
                                                                <button type="button" @click="clearAllFilters('status')"
                                                                    x-show="getSelectedFilterCount('status') > 0"
                                                                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                                                    {{ t('clear_all') }}
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Options List -->
                                                        <template x-for="option in getFilteredOptions('status')"
                                                            :key="option.value">
                                                            <div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                                                @click="toggleFilterOption('status', option.value)">
                                                                <div class="flex items-center">
                                                                    <input type="checkbox"
                                                                        :checked="isFilterSelected('status', option.value)"
                                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-3">
                                                                    <div class="flex-1">
                                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200"
                                                                            x-text="option.label"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <!-- No Results -->
                                                        <div x-show="getFilteredOptions('status').length === 0"
                                                            class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                            {{ t('no_statuses_found') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Source Filter -->
                                                <div class="relative">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">
                                                        {{ t('filter_by_source') }}
                                                    </label>

                                                    <button type="button" @click="toggleDropdown('source')"
                                                        :disabled="formData.select_all"
                                                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                                        <span class="block truncate"
                                                            x-text="getDisplayText('source')"></span>
                                                        <span
                                                            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                            <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200"
                                                                :class="{'rotate-180': dropdownStates.source.open}"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </button>

                                                    <div x-show="dropdownStates.source.open"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="transform opacity-0 scale-95"
                                                        x-transition:enter-end="transform opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="transform opacity-100 scale-100"
                                                        x-transition:leave-end="transform opacity-0 scale-95"
                                                        @click.away="closeDropdown('source')"
                                                        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">

                                                        <div
                                                            class="px-3 py-2 border-b border-gray-200 dark:border-gray-600">
                                                            <input type="text" x-model="dropdownStates.source.search"
                                                                placeholder="{{ t('search_sources') }}"
                                                                class="w-full px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-200">
                                                        </div>

                                                        <div
                                                            class="px-3 py-2 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                                                            <div class="flex justify-between text-xs">
                                                                <button type="button"
                                                                    @click="selectAllFilters('source')"
                                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                                    {{ t('select_all') }}
                                                                </button>
                                                                <button type="button" @click="clearAllFilters('source')"
                                                                    x-show="getSelectedFilterCount('source') > 0"
                                                                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                                                    {{ t('clear_all') }}
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <template x-for="option in getFilteredOptions('source')"
                                                            :key="option.value">
                                                            <div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                                                @click="toggleFilterOption('source', option.value)">
                                                                <div class="flex items-center">
                                                                    <input type="checkbox"
                                                                        :checked="isFilterSelected('source', option.value)"
                                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-3">
                                                                    <div class="flex-1">
                                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200"
                                                                            x-text="option.label"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <div x-show="getFilteredOptions('source').length === 0"
                                                            class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                            {{ t('no_sources_found') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Groups Filter -->
                                                <div class="relative">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">
                                                        {{ t('filter_by_groups') }}
                                                    </label>

                                                    <button type="button" @click="toggleDropdown('group')"
                                                        :disabled="formData.select_all"
                                                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                                        <span class="block truncate"
                                                            x-text="getDisplayText('group')"></span>
                                                        <span
                                                            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                            <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200"
                                                                :class="{'rotate-180': dropdownStates.group.open}"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </button>

                                                    <div x-show="dropdownStates.group.open"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="transform opacity-0 scale-95"
                                                        x-transition:enter-end="transform opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="transform opacity-100 scale-100"
                                                        x-transition:leave-end="transform opacity-0 scale-95"
                                                        @click.away="closeDropdown('group')"
                                                        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">

                                                        <div
                                                            class="px-3 py-2 border-b border-gray-200 dark:border-gray-600">
                                                            <input type="text" x-model="dropdownStates.group.search"
                                                                placeholder="{{ t('search_groups') }}"
                                                                class="w-full px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-200">
                                                        </div>

                                                        <div
                                                            class="px-3 py-2 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                                                            <div class="flex justify-between text-xs">
                                                                <button type="button" @click="selectAllFilters('group')"
                                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                                    {{ t('select_all') }}
                                                                </button>
                                                                <button type="button" @click="clearAllFilters('group')"
                                                                    x-show="getSelectedFilterCount('group') > 0"
                                                                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                                                    {{ t('clear_all') }}
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <template x-for="option in getFilteredOptions('group')"
                                                            :key="option.value">
                                                            <div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                                                @click="toggleFilterOption('group', option.value)">
                                                                <div class="flex items-center">
                                                                    <input type="checkbox"
                                                                        :checked="isFilterSelected('group', option.value)"
                                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-3">
                                                                    <div class="flex-1">
                                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200"
                                                                            x-text="option.label"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <div x-show="getFilteredOptions('group').length === 0"
                                                            class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                            {{ t('no_groups_found') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Contact Selection -->
                                            <div :class="{ 'opacity-50 pointer-events-none': formData.select_all }">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-300">
                                                        {{ t('select_contacts') }}
                                                    </h3>
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 dark:text-primary-500">
                                                        <span x-text="selectedContacts.length" class="mr-1"></span>
                                                        {{ t('selected') }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="border border-gray-200 dark:border-slate-600 rounded-xl overflow-hidden">
                                                    <!-- Search Box and List Select All -->
                                                    <div
                                                        class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 px-2 py-1 space-x-2">
                                                        <div class="flex items-center flex-1 space-x-2">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                            </svg>
                                                            <input type="text" x-model="search" @input="filterContacts"
                                                                placeholder="{{ t('search_all_contacts') }}"
                                                                autocomplete="off"
                                                                class="flex-1 text-xs border-0 bg-transparent focus:ring-0 text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400" />

                                                            <!-- Search Loading Indicator -->
                                                            <template x-if="isSearching">
                                                                <div
                                                                    class="w-4 h-4 border-2 border-primary-200 border-t-primary-600 rounded-full animate-spin">
                                                                </div>
                                                            </template>

                                                            <!-- Clear Search Button -->
                                                            <template x-if="search && isInSearchMode">
                                                                <button @click="search = ''; exitSearchMode()"
                                                                    class="text-gray-400 hover:text-gray-600">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>

                                                        <div class="flex items-center space-x-2">
                                                            <!-- Search Mode Indicator -->
                                                            <template x-if="isInSearchMode && !isSearching">
                                                                <span
                                                                    class="text-xs text-primary-600 dark:text-primary-400 mr-2 bg-primary-50 dark:bg-primary-900/30 px-2 py-1 rounded">
                                                                    <span x-text="searchTotalCount"></span>
                                                                    {{ t('search_results') }}
                                                                </span>
                                                            </template>

                                                            <!-- Normal Mode Indicator -->
                                                            <template
                                                                x-if="!isInSearchMode && !search && !loading.contacts">
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400 mr-2">
                                                                    <span x-text="totalContactsCount"></span>
                                                                    {{ t('total_contacts') }}
                                                                </span>
                                                            </template>

                                                            <label
                                                                class="flex items-center space-x-2 text-xs text-gray-600 dark:text-gray-300">
                                                                <input type="checkbox" x-model="listSelectAll"
                                                                    @click="toggleListSelectAll"
                                                                    :disabled="formData.select_all"
                                                                    class="w-4 h-4 text-primary-600 border-gray-300 dark:border-gray-600 rounded">
                                                                <span
                                                                    x-text="isInSearchMode ? '{{ t('select_all_search_results') }}' : '{{ t('select_all_listed') }}'"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="max-h-80 overflow-y-auto scrollbar-visible"
                                                        @scroll="checkScrollBottom" x-ref="contactContainer">
                                                        <!-- Loading State -->
                                                        <template x-if="loading.contacts ">
                                                            <div
                                                                class="flex items-center justify-center py-8 text-sm text-gray-500">
                                                                <div
                                                                    class="w-5 h-5 border-4 border-primary-200 dark:border-slate-600 border-t-primary-600 rounded-full animate-spin mr-2">
                                                                </div>
                                                                {{ t('loading_contacts') }}...
                                                            </div>
                                                        </template>

                                                        <!-- Empty State -->
                                                        <template
                                                            x-if="!loading.contacts && filteredContacts.length === 0">
                                                            <div
                                                                class="flex flex-col items-center justify-center py-8 text-center text-sm text-gray-500">
                                                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                </svg>
                                                                <span
                                                                    x-text="isInSearchMode ? '{{ t('no_search_results') }}' : '{{ t('no_contacts_found') }}'"></span>
                                                            </div>
                                                        </template>

                                                        <!-- Contact List -->
                                                        <div class="divide-y divide-gray-100 dark:divide-gray-600">
                                                            <template x-for="contact in filteredContacts"
                                                                :key="contact.id">
                                                                <label
                                                                    class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-xs text-gray-700 dark:text-gray-300">
                                                                    <input type="checkbox" :value="contact.id"
                                                                        x-model="selectedContacts"
                                                                        class="w-4 h-4 text-primary-600 border-gray-300 dark:border-gray-600 rounded mr-2">
                                                                    <div class="flex items-center space-x-2 flex-1">
                                                                        <div
                                                                            class="w-8 h-8 bg-gradient-to-br from-primary-400 to-purple-500 rounded-full flex items-center justify-center text-white text-xs">
                                                                            <span
                                                                                x-text="(contact.firstname?.charAt(0) || '') + (contact.lastname?.charAt(0) || '')"></span>
                                                                        </div>
                                                                        <div class="truncate">
                                                                            <div class="font-medium text-gray-900 dark:text-gray-100 truncate"
                                                                                x-text="contact.firstname + ' ' + contact.lastname">
                                                                            </div>
                                                                            <div class="text-gray-500 dark:text-gray-400"
                                                                                x-text="contact.phone"></div>
                                                                        </div>
                                                                    </div>
                                                                    <span
                                                                        class="ml-2 px-2 py-0.5 rounded-full text-xs bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300">
                                                                        {{ t('active') }}
                                                                    </span>
                                                                </label>
                                                            </template>
                                                        </div>

                                                        <!-- Loading More Indicator -->
                                                        <template x-if="pagination.isLoadingMore && !isInSearchMode">
                                                            <div
                                                                class="flex items-center justify-center py-4 text-sm text-gray-500">
                                                                <div
                                                                    class="w-4 h-4 border-2 border-primary-200 dark:border-slate-600 border-t-primary-600 rounded-full animate-spin mr-2">
                                                                </div>
                                                                {{ t('loading_more') }}...
                                                            </div>
                                                        </template>

                                                        <!-- No More Data Indicator -->
                                                        <template
                                                            x-if="!pagination.hasMore && filteredContacts.length > 0 && !loading.contacts && !isInSearchMode">
                                                            <div class="text-center py-4 text-xs text-gray-400">
                                                                {{ t('no_more_contacts') }}
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <template x-if="errors.contacts">
                                                    <div
                                                        class="text-xs text-danger-600 flex items-center space-x-1 mt-1">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        <span x-text="errors.contacts"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            </div>

                            <!-- Step 3: Variables & Files -->
                            <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0">
                                <x-card class="rounded-lg shadow-sm">
                                    <!-- Header -->
                                    <x-slot:header>
                                        <div class="flex items-center justify-between flex-wrap">
                                            <!-- Left Section -->
                                            <div class="flex items-center space-x-3">
                                                <div
                                                    class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-primary-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                                        {{ t('variables_and_files') }}
                                                    </h2>
                                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                                        {{ t('customize_message_variables_media') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Right Section -->
                                            <p class="text-sm text-gray-500 dark:text-gray-300 mt-2 md:mt-0">
                                                {{ t('use_mergefields') }}
                                            </p>
                                        </div>
                                    </x-slot:header>

                                    <!-- Content -->
                                    <x-slot:content>
                                        <div>
                                            <!-- File Upload Section -->
                                            <div x-show="templateData && ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(templateData?.header?.format)"
                                                class="mb-6">
                                                <!-- Unique Header Design -->
                                                <div class="relative mb-4">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-primary-500/10 via-primary-400/5 to-transparent rounded-xl dark:from-primary-400/20 dark:via-primary-500/10">
                                                    </div>
                                                    <div
                                                        class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-primary-200/50 dark:border-primary-500/30 rounded-xl p-4 shadow-sm">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="relative">
                                                                    <div
                                                                        class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center shadow-lg">
                                                                        <svg class="w-5 h-5 text-white" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                                        </svg>
                                                                    </div>
                                                                    <div
                                                                        class="absolute -top-1 -right-1 w-4 h-4 bg-danger-500 rounded-full flex items-center justify-center">
                                                                        <span
                                                                            class="text-xs font-bold text-white">*</span>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <h3
                                                                        class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                                                                        {{ t('file_upload') }}
                                                                    </h3>
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400"
                                                                        x-text="`Upload ${templateData?.header?.format?.toLowerCase()} file for your campaign header`">
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="hidden sm:block">
                                                                <span
                                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
                                                                    x-text="templateData?.header?.format">
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Compact File Drop Zone -->
                                                <div class="relative bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 transition-all duration-300 group cursor-pointer"
                                                    :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': isDragOver }"
                                                    @click="$refs.fileInput.click()">

                                                    <input type="file" x-ref="fileInput" name="file" class="hidden"
                                                        :accept="templateData?.allowed_file_types?.accept || ''"
                                                        @change="handleFileSelect($event)">

                                                    <!-- Upload Content -->
                                                    <div class="p-6 text-center">
                                                        <div class="flex items-center justify-center space-x-4">
                                                            <!-- Upload Icon -->
                                                            <div class="flex-shrink-0">
                                                                <div
                                                                    class="w-12 h-12 bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-800 dark:to-primary-700 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                                                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                                    </svg>
                                                                </div>
                                                            </div>

                                                            <!-- Upload Text -->
                                                            <div class="text-left flex-1 hidden sm:block">
                                                                <p
                                                                    class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                                                    {{ t('click_to_upload') }}

                                                                </p>
                                                                <div
                                                                    class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 font-medium">
                                                                        <span
                                                                            x-text="templateData?.header?.format"></span>
                                                                        {{ t('files_only') }}
                                                                    </span>
                                                                    <span class="text-xs">
                                                                        <span
                                                                            x-text="templateData?.allowed_file_types?.extensions"></span>
                                                                        • Max <span
                                                                            x-text="Math.round((templateData?.max_file_size || 0) / 1024 / 1024)"></span>MB
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Drag Overlay -->
                                                    <div x-show="isDragOver"
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="opacity-0 scale-95"
                                                        x-transition:enter-end="opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="opacity-100 scale-100"
                                                        x-transition:leave-end="opacity-0 scale-95"
                                                        class="absolute inset-0 bg-primary-50 dark:bg-primary-900/30 border-2 border-primary-400 dark:border-primary-500 rounded-xl flex items-center justify-center">
                                                        <div class="text-center">
                                                            <div
                                                                class="w-12 h-12 mx-auto mb-3 bg-primary-100 dark:bg-primary-800 rounded-full flex items-center justify-center animate-bounce">
                                                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                                </svg>
                                                            </div>
                                                            <p
                                                                class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                                                                {{ t('drop_files_here') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Compact File Preview -->
                                                <div x-show="selectedFile"
                                                    x-transition:enter="transition ease-out duration-300"
                                                    x-transition:enter-start="opacity-0 translate-y-4"
                                                    x-transition:enter-end="opacity-100 translate-y-0" class="mt-4">
                                                    <div
                                                        class="bg-gradient-to-r from-success-50 to-emerald-50 dark:from-success-900/20 dark:to-emerald-900/20 rounded-lg p-4 border border-success-200 dark:border-success-700">
                                                        <div class="flex items-center space-x-3">
                                                            <!-- Preview Thumbnail -->
                                                            <div class="flex-shrink-0">
                                                                <!-- Image Thumbnail -->
                                                                <template x-if="filePreview.type === 'image'">
                                                                    <div
                                                                        class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border border-white dark:border-gray-600 shadow-sm">
                                                                        <img :src="filePreview.url"
                                                                            class="w-full h-full object-cover"
                                                                            alt="Preview">
                                                                    </div>
                                                                </template>

                                                                <!-- Video Thumbnail -->
                                                                <template x-if="filePreview.type === 'video'">
                                                                    <div
                                                                        class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border border-white dark:border-gray-600 shadow-sm">
                                                                        <video :src="filePreview.url"
                                                                            class="w-full h-full object-cover"
                                                                            muted></video>
                                                                    </div>
                                                                </template>

                                                                <!-- Fallback Thumbnail for Other Types -->
                                                                <template
                                                                    x-if="filePreview.type !== 'image' && filePreview.type !== 'video'">
                                                                    <div
                                                                        class="w-12 h-12 bg-gradient-to-br from-info-100 to-primary-100 dark:from-info-800 dark:to-primary-800 rounded-lg flex items-center justify-center border border-white dark:border-gray-600 shadow-sm">
                                                                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400"
                                                                            fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                        </svg>
                                                                    </div>
                                                                </template>
                                                            </div>

                                                            <!-- File Info -->
                                                            <div class="flex-1 min-w-0">
                                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                                                                    x-text="selectedFile?.name"></h4>
                                                                <div
                                                                    class="flex items-center space-x-3 text-xs text-gray-600 dark:text-gray-400 mt-1">

                                                                    <span
                                                                        class="flex items-center text-success-600 dark:text-success-400">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                                            viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd"
                                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                                clip-rule="evenodd" />
                                                                        </svg>
                                                                        {{ t('uploaded_successfully') }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Remove Button -->
                                                            <button type="button" @click="removeFile"
                                                                class="flex-shrink-0 p-2 text-danger-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-colors duration-200 group">
                                                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-200"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Error Message -->
                                                <div x-show="errors.file"
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    class="mt-3 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                                                    <div
                                                        class="flex items-center space-x-2 text-sm text-danger-600 dark:text-danger-400">
                                                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        <span x-text="errors.file"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Header Variables -->
                                            <div x-show="templateData && templateData.header && templateData.header.params_count > 0"
                                                class="mb-6">
                                                <!-- Unique Header Design -->
                                                <div class="relative mb-4">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-orange-500/10 via-orange-400/5 to-transparent rounded-xl dark:from-orange-400/20 dark:via-orange-500/10">
                                                    </div>
                                                    <div
                                                        class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-orange-200/50 dark:border-orange-500/30 rounded-xl p-4 s">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="relative">
                                                                    <div
                                                                        class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                                                                        <svg class="w-5 h-5 text-orange-500" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                        </svg>
                                                                    </div>

                                                                </div>
                                                                <div>
                                                                    <h3
                                                                        class="text-lg font-bold text-gray-900 dark:text-white">
                                                                        {{ t('header_variables') }}
                                                                    </h3>
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                        {{ t('customize_content_dynamic_values') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="hidden sm:block">
                                                                <span
                                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                                    {{ t('header_section') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Variables Grid -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <template x-for="i in (templateData?.header?.params_count || 0)"
                                                        :key="'header-' + i">
                                                        <div
                                                            class="group relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200">
                                                            <label :for="'header_input_' + i"
                                                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                                <span class="text-danger-500">*</span>
                                                                <span x-text="`Header Variable`"></span>
                                                                <span
                                                                    class="ml-auto text-xs font-mono bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 px-2 py-1 rounded"
                                                                    x-text="`${i}`"></span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="text" :id="'header_input_' + i"
                                                                    :name="'headerInputs[' + (i - 1) + ']'"
                                                                    x-model="variables.header[i-1]"
                                                                    @input="updatePreview"
                                                                    class="mentionable block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-gray-100 text-sm bg-white dark:bg-gray-700 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 dark:focus:border-orange-400 transition-colors duration-200 placeholder-gray-400 dark:placeholder-gray-500 mentionable"
                                                                    :placeholder="`Enter value for variable ${i}`"
                                                                    autocomplete="off">
                                                            </div>
                                                            <p
                                                                class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg>
                                                                <span
                                                                    x-text="`This will replace ${i} in the header`"></span>
                                                            </p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Body Variables -->
                                            <div x-show="templateData && templateData.body && templateData.body.params_count > 0"
                                                class="mb-6">
                                                <!-- Unique Header Design -->
                                                <div class="relative mb-4">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-info-500/10 via-info-400/5 to-transparent rounded-xl dark:from-info-400/20 dark:via-info-500/10">
                                                    </div>
                                                    <div
                                                        class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-info-200/50 dark:border-info-500/30 rounded-xl p-4 ">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="relative">
                                                                    <div
                                                                        class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center">
                                                                        <svg class="w-5 h-5 text-info-500" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                        </svg>
                                                                    </div>

                                                                </div>
                                                                <div>
                                                                    <h3
                                                                        class="text-lg font-bold text-gray-900 dark:text-white">
                                                                        {{ t('body_variables') }}
                                                                    </h3>
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                        {{ t('personalize_message_content') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="hidden sm:block">
                                                                <span
                                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-300">
                                                                    {{ t('body_section') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Variables Grid -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <template x-for="i in (templateData?.body?.params_count || 0)"
                                                        :key="'body-' + i">
                                                        <div
                                                            class="group relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-info-300 dark:hover:border-info-500 transition-all duration-200 ">
                                                            <label :for="'body_input_' + i"
                                                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                                <span class="text-danger-500">*</span>
                                                                <span x-text="`Body Variable`"></span>
                                                                <span
                                                                    class="ml-auto text-xs font-mono bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-300 px-2 py-1 rounded"
                                                                    x-text="`${i}`"></span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="text" :id="'body_input_' + i"
                                                                    :name="'bodyInputs[' + (i - 1) + ']'"
                                                                    x-model="variables.body[i-1]" @input="updatePreview"
                                                                    class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-gray-100 text-sm bg-white dark:bg-gray-700 focus:ring-1 focus:ring-info-500 focus:border-info-500 dark:focus:ring-info-400 dark:focus:border-info-400 transition-colors duration-200 placeholder-gray-400 dark:placeholder-gray-500 mentionable"
                                                                    :placeholder="`Enter value for variable ${i}`"
                                                                    autocomplete="off">
                                                            </div>
                                                            <p
                                                                class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg>
                                                                <span
                                                                    x-text="`This will replace ${i} in the body`"></span>
                                                            </p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Footer Variables -->
                                            <div x-show="templateData && templateData.footer && templateData.footer.params_count > 0"
                                                class="mb-6">
                                                <!-- Unique Header Design -->
                                                <div class="relative mb-4">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-purple-500/10 via-purple-400/5 to-transparent rounded-xl dark:from-purple-400/20 dark:via-purple-500/10">
                                                    </div>
                                                    <div
                                                        class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-purple-200/50 dark:border-purple-500/30 rounded-xl p-4 ">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="relative">
                                                                    <div
                                                                        class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                                                        <svg class="w-5 h-5 text-purple-500" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                                                        </svg>
                                                                    </div>

                                                                </div>
                                                                <div>
                                                                    <h3
                                                                        class="text-lg font-bold text-gray-900 dark:text-white">
                                                                        {{ t('footer_variables') }}
                                                                    </h3>
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                        {{ t('dynamic_content_footer') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="hidden sm:block">
                                                                <span
                                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                                    {{ t('footer_section') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Variables Grid -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <template x-for="i in (templateData?.footer?.params_count || 0)"
                                                        :key="'footer-' + i">
                                                        <div
                                                            class="group relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200">
                                                            <label :for="'footer_input_' + i"
                                                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                                <span class="text-danger-500">*</span>
                                                                <span x-text="`Footer Variable`"></span>
                                                                <span
                                                                    class="ml-auto text-xs font-mono bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-2 py-1 rounded"
                                                                    x-text="`${i}`"></span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="text" :id="'footer_input_' + i"
                                                                    :name="'footerInputs[' + (i - 1) + ']'"
                                                                    x-model="variables.footer[i-1]"
                                                                    @input="updatePreview"
                                                                    class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-gray-100 text-sm bg-white dark:bg-gray-700 focus:ring-1 focus:ring-purple-500 focus:border-purple-500 dark:focus:ring-purple-400 dark:focus:border-purple-400 transition-colors duration-200 placeholder-gray-400 dark:placeholder-gray-500 mentionable"
                                                                    :placeholder="`Enter value for variable ${i}`"
                                                                    autocomplete="off">
                                                            </div>
                                                            <p
                                                                class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg>
                                                                <span
                                                                    x-text="`This will replace ${i} in the footer`"></span>
                                                            </p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- No Variables Message -->
                                            <div x-show="!hasVariablesOrFiles" class="text-center py-12">
                                                <div
                                                    class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                                    {{ t('no_customization_needed') }}</h3>
                                                <p class="text-gray-500">
                                                    {{ t('template_require_variables_files') }}</p>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            </div>

                            <!-- Step 4: Scheduling -->
                            <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0">

                                <x-card class="rounded-lg shadow-sm">
                                    <!-- Header -->
                                    <x-slot:header>

                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                                    {{ t('scheduling') }}
                                                </h2>
                                                <p class="text-sm text-gray-500 dark:text-gray-300">
                                                    {{ t('choose_send_campaign') }}</p>
                                            </div>
                                        </div>

                                    </x-slot:header>
                                    <x-slot:content>
                                        <!-- Content -->
                                        <div class="space-y-6">
                                            <!-- Send Options -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <!-- Send Now Option -->
                                                <div class="relative group">
                                                    <div class="bg-gradient-to-br from-success-50 to-emerald-50 dark:from-success-900/20 dark:to-emerald-900/20 border border-success-200 dark:border-success-800 rounded-xl p-4 cursor-pointer transition-all duration-200"
                                                        :class="{
                                                            'ring-1 ring-success-500 bg-success-100 dark:bg-success-900/30': formData
                                                                .send_now
                                                        }" @click="formData.send_now = true">

                                                        <!-- Header with Radio and Title -->
                                                        <div class="flex items-start gap-3 mb-3">
                                                            <div class="flex-shrink-0 mt-0.5">
                                                                <input type="radio" id="send_now" name="send_option"
                                                                    x-model="formData.send_now" :value="true"
                                                                    class="w-4 h-4 text-success-600 border-2 border-gray-300 focus:ring-2 focus:ring-success-500 focus:ring-offset-0">
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <label for="send_now"
                                                                    class="block text-base font-semibold text-gray-900 dark:text-gray-300 cursor-pointer">
                                                                    {{ t('send_immediately') }}
                                                                </label>
                                                                <p
                                                                    class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                                    {{ t('Campaign_start_immediately_creation') }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <!-- Feature Badge -->
                                                        <div class="flex items-center justify-between">
                                                            <div
                                                                class="flex items-center text-success-600 dark:text-success-400">
                                                                <div
                                                                    class="w-5 h-5 bg-success-100 dark:bg-success-800 rounded-full flex items-center justify-center mr-2">
                                                                    <svg class="w-3 h-3" fill="currentColor"
                                                                        viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                </div>
                                                                <span class="text-sm font-medium">{{
                                                                    t('instant_delivery') }}</span>
                                                            </div>
                                                            <div class="text-right">
                                                                <div
                                                                    class="w-8 h-8 bg-success-600 rounded-full flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-white" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Schedule Later Option -->
                                                <div class="relative group">
                                                    <div class="bg-gradient-to-br from-info-50 to-primary-50 dark:from-info-900/20 dark:to-primary-900/20 border border-info-200 dark:border-info-800 rounded-xl p-4 cursor-pointer transition-all duration-200 "
                                                        :class="{
                                                            'ring-1 ring-primary-500 bg-info-100 dark:bg-info-900/30':
                                                                !
                                                                formData.send_now
                                                        }" @click="formData.send_now = false">

                                                        <!-- Header with Radio and Title -->
                                                        <div class="flex items-start gap-3 mb-3">
                                                            <div class="flex-shrink-0 mt-0.5">
                                                                <input type="radio" id="schedule_later"
                                                                    name="send_option" x-model="formData.send_now"
                                                                    :value="false"
                                                                    class="w-4 h-4 text-primary-600 border-2 border-gray-300 focus:ring-2 focus:ring-primary-500 focus:ring-offset-0">
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <label for="schedule_later"
                                                                    class="block text-base font-semibold text-gray-900 dark:text-gray-300 cursor-pointer">
                                                                    {{ t('schedule_for_later') }}
                                                                </label>
                                                                <p
                                                                    class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                                    {{ t('choose_specific_date_time_send') }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <!-- Feature Badge -->
                                                        <div class="flex items-center justify-between">
                                                            <div
                                                                class="flex items-center text-primary-600 dark:text-primary-400">
                                                                <div
                                                                    class="w-5 h-5 bg-primary-100 dark:bg-primary-800 rounded-full flex items-center justify-center mr-2">
                                                                    <svg class="w-3 h-3" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                </div>
                                                                <span class="text-sm font-medium">{{ t('perfect_timing')
                                                                    }}</span>
                                                            </div>
                                                            <div class="text-right">
                                                                <div
                                                                    class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-white" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Scheduled Time Input -->
                                            <div x-show="!formData.send_now"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100" class="mt-6">
                                                <label for="scheduled_send_time"
                                                    class="block text-sm font-medium text-gray-700 mb-3">
                                                    {{ t('scheduled_send_time') }} <span
                                                        class="text-danger-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="text" data-input id="scheduled_datepicker"
                                                        x-on:click="flatePickrWithTime()" name="scheduled_send_time"
                                                        x-model="formData.scheduled_send_time" autocomplete="off"
                                                        class="block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600">
                                                    <button type="button" id="calendar-button"
                                                        class="right-3 top-[0.50rem] absolute"
                                                        x-on:click="flatePickrWithTime()">
                                                        <x-heroicon-o-calendar class="w-5 h-5 input-button"
                                                            title="toggle" data-toggle />
                                                    </button>
                                                </div>
                                                <div x-show="errors.scheduled_send_time"
                                                    class="mt-2 text-sm text-danger-600 flex items-center space-x-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span x-text="errors.scheduled_send_time"></span>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-500">
                                                    {{ t('campaign_sent_specified_time') }}</p>
                                            </div>
                                        </div>
                                    </x-slot:content>
                                </x-card>
                            </div>
                        </x-slot:content>
                    </x-card>

                    <!-- Sidebar Column - Preview -->
                    <x-card class="rounded-lg shadow-sm xl:col-span-2 self-start">
                        <x-slot:header>
                            <div class="flex flex-col items-start">
                                <div class="flex items-center">
                                    <x-heroicon-o-eye class="w-5 h-5 mr-2 text-primary-600" />
                                    <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300">
                                        {{ t('live_preview') }}
                                    </h1>
                                </div>
                                <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">
                                    {{ t('see_how_message_will_look') }}
                                </p>
                            </div>
                        </x-slot:header>
                        <x-slot:content>
                            <div class="sticky top-8 space-y-6">
                                <!-- Preview Card -->
                                <div class="rounded-lg">
                                    <div>
                                        <div class="preview-container rounded-md p-4 min-h-[400px]" x-cloak>
                                            <div x-show="loading.template"
                                                class="absolute inset-y-0 right-0 flex items-center pr-[14.75rem] pointer-events-none">
                                                <svg class="animate-spin h-8 w-8 text-primary-500"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4">
                                                    </circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <!-- WhatsApp Message Preview -->
                                            <div
                                                class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 max-w-sm mx-auto">
                                                <!-- File Preview in Message -->
                                                <div x-show="filePreview.url" class="mb-3">
                                                    <!-- Image Preview -->
                                                    <template x-if="filePreview.type === 'image'">
                                                        <div class="rounded-lg overflow-hidden">
                                                            <img :src="filePreview.url" class="w-full h-auto"
                                                                alt="Preview">
                                                        </div>
                                                    </template>

                                                    <!-- Video Preview -->
                                                    <template x-if="filePreview.type === 'video'">
                                                        <div class="rounded-lg overflow-hidden">
                                                            <video :src="filePreview.url" controls
                                                                class="w-full h-auto"></video>
                                                        </div>
                                                    </template>

                                                    <!-- Fallback for other file types -->
                                                    <template
                                                        x-if="filePreview.type !== 'image' && filePreview.type !== 'video'">
                                                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3">
                                                            <div class="flex items-center space-x-2">
                                                                <svg class="w-6 h-6 text-gray-400" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                                <span class="text-sm text-gray-600 font-medium"
                                                                    x-text="selectedFile?.name"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Header Preview -->
                                                <div x-show="preview.header" x-html="preview.header"
                                                    class="mb-3 font-medium text-gray-800 dark:text-gray-200 break-words">
                                                </div>

                                                <!-- Body Preview -->
                                                <div x-html="preview.body"
                                                    class="mb-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">
                                                </div>

                                                <!-- Footer Preview -->
                                                <div x-show="preview.footer" x-html="preview.footer"
                                                    class="text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-2 mt-3 break-words">
                                                </div>

                                                <!-- Buttons Preview -->
                                                <div x-show="templateData && templateData.buttons && templateData.buttons.length > 0"
                                                    class="mt-4 space-y-2">
                                                    <template x-for="button in (templateData?.buttons || [])"
                                                        :key="button.text">
                                                        <button type="button"
                                                            class="w-full p-3 text-sm text-center dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors duration-150"
                                                            x-text="button.text"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>
                </div>

                <!-- Sticky Navigation Buttons Bar -->
                <div
                    class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 z-10">
                    <div class="flex justify-between sm:justify-end items-center px-6 py-3 space-x-4 sm:space-x-12">
                        <!-- Previous Button -->
                        <button type="button" @click="prevStep" :disabled="currentStep === 1"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-primary-100 text-primary-700 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-slate-700 dark:border-slate-500 dark:text-slate-200 dark:hover:border-slate-400 dark:focus:ring-offset-slate-800 mx-2">

                            {{ t('previous') }}
                        </button>

                        <!-- Step Indicator -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">{{ t('step') }}</span>
                            <span class="text-sm font-bold text-primary-500" x-text="currentStep"></span>
                            <span class="text-sm text-gray-500">{{ t('of') }}</span>
                            <span class="text-sm font-bold text-gray-400" x-text="totalSteps"></span>
                        </div>

                        <!-- Next/Submit Buttons -->
                        <template x-if="currentStep < totalSteps">
                            <x-button.loading-button ype="button" @click="nextStep">
                                {{ t('next') }}
                            </x-button.loading-button>
                        </template>

                        <!-- Enhanced Submit Button with Real-time Validation -->
                        <template x-if="currentStep === totalSteps">
                            <button type="submit" :disabled="isSubmitting || !isFormValid"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-md shadow-sm transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                :class="{
                                    // Enabled state
                                    'text-white bg-success-600 hover:bg-success-500 focus-visible:outline-success-600 cursor-pointer':
                                        !isSubmitting && isFormValid,
                                    // Disabled state
                                    'text-gray-400 bg-gray-200 cursor-not-allowed': isSubmitting || !isFormValid,
                                    // Submitting state
                                    'text-white bg-success-500': isSubmitting
                                }">

                                <template x-if="isSubmitting">
                                    <div class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ t('processing') }}
                                    </div>
                                </template>

                                <template x-if="!isSubmitting">
                                    <div class="flex items-center">
                                        <x-heroicon-o-paper-airplane class="w-5 h-5 mr-0 sm:mr-2" />
                                        <span class="hidden sm:inline">
                                            {{ isset($campaign) ? t('update_campaign') : t('create_campaign') }}
                                        </span>
                                    </div>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    <script>
        function campaignForm() {
            return {
                // Form state
                currentStep: 1,
                totalSteps: 4,
                isSubmitting: false,
                isDragOver: false,

                // Steps configuration
                steps: [{
                        title: '{{ t('basic_information') }}'
                    },
                    {
                        title: '{{ t('contact_selection') }}'
                    },
                    {
                        title: '{{ t('variables_files') }}'
                    },
                    {
                        title: '{{ t('scheduling') }}'
                    }
                ],

                // Form data
                formData: {
                    campaign_name: '{{ old('campaign_name', $campaign->name ?? '') }}',
                    rel_type: '{{ old('rel_type', $campaign->rel_type ?? '') }}',
                    template_id: '{{ old('template_id', $campaign->template_id ?? '') }}',
                    select_all: {{ old('select_all', $campaign->select_all ?? 'false') ? 'true' : 'false' }},
                    send_now: {{ old('send_now', $campaign->send_now ?? 'true') ? 'true' : 'false' }},
                    scheduled_send_time: '{{ old('scheduled_send_time', isset($campaign) ? $campaign->scheduled_send_time : '') }}',

                },

                // Template and contact data
                templateData: null,
                contacts: [],
                filteredContacts: [],
                listSelectAll: false,
                search: '',
                // Filters
                filters: {
                    status_id: [], // Changed from string to array
                    source_id: [], // Changed from string to array
                    group_id: []   // Changed from string to array
                },

                // Variables
                variables: {
                    header: @json($existingVariables['header'] ?? []),
                    body: @json($existingVariables['body'] ?? []),
                    footer: @json($existingVariables['footer'] ?? []),
                },
                // Update selectedContacts initialization
                selectedContacts: @json($selectedContacts ?? []),
                totalSelectedFromCampaign: {{ $totalSelectedCount ?? 0 }},
                isEditMode: {{ isset($campaign) && $isEditMode ? 'true' : 'false' }},
                originallySelectedCount: {{ $totalSelectedCount ?? 0 }},

                // Add existing file data
                existingFileData: @json($existingFile ?? null),
                // File handling
                selectedFile: null,
                filePreview: {
                    url: '',
                    type: ''
                },

                // UI state
                loading: {
                    contacts: false,
                    template: false,
                    submit: false
                },

                // Error handling
                errors: {},

                // Toast notifications
                toast: {
                    show: false,
                    type: 'info',
                    message: ''
                },

                // Preview
                preview: {
                    header: '',
                    body: '{{ t('select_template_see_preview') }}',
                    footer: ''
                },
                mergeFields: @js($mergeFields),
                totalContactsCount: 0,
                previousTemplateId: null,
                isInitializing: true,
                validationIssues: [],
                pagination: {
                    currentPage: 1,
                    hasMore: true,
                    isLoadingMore: false,

                },
                searchTimeout: null,
                scrollTimeout: null, // Add timeout for scroll debouncing
                isSearching: false,
                searchResults: [],
                isInSearchMode: false,
                originalContacts: [], // Store original paginated contacts
                searchTotalCount: 0,
                dropdownStates: {
                            status: { open: false, search: '' },
                            source: { open: false, search: '' },
                            group: { open: false, search: '' }
                },

        // Add these options arrays (you can initialize from your blade data)
        filterOptions: {
            status: @js($statuses->map(fn($s) => ['value' => $s->id, 'label' => $s->name])->toArray()),
            source: @js($sources->map(fn($s) => ['value' => $s->id, 'label' => $s->name])->toArray()),
            group: @js($groups->map(fn($g) => ['value' => $g->id, 'label' => $g->name])->toArray())
        },

        // ... your existing computed properties ...

        // Add these new methods for dropdown functionality
        getDisplayText(filterType) {
            const selected = this.filters[filterType + '_id'] || [];
            const filterName = filterType.charAt(0).toUpperCase() + filterType.slice(1);

            if (!selected || selected.length === 0) {
                return `Select ${filterName}s`;
            }

            if (selected.length === 1) {
                const option = this.filterOptions[filterType].find(opt => opt.value == selected[0]);
                return option ? option.label : `1 ${filterName} selected`;
            }

            return `${selected.length} ${filterName}s selected`;
        },

        getFilteredOptions(filterType) {
            const search = this.dropdownStates[filterType].search;
            if (!search.trim()) {
                return this.filterOptions[filterType];
            }

            return this.filterOptions[filterType].filter(option =>
                option.label.toLowerCase().includes(search.toLowerCase())
            );
        },

        toggleDropdown(filterType) {
            this.dropdownStates[filterType].open = !this.dropdownStates[filterType].open;
            // Close other dropdowns
            Object.keys(this.dropdownStates).forEach(key => {
                if (key !== filterType) {
                    this.dropdownStates[key].open = false;
                }
            });
        },

        closeDropdown(filterType) {
            this.dropdownStates[filterType].open = false;
        },

        // Computed property to check if any filter is selected
        get hasAnyFilterSelected() {
            return ['status', 'source', 'group'].some(filterType =>
                (this.filters[filterType + '_id'] || []).length > 0
            );
        },

       toggleFilterOption(filterType, value) {
    let selected = [...(this.filters[filterType + '_id'] || [])];
    const index = selected.indexOf(value);

    if (index > -1) {
        selected.splice(index, 1);
    } else {
        selected.push(value);
    }

    this.filters[filterType + '_id'] = selected;

    // Reset contact selection when filters change
    this.selectedContacts = [];
    this.listSelectAll = false;
    this.formData.select_all = false;

    this.handleFilterChange();
},

       isFilterSelected(filterType, value) {
    // Convert value to integer for comparison
    const intValue = parseInt(value);
    const selectedValues = (this.filters[filterType + '_id'] || []).map(id => parseInt(id));
    return selectedValues.includes(intValue);
},

        selectAllFilters(filterType) {
            const filtered = this.getFilteredOptions(filterType);
            let selected = [...(this.filters[filterType + '_id'] || [])];

            filtered.forEach(option => {
                if (!selected.includes(option.value)) {
                    selected.push(option.value);
                }
            });

            this.filters[filterType + '_id'] = selected;
            this.dropdownStates[filterType].search = '';
            this.handleFilterChange();
        },
clearAllFilters(filterType) {
    const search = this.dropdownStates[filterType].search;

    if (search.trim()) {
        // Clear only filtered results
        const filtered = this.getFilteredOptions(filterType);
        let selected = [...(this.filters[filterType + '_id'] || [])];

        filtered.forEach(option => {
            const index = selected.indexOf(option.value);
            if (index > -1) {
                selected.splice(index, 1);
            }
        });

        this.filters[filterType + '_id'] = selected;
        this.dropdownStates[filterType].search = '';
    } else {
        // Clear all for this filter type
        this.filters[filterType + '_id'] = [];
    }

    // Reset contact selection when filters change
    this.selectedContacts = [];
    this.listSelectAll = false;
    this.formData.select_all = false;

    this.handleFilterChange();
},
clearAllFiltersGlobally() {
    this.filters = {
        status_id: [],
        source_id: [],
        group_id: []
    };
    this.selectedContacts = [];
    this.listSelectAll = false;
    this.formData.select_all = false;
    this.totalContactsCount = 0;
    this.handleFilterChange();
},
        getSelectedFilterCount(filterType) {
            return (this.filters[filterType + '_id'] || []).length;
        },
                // Computed properties
                get formAction() {
                    return '{{ isset($campaign) ? tenant_route('tenant.campaign.update', ['id' => $campaign->id]) : tenant_route('tenant.store') }}';
                },
                get isFormValid() {
                    this.validationIssues = []; // Reset issues

                    const step1Valid = this.validateStep1();
                    const step2Valid = this.validateStep2();
                    const step3Valid = this.validateStep3();
                    const step4Valid = this.validateStep4();

                    return step1Valid && step2Valid && step3Valid && step4Valid;
                },
                validateStep1() {
                    let isValid = true;

                    if (!this.formData.campaign_name?.trim()) {
                        this.validationIssues.push('Campaign name is required');
                        isValid = false;
                    }

                    if (!this.formData.rel_type) {
                        this.validationIssues.push('Relation type must be selected');
                        isValid = false;
                    }

                    if (!this.formData.template_id) {
                        this.validationIssues.push('Template must be selected');
                        isValid = false;
                    }

                    return isValid;
                },

                validateStep2() {
                    let isValid = true;

                    // Check if contacts are selected (either select_all or individual contacts)
                    if (!this.formData.select_all && this.selectedContacts.length === 0) {
                        this.validationIssues.push('At least one contact must be selected');
                        isValid = false;
                    }

                    // If select_all is true but no contacts available
                    if (this.formData.select_all && this.recipientCount === 0) {
                        this.validationIssues.push('No contacts available for the selected criteria');
                        isValid = false;
                    }

                    return isValid;
                },

                validateStep3() {
                    let isValid = true;

                    if (!this.templateData) {
                        return true; // Can't validate without template data
                    }

                    // Validate header variables
                    if (this.templateData.header?.params_count > 0) {
                        for (let i = 0; i < this.templateData.header.params_count; i++) {
                            if (!this.variables.header[i]?.trim()) {
                                this.validationIssues.push(`Header variable ${i + 1} is required`);
                                isValid = false;
                            }
                        }
                    }

                    // Validate body variables
                    if (this.templateData.body?.params_count > 0) {
                        for (let i = 0; i < this.templateData.body.params_count; i++) {
                            if (!this.variables.body[i]?.trim()) {
                                this.validationIssues.push(`Body variable ${i + 1} is required`);
                                isValid = false;
                            }
                        }
                    }

                    // Validate footer variables
                    if (this.templateData.footer?.params_count > 0) {
                        for (let i = 0; i < this.templateData.footer.params_count; i++) {
                            if (!this.variables.footer[i]?.trim()) {
                                this.validationIssues.push(`Footer variable ${i + 1} is required`);
                                isValid = false;
                            }
                        }
                    }

                    // Validate file requirement
                    if (['IMAGE', 'VIDEO', 'DOCUMENT'].includes(this.templateData.header?.format) && !this.selectedFile) {
                        const fileType = this.templateData.header.format.toLowerCase();
                        this.validationIssues.push(`${fileType} file is required for this template`);
                        isValid = false;
                    }

                    return isValid;
                },

                validateStep4() {
                    let isValid = true;


                    // If not sending now, scheduled time is required
                    if (!this.formData.send_now && !this.formData.scheduled_send_time) {

                        this.validationIssues.push('Scheduled send time is required when not sending immediately');
                        isValid = false;
                    }

                    // Validate scheduled time is in the future
                    if (!this.formData.send_now && this.formData.scheduled_send_time) {
                        // Use flatpickr's parseDate function with the configured format
                        const currentFormat = `${date_format} ${time_format}`;
                        let scheduledDate;

                        try {
                            // Use flatpickr's built-in parseDate function
                            scheduledDate = flatpickr.parseDate(this.formData.scheduled_send_time, currentFormat);
                        } catch (error) {
                            scheduledDate = null;
                        }

                        const now = new Date();
                        if (!scheduledDate || isNaN(scheduledDate.getTime())) {
                            this.validationIssues.push('Invalid scheduled date format');
                            isValid = false;
                        } else if (scheduledDate <= now) {
                            this.validationIssues.push('Scheduled time must be in the future');
                            isValid = false;
                        }
                    }
                    return isValid;
                },

                get recipientCount() {
                    if (this.formData.select_all) {
                        // When select_all is enabled, always show total available contacts
                        // (not the original campaign count)
                        return this.totalContactsCount || 0;
                    }

                    // For individual selection
                    if (this.isEditMode) {
                        return this.selectedContacts.length;
                    }

                    return this.selectedContacts.length || 0;
                },

                get hasVariablesOrFiles() {
                    if (!this.templateData) return false;

                    const hasHeaderParams = this.templateData.header?.params_count > 0;
                    const hasBodyParams = this.templateData.body?.params_count > 0;
                    const hasFooterParams = this.templateData.footer?.params_count > 0;
                    const hasFileRequirement = ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(this.templateData
                        ?.header
                        ?.format);

                    return hasHeaderParams || hasBodyParams || hasFooterParams || hasFileRequirement;
                },
                handleTributeEvent() {

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

                },
                // Add method to determine file type from URL
                getFileTypeFromUrl(url) {
                    const extension = url.split('.').pop().toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) return 'image';
                    if (['mp4', '3gp', 'mov'].includes(extension)) return 'video';
                    if (['pdf', 'doc', 'docx'].includes(extension)) return 'document';
                    return 'file';
                },

                // Update the initializeVariables method to preserve existing values
                initializeVariables() {
                    if (!this.templateData) return;

                    // Get existing values
                    const existingHeader = @json($existingVariables['header'] ?? []);
                    const existingBody = @json($existingVariables['body'] ?? []);
                    const existingFooter = @json($existingVariables['footer'] ?? []);
                    // Initialize arrays with proper length, preserving existing values
                    this.variables.header = Array(this.templateData.header?.params_count || 0).fill('').map((_,
                            index) =>
                        existingHeader[index] || ''
                    );
                    this.variables.body = Array(this.templateData.body?.params_count || 0).fill('').map((_,
                            index) =>
                        existingBody[index] || ''
                    );
                    this.variables.footer = Array(this.templateData.footer?.params_count || 0).fill('').map((_,
                            index) =>
                        existingFooter[index] || ''
                    );
                },
                // Update the init() method
                init() {

                    this.isInitializing = true;
                    this.scrollTimeout = null; // Initialize scroll timeout
                    const existingHeader = @json($existingVariables['header'] ?? []);
                    const existingBody = @json($existingVariables['body'] ?? []);
                    const existingFooter = @json($existingVariables['footer'] ?? []);

                    // Initialize existing file if present
                    if (this.existingFileData) {
                        this.selectedFile = {
                            name: this.existingFileData.filename.split('/').pop(),
                            size: 0 // You might want to get actual size from server
                        };
                        this.filePreview = {
                            url: this.existingFileData.url,
                            type: this.getFileTypeFromUrl(this.existingFileData.url)
                        };
                    }

                    // Load existing data if editing
                    if (this.formData.rel_type) {
                        this.loadContacts();
                    }
                    if (this.formData.template_id) {
                        this.handleTemplateChange();
                    }
                    if (this.formData.template_id) {
                        this.handleTemplateChange().then(() => {
                            // Mark initialization as complete after template loads
                            this.isInitializing = false;
                        });
                    } else {
                        this.isInitializing = false;
                    }
                    // Set filters from campaign if editing

@if (isset($campaign))
    const relData = @json(json_decode($campaign->rel_data ?? '{}', true));

    // Convert single values to arrays for multi-select compatibility
    if (relData.status_id !== null && relData.status_id !== undefined) {
        this.filters.status_id = Array.isArray(relData.status_id)
            ? relData.status_id.map(id => parseInt(id))
            : (relData.status_id ? [parseInt(relData.status_id)] : []);
    }
    if (relData.source_id !== null && relData.source_id !== undefined) {
        this.filters.source_id = Array.isArray(relData.source_id)
            ? relData.source_id.map(id => parseInt(id))
            : (relData.source_id ? [parseInt(relData.source_id)] : []);
    }
    if (relData.group_id !== null && relData.group_id !== undefined) {
        this.filters.group_id = Array.isArray(relData.group_id)
            ? relData.group_id.map(id => parseInt(id))
            : (relData.group_id ? [parseInt(relData.group_id)] : []);
    }
@endif

                    // Watch for changes to trigger validation
                    this.$watch('formData.campaign_name', () => this.clearFieldError('campaign_name'));
                    this.$watch('formData.rel_type', () => this.clearFieldError('rel_type'));
                    this.$watch('formData.template_id', () => this.clearFieldError('template_id'));
                    this.$watch('selectedContacts', () => this.clearFieldError('contacts'));
                    this.$watch('formData.select_all', () => this.clearFieldError('contacts'));
                    this.$watch('formData.send_now', () => this.clearFieldError('scheduled_send_time'));
                    this.$watch('formData.scheduled_send_time', () => this.clearFieldError('scheduled_send_time'));

                    // Watch variables arrays
                    this.$watch('variables.header', () => this.clearFieldError('variables'), {
                        deep: true
                    });
                    this.$watch('variables.body', () => this.clearFieldError('variables'), {
                        deep: true
                    });
                    this.$watch('variables.footer', () => this.clearFieldError('variables'), {
                        deep: true
                    });
                    this.$watch('selectedFile', () => this.clearFieldError('file'));
                },
                clearFieldError(field) {
                    if (this.errors[field]) {
                        delete this.errors[field];
                    }
                },
                // Initialize listSelectAll property


                // New method: Client-side search using separate API
                async searchContacts() {
                    // Clear existing timeout
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    // If search is empty, return to normal pagination mode
                    if (!this.search.trim()) {
                        this.exitSearchMode();
                        return;
                    }

                    this.isSearching = true;

                    // Debounce search requests (wait 300ms after user stops typing)
                    this.searchTimeout = setTimeout(async () => {
                        await this.performSearch();
                        this.isSearching = false;
                    }, 300);
                },

                // Perform the actual search
                async performSearch() {
                    const searchUrl = "{{ tenant_route('tenant.campaign.search-contacts') }}";

                    if (!this.formData.rel_type || !this.search.trim()) return;

                    try {
                        const response = await this.apiCall(searchUrl, {
                            rel_type: this.formData.rel_type,
                           status_ids: this.filters.status_id, // Changed to array
                        source_ids: this.filters.source_id, // Changed to array
                        group_ids: this.filters.group_id,   // Changed to array
                            search: this.search.trim()
                        });

                        if (response.success) {
                            // Store original contacts if not already in search mode
                            if (!this.isInSearchMode) {
                                this.originalContacts = [...this.contacts];
                            }

                            // Enter search mode
                            this.isInSearchMode = true;
                            this.searchResults = response.data || [];
                            this.searchTotalCount = response.total || 0;

                            // Update display
                            this.filteredContacts = this.searchResults;

                            // Reset list select all
                            this.listSelectAll = false;

                        } else {
                            showNotification(`{{ t('search_failed') }}`, 'danger');
                        }
                    } catch (error) {
                        showNotification(`{{ t('search_error') }}: ${error.message}`, 'danger');
                    }
                },

                // Exit search mode and return to pagination
                exitSearchMode() {
                    this.isInSearchMode = false;
                    this.searchResults = [];
                    this.searchTotalCount = 0;

                    // Restore original contacts or reload if needed
                    if (this.originalContacts.length > 0) {
                        this.contacts = [...this.originalContacts];
                        this.filteredContacts = this.contacts;
                    } else {
                        // Reload contacts if we don't have original data
                        this.loadContacts(true);
                    }

                    this.listSelectAll = false;
                },

                // Update existing filterContacts method to use search API
                filterContacts() {
                    // Use the new search functionality instead of client-side filtering
                    this.searchContacts();
                },

                toggleListSelectAll() {
                    // Only proceed if not using main select all
                    if (this.formData.select_all) return;

                    // Toggle the list select all state
                    this.listSelectAll = !this.listSelectAll;
                    // Get IDs of currently displayed contacts (search results or paginated results)
                    const displayedIds = this.filteredContacts.map(c => c.id);

                    if (this.listSelectAll) {
                        // Add all displayed IDs that aren't already selected
                        this.selectedContacts = [...new Set([...this.selectedContacts, ...displayedIds])];
                    } else {
                        // Remove only the displayed IDs from selection
                        this.selectedContacts = this.selectedContacts.filter(id => !displayedIds.includes(id));
                    }
                },

                // Add method to handle filter changes properly
                async handleFilterChange() {
                    // Reset contact selection when filters change
                    this.selectedContacts = [];
                    this.listSelectAll = false;
                    this.formData.select_all = false;

                    // Reload contacts when filters change
                    await this.loadContacts(true);

                    // If select_all was enabled, update the count with new filters
                    // (This will now be 0 since we reset select_all above)
                    if (this.formData.select_all) {
                        await this.loadContactCount();
                    }
                },
                // Step navigation
                nextStep() {

                    if (this.validateCurrentStep()) {
                        if (this.currentStep < this.totalSteps) {
                            this.currentStep++;
                        }
                    }
                    this.filteredContacts = this.contacts;

                    this.handleTributeEvent();
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },

                // Validation
                validateCurrentStep() {
                    this.errors = {};

                    switch (this.currentStep) {
                        case 1:
                            return this.validateBasicInfo();
                        case 2:
                            return this.validateContactSelection();
                        case 3:
                            return this.validateVariables();
                        case 4:
                            return this.validateScheduling();
                        default:
                            return true;
                    }
                },

                validateBasicInfo() {
                    let isValid = true;

                    if (!this.formData.campaign_name.trim()) {
                        this.errors.campaign_name = '{{ t('campaign_name_required') }}';
                        isValid = false;
                    }

                    if (!this.formData.rel_type) {
                        this.errors.rel_type = '{{ t('relation_type_required') }}';
                        isValid = false;
                    }

                    if (!this.formData.template_id) {
                        this.errors.template_id = '{{ t('template_required') }}';
                        isValid = false;
                    }

                    if (!isValid) {
                        showNotification(`{{ t('please_fill_required_fields') }}`, 'danger');

                    }

                    return isValid;
                },

                validateContactSelection() {
                    if (!this.formData.select_all && this.selectedContacts.length === 0) {
                        this.errors.contacts = '{{ t('please_select_at_least_one_contact') }}';
                        showNotification(`{{ t('please_select_contacts') }}`, 'danger');

                        return false;
                    }
                    return true;
                },

                validateVariables() {
                    let isValid = true;

                    // Validate header variables
                    if (this.templateData?.header?.params_count > 0) {
                        for (let i = 0; i < this.templateData.header.params_count; i++) {
                            if (!this.variables.header[i]?.trim()) {
                                showNotification(`{{ t('please_fill_all_header_variables') }}`, 'danger');

                                isValid = false;
                                break;
                            }
                        }
                    }

                    // Validate body variables
                    if (this.templateData?.body?.params_count > 0) {
                        for (let i = 0; i < this.templateData.body.params_count; i++) {
                            if (!this.variables.body[i]?.trim()) {
                                showNotification(`{{ t('please_fill_all_body_variables') }}`, 'danger');

                                isValid = false;
                                break;
                            }
                        }
                    }

                    // Validate footer variables
                    if (this.templateData?.footer?.params_count > 0) {
                        for (let i = 0; i < this.templateData.footer.params_count; i++) {
                            if (!this.variables.footer[i]?.trim()) {
                                showNotification(`{{ t('please_fill_all_footer_variables') }}`, 'danger');

                                isValid = false;
                                break;
                            }
                        }
                    }

                    // Validate file requirement with enhanced validation
                    if (['IMAGE', 'VIDEO', 'DOCUMENT'].includes(this.templateData?.header?.format) && !this
                        .selectedFile) {
                        this.errors.file = '{{ t('file_required_for_this_template') }}';
                        showNotification(`{{ t('please_upload_required_file') }}`, 'danger');

                        isValid = false;
                    }

                    return isValid;
                },

                validateScheduling() {
                    // Clear previous errors

                    // If send now is selected, no validation needed
                    if (this.formData.send_now) {
                        return true;
                    }
                    if (!this.formData.send_now && !this.formData.scheduled_send_time) {
                        this.errors.scheduled_send_time = '{{ t('please_set_schedule_time_or_send_now') }}';
                        showNotification(`{{ t('please_set_schedule_time') }}`, 'danger');

                        return false;
                    }
                    return true;
                },


                // Event handlers
                async handleRelTypeChange() {
                    this.selectedContacts = [];
                    this.search = ''; // Clear search when relation type changes
                    this.exitSearchMode(); // Exit search mode
                    this.contacts = [];
                    await this.loadContacts(true);
                },

                async handleTemplateChange() {
                    // Store previous template ID to detect actual changes
                    if (!this.isInitializing) {
                        this.clearPreviewOnChange();
                    }

                    if (!this.formData.template_id) {
                        this.templateData = null;
                        this.updatePreview();
                        return;
                    }
                    const templatesUrl = "{{ tenant_route('tenant.campaign.template') }}";
                    try {
                        this.loading.template = true;
                        const response = await this.apiCall(templatesUrl, {
                            template_id: this.formData.template_id
                        });

                        if (response.success) {
                            this.templateData = response.data;

                            // Convert all params_count values to numbers
                            if (this.templateData.header) {
                                this.templateData.header.params_count = Number(this.templateData.header.params_count) || 0;
                            }
                            if (this.templateData.body) {
                                this.templateData.body.params_count = Number(this.templateData.body.params_count) || 0;
                            }
                            if (this.templateData.footer) {
                                this.templateData.footer.params_count = Number(this.templateData.footer.params_count) || 0;
                            }

                            // Only initialize variables if we don't have existing ones (new campaign)
                            const isEditing = {{ isset($campaign) ? 'true' : 'false' }};
                            if (!isEditing) {
                                this.initializeVariables();
                            }

                            this.updatePreview();
                        } else {
                            showNotification(`{{ t('failed_to_load_template') }}`, 'danger');
                        }
                    } catch (error) {
                        showNotification(`{{ t('error_loading_template') }}: ${error.message}`, 'danger');
                    } finally {
                        this.loading.template = false;
                    }
                },

                clearPreviewOnChange() {
                    // Clear file selection
                    this.selectedFile = null;

                    // Clear file preview
                    if (this.filePreview.url && this.filePreview.url !== '#' && this.filePreview.url.startsWith(
                            'blob:')) {
                        URL.revokeObjectURL(this.filePreview.url);
                    }

                    this.filePreview = {
                        url: '',
                        type: ''
                    };

                    // Clear file input
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }

                    // Clear variables
                    this.variables = {
                        header: [],
                        body: [],
                        footer: []
                    };

                    // Clear file errors
                    this.errors.file = '';

                    // Clear preview
                    this.preview = {
                        header: '',
                        body: '{{ t('loading_template') }}...',
                        footer: ''
                    };
                },
                handleSelectAllChange() {

                    if (this.formData.select_all) {
                        // When "Select All" is checked, load total count
                        if (this.isEditMode && this.originallySelectedCount > 0) {
                            // Don't change the count, it should remain as original

                        } else {
                            // For new campaigns, load the current count
                            this.loadContactCount();
                        }
                        // Clear individual selections since we don't need them
                        this.selectedContacts = [];
                    } else {
                        if (this.isEditMode) {
                            this.selectedContacts = @json($selectedContacts ?? []);
                            // Don't clear them
                        } else {
                            this.selectedContacts = [];
                        }
                        // When "Select All" is unchecked, show individual selection
                    }
                },

                // Contact management
                async loadContacts(resetPagination = true) {
                    const contactsUrl = "{{ tenant_route('tenant.campaign.contacts-paginated') }}";

                    if (!this.formData.rel_type) return;
                    if (this.isInSearchMode && !resetPagination) return;

                    // Prevent multiple concurrent requests
                    if (this.loading.contacts || (!resetPagination && this.pagination.isLoadingMore)) {
                        return;
                    }

                    try {
                        if (resetPagination) {
                            this.loading.contacts = true;
                            this.pagination.currentPage = 1;
                            this.pagination.hasMore = true;
                            this.contacts = [];
                            this.filteredContacts = [];

                            if (this.isInSearchMode) {
                                this.isInSearchMode = false;
                                this.searchResults = [];
                                this.search = '';
                            }
                        } else {
                            this.pagination.isLoadingMore = true;
                        }

                        const response = await this.apiCall(contactsUrl, {
                            rel_type: this.formData.rel_type,
                        status_ids: this.filters.status_id, // Changed to array
            source_ids: this.filters.source_id, // Changed to array
            group_ids: this.filters.group_id,   // Changed to array
                            page: this.pagination.currentPage,

                        });

                        if (response.success) {
                            const newContacts = response.data ?? [];

                            if (resetPagination) {
                                this.contacts = newContacts;
                                this.originalContacts = [...newContacts];
                            } else {
                                // Prevent duplicate contacts by checking IDs
                                const existingIds = new Set(this.contacts.map(c => c.id));
                                const uniqueNewContacts = newContacts.filter(contact => !existingIds.has(contact.id));

                                this.contacts = [...this.contacts, ...uniqueNewContacts];
                                this.originalContacts = [...this.contacts];
                            }

                          	console.log("contacts",  this.contacts.length);

                            this.filteredContacts = this.contacts;
                          	console.log("filteredContacts",  this.filteredContacts.length);
                            this.pagination.hasMore = response.has_more;

                            this.totalContactsCount = response.total;


                            // In edit mode, ensure ALL originally selected contacts remain selected
                            if (this.isEditMode && !this.formData.select_all) {
                                // Keep all selected contacts in edit mode
                                // Don't modify the selection when loading contacts
                            } else if (!this.formData.select_all) {
                                if (this.filters.status_id || this.filters.source_id || this.filters.group_id) {
                                    // When filters are applied, don't auto-select contacts
                                    // Let user manually select contacts from filtered results
                                    this.selectedContacts = [];
                                } else {
                                    // No filters - just validate existing selections against loaded contacts
                                    const validContactIds = this.contacts.map(c => c.id);
                                    this.selectedContacts = this.selectedContacts.filter(id =>
                                        validContactIds.includes(parseInt(id))
                                    );
                                }
                            }
                        } else {
                            showNotification(`{{ t('failed_to_load_contacts') }}`, 'danger');
                        }
                    } catch (error) {
                        showNotification(`{{ t('error_loading_contacts') }}` + error.message, 'danger');
                    } finally {
                        this.loading.contacts = false;
                        this.pagination.isLoadingMore = false;
                    }
                },
                // Add this new method for infinite scroll
                async loadMoreContacts() {
                    // Multiple checks to ensure we don't send duplicate requests
                    if (!this.pagination.hasMore ||
                        this.pagination.isLoadingMore ||
                        this.isInSearchMode ||
                        this.loading.contacts) {
                        return;
                    }

                    this.pagination.currentPage++;
                    await this.loadContacts(false);
                },

                // Add this method to check scroll position
                checkScrollBottom(event) {
                    // Prevent multiple requests when already loading
                    if (this.isInSearchMode || this.pagination.isLoadingMore || !this.pagination.hasMore || this.loading.contacts) {
                        return;
                    }

                    const container = event.target;
                    const threshold = 100; // Load more when 100px from bottom

                    if (container.scrollTop + container.clientHeight >= container.scrollHeight - threshold) {
                        // Debounce scroll events to prevent multiple rapid calls
                        if (this.scrollTimeout) {
                            clearTimeout(this.scrollTimeout);
                        }

                        this.scrollTimeout = setTimeout(() => {
                            this.loadMoreContacts();
                        }, 150); // 150ms debounce
                    }
                },
                async loadContactCount() {
                    if (!this.formData.rel_type) return;
                    const contactsCountUrl = "{{ tenant_route('tenant.campaign.contacts-counts') }}";

                    try {
                        const response = await this.apiCall(contactsCountUrl, {
                            rel_type: this.formData.rel_type,
                            status_ids: this.filters.status_id, // Changed to array
            source_ids: this.filters.source_id, // Changed to array
            group_ids: this.filters.group_id,   // Changed to array
                            select_all: true
                        });

                        if (response.success) {
                            // Update contact count display
                            this.totalContactsCount = response.count || 0;
                        }
                    } catch (error) {
                        console.error('Error counting contacts:', error);
                        this.totalContactsCount = 0;
                    }
                },



                // Enhanced file handling with validation
                handleDragOver(event) {
                    this.isDragOver = true;
                },

                handleDrop(event) {
                    this.isDragOver = false;
                    const files = event.dataTransfer.files;
                    if (files.length > 0) {
                        this.handleFileSelect({
                            target: {
                                files
                            }
                        });
                    }
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Enhanced file validation
                    if (!this.validateFile(file)) return;
                    this.selectedFile = file;
                    this.createFilePreview(file);
                    this.updatePreview();
                },

                validateFile(file) {

                    if (!this.templateData?.allowed_file_types) return true;

                    const {
                        max_file_size,
                        allowed_file_types
                    } = this.templateData;
                    const maxSizeBytes = max_file_size || 5242880; // default to 5MB
                    const allowedExtensions = allowed_file_types?.extensions || '';

                    // Check file size
                    if (file.size > maxSizeBytes) {
                        this.errors.file = `{{ t('file_size_exceeds') }} ${this.formatFileSize(maxSizeBytes)}`;
                        showNotification(this.errors.file, 'danger');

                        return false;
                    }

                    // Check file extension
                    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                    const extensionList = allowedExtensions
                        .split(',')
                        .map(ext => ext.trim().toLowerCase())
                        .filter(ext => ext); // remove empty entries


                    if (extensionList.length > 0 && !extensionList.includes(fileExtension)) {
                        this.errors.file =
                            `{{ t('invalid_file_type') }}. {{ t('allowed_types') }}: ${allowedExtensions}`;
                        showNotification(this.errors.file, 'danger');
                        return false;
                    }

                    // Clear any previous errors
                    this.errors.file = '';
                    return true;
                },

                createFilePreview(file) {
                    const fileType = file?.type?.split('/')[0] || '';

                    if (fileType === 'image') {
                        this.filePreview = {
                            url: URL.createObjectURL(file),
                            type: 'image'
                        };
                    } else if (fileType === 'video') {
                        this.filePreview = {
                            url: URL.createObjectURL(file),
                            type: 'video'
                        };
                    } else {
                        this.filePreview = {
                            url: '#',
                            type: fileType || 'unknown'
                        };
                    }
                },


                removeFile() {
                    this.selectedFile = null;
                    this.filePreview = {
                        url: '',
                        type: ''
                    };
                    this.$refs.fileInput.value = '';
                    this.errors.file = '';
                    this.updatePreview();
                },

                // Preview management
                updatePreview() {
                    if (!this.templateData) {
                        this.preview = {
                            header: '',
                            body: '{{ t('select_template_to_see_preview') }}',
                            footer: ''
                        };
                        return;
                    }

                    // Update header preview
                    this.preview.header = this.replaceVariables(
                        this.templateData.header?.text || '',
                        this.variables.header
                    );

                    // Update body preview
                    this.preview.body = this.replaceVariables(
                        this.templateData.body?.text || '',
                        this.variables.body
                    );

                    // Update footer preview
                    this.preview.footer = this.replaceVariables(
                        this.templateData.footer?.text || '',
                        this.variables.footer
                    );
                },

                replaceVariables(text, variables) {
                    if (!text) return '';

                    let result = text;

                    variables.forEach((variable, index) => {
                        // Create placeholder like {{ 1 }}, {{ 2 }}, etc.
                        const placeholder = new RegExp(`\\{\\{\\s*${index + 1}\\s*\\}\\}`, 'g');

                        // Fallback value if variable is not provided
                        const value = variable || `[${('variable')} ${index + 1}]`;

                        // Replace placeholder with styled span
                        result = result.replace(
                            placeholder,
                            `<span class="text-primary-600 dark:text-primary-500 font-medium">${value}</span>`
                        );
                    });

                    return result;
                },
 appendFilter(formData, key, value) {
    if (Array.isArray(value)) {
        // Handle multiple values
        value.forEach((val, index) => {
            formData.append(`${key}[${index}]`, val);
        });
    } else if (value) {
        // Handle single value (string or number)
        formData.append(key, value);
    }
},


                // Form submission
                async handleSubmit() {
                    if (!this.validateCurrentStep()) return;

                    this.isSubmitting = true;

                    try {
                        const formData = new FormData();

                        // Add basic form data
                        Object.keys(this.formData).forEach(key => {
                            let value = this.formData[key];

                            // Convert boolean values to integers for Laravel validation
                            if (key === 'send_now' || key === 'select_all') {
                                value = value ? '1' : '0';
                            }

                            formData.append(key, value);
                        });
                        if ({{ isset($campaign) ? 'true' : 'false' }}) {
                            formData.append('_method', 'PUT');
                        }
                        // Add filters
                      // Add filters dynamically
this.appendFilter(formData, 'status_name', this.filters.status_id);
this.appendFilter(formData, 'source_name', this.filters.source_id);
this.appendFilter(formData, 'group_name', this.filters.group_id);

                        // Add selected contacts if not select all
                        if (!this.formData.select_all) {
                            this.selectedContacts.forEach(contactId => {
                                formData.append('relation_type_dynamic[]', contactId);
                            });
                        }
                        // Add variables
                        this.variables.header.forEach((variable, index) => {
                            formData.append(`headerInputs[${index}]`, variable);
                        });
                        this.variables.body.forEach((variable, index) => {
                            formData.append(`bodyInputs[${index}]`, variable);
                        });
                        this.variables.footer.forEach((variable, index) => {
                            formData.append(`footerInputs[${index}]`, variable);
                        });

                        // Add file if selected
                        if (this.selectedFile) {
                            formData.append('file', this.selectedFile);
                        }
                        const response = await fetch(this.formAction, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            showNotification(result.message, 'success');

                            if (result.redirect) {
                                setTimeout(() => {
                                    window.location.href = result.redirect;
                                }, 1500);
                            }
                        } else {
                            showNotification(result.message, 'danger');

                            if (result.errors) {
                                this.errors = result.errors;
                            }
                        }

                    } catch (error) {
                        showNotification('error_submitting_campaign' + error.message, 'danger');

                    } finally {
                        this.isSubmitting = false;
                    }
                },

                // Utility functions
                async apiCall(endpoint, data = {}) {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return await response.json();
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        }
        window.addEventListener("load", function() {
            window.flatePickrWithTime = function() {
                const datePicker = flatpickr("#scheduled_datepicker", {
                    dateFormat: `${date_format} ${time_format}`,
                    enableTime: true,
                    allowInput: true,
                    disableMobile: true,
                    time_24hr: is24Hour,
                    minDate: "today",

                });
                datePicker.open();
                document.getElementById("scheduled_datepicker").focus();
            };
        });
    </script>
</x-app-layout>
