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
                        <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-sparkles class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-primary-900 dark:text-primary-100 mb-1">
                                        {{ t('embedded_signup') }}
                                    </h4>
                                    <p class="text-xs text-primary-700 dark:text-primary-300 mb-3">
                                        {{ t('emb_signup_info') }}
                                    </p>
                                    <div class="flex items-center justify-center">
                                        <button id="fb-login-button" 
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                            {{ t('connect_with_facebook') }}
                                        </button>
                                    </div>
                                    <div id="fb-embedded-signup" class="w-full"></div>
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
{{-- Facebook Embedded Signup SDK --}}
<div id="fb-root"></div>
<script>
    // ============================================
    // FACEBOOK EMBEDDED SIGNUP - DETAILED LOGGING
    // ============================================
    console.log('========================================');
    console.log('FACEBOOK EMBEDDED SIGNUP INITIALIZATION');
    console.log('========================================');
    console.log('Admin Facebook App ID:', '{{ $admin_fb_app_id }}');
    console.log('Admin Facebook Config ID:', '{{ $admin_fb_config_id }}');
    console.log('Admin Facebook App Secret:', '{{ $admin_fb_app_secret ? "***CONFIGURED***" : "NOT SET" }}');
    console.log('Redirect URI:', '{{ url(tenant_route("tenant.connect", [], false)) }}');
    console.log('Embedded Signup Configured:', {{ $embedded_signup_configured ? 'true' : 'false' }});
    console.log('========================================');
    
    // Facebook SDK
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
        console.log('Facebook SDK script tag created and added to DOM');
    }(document, 'script', 'facebook-jssdk'));

    // Store reference to embedded button for reuse (global scope)
    let embeddedSignupButton = null;
    
    // Function to trigger embedded signup using Facebook's API
    function triggerEmbeddedSignup() {
        if (typeof FB === 'undefined') {
            console.error('Facebook SDK not loaded');
            return false;
        }
        
        const container = document.getElementById('fb-embedded-signup');
        if (!container) {
            console.error('Container not found');
            return false;
        }
        
        // Make container visible temporarily so widget can render
        const wasHidden = container.style.display === 'none';
        if (wasHidden) {
            container.style.display = 'block';
            container.style.visibility = 'hidden';
            container.style.position = 'absolute';
            container.style.width = '1px';
            container.style.height = '1px';
        }
        
        // Try to find and click the button
        const selectors = [
            'button',
            '[role="button"]',
            '.fb-embedded-signup button',
            'iframe',
            'div[role="button"]',
            'a[role="button"]',
            'span[role="button"]'
        ];
        
        // First try cached button
        if (embeddedSignupButton) {
            try {
                embeddedSignupButton.click();
                console.log('Triggered embedded signup using cached button');
                return true;
            } catch (e) {
                console.log('Cached button click failed:', e);
                embeddedSignupButton = null;
            }
        }
        
        // Try to find button in container
        for (let selector of selectors) {
            const button = container.querySelector(selector);
            if (button) {
                embeddedSignupButton = button;
                try {
                    button.click();
                    console.log('Found and clicked button with selector:', selector);
                    return true;
                } catch (e) {
                    console.log('Click failed for selector:', selector, e);
                }
            }
        }
        
        // Try to trigger via iframe
        const iframe = container.querySelector('iframe');
        if (iframe) {
            try {
                // Try to access iframe content (may fail due to CORS)
                if (iframe.contentDocument) {
                    const iframeButton = iframe.contentDocument.querySelector('button, [role="button"]');
                    if (iframeButton) {
                        iframeButton.click();
                        return true;
                    }
                }
                
                // Try postMessage to trigger embedded signup
                try {
                    iframe.contentWindow.postMessage({
                        type: 'fb-embedded-signup-trigger',
                        action: 'click'
                    }, '*');
                    console.log('Sent postMessage to trigger signup');
                } catch (e) {
                    console.log('postMessage failed:', e);
                }
                
                // Try clicking the iframe itself
                iframe.click();
                console.log('Clicked iframe directly');
                return true;
            } catch (e) {
                console.log('Iframe click failed:', e);
            }
        }
        
        console.error('Could not find embedded signup button');
        return false;
    }

    window.fbAsyncInit = function() {
        console.log('========================================');
        console.log('FACEBOOK SDK INITIALIZATION');
        console.log('========================================');
        console.log('Initializing Facebook SDK with:');
        console.log('  - App ID:', '{{ $admin_fb_app_id }}');
        console.log('  - Version: v18.0');
        console.log('  - Cookie: true');
        console.log('  - XFBML: true');
        
        FB.init({
            appId: '{{ $admin_fb_app_id }}',
            cookie: true,
            xfbml: true,
            version: 'v18.0'
        });
        
        console.log('Facebook SDK initialized successfully');
        console.log('FB object:', typeof FB !== 'undefined' ? 'Available' : 'Not Available');
        console.log('FB.XFBML:', typeof FB !== 'undefined' && FB.XFBML ? 'Available' : 'Not Available');
        console.log('========================================');
        
        // Trigger widget rendering after FB is initialized
        setTimeout(function() {
            console.log('Starting widget rendering after 500ms delay...');
            renderEmbeddedSignup();
        }, 500);

        // Listen for embedded signup completion - This is the main event handler
        FB.Event.subscribe('embedded_signup', function(response) {
            console.log('========================================');
            console.log('EMBEDDED SIGNUP EVENT RECEIVED');
            console.log('========================================');
            console.log('Full response object:', response);
            console.log('Response type:', typeof response);
            console.log('Has code:', !!(response && response.code));
            console.log('Has authResponse:', !!(response && response.authResponse));
            console.log('Has accessToken:', !!(response && response.authResponse && response.authResponse.accessToken));
            
            if (response && response.code) {
                console.log('Authorization code received:', response.code);
                console.log('Calling Livewire handleEmbeddedSignup() with code...');
                // Send authorization code to Livewire - this is the correct flow
                @this.handleEmbeddedSignup(response.code);
            } else if (response && response.authResponse && response.authResponse.accessToken) {
                console.log('Access token received directly (fallback)');
                console.log('Access token (first 20 chars):', response.authResponse.accessToken.substring(0, 20) + '...');
                // Fallback: if we get access token directly (shouldn't happen with embedded signup)
                const accessToken = response.authResponse.accessToken;
                console.log('Fetching business accounts from Facebook API...');
                console.log('API Endpoint: https://graph.facebook.com/v18.0/me/businesses');
                // Try to get business account ID
                FB.api('/me/businesses', { access_token: accessToken }, function(businessResponse) {
                    console.log('Business accounts API response:', businessResponse);
                    if (businessResponse && businessResponse.data && businessResponse.data.length > 0) {
                        const businessAccountId = businessResponse.data[0].id;
                        console.log('Business Account ID found:', businessAccountId);
                        console.log('Calling Livewire handleEmbeddedSignupDirect()...');
                        @this.call('handleEmbeddedSignupDirect', accessToken, businessAccountId);
                    } else {
                        console.error('No business accounts found in response');
                    }
                });
            } else {
                console.error('Unexpected response format:', response);
            }
            console.log('========================================');
        });
    };

    // Handle Facebook Login button click - Trigger embedded signup widget
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'fb-login-button' || e.target.closest('#fb-login-button'))) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('========================================');
            console.log('FACEBOOK LOGIN BUTTON CLICKED');
            console.log('========================================');
            console.log('Button clicked at:', new Date().toISOString());
            console.log('Attempting to trigger embedded signup...');
            
            // Try to trigger embedded signup
            if (triggerEmbeddedSignup()) {
                console.log('Embedded signup triggered successfully on first attempt');
                return;
            }
            
            console.log('First attempt failed, retrying after 500ms...');
            // If button not found, try again after a short delay
            setTimeout(function() {
                if (triggerEmbeddedSignup()) {
                    console.log('Embedded signup triggered successfully on retry');
                    return;
                }
                
                        // Last attempt: Try using Facebook's UI method or direct API call
                console.log('========================================');
                console.log('FALLBACK: TRYING ALTERNATIVE METHODS');
                console.log('========================================');
                
                // Method 1: Try to use FB.ui if available
                if (typeof FB !== 'undefined' && FB.ui) {
                    console.log('FB.ui is available, attempting to use it...');
                    // This might not work for embedded signup, but worth trying
                } else {
                    console.log('FB.ui is NOT available');
                }
                
                // Method 2: Inspect what was actually rendered
                const container = document.getElementById('fb-embedded-signup');
                if (container) {
                    console.log('Container exists, inspecting contents...');
                    console.log('Container innerHTML length:', container.innerHTML.length);
                    console.log('Container innerHTML (first 2000 chars):', container.innerHTML.substring(0, 2000));
                    console.log('Container children count:', container.children.length);
                    console.log('Container computed style display:', window.getComputedStyle(container).display);
                    console.log('Container computed style visibility:', window.getComputedStyle(container).visibility);
                    
                    // Try to find ANY interactive element
                    const allElements = container.querySelectorAll('*');
                    console.log('Total elements in container:', allElements.length);
                    
                    console.log('First 20 elements:');
                    for (let i = 0; i < Math.min(allElements.length, 20); i++) {
                        const el = allElements[i];
                        console.log(`  Element ${i}:`, {
                            tagName: el.tagName,
                            className: el.className,
                            id: el.id,
                            role: el.getAttribute('role'),
                            onclick: el.onclick ? 'Has onclick' : 'No onclick',
                            innerHTML: el.innerHTML.substring(0, 100)
                        });
                    }
                    
                    // Method 3: Try clicking the container itself or first child
                    const firstChild = container.firstElementChild;
                    if (firstChild) {
                        console.log('Trying to click first child:', {
                            tagName: firstChild.tagName,
                            className: firstChild.className,
                            id: firstChild.id
                        });
                        try {
                            firstChild.click();
                            console.log('First child click executed');
                            return;
                        } catch (e) {
                            console.error('First child click failed:', e);
                        }
                    } else {
                        console.log('No first child found in container');
                    }
                    
                    // Method 4: Try dispatching click event on container
                    try {
                        const clickEvent = new MouseEvent('click', {
                            view: window,
                            bubbles: true,
                            cancelable: true
                        });
                        container.dispatchEvent(clickEvent);
                        console.log('Dispatched click event on container');
                        return;
                    } catch (e) {
                        console.error('Container click dispatch failed:', e);
                    }
                } else {
                    console.error('Container not found!');
                }
                
                // Final fallback: Show error
                console.error('========================================');
                console.error('EMBEDDED SIGNUP FAILED');
                console.error('========================================');
                console.error('All methods to trigger embedded signup have failed.');
                console.error('Please check:');
                console.error('  1. Facebook App ID is configured:', '{{ $admin_fb_app_id ? "YES" : "NO" }}');
                console.error('  2. Facebook Config ID is configured:', '{{ $admin_fb_config_id ? "YES" : "NO" }}');
                console.error('  3. Config ID value:', '{{ $admin_fb_config_id }}');
                console.error('  4. Admin panel URL: https://soft.chatvoo.com/admin/whatsapp-webhook');
                console.error('========================================');
                alert('Facebook embedded signup is not available. Please ensure the Facebook Config ID is correctly configured in the admin panel at /admin/whatsapp-webhook');
            }, 500);
        }
    });
    
    // Render embedded signup widget - This will create a button that opens in a popup
    function renderEmbeddedSignup() {
        console.log('========================================');
        console.log('RENDERING EMBEDDED SIGNUP WIDGET');
        console.log('========================================');
        console.log('FB SDK Status:', {
            'FB defined': typeof FB !== 'undefined',
            'FB.XFBML available': typeof FB !== 'undefined' && FB.XFBML ? true : false,
        });
        
        if (typeof FB === 'undefined' || !FB.XFBML) {
            console.log('FB SDK not ready, retrying in 500ms...');
            // Retry if FB not loaded yet
            setTimeout(renderEmbeddedSignup, 500);
            return;
        }
        
        const container = document.getElementById('fb-embedded-signup');
        if (!container) {
            console.error('ERROR: fb-embedded-signup container not found in DOM');
            return;
        }
        
        console.log('Container found, setting up visibility...');
        // Make container visible (but off-screen) so Facebook can render the widget
        // Facebook's widget needs to be visible to render properly
        container.style.display = 'block';
        container.style.visibility = 'visible';
        container.style.position = 'absolute';
        container.style.left = '-9999px';
        container.style.top = '0';
        container.style.width = '400px';
        container.style.height = '50px';
        container.style.opacity = '1';
        
        // Only render if container is empty
        if (container.innerHTML.trim() === '') {
            console.log('Container is empty, rendering widget...');
            console.log('Widget Configuration:');
            console.log('  - Config ID:', '{{ $admin_fb_config_id }}');
            console.log('  - Redirect URI:', '{{ url(tenant_route("tenant.connect", [], false)) }}');
            console.log('  - Width: 100%');
            console.log('  - Onboarding Type: popup');
            
            // Use Facebook's Embedded Signup widget - it will open in popup automatically
            // The widget uses the config_id from admin panel
            container.innerHTML = `
                <div class="fb-embedded-signup" 
                     data-config-id="{{ $admin_fb_config_id }}"
                     data-redirect-uri="{{ url(tenant_route('tenant.connect', [], false)) }}"
                     data-width="100%"
                     data-onboarding-type="popup">
                </div>
            `;
            
            console.log('Widget HTML inserted, parsing with FB.XFBML...');
            try {
                FB.XFBML.parse(container);
                console.log('✅ FB.XFBML.parse completed successfully');
            } catch (e) {
                console.error('❌ ERROR parsing FB XFBML:', e);
                console.error('Error details:', {
                    message: e.message,
                    stack: e.stack
                });
            }
        } else {
            // Widget already rendered, try to find the button
            findEmbeddedButton();
        }
        
        // Use MutationObserver to watch for widget rendering
        const observer = new MutationObserver(function(mutations) {
            console.log('Container changed, looking for button...');
            if (findEmbeddedButton()) {
                observer.disconnect();
            }
        });
        
        observer.observe(container, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true
        });
        
        // Also try to find button after delays (longer delays for Facebook to render)
        setTimeout(function() {
            console.log('Checking for button at 1s...');
            findEmbeddedButton();
        }, 1000);
        
        setTimeout(function() {
            console.log('Checking for button at 2s...');
            findEmbeddedButton();
        }, 2000);
        
        setTimeout(function() {
            console.log('Checking for button at 3s...');
            findEmbeddedButton();
        }, 3000);
        
        setTimeout(function() {
            console.log('Checking for button at 5s...');
            findEmbeddedButton();
            observer.disconnect();
        }, 5000);
    }
    
    // Function to find the embedded button
    function findEmbeddedButton() {
        const container = document.getElementById('fb-embedded-signup');
        if (!container) {
            return false;
        }
        
        // Log what's actually in the container for debugging
        console.log('=== DEBUGGING EMBEDDED SIGNUP WIDGET ===');
        console.log('Container HTML length:', container.innerHTML.length);
        console.log('Container HTML (first 1000 chars):', container.innerHTML.substring(0, 1000));
        console.log('Container children count:', container.children.length);
        
        // Log all direct children
        Array.from(container.children).forEach((child, index) => {
            console.log(`Child ${index}:`, child.tagName, child.className, child.id, child.innerHTML.substring(0, 200));
        });
        
        const selectors = [
            'button',
            '[role="button"]',
            '.fb-embedded-signup button',
            'iframe',
            'div[role="button"]',
            'a[role="button"]',
            'span[role="button"]',
            'div.fb-embedded-signup',
            '*[onclick]',
            '*[data-testid]'
        ];
        
        for (let selector of selectors) {
            const button = container.querySelector(selector);
            if (button) {
                embeddedSignupButton = button;
                // Hide Facebook's default button (we'll use our custom styled button)
                if (button.style) {
                    button.style.display = 'none';
                    button.style.visibility = 'hidden';
                    button.style.opacity = '0';
                    button.style.position = 'absolute';
                    button.style.width = '1px';
                    button.style.height = '1px';
                }
                console.log('Facebook embedded signup button found with selector:', selector, button);
                return true;
            }
        }
        
        // Check all children recursively
        function findButtonRecursive(element) {
            if (!element) return null;
            
            // Check if element is clickable
            if (element.onclick || element.getAttribute('onclick') || 
                element.getAttribute('role') === 'button' ||
                element.tagName === 'BUTTON' ||
                element.tagName === 'A') {
                return element;
            }
            
            // Check children
            for (let child of element.children) {
                const found = findButtonRecursive(child);
                if (found) return found;
            }
            
            return null;
        }
        
        const foundButton = findButtonRecursive(container);
        if (foundButton) {
            embeddedSignupButton = foundButton;
            console.log('Found button recursively:', foundButton);
            return true;
        }
        
        // Check iframe - try to communicate with it via postMessage
        const iframe = container.querySelector('iframe');
        if (iframe) {
            embeddedSignupButton = iframe;
            console.log('Facebook embedded signup iframe found:', iframe.src);
            
            // Try to send postMessage to trigger the signup
            try {
                iframe.contentWindow.postMessage({
                    type: 'fb-embedded-signup-trigger',
                    config_id: '{{ $admin_fb_config_id }}'
                }, 'https://www.facebook.com');
                console.log('Sent postMessage to iframe');
            } catch (e) {
                console.log('postMessage failed (expected for cross-origin):', e);
            }
            
            return true;
        }
        
        console.log('No button found in container');
        console.log('Full container structure:', container.outerHTML.substring(0, 2000));
        return false;
    }
    
    // Wait for DOM and FB SDK to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof FB !== 'undefined') {
                renderEmbeddedSignup();
            } else {
                setTimeout(renderEmbeddedSignup, 1500);
            }
        });
    } else {
        // DOM already loaded
        if (typeof FB !== 'undefined') {
            renderEmbeddedSignup();
        } else {
            setTimeout(renderEmbeddedSignup, 1500);
        }
    }
</script>
@endpush
@endif
