<div class="mx-auto ">
    <x-slot:title>
        {{ t('whatsapp_auto_lead') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('application_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-tenant-whatsmark-settings-navigation wire:ignore />
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit.prevent="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('whatsapp_auto_lead') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('automate_lead_generation') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div x-data="{ auto_lead_enabled: @entangle('auto_lead_enabled').defer }">
                            <!-- Label -->
                            <x-label :value="t('acquire_new_lead_automatically')" class="mb-2" />

                            <!-- Toggle (UI/UX identical, logic still updates auto_lead_enabled) -->
                            <x-toggle id="auto-lead-toggle" name="auto_lead_enabled" :value="$auto_lead_enabled"
                                wire:model="auto_lead_enabled" />
                        </div>

                        <div x-data="{ 'auto_lead_enabled': @entangle('auto_lead_enabled') }"
                            class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-2">
                            <div>
                                <div wire:ignore>
                                    <div class="flex items-center">
                                        <span x-show="auto_lead_enabled" class="text-danger-500 mr-1">*</span>
                                        <x-label for="lead_status" :value="t('lead_status')" />
                                    </div>
                                    <x-select wire:model.defer="lead_status" id="lead_status"
                                        class="mt-1 block w-full tom-select">
                                        <option>{{ t('select_status')}}</option>
                                        @foreach ($statuses as $index => $status)
                                        <option value="{{ $status->id }}" {{ $status->id == $lead_status ? 'selected' :
                                            '' }}> {{ $status->name }}
                                        </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="lead_status" class="mt-2" />
                            </div>
                            <div>
                                <div wire:ignore>
                                    <div class="flex items-center">
                                        <span x-show="auto_lead_enabled" class="text-danger-500 mr-1">*</span>
                                        <x-label for="lead_source" :value="t('lead_source')" />
                                    </div>
                                    <x-select wire:model.defer="lead_source" id="lead_source"
                                        class="mt-1 block w-full tom-select">
                                        <option>{{ t('select_source') }}</option>
                                        @foreach ($sources as $source)
                                        <option value="{{ $source['id'] }}" {{ $source['id']==$lead_source ? 'selected'
                                            : '' }}>
                                            {{ $source['name'] }}
                                        </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="lead_source" class="mt-2" />
                            </div>
                            <div>
                                <div wire:ignore class="mt-1">
                                    <div class="flex items-center">
                                        <x-label for="lead_assigned_to" :value="t('lead_assigned')" />
                                    </div>
                                    <x-select wire:model.defer="lead_assigned_to" id="lead_assigned_to"
                                        class="mt-1 block w-full tom-select">
                                        <option value="">{{ t('select_assign') }}</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user['id'] }}" {{ $user['id']==$lead_assigned_to ? 'selected'
                                            : '' }}>
                                            {{ $user['firstname'] . ' ' . $user['lastname'] }}
                                        </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="lead_assigned_to" class="mt-2" />
                            </div>

                        </div>
                    </x-slot:content>
                    @if (checkPermission('tenant.whatsmark_settings.edit'))
                    <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
                        <div class="flex justify-end">
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