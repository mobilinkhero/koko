<div class="mx-auto">

    <x-slot:title>
        {{ t('create_message_bot') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('message_bots'), 'route' => tenant_route('tenant.messagebot.list')],
        ['label' => $message_bot->exists ? t('edit_bot') : t('add_message_bot') ]
    ]" />


    <!-- Feature Limit Warning  -->
    @if (!$message_bot->exists && isset($this->hasReachedLimit) && $this->hasReachedLimit)
    <div class="pb-3">
        <div class="rounded-md bg-warning-50 dark:bg-warning-900/30 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-warning-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                        {{ t('message_bot_limit_reached') }}</h3>
                    <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                        <p>{{ t('message_bot_limit_reached_message') }} <a
                                href="{{ tenant_route('tenant.subscription') }}" class="font-medium underline">{{
                                t('upgrade_plan') }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="flex-1 space-y-5">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start" x-data="{
                mergeFields: @entangle('mergeFields'),
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
                    }, 500);
                },
            }">
                {{-- Left Side --}}
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300 ">
                            {{ t('message_bot') }}
                        </h1>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="col-span-3">
                            <div class="flex items-center justify-start gap-1">
                                <span class="text-danger-500">*</span>
                                <x-label class="mt-[2px]" for="name" :value="t('bot_name')" class="mt-[2px]" />
                            </div>
                            <x-input wire:model.defer="name" id="name" type="text" class="block w-full"
                                autocomplete="off" />
                            <x-input-error for="name" class="mt-2" />
                        </div>

                        <span x-data="{ rel_type: @entangle('rel_type') }">
                            <div class="mt-4">
                                <div class="flex item-centar justify-start ">
                                    <span class="text-danger-500 me-1 ">*</span>
                                    <x-label class="mt-[2px]" for="rel_type" :value="t('relation_type')" />
                                </div>
                                <div wire:ignore>
                                    <x-select x-model="rel_type" id="relation_type_select"
                                        x-on:change="handleTributeEvent()" class="tom-select mt-1 block w-full"
                                        wire:model="rel_type" wire:change="$set('rel_type', $event.target.value)">
                                        @foreach (\App\Enum\Tenant\WhatsAppTemplateRelationType::getRelationType() as  $key => $relationType)
                                        <option value="{{ $key }}">{{ ucfirst($relationType) }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="rel_type" class="mt-2" />
                            </div>

                            <div x-data="{ text: @entangle('reply_text') }" class="col-span-3 mt-3">
                                <div class="flex justify-between">
                                    <div class="flex items-center justify-start gap-1">
                                        <x-heroicon-o-question-mark-circle
                                            class="me-1 dark:text-slate-200 hidden h-5 lg:h-5 sm:block"
                                            data-tippy-content="{{ t('data_tippy_content') }}" />
                                        <span class="text-danger-500">*</span>
                                        <x-label class="mt-[2px]" for="reply_text" :value="t('reply_text')" />
                                    </div>
                                    <!-- Live character count -->
                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                        <span x-text="text?.length || 0"></span>/1024
                                    </span>
                                </div>
                                <x-textarea x-model="text" x-init='handleTributeEvent()' wire:model.defer="reply_text"
                                    type="text" rows="7" id="reply_text" class="mentionable mt-1" />
                                <x-input-error for="reply_text" class="mt-2" />
                            </div>


                            <div class="mt-4" x-data="{ replyType: @entangle('reply_type') }">
                                <div class="flex item-centar justify-start ">
                                    <span class="text-danger-500 me-1 ">*</span>
                                    <x-label for="reply_type" :value="t('reply_type')" />
                                </div>
                                <div>
                                    <x-select x-model="replyType" id="reply_type" wire:model="reply_type"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm subtext-select">
                                        @foreach (\App\Enum\Tenant\WhatsAppTemplateRelationType::getReplyType() as $key
                                        => $replyType)
                                        <option value="{{ $key }}" data-subtext="{{ $replyType['subtext'] }}" {{
                                            $reply_type==$key ? 'selected' : '' }}>
                                            {{ $replyType['label'] }}
                                        </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <x-input-error for="reply_type" class="mt-2" />

                                <template x-if="replyType==1 || replyType==2">
                                    <div class="col-span-3  mt-3">
                                        <div class="flex items-center justify-start gap-1">
                                            <span class="text-danger-500">*</span>
                                            <x-label class="mt-[2px]" for="trigger" :value="t('trigger_keyword')" />
                                        </div>
                                        <div x-data="{
                                            tags: @entangle('trigger'),
                                            newTag: '',
                                            errorMessage: '',

                                            purifyInput(input) {
                                                let tempDiv = document.createElement('div');
                                                tempDiv.textContent = input; // Remove potential HTML tags
                                                return tempDiv.innerHTML.trim();
                                            },

                                            addTag() {
                                                let tag = this.purifyInput(this.newTag);

                                                // Prevent empty input
                                                if (!tag) return;

                                                // Patterns for SQL & JSON injection
                                                let upper = tag.toUpperCase();
                                                let sqlKeywords = ['SELECT', 'INSERT', 'DELETE', 'DROP', 'UNION', 'WHERE', 'HAVING'];
                                                let injectionPattern = /(\{.*\}|\[.*\])|[\<\>\&\'\\\;]/;

                                                // Check for SQL injection, JSON injection, or unsafe characters
                                                if (sqlKeywords.some(k => upper.includes(k)) || injectionPattern.test(tag)) {
                                                    this.errorMessage = '{{ t('sql_injection_error') }}';
                                                    return;
                                                }

                                                // Prevent duplicate entries
                                                if (this.tags.includes(tag)) {
                                                    this.errorMessage = '{{ t('this_trigger_already_exists') }}';
                                                    return;
                                                }

                                                // Add the valid tag
                                                this.tags.push(tag);
                                                this.errorMessage = '';
                                                this.newTag = '';
                                            }
                                        }">
                                            <x-input type="text" x-model="newTag" x-on:keydown.enter.prevent="addTag()"
                                                x-on:compositionend="addTag()" x-on:blur="addTag()"
                                                placeholder="{{ t('type_and_press_enter') }}" autocomplete="off"
                                                class="block w-full mt-1 border p-2" />

                                            <div class="mt-2">
                                                <template x-for="(tag, index) in tags" :key="index">
                                                    <span
                                                        class="bg-primary-500 dark:bg-primary-800 text-white mb-2 dark:text-gray-100 rounded-xl px-2 py-1 text-sm mr-2 inline-flex items-center">
                                                        <span x-text="tag"></span>
                                                        <button x-on:click="tags.splice(index, 1)"
                                                            class="ml-2 text-white dark:text-gray-100">&times;</button>
                                                    </span>
                                                </template>
                                            </div>
                                            <!-- Error Message -->
                                            <p x-show="errorMessage" class="text-danger-500 text-sm mt-1"
                                                x-text="errorMessage"></p>
                                        </div>
                                        <x-input-error for="trigger" class="mt-2" />
                                    </div>
                                </template>
                                <template x-if="replyType==4">
                                    <x-dynamic-alert type="warning" class="w-full mt-3">
                                        <p class="text-sm">
                                            <span class="font-medium"> {{ t('note') }}</span>
                                            {{ t('increase_webhook_note') }} <a
                                                href="https://docs.corbitaltech.dev/products/whatsmark-saas/"
                                                target="blank" class="underline">{{ t('link') }}</a>
                                        </p>
                                    </x-dynamic-alert>
                                </template>
                            </div>
                            <div x-data="{ headerText: @entangle('bot_header') }" class="col-span-3 mt-3">
                                <div class="flex justify-between">
                                    <div class="flex">
                                        <x-heroicon-o-question-mark-circle class="me-1 dark:text-slate-200"
                                            height="20px" data-tippy-content="{{ t('max_allowed_character_60') }}" />
                                        <x-label class="mt-[2px]" for="bot_header" :value="t('header')" />
                                    </div>
                                    <!-- Live character counter -->
                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                        <span x-text="headerText?.length || 0"></span>/60
                                    </span>
                                </div>
                                <!-- Input field with live binding -->
                                <x-input x-model="headerText" wire:model.defer="bot_header" id="bot_header" type="text"
                                    class="block w-full mt-1" autocomplete="off" />
                                <x-input-error for="bot_header" class="mt-2" />
                            </div>
                            <div x-data="{ footerText: @entangle('bot_footer') }" class="col-span-3 mt-3">
                                <div class="flex justify-between">
                                    <div class="flex">
                                        <x-heroicon-o-question-mark-circle class="me-1 dark:text-slate-200"
                                            height="20px" data-tippy-content="{{ t('max_allowed_character_60') }}" />
                                        <x-label class="mt-[2px]" for="bot_footer" :value="t('footer')" />
                                    </div>
                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                        <span x-text="footerText?.length || 0"></span>/60
                                    </span>
                                </div>
                                <x-input x-model="footerText" wire:model.defer="bot_footer" id="bot_footer" type="text"
                                    class="block w-full mt-1" autocomplete="off" />
                                <x-input-error for="bot_footer" class="mt-2" />
                            </div>
                    </x-slot:content>
                </x-card>

                {{-- Right Side --}}
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <h1 class="text-xl font-semibold text-slate-700 dark:text-slate-300 ">
                            {{ t('message_bot_options') }}
                        </h1>
                    </x-slot:header>
                    <x-slot:content>
                        <div x-data="{ activeTab: 'option1' }" class="space-y-6" x-cloak>
                            <!-- Tab Navigation -->
                            <div class="border-b dark:border-slate-600 flex items-center relative">
                                <div id="leftscroll" class="dark:text-white sm:hidden">
                                    <x-heroicon-s-chevron-left class="w-5 h-5"
                                        x-on:click="$refs.tabList.scrollBy({ left: -150, behavior: 'smooth' })" />
                                </div>
                                <nav class="flex pr-12 gap-4 overflow-hidden whitespace-nowrap flex-nowrap"
                                    x-ref="tabList"
                                    x-on:keydown.right.prevent="$refs.tabList.scrollBy({ left: 150, behavior: 'smooth' })"
                                    x-on:keydown.left.prevent="$refs.tabList.scrollBy({ left: -150, behavior: 'smooth' })">
                                    <!-- Navigation buttons -->
                                    <button type="button" x-on:click="activeTab = 'option1'" :class="{
                                            'border-b-2 border-primary-500 text-primary-500  dark:text-primary-500': activeTab === 'option1',
                                            'border-b-2 border-danger-500 text-danger-600': activeTab !== 'option1' &&
                                                {{ $errors->hasAny(['button1', 'button2', 'button3', 'button1_id', 'button2_id', 'button3_id'])
                                                    ? 'true'
                                                    : 'false' }}
                                        }"
                                        class="px-4 py-2 text-sm font-medium dark:text-slate-300 hover:text-primary-500">
                                        {{ t('reply_button') }}
                                    </button>
                                    <button type="button" x-on:click="activeTab = 'option2'" :class="{
                                            'border-b-2 border-primary-500 text-primary-500 dark:text-primary-500': activeTab === 'option2',
                                            'border-b-2 border-danger-500 text-danger-600': activeTab !== 'option2' &&
                                                {{ $errors->hasAny(['button_name']) ? 'true' : 'false' }}
                                        }"
                                        class="px-4 py-2 text-sm font-medium dark:text-slate-300 hover:text-primary-500">
                                        {{ t('cta_url') }}
                                    </button>
                                    <button type="button" x-on:click="activeTab = 'option3'" :class="{
                                            'border-b-2 border-primary-500 text-primary-500 dark:text-primary-500': activeTab === 'option3',
                                            'border-b-2 border-danger-500 text-danger-600': activeTab !== 'option3' &&
                                                {{ $errors->hasAny(['file_upload']) ? 'true' : 'false' }}
                                        }"
                                        class="px-4 py-2 text-sm font-medium dark:text-slate-300 hover:text-primary-500">
                                        {{ t('file_upload') }}
                                    </button>
                                    {{ do_action('messagebot.after_fileupload_button',$errors)}}
                                </nav>
                                <div id="rightscroll" class="dark:text-white sm:hidden ml-auto">
                                    <x-heroicon-s-chevron-right class="w-5 h-5"
                                        x-on:click="$refs.tabList.scrollBy({ left: 150, behavior: 'smooth' })" />
                                </div>
                            </div>

                            <!-- Tab Contents -->
                            <div>
                                <!-- Option 1 - Reply Buttons -->
                                <div x-show="activeTab === 'option1'" class="space-y-4 ">
                                    <!-- Original Option 1 Content Here -->
                                    <div class="text-slate-700 dark:text-slate-200">
                                        <h1>{{ t('reply_button_option1') }} </h1>
                                        <div x-data="{ button1Text: @entangle('button1'), button1IdText: @entangle('button1_id') }"
                                            class="grid gap-4 mt-3 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-2">
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="flex">
                                                        <x-heroicon-o-question-mark-circle
                                                            class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                            data-tippy-content="{{ t('max_allowed_char_20') }}" />
                                                        <x-label class="mt-[2px]" for="button1" :value="t('button1')"
                                                            class="sm:mt-px sm:pt-2" />
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                                        <span x-text="button1Text?.length || 0"></span>/20
                                                    </span>
                                                </div>
                                                <x-input x-model="button1Text" wire:model.defer="button1" id="button1"
                                                    type="text" class="mt-2 sm:mt-1" autocomplete="off" />

                                                <x-input-error for="button1" class="mt-2" />
                                            </div>

                                            <!-- Button 1 ID Input with Counter -->
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="flex">
                                                        <x-heroicon-o-question-mark-circle
                                                            class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                            data-tippy-content="{{ t('max_allow_char_256') }}" />
                                                        <x-label class="mt-[2px]" for="button1_id"
                                                            :value="t('button1_id')" class="sm:mt-px sm:pt-2" />
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                                        <span x-text="button1IdText?.length || 0"></span>/256
                                                    </span>
                                                </div>
                                                <x-input x-model="button1IdText" wire:model.defer="button1_id"
                                                    id="button1_id" type="text" class="mt-2 sm:mt-1"
                                                    autocomplete="off" />
                                                <x-input-error for="button1_id" class="mt-2" />
                                            </div>
                                        </div>
                                        <div x-data="{ button2Text: @entangle('button2'), button2IdText: @entangle('button2_id') }"
                                            class="grid gap-4 mt-3 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-2">
                                            <!-- Button 2 Input with Counter -->
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="flex">
                                                        <x-heroicon-o-question-mark-circle
                                                            class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                            data-tippy-content="{{ t('max_allowed_char_20') }}" />
                                                        <x-label class="mt-[2px]" for="button2" :value="t('button2')"
                                                            class="sm:mt-px sm:pt-2" />
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                                        <span x-text="button2Text?.length || 0"></span>/20
                                                    </span>
                                                </div>
                                                <x-input x-model="button2Text" wire:model.defer="button2" id="button2"
                                                    type="text" class="mt-2 sm:mt-1" autocomplete="off" />
                                                <x-input-error for="button2" class="mt-2" />
                                            </div>

                                            <!-- Button 2 ID Input with Counter -->
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="flex">
                                                        <x-heroicon-o-question-mark-circle
                                                            class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                            data-tippy-content="{{ t('max_allow_char_256') }}" />
                                                        <x-label class="mt-[2px]" for="button2_id"
                                                            :value="t('button2_id')" class="sm:mt-px sm:pt-2" />
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                                        <span x-text="button2IdText?.length || 0"></span>/256
                                                    </span>
                                                </div>
                                                <x-input x-model="button2IdText" wire:model.defer="button2_id"
                                                    id="button2_id" type="text" class="mt-2 sm:mt-1"
                                                    autocomplete="off" />
                                                <x-input-error for="button2_id" class="mt-2" />
                                            </div>
                                        </div>

                                        <div x-data="{ button3Text: @entangle('button3'), button3IdText: @entangle('button3_id') }"
                                            class="grid gap-4 mt-3 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-2">
                                            <!-- Button 3 Input with Counter -->
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="flex">
                                                        <x-heroicon-o-question-mark-circle
                                                            class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                            data-tippy-content="{{ t('max_allowed_char_20') }}" />
                                                        <x-label class="mt-[2px]" for="button3" :value="t('button3')"
                                                            class="sm:mt-px sm:pt-2" />
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                                        <span x-text="button3Text?.length || 0"></span>/20
                                                    </span>
                                                </div>
                                                <x-input x-model="button3Text" wire:model.defer="button3" id="button3"
                                                    type="text" class="mt-2 sm:mt-1" />
                                                <x-input-error for="button3" class="mt-2" />
                                            </div>

                                            <!-- Button 3 ID Input with Counter -->
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="flex">
                                                        <x-heroicon-o-question-mark-circle
                                                            class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                            data-tippy-content="{{ t('max_allow_char_256') }}" />
                                                        <x-label class="mt-[2px]" for="button3_id"
                                                            :value="t('button3_id')" class="sm:mt-px sm:pt-2" />
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-200">
                                                        <span x-text="button3IdText?.length || 0"></span>/256
                                                    </span>
                                                </div>
                                                <x-input x-model="button3IdText" wire:model.defer="button3_id"
                                                    id="button3_id" type="text" class="mt-2 sm:mt-1"
                                                    autocomplete="off" />
                                                <x-input-error for="button3_id" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Option 2 - Single Button -->
                                <div x-show="activeTab === 'option2'">
                                    <div class=" text-slate-700 dark:text-slate-200">
                                        </h6> {{ t('option2_button_name') }}</h6>
                                    </div>
                                    <div class="border-slate-200  dark:border-slate-600 ">
                                        <div x-data="{ buttonNameText: @entangle('button_name') }"
                                            class="col-span-3 mt-3">
                                            <div class="flex justify-between">
                                                <div class="flex">
                                                    <x-heroicon-o-question-mark-circle
                                                        class="me-1 sm:mt-2 dark:text-slate-200" height="20px"
                                                        data-tippy-content="{{ t('max_allowed_char_20') }}" />
                                                    <x-label class="mt-[2px]" for="button_name"
                                                        :value="t('button_name')" class="sm:mt-px sm:pt-2" />
                                                </div>
                                                <span class="text-sm text-slate-700 dark:text-slate-200">
                                                    <span x-text="buttonNameText?.length || 0"></span>/20
                                                </span>
                                            </div>
                                            <x-input x-model="buttonNameText" wire:model.defer="button_name"
                                                id="button_name" type="text" class="block w-full mt-1"
                                                autocomplete="off" />
                                            <x-input-error for="button_name" class="mt-2" />
                                        </div>
                                        <div class="col-span-3 mt-3">
                                            <x-label class="mt-[2px]" for="button_url" :value="t('button_link')" />
                                            <x-input wire:model.defer="button_url" id="button_url" type="text"
                                                class="block w-full mt-1" placeholder="https://" autocomplete="off" />
                                            <x-input-error for="button_url" class="mt-2" />
                                        </div>
                                    </div>
                                </div>


                                <!-- Integrated File Upload Component -->
                                <div x-show="activeTab === 'option3'" x-data="{
                                    fileName: '',
                                    fileError: '',
                                    fileType: @entangle('file_type'),
                                    isUploaded: false,
                                    metaExtensions: {{ json_encode(get_meta_allowed_extension()) }},
                                    isUploading: @entangle('isUploading'),
                                    progress: 0,
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

                                    init() {
                                        // Check if we already have a filename (for edit mode)
                                        if (@js($filename)) {
                                            this.fileName = @js($filename).split('/').pop();
                                            this.isUploaded = true;

                                            // Set the file type based on the file extension
                                            const fileExtension = '.' + this.fileName.split('.').pop().toLowerCase();

                                            // Match extension to file type
                                            if (this.metaExtensions.document.extension.includes(fileExtension)) {
                                                this.fileType = 'document';
                                            } else if (this.metaExtensions.image.extension.includes(fileExtension)) {
                                                this.fileType = 'image';
                                            } else if (this.metaExtensions.video.extension.includes(fileExtension)) {
                                                this.fileType = 'video';
                                            }
                                        }
                                    },

                                    handleFileUpload(event) {
                                        const file = event.target.files[0];
                                        if (!file) return;

                                        // Reset error
                                        this.fileError = '';

                                        // Get metadata based on file type
                                        const metaData = this.metaExtensions[this.fileType];
                                        if (!metaData) {
                                            this.fileError = 'Invalid file type.';
                                            return;
                                        }

                                        // Extract allowed extensions and max file size
                                        const allowedExtensions = metaData.extension.split(',').map(ext => ext.trim());
                                        const maxSizeMB = metaData.size || 0; // Default to 0MB if not set
                                        const maxSizeBytes = maxSizeMB * 1024 * 1024; // Convert MB to bytes

                                        // Extract file extension
                                        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                                        // Validate file extension
                                        if (!allowedExtensions.includes(fileExtension)) {
                                            this.fileError = `Invalid file type. Allowed: ${allowedExtensions.join(', ')}`;
                                            return;
                                        }

                                        // Validate file size
                                        if (file.size > maxSizeBytes) {
                                            this.fileError = `File size exceeds ${maxSizeMB} MB. Please upload a smaller file.`;
                                            return;
                                        }

                                        // If validation passes, let the file upload happen
                                        // The wire:model='file' will handle the actual upload
                                        this.fileName = file.name;
                                        this.isUploaded = true;
                                    },

                                    removeFile() {
                                        // Reset file input
                                        this.$refs.fileInput.value = '';

                                        // Clear the file from Livewire
                                        this.$wire.call('removeFile');

                                        // Update UI state
                                        this.fileName = '';
                                        this.isUploaded = false;
                                    },



                                 }" class="w-full" x-on:livewire-upload-start="uploadStarted()"
                                    x-on:livewire-upload-finish="uploadFinished()"
                                    x-on:livewire-upload-error="isUploading = false"
                                    x-on:livewire-upload-progress="updateProgress($event.detail.progress)">
                                    <!-- File Type Selection -->
                                    <div class="mb-4">
                                        <x-label for="file-type" class="block text-sm font-medium text-gray-700 mb-1"
                                            :value="t('file_type')" />
                                        <select id="file-type" x-model="fileType"
                                            class="block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                            :class="{ 'bg-gray-50': isUploaded }" :disabled="isUploaded">
                                            <option value="image">Image</option>
                                            <option value="document">Document</option>
                                            <option value="video">Video</option>
                                        </select>
                                    </div>

                                    <div class="mt-2">

                                        <label for="file-upload" class="block w-full cursor-pointer">
                                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md transition-all dark:bg-transparent "
                                                :class="fileError ? 'border-danger-300 bg-danger-50' : (isUploaded ?
                                                    'border-primary-300 bg-primary-50' : 'hover:border-primary-400')">

                                                <div class="space-y-2 text-center">
                                                    <!-- Icon -->
                                                    <div x-show="!isUploaded" class="flex justify-center">
                                                        <template x-if="fileType === 'document'">
                                                            <x-heroicon-o-document
                                                                class="mx-auto h-10 w-10 text-gray-400" />
                                                        </template>
                                                        <template x-if="fileType === 'image'">
                                                            <x-heroicon-o-photo
                                                                class="mx-auto h-10 w-10 text-gray-400" />
                                                        </template>
                                                        <template x-if="fileType === 'video'">
                                                            <x-heroicon-o-film
                                                                class="mx-auto h-10 w-10 text-gray-400" />
                                                        </template>
                                                    </div>

                                                    <!-- Uploaded file info -->
                                                    <div x-show="isUploaded"
                                                        class="flex flex-wrap md:flex-nowrap items-center justify-center gap-2 md:space-x-2 text-center md:text-left">

                                                        <!-- File type icon -->
                                                        <template x-if="fileType === 'document'">
                                                            <x-heroicon-o-document class="h-6 w-6 text-primary-500" />
                                                        </template>
                                                        <template x-if="fileType === 'image'">
                                                            <x-heroicon-o-photo class="h-8 w-8 text-primary-500" />
                                                        </template>
                                                        <template x-if="fileType === 'video'">
                                                            <x-heroicon-o-film class="h-8 w-8 text-primary-500" />
                                                        </template>

                                                        <!-- File name -->
                                                        <span
                                                            class="text-sm break-all text-gray-800 dark:text-slate-200 font-medium w-full md:w-auto"
                                                            x-text="fileName"></span>

                                                        <!-- Delete button -->
                                                        <button type="button" x-on:click="removeFile"
                                                            class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-500 dark:text-slate-400 hover:text-danger-500 dark:hover:text-danger-400">
                                                            <x-heroicon-o-trash class="w-5 h-5" />
                                                        </button>
                                                    </div>

                                                    <!-- Instructions -->
                                                    <div x-show="!isUploaded"
                                                        class="text-sm text-gray-600 text-center md:text-left">
                                                        <p class="font-medium text-primary-600 underline">
                                                            {{ t('select_or_browse_to') }} <span
                                                                x-text="fileType"></span>
                                                        </p>
                                                    </div>

                                                    <!-- Progress Bar -->
                                                    <div x-show="isUploading"
                                                        class="relative mt-2 w-full md:w-48 mx-auto">
                                                        <div
                                                            class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                                            <div class="bg-info-600 h-2.5 rounded-full transition-all duration-300"
                                                                :style="'width: ' + progress + '%'">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- File size info -->
                                                    <p x-show="!isUploaded"
                                                        class="text-xs text-gray-500 text-center md:text-left">
                                                        <span
                                                            x-text="metaExtensions[fileType]?.extension.replace(/\./g, ' ')"></span>
                                                        up to <span x-text="metaExtensions[fileType]?.size"></span>MB
                                                    </p>

                                                    <!-- Error message -->
                                                    <p x-show="fileError"
                                                        class="text-xs text-danger-600 font-semibold text-center md:text-left"
                                                        x-text="fileError"></p>
                                                </div>
                                            </div>

                                            <!-- Actual Input (hidden) -->
                                            <input id="file-upload" name="file-upload" type="file" wire:model="file"
                                                class="sr-only" x-on:change="handleFileUpload($event)"
                                                :accept="metaExtensions[fileType]?.extension" x-ref="fileInput">
                                        </label>
                                    </div>
                                </div>
                                <!-- Option 4 - Personal Assistant -->
                                <div x-show="activeTab === 'option4'">
                                    {{ do_action('messagebot.personal_assistant_tab',$selectedAssistantId)}}
                                </div>
                            </div>
                        </div>
                    </x-slot:content>
                    <x-slot:footer>
                        <div x-data="{ isUploading: @entangle('isUploading') }"
                            class="dark:bg-transparent rounded-b-lg flex justify-end">
                            <x-button.loading-button type="submit" target="save" x-bind:disabled="isUploading"
                                x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }">
                                {{ $message_bot->exists ? t('update_button') : t('add_button') }}
                            </x-button.loading-button>
                        </div>
                    </x-slot:footer>
                </x-card>
            </div>
        </form>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        let selectElement = document.querySelector(".subtext-select");
        if (selectElement) {
            window.initTomSelect(".subtext-select", {
                allowEmptyOption: true,
                render: {
                    option: function(data, escape) {
                        return `
                        <div>
                            <span class="font-medium text-sm">${escape(data.text)}</span>
                            <div class="text-gray-500 text-xs">${escape(data.subtext || "")}</div>
                        </div>
                    `;
                    },
                    item: function(data, escape) {
                        return `<div>${escape(data.text)}</div>`;
                    }
                }
            });
        }
    });
</script>
