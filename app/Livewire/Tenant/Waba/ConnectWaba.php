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

        // Check if this is an OAuth callback from Facebook
        $code = request()->query('code');
        if ($code) {
            // Handle OAuth callback
            $this->handleEmbeddedSignup($code);
            return;
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
     * Handle embedded signup callback from Facebook
     * This method receives the authorization code from Facebook's embedded signup
     * and exchanges it for an access token, then gets the business account ID
     */
    public function handleEmbeddedSignup($authCode)
    {
        whatsapp_log('========================================', 'info', [], null, tenant_id());
        whatsapp_log('EMBEDDED SIGNUP - HANDLING AUTHORIZATION CODE', 'info', [], null, tenant_id());
        whatsapp_log('========================================', 'info', [], null, tenant_id());
        whatsapp_log('Authorization Code Received:', 'info', [
            'code_length' => strlen($authCode ?? ''),
            'code_preview' => substr($authCode ?? '', 0, 20) . '...',
        ], null, tenant_id());
        
        try {
            // Validate the received data
            if (empty($authCode)) {
                whatsapp_log('ERROR: Authorization code is empty', 'error', [], null, tenant_id());
                $this->notify([
                    'message' => t('embedded_signup_not_configured'),
                    'type' => 'danger',
                ]);
                return;
            }

            whatsapp_log('Configuration Check:', 'info', [
                'admin_fb_app_id' => $this->admin_fb_app_id ? 'SET (' . strlen($this->admin_fb_app_id) . ' chars)' : 'NOT SET',
                'admin_fb_app_secret' => $this->admin_fb_app_secret ? 'SET (' . strlen($this->admin_fb_app_secret) . ' chars)' : 'NOT SET',
                'admin_fb_config_id' => $this->admin_fb_config_id ? 'SET (' . $this->admin_fb_config_id . ')' : 'NOT SET',
            ], null, tenant_id());

            if (empty($this->admin_fb_app_id) || empty($this->admin_fb_app_secret)) {
                whatsapp_log('ERROR: Admin Facebook credentials not configured', 'error', [
                    'app_id_set' => !empty($this->admin_fb_app_id),
                    'app_secret_set' => !empty($this->admin_fb_app_secret),
                ], null, tenant_id());
                $this->notify([
                    'message' => t('embedded_signup_not_configured'),
                    'type' => 'danger',
                ]);
                return;
            }

            // Exchange authorization code for access token
            // Get absolute URL for redirect_uri
            $redirectUri = url(tenant_route('tenant.connect', [], false));
            
            whatsapp_log('Exchanging Authorization Code for Access Token', 'info', [
                'api_endpoint' => 'https://graph.facebook.com/v18.0/oauth/access_token',
                'method' => 'POST',
                'client_id' => $this->admin_fb_app_id,
                'client_secret_length' => strlen($this->admin_fb_app_secret),
                'redirect_uri' => $redirectUri,
                'code_length' => strlen($authCode),
            ], null, tenant_id());
            
            $tokenResponse = \Illuminate\Support\Facades\Http::asForm()->post('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => $this->admin_fb_app_id,
                'client_secret' => $this->admin_fb_app_secret,
                'redirect_uri' => $redirectUri,
                'code' => $authCode,
            ]);
            
            whatsapp_log('Token Exchange Response:', 'info', [
                'status_code' => $tokenResponse->status(),
                'successful' => $tokenResponse->successful(),
                'response_body_preview' => substr($tokenResponse->body(), 0, 500),
            ], null, tenant_id());

            if ($tokenResponse->failed()) {
                $error = $tokenResponse->json('error');
                whatsapp_log('ERROR: Token exchange failed', 'error', [
                    'error_code' => $error['code'] ?? 'N/A',
                    'error_message' => $error['message'] ?? 'N/A',
                    'error_type' => $error['type'] ?? 'N/A',
                    'full_error' => $error,
                ], null, tenant_id());
                $this->notify([
                    'message' => $error['message'] ?? t('connection_failed'),
                    'type' => 'danger',
                ]);
                return;
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            whatsapp_log('Access Token Received:', 'info', [
                'token_length' => strlen($accessToken ?? ''),
                'token_preview' => $accessToken ? substr($accessToken, 0, 20) . '...' : 'NULL',
                'token_type' => $tokenData['token_type'] ?? 'N/A',
                'expires_in' => $tokenData['expires_in'] ?? 'N/A',
            ], null, tenant_id());

            if (empty($accessToken)) {
                whatsapp_log('ERROR: Access token is empty in response', 'error', [
                    'response_data' => $tokenData,
                ], null, tenant_id());
                $this->notify([
                    'message' => t('connection_failed'),
                    'type' => 'danger',
                ]);
                return;
            }

            // Get business account ID from the access token
            whatsapp_log('Fetching Business Accounts', 'info', [
                'api_endpoint' => 'https://graph.facebook.com/v18.0/me/businesses',
                'method' => 'GET',
                'access_token_length' => strlen($accessToken),
            ], null, tenant_id());
            
            $accountResponse = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/v18.0/me/businesses', [
                'access_token' => $accessToken,
            ]);
            
            whatsapp_log('Business Accounts API Response:', 'info', [
                'status_code' => $accountResponse->status(),
                'successful' => $accountResponse->successful(),
                'response_preview' => substr($accountResponse->body(), 0, 500),
            ], null, tenant_id());

            $businessAccountId = null;
            if ($accountResponse->successful()) {
                $accountData = $accountResponse->json();
                whatsapp_log('Business Accounts Data:', 'info', [
                    'has_data' => isset($accountData['data']),
                    'data_count' => isset($accountData['data']) ? count($accountData['data']) : 0,
                    'first_account' => isset($accountData['data'][0]) ? $accountData['data'][0] : null,
                ], null, tenant_id());
                
                if (isset($accountData['data']) && count($accountData['data']) > 0) {
                    $businessAccountId = $accountData['data'][0]['id'];
                    whatsapp_log('Business Account ID Found:', 'info', [
                        'business_account_id' => $businessAccountId,
                    ], null, tenant_id());
                }
            } else {
                whatsapp_log('WARNING: Business accounts API failed', 'warning', [
                    'status_code' => $accountResponse->status(),
                    'error' => $accountResponse->json(),
                ], null, tenant_id());
            }

            // If business account not found, try to get from WhatsApp Business Account
            if (empty($businessAccountId)) {
                whatsapp_log('Business Account ID not found, trying WhatsApp Business Account API', 'info', [
                    'api_endpoint' => 'https://graph.facebook.com/v18.0/me',
                    'method' => 'GET',
                    'fields' => 'whatsapp_business_accounts',
                ], null, tenant_id());
                
                $waResponse = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/v18.0/me', [
                    'access_token' => $accessToken,
                    'fields' => 'whatsapp_business_accounts',
                ]);
                
                whatsapp_log('WhatsApp Business Account API Response:', 'info', [
                    'status_code' => $waResponse->status(),
                    'successful' => $waResponse->successful(),
                    'response_preview' => substr($waResponse->body(), 0, 500),
                ], null, tenant_id());

                if ($waResponse->successful()) {
                    $waData = $waResponse->json();
                    if (isset($waData['whatsapp_business_accounts']['data']) && count($waData['whatsapp_business_accounts']['data']) > 0) {
                        $businessAccountId = $waData['whatsapp_business_accounts']['data'][0]['id'];
                        whatsapp_log('WhatsApp Business Account ID Found:', 'info', [
                            'business_account_id' => $businessAccountId,
                        ], null, tenant_id());
                    }
                }
            }

            if (empty($businessAccountId)) {
                $this->notify([
                    'message' => t('connection_failed').': Could not retrieve business account ID',
                    'type' => 'danger',
                ]);
                return;
            }

            // Check for duplicates (same as manual connection)
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

            // Save the account details - SAME AS MANUAL CONNECTION
            save_tenant_setting('whatsapp', 'wm_business_account_id', $businessAccountId);
            save_tenant_setting('whatsapp', 'wm_access_token', $accessToken);

            // Set account as connected
            $this->account_connected = true;
            $this->wm_business_account_id = $businessAccountId;
            $this->wm_access_token = $accessToken;

            // If admin webhook is connected, verify connection and complete setup
            if ($this->admin_webhook_connected) {
                whatsapp_log('Admin webhook is connected, completing setup', 'info', [
                    'admin_webhook_connected' => true,
                ], null, tenant_id());
                
                save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
                $this->is_webhook_connected = 1;

                // Verify connection and complete setup
                whatsapp_log('Loading templates from WhatsApp API', 'info', [
                    'api_endpoint' => 'WhatsApp Cloud API',
                ], null, tenant_id());
                
                $response = $this->loadTemplatesFromWhatsApp();
                
                whatsapp_log('Template Loading Response:', 'info', [
                    'status' => $response['status'],
                    'message' => $response['message'] ?? 'N/A',
                    'templates_count' => isset($response['data']) ? count($response['data']) : 0,
                ], null, tenant_id());
                
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
                save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->admin_fb_app_id);
                save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->admin_fb_app_secret);
                
                whatsapp_log('Final Settings Saved:', 'info', [
                    'is_whatsmark_connected' => $response['status'] ? 1 : 0,
                    'wm_fb_app_id' => $this->admin_fb_app_id,
                    'wm_fb_app_secret_length' => strlen($this->admin_fb_app_secret),
                ], null, tenant_id());

                if ($response['status']) {
                    $this->is_whatsmark_connected = 1;
                    whatsapp_log('========================================', 'info', [], null, tenant_id());
                    whatsapp_log('EMBEDDED SIGNUP COMPLETED SUCCESSFULLY', 'info', [], null, tenant_id());
                    whatsapp_log('========================================', 'info', [], null, tenant_id());
                    $this->notify([
                        'message' => t('whatsapp_connected_successfully'),
                        'type' => 'success',
                    ], true);

                    return redirect()->to(tenant_route('tenant.waba'));
                } else {
                    whatsapp_log('ERROR: Template loading failed', 'error', [
                        'response_message' => $response['message'] ?? 'N/A',
                    ], null, tenant_id());
                    $this->notify([
                        'message' => $response['message'],
                        'type' => 'danger',
                    ]);
                }
            } else {
                // Move to webhook setup
                whatsapp_log('Admin webhook not connected, moving to step 2', 'info', [
                    'admin_webhook_connected' => false,
                    'next_step' => 2,
                ], null, tenant_id());
                
                save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
                $this->is_webhook_connected = 0;
                $this->is_whatsmark_connected = 0;
                $this->step = 2;

                $this->notify([
                    'message' => t('account_details_saved').' '.t('now_setup_webhook'),
                    'type' => 'success',
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('========================================', 'error', [], null, tenant_id());
            whatsapp_log('EMBEDDED SIGNUP EXCEPTION', 'error', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ], $e, tenant_id());
            whatsapp_log('========================================', 'error', [], null, tenant_id());
            $this->notify([
                'message' => t('connection_failed').': '.$e->getMessage(),
                'type' => 'danger',
            ]);
        }
    }

    /**
     * Handle embedded signup with direct access token and business account ID
     * This is called from JavaScript when user clicks the Facebook login button
     */
    public function handleEmbeddedSignupDirect($accessToken, $businessAccountId)
    {
        whatsapp_log('========================================', 'info', [], null, tenant_id());
        whatsapp_log('EMBEDDED SIGNUP DIRECT - HANDLING ACCESS TOKEN', 'info', [], null, tenant_id());
        whatsapp_log('========================================', 'info', [], null, tenant_id());
        whatsapp_log('Direct Access Token Received:', 'info', [
            'access_token_length' => strlen($accessToken ?? ''),
            'access_token_preview' => $accessToken ? substr($accessToken, 0, 20) . '...' : 'NULL',
            'business_account_id' => $businessAccountId,
            'tenant_id' => tenant_id(),
        ], null, tenant_id());
        
        try {
            // Validate inputs
            if (empty($accessToken) || empty($businessAccountId)) {
                whatsapp_log('ERROR: Missing required information', 'error', [
                    'access_token_empty' => empty($accessToken),
                    'business_account_id_empty' => empty($businessAccountId),
                ], null, tenant_id());
                $this->notify([
                    'message' => t('connection_failed').': Missing required information',
                    'type' => 'danger',
                ]);
                return;
            }

            // Check for duplicates (same as manual connection)
            whatsapp_log('Checking for duplicate accounts', 'info', [
                'business_account_id' => $businessAccountId,
                'current_tenant_id' => tenant_id(),
            ], null, tenant_id());
            
            $is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
                ->where('value', 'like', "%$businessAccountId%")
                ->where('tenant_id', '!=', tenant_id())
                ->exists();
            $is_found_wm_access_token = TenantSetting::where('key', 'wm_access_token')
                ->where('value', 'like', "%$accessToken%")
                ->where('tenant_id', '!=', tenant_id())
                ->exists();

            whatsapp_log('Duplicate Check Results:', 'info', [
                'business_account_id_duplicate' => $is_found_wm_business_account_id,
                'access_token_duplicate' => $is_found_wm_access_token,
            ], null, tenant_id());

            if ($is_found_wm_business_account_id || $is_found_wm_access_token) {
                whatsapp_log('ERROR: Duplicate account detected', 'error', [
                    'business_account_duplicate' => $is_found_wm_business_account_id,
                    'token_duplicate' => $is_found_wm_access_token,
                ], null, tenant_id());
                $this->notify([
                    'message' => t('you_cant_use_this_details_already_used_by_other'),
                    'type' => 'danger',
                ]);
                return;
            }

            // Save the account details - SAME AS MANUAL CONNECTION
            whatsapp_log('Saving account details to tenant_settings', 'info', [
                'tenant_id' => tenant_id(),
                'group' => 'whatsapp',
                'wm_business_account_id' => $businessAccountId,
                'wm_access_token_length' => strlen($accessToken),
            ], null, tenant_id());
            
            save_tenant_setting('whatsapp', 'wm_business_account_id', $businessAccountId);
            save_tenant_setting('whatsapp', 'wm_access_token', $accessToken);
            
            whatsapp_log('Account details saved successfully', 'info', [], null, tenant_id());

            // Set account as connected
            $this->account_connected = true;
            $this->wm_business_account_id = $businessAccountId;
            $this->wm_access_token = $accessToken;

            // If admin webhook is connected, verify connection and complete setup
            if ($this->admin_webhook_connected) {
                whatsapp_log('Admin webhook is connected, completing setup', 'info', [
                    'admin_webhook_connected' => true,
                ], null, tenant_id());
                
                save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
                $this->is_webhook_connected = 1;

                // Verify connection and complete setup
                whatsapp_log('Loading templates from WhatsApp API', 'info', [
                    'api_endpoint' => 'WhatsApp Cloud API',
                ], null, tenant_id());
                
                $response = $this->loadTemplatesFromWhatsApp();
                
                whatsapp_log('Template Loading Response:', 'info', [
                    'status' => $response['status'],
                    'message' => $response['message'] ?? 'N/A',
                    'templates_count' => isset($response['data']) ? count($response['data']) : 0,
                ], null, tenant_id());
                
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
                save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->admin_fb_app_id);
                save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->admin_fb_app_secret);
                
                whatsapp_log('Final Settings Saved:', 'info', [
                    'is_whatsmark_connected' => $response['status'] ? 1 : 0,
                    'wm_fb_app_id' => $this->admin_fb_app_id,
                    'wm_fb_app_secret_length' => strlen($this->admin_fb_app_secret),
                ], null, tenant_id());

                if ($response['status']) {
                    $this->is_whatsmark_connected = 1;
                    whatsapp_log('========================================', 'info', [], null, tenant_id());
                    whatsapp_log('EMBEDDED SIGNUP DIRECT COMPLETED SUCCESSFULLY', 'info', [], null, tenant_id());
                    whatsapp_log('========================================', 'info', [], null, tenant_id());
                    $this->notify([
                        'message' => t('whatsapp_connected_successfully'),
                        'type' => 'success',
                    ], true);

                    return redirect()->to(tenant_route('tenant.waba'));
                } else {
                    whatsapp_log('ERROR: Template loading failed', 'error', [
                        'response_message' => $response['message'] ?? 'N/A',
                    ], null, tenant_id());
                    $this->notify([
                        'message' => $response['message'],
                        'type' => 'danger',
                    ]);
                }
            } else {
                // Move to webhook setup
                whatsapp_log('Admin webhook not connected, moving to step 2', 'info', [
                    'admin_webhook_connected' => false,
                    'next_step' => 2,
                ], null, tenant_id());
                
                save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
                save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
                $this->is_webhook_connected = 0;
                $this->is_whatsmark_connected = 0;
                $this->step = 2;

                $this->notify([
                    'message' => t('account_details_saved').' '.t('now_setup_webhook'),
                    'type' => 'success',
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('========================================', 'error', [], null, tenant_id());
            whatsapp_log('EMBEDDED SIGNUP DIRECT EXCEPTION', 'error', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ], $e, tenant_id());
            whatsapp_log('========================================', 'error', [], null, tenant_id());
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
