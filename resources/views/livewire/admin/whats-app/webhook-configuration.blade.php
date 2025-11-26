<x-slot:title>
    {{ t('webhook_configuration') }}
</x-slot:title>

<div class="min-h-screen">
   
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-6">
          <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('whatsapp_webhook')],
    ]" />
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ t('meta_whatsapp_api_webhook') }}</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                {{ t('configure_your_webhook_connection_for_the_whatsapp_business_api') }}</p>
        </div>

        <x-card>
            <x-slot:header class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-t-lg">
                <div class="flex flex-col items-start">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 mr-3 text-white" fill="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>

                        <h1 class="text-xl font-semibold text-white dark:text-slate-300">
                            {{ t('whatsapp_webhook_configuration') }}
                        </h1>
                    </div>
                    <p class="text-white dark:text-gray-300 text-sm mt-1">
                        {{ t('configure_your_webhook_connection') }}
                    </p>
                </div>
            </x-slot:header>
            <x-slot:content>

                <!-- Main Content -->
                <div class="space-y-6">
                    <!-- Info Alert -->
                    <x-card class="border-l-4 border-info-500">
                        <x-slot:content>
                            <div>
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-o-information-circle class="h-5 w-5 text-info-500" />
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-info-800 dark:text-info-200 font-semibold">
                                            {{ t('meta_whatsapp_business_api_setup') }}</h3>
                                        <div class="mt-2 text-sm text-info-700 dark:text-info-300">
                                            <p>{{ t('configure_the_webhook_endpoint') }}</p>
                                            <ul class="list-disc list-inside mt-2 space-y-1">
                                                <li>{{ t('facebook_app_id_and_secret') }}
                                                </li>
                                                <li>{{ t('admin_privileges_facebook') }}</li>
                                            </ul>
                                            <p class="mt-2">
                                                {{ t('api_endpoint_receive_and_process') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>

                    <!-- Facebook App Configuration -->
                    <x-card>
                        <x-slot:header>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-primary-500 dark:text-primary-400 " />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                    {{ t('meta_application_details') }}
                                </h3>
                            </div>
                        </x-slot:header>
                        <x-slot:content>

                            <div class="space-y-3">
                                <!-- Facebook App ID -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="wm_fb_app_id"
                                            class="text-gray-700 dark:text-gray-300 font-medium">
                                            {{ t('facebook_app_id') }}
                                        </x-label>
                                    </div>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <x-heroicon-o-identification class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <x-input id="wm_fb_app_id" type="text" class="pl-10" wire:model="wm_fb_app_id"
                                            placeholder="{{ t('enter_your_facebook_app_id') }}" />
                                    </div>
                                    <x-input-error for="wm_fb_app_id" class="mt-2" />
                                </div>

                                <!-- Facebook App Secret -->
                                <div x-data="{ showPassword: false }">
                                    <div class="flex items-center gap-1 mb-2">
                                        <span class="text-danger-500">*</span>
                                        <x-label for="wm_fb_app_secret"
                                            class="text-gray-700 dark:text-gray-300 font-medium">
                                            {{ t('facebook_app_secret') }}
                                        </x-label>
                                    </div>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <x-heroicon-o-key class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <x-input id="wm_fb_app_secret" class="pl-10 pr-10"
                                            x-bind:type="showPassword ? 'text' : 'password'"
                                            wire:model="wm_fb_app_secret"
                                            placeholder="{{ t('enter_your_facebook_app_secret') }}" />
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                            <x-heroicon-m-eye class="h-5 w-5" x-show="!showPassword" />
                                            <x-heroicon-m-eye-slash class="h-5 w-5" x-show="showPassword" />
                                        </button>
                                    </div>
                                    <x-input-error for="wm_fb_app_secret" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center gap-1 mb-2">
                                        <x-label for="wm_fb_config_id" class="text-gray-700 dark:text-gray-300 font-medium">
                                            {{ t('facebook_config_id') }}
                                        </x-label>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <x-input id="wm_fb_config_id" type="text" class="pl-10"
                                            wire:model="wm_fb_config_id"
                                            placeholder="{{ t('enter_your_facebook_config_id') }}" />
                                    </div>
                                    <x-input-error for="wm_fb_config_id" class="mt-2" />
                                </div>
                            </div>
                        </x-slot:content>
                        <x-slot:footer>
                            <div class="flex justify-end">
                                <x-button.primary wire:click="saveConfiguration"
                                    class="flex items-center justify-center space-x-2 w-[200px]">
                                    <!-- When NOT loading -->
                                    <span wire:loading.remove wire:target="saveConfiguration"
                                        class="flex items-center space-x-2">
                                        <x-heroicon-o-check class="h-4 w-4" />
                                        <span>{{ t('save_configuration') }}</span>
                                    </span>

                                    <!-- When loading -->
                                    <span wire:loading wire:target="saveConfiguration"
                                        class="flex items-center space-x-2">
                                        <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />
                                        <!-- Invisible text keeps width -->
                                    </span>
                                </x-button.primary>
                            </div>

                        </x-slot:footer>
                    </x-card>

                    <!-- Webhook Configuration -->
                    <x-card>
                        <x-slot:header>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-link class="h-5 w-5 text-primary-500 dark:text-primary-400 " />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                    {{ t('webhook_connection') }}
                                </h3>
                            </div>
                        </x-slot:header>
                        <x-slot:content>
                            <div class="space-y-3">
                                <!-- Webhook URL -->
                                <div>
                                    <x-label for="webhook_url"
                                        class="text-gray-700 dark:text-gray-300 font-medium mb-2 block">
                                        {{ t('webhook_url') }}
                                    </x-label>

                                    <div class="flex justify-center items-center gap-2" x-data="{
                                        copied: false,
                                        copyText() {
                                            const text = $refs.webhookUrl?.value;
                                            if (!text) {
                                                showNotification('No text found to copy', 'danger');
                                                return;
                                            }
                                            copyToClipboard(text);
                                            this.copied = true;
                                            setTimeout(() => this.copied = false, 2000);
                                        }
                                    }">
                                        <!-- Input Field -->
                                        <div class="relative flex-1">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <x-heroicon-o-globe-alt class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <x-input id="webhook_url" type="text" x-ref="webhookUrl" readonly
                                                value="{{ route('whatsapp.webhook') }}" class="pl-10 pr-3" />
                                        </div>

                                        <!-- Copy Button -->
                                        @php
                                        $copyText = t('copy');
                                        $copiedText = t('copied');
                                        @endphp
                                        <div class="flex justify-end mt-1">
                                            <x-button.secondary x-on:click="copyText()">
                                                <span x-text="copied ? '{{ $copiedText }}' : '{{ $copyText }}'"></span>
                                            </x-button.secondary>
                                        </div>

                                    </div>

                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('use_url_in_meta_for_developers') }}
                                    </p>
                                </div>
                            </div>
                        </x-slot:content>
                        <x-slot:footer>
                            <!-- Connection Status -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between ">
                                <div class="flex items-center gap-3 mb-3 sm:mb-0">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('connection_status') }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $is_webhook_connected ? 'bg-success-100 dark:bg-success-900/30 text-success-800 dark:text-success-200' : 'bg-danger-100 dark:bg-danger-900/30 text-danger-800 dark:text-danger-200' }}">
                                        @if ($is_webhook_connected)
                                        <x-heroicon-o-check-circle class="h-4 w-4 mr-2" />
                                        {{ t('connected') }}
                                        @else
                                        <x-heroicon-o-x-circle class="h-4 w-4 mr-2" />
                                        {{ t('disconnected') }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex flex-col lg:flex-row gap-3">
                                    @if (!$is_webhook_connected)
                                    <x-button.primary wire:click="connectHook"
                                        class="flex items-center justify-center space-x-2 w-[200px]">
                                        <!-- Normal state -->
                                        <span wire:loading.remove wire:target="connectHook"
                                            class="flex items-center space-x-2">
                                            <x-heroicon-o-check-circle class="h-3 w-3" />
                                            <span>{{ t('connect_webhook') }}</span>
                                        </span>

                                        <!-- Loading state -->
                                        <span wire:loading wire:target="connectHook"
                                            class="flex items-center space-x-2">
                                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />

                                        </span>
                                    </x-button.primary>
                                    @else
                                    <x-button.secondary wire:click="disconnectHook"
                                        class="flex items-center justify-center space-x-2 w-[200px]">
                                        <!-- Normal state -->
                                        <span wire:loading.remove wire:target="disconnectHook"
                                            class="flex items-center space-x-2">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                            <span>{{ t('disconnect_webhook') }}</span>
                                        </span>

                                        <!-- Loading state -->
                                        <span wire:loading wire:target="disconnectHook"
                                            class="flex items-center space-x-2">
                                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />

                                        </span>
                                    </x-button.secondary>
                                    @endif

                                    <!-- Verify Connection Button -->
                                    <x-button.secondary wire:click="verifyWebhook"
                                        class="flex items-center justify-center space-x-2 w-[200px]">
                                        <!-- Normal state -->
                                        <span wire:loading.remove wire:target="verifyWebhook"
                                            class="flex items-center space-x-2">
                                            <x-heroicon-o-check-badge class="h-4 w-4" />
                                            <span>{{ t('verify') }}</span>
                                        </span>

                                        <!-- Loading state -->
                                        <span wire:loading wire:target="verifyWebhook"
                                            class="flex items-center space-x-2">
                                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />

                                        </span>
                                    </x-button.secondary>
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-card>

                    <!-- Setup Instructions -->
                    <x-card>
                        <x-slot:content>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <x-heroicon-o-information-circle class="h-5 w-5 mr-2 text-primary-500" />
                                    {{ t('meta_developer_portal_setup_instructions') }}
                                </h4>
                                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-6 h-6 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center text-xs font-medium">1</span>
                                        <div>
                                            {{ t('Create a Facebook App in the') }}
                                            <a href="https://developers.facebook.com/" target="_blank"
                                                class="text-primary-600 hover:text-primary-500 hover:underline font-medium">
                                                {{ t('meta_for_developers') }}
                                            </a>
                                            {{ t('portal') }}
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-6 h-6 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center text-xs font-medium">2</span>
                                        <div>{{ t('navigate_to_whatsapp_setup') }}
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-6 h-6 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center text-xs font-medium">3</span>
                                        <div>{{ t('click_on_configure_webhooks') }}
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-6 h-6 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center text-xs font-medium">4</span>
                                        <div>{{ t('enter_the_webhook_url_provided') }}</div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-6 h-6 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center text-xs font-medium">5</span>
                                        <div>
                                            {{ t('subscribe_to_these_fields') }}
                                            <div
                                                class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-md text-xs">
                                                {{ t('message_template_status_update') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>

                    <!-- Additional Documentation -->
                    <div class="flex justify-center sm:items-center px-4 flex-col ">
                        <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks" target="_blank"
                            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors break-words text-center">
                            <x-heroicon-o-book-open class="h-5 w-5 shrink-0" />
                            <span class="truncate">
                                {{ t('view_whatsapp_webhook_documentation') }}
                            </span>
                            <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4 shrink-0" />
                        </a>
                    </div>

                </div>
            </x-slot:content>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
    // Webhook Configuration Helper
        const WebhookConfig = {
            copyToClipboard(text) {
                // Modern clipboard API
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.showNotification("{{ t('copied_to_clipboard') }}", 'success');
                    }).catch(() => {
                        this.fallbackCopy(text);
                    });
                } else {
                    this.fallbackCopy(text);
                }
            },

            fallbackCopy(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    document.execCommand('copy');
                    this.showNotification("{{ t('copied_to_clipboard') }}", 'success');
                } catch (err) {
                    this.showNotification("{{ t('copy_failed') }}", 'error');
                } finally {
                    document.body.removeChild(textArea);
                }
            },

            showNotification(message, type = 'success') {
                // Check for existing notification system
                if (typeof window.showNotification === 'function') {
                    window.showNotification(message, type);
                    return;
                }

                // Use Livewire notification if available
                if (window.Livewire) {
                    window.Livewire.emit('notify', {
                        message,
                        type
                    });
                    return;
                }

                // Custom notification fallback
                this.createToast(message, type);
            },

            createToast(message, type) {
                // Remove existing toasts
                const existingToasts = document.querySelectorAll('[data-toast]');
                existingToasts.forEach(toast => toast.remove());

                const toast = document.createElement('div');
                toast.setAttribute('data-toast', 'true');
                toast.className = `
                fixed top-4 right-4 z-50 flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full
                ${type === 'success' ? 'bg-success-500' : 'bg-danger-500'} text-white max-w-sm
            `;

                toast.innerHTML = `
                <div class="flex items-center">
                    <div class="mr-3">
                        ${type === 'success' ?
                            '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
                            '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                        }
                    </div>
                    <span class="flex-1 text-sm font-medium">${message}</span>
                    <button class="ml-3 text-white hover:text-gray-200 transition-colors" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

                document.body.appendChild(toast);

                // Animate in
                setTimeout(() => {
                    toast.classList.replace('translate-x-full', 'translate-x-0');
                }, 10);

                // Auto remove after 4 seconds
                setTimeout(() => {
                    toast.classList.replace('translate-x-0', 'translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }
        };
</script>
@endpush