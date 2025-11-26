<?php

namespace App\Livewire\Tenant\Waba;

use App\Models\Tenant\TenantSetting;
use App\Traits\WhatsApp;
use Livewire\Component;

class ConnectWaba extends Component
{
    use WhatsApp;

    public $wm_fb_app_id;

    public $wm_fb_app_secret;

    public $wm_business_account_id;

    public $wm_access_token;

    public $is_webhook_connected;

    public $is_whatsmark_connected;

    public $webhook_verify_token;

    public $admin_webhook_connected;

    public $admin_fb_app_id;

    public $admin_fb_app_secret;

    public $step = 1; // Track current step

    public $account_connected = false; // Track if account is connected

    protected $messages = [
        'wm_fb_app_id.required' => 'The Facebook App ID is required.',
        'wm_fb_app_secret.required' => 'The Facebook App Secret is required.',
        'wm_business_account_id.required' => 'The Whatsapp Business Account ID is required.',
        'wm_access_token.required' => 'The Whatsapp Access Token is required.',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.connect_account.connect')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $whatsapp_settings = tenant_settings_by_group('whatsapp');
        $this->wm_fb_app_id = $whatsapp_settings['wm_fb_app_id'] ?? '';
        $this->wm_fb_app_secret = $whatsapp_settings['wm_fb_app_secret'] ?? '';
        $this->wm_business_account_id = $whatsapp_settings['wm_business_account_id'] ?? '';
        $this->wm_access_token = $whatsapp_settings['wm_access_token'] ?? '';
        $this->is_whatsmark_connected = $whatsapp_settings['is_whatsmark_connected'] ?? '';
        $this->is_webhook_connected = $whatsapp_settings['is_webhook_connected'] ?? '';

        $admin_settings = get_settings_by_group('whatsapp');
        $this->admin_webhook_connected = $admin_settings->is_webhook_connected ?? '';
        $this->admin_fb_app_id = $admin_settings->wm_fb_app_id ?? '';
        $this->admin_fb_app_secret = $admin_settings->wm_fb_app_secret ?? '';

        // Check if account is already connected
        $this->account_connected = $this->is_whatsmark_connected == 1;
        $webhook_configuration_url = '';
        if ($this->account_connected) {
            $phone_numbers = $this->getPhoneNumbers();
            if ($phone_numbers['status']) {
                $webhook_configuration_url = array_column(array_column($phone_numbers['data'], 'webhook_configuration'), 'application');
            } else {
                $this->step = 1;

                return;
            }
        }

        // Determine current step
        if ($this->account_connected && ! $this->admin_webhook_connected && ! $this->is_webhook_connected) {
            $this->step = 2; // Move to webhook setup
        } elseif ($this->account_connected && ! in_array(route('whatsapp.webhook'), $webhook_configuration_url)) {
            $this->step = 2;
        } elseif ($this->is_whatsmark_connected == 1 && ($this->admin_webhook_connected == 1 || $this->is_webhook_connected == 1)) {
            return redirect()->to(tenant_route('tenant.waba'));
        }
    }

    public function connectAccount()
    {
        $this->validate([
            'wm_business_account_id' => 'required',
            'wm_access_token' => 'required',
        ]);

        try {
            $is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
                ->where('value', 'like', "%$this->wm_business_account_id%")
                ->where('tenant_id', '!=', tenant_id())
                ->exists();
            $is_found_wm_access_token = TenantSetting::where('key', 'wm_access_token')
                ->where('value', 'like', "%$this->wm_access_token%")
                ->where('tenant_id', '!=', tenant_id())
                ->exists();

            if (! $is_found_wm_business_account_id && ! $is_found_wm_access_token) {
                // Save the account details
                save_tenant_setting('whatsapp', 'wm_business_account_id', $this->wm_business_account_id);
                save_tenant_setting('whatsapp', 'wm_access_token', $this->wm_access_token);

                // Set account as connected for UI purposes (will be verified later)
                $this->account_connected = true;

                // Set initial webhook connection status
                if ($this->admin_webhook_connected) {
                    save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
                    $this->is_webhook_connected = 1;

                    // If admin webhook is connected, verify connection and complete setup
                    $response = $this->loadTemplatesFromWhatsApp();
                    save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
                    save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->admin_fb_app_id);
                    save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->admin_fb_app_secret);

                    if ($response['status']) {
                        $this->is_whatsmark_connected = 1;
                        $this->notify([
                            'message' => t('account_connect_successfully'),
                            'type' => 'success',
                        ], true);

                        return redirect()->to(tenant_route('tenant.waba'));
                    } else {
                        $this->notify([
                            'message' => $response['message'],
                            'type' => 'danger',
                        ]);

                        return;
                    }
                } else {
                    // Move to webhook setup without verifying connection yet
                    save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
                    save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
                    $this->is_webhook_connected = 0;
                    $this->is_whatsmark_connected = 0;
                    $this->step = 2; // Move to webhook setup

                    $this->notify([
                        'message' => t('account_details_saved').' '.t('now_setup_webhook'),
                        'type' => 'success',
                    ]);
                }
            } else {
                $this->notify([
                    'message' => t('you_cant_use_this_details_already_used_by_other'),
                    'type' => 'danger',
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('WhatsApp Account Connection Failed', 'error', [], $e);
            $this->notify(['message' => $e->getMessage(), 'type' => 'danger']);
        }
    }

    public function connectMetaWebhook()
    {
        $this->validate([
            'wm_fb_app_id' => 'required',
            'wm_fb_app_secret' => 'required',
        ]);

        try {
            // Save webhook settings
            save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->wm_fb_app_id);
            save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->wm_fb_app_secret);

            // Connect webhook using the trait method
            $response = $this->connectWebhook($this->wm_fb_app_id, $this->wm_fb_app_secret);

            if ($response['status']) {
                save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
                $this->is_webhook_connected = 1;

                // Now verify the WhatsApp connection since webhook is setup
                $whatsapp_response = $this->loadTemplatesFromWhatsApp();
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', $whatsapp_response['status'] ? 1 : 0);

                if ($whatsapp_response['status']) {
                    $this->is_whatsmark_connected = 1;
                    $this->notify([
                        'message' => t('webhook_connected_successfully').' '.t('account_connect_successfully'),
                        'type' => 'success',
                    ], true);

                    return redirect()->to(tenant_route('tenant.waba'));
                } else {
                    $this->notify([
                        'message' => t('webhook_connected_but_whatsapp_verification_failed').': '.$whatsapp_response['message'],
                        'type' => 'warning',
                    ]);
                }
            } else {
                $this->notify([
                    'message' => $response['message'] ?? t('webhook_connection_failed'),
                    'type' => 'danger',
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('Webhook Connection Failed', 'error', [
                'wm_fb_app_id' => $this->wm_fb_app_id,
                'error' => $e->getMessage(),
            ], $e);

            $this->notify([
                'message' => t('webhook_connection_failed'),
                'type' => 'danger',
            ]);
        }
    }

    /**
     * Go back to step 1 to update account details
     */
    public function goBackToStep1()
    {
        // Reset to step 1
        $this->step = 1;

        // Reset connection status to allow re-configuration
        $this->account_connected = false;
        save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
        save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
        $this->is_whatsmark_connected = 0;
        $this->is_webhook_connected = 0;
    }

    public function render()
    {
        return view('livewire.tenant.waba.connect-waba', [
            'wm_default_phone_number' => $this->account_connected ?
                (tenant_settings_by_group('whatsapp')['wm_default_phone_number'] ?? null) : null,
        ]);
    }
}
