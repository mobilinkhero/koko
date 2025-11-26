<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WhatsApp Embedded Signup Service
 * 
 * Handles the complete WhatsApp Cloud API embedded signup flow
 * compatible with manual connection data structure
 */
class WhatsAppEmbeddedSignupService
{
    protected string $baseApiEndpoint = 'https://graph.facebook.com/v23.0/';
    protected ?string $accessToken = null;
    protected ?int $tenantId = null;

    /**
     * Process embedded signup flow
     * 
     * @param array $data Contains: request_code, waba_id, phone_number_id, is_app_onboarding
     * @param string $appId Facebook App ID
     * @param string $appSecret Facebook App Secret
     * @param int $tenantId Tenant ID
     * @return array Response with status and data/message
     */
    public function processEmbeddedSignup(array $data, string $appId, string $appSecret, int $tenantId): array
    {
        $this->tenantId = $tenantId;
        $isBusinessAppOnboarding = ($data['is_app_onboarding'] ?? '') === 'YES';
        $wabaId = $data['waba_id'] ?? null;
        $phoneNumberId = $data['phone_number_id'] ?? null;

        try {
            // Log the start of the process
            $this->logProgress('Starting embedded signup process...', [
                'waba_id' => $wabaId,
                'phone_number_id' => $phoneNumberId,
                'is_app_onboarding' => $isBusinessAppOnboarding
            ]);

            // Validate required data
            if (empty($wabaId)) {
                throw new Exception('WABA ID is required but was not provided by Facebook');
            }

            if (!$isBusinessAppOnboarding && empty($phoneNumberId)) {
                throw new Exception('Phone Number ID is required for standard onboarding');
            }

            // Step 1: Exchange authorization code for access token
            $this->logProgress('Exchanging authorization code for access token...');
            $this->accessToken = $this->exchangeCodeForToken($data['request_code'], $appId, $appSecret);
            
            if (!$this->accessToken) {
                throw new Exception('Failed to obtain access token from Facebook');
            }

            $this->logProgress('Access token obtained successfully');

            // Step 2: Get and verify phone numbers
            $this->logProgress('Fetching phone numbers from WhatsApp Business Account...');
            $phoneNumbers = $this->getPhoneNumbers($wabaId);
            
            if (empty($phoneNumbers['data'])) {
                throw new Exception('No phone numbers found for this WhatsApp Business Account');
            }

            // Step 3: Handle phone number registration if needed
            if (!$isBusinessAppOnboarding) {
                $phoneNumberRecord = $this->getPhoneNumberRecord($phoneNumbers['data'], $phoneNumberId);
                
                // Register phone number if not properly configured
                if ($this->needsRegistration($phoneNumberRecord)) {
                    $this->logProgress('Registering phone number with WhatsApp Cloud API...');
                    $this->registerPhoneNumber($phoneNumberId);
                    
                    // Fetch updated phone numbers
                    $phoneNumbers = $this->getPhoneNumbers($wabaId);
                    $phoneNumberRecord = $this->getPhoneNumberRecord($phoneNumbers['data'], $phoneNumberId);
                }
            } else {
                // For business app onboarding, get the first phone number
                $phoneNumberRecord = $phoneNumbers['data'][0] ?? null;
                $phoneNumberId = $phoneNumberRecord['id'] ?? null;
                
                if (!$phoneNumberId) {
                    throw new Exception('Could not retrieve phone number from WhatsApp Business Account');
                }
            }

            // Step 4: Setup webhook
            $this->logProgress('Configuring webhook subscriptions...');
            $webhookSetup = $this->setupWebhook($wabaId);
            
            if (!$webhookSetup['success']) {
                throw new Exception('Failed to setup webhook: ' . ($webhookSetup['message'] ?? 'Unknown error'));
            }

            $this->logProgress('Webhook configured successfully');

            // Step 5: Handle business app contact sync if applicable
            $contactsSyncRequestId = null;
            if ($isBusinessAppOnboarding) {
                $this->logProgress('Requesting contacts sync for Business App...');
                $contactsSyncRequestId = $this->syncBusinessAppContacts($phoneNumberId);
            }

            // Step 6: Prepare data to save (compatible with manual connection format)
            $dataToSave = [
                'wm_business_account_id' => $wabaId,
                'wm_access_token' => $this->accessToken,
                'wm_fb_app_id' => $appId,
                'wm_fb_app_secret' => $appSecret,
                'is_webhook_connected' => 1,
                'is_whatsmark_connected' => 1,
                'wm_default_phone_number_id' => $phoneNumberId,
                'wm_default_phone_number' => $this->cleanPhoneNumber($phoneNumberRecord['display_phone_number'] ?? ''),
                'embedded_signup_completed_at' => now()->format('Y-m-d H:i:s'),
                'whatsapp_onboarding_method' => 'embedded_signup',
                'whatsapp_phone_numbers_data' => json_encode($phoneNumbers),
                'whatsapp_onboarding_raw_data' => json_encode([
                    'waba_id' => $wabaId,
                    'phone_number_id' => $phoneNumberId,
                    'is_app_onboarded' => $isBusinessAppOnboarding,
                    'contacts_sync_request_id' => $contactsSyncRequestId,
                    'webhook_setup' => $webhookSetup,
                ])
            ];

            $this->logProgress('Embedded signup completed successfully!');

            return [
                'status' => true,
                'message' => 'WhatsApp account connected successfully via Embedded Signup',
                'data' => $dataToSave
            ];

        } catch (Exception $e) {
            $this->logProgress('Error: ' . $e->getMessage(), [], 'error');
            
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Exchange authorization code for access token
     */
    protected function exchangeCodeForToken(string $code, string $appId, string $appSecret): ?string
    {
        try {
            $response = Http::asForm()->post("{$this->baseApiEndpoint}oauth/access_token", [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'code' => $code,
            ]);

            if ($response->failed()) {
                $error = $response->json('error');
                throw new Exception($error['message'] ?? 'Failed to exchange code for token');
            }

            $data = $response->json();
            return $data['access_token'] ?? null;

        } catch (Exception $e) {
            throw new Exception('Token exchange failed: ' . $e->getMessage());
        }
    }

    /**
     * Get phone numbers from WhatsApp Business Account
     */
    protected function getPhoneNumbers(string $wabaId): array
    {
        try {
            $response = $this->apiGetRequest("{$wabaId}/phone_numbers", [
                'fields' => 'id,verified_name,display_phone_number,quality_rating,platform_type,code_verification_status,is_official_business_account'
            ]);

            if (!isset($response['data'])) {
                throw new Exception('Invalid response from phone numbers API');
            }

            return $response;

        } catch (Exception $e) {
            throw new Exception('Failed to fetch phone numbers: ' . $e->getMessage());
        }
    }

    /**
     * Get specific phone number record from the array
     */
    protected function getPhoneNumberRecord(array $phoneNumbers, string $phoneNumberId): ?array
    {
        foreach ($phoneNumbers as $number) {
            if ($number['id'] == $phoneNumberId) {
                return $number;
            }
        }
        return null;
    }

    /**
     * Check if phone number needs registration
     */
    protected function needsRegistration(?array $phoneNumberRecord): bool
    {
        if (!$phoneNumberRecord) {
            return true;
        }

        // Check if platform type is not Cloud API or if it's on business app
        return ($phoneNumberRecord['platform_type'] ?? '') !== 'CLOUD_API' ||
               ($phoneNumberRecord['is_on_biz_app'] ?? false) !== false;
    }

    /**
     * Register phone number with WhatsApp Cloud API
     */
    protected function registerPhoneNumber(string $phoneNumberId): void
    {
        try {
            $response = $this->apiPostRequest("{$phoneNumberId}/register", [
                'messaging_product' => 'whatsapp',
                'pin' => '123456',
            ]);

            if (!($response['success'] ?? false)) {
                throw new Exception('Phone number registration failed');
            }

        } catch (Exception $e) {
            throw new Exception('Failed to register phone number: ' . $e->getMessage());
        }
    }

    /**
     * Setup webhook for WhatsApp Business Account
     */
    protected function setupWebhook(string $wabaId): array
    {
        try {
            // Get webhook URL and verify token from settings
            $webhookUrl = route('whatsapp.webhook');
            
            // Get the verify token from settings (same as webhook controller uses)
            $verifyToken = get_setting('whatsapp', 'webhook_verify_token');
            
            // If no verify token is configured, generate and save one
            if (empty($verifyToken)) {
                $verifyToken = Str::random(32);
                save_setting('whatsapp', 'webhook_verify_token', $verifyToken);
            }

            // Subscribe to webhook
            $subscribeResponse = $this->apiPostRequest("{$wabaId}/subscribed_apps");

            // Override webhook URL for this specific WABA
            $overrideResponse = $this->apiPostRequest("{$wabaId}/subscribed_apps", [
                'override_callback_uri' => $webhookUrl,
                'verify_token' => $verifyToken
            ]);

            if (!($overrideResponse['success'] ?? false)) {
                throw new Exception('Failed to configure webhook override');
            }

            return [
                'success' => true,
                'webhook_url' => $webhookUrl,
                'verify_token' => $verifyToken,
                'subscribe_response' => $subscribeResponse,
                'override_response' => $overrideResponse
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync contacts from WhatsApp Business App
     */
    protected function syncBusinessAppContacts(string $phoneNumberId): ?string
    {
        try {
            $response = $this->apiPostRequest("{$phoneNumberId}/smb_app_data", [
                'messaging_product' => 'whatsapp',
                'sync_type' => 'smb_app_state_sync'
            ]);

            return $response['request_id'] ?? null;

        } catch (Exception $e) {
            // Log but don't fail - contact sync is optional
            $this->logProgress('Contact sync warning: ' . $e->getMessage(), [], 'warning');
            return null;
        }
    }

    /**
     * Clean phone number (remove non-digits)
     */
    protected function cleanPhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/\D/', '', $phoneNumber);
    }

    /**
     * Make GET request to Facebook API
     */
    protected function apiGetRequest(string $endpoint, array $params = []): array
    {
        return $this->makeApiRequest('get', $endpoint, $params);
    }

    /**
     * Make POST request to Facebook API
     */
    protected function apiPostRequest(string $endpoint, array $params = []): array
    {
        return $this->makeApiRequest('post', $endpoint, $params);
    }

    /**
     * Make API request with error handling
     */
    protected function makeApiRequest(string $method, string $endpoint, array $params = []): array
    {
        try {
            $url = $this->baseApiEndpoint . $endpoint;
            
            $request = Http::withToken($this->accessToken);
            
            $response = $method === 'get' 
                ? $request->get($url, $params)
                : $request->post($url, $params);

            if ($response->failed()) {
                $error = $response->json('error');
                $errorMessage = $error['error_user_title'] ?? $error['message'] ?? $error['error_user_msg'] ?? 'API request failed';
                throw new Exception($errorMessage);
            }

            return $response->json();

        } catch (Exception $e) {
            throw new Exception("API request to {$endpoint} failed: " . $e->getMessage());
        }
    }

    /**
     * Log progress for debugging and user feedback
     */
    protected function logProgress(string $message, array $context = [], string $level = 'info'): void
    {
        $logData = array_merge([
            'tenant_id' => $this->tenantId,
            'process' => 'embedded_signup'
        ], $context);

        Log::channel('whatsapp')->{$level}($message, $logData);
        
        // Optionally dispatch an event for real-time UI updates
        // event(new EmbeddedSignupProgress($this->tenantId, $message));
    }
}
