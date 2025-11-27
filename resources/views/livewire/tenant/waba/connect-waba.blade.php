<div class="px-8 md:px-0">
    <x-slot:title>
        {{ t('connect_waba') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('connect_waba')],
    ]" />

    <div class="max-w-6xl md:flex md:items-center md:justify-between">
        <x-page-heading>
            {{ t('whatsapp_business_account') }}
        </x-page-heading>
    </div>

    {{-- Warning Banner when API verification fails but connection is maintained --}}
    @if($api_verification_warning)
    <div class="mb-4 p-4 bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-warning-400" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                    {{ t('connection_verification_warning') ?? 'Connection Verification Issue' }}
                </h3>
                <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                    <p>{{ t('api_verification_failed_message') ?? 'Your WhatsApp account is still connected, but we couldn\'t verify it with Facebook API right now. This is usually temporary. Your messages will continue to work normally.' }}</p>
                    <p class="mt-2 font-semibold">⚠️ {{ t('do_not_reconnect_warning') ?? 'Do NOT reconnect unless messages are actually not working, as reconnecting may cause duplicate messages.' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-8">

            {{-- Step - 1 : WhatsApp Integration Setup --}}
            @if ($step == 1)
            <div class="py-4">
                <x-card class="-mx-4 sm:-mx-0 rounded-md">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg leading-6 font-medium text-primary-600 dark:text-slate-200">
                                {{ t('wp_integration_step1') }}
                            </h3>
                            @if (!$admin_webhook_connected)
                            <span
                                class="px-3 py-1 text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 rounded-full">
                                Step 1 of 2
                            </span>
                            @endif
                        </div>
                    </x-slot:header>
                    <x-slot:content>
                        @if($embedded_signup_configured)
                        {{-- Embedded Signup Option --}}
                        <div class="mb-6 p-6 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-3">
                                    <x-heroicon-o-sparkles class="h-8 w-8 text-primary-600 dark:text-primary-400" />
                                </div>
                                <h4 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">
                                    {{ t('embedded_signup') ?? 'Embedded Signup' }}
                                </h4>
                                <p class="text-sm text-primary-700 dark:text-primary-300 mb-6 max-w-2xl mx-auto">
                                    {{ t('emb_signup_info') ?? 'Seamlessly authenticate users with their Facebook account using our embedded sign-in feature. No redirects, just smooth onboarding' }}
                                </p>
                                
                                {{-- Facebook Login Button with Loading State --}}
                                <div x-data="{ 
                                    embeddedLoading: false,
                                    init() {
                                        window.addEventListener('reset-embedded-loading', () => {
                                            this.embeddedLoading = false;
                                        });
                                    }
                                }" class="flex flex-col items-center">
                                    <button type="button" 
                                        @click="embeddedLoading = true; launchWhatsAppSignup()" 
                                        :disabled="embeddedLoading"
                                        id="fb-connect-btn"
                                        class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300"
                                        :class="embeddedLoading ? 'opacity-70 cursor-not-allowed' : 'hover:bg-blue-700'"
                                        style="background-color: #1877f2; min-width: 300px;">
                                        
                                        {{-- Loading Spinner --}}
                                        <svg x-show="embeddedLoading" x-cloak class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        
                                        {{-- Facebook Icon --}}
                                        <i x-show="!embeddedLoading" class="fab fa-facebook text-2xl mr-3"></i>
                                        
                                        {{-- Button Text --}}
                                        <span x-show="!embeddedLoading">{{ t('connect_with_facebook') ?? 'Connect with Facebook' }}</span>
                                        <span x-show="embeddedLoading" x-cloak>{{ t('connecting') ?? 'Connecting' }}...</span>
                                    </button>
                                    
                                    {{-- Loading Status Message --}}
                                    <div x-show="embeddedLoading" x-cloak class="mt-4 flex items-center space-x-2 text-sm text-primary-700 dark:text-primary-300">
                                        <svg class="animate-spin h-4 w-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Please wait while we connect to Facebook...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                    {{ t('or') }}
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- Manual Connection Form --}}
                        <div class="flex flex-col gap-2 items-center">
                            <div class="w-full">
                                <x-label for="wm_business_account_id" class="flex items-center space-x-1">
                                    <span data-tippy-content="{{ t('wp_business_id') }}">
                                        <x-heroicon-o-question-mark-circle
                                            class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                    </span>
                                    <span>{{ t('wp_business_id') }}</span>
                                </x-label>

                                <x-input id="wm_business_account_id" type="text" class="block w-full mt-1"
                                    wire:model="wm_business_account_id" />
                                <x-input-error for="wm_business_account_id" class="mt-2" />
                            </div>
                            <div class="w-full">
                                <x-label for="wm_access_token" class="flex items-center space-x-1">
                                    <span data-tippy-content="{{ t('user_access_token_info') }}">
                                        <x-heroicon-o-question-mark-circle
                                            class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                    </span>
                                    <span>{{ t('wp_access_token') }}</span>
                                </x-label>
                                <div class="flex items-center space-x-1"
                                    x-data="{ wm_access_token: @entangle('wm_access_token') }">
                                    <x-input id="wm_access_token" type="text" class="block w-full mt-1"
                                        wire:model="wm_access_token" x-model="wm_access_token" />
                                    <a :href="`https://developers.facebook.com/tools/debug/accesstoken/?access_token=${wm_access_token}`"
                                        target="_blank">
                                        <x-button.ghost class="mt-1">
                                            <x-heroicon-o-arrow-top-right-on-square class="h-5 w-5 mr-1" />
                                            {{ t('debug_token') }}
                                        </x-button.ghost>
                                    </a>
                                </div>
                                <x-input-error for="wm_access_token" class="mt-2" />
                            </div>
                        </div>
                    </x-slot:content>
                    <x-slot:footer>
                        <div class="flex justify-end">
                            <x-button.green wire:click="connectAccount">
                                <span wire:loading.remove wire:target="connectAccount">
                                    <x-heroicon-o-link class="h-5 w-5 mr-1 inline-block" />
                                    {{ t('config') }}
                                </span>
                                <div wire:loading wire:target="connectAccount" class="min-w-20">
                                    <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 ms-7" />
                                </div>
                            </x-button.green>
                        </div>
                    </x-slot:footer>
                </x-card>
            </div>
            @endif

            {{-- Step - 2 : Webhook Setup (only if admin webhook not connected) --}}
            @if ($step == 2)
            <div class="py-4">
                <x-card class="-mx-4 sm:-mx-0 rounded-md">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg leading-6 font-medium text-primary-600 dark:text-slate-200">
                                {{ t('wp_integration_step2') }}
                            </h3>
                            <span
                                class="px-3 py-1 text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 rounded-full">
                                {{ t('step_2_of_2') }}
                            </span>
                        </div>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="mb-4 p-4 bg-info-50 dark:bg-info-900/30 rounded-md">
                            <h4 class="flex items-center text-sm font-medium text-info-800 dark:text-info-300">
                                <x-heroicon-o-information-circle class="h-5 w-5 mr-2" />
                                {{ t('webhook_setup_required') }}
                            </h4>
                            <p class="mt-1 text-xs text-info-700 dark:text-info-300">
                                {{ t('webhook_setup_description') }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="w-full">
                                <x-label for="wm_fb_app_id" class="flex items-center space-x-1">
                                    <span data-tippy-content="{{ t('webhook_fb_app_id_info') }}">
                                        <x-heroicon-o-question-mark-circle
                                            class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                    </span>
                                    <span>{{ t('webhook_fb_app_id') }}</span>
                                </x-label>
                                <x-input id="wm_fb_app_id" type="text" class="block w-full mt-1"
                                    wire:model="wm_fb_app_id" placeholder="{{ t('enter_facebook_app_id') }}" />
                                <x-input-error for="wm_fb_app_id" class="mt-2" />
                            </div>

                            <div class="w-full">
                                <x-label for="wm_fb_app_secret" class="flex items-center space-x-1">
                                    <span data-tippy-content="{{ t('webhook_fb_app_secret_info') }}">
                                        <x-heroicon-o-question-mark-circle
                                            class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                    </span>
                                    <span>{{ t('webhook_fb_app_secret') }}</span>
                                </x-label>
                                <x-input id="wm_fb_app_secret" type="password" class="block w-full mt-1"
                                    wire:model="wm_fb_app_secret" placeholder="{{ t('enter_facebook_app_secret') }}" />
                                <x-input-error for="wm_fb_app_secret" class="mt-2" />
                            </div>

                            <div class="mt-4 p-3 bg-warning-50 dark:bg-warning-900/30 rounded-md">
                                <h4
                                    class="flex items-center text-sm font-medium text-warning-800 dark:text-warning-300">
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                                    {{ t('webhook_requirements') }}
                                </h4>
                                <ul class="mt-2 text-xs text-warning-700 dark:text-warning-300 space-y-1">
                                    <li>• {{ t('webhook_requirement_1') }}</li>
                                    <li>• {{ t('webhook_requirement_2') }}</li>
                                    <li>• {{ t('webhook_requirement_3') }}</li>
                                </ul>
                            </div>
                        </div>
                    </x-slot:content>
                    <x-slot:footer>
                        <div class="flex justify-between">
                            {{-- Back Button --}}
                            <x-button.secondary wire:click="goBackToStep1">
                                <span wire:loading.remove wire:target="goBackToStep1">
                                    <x-heroicon-o-arrow-left class="h-5 w-5 mr-1 inline-block" />
                                    {{ t('back') }}
                                </span>
                                <div wire:loading wire:target="goBackToStep1" class="min-w-20">
                                    <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 ms-7" />
                                </div>
                            </x-button.secondary>

                            {{-- Connect Webhook Button --}}
                            <x-button.green wire:click="connectMetaWebhook">
                                <span wire:loading.remove wire:target="connectMetaWebhook">
                                    <x-heroicon-o-link class="h-5 w-5 mr-1 inline-block" />
                                    {{ t('connect_webhook') }}
                                </span>
                                <div wire:loading wire:target="connectMetaWebhook" class="min-w-20">
                                    <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 ms-7" />
                                </div>
                            </x-button.green>
                        </div>
                    </x-slot:footer>
                </x-card>
            </div>
            @endif
        </div>

        <div class="md:col-span-4">
            <div class="py-4">
                <x-card class="-mx-4 sm:-mx-0 rounded-md">
                    <x-slot:header>
                        <h3 class="text-lg leading-6 font-medium text-primary-600 dark:text-slate-200">
                            @if ($step == 1)
                            {{ t('connection_requirements') }}
                            @else
                            {{ t('webhook_setup_guide') }}
                            @endif
                        </h3>
                    </x-slot:header>
                    <x-slot:content>
                        @if ($step == 1)
                        <div class="space-y-4">
                            <div class="border-l-4 border-primary-500 pl-4 py-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ t('connection_information') }}
                                </p>
                            </div>

                            <ul class="space-y-3">
                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 mr-3">
                                        1
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('valid_mobile_number') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('phone_number_register_meta') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 mr-3">
                                        2
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('facebook_developer_account') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('register_facebook_account') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 mr-3">
                                        3
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('whatsapp_business_profile') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('add_phone_number_to_verify') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 mr-3">
                                        4
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('system_user_access_token') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('create_system_user') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 mr-3">
                                        5
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('verify_your_setup') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('whatsapp_cloud_api_desc') }}</p>
                                    </div>
                                </li>
                            </ul>

                            <div class="mt-6 p-3 bg-info-50 dark:bg-info-900/30 rounded-md">
                                <h4 class="flex items-center text-sm font-medium text-info-800 dark:text-info-300">
                                    <x-heroicon-o-information-circle class="h-5 w-5 mr-2" />
                                    {{ t('need_help') }}
                                </h4>
                                <p class="mt-1 text-xs text-info-700 dark:text-info-300">
                                    {{ t('for_detailed_instructions') }} <a
                                        href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started"
                                        class="font-medium underline" target="_blank" rel="noopener noreferrer">{{
                                        t('cloud_api_documentation') }}</a>
                                </p>
                            </div>
                        </div>
                        @else
                        {{-- Webhook setup guide --}}
                        <div class="space-y-4">
                            <div class="border-l-4 border-success-500 pl-4 py-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ t('webhook_setup_information') }}
                                </p>
                            </div>

                            <ul class="space-y-3">
                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-success-100 dark:bg-success-900 text-success-600 dark:text-success-400 mr-3">
                                        1
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('create_facebook_app') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('create_app_in_facebook_developers') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-success-100 dark:bg-success-900 text-success-600 dark:text-success-400 mr-3">
                                        2
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('get_app_credentials') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('copy_app_id_and_secret') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-success-100 dark:bg-success-900 text-success-600 dark:text-success-400 mr-3">
                                        3
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('configure_webhook_url') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('webhook_url_will_be') }}: {{ route('whatsapp.webhook') }}</p>
                                    </div>
                                </li>

                                <li class="flex">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-success-100 dark:bg-success-900 text-success-600 dark:text-success-400 mr-3">
                                        4
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ t('webhook_verification') }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ t('webhook_will_be_verified_automatically') }}</p>
                                    </div>
                                </li>
                            </ul>

                            <div class="mt-6 p-3 bg-success-50 dark:bg-success-900/30 rounded-md">
                                <h4
                                    class="flex items-center text-sm font-medium text-success-800 dark:text-success-300">
                                    <x-heroicon-o-cog-6-tooth class="h-5 w-5 mr-2" />
                                    {{ t('webhook_configuration') }}
                                </h4>
                                <div class="mt-2 text-xs text-success-700 dark:text-success-300 space-y-1">
                                    <p><strong>{{ t('webhook_url') }}:</strong> {{ route('whatsapp.webhook') }}
                                    </p>
                                    <p><strong>{{ t('subscription_fields') }}:</strong>
                                        messages, message_template_status_update</p>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-info-50 dark:bg-info-900/30 rounded-md">
                                <h4 class="flex items-center text-sm font-medium text-info-800 dark:text-info-300">
                                    <x-heroicon-o-information-circle class="h-5 w-5 mr-2" />
                                    {{ t('webhook_help') }}
                                </h4>
                                <p class="mt-1 text-xs text-info-700 dark:text-info-300">
                                    {{ t('webhook_setup_help_text') }} <a
                                        href="https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks"
                                        class="font-medium underline" target="_blank" rel="noopener noreferrer">{{
                                        t('webhook_documentation') }}</a>
                                </p>
                            </div>
                        </div>
                        @endif

                        @if (isset($is_whatsmark_connected) && !$is_whatsmark_connected && $step == 1)
                        <div class="mt-4 p-3 bg-warning-50 dark:bg-warning-900/30 rounded-md">
                            <h4 class="flex items-center text-sm font-medium text-warning-800 dark:text-warning-300">
                                <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                                {{ t('connection_status') }}
                            </h4>
                            <p class="mt-1 text-xs text-warning-700 dark:text-warning-300">
                                {{ t('business_api_not_connected') }}
                            </p>
                        </div>
                        @endif

                        @if (isset($wm_default_phone_number) && $wm_default_phone_number)
                        <div
                            class="mt-6 p-4 flex flex-col items-center border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                                {{ t('scan_connect_whatsapp') }}</h3>

                            <div class="bg-white p-2 rounded-lg">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://wa.me/{{ preg_replace('/\D/', '', $wm_default_phone_number) }}"
                                    alt="WhatsApp QR Code" class="w-48 h-48" />
                            </div>

                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ t('scan_qr_code') }}
                            </p>
                        </div>
                        @endif
                    </x-slot:content>
                </x-card>
            </div>
        </div>
    </div>
</div>

@if($embedded_signup_configured)
@push('scripts')
{{-- Facebook SDK --}}
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

<script>
    /**
     * Facebook Embedded Signup Implementation
     * Based on Chatvvoold system - matches the exact flow
     * Reference: https://developers.facebook.com/docs/whatsapp/embedded-signup
     */
    (function() {
        'use strict';

        // Initialize Facebook SDK
        window.fbAsyncInit = function() {
            FB.init({
                appId: '{{ $admin_fb_app_id }}',
                autoLogAppEvents: true,
                xfbml: true,
                version: 'v18.0'
            });
            
            console.log('Facebook SDK Initialized');
        };

        // Launch WhatsApp Embedded Signup
        // WhatsApp Business App Onboarding is ALWAYS ENABLED by default
        window.launchWhatsAppSignup = function() {
            const enableBusinessOnboarding = true; // Always enabled for all users
            console.log('Launching WhatsApp Embedded Signup...', {
                businessAppOnboarding: enableBusinessOnboarding
            });
            
            var tempAccessCode = '',
                phoneNumberId = '',
                waBaId = '',
                isFinished = false,
                setupTimeout = null,
                authReceived = false;

            // Function to reset loading state
            const resetLoadingState = function() {
                const event = new CustomEvent('reset-embedded-loading');
                window.dispatchEvent(event);
            };

            // Function to send setup data to backend
            const sendSetupData = function() {
                if (tempAccessCode && waBaId && !isFinished) {
                    isFinished = true;
                    
                    // Clear timeout since we're sending data
                    if (setupTimeout) {
                        clearTimeout(setupTimeout);
                    }

                    console.log('Sending embedded signup data to backend...', {
                        waba_id: waBaId,
                        phone_number_id: phoneNumberId,
                        has_code: !!tempAccessCode
                    });

                    // Call Livewire method with proper parameters
                    @this.handleEmbeddedSignup(tempAccessCode, waBaId, phoneNumberId)
                        .then(function() {
                            console.log('Embedded signup processed successfully');
                            // Loading state will be reset by page redirect or Livewire
                        })
                        .catch(function(error) {
                            console.error('Embedded signup processing failed:', error);
                            resetLoadingState();
                        });
                } else {
                    console.warn('Missing required data for embedded signup:', {
                        tempAccessCode: !!tempAccessCode,
                        waBaId: !!waBaId,
                        isFinished: isFinished
                    });
                }
            };

            // Prepare setup extras for WhatsApp Business Platform onboarding
            // IMPORTANT: featureType must be at same level as setup, not inside it!
            // Reference: https://developers.facebook.com/docs/whatsapp/embedded-signup/custom-flows/onboarding-business-app-users/
            var setupExtras = {
                setup: {},  // Empty setup object
                featureType: 'whatsapp_business_app_onboarding',  // Enable Business App onboarding (at same level as setup)
                sessionInfoVersion: '3'
            };
            
            console.log('WhatsApp Business App Onboarding ENABLED - featureType configured correctly');

            // Launch Facebook Login with Embedded Signup
            FB.login(function(response) {
                if (response.authResponse) {
                    // Get authorization code
                    tempAccessCode = response.authResponse.code;
                    authReceived = true;
                    console.log('Auth code received from Facebook');

                    if (!tempAccessCode) {
                        console.error('Failed to get authorization code from Facebook');
                        resetLoadingState();
                        return;
                    }

                    // Start timeout AFTER auth code is received
                    // Give 2 minutes for user to complete the embedded signup flow
                    setupTimeout = setTimeout(function() {
                        if (!isFinished && authReceived) {
                            console.error('Setup timeout - WABA ID not received within 2 minutes');
                            resetLoadingState();
                            alert('{{ t("setup_timeout_message") ?? "Setup timeout: Please complete the WhatsApp setup flow and click Finish within 2 minutes." }}');
                        }
                    }, 120000); // 2 minutes timeout

                } else {
                    if (setupTimeout) {
                        clearTimeout(setupTimeout);
                    }
                    console.log('User cancelled login or did not fully authorize');
                    resetLoadingState();
                }
            }, {
                config_id: '{{ $admin_fb_config_id }}',
                response_type: 'code',
                override_default_response_type: true,
                extras: setupExtras
            });

            // Listen for session info from Facebook
            const sessionInfoListener = function(event) {
                // Accept messages from both www.facebook.com and web.facebook.com
                if (event.origin !== "https://www.facebook.com" && event.origin !== "https://web.facebook.com") {
                    return;
                }

                console.log('Message received from Facebook:', event.origin);

                try {
                    const data = JSON.parse(event.data);
                    console.log('Facebook message data:', data);

                    if (data.type === 'WA_EMBEDDED_SIGNUP') {
                        console.log('WhatsApp Embedded Signup event:', data.event);

                        // If user finishes the Embedded Signup flow
                        if (data.event === 'FINISH') {
                            console.log('FINISH event received!');
                            const { phone_number_id, waba_id } = data.data;
                            phoneNumberId = phone_number_id;
                            waBaId = waba_id;

                            console.log('Extracted WABA ID:', waBaId);
                            console.log('Extracted Phone Number ID:', phoneNumberId);

                            // Clear timeout since we received the data
                            if (setupTimeout) {
                                clearTimeout(setupTimeout);
                            }

                            // Check if we have auth code, if not wait for it
                            if (tempAccessCode) {
                                console.log('Auth code already available, sending immediately');
                                sendSetupData();
                            } else {
                                console.log('Waiting for auth code...');
                                // Wait up to 3 seconds for auth code
                                let waitCount = 0;
                                const waitInterval = setInterval(function() {
                                    waitCount++;
                                    if (tempAccessCode) {
                                        console.log('Auth code received, sending data now');
                                        clearInterval(waitInterval);
                                        sendSetupData();
                                    } else if (waitCount >= 30) { // 3 seconds
                                        console.error('Timeout waiting for auth code');
                                        clearInterval(waitInterval);
                                        resetLoadingState();
                                        alert('{{ t("setup_failed_no_auth_code") ?? "Setup failed: Could not retrieve authorization code from Facebook." }}');
                                    }
                                }, 100);
                            }
                        } 
                        // If user cancels the Embedded Signup flow
                        else if (data.event === 'CANCEL') {
                            console.log('Setup cancelled by user');
                            if (setupTimeout) {
                                clearTimeout(setupTimeout);
                            }
                            resetLoadingState();
                        } else {
                            console.log('Other event received:', data.event);
                        }
                    }
                } catch (e) {
                    // Non-JSON message received
                    console.log('Non-JSON message:', event.data);

                    // Try to extract code from URL-encoded string
                    if (typeof event.data === 'string' && event.data.includes('code=')) {
                        try {
                            const params = new URLSearchParams(event.data);
                            const code = params.get('code');
                            if (code && !tempAccessCode) {
                                tempAccessCode = code;
                                console.log('Auth code extracted from URL params');
                                // Try to send data if we have everything
                                if (waBaId && phoneNumberId) {
                                    sendSetupData();
                                }
                            }
                        } catch (parseError) {
                            console.log('Could not parse URL params');
                        }
                    }
                }
            };

            // Add event listener for Facebook messages
            window.addEventListener('message', sessionInfoListener);
        };
    })();
</script>
@endpush
@endif
