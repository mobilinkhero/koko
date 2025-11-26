<div class="mx-auto">
    <x-slot:title>
        {{ t('ai_integration') }}
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
            <form wire:submit="save" x-data="{ 'enable_openai_in_chat': @entangle('enable_openai_in_chat') }"
                class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('ai_integration') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('integrate_ai_tools') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Activate OpenAI in the chat -->
                            <div x-data="{ enable_openai_in_chat: @entangle('enable_openai_in_chat').defer }">
                                <x-label for="message" :value="t('activate_openai_in_chat')" />

                                <div class="flex justify-start items-center">
                                    <x-toggle id="openai-chat-toggle" name="enable_openai_in_chat"
                                        :value="$enable_openai_in_chat" wire:model="enable_openai_in_chat" />
                                </div>
                            </div>


                            <!-- Chat Model -->
                            <div class="mt-1" x-data="{ 'enable_openai_in_chat': @entangle('enable_openai_in_chat') }">
                                <div wire:ignore>
                                    <div class="flex items-center">
                                        <span x-show="enable_openai_in_chat" x-cloak
                                            class="text-danger-500 mr-1">*</span>
                                        <x-label for="chat_model" :value="t('chat_model')" class="mb-1" />
                                    </div>
                                    <x-select class="tom-select" wire:model.defer="chat_model" id="chat_model">
                                        <option> {{ t('select_model') }} </option>
                                        @foreach ($chatGptModels as $model)
                                        <option value="{{ $model['id'] }}">{{ $model['name'] }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="chat_model" class="mt-1" />
                            </div>
                        </div>

                        <!-- OpenAI Secret Key -->
                        <div x-data="{ 'enable_openai_in_chat': @entangle('enable_openai_in_chat') }"
                            class="mt-4 sm:mt-0">
                            <div class="flex items-center sm:mt-2">
                                <span x-show="enable_openai_in_chat" x-cloak class="text-danger-500 mr-1">*</span>
                                <x-label for="openai_secret_key" :value="t('openai_secret_key')" />
                                <a href="https://platform.openai.com/api-keys" target="blank">
                                    <em class="text-sm text-primary-700 dark:text-primary-600 ml-1">
                                        {{ t('where_to_find_secret_key') }}
                                    </em>
                                </a>
                            </div>
                            <x-input wire:model.defer="openai_secret_key" id="openai_secret_key" type="text" />
                            <x-input-error for="openai_secret_key" class="mt-1" />
                        </div>
                    </x-slot:content>

                    <!-- Submit Button -->
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