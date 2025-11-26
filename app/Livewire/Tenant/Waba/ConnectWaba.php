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

    public $admin_fb_config_id;

    public $step = 1; // Track current step

    public $account_connected = false; // Track if account is connected

    public $use_embedded_signup = false; // Track if user wants to use embedded signup
    
    public $api_verification_warning = false; // Show warning if API verification fails

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
        $this->admin_fb_config_id = $admin_settings->wm_fb_config_id ?? '';

        // Check if account is already connected
        $this->account_connected = $this->is_whatsmark_connected == 1;
        $webhook_configuration_url = '';
        if ($this->account_connected) {
            $phone_numbers = $this->getPhoneNumbers();
            if ($phone_numbers['status']) {
                $webhook_configuration_url = array_column(array_column($phone_numbers['data'], 'webhook_configuration'), 'application');
            } else {
                // API call failed - but don't disconnect! This could be a temporary issue.
                // Resetting to step 1 causes duplicate webhook subscriptions.
                whatsapp_log('Failed to verify WhatsApp connection via API (temporary)', 'warning', [
                    'tenant_id' => tenant_id(),
                    'is_whatsmark_connected' => $this->is_whatsmark_connected,
                    'error' => $phone_numbers['message'] ?? 'Unknown error',
                ]);
                
                // Show warning to user but maintain connection
                $this->api_verification_warning = true;
                
                // Keep the connection status and webhook configuration
                // User can manually verify or reconnect if there's a real issue
                $webhook_configuration_url = [];
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
     * Handle embedded signup callback from Facebook
     * Processes the embedded signup data similar to manual connection
     * This matches the EXACT flow and data storage as manual connection
     */
    public function handleEmbeddedSignup($requestCode, $wabaId, $phoneNumberId)
    {
        try {
            whatsapp_log('Embedded Signup Started', 'info', [
                'has_code' => !empty($requestCode),
                'waba_id' => $wabaId,
                'phone_number_id' => $phoneNumberId,
            ]);

            // Validate the received data
            if (empty($requestCode) || empty($wabaId)) {
                $this->notify([
                    'message' => t('embedded_signup_failed') . ': Missing required data',
                    'type' => 'danger',
                ]);
                return;
            }

            if (empty($this->admin_fb_app_id) || empty($this->admin_fb_app_secret)) {
                $this->notify([
                    'message' => t('embedded_signup_not_configured'),
                    'type' => 'danger',
                ]);
                return;
            }

            // Exchange authorization code for access token
            // This is the same process as the Chatvvoold system
            $tokenResponse = \Illuminate\Support\Facades\Http::post('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => $this->admin_fb_app_id,
                'client_secret' => $this->admin_fb_app_secret,
                'code' => $requestCode,
            ]);

            if ($tokenResponse->failed()) {
                $error = $tokenResponse->json('error');
                whatsapp_log('Token Exchange Failed', 'error', $error);
                $this->notify([
                    'message' => ($error['message'] ?? t('connection_failed')),
                    'type' => 'danger',
                ]);
                return;
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (empty($accessToken)) {
                $this->notify([
                    'message' => t('connection_failed') . ': Access token not received',
                    'type' => 'danger',
                ]);
                return;
            }

            whatsapp_log('Access Token Received', 'info');

            // Use WABA ID from embedded signup (same as Chatvvoold)
            $businessAccountId = $wabaId;

            // Check for duplicates (SAME AS MANUAL CONNECTION)
            $is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
                ->where('value', 'like', "%$businessAccountId%")
                ->where('tenant_id', '!=', tenant_id())
                ->exists();
            $is_found_wm_access_token = TenantSetting::where('key', 'wm_access_token')
                ->where('value', 'like', "%$accessToken%")
                ->where('tenant_id', '!=', tenant_id())
                ->exists();

            if ($is_found_wm_business_account_id || $is_found_wm_access_token) {
                $this->notify([
                    'message' => t('you_cant_use_this_details_already_used_by_other'),
                    'type' => 'danger',
                ]);
                return;
            }

            // PHASE 1: Save Core Account Credentials
            // This matches EXACTLY the manual connection flow from your documentation
            save_tenant_setting('whatsapp', 'wm_business_account_id', $businessAccountId);
            save_tenant_setting('whatsapp', 'wm_access_token', $accessToken);

            whatsapp_log('Account Credentials Saved', 'info', [
                'waba_id' => substr($businessAccountId, 0, 10) . '...',
            ]);

            // Set account as connected for UI
            $this->account_connected = true;
            $this->wm_business_account_id = $businessAccountId;
            $this->wm_access_token = $accessToken;

            // PHASE 2/4: Webhook Setup (Same logic as manual connection)
            if ($this->admin_webhook_connected) {
                // AUTOMATIC SETUP (Admin webhook connected)
                save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
                $this->is_webhook_connected = 1;

                // Verify connection and sync templates (PHASE 3)
                $response = $this->loadTemplatesFromWhatsApp();
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
                save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->admin_fb_app_id);
                save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->admin_fb_app_secret);

                if ($response['status']) {
                    $this->is_whatsmark_connected = 1;
                    
                    whatsapp_log('Embedded Signup Completed Successfully', 'info');
                    
                    $this->notify([
                        'message' => t('whatsapp_connected_successfully'),
                        'type' => 'success',
                    ], true);

                    return redirect()->to(tenant_route('tenant.waba'));
                } else {
                    $this->notify([
                        'message' => $response['message'],
                        'type' => 'danger',
                    ]);
                }
            } else {
                // MANUAL SETUP REQUIRED (Admin webhook NOT connected)
                // Move to step 2 - same as manual connection
                save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
                $this->is_webhook_connected = 0;
                $this->is_whatsmark_connected = 0;
                $this->step = 2;

                whatsapp_log('Embedded Signup - Webhook Setup Required', 'info');

                $this->notify([
                    'message' => t('account_details_saved').' '.t('now_setup_webhook'),
                    'type' => 'success',
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('Embedded Signup Connection Failed', 'error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], $e);
            $this->notify([
                'message' => t('connection_failed').': '.$e->getMessage(),
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
        // Check if embedded signup is configured
        $embedded_signup_configured = !empty($this->admin_fb_app_id) && !empty($this->admin_fb_config_id);

        return view('livewire.tenant.waba.connect-waba', [
            'wm_default_phone_number' => $this->account_connected ?
                (tenant_settings_by_group('whatsapp')['wm_default_phone_number'] ?? null) : null,
            'embedded_signup_configured' => $embedded_signup_configured,
            'admin_fb_app_id' => $this->admin_fb_app_id,
            'admin_fb_config_id' => $this->admin_fb_config_id,
        ]);
    }
}
