<?php

namespace App\Traits;

use App\Models\Tenant\WhatsappTemplate;
use App\Models\Tenant\WmActivityLog;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\Button;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\ButtonAction;
use Netflie\WhatsAppCloudApi\Message\CtaUrl\TitleHeader;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Throwable;

trait WhatsApp
{
    use Ai;

    protected static string $facebookAPI = 'https://graph.facebook.com/';

    /**
     * Store WhatsApp settings to avoid multiple database calls
     */
    protected $whatsappSettings;

    protected $connectionSettings;

    /**
     * Load all WhatsApp settings in a single batch call
     */
    protected function loadWhatsAppSettings()
    {
        if (!isset($this->whatsappSettings)) {
            $this->whatsappSettings = get_batch_settings([
                'whatsapp.api_version',
                'whatsapp.wm_fb_app_id',
                'whatsapp.wm_fb_app_secret',
                'whatsapp.queue',
                'whatsapp.webhook_verify_token',
            ]);
        }

        return $this->whatsappSettings;
    }

    protected static array $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'audio/mp3' => 'mp3',
        'video/mp4' => 'mp4',
        'audio/aac' => 'aac',
        'audio/amr' => 'amr',
        'audio/ogg' => 'ogg',
        'audio/mp4' => 'mp4',
        'text/plain' => 'txt',
        'application/pdf' => 'pdf',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/msword' => 'doc',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'video/3gp' => '3gp',
        'image/webp' => 'webp',
    ];

    // Add this property to store tenant ID
    protected $wa_tenant_id = null;

    // Add this method to set tenant ID and enable method chaining
    public function setWaTenantId($tenant_id)
    {
        $this->wa_tenant_id = $tenant_id;

        return $this;
    }

    // Add this method to get current tenant ID with fallback
    protected function getWaTenantId()
    {
        return $this->wa_tenant_id ?? tenant_id();
    }

    protected static function getApiVersion(): string
    {
        // For static context, use regular get_setting
        $settings = get_batch_settings(['whatsapp.api_version']);

        return $settings['whatsapp.api_version'] ?? 'v21.0';
    }

    protected static function getBaseUrl(): string
    {
        return self::$facebookAPI . self::getApiVersion() . '/';
    }

    protected function handleApiError(Throwable $e, string $operation, array $context = []): array
    {
        $tenant_id = $this->getWaTenantId();

        $errorContext = array_merge([
            'operation' => $operation,
            'account_id' => $this->getAccountID(),
            'phone_id' => $this->getPhoneID(),
            'tenant_id' => $tenant_id,
        ], $context);

        // Get user-friendly message based on debug mode
        $userMessage = config('app.debug')
            ? $e->getMessage()
            : __($operation, ['default' => 'An error occurred during ' . $operation]);

        whatsapp_log("[WhatsApp {$operation} Error] " . $e->getMessage(), 'error', $errorContext, $e, $tenant_id);

        return [
            'status' => false,
            'message' => $userMessage,
        ];
    }

    /**
     * Load core WhatsApp connection settings in a single batch call
     */
    protected function loadConnectionSettings()
    {
        if (!isset($this->connectionSettings)) {
            $this->connectionSettings = tenant_settings_by_group('whatsapp', $this->wa_tenant_id);
        }

        return $this->connectionSettings;
    }

    private function getToken(): ?string
    {
        $this->loadConnectionSettings();

        return $this->connectionSettings['wm_access_token'] ?? null;
    }

    private function getAccountID(): ?string
    {
        $this->loadConnectionSettings();

        return $this->connectionSettings['wm_business_account_id'] ?? null;
    }

    private function getPhoneID(): ?string
    {
        $this->loadConnectionSettings();

        return $this->connectionSettings['wm_default_phone_number_id'] ?? null;
    }

    private function getFBAppID(): ?string
    {
        $this->loadWhatsAppSettings();

        return $this->whatsappSettings['whatsapp.wm_fb_app_id'] ?? null;
    }

    private function getFBAppSecret(): ?string
    {
        $this->loadWhatsAppSettings();

        return $this->whatsappSettings['whatsapp.wm_fb_app_secret'] ?? null;
    }

    /**
     * Load WhatsApp Cloud API configuration
     *
     * @param  string|null  $fromNumber  Optional phone number to use as the sender
     * @return WhatsAppCloudApi Instance of the WhatsAppCloudApi class
     */
    public function loadConfig($fromNumber = null)
    {
        return new WhatsAppCloudApi([
            'from_phone_number_id' => (!empty($fromNumber)) ? $fromNumber : $this->getPhoneID(),
            'access_token' => $this->getToken(),
        ]);
    }

    public function getPhoneNumbers(): array
    {
        try {
            $response = Http::get(self::getBaseUrl() . "{$this->getAccountID()}/phone_numbers", [
                'access_token' => $this->getToken(),
            ]);

            if ($response->failed()) {
                $errorData = $response->json('error') ?? [];
                $userMessage = $this->extractUserFriendlyErrorMessage($errorData, 'Failed to get phone numbers.');

                return [
                    'status' => false,
                    'message' => $userMessage,
                ];
            }

            return ['status' => true, 'data' => $response->json('data')];
        } catch (Throwable $e) {
            return $this->handleApiError($e, 'get_phone_numbers');
        }
    }

    public function loadTemplatesFromWhatsApp(): array
    {
        try {
            $accountId = $this->getAccountID();
            $accessToken = $this->getToken();
            $tenant_id = $this->getWaTenantId();

            $templates = [];
            $url = self::getBaseUrl() . "{$accountId}/message_templates?limit=100&access_token={$accessToken}";

            // Fetch all templates using pagination
            do {
                $response = Http::get($url);

                if ($response->failed()) {
                    $errorData = $response->json('error') ?? [];
                    $userMessage = $this->extractUserFriendlyErrorMessage($errorData, 'Failed to load message templates.');

                    return [
                        'status' => false,
                        'message' => $userMessage,
                    ];
                }

                $data = $response->json('data');
                
                // If no templates found, that's OK - not an error
                // User might not have created any templates yet
                if (!$data) {
                    whatsapp_log('No message templates found for this account', 'info', [
                        'account_id' => $accountId,
                        'tenant_id' => $tenant_id,
                    ], null, $tenant_id);
                    break; // Exit the loop, continue with empty templates
                }

                $templates = array_merge($templates, $data);

                $url = $response->json('paging.next') ?? null;
            } while ($url);

            // Get existing template IDs from database to track what should be deleted
            $existingTemplateIds = WhatsappTemplate::where('tenant_id', $tenant_id)
                ->pluck('template_id')->toArray();
            $apiTemplateIds = [];

            foreach ($templates as $templateData) {
                $apiTemplateIds[] = $templateData['id'];
                $template = [
                    'template_name' => $templateData['name'],
                    'language' => $templateData['language'],
                    'status' => $templateData['status'],
                    'category' => $templateData['category'],
                    'tenant_id' => $tenant_id,
                ];

                // Initialize variables
                $components = [];
                $headerText = $bodyText = $footerText = $buttonsData = null;
                $headerParamsCount = $bodyParamsCount = $footerParamsCount = 0;
                $headerVariableValue = null;
                $headerFileUrl = null;
                $bodyVariableValue = null;

                // Loop through components
                foreach ($templateData['components'] as $component) {
                    $type = $component['type'] ?? null;

                    if ($type === 'HEADER') {
                        $format = $component['format'] ?? null;
                        $components['TYPE'] = $format;

                        if (isset($component['text'])) {
                            $headerText = $component['text'];
                            $headerParamsCount = preg_match_all('/{{(.*?)}}/i', $headerText, $matches);
                            $components['HEADER'] = $headerText;
                        }

                        if (isset($component['example'])) {
                            if (!empty($component['example']['header_text'])) {
                                $headerVariableValue = $component['example']['header_text'];
                            }
                            if (!empty($component['example']['header_handle'])) {
                                $headerVariableValue = $component['example']['header_handle'];
                                $headerFileUrl = $headerVariableValue[0];
                            }
                        }
                    }

                    if ($type === 'BODY' && isset($component['text'])) {
                        $bodyText = $component['text'];
                        $bodyParamsCount = preg_match_all('/{{(.*?)}}/i', $bodyText, $matches);
                        $components['BODY'] = $bodyText;

                        if (isset($component['example']['body_text'])) {
                            $bodyVariableValue = $component['example']['body_text'];
                        }
                    }

                    if ($type === 'FOOTER' && isset($component['text'])) {
                        $footerText = $component['text'];
                        $footerParamsCount = preg_match_all('/{{(.*?)}}/i', $footerText, $matches);
                        $components['FOOTER'] = $footerText;
                    }

                    if ($type === 'BUTTONS') {
                        $components['BUTTONS'] = isset($component['buttons']) ? json_encode($component['buttons']) : null;
                    }
                }

                // Assign all extracted data to the $template array
                $template['header_data_text'] = $components['HEADER'] ?? null;
                $template['header_data_format'] = $components['TYPE'] ?? null;
                $template['body_data'] = $components['BODY'] ?? null;
                $template['footer_data'] = $components['FOOTER'] ?? null;
                $template['buttons_data'] = $components['BUTTONS'] ?? null;
                $template['header_params_count'] = $headerParamsCount;
                $template['body_params_count'] = $bodyParamsCount;
                $template['footer_params_count'] = $footerParamsCount;

                // New fields
                $template['header_file_url'] = $headerFileUrl;

                // Always save as JSON string
                $template['header_variable_value'] = $headerVariableValue
                    ? json_encode($headerVariableValue)
                    : null;

                $template['body_variable_value'] = $bodyVariableValue
                    ? json_encode($bodyVariableValue)
                    : null;

                // Save or update
                WhatsappTemplate::updateOrCreate(
                    [
                        'template_id' => $templateData['id'],
                        'tenant_id' => $tenant_id,
                    ],
                    $template
                );
            }

            // Delete templates that exist in DB but not in API
            $templatesForDeletion = array_diff($existingTemplateIds, $apiTemplateIds);
            if (!empty($templatesForDeletion)) {
                $deletedCount = WhatsappTemplate::where('tenant_id', $tenant_id)
                    ->whereIn('template_id', $templatesForDeletion)
                    ->delete();

                whatsapp_log('Deleted templates during sync', 'info', [
                    'deleted_count' => $deletedCount,
                    'template_ids' => $templatesForDeletion,
                    'tenant_id' => $tenant_id,
                ], null, $tenant_id);
            }

            // Generate appropriate success message
            $templateCount = count($apiTemplateIds);
            if ($templateCount === 0) {
                $message = t('connection_successful_no_templates') ?? 'WhatsApp connected successfully! No message templates found. You can create templates in Meta Business Suite.';
            } else {
                $message = t('templates_synced_successfully') ?? "Successfully synced {$templateCount} message template(s).";
            }

            return [
                'status' => true,
                'data' => $templates,
                'synced' => [
                    'updated_or_created' => count($apiTemplateIds),
                    'deleted' => count($templatesForDeletion),
                ],
                'message' => $message,
            ];
        } catch (Throwable $e) {
            return $this->handleApiError($e, $e->getMessage() ?? '');
        }
    }

    public function subscribeWebhook()
    {
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();
        $tenant_id = $this->getWaTenantId();
        $url = self::$facebookAPI . "/$accountId/subscribed_apps?access_token=" . $accessToken;

        try {
            $response = Http::post($url);

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'status' => false,
                    'message' => $data['error']['error_user_msg'] ?? $data['error']['message'] ?? 'Failed to subscribe webhook.',
                ];
            }

            return [
                'status' => true,
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            whatsapp_log('Failed to subscribe webhook: ' . $th->getMessage(), 'error', [
                'url' => $url,
                'account_id' => $accountId,
                'tenant_id' => $tenant_id,
            ], $th, $tenant_id);

            return [
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ];
        }
    }

    public function registerPhoneNumber(string $phoneNumberId)
    {
        $accessToken = $this->getToken();
        $tenantId = $this->getWaTenantId();

        $url = self::$facebookAPI . self::getApiVersion() . "/{$phoneNumberId}/register";

        try {
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'pin' => '123456',
                ]);

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'status' => false,
                    'message' => $data['error']['error_user_msg'] ?? $data['error']['message'] ?? 'Failed to register phone number.',
                ];
            }

            return [
                'status' => true,
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            whatsapp_log('Failed to register phone number: ' . $th->getMessage(), 'error', [
                'url' => $url,
                'phone_number_id' => $phoneNumberId,
                'tenant_id' => $tenantId,
            ], $th, $tenantId);

            return [
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ];
        }
    }

    public function getMessagingLimit()
    {
        $accessToken = $this->getToken();
        $phoneNumberId = $this->getPhoneID();
        $tenantId = $this->getWaTenantId();

        $url = self::$facebookAPI . self::getApiVersion() . "/{$phoneNumberId}?fields=messaging_limit_tier";

        try {
            $response = Http::withToken($accessToken)->get($url);

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'status' => false,
                    'message' => $data['error']['error_user_msg'] ?? $data['error']['message'] ?? 'Failed to get messaging limit.',
                ];
            }

            $limits = [
                'TIER_250' => 250,
                'TIER_500' => 500,
                'TIER_1K' => 1000,
                'TIER_10K' => 10000,
                'TIER_100K' => 100000,
                'TIER_UNLIMITED' => -1,
            ];

            $tier = $data['messaging_limit_tier'];
            $data['limit_value'] = $limits[$tier] ?? 0;

            return [
                'status' => true,
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            whatsapp_log('Failed to get phone number limit: ' . $th->getMessage(), 'error', [
                'url' => $url,
                'phone_number_id' => $phoneNumberId,
                'tenant_id' => $tenantId,
            ], $th, $tenantId);

            return [
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ];
        }
    }

    public function debugToken($fb_app_id, $fb_app_secret): array
    {
        try {
            $accessToken = $this->getToken();
            $appAccessToken = $fb_app_id . '|' . $fb_app_secret;

            $response = Http::get(self::getBaseUrl() . 'debug_token', [
                'input_token' => $accessToken,
                'access_token' => $appAccessToken,
            ]);

            if ($response->failed()) {
                $errorData = $response->json('error') ?? [];
                $userMessage = $this->extractUserFriendlyErrorMessage($errorData, 'Failed to debug token.');

                return [
                    'status' => false,
                    'message' => $userMessage,
                ];
            }

            return ['status' => true, 'data' => $response->json('data')];
        } catch (Throwable $e) {
            return $this->handleApiError($e, 'debug_token');
        }
    }

    public function getProfile(): array
    {
        try {
            $response = Http::get(self::getBaseUrl() . $this->getPhoneID() . '/whatsapp_business_profile', [
                'fields' => 'profile_picture_url',
                'access_token' => $this->getToken(),
            ]);

            if ($response->failed()) {
                $errorData = $response->json('error') ?? [];
                $userMessage = $this->extractUserFriendlyErrorMessage($errorData, 'Failed to get business profile.');

                return [
                    'status' => false,
                    'data' => [],
                    'message' => $userMessage,
                ];
            }

            return ['status' => true, 'data' => $response->json('data')];
        } catch (Throwable $e) {
            $data = $this->handleApiError($e, 'get_profile');

            return ['status' => false, 'data' => [], 'message' => $data['message']];
        }
    }

    public function getHealthStatus(): array
    {
        try {
            $response = Http::get(self::getBaseUrl() . $this->getAccountID(), [
                'fields' => 'health_status',
                'access_token' => $this->getToken(),
            ]);

            if ($response->failed()) {
                $errorData = $response->json('error') ?? [];
                $userMessage = $this->extractUserFriendlyErrorMessage($errorData, 'Failed to get health status.');

                return [
                    'status' => false,
                    'message' => $userMessage,
                ];
            }

            return ['status' => true, 'data' => $response->json()];
        } catch (Throwable $e) {
            return $this->handleApiError($e, 'health_status');
        }
    }

    public function getMessageLimit(): array
    {
        $startTime = strtotime(date('Y-m-d 00:00:00'));
        $endTime = strtotime(date('Y-m-d 23:59:59'));
        try {

            $response = Http::get(self::getBaseUrl() . $this->getAccountID(), [
                'fields' => "id,name,analytics.start({$startTime}).end({$endTime}).granularity(DAY)",
                'access_token' => $this->getToken(),
            ]);

            if ($response->failed()) {
                $errorData = $response->json('error') ?? [];
                $userMessage = $this->extractUserFriendlyErrorMessage($errorData, 'Failed to get message limit.');

                return [
                    'status' => false,
                    'data' => [],
                    'message' => $userMessage,
                ];
            }

            return ['status' => true, 'data' => $response->json()];
        } catch (Throwable $e) {
            $data = $this->handleApiError($e, 'get_message_limit');

            return ['status' => false, 'data' => [], 'message' => $data['message']];
        }
    }

    public function generateUrlQR(string $url, ?string $logo = null): bool
    {
        try {
            $tenant_id = $this->getWaTenantId();
            $writer = new PngWriter;

            $qrCode = new QrCode(
                data: $url,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            );

            if ($logo) {
                $logo = new Logo(
                    path: public_path('img/whatsapp.png'),
                    resizeToWidth: 50,
                    punchoutBackground: true
                );
            }

            // Create generic label
            $label = new Label(
                text: '',
                textColor: new Color(255, 0, 0)
            );

            // Generate the QR code
            $result = $writer->write($qrCode, $logo, $label);

            create_storage_link();

            // Define the path to save the file
            $filePath = storage_path("app/public/tenant/{$tenant_id}/images/qrcode.png");

            // Ensure the directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            // Save the QR code to the file
            $result->saveToFile($filePath);

            return true;
        } catch (Throwable $e) {
            $tenant_id = $this->getWaTenantId();
            whatsapp_log('Error generating QR code: ' . $e->getMessage(), 'error', [
                'url' => $url,
                'logo' => $logo,
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return false;
        }
    }

    public function connectWebhook($appId = null, $appSecret = null)
    {
        $appId = $appId ?? $this->getFBAppID();
        $appSecret = $appSecret ?? $this->getFBAppSecret();
        $tenant_id = $this->getWaTenantId();

        try {
            $url = self::$facebookAPI . $appId . '/subscriptions?access_token=' . $appId . '|' . $appSecret;

            $response = Http::post($url, [
                'object' => 'whatsapp_business_account',
                'fields' => 'messages,message_template_quality_update,message_template_status_update,account_update',
                'callback_url' => route('whatsapp.webhook'),
                'verify_token' => $this->loadWhatsAppSettings()['whatsapp.webhook_verify_token'] ?? '',
            ]);

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'status' => false,
                    'message' => $data['error']['error_user_msg'] ?? $data['error']['message'] ?? 'Failed to connect webhook.',
                ];
            }

            return [
                'status' => true,
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            whatsapp_log('Error connecting webhook: ' . $th->getMessage(), 'error', [
                'tenant_id' => $tenant_id,
            ], $th, $tenant_id);

            return [
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ];
        }
    }

    public function disconnectWebhook()
    {
        $appId = $this->getFBAppID();
        $appSecret = $this->getFBAppSecret();
        $tenant_id = $this->getWaTenantId();

        $url = self::$facebookAPI . $appId . '/subscriptions?access_token=' . $appId . '|' . $appSecret;

        try {
            $response = Http::delete($url, [], [
                'object' => 'whatsapp_business_account',
                'fields' => 'messages,message_template_quality_update,message_template_status_update,account_update',
            ]);

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'status' => false,
                    'message' => $data['error']['error_user_msg'] ?? $data['error']['message'] ?? 'Failed to disconnect webhook.',
                ];
            }

            return [
                'status' => true,
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            whatsapp_log('Error disconnecting webhook: ' . $th->getMessage(), 'error', [
                'tenant_id' => $tenant_id,
            ], $th, $tenant_id);

            return [
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ];
        }
    }

    public function sendTestMessages($number)
    {
        $tenant_id = $this->getWaTenantId();
        $whatsapp_cloud_api = $this->loadConfig();

        try {
            $result = $whatsapp_cloud_api->sendTemplate($number, 'hello_world', 'en_US');
            $status = true;
            $message = t('whatsapp_message_sent_successfully');
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $errorData = $th->responseData()['error'] ?? [];
            $message = $errorData['error_user_msg'] ?? $errorData['message'] ?? $th->rawResponse() ?? $th->getMessage() ?? 'Failed to send test message.';
            $responseCode = $th->httpStatusCode();

            whatsapp_log('Error sending test message: ' . $message, 'error', [
                'number' => $number,
                'response_code' => $responseCode,
                'tenant_id' => $tenant_id,
            ], $th, $tenant_id);
        }

        return ['status' => $status, 'message' => $message ?? ''];
    }

    public function checkServiceHealth(): array
    {
        try {
            $tenant_id = $this->getWaTenantId();

            // Load settings once if not already loaded
            $this->loadWhatsAppSettings();
            $queueSettings = json_decode($this->whatsappSettings['whatsapp.queue'] ?? '{"name":"default"}', true);

            $healthData = [
                'api_status' => $this->getHealthStatus(),
                'queue_size' => Queue::size($queueSettings['name']),
                'daily_api_calls' => Cache::get('whatsapp_api_calls_' . now()->format('Y-m-d')),
                'token_status' => $this->debugToken($this->getFBAppID(), $this->getFBAppSecret()),
                'profile_status' => $this->getProfile(),
                'tenant_id' => $tenant_id,
            ];

            whatsapp_log(
                'WhatsApp service health check',
                'info',
                $healthData,
                null,
                $tenant_id
            );

            return ['status' => true, 'data' => $healthData];
        } catch (Throwable $e) {
            return $this->handleApiError($e, 'health_check');
        }
    }

    protected function getExtensionForType(string $mimeType): ?string
    {
        return self::$extensionMap[$mimeType] ?? null;
    }

    /**
     * Extract user-friendly error message from WhatsApp API error response
     *
     * @param  array  $errorData  The error data from API response
     * @param  string  $fallbackMessage  Default message if no user-friendly message is available
     * @return string User-friendly error message
     */
    protected function extractUserFriendlyErrorMessage(array $errorData, string $fallbackMessage = 'An error occurred'): string
    {
        return $errorData['error_user_msg'] ?? $errorData['message'] ?? $fallbackMessage;
    }

    /**
     * Send a template message using the WhatsApp Cloud API
     *
     * @param  string  $to  Recipient phone number
     * @param  array  $template_data  Data for the template message
     * @param  string  $type  Type of the message, default is 'campaign'
     * @param  string|null  $fromNumber  Optional sender phone number
     * @return array Response containing status, log data, and any response data or error message
     */
    public function sendTemplate($to, $template_data, $type = 'campaign', $fromNumber = null, $logEntry = true)
    {
        $tenant_id = $this->getWaTenantId();

        // CONVERSATION LIMIT CHECK FOR CAMPAIGNS
        $conversationTrackingNeeded = false;
        $identifierForTracking = null;

        if (($type === 'campaign' || $type == 'Initiate Chat') && !empty($template_data['rel_id'])) {
            $featureService = app(\App\Services\FeatureService::class);
            $tenant_subdomain = tenant_subdomain_by_tenant_id($tenant_id);

            // Determine the conversation type and identifier
            $conversationType = $template_data['rel_type'] ?? 'guest';
            $identifierForCheck = $template_data['rel_id'];

            // Check if this would be a new conversation
            try {
                $hasActiveSession = $featureService->isConversationSessionActive(
                    $identifierForCheck,
                    $tenant_id,
                    $tenant_subdomain,
                    $conversationType
                );

                if (!$hasActiveSession) {
                    // This would be a new conversation
                    $conversationTrackingNeeded = true;
                    $identifierForTracking = $identifierForCheck;

                    // Check conversation limit
                    if ($featureService->checkConversationLimit($identifierForCheck, $tenant_id, $tenant_subdomain, $conversationType)) {
                        whatsapp_log('Campaign: Conversation limit reached - BLOCKING', 'warning', [
                            'to' => $to,
                            'identifier' => $identifierForCheck,
                            'type' => $conversationType,
                            'current_usage' => $featureService->getCurrentUsage('conversations'),
                            'current_limit' => $featureService->getLimit('conversations'),
                        ], null, $tenant_id);

                        $logdata = [
                            'status' => false,
                            'log_data' => [
                                'response_code' => 429,
                                'category' => $type,
                                'category_id' => $template_data['campaign_id'] ?? $template_data['template_bot_id'] ?? 0,
                                'rel_type' => $conversationType,
                                'rel_id' => $identifierForCheck,
                                'response_data' => json_encode(['error' => 'Conversation limit reached']),
                                'tenant_id' => $tenant_id,
                                'category_params' => json_encode(['templateId' => $template_data['template_id'], 'message' => $message ?? '']),
                                'raw_data' => json_encode(['error' => 'Conversation limit reached']),
                                'phone_number_id' => $this->getPhoneID(),
                                'access_token' => $this->getToken(),
                                'business_account_id' => $this->getAccountID(),
                            ],
                            'data' => [],
                            'message' => t('conversation_limit_reached'),
                        ];
                        if ($logEntry) {
                            WmActivityLog::create($logdata['log_data']);
                        }

                        return $logdata;
                    }
                }
            } catch (\Exception $e) {
                whatsapp_log('Campaign: Error checking conversation limit', 'error', [
                    'to' => $to,
                    'identifier' => $identifierForCheck,
                    'error' => $e->getMessage(),
                ], $e, $tenant_id);
            }
        }

        // BUILD TEMPLATE COMPONENTS
        $rel_type = $template_data['rel_type'];
        $header_data = [];

        if ($template_data['header_data_format'] == 'TEXT') {
            $header_data = parseText($rel_type, 'header', $template_data, 'array');
        }
        $body_data = parseText($rel_type, 'body', $template_data, 'array');
        $buttons_data = parseText($rel_type, 'footer', $template_data, 'array');

        $component_header = $component_body = $component_buttons = [];
        $file_link = asset('storage/' . $template_data['filename']);

        $template_buttons_data = json_decode($template_data['buttons_data']);
        $is_flow = false;
        if (!empty($template_buttons_data)) {
            $button_types = array_column($template_buttons_data, 'type');
            $is_flow = in_array('FLOW', $button_types);
        }

        $component_header = $this->buildHeaderComponent($template_data, $file_link, $header_data);
        $component_body = $this->buildTextComponent($body_data);
        $component_buttons = $this->buildTextComponent($buttons_data);

        if ($is_flow) {
            $buttons = json_decode($template_data['buttons_data']);
            $flow_id = reset($buttons)->flow_id;
            $component_buttons[] = [
                'type' => 'button',
                'sub_type' => 'FLOW',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'action',
                        'action' => [
                            'flow_token' => json_encode(['flow_id' => $flow_id, 'rel_data' => $template_data['flow_action_data'] ?? []]),
                        ],
                    ],
                ],
            ];
        }

        $whatsapp_cloud_api = $this->loadConfig($fromNumber);

        try {
            $components = new Component($component_header, $component_body, $component_buttons);
            $result = $whatsapp_cloud_api->sendTemplate($to, $template_data['template_name'], $template_data['language'], $components);
            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
            $responseData = json_encode($result->decodedBody());
            $rawData = json_encode($result->request()->body());

            // TRACK CONVERSATION AFTER SUCCESSFUL SEND
            if ($status && $conversationTrackingNeeded && $identifierForTracking) {
                try {
                    $featureService = app(\App\Services\FeatureService::class);
                    $tenant_subdomain = tenant_subdomain_by_tenant_id($tenant_id);

                    $tracked = $featureService->trackNewConversation(
                        $identifierForTracking,
                        $tenant_id,
                        $tenant_subdomain,
                        $template_data['rel_type'] ?? 'guest'
                    );
                } catch (\Exception $e) {
                    whatsapp_log('Campaign: Failed to track conversation after send', 'error', [
                        'to' => $to,
                        'identifier' => $identifierForTracking,
                        'error' => $e->getMessage(),
                    ], $e, $tenant_id);
                }
            }
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $errorData = $th->responseData()['error'] ?? [];
            $message = $errorData['error_user_msg'] ?? $errorData['message'] ?? $th->rawResponse() ?? $th->getMessage() ?? 'Failed to send template message.';
            $responseCode = $th->httpStatusCode();
            $responseData = json_encode($message);
            $rawData = json_encode([]);

            whatsapp_log('Error sending template: ' . $message, 'error', [
                'to' => $to,
                'template_name' => $template_data['template_name'],
                'language' => $template_data['language'],
                'response_code' => $responseCode,
                'response_data' => $responseData,
                'raw_data' => $rawData,
                'tenant_id' => $tenant_id,
            ], $th, $tenant_id);
        }

        $log_data = [
            'response_code' => $responseCode,
            'category' => $type,
            'category_id' => $template_data['campaign_id'] ?? $template_data['template_bot_id'] ?? 0,
            'rel_type' => $rel_type,
            'rel_id' => $template_data['rel_id'],
            'category_params' => json_encode(['templateId' => $template_data['template_id'], 'message' => $message ?? '']),
            'response_data' => $responseData,
            'raw_data' => $rawData,
            'phone_number_id' => $this->getPhoneID(),
            'access_token' => $this->getToken(),
            'business_account_id' => $this->getAccountID(),
            'tenant_id' => $tenant_id,
        ];

        // Create activity log
        if ($logEntry) {
            WmActivityLog::create($log_data);
        }

        return ['status' => $status, 'log_data' => $log_data, 'data' => $data ?? [], 'message' => $message ?? ''];
    }

    /**
     * Send a message using the WhatsApp Cloud API
     *
     * @param  string  $to  Recipient phone number
     * @param  array  $message_data  Data for the message
     * @param  string|null  $fromNumber  Optional sender phone number
     * @return array Response containing status, log data, and any response data or error message
     */
    public function sendMessage($to, $message_data, $fromNumber = null, $folder = 'bot_files')
    {
        $tenant_id = $this->getWaTenantId();
        $message_data = parseMessageText($message_data);
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);

        // try {
        $rows = [];
        if (!empty($message_data['button1_id'])) {
            $rows[] = new Button($message_data['button1_id'], $message_data['button1']);
        }
        if (!empty($message_data['button2_id'])) {
            $rows[] = new Button($message_data['button2_id'], $message_data['button2']);
        }
        if (!empty($message_data['button3_id'])) {
            $rows[] = new Button($message_data['button3_id'], $message_data['button3']);
        }
        if (!empty($rows)) {
            $action = new ButtonAction($rows);
            $result = $whatsapp_cloud_api->sendButton(
                $to,
                $message_data['reply_text'],
                $action,
                $message_data['bot_header'],
                $message_data['bot_footer']
            );
        } elseif (!empty($message_data['button_name']) && !empty($message_data['button_url']) && filter_var($message_data['button_url'], \FILTER_VALIDATE_URL)) {
            $header = null;
            if (!empty($message_data['bot_header'])) {
                $header = new TitleHeader($message_data['bot_header']);
            }

            $result = $whatsapp_cloud_api->sendCtaUrl(
                $to,
                $message_data['button_name'],
                $message_data['button_url'],
                $header,
                $message_data['reply_text'],
                $message_data['bot_footer'],
            );
        } else {
            $message = $message_data['bot_header'] . "\n" . $message_data['reply_text'] . "\n" . $message_data['bot_footer'];
            if (!empty($message_data['filename'])) {
                $url = asset('storage/' . $message_data['filename']);
                $link_id = new LinkID($url);
                $fileExtensions = get_meta_allowed_extension();
                $extension = strtolower(pathinfo($message_data['filename'], PATHINFO_EXTENSION));
                $fileType = array_key_first(array_filter($fileExtensions, fn($data) => in_array('.' . $extension, explode(', ', $data['extension']))));
                if ($fileType == 'image') {
                    $result = $whatsapp_cloud_api->sendImage($to, $link_id, $message);
                } elseif ($fileType == 'video') {
                    $result = $whatsapp_cloud_api->sendVideo($to, $link_id, $message);
                } elseif ($fileType == 'document') {
                    $result = $whatsapp_cloud_api->sendDocument($to, $link_id, $message_data['filename'], $message);
                }
            } else {
                $result = $whatsapp_cloud_api->sendTextMessage($to, $message, true);
            }
        }

        $status = true;
        $data = json_decode($result->body());
        $responseCode = $result->httpStatusCode();
        $responseData = $data;
        $rawData = json_encode($result->request()->body());
        // } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
        //     $status = false;
        //     $errorData = $th->responseData()['error'] ?? [];
        //     $message = $errorData['error_user_msg'] ?? $errorData['message'] ?? $th->rawResponse() ?? $th->getMessage() ?? 'Failed to send message.';
        //     $responseCode = $th->httpStatusCode();
        //     $responseData = $message;
        //     $rawData = json_encode([]);

        //     whatsapp_log('Error sending message: '.$message, 'error', [
        //         'to' => $to,
        //         'message_type' => $folder,
        //         'response_code' => $responseCode,
        //         'tenant_id' => $tenant_id,
        //     ], $th, $tenant_id);
        // }

        $log_data = [
            'response_code' => $responseCode ?? 500,
            'category' => $folder == 'bot_files' ? 'message_bot' : '',
            'category_id' => $message_data['id'] ?? 0,
            'rel_type' => $message_data['rel_type'] ?? '',
            'rel_id' => $message_data['rel_id'] ?? '',
            'category_params' => json_encode(['message' => $message ?? '']),
            'response_data' => !empty($responseData) ? json_encode($responseData) : '',
            'raw_data' => $rawData,
            'phone_number_id' => $this->getPhoneID(),
            'access_token' => $this->getToken(),
            'business_account_id' => $this->getAccountID(),
            'tenant_id' => $tenant_id,
        ];

        // Create activity log
        WmActivityLog::create($log_data);

        // Track conversation usage if this is a new conversation
        // Only track if message was sent successfully and we have contact/chat info
        if ($status && !empty($message_data['rel_type'])) {
            try {
                $featureService = app(\App\Services\FeatureService::class);
                $tenant_subdomain = tenant_subdomain_by_tenant_id($tenant_id);

                // Determine identifier for tracking
                $identifierForTracking = null;
                $relType = $message_data['rel_type'];

                if ($relType === 'guest') {
                    // For guests, we need chat_id - try to find it by phone number
                    try {
                        $chat = \App\Models\Tenant\Chat::fromTenant($tenant_subdomain)
                            ->where('receiver_id', $to)
                            ->where('type', 'guest')
                            ->where('tenant_id', $tenant_id)
                            ->orderBy('id', 'desc')
                            ->first();

                        if ($chat) {
                            $identifierForTracking = $chat->id;
                        } else {
                            // If no chat found, use phone number as fallback
                            $identifierForTracking = $to;
                        }
                    } catch (\Exception $e) {
                        // Fallback to phone number if chat lookup fails
                        $identifierForTracking = $to;
                    }
                } elseif (!empty($message_data['rel_id'])) {
                    // For contacts/leads, use contact ID
                    $identifierForTracking = $message_data['rel_id'];
                }

                if ($identifierForTracking) {
                    // Check if we should track (new conversation)
                    $shouldTrack = $this->shouldTrackNewConversation(
                        $identifierForTracking,
                        $relType,
                        $tenant_id,
                        $tenant_subdomain
                    );

                    if ($shouldTrack) {
                        $tracked = $featureService->trackNewConversation(
                            $identifierForTracking,
                            $tenant_id,
                            $tenant_subdomain,
                            $relType
                        );

                        $this->logFlowDebug('Conversation Usage Tracked', [
                            'identifier' => $identifierForTracking,
                            'rel_type' => $relType,
                            'rel_id' => $message_data['rel_id'] ?? 'N/A',
                            'to' => $to,
                            'tracked' => $tracked,
                        ]);
                    } else {
                        $this->logFlowDebug('Conversation Not Tracked - Active Session Exists', [
                            'identifier' => $identifierForTracking,
                            'rel_type' => $relType,
                            'to' => $to,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Don't fail message send if tracking fails, just log it
                whatsapp_log('Failed to track conversation after sendMessage', 'error', [
                    'to' => $to,
                    'rel_type' => $message_data['rel_type'] ?? 'N/A',
                    'rel_id' => $message_data['rel_id'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ], $e, $tenant_id);
            }
        }

        return ['status' => $status, 'log_data' => $log_data, 'data' => $data ?? [], 'message' => $message ?? '', 'responseCode' => $responseCode ?? 200];
    }

    /**
     * Send bulk campaign to WhatsApp recipients
     *
     * @param  string  $to  Recipient phone number
     * @param  array  $templateData  Template configuration
     * @param  array  $campaign  Campaign data
     * @param  string|null  $fromNumber  Sender phone number (optional)
     * @return array Response data
     */
    public function sendBulkCampaign($to, $templateData, $campaign, $fromNumber = null)
    {
        $tenant_id = $this->getWaTenantId();

        try {
            // Parse template data for header, body, and buttons
            $headerData = [];
            if ($templateData['header_data_format'] == 'TEXT') {
                $headerData = parseCsvText('header', $templateData, $campaign);
            }

            $bodyData = parseCsvText('body', $templateData, $campaign);
            $buttonsData = parseCsvText('footer', $templateData, $campaign);

            // Get file link if available
            $fileLink = ($templateData['filename']) ? asset('storage/' . $templateData['filelink']) : '';

            // Build components for WhatsApp message
            $componentHeader = $this->buildHeaderComponent($templateData, $fileLink, $headerData);
            $componentBody = $this->buildTextComponent($bodyData);
            $componentButtons = $this->buildTextComponent($buttonsData);

            // Load WhatsApp API configuration
            $whatsappCloudApi = $this->loadConfig($fromNumber);

            // Create components object and send template
            $components = new Component($componentHeader, $componentBody, $componentButtons);
            $result = $whatsappCloudApi->sendTemplate(
                $to,
                $templateData['template_name'],
                $templateData['language'],
                $components
            );

            return [
                'status' => true,
                'data' => json_decode($result->body(), true),
                'responseCode' => $result->httpStatusCode(),
                'message' => '',
                'phone' => $to,
                'tenant_id' => $tenant_id,
            ];
        } catch (ResponseException $e) {

            whatsapp_log('WhatsApp API Error: ' . $e->getMessage(), 'error', [
                'phone' => $to,
                'template' => $templateData['template_name'],
                'response_code' => $e->httpStatusCode(),
                'response_data' => $e->responseData() ?? [],
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'data' => [],
                'responseCode' => $e->httpStatusCode(),
                'message' => $e->responseData()['error']['error_user_msg'] ?? $e->responseData()['error']['message'] ?? $e->getMessage(),
                'phone' => $to,
                'tenant_id' => $tenant_id,
            ];
        } catch (\Exception $e) {

            whatsapp_log('WhatsApp Campaign Error: ' . $e->getMessage(), 'error', [
                'phone' => $to,
                'template' => $templateData['template_name'] ?? 'unknown',
                'response_code' => 500,
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'data' => [],
                'responseCode' => 500,
                'message' => $e->getMessage(),
                'phone' => $to,
                'tenant_id' => $tenant_id,
            ];
        }
    }

    /**
     * Retry sending a campaign message with exponential backoff
     *
     * @param  string  $to  Recipient phone number
     * @param  array  $templateData  Template configuration
     * @param  array  $campaign  Campaign data
     * @param  string|null  $fromNumber  Sender phone number (optional)
     * @param  int  $maxRetries  Maximum number of retry attempts
     * @return array Response data
     */
    public function sendWithRetry($to, $templateData, $campaign, $fromNumber = null, $maxRetries = 3)
    {
        $tenant_id = $this->getWaTenantId();
        $attempt = 0;
        $result = null;

        while ($attempt < $maxRetries) {
            $result = $this->sendBulkCampaign($to, $templateData, $campaign, $fromNumber);

            // If successful or not a retryable error, break the loop
            if ($result['status'] || !$this->isRetryableError($result['responseCode'])) {
                break;
            }

            // Exponential backoff: wait longer between each retry
            $waitTime = pow(2, $attempt) * 1000000; // in microseconds (1s, 2s, 4s)
            usleep($waitTime);
            $attempt++;
        }

        return $result;
    }

    /**
     * Check if an error is retryable
     *
     * @param  int  $statusCode  HTTP status code
     * @return bool Whether the error is retryable
     */
    protected function isRetryableError($statusCode)
    {
        // Retry on rate limiting, server errors, and certain client errors
        return in_array($statusCode, [408, 429, 500, 502, 503, 504]);
    }

    /**
     * Handle batch processing for large campaigns
     *
     * @param  array  $recipients  List of recipients
     * @param  array  $templateData  Template configuration
     * @param  int  $batchSize  Batch size (default: 50)
     * @return array Results for each recipient
     */
    public function processBatchCampaign($recipients, $templateData, $batchSize = 50)
    {
        $tenant_id = $this->getWaTenantId();
        $results = [];
        $batches = array_chunk($recipients, $batchSize);

        foreach ($batches as $batch) {
            foreach ($batch as $recipient) {
                $to = $recipient['phone'];
                $result = $this->sendBulkCampaign($to, $templateData, $recipient);
                $results[] = $result;
            }

            // Reduced delay for better performance while still avoiding rate limiting
            if (count($batches) > 1) {
                usleep(100000); // 0.1 second delay instead of 0.5
            }
        }

        return $results;
    }

    private function buildHeaderComponent($templateData, $fileLink, $headerData)
    {
        return match ($templateData['header_data_format']) {
            'IMAGE' => [['type' => 'image', 'image' => ['link' => $fileLink]]],
            'DOCUMENT' => [['type' => 'document', 'document' => ['link' => $fileLink, 'filename' => 'file_' . uniqid() . '.' . pathinfo($templateData['filename'], PATHINFO_EXTENSION)]]],
            'VIDEO' => [['type' => 'video', 'video' => ['link' => $fileLink]]],
            default => collect($headerData)->map(fn($header) => ['type' => 'text', 'text' => $header])->toArray(),
        };
    }

    private function buildTextComponent($data)
    {
        return collect($data)->map(fn($text) => ['type' => 'text', 'text' => $text])->toArray();
    }

    /**
     * Retrieve a URL for a media file using its media ID
     *
     * @param  string  $media_id  Media ID to retrieve the URL for
     * @return string|null Filename of the saved media file or null on failure
     */
    public function retrieveUrl($media_id)
    {
        $tenant_id = $this->getWaTenantId();
        $url = self::$facebookAPI . $media_id;
        $accessToken = $this->getToken();

        $response = Http::withToken($accessToken)->get($url);

        if ($response->successful()) {
            $responseData = $response->json();

            if (isset($responseData['url'])) {
                $mediaUrl = $responseData['url'];
                $mediaData = Http::withToken($accessToken)->get($mediaUrl);

                if ($mediaData->successful()) {
                    $imageContent = $mediaData->body();
                    $contentType = $mediaData->header('Content-Type');

                    $extensionMap = self::$extensionMap;
                    $extension = $extensionMap[$contentType] ?? 'unknown';
                    $filename = 'media_' . uniqid() . '.' . $extension;
                    $storagePath = 'whatsapp-attachments/' . $filename;

                    Storage::disk('public')->put($storagePath, $imageContent);

                    return $filename;
                }
            }
        }

        whatsapp_log('Failed to retrieve media URL', 'error', [
            'media_id' => $media_id,
            'tenant_id' => $tenant_id,
        ], null, $tenant_id);

        return null;
    }

    /**
     * Send a flow message based on node type
     *
     * @param  string  $to  Recipient phone number
     * @param  array  $nodeData  Node data from flow
     * @param  string  $nodeType  Type of node
     * @param  string  $phoneNumberId  WhatsApp phone number ID
     * @param  array  $contactData  Contact information
     * @param  array  $context  Flow context information
     * @return array Response data
     */
    public function sendFlowMessage($to, $nodeData, $nodeType, $phoneNumberId, $contactData, $context = [])
    {
        $this->logFlowDebug('Flow Execution Started', [
            'node_type' => $nodeType,
            'to' => $to,
            'phone_number_id' => $phoneNumberId,
            'contact_id' => $contactData->id ?? 'N/A',
            'node_data_keys' => array_keys($nodeData)
        ]);

        switch ($nodeType) {
            case 'textMessage':
                return $this->sendFlowTextMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'buttonMessage':
                return $this->sendFlowButtonMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'callToAction':
                return $this->sendFlowCTAMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'templateMessage':
                return $this->sendFlowTemplateMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'mediaMessage':
                return $this->sendFlowMediaMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'listMessage':
                return $this->sendFlowListMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'locationMessage':
                return $this->sendFlowLocationMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'contactMessage':
                return $this->sendFlowContactMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'aiAssistant':
                return $this->sendFlowAiMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            case 'webhookApi':
                return $this->sendFlowWebhookApi($nodeData, $phoneNumberId, $contactData, $context);

            case 'condition':
                return $this->processFlowCondition($nodeData, $phoneNumberId, $contactData, $context);

            case 'delay':
                return $this->processFlowDelay($nodeData, $phoneNumberId, $contactData, $context);

            case 'inputCollection':
                return $this->processFlowInputCollection($to, $nodeData, $phoneNumberId, $contactData, $context);


            case 'tagManagement':
                return $this->processFlowTagManagement($nodeData, $phoneNumberId, $contactData, $context);

            case 'variableManagement':
                return $this->processFlowVariableManagement($nodeData, $phoneNumberId, $contactData, $context);

            default:
                return ['status' => false, 'message' => 'Unsupported node type: ' . $nodeType];
        }
    }

    /**
     * Send a text message from flow
     */
    protected function sendFlowTextMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Use array functions to get reply_text from output if present
        $replyText = '';
        if (!empty($nodeData['output']) && is_array($nodeData['output'])) {
            $replyText = collect($nodeData['output'])
                ->pluck('reply_text')
                ->filter()
                ->first() ?? '';
        }
        if (empty($replyText)) {
            // Fallback to 'message' key if present
            $replyText = $nodeData['message'] ?? '';
        }
        // Replace variables in message
        $message = $this->replaceFlowVariables($replyText, $contactData);

        $messageData = [
            'rel_type' => $contactData->type ?? 'guest',
            'rel_id' => $contactData->id ?? '',
            'reply_text' => $message,
            'bot_header' => '',
            'bot_footer' => '',
            'tenant_id' => $this->wa_tenant_id,
        ];

        return $this->sendMessage($to, $messageData, $phoneNumberId);
    }

    /**
     * Send a button message from flow
     */
    protected function sendFlowButtonMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Extract button message fields from the first output element
        $output = collect($nodeData['output'] ?? [])->first() ?? [];
        $replyText = $this->replaceFlowVariables($output['reply_text'] ?? '', $contactData);
        $button1 = $output['button1'] ?? null;
        $button2 = $output['button2'] ?? null;
        $button3 = $output['button3'] ?? null;

        // Prepare buttons data with UNIQUE IDs
        $buttons = [];
        $currentNodeId = $context['current_node'] ?? uniqid();

        if ($button1) {
            $uniqueButtonId = $currentNodeId . '_btn_0';
            $buttons[] = new Button($uniqueButtonId, $button1);
        }
        if ($button2) {
            $uniqueButtonId = $currentNodeId . '_btn_1';
            $buttons[] = new Button($uniqueButtonId, $button2);
        }
        if ($button3) {
            $uniqueButtonId = $currentNodeId . '_btn_2';
            $buttons[] = new Button($uniqueButtonId, $button3);
        }

        if (empty($buttons)) {
            return [
                'status' => false,
                'message' => 'No buttons defined for button message',
            ];
        }

        try {
            $whatsapp_cloud_api = $this->loadConfig($phoneNumberId);

            // Create button action
            $buttonAction = new ButtonAction($buttons);

            // Send the button message
            $result = $whatsapp_cloud_api->sendButton(
                $to,
                $replyText,
                $buttonAction
            );

            $response = [
                'status' => true,
                'data' => json_decode($result->body()),
                'responseCode' => $result->httpStatusCode(),
                'responseData' => $result->decodedBody(),
            ];

            // Log the activity with unique button IDs
            $buttonTexts = array_filter([$button1, $button2, $button3]);
            $this->logFlowActivity($to, $response, [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'reply_text' => $replyText,
                'buttons' => $buttonTexts,
                'next_nodes' => $context['next_nodes'] ?? [],
            ], 'flow_button_message');

            return $response;
        } catch (\Throwable $e) {
            whatsapp_log('Flow button message error', 'error', [
                'error' => $e->getMessage(),
                'to' => $to,
            ], $e);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function sendFlowCTAMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Extract CTA fields from the first output element using array functions
        $output = collect($nodeData['output'] ?? [])->first() ?? [];
        $header = $this->replaceFlowVariables($output['bot_header'] ?? '', $contactData);
        $valueText = $this->replaceFlowVariables($output['reply_text'] ?? '', $contactData);
        $footer = $this->replaceFlowVariables($output['bot_footer'] ?? '', $contactData);
        $buttonText = $output['buttonText'] ?? 'Click Here';
        $buttonLink = $output['buttonLink'] ?? '';

        try {
            // Use CtaUrl message type from the library
            $whatsapp_cloud_api = $this->loadConfig($phoneNumberId);

            // Create header component if provided
            $headerComponent = null;
            if (!empty($header)) {
                $headerComponent = new TitleHeader($header);
            }

            // Send the CTA message
            $result = $whatsapp_cloud_api->sendCtaUrl(
                $to,
                $buttonText,
                $buttonLink,
                $headerComponent,
                $valueText,
                $footer
            );

            $response = [
                'status' => true,
                'data' => json_decode($result->body()),
                'responseCode' => $result->httpStatusCode(),
                'responseData' => $result->decodedBody(),
            ];

            // Log the activity
            $this->logFlowActivity($to, $response, [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'header' => $header,
                'value_text' => $valueText,
                'footer' => $footer,
                'button_text' => $buttonText,
                'button_link' => $buttonLink,
            ], 'flow_cta_message');

            return $response;
        } catch (\Throwable $e) {
            whatsapp_log('Flow CTA message error', 'error', [
                'error' => $e->getMessage(),
                'to' => $to,
            ], $e);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a template message from flow
     */
    protected function sendFlowTemplateMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        $templateId = $nodeData['templateId'] ?? '';
        $params = $nodeData['params'] ?? [];

        // Get template details from database
        $template = \App\Models\Tenant\WhatsappTemplate::where('template_id', $templateId)->first();

        if (!$template) {
            return ['status' => false, 'message' => 'Template not found'];
        }

        // Prepare template data
        $templateData = [
            'rel_type' => $contactData->type ?? 'guest',
            'rel_id' => $contactData->id ?? '',
            'template_id' => $templateId,
            'template_name' => $template->template_name,
            'language' => $template->language,
            'header_data_format' => $template->header_data_format,
            'header_data_text' => $template->header_data_text,
            'body_data' => $template->body_data,
            'footer_data' => $template->footer_data,
            'template_bot_id' => 0,
        ];

        // Add parameters
        if (!empty($params['header'])) {
            $templateData['header_params'] = json_encode($this->replaceFlowVariablesInArray($params['header'], $contactData));
        }

        if (!empty($params['body'])) {
            $templateData['body_params'] = json_encode($this->replaceFlowVariablesInArray($params['body'], $contactData));
        }

        if (!empty($params['footer'])) {
            $templateData['footer_params'] = json_encode($this->replaceFlowVariablesInArray($params['footer'], $contactData));
        }

        return $this->sendTemplate($to, $templateData, 'template_bot', $phoneNumberId);
    }

    /**
     * Send a media message from flow
     */
    protected function sendFlowMediaMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Extract media fields from the first output element using array functions
        $output = collect($nodeData['output'] ?? [])->first() ?? [];
        $mediaType = $output['media_type'] ?? 'image';
        $mediaUrl = $output['media_url'] ?? '';
        $caption = $this->replaceFlowVariables($output['media_caption'] ?? '', $contactData);
        $fileName = $output['media_filename'] ?? basename($mediaUrl);

        // Load WhatsApp Cloud API
        $whatsapp_cloud_api = $this->loadConfig($phoneNumberId);

        try {
            $result = null;
            $link = new LinkID($mediaUrl);

            switch ($mediaType) {
                case 'image':
                    $result = $whatsapp_cloud_api->sendImage($to, $link, $caption);
                    break;

                case 'video':
                    $result = $whatsapp_cloud_api->sendVideo($to, $link, $caption);
                    break;

                case 'audio':
                    $result = $whatsapp_cloud_api->sendAudio($to, $link);
                    break;

                case 'document':
                    $result = $whatsapp_cloud_api->sendDocument($to, $link, $fileName, $caption);
                    break;

                default:
                    return ['status' => false, 'message' => 'Unsupported media type'];
            }

            $response = [
                'status' => true,
                'data' => json_decode($result->body()),
                'responseCode' => $result->httpStatusCode(),
                'responseData' => $result->decodedBody(),
            ];

            // Log the activity
            $this->logFlowActivity($to, $response, [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'media_type' => $mediaType,
                'media_url' => $mediaUrl,
                'caption' => $caption,
            ], 'flow_media_message');

            return $response;
        } catch (\Throwable $e) {
            whatsapp_log('Flow media message error', 'error', [
                'error' => $e->getMessage(),
                'media_type' => $mediaType,
                'to' => $to,
            ], $e);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a list message from flow
     */
    protected function sendFlowListMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Extract list message fields from the first output element
        $output = collect($nodeData['output'] ?? [])->first() ?? [];
        $headerText = $this->replaceFlowVariables($output['bot_header'] ?? '', $contactData);
        $bodyText = $this->replaceFlowVariables($output['reply_text'] ?? '', $contactData);
        $footerText = $this->replaceFlowVariables($output['bot_footer'] ?? '', $contactData);
        $buttonText = $output['buttonText'] ?? 'View Options';
        $sections = $output['sections'] ?? [];

        if (empty($sections)) {
            return [
                'status' => false,
                'message' => 'No sections defined for list message',
            ];
        }

        // Load WhatsApp Cloud API
        $whatsapp_cloud_api = $this->loadConfig($phoneNumberId);
        $currentNodeId = $context['current_node'] ?? uniqid();

        try {
            // Format sections for WhatsApp API with unique IDs
            $formattedSections = [];
            foreach ($sections as $sectionIndex => $section) {
                $formattedSection = [
                    'title' => $section['title'] ?? 'Section ' . ($sectionIndex + 1),
                    'rows' => [],
                ];

                foreach ($section['items'] as $itemIndex => $item) {
                    // Create unique list item ID for flow navigation
                    $uniqueItemId = $currentNodeId . '_item_' . $sectionIndex . '_' . $itemIndex;

                    $formattedSection['rows'][] = [
                        'id' => $uniqueItemId, // Use unique ID instead of original item ID
                        'title' => $item['title'] ?? 'Item ' . ($itemIndex + 1),
                        'description' => $item['description'] ?? '',
                    ];
                }
                $formattedSections[] = $formattedSection;
            }

            // Create interactive list message payload
            $interactivePayload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => [
                        'text' => $bodyText,
                    ],
                    'action' => [
                        'button' => $buttonText,
                        'sections' => $formattedSections,
                    ],
                ],
            ];

            // Add header if provided
            if (!empty($headerText)) {
                $interactivePayload['interactive']['header'] = [
                    'type' => 'text',
                    'text' => $headerText,
                ];
            }

            // Add footer if provided
            if (!empty($footerText)) {
                $interactivePayload['interactive']['footer'] = [
                    'text' => $footerText,
                ];
            }

            whatsapp_log('Sending list message with unique IDs', 'debug', [
                'node_id' => $currentNodeId,
                'to' => $to,
                'sections_count' => count($formattedSections),
                'total_items' => array_sum(array_map(function ($section) {
                    return count($section['rows']);
                }, $formattedSections)),
            ]);

            // Send using raw API call
            $response = Http::withToken($this->getToken())
                ->post(self::getBaseUrl() . $this->getPhoneID() . '/messages', $interactivePayload);

            $responseData = $response->json();

            $result = [
                'status' => $response->successful(),
                'data' => (object) $responseData,
                'responseCode' => $response->status(),
                'responseData' => $responseData,
            ];

            whatsapp_log('List message sent', 'info', [
                'node_id' => $currentNodeId,
                'to' => $to,
                'response_code' => $response->status(),
                'success' => $response->successful(),
                'message_id' => $responseData['messages'][0]['id'] ?? 'unknown',
            ]);

            // Log the activity
            $this->logFlowActivity($to, $result, [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'header' => $headerText,
                'body' => $bodyText,
                'footer' => $footerText,
                'button' => $buttonText,
                'sections' => $sections,
                'unique_ids_used' => true,
                'next_nodes' => $context['next_nodes'] ?? [],
            ], 'flow_list_message');

            return $result;
        } catch (\Throwable $e) {
            whatsapp_log('Flow list message error', 'error', [
                'error' => $e->getMessage(),
                'to' => $to,
                'node_id' => $currentNodeId,
                'trace' => $e->getTraceAsString(),
            ], $e);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a location message from flow
     */
    protected function sendFlowLocationMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Extract location fields from the first output element using array functions
        $output = collect($nodeData['output'] ?? [])->first() ?? [];
        $latitude = $output['location_latitude'] ?? '';
        $longitude = $output['location_longitude'] ?? '';
        $name = $this->replaceFlowVariables($output['location_name'] ?? '', $contactData);
        $address = $this->replaceFlowVariables($output['location_address'] ?? '', $contactData);

        // In netflie/whatsapp-cloud-api v2.2, we may need to use raw API for location
        try {
            // Send using raw API call since the library might not support location directly
            $response = Http::withToken($this->getToken())
                ->post(self::getBaseUrl() . $this->getPhoneID() . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'location',
                    'location' => [
                        'latitude' => (float) $latitude,
                        'longitude' => (float) $longitude,
                        'name' => $name,
                        'address' => $address,
                    ],
                ]);

            $responseData = $response->json();

            $result = [
                'status' => $response->successful(),
                'data' => (object) $responseData,
                'responseCode' => $response->status(),
                'responseData' => $responseData,
            ];

            // Log the activity
            $this->logFlowActivity($to, $result, [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => $name,
                'address' => $address,
            ], 'flow_location_message');

            return $result;
        } catch (\Throwable $e) {
            whatsapp_log('Flow location message error', 'error', [
                'error' => $e->getMessage(),
                'to' => $to,
            ], $e);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a contact message from flow
     */
    protected function sendFlowContactMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        // Extract contacts array from the first output element using array functions
        $output = collect($nodeData['output'] ?? [])->first() ?? [];
        $contacts = $output['contacts'] ?? [];
        $processedContacts = [];

        // Process and replace variables in contact data
        foreach ($contacts as $contact) {
            $firstName = $this->replaceFlowVariables($contact['firstName'] ?? '', $contactData);
            $lastName = $this->replaceFlowVariables($contact['lastName'] ?? '', $contactData);
            $formattedName = trim($firstName . ' ' . $lastName);
            $processed = [
                'name' => [
                    'formatted_name' => $formattedName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ],
                'phones' => [
                    [
                        'phone' => $this->replaceFlowVariables($contact['phone'] ?? '', $contactData),
                        'type' => 'CELL',
                    ],
                ],
            ];

            // Add email if provided
            if (!empty($contact['email'])) {
                $processed['emails'] = [
                    [
                        'email' => $this->replaceFlowVariables($contact['email'] ?? '', $contactData),
                        'type' => 'WORK',
                    ],
                ];
            }

            // Add company info if provided
            if (!empty($contact['company'])) {
                $processed['org'] = [
                    'company' => $this->replaceFlowVariables($contact['company'] ?? '', $contactData),
                ];

                if (!empty($contact['title'])) {
                    $processed['org']['title'] = $this->replaceFlowVariables($contact['title'] ?? '', $contactData);
                }
            }

            $processedContacts[] = $processed;
        }

        // In netflie/whatsapp-cloud-api v2.2, we need to use raw API for contacts
        try {
            // Send using raw API call
            $response = Http::withToken($this->getToken())
                ->post(self::getBaseUrl() . $this->getPhoneID() . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'contacts',
                    'contacts' => $processedContacts,
                ]);

            $responseData = $response->json();

            $result = [
                'status' => $response->successful(),
                'data' => (object) $responseData,
                'responseCode' => $response->status(),
                'responseData' => $responseData,
            ];

            // Log the activity
            $this->logFlowActivity($to, $result, [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'contacts' => $processedContacts,
            ], 'flow_contact_message');

            return $result;
        } catch (\Throwable $e) {
            whatsapp_log('Flow contact message error', 'error', [
                'error' => $e->getMessage(),
                'to' => $to,
            ], $e);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an AI-generated message from flow
     */
    protected function sendFlowAiMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        $assistantMode = $nodeData['assistantMode'] ?? 'custom';
        $hasSelectedAssistant = isset($nodeData['selectedAssistantId']) && !empty($nodeData['selectedAssistantId']);

        $this->logFlowDebug('AI Assistant Node - sendFlowAiMessage Called', [
            'assistant_mode' => $assistantMode,
            'has_selected_assistant' => $hasSelectedAssistant,
            'selected_assistant_id' => $nodeData['selectedAssistantId'] ?? 'N/A',
            'to' => $to,
            'contact_id' => $contactData->id ?? 'N/A',
            'tenant_id' => $this->wa_tenant_id,
            'node_data_keys' => array_keys($nodeData),
            'has_output' => isset($nodeData['output']),
            'output_count' => isset($nodeData['output']) ? count($nodeData['output']) : 0,
        ]);

        whatsapp_log('AI Assistant Node Processing', 'info', [
            'mode' => $assistantMode,
            'has_selected_assistant' => $hasSelectedAssistant,
            'to' => $to,
            'contact_id' => $contactData->id ?? 'N/A',
            'tenant_id' => $this->wa_tenant_id,
        ]);

        // Prioritize Personal Assistant mode if:
        // 1. Mode is explicitly set to 'personal', OR
        // 2. A specific assistant is selected (selectedAssistantId exists)
        if ($assistantMode === 'personal' || $hasSelectedAssistant) {
            $this->logFlowDebug('AI Assistant - Routing to Personal Assistant Mode', [
                'reason' => $hasSelectedAssistant ? 'selectedAssistantId present' : 'assistantMode is personal',
                'to' => $to,
                'tenant_id' => $this->wa_tenant_id,
            ]);

            return $this->sendFlowPersonalAssistantMessage($to, $nodeData, $phoneNumberId, $contactData, $context);
        }

        // Custom AI mode (original implementation)
        $this->logFlowDebug('AI Assistant - Using Custom AI Mode', [
            'to' => $to,
            'has_prompt' => isset($nodeData['prompt']),
            'has_aiModel' => isset($nodeData['aiModel']),
            'has_temperature' => isset($nodeData['temperature']),
            'has_maxTokens' => isset($nodeData['maxTokens']),
        ]);

        $prompt = $this->replaceFlowVariables($nodeData['prompt'] ?? '', $contactData);
        $aiModel = $nodeData['aiModel'] ?? 'gpt-3.5-turbo';
        $contextType = $nodeData['contextType'] ?? 'message';
        $temperature = $nodeData['temperature'] ?? 0.7;
        $maxTokens = $nodeData['maxTokens'] ?? 500;

        $this->logFlowDebug('AI Assistant - Custom Mode Configuration', [
            'prompt_length' => strlen($prompt),
            'ai_model' => $aiModel,
            'context_type' => $contextType,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        // Check if custom mode has valid prompt, if not fallback to personal assistant
        if (empty(trim($prompt))) {
            $this->logFlowDebug('AI Assistant - Custom Mode has empty prompt, falling back to Personal Assistant', [
                'to' => $to,
            ]);

            // Try personal assistant mode as fallback
            return $this->sendFlowPersonalAssistantMessage($to, $nodeData, $phoneNumberId, $contactData, $context);
        }

        // Get AI response
        $this->logFlowDebug('AI Assistant - Calling generateFlowAiResponse', [
            'prompt' => substr($prompt, 0, 200) . '...',
            'model' => $aiModel,
        ]);

        $aiResponse = $this->generateFlowAiResponse($prompt, $aiModel, $contextType, $context, $temperature, $maxTokens);

        if (!$aiResponse) {
            $this->logFlowDebug('AI Assistant - Custom Mode Response Generation FAILED, falling back to Personal Assistant', [
                'mode' => 'custom',
                'to' => $to,
            ]);

            // Try personal assistant mode as fallback
            $fallbackResult = $this->sendFlowPersonalAssistantMessage($to, $nodeData, $phoneNumberId, $contactData, $context);

            // If fallback also fails, send error message to user
            if (!$fallbackResult || !($fallbackResult['status'] ?? false)) {
                $this->logFlowDebug('AI Assistant - Both Custom and Personal Assistant modes failed, sending error message', [
                    'to' => $to,
                ]);

                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => 'Sorry, I encountered an error processing your request. Please try again later.',
                ];

                return $this->sendMessage($to, $messageData, $phoneNumberId);
            }

            return $fallbackResult;
        }

        $this->logFlowDebug('AI Assistant - Response Generated Successfully', [
            'response_length' => strlen($aiResponse),
            'response_preview' => substr($aiResponse, 0, 100) . '...',
        ]);

        $messageData = [
            'rel_type' => $contactData->type ?? 'guest',
            'rel_id' => $contactData->id ?? '',
            'reply_text' => $aiResponse,
            'bot_header' => '',
            'bot_footer' => '',
        ];

        $this->logFlowDebug('AI Assistant - Sending Message to User', [
            'to' => $to,
            'message_length' => strlen($aiResponse),
        ]);

        $result = $this->sendMessage($to, $messageData, $phoneNumberId);

        // Store AI response in result for chat history display
        $result['ai_response'] = $aiResponse;

        $this->logFlowDebug('AI Assistant - Message Send Result', [
            'status' => $result['status'] ?? 'unknown',
            'response_code' => $result['responseCode'] ?? 'unknown',
            'ai_response_stored' => !empty($result['ai_response']),
        ]);

        return $result;
    }

    /**
     * Send a Personal Assistant message from flow
     */
    protected function sendFlowPersonalAssistantMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        $logFile = storage_path('logs/aipersonaldebug.log');
        $timestamp = now()->format('Y-m-d H:i:s');

        $this->logToAiFile($logFile, "================================================================================");
        $this->logToAiFile($logFile, "[$timestamp] FLOW AI ASSISTANT NODE - PROCESSING START");
        $this->logToAiFile($logFile, "================================================================================");
        $this->logToAiFile($logFile, "TO: " . $to);
        $this->logToAiFile($logFile, "NODE DATA: " . json_encode($nodeData));

        try {
            // Get tenant ID first (needed for security checks)
            $tenantId = $this->wa_tenant_id ?? tenant_id();

            $this->logToAiFile($logFile, "TENANT ID: " . ($tenantId ?? 'N/A'));

            // ✅ CORRECT: Get selected assistant ID from node data
            $selectedAssistantId = $nodeData['selectedAssistantId'] ?? null;

            $this->logToAiFile($logFile, "SELECTED ASSISTANT ID: " . ($selectedAssistantId ?? 'NULL'));

            if (!$selectedAssistantId) {
                $this->logToAiFile($logFile, "ERROR: No assistant selected in node");

                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => 'AI Assistant not configured. Please select an assistant in the flow settings.',
                ];

                return $this->sendMessage($to, $messageData, $phoneNumberId);
            }

            // ✅ SECURITY: Get the SPECIFIC assistant by ID with tenant verification
            // This prevents cross-tenant access even if someone tries to use another tenant's assistant ID
            $assistant = \App\Models\PersonalAssistant::findForTenant($selectedAssistantId, $tenantId);

            if (!$assistant) {
                $this->logToAiFile($logFile, "ERROR: Assistant not found or doesn't belong to tenant");
                $this->logToAiFile($logFile, "  - Assistant ID: $selectedAssistantId");
                $this->logToAiFile($logFile, "  - Tenant ID: $tenantId");

                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => 'AI Assistant not found. Please reconfigure the flow.',
                ];

                return $this->sendMessage($to, $messageData, $phoneNumberId);
            }

            $this->logToAiFile($logFile, "ASSISTANT FOUND:");
            $this->logToAiFile($logFile, "  - ID: " . $assistant->id);
            $this->logToAiFile($logFile, "  - Name: " . $assistant->name);
            $this->logToAiFile($logFile, "  - Model: " . $assistant->model);
            $this->logToAiFile($logFile, "  - Is Active: " . ($assistant->is_active ? 'Yes' : 'No'));

            if (!$assistant->is_active) {
                $this->logToAiFile($logFile, "ERROR: Assistant is disabled");

                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => 'AI Assistant is currently disabled. Please try again later.',
                ];

                return $this->sendMessage($to, $messageData, $phoneNumberId);
            }

            // Get the trigger message from context
            $userMessage = $context['trigger_message'] ?? 'Hello';

            $this->logToAiFile($logFile, "USER MESSAGE FROM CONTEXT: " . $userMessage);

            // Get contact information for thread persistence
            $contactId = $contactData->id ?? null;
            $contactPhone = $to;
            // $tenantId already defined at the top of try block

            $this->logToAiFile($logFile, "CONTACT INFO:");
            $this->logToAiFile($logFile, "  - Contact ID: " . ($contactId ?? 'N/A'));
            $this->logToAiFile($logFile, "  - Contact Phone: " . $contactPhone);
            $this->logToAiFile($logFile, "  - Tenant ID: " . ($tenantId ?? 'N/A'));

            // Build conversation history if available
            $conversationHistory = [];
            if (!empty($context['conversation_history'])) {
                $conversationHistory = $context['conversation_history'];
                $this->logToAiFile($logFile, "CONVERSATION HISTORY: " . count($conversationHistory) . " messages");
            }

            // Log to flow debug - Message sent to AI
            $this->logFlowDebug('AI Assistant - Message Sent to AI', [
                'user_message' => $userMessage,
                'message_length' => strlen($userMessage),
                'assistant_id' => $assistant->id,
                'assistant_name' => $assistant->name,
                'assistant_model' => $assistant->model,
                'has_conversation_history' => !empty($conversationHistory),
                'history_count' => count($conversationHistory),
                'using_openai_assistant' => !empty($assistant->openai_assistant_id),
                'openai_assistant_id' => $assistant->openai_assistant_id ?? 'N/A',
                'vector_store_id' => $assistant->openai_vector_store_id ?? 'N/A',
            ]);

            $this->logToAiFile($logFile, "CALLING personalAssistantResponse()...");
            $this->logToAiFile($logFile, "  - Passing selected assistant (ID: " . $assistant->id . ")");

            $this->logFlowDebug('AI Assistant - Calling AI Service', [
                'method' => 'personalAssistantResponse',
                'assistant_id' => $assistant->id,
                'user_message' => $userMessage,
                'message_length' => strlen($userMessage),
            ]);

            // Use the Ai trait method to get response with the selected assistant
            // Pass contact info for thread persistence
            $aiResult = null;
            $aiResponseText = '';

            try {
                $this->logFlowDebug('AI Assistant - Starting AI Call', [
                    'timestamp' => now()->format('H:i:s.u'),
                    'contact_id' => $contactId,
                    'contact_phone' => $contactPhone,
                ]);

                $aiResult = $this->personalAssistantResponse(
                    $userMessage,
                    $conversationHistory,
                    $assistant,
                    $contactId,
                    $contactPhone,
                    $tenantId
                );

                $this->logFlowDebug('AI Assistant - AI Call Completed', [
                    'timestamp' => now()->format('H:i:s.u'),
                    'has_result' => !is_null($aiResult),
                    'result_keys' => is_array($aiResult) ? array_keys($aiResult) : [],
                ]);

                $this->logToAiFile($logFile, "personalAssistantResponse() RETURNED:");
                $this->logToAiFile($logFile, "  - Status: " . ($aiResult['status'] ?? 'UNKNOWN'));
                $this->logToAiFile($logFile, "  - Has Message: " . (isset($aiResult['message']) ? 'Yes' : 'No'));
                $this->logToAiFile($logFile, "  - Message Length: " . strlen($aiResult['message'] ?? ''));

                // Log AI response to flow debug
                $aiResponseText = $aiResult['message'] ?? '';

                // Store AI response in context for chat history display
                $context['ai_response'] = $aiResponseText;

                $this->logFlowDebug('AI Assistant - AI Response Received', [
                    'status' => ($aiResult['status'] ?? false) ? 'SUCCESS' : 'FAILED',
                    'response_length' => strlen($aiResponseText),
                    'response_preview' => substr($aiResponseText, 0, 200) . (strlen($aiResponseText) > 200 ? '...' : ''),
                    'full_response' => $aiResponseText,
                    'tokens_used' => $aiResult['tokens_used'] ?? 'N/A',
                    'model_used' => $aiResult['model_used'] ?? $assistant->model ?? 'N/A',
                    'assistant_name' => $aiResult['assistant_name'] ?? $assistant->name ?? 'N/A',
                ]);

            } catch (\Throwable $e) {
                $this->logFlowDebug('AI Assistant - Exception Getting Response', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => substr($e->getTraceAsString(), 0, 500),
                ]);

                $this->logToAiFile($logFile, "EXCEPTION in personalAssistantResponse:");
                $this->logToAiFile($logFile, "  - Error: " . $e->getMessage());
                $this->logToAiFile($logFile, "  - File: " . $e->getFile());
                $this->logToAiFile($logFile, "  - Line: " . $e->getLine());

                throw $e;
            }

            if (!$aiResult || !($aiResult['status'] ?? false)) {
                $errorMessage = $aiResult['message'] ?? 'Unknown error';
                $this->logToAiFile($logFile, "ERROR: " . $errorMessage);

                $this->logFlowDebug('AI Assistant - Response Generation Failed', [
                    'error' => $errorMessage,
                    'user_message' => $userMessage,
                ]);

                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => 'Sorry, I encountered an error. Please try again.',
                ];

                return $this->sendMessage($to, $messageData, $phoneNumberId);
            }

            // Log sending message to user
            $this->logFlowDebug('AI Assistant - Sending Response to User', [
                'to' => $to,
                'response_length' => strlen($aiResponseText),
                'contact_id' => $contactData->id ?? 'N/A',
            ]);

            // Send the AI response
            $messageData = [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'reply_text' => $aiResponseText,
                'bot_header' => '',
                'bot_footer' => '',
                'tenant_id' => $this->wa_tenant_id ?? tenant_id() ?? null,
            ];

            $this->logToAiFile($logFile, "SENDING MESSAGE TO USER:");
            $this->logToAiFile($logFile, "  - To: " . $to);
            $this->logToAiFile($logFile, "  - Message: " . $aiResponseText);
            $this->logToAiFile($logFile, "  - Phone Number ID: " . $phoneNumberId);
            $this->logToAiFile($logFile, "  - Contact Type: " . ($contactData->type ?? 'N/A'));
            $this->logToAiFile($logFile, "  - Contact ID: " . ($contactData->id ?? 'N/A'));

            try {
                $this->logFlowDebug('AI Assistant - Calling sendMessage', [
                    'to' => $to,
                    'phone_number_id' => $phoneNumberId,
                    'message_data' => $messageData,
                ]);

                $sendResult = $this->sendMessage($to, $messageData, $phoneNumberId);

                $this->logToAiFile($logFile, "MESSAGE SEND RESULT:");
                $this->logToAiFile($logFile, "  - Status: " . ($sendResult['status'] ?? 'unknown'));
                $this->logToAiFile($logFile, "  - Response Code: " . ($sendResult['responseCode'] ?? 'unknown'));
                $this->logToAiFile($logFile, "  - Full Result: " . json_encode($sendResult));

                // Store AI response in result for chat history display
                $sendResult['ai_response'] = $aiResponseText;

                // Log final result to flow debug
                $this->logFlowDebug('AI Assistant - Message Send Result', [
                    'status' => $sendResult['status'] ?? 'UNKNOWN',
                    'response_code' => $sendResult['responseCode'] ?? 'UNKNOWN',
                    'message_sent' => ($sendResult['status'] ?? false) ? 'Yes' : 'No',
                    'to' => $to,
                    'ai_response_stored' => !empty($sendResult['ai_response']),
                    'full_result' => $sendResult,
                ]);

            } catch (\Throwable $sendException) {
                $this->logFlowDebug('AI Assistant - Exception Sending Message', [
                    'error' => $sendException->getMessage(),
                    'file' => $sendException->getFile(),
                    'line' => $sendException->getLine(),
                    'trace' => substr($sendException->getTraceAsString(), 0, 500),
                ]);

                $this->logToAiFile($logFile, "EXCEPTION SENDING MESSAGE:");
                $this->logToAiFile($logFile, "  - Error: " . $sendException->getMessage());
                $this->logToAiFile($logFile, "  - File: " . $sendException->getFile());
                $this->logToAiFile($logFile, "  - Line: " . $sendException->getLine());

                throw $sendException;
            }

            $this->logToAiFile($logFile, "[$timestamp] FLOW AI ASSISTANT NODE - END (SUCCESS)");
            $this->logToAiFile($logFile, "================================================================================\n");

            return $sendResult;

        } catch (\Throwable $e) {
            $this->logToAiFile($logFile, "EXCEPTION OCCURRED:");
            $this->logToAiFile($logFile, "  - Error: " . $e->getMessage());
            $this->logToAiFile($logFile, "[$timestamp] FLOW AI ASSISTANT NODE - END (EXCEPTION)");
            $this->logToAiFile($logFile, "================================================================================\n");

            $messageData = [
                'rel_type' => $contactData->type ?? 'guest',
                'rel_id' => $contactData->id ?? '',
                'reply_text' => 'Sorry, I encountered an unexpected error. Please try again later.',
            ];

            return $this->sendMessage($to, $messageData, $phoneNumberId);
        }
    }

    /**
     * Log to AI debug file
     */
    private function logToAiFile($filePath, $message)
    {
        try {
            // Ensure directory exists
            $directory = dirname($filePath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($filePath, $message . "\n", FILE_APPEND);
        } catch (\Exception $e) {
            // Silently fail if logging fails
        }
    }

    /**
     * Generate AI response for flow context
     */
    protected function generateFlowAiResponse($prompt, $aiModel, $contextType, $context, $temperature = 0.7, $maxTokens = 500)
    {
        try {
            // Check if AI is enabled
            $aiEnabled = get_tenant_setting_from_db('whats-mark', 'enable_openai_in_chat');
            if (!$aiEnabled) {
                whatsapp_log('AI not enabled for tenant', 'warning', [
                    'tenant_id' => $this->wa_tenant_id,
                ]);
                return false;
            }

            // Get AI configuration
            $openAiKey = get_tenant_setting_from_db('whats-mark', 'openai_secret_key');
            $configuredModel = get_tenant_setting_from_db('whats-mark', 'chat_model') ?: 'gpt-3.5-turbo';

            if (empty($openAiKey)) {
                whatsapp_log('OpenAI API key not configured', 'error', [
                    'tenant_id' => $this->wa_tenant_id,
                ]);
                return false;
            }

            // Use the configured model or the one specified in the node
            $model = $aiModel ?: $configuredModel;

            // Prepare the conversation context
            $messages = [];

            // Add system prompt
            if (!empty($prompt)) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $prompt
                ];
            }

            // Add context based on context type
            switch ($contextType) {
                case 'conversation':
                    // Get recent conversation history (implement as needed)
                    $conversationHistory = $this->getRecentConversationHistory($context);
                    foreach ($conversationHistory as $msg) {
                        $messages[] = $msg;
                    }
                    break;

                case 'flow':
                    // Add flow context information
                    $flowContext = $this->buildFlowContext($context);
                    if (!empty($flowContext)) {
                        $messages[] = [
                            'role' => 'system',
                            'content' => "Flow context: " . $flowContext
                        ];
                    }
                    break;

                case 'message':
                default:
                    // Add the trigger message if available
                    if (!empty($context['trigger_message'])) {
                        $messages[] = [
                            'role' => 'user',
                            'content' => $context['trigger_message']
                        ];
                    }
                    break;
            }

            // If no user message in context, add a generic prompt
            if (empty($context['trigger_message'])) {
                $messages[] = [
                    'role' => 'user',
                    'content' => 'Please provide assistance based on the system prompt.'
                ];
            }

            // Initialize OpenAI client
            $config = new \LLPhant\OpenAIConfig();
            $config->apiKey = $openAiKey;
            $config->model = $model;
            $config->temperature = $temperature;
            $config->maxTokens = $maxTokens;

            $chat = new \LLPhant\Chat\OpenAIChat($config);

            // Generate response
            $response = $chat->generateChat($messages);

            whatsapp_log('AI response generated successfully', 'info', [
                'model' => $model,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'context_type' => $contextType,
                'prompt_length' => strlen($prompt),
                'response_length' => strlen($response),
                'tenant_id' => $this->wa_tenant_id,
            ]);

            return $response;

        } catch (\Throwable $e) {
            whatsapp_log('AI response generation failed', 'error', [
                'error' => $e->getMessage(),
                'model' => $aiModel,
                'context_type' => $contextType,
                'tenant_id' => $this->wa_tenant_id,
            ], $e);

            return false;
        }
    }

    /**
     * Get recent conversation history for AI context
     */
    protected function getRecentConversationHistory($context, $limit = 10)
    {
        // This is a placeholder - implement based on your message storage system
        // You might want to query your messages table here
        $messages = [];

        // Example implementation:
        // $recentMessages = Message::where('chat_id', $context['chat_id'])
        //     ->orderBy('created_at', 'desc')
        //     ->limit($limit)
        //     ->get()
        //     ->reverse();
        //
        // foreach ($recentMessages as $msg) {
        //     $messages[] = [
        //         'role' => $msg->from_user ? 'user' : 'assistant',
        //         'content' => $msg->message
        //     ];
        // }

        return $messages;
    }

    /**
     * Build flow context information for AI
     */
    protected function buildFlowContext($context)
    {
        $contextInfo = [];

        if (!empty($context['flow_id'])) {
            $contextInfo[] = "Flow ID: " . $context['flow_id'];
        }

        if (!empty($context['current_node'])) {
            $contextInfo[] = "Current Node: " . $context['current_node'];
        }

        if (!empty($context['user_variables'])) {
            $contextInfo[] = "User Variables: " . json_encode($context['user_variables']);
        }

        return implode(', ', $contextInfo);
    }

    /**
     * Replace flow variables in text with contact data
     */
    public function replaceFlowVariables($text, $contactData)
    {
        if (empty($text)) {
            return $text;
        }
        $data['rel_type'] = $contactData['type'] ?? 'lead';
        $data['rel_id'] = $contactData['id'] ?? '';
        $data['reply_text'] = $text;
        $data['tenant_id'] = $this->wa_tenant_id;

        $data = parseMessageText($data);

        return $data['reply_text'] ?? '';
    }

    /**
     * Delete a template from Meta and local database
     */
    public function deleteTemplate(string $templateName, string $templateId): array
    {
        $tenant_id = $this->getWaTenantId();
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();

        try {
            // First, delete from Meta WhatsApp Business API
            $url = self::getBaseUrl() . "{$accountId}/message_templates";

            $response = Http::withToken($accessToken)
                ->delete($url, [
                    'name' => $templateName,
                    'hsm_id' => $templateId,
                ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to delete template from Meta';

                whatsapp_log('Meta API template deletion failed', 'error', [
                    'template_name' => $templateName,
                    'template_id' => $templateId,
                    'response_code' => $response->status(),
                    'error_message' => $errorMessage,
                    'tenant_id' => $tenant_id,
                ], null, $tenant_id);

                return [
                    'status' => false,
                    'message' => "Meta API Error: {$errorMessage}",
                    'error_code' => $response->status(),
                    'meta_deleted' => false,
                    'db_deleted' => false,
                ];
            }

            $metaResponse = $response->json();

            whatsapp_log('Template deleted from Meta successfully', 'info', [
                'template_name' => $templateName,
                'template_id' => $templateId,
                'meta_response' => $metaResponse,
                'tenant_id' => $tenant_id,
            ], null, $tenant_id);

            // If Meta deletion is successful, delete from local database
            try {
                $deleted = WhatsappTemplate::where('template_id', $templateId)
                    ->where('tenant_id', $tenant_id)
                    ->delete();

                if ($deleted) {
                    whatsapp_log('Template deleted from database successfully', 'info', [
                        'template_name' => $templateName,
                        'template_id' => $templateId,
                        'tenant_id' => $tenant_id,
                    ], null, $tenant_id);

                    return [
                        'status' => true,
                        'message' => 'Template deleted successfully from Meta and database',
                        'meta_deleted' => true,
                        'db_deleted' => true,
                        'meta_response' => $metaResponse,
                    ];
                } else {
                    whatsapp_log('Template not found in database', 'warning', [
                        'template_name' => $templateName,
                        'template_id' => $templateId,
                        'tenant_id' => $tenant_id,
                    ], null, $tenant_id);

                    return [
                        'status' => true,
                        'message' => 'Template deleted from Meta but not found in database',
                        'meta_deleted' => true,
                        'db_deleted' => false,
                        'meta_response' => $metaResponse,
                    ];
                }
            } catch (\Exception $dbException) {
                whatsapp_log('Database deletion failed after Meta deletion', 'error', [
                    'template_name' => $templateName,
                    'template_id' => $templateId,
                    'db_error' => $dbException->getMessage(),
                    'tenant_id' => $tenant_id,
                ], $dbException, $tenant_id);

                return [
                    'status' => false,
                    'message' => 'Template deleted from Meta but database deletion failed: ' . $dbException->getMessage(),
                    'meta_deleted' => true,
                    'db_deleted' => false,
                    'meta_response' => $metaResponse,
                ];
            }
        } catch (\Exception $e) {
            whatsapp_log('Template deletion failed', 'error', [
                'template_name' => $templateName,
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'message' => 'Template deletion failed: ' . $e->getMessage(),
                'meta_deleted' => false,
                'db_deleted' => false,
                'error_details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete multiple templates with proper rollback handling
     *
     * @param  array  $templates  Array of templates with 'name' and 'id' keys
     */
    public function deleteMultipleTemplates(array $templates): array
    {
        $tenant_id = $this->getWaTenantId();
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        $rollbackNeeded = [];

        try {
            foreach ($templates as $template) {
                $templateName = $template['name'] ?? '';
                $templateId = $template['id'] ?? '';

                if (empty($templateName) || empty($templateId)) {
                    $failureCount++;
                    $results[] = [
                        'template_name' => $templateName,
                        'template_id' => $templateId,
                        'status' => false,
                        'message' => 'Invalid template data provided',
                    ];

                    continue;
                }

                $result = $this->deleteTemplate($templateName, $templateId);

                if ($result['status']) {
                    $successCount++;
                    if ($result['meta_deleted']) {
                        $rollbackNeeded[] = $template; // For potential rollback
                    }
                } else {
                    $failureCount++;
                }

                $results[] = array_merge($result, [
                    'template_name' => $templateName,
                    'template_id' => $templateId,
                ]);
            }

            whatsapp_log('Bulk template deletion completed', 'info', [
                'total_templates' => count($templates),
                'successful' => $successCount,
                'failed' => $failureCount,
                'tenant_id' => $tenant_id,
            ], null, $tenant_id);

            return [
                'status' => $failureCount === 0,
                'message' => "Deletion completed: {$successCount} successful, {$failureCount} failed",
                'total_processed' => count($templates),
                'successful' => $successCount,
                'failed' => $failureCount,
                'results' => $results,
                'rollback_available' => !empty($rollbackNeeded),
            ];
        } catch (\Exception $e) {
            whatsapp_log('Bulk template deletion failed', 'error', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'message' => 'Bulk deletion failed: ' . $e->getMessage(),
                'total_processed' => count($results),
                'successful' => $successCount,
                'failed' => $failureCount,
                'results' => $results,
                'error_details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log flow activity to database
     */
    protected function logFlowActivity($to, $response, $messageData, $category)
    {
        $data = [
            'response_code' => $response['responseCode'] ?? 200,
            'category' => $category,
            'category_id' => $messageData['flow_id'] ?? 0,
            'rel_type' => $messageData['rel_type'] ?? '',
            'rel_id' => $messageData['rel_id'] ?? '',
            'category_params' => json_encode($messageData),
            'response_data' => json_encode($response['responseData'] ?? []),
            'raw_data' => json_encode($response['data'] ?? []),
            'phone_number_id' => get_setting('whatsapp.wm_default_phone_number_id'),
            'access_token' => get_setting('whatsapp.wm_access_token'),
            'business_account_id' => get_setting('whatsapp.wm_business_account_id'),
        ];

        WmActivityLog::create($data);
    }

    /**
     * Upload resumable media for template examples (Fixed WhatsJet Pattern)
     * This method properly handles the upload and returns the handle needed for templates
     */
    protected function uploadResumableMediaForTemplate(string $mediaUrl): ?string
    {

        $tenant_id = $this->getWaTenantId();
        $accessToken = $this->getToken();
        $facebookAppId = $this->getFBAppID();

        whatsapp_log('Starting resumable media upload for template', 'debug', [
            'media_url' => $mediaUrl,
            'facebook_app_id' => $facebookAppId,
        ], null, $tenant_id);

        // Step 1: Download the media file
        $fileResponse = Http::timeout(30)->get($mediaUrl);
        if ($fileResponse->failed()) {
            throw new \Exception('Failed to download media file from: ' . $mediaUrl);
        }

        $fileContents = $fileResponse->body();
        $mimeType = $fileResponse->header('Content-Type');

        // If Content-Type header is not available, try to detect from URL extension
        if (!$mimeType) {
            $extension = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'mp4' => 'video/mp4',
                'pdf' => 'application/pdf',
                default => 'application/octet-stream'
            };
        }

        $fileLength = strlen($fileContents);

        whatsapp_log('Media file downloaded', 'debug', [
            'file_length' => $fileLength,
            'mime_type' => $mimeType,
        ], null, $tenant_id);

        // Step 2: Create upload session
        $uploadSessionResponse = Http::withToken($accessToken)
            ->post(self::getBaseUrl() . "{$facebookAppId}/uploads", [
                'file_length' => $fileLength,
                'file_type' => $mimeType,
            ]);

        if ($uploadSessionResponse->failed()) {
            $errorData = $uploadSessionResponse->json();
            throw new \Exception('Failed to create upload session: ' . ($errorData['error']['message'] ?? $uploadSessionResponse->body()));
        }

        $uploadSessionData = $uploadSessionResponse->json();
        $uploadSessionId = $uploadSessionData['id'];

        whatsapp_log('Upload session created', 'debug', [
            'session_id' => $uploadSessionId,
        ], null, $tenant_id);

        // Step 3: Upload the file using cURL (like WhatsJet)
        $uploadUrl = self::getBaseUrl() . $uploadSessionId;

        $ch = curl_init();

        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'whatsapp_upload_');
        file_put_contents($tempFile, $fileContents);

        $postFields = [
            'file' => new \CURLFile($tempFile, $mimeType, basename($mediaUrl)),
            'type' => $mimeType,
        ];

        $headers = [
            'Authorization: OAuth ' . $accessToken,
            'file_offset: 0',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $uploadUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Clean up temp file
        unlink($tempFile);

        if ($result === false || !empty($curlError)) {
            throw new \Exception('cURL error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new \Exception('Upload failed with HTTP code: ' . $httpCode . ', Response: ' . $result);
        }

        $resultData = json_decode($result, true);

        if (!$resultData) {
            throw new \Exception('Invalid JSON response: ' . $result);
        }

        if (isset($resultData['error'])) {
            throw new \Exception('Upload API error: ' . ($resultData['error']['message'] ?? json_encode($resultData['error'])));
        }

        // WhatsJet returns the 'h' field for resumable uploads
        $uploadHandle = $resultData['h'] ?? null;

        if (!$uploadHandle) {
            throw new \Exception('No upload handle returned. Response: ' . $result);
        }

        whatsapp_log('Resumable media uploaded successfully for template', 'info', [
            'original_url' => $mediaUrl,
            'upload_handle' => $uploadHandle,
            'tenant_id' => $tenant_id,
        ], null, $tenant_id);

        return $uploadHandle ?? null;
    }

    /**
     * Create a new WhatsApp template (Fixed version)
     */
    public function createTemplate(array $templateData): array
    {
        $tenant_id = $this->getWaTenantId();
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();

        try {
            $url = self::getBaseUrl() . "{$accountId}/message_templates";

            // Build template components from the incoming data structure
            $components = [];
            $data = $templateData['data'] ?? [];

            // Add header component if provided
            if (!empty($data['header'])) {
                $headerComponent = [
                    'type' => 'HEADER',
                ];

                if ($data['header']['type'] === 'TEXT') {
                    $headerComponent['format'] = 'TEXT';
                    $headerComponent['text'] = $data['header']['text'];

                    // Add header example if there are variables
                    if (preg_match('/\{\{\d+\}\}/', $data['header']['text'])) {
                        $headerComponent['example'] = [
                            'header_text' => ['Sample Header Value'],
                        ];
                    }
                } elseif (in_array($data['header']['type'], ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                    $headerComponent['format'] = $data['header']['type'];

                    $mediaUrl = $data['header']['media_url'] ?? null;

                    if (!empty($mediaUrl)) {
                        // Upload media and get handle
                        $uploadedHandle = $this->uploadResumableMediaForTemplate($mediaUrl);

                        if ($uploadedHandle) {
                            $headerComponent['example'] = [
                                'header_handle' => [$uploadedHandle],
                            ];

                            whatsapp_log('Using upload handle for template header', 'info', [
                                'upload_handle' => $uploadedHandle,
                                'media_url' => $mediaUrl,
                                'header_type' => $data['header']['type'],
                            ], null, $tenant_id);
                        } else {
                            // If upload fails, we cannot create IMAGE/VIDEO/DOCUMENT header without example
                            return [
                                'status' => false,
                                'message' => 'Failed to upload media for template header. Media upload is required for ' . $data['header']['type'] . ' headers.',
                                'error_details' => 'Media upload failed',
                            ];
                        }
                    }
                }

                $components[] = $headerComponent;
            }

            // Add body component (required)
            if (!empty($data['body'])) {
                $bodyComponent = [
                    'type' => 'BODY',
                    'text' => $data['body'],
                ];

                // Add body parameters example if there are variables
                if (preg_match_all('/\{\{\d+\}\}/', $data['body'], $matches)) {
                    $paramCount = count(array_unique($matches[0]));
                    $examples = [];
                    for ($i = 1; $i <= $paramCount; $i++) {
                        $examples[] = "Sample Value {$i}";
                    }
                    $bodyComponent['example'] = [
                        'body_text' => [$examples],
                    ];
                }

                $components[] = $bodyComponent;
            }

            // Add footer component if provided
            if (!empty($data['footer'])) {
                $components[] = [
                    'type' => 'FOOTER',
                    'text' => $data['footer'],
                ];
            }

            // Add buttons component if provided
            if (!empty($data['buttons']) && is_array($data['buttons'])) {
                $buttonsComponent = [
                    'type' => 'BUTTONS',
                    'buttons' => [],
                ];

                foreach ($data['buttons'] as $button) {
                    $buttonData = [
                        'type' => $button['type'] ?? 'QUICK_REPLY',
                    ];

                    if ($button['type'] === 'QUICK_REPLY') {
                        $buttonData['text'] = $button['text'] ?? '';
                    } elseif ($button['type'] === 'PHONE_NUMBER') {
                        $buttonData['text'] = $button['text'] ?? '';
                        $buttonData['phone_number'] = $button['phone_number'] ?? '';
                    } elseif ($button['type'] === 'URL') {
                        $buttonData['text'] = $button['text'] ?? '';
                        $buttonData['url'] = $button['url'] ?? '';

                        // Check if URL has variables
                        if (preg_match('/\{\{\d+\}\}/', $button['url'])) {
                            $buttonData['example'] = ['https://example.com/sample'];
                        }
                    } elseif ($button['type'] === 'COPY_CODE') {
                        $buttonData['text'] = $button['text'] ?? '';
                        $buttonData['copy_code'] = $button['copy_code'] ?? '';
                    }

                    $buttonsComponent['buttons'][] = $buttonData;
                }

                $components[] = $buttonsComponent;
            }

            // Prepare the request payload
            $payload = [
                'name' => $templateData['template_name'],
                'language' => $templateData['language'] ?? 'en_US',
                'category' => $templateData['category'] ?? 'MARKETING',
                'components' => $components,
            ];

            // Add allow_category_change if specified
            if (isset($templateData['allow_category_change'])) {
                $payload['allow_category_change'] = $templateData['allow_category_change'];
            }

            $response = Http::withToken($accessToken)->post($url, $payload);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? t('failed_to_create_template');

                whatsapp_log('Template creation failed', 'error', [
                    'template_name' => $templateData['template_name'],
                    'response_code' => $response->status(),
                    'error_message' => $errorMessage,
                    'full_error' => $errorData,
                    'payload' => $payload,
                    'tenant_id' => $tenant_id,
                ], null, $tenant_id);

                return [
                    'status' => false,
                    'message' => "Template creation failed: {$errorMessage}",
                    'error_code' => $response->status(),
                    'error_details' => $errorData,
                ];
            }

            $responseData = $response->json();

            whatsapp_log('Template created successfully', 'info', [
                'template_name' => $templateData['template_name'],
                'template_id' => $responseData['id'] ?? 'unknown',
                'status' => $responseData['status'] ?? 'unknown',
                'tenant_id' => $tenant_id,
            ], null, $tenant_id);

            return [
                'status' => true,
                'message' => 'Template created successfully',
                'data' => $responseData,
                'template_id' => $responseData['id'] ?? null,
                'template_status' => $responseData['status'] ?? 'PENDING',
            ];
        } catch (\Exception $e) {
            whatsapp_log('Template creation exception', 'error', [
                'template_name' => $templateData['template_name'] ?? 'unknown',
                'error' => $e->getMessage(),
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'message' => 'Template creation failed: ' . $e->getMessage(),
                'error_details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing WhatsApp template in Meta (Fixed version)
     *
     * @param  string  $templateId  Template ID to update
     * @param  array  $templateData  Data to update
     * @return array Response containing status and updated data
     */
    public function updateTemplate(string $templateId, array $templateData): array
    {
        $tenant_id = $this->getWaTenantId();
        $accessToken = $this->getToken();

        try {
            // First, try to get the template from local database to get Meta template ID
            $localTemplate = null;
            $metaTemplateId = $templateId;

            // Check if this is a local database ID (numeric) vs Meta template ID
            if (is_numeric($templateId)) {
                $localTemplate = WhatsappTemplate::where('template_id', $templateId)
                    ->where('tenant_id', $tenant_id)
                    ->first();

                if (!$localTemplate || !$localTemplate->template_id) {
                    return [
                        'status' => false,
                        'message' => 'Template not found or missing Meta template ID',
                    ];
                }

                $metaTemplateId = $localTemplate->template_id;
            } else {
                // If it's already a Meta template ID, get the local template
                $localTemplate = WhatsappTemplate::where('template_id', $templateId)
                    ->where('tenant_id', $tenant_id)
                    ->first();
            }

            // Check if template can be edited (only certain statuses can be edited)
            if ($localTemplate && in_array($localTemplate->status, ['REJECTED', 'DISABLED'])) {
                return [
                    'status' => false,
                    'message' => ucfirst(strtolower($localTemplate->status)) . ' templates cannot be edited. Please create a new template.',
                    'error_code' => 'TEMPLATE_NOT_EDITABLE',
                ];
            }

            // Use the correct Meta API endpoint for template updates
            // The endpoint should be the template ID directly, not account/template_id
            $url = self::getBaseUrl() . $metaTemplateId;

            // Build template components from the incoming data structure
            $components = [];
            $data = $templateData['data'] ?? [];

            // Add header component if provided
            if (!empty($data['header'])) {
                $headerComponent = [
                    'type' => 'HEADER',
                ];

                if ($data['header']['type'] === 'TEXT') {
                    $headerComponent['format'] = 'TEXT';
                    $headerComponent['text'] = $data['header']['text'];

                    // Add header example if there are variables
                    if (preg_match('/\{\{\d+\}\}/', $data['header']['text'])) {
                        $headerComponent['example'] = [
                            'header_text' => ['Sample Header Value'],
                        ];
                    }
                } elseif (in_array($data['header']['type'], ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                    $headerComponent['format'] = $data['header']['type'];

                    $mediaUrl = $data['header']['media_url'] ?? null;

                    if (!empty($mediaUrl)) {
                        // Upload media and get handle for updates too
                        $uploadedHandle = $this->uploadResumableMediaForTemplate($mediaUrl);

                        if ($uploadedHandle) {
                            $headerComponent['example'] = [
                                'header_handle' => [$uploadedHandle],
                            ];

                            whatsapp_log('Using upload handle for template update', 'info', [
                                'upload_handle' => $uploadedHandle,
                                'media_url' => $mediaUrl,
                                'template_id' => $metaTemplateId,
                            ], null, $tenant_id);
                        } else {
                            // If upload fails, we cannot update with IMAGE/VIDEO/DOCUMENT header
                            return [
                                'status' => false,
                                'message' => 'Failed to upload media for template header update. Media upload is required for ' . $data['header']['type'] . ' headers.',
                                'error_details' => 'Media upload failed',
                            ];
                        }
                    }
                }

                $components[] = $headerComponent;
            }

            // Add body component (required)
            if (!empty($data['body'])) {
                $bodyComponent = [
                    'type' => 'BODY',
                    'text' => $data['body'],
                ];

                // Add body parameters example if there are variables
                if (preg_match_all('/\{\{\d+\}\}/', $data['body'], $matches)) {
                    $paramCount = count(array_unique($matches[0]));
                    $examples = [];
                    for ($i = 1; $i <= $paramCount; $i++) {
                        $examples[] = "Sample Value {$i}";
                    }
                    $bodyComponent['example'] = [
                        'body_text' => [$examples],
                    ];
                }

                $components[] = $bodyComponent;
            }

            // Add footer component if provided
            if (!empty($data['footer'])) {
                $components[] = [
                    'type' => 'FOOTER',
                    'text' => $data['footer'],
                ];
            }

            // Add buttons component if provided
            if (!empty($data['buttons']) && is_array($data['buttons'])) {
                $buttonsComponent = [
                    'type' => 'BUTTONS',
                    'buttons' => [],
                ];

                foreach ($data['buttons'] as $button) {
                    $buttonData = [
                        'type' => $button['type'] ?? 'QUICK_REPLY',
                    ];

                    if ($button['type'] === 'QUICK_REPLY') {
                        $buttonData['text'] = $button['text'] ?? '';
                    } elseif ($button['type'] === 'PHONE_NUMBER') {
                        $buttonData['text'] = $button['text'] ?? '';
                        $buttonData['phone_number'] = $button['phone_number'] ?? '';
                    } elseif ($button['type'] === 'URL') {
                        $buttonData['text'] = $button['text'] ?? '';
                        $buttonData['url'] = $button['url'] ?? '';

                        // Check if URL has variables
                        if (preg_match('/\{\{\d+\}\}/', $button['url'])) {
                            $buttonData['example'] = ['https://example.com/sample'];
                        }
                    } elseif ($button['type'] === 'COPY_CODE') {
                        $buttonData['text'] = $button['text'] ?? '';
                        $buttonData['copy_code'] = $button['copy_code'] ?? '';
                    }

                    $buttonsComponent['buttons'][] = $buttonData;
                }

                $components[] = $buttonsComponent;
            }

            // Prepare the update payload - Following WhatsJet pattern
            $payload = [
                'components' => $components,
            ];

            // For updates, we can also include the template name if it's being changed
            if (isset($templateData['template_name'])) {
                $payload['name'] = $templateData['template_name'];
            }

            // Category can be updated too
            if (isset($templateData['category'])) {
                $payload['category'] = $templateData['category'];
            }

            // Send the update request to Meta
            $response = Http::withToken($accessToken)->post($url, $payload);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to update template';

                whatsapp_log('Template update failed', 'error', [
                    'template_id' => $metaTemplateId,
                    'response_code' => $response->status(),
                    'error_message' => $errorMessage,
                    'payload' => $payload,
                    'full_error' => $errorData,
                    'tenant_id' => $tenant_id,
                ], null, $tenant_id);

                return [
                    'status' => false,
                    'message' => "Template update failed: {$errorMessage}",
                    'error_code' => $response->status(),
                    'error_details' => $errorData,
                ];
            }

            $responseData = $response->json();

            // Update local database with the updated template data
            if ($localTemplate) {
                $updateData = [
                    'status' => $responseData['status'] ?? 'PENDING',
                    'updated_at' => now(),
                ];

                // Update name and category if they were provided
                if (isset($templateData['template_name'])) {
                    $updateData['template_name'] = $templateData['template_name'];
                }
                if (isset($templateData['category'])) {
                    $updateData['category'] = $templateData['category'];
                }
                if (isset($templateData['language'])) {
                    $updateData['language'] = $templateData['language'];
                }

                $localTemplate->update($updateData);
            }

            whatsapp_log('Template updated successfully', 'info', [
                'template_id' => $metaTemplateId,
                'template_name' => $templateData['template_name'] ?? $localTemplate->template_name ?? 'unknown',
                'status' => $responseData['status'] ?? 'unknown',
                'tenant_id' => $tenant_id,
            ], null, $tenant_id);

            return [
                'status' => true,
                'message' => 'Template updated successfully',
                'data' => $responseData,
                'template_id' => $metaTemplateId,
                'template_status' => $responseData['status'] ?? 'PENDING',
            ];
        } catch (\Exception $e) {
            whatsapp_log('Template update exception', 'error', [
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'message' => 'Template update failed: ' . $e->getMessage(),
                'error_details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get template details from Meta
     *
     * @param  string  $templateId  Template ID
     * @return array Template details
     */
    public function getTemplate(string $templateId): array
    {
        $tenant_id = $this->getWaTenantId();
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();

        try {
            $url = self::getBaseUrl() . "{$accountId}/message_templates/{$templateId}";

            $response = Http::withToken($accessToken)->get($url);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to get template';

                whatsapp_log('Get template failed', 'error', [
                    'template_id' => $templateId,
                    'response_code' => $response->status(),
                    'error_message' => $errorMessage,
                    'tenant_id' => $tenant_id,
                ], null, $tenant_id);

                return [
                    'status' => false,
                    'message' => "Failed to get template: {$errorMessage}",
                    'error_code' => $response->status(),
                ];
            }

            $templateData = $response->json();

            return [
                'status' => true,
                'data' => $templateData,
                'message' => 'Template retrieved successfully',
            ];
        } catch (\Exception $e) {
            whatsapp_log('Get template exception', 'error', [
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);

            return [
                'status' => false,
                'message' => 'Failed to get template: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update template Meta information (template_id and status) in local database
     *
     * @param  int  $localId  Local database template ID (primary key)
     * @param  string  $metaTemplateId  Meta template ID to store in template_id column
     * @param  string  $status  Template status from Meta
     */
    protected function updateTemplateMetaInfo(int $localId, string $metaTemplateId, string $status): void
    {
        $tenant_id = $this->getWaTenantId();

        try {
            $updated = WhatsappTemplate::where('id', $localId)
                ->where('tenant_id', $tenant_id)
                ->update([
                    'template_id' => $metaTemplateId,
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            whatsapp_log('Failed to update template Meta info in database', 'error', [
                'local_id' => $localId,
                'meta_template_id' => $metaTemplateId,
                'error' => $e->getMessage(),
                'tenant_id' => $tenant_id,
            ], $e, $tenant_id);
        }
    }

    /**
     * Send Flow Webhook API request based on node data
     *
     * @param  array  $nodeData  The API node data from the flow builder
     * @return array Response containing success status and data
     */
    public function sendFlowWebhookApi($nodeData, $phoneNumberId, $contactData, $context): array
    {

        try {
            // Extract data from output array if present, otherwise use direct nodeData
            $output = $nodeData['output'] ?? $nodeData;

            // Validate required fields - check both locations
            if (isset($output[0]) && is_array($output[0])) {
                $output = $output[0];
            }
            $requestUrl = $output['requestUrl'] ?? '';

            if (empty($requestUrl)) {
                return [
                    'success' => false,
                    'error' => 'Request URL is required',
                    'response' => null,
                ];
            }

            $url = $requestUrl;
            $method = strtoupper($output['requestMethod'] ?? $nodeData['requestMethod'] ?? 'get');
            $format = $output['requestFormat'] ?? $nodeData['requestFormat'] ?? 'json';
            $headers = $output['requestHeaders'] ?? $nodeData['requestHeaders'] ?? [];
            $body = $output['requestBody'] ?? $nodeData['requestBody'] ?? [];

            // Build headers array
            $requestHeaders = [];
            foreach ($headers as $header) {
                if (!empty($header['name']) && !empty($header['value'])) {
                    $headerValue = $this->replaceFlowVariables($header['value'], $contactData);
                    $requestHeaders[$header['name']] = $headerValue;
                }
            }

            // Set Content-Type based on format if not already set
            $contentTypeSet = false;
            foreach ($requestHeaders as $key => $value) {
                if (strtolower($key) === 'content-type') {
                    $contentTypeSet = true;
                    break;
                }
            }

            if (!$contentTypeSet && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                switch (strtolower($format)) {
                    case 'json':
                        $requestHeaders['Content-Type'] = 'application/json';
                        break;
                    case 'xml':
                        $requestHeaders['Content-Type'] = 'application/xml';
                        break;
                    case 'form':
                        $requestHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
                        break;
                    case 'text':
                        $requestHeaders['Content-Type'] = 'text/plain';
                        break;
                }
            }

            // Build request body based on format
            $requestBody = null;
            if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($body)) {
                $bodyData = [];
                foreach ($body as $field) {
                    if (isset($field['key']) && $field['key'] !== '' && isset($field['value'])) {
                        $bodyValue = $this->replaceFlowVariables($field['value'], $contactData);
                        $bodyData[$field['key']] = $bodyValue;
                    }
                }

                if (!empty($bodyData)) {
                    switch (strtolower($format)) {
                        case 'json':
                            $requestBody = json_encode($bodyData);
                            break;
                        case 'xml':
                            $requestBody = $this->arrayToXml($bodyData);
                            break;
                        case 'form':
                            $requestBody = http_build_query($bodyData);
                            break;
                        case 'text':
                            // For text format, join key-value pairs with newlines
                            $textParts = [];
                            foreach ($bodyData as $key => $value) {
                                $textParts[] = "$key: $value";
                            }
                            $requestBody = implode("\n", $textParts);
                            break;
                    }
                }
            }

            // Log the request for debugging
            \Log::info('Flow Webhook API Request', [
                'url' => $url,
                'method' => $method,
                'headers' => $requestHeaders,
                'body' => $requestBody,
                'tenant_id' => $this->getWaTenantId(),
            ]);

            // Make HTTP request
            $httpClient = Http::withHeaders($requestHeaders)
                ->timeout(30)
                ->retry(2, 1000);

            // Execute the request based on method
            switch ($method) {
                case 'GET':
                    $response = $httpClient->get($url);
                    break;
                case 'POST':
                    if ($requestBody !== null) {
                        if (isset($requestHeaders['Content-Type']) && $requestHeaders['Content-Type'] === 'application/json') {
                            $response = $httpClient->post($url, json_decode($requestBody, true));
                        } else {
                            $response = $httpClient->withBody($requestBody, $requestHeaders['Content-Type'] ?? 'application/json')->post($url);
                        }
                    } else {
                        $response = $httpClient->post($url);
                    }
                    break;
                case 'PUT':
                    if ($requestBody !== null) {
                        if (isset($requestHeaders['Content-Type']) && $requestHeaders['Content-Type'] === 'application/json') {
                            $response = $httpClient->put($url, json_decode($requestBody, true));
                        } else {
                            $response = $httpClient->withBody($requestBody, $requestHeaders['Content-Type'] ?? 'application/json')->put($url);
                        }
                    } else {
                        $response = $httpClient->put($url);
                    }
                    break;
                case 'PATCH':
                    if ($requestBody !== null) {
                        if (isset($requestHeaders['Content-Type']) && $requestHeaders['Content-Type'] === 'application/json') {
                            $response = $httpClient->patch($url, json_decode($requestBody, true));
                        } else {
                            $response = $httpClient->withBody($requestBody, $requestHeaders['Content-Type'] ?? 'application/json')->patch($url);
                        }
                    } else {
                        $response = $httpClient->patch($url);
                    }
                    break;
                case 'DELETE':
                    $response = $httpClient->delete($url);
                    break;
                case 'HEAD':
                    $response = $httpClient->head($url);
                    break;
                case 'OPTIONS':
                    $response = $httpClient->send('OPTIONS', $url);
                    break;
                default:
                    return [
                        'success' => false,
                        'error' => "Unsupported HTTP method: $method",
                        'response' => null,
                    ];
            }

            // Process response
            $responseJson = null;
            try {
                $responseJson = $response->json();
            } catch (\Exception $jsonException) {
                // If JSON parsing fails, set to null
                $responseJson = null;
            }

            $responseData = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'json' => $responseJson,
                'response_time' => null, // HTTP facade doesn't provide this
            ];

            // Log the response
            \Log::info('Flow Webhook API Response', [
                'url' => $url,
                'status_code' => $response->status(),
                'success' => $response->successful(),
                'tenant_id' => $this->getWaTenantId(),
            ]);

            return [
                'success' => true,
                'error' => null,
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Flow Webhook API Error', [
                'url' => $requestUrl ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => $this->getWaTenantId(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null,
            ];
        }
    }

    /**
     * Convert array to XML string
     */
    private function arrayToXml(array $data, string $rootElement = 'root'): string
    {
        try {
            $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><$rootElement></$rootElement>");

            foreach ($data as $key => $value) {
                // Ensure key is a valid XML element name
                $xmlKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $key);

                if (is_array($value)) {
                    $subnode = $xml->addChild($xmlKey);
                    foreach ($value as $subkey => $subvalue) {
                        $xmlSubkey = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $subkey);
                        $subnode->addChild($xmlSubkey, htmlspecialchars((string) $subvalue));
                    }
                } else {
                    $xml->addChild($xmlKey, htmlspecialchars((string) $value));
                }
            }

            $result = $xml->asXML();

            return $result !== false ? $result : "<?xml version=\"1.0\"?><$rootElement></$rootElement>";
        } catch (\Exception $e) {
            // Return a basic XML structure if conversion fails
            return "<?xml version=\"1.0\"?><$rootElement><error>XML conversion failed</error></$rootElement>";
        }
    }

    /**
     * Process condition node in flow
     */
    protected function processFlowCondition($nodeData, $phoneNumberId, $contactData, $context)
    {
        try {
            $conditions = $nodeData['conditions'] ?? [];
            $defaultAction = $nodeData['defaultAction'] ?? 'continue';

            if (empty($conditions)) {
                return ['status' => false, 'message' => 'No conditions defined'];
            }

            $result = $this->evaluateConditions($conditions, $contactData, $context);

            whatsapp_log('Condition evaluation result', 'info', [
                'node_id' => $context['current_node'] ?? 'unknown',
                'result' => $result,
                'conditions_count' => count($conditions),
            ]);

            return [
                'status' => true,
                'condition_result' => $result,
                'next_path' => $result ? 'true' : 'false',
                'default_action' => $defaultAction,
            ];

        } catch (\Throwable $e) {
            whatsapp_log('Condition processing error', 'error', [
                'error' => $e->getMessage(),
                'node_data' => $nodeData,
            ], $e);

            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Evaluate multiple conditions with logic operators
     */
    protected function evaluateConditions($conditions, $contactData, $context)
    {
        if (empty($conditions)) {
            return false;
        }

        $results = [];

        foreach ($conditions as $condition) {
            $results[] = $this->evaluateSingleCondition($condition, $contactData, $context);
        }

        // Process logic operators (AND/OR)
        $finalResult = $results[0];

        for ($i = 0; $i < count($conditions) - 1; $i++) {
            $logic = $conditions[$i]['logic'] ?? 'AND';
            $nextResult = $results[$i + 1];

            if ($logic === 'OR') {
                $finalResult = $finalResult || $nextResult;
            } else { // AND
                $finalResult = $finalResult && $nextResult;
            }
        }

        return $finalResult;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateSingleCondition($condition, $contactData, $context)
    {
        $variable = $condition['variable'] ?? '';
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? '';
        $customVariable = $condition['customVariable'] ?? '';

        // Get the actual value to compare
        $actualValue = $this->getVariableValue($variable, $customVariable, $contactData, $context);

        // Perform comparison based on operator
        switch ($operator) {
            case 'equals':
                return $actualValue == $value;
            case 'not_equals':
                return $actualValue != $value;
            case 'contains':
                return stripos($actualValue, $value) !== false;
            case 'not_contains':
                return stripos($actualValue, $value) === false;
            case 'starts_with':
                return stripos($actualValue, $value) === 0;
            case 'ends_with':
                return substr_compare($actualValue, $value, -strlen($value), strlen($value), true) === 0;
            case 'greater_than':
                return is_numeric($actualValue) && is_numeric($value) && floatval($actualValue) > floatval($value);
            case 'less_than':
                return is_numeric($actualValue) && is_numeric($value) && floatval($actualValue) < floatval($value);
            case 'is_empty':
                return empty($actualValue);
            case 'is_not_empty':
                return !empty($actualValue);
            default:
                return false;
        }
    }

    /**
     * Get variable value for condition evaluation
     */
    protected function getVariableValue($variable, $customVariable, $contactData, $context)
    {
        // Handle custom variables
        if ($variable === 'custom_variable' && !empty($customVariable)) {
            return $context['variables'][$customVariable] ?? '';
        }

        // Handle system variables
        switch ($variable) {
            case 'contact_name':
                return $contactData->name ?? '';
            case 'contact_phone':
                return $contactData->phone ?? '';
            case 'contact_email':
                return $contactData->email ?? '';
            case 'contact_type':
                return $contactData->type ?? '';
            case 'current_time':
                return now()->format('H:i');
            case 'day_of_week':
                return now()->format('l');
            case 'business_hours':
                return $this->isBusinessHours() ? 'yes' : 'no';
            case 'user_message':
                return $context['trigger_message'] ?? '';
            default:
                return $context['variables'][$variable] ?? '';
        }
    }

    /**
     * Check if current time is within business hours
     */
    protected function isBusinessHours()
    {
        $now = now();
        $hour = $now->hour;
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Default business hours: Monday-Friday, 9 AM - 6 PM
        $isWeekday = $dayOfWeek >= 1 && $dayOfWeek <= 5;
        $isBusinessHour = $hour >= 9 && $hour < 18;

        return $isWeekday && $isBusinessHour;
    }

    /**
     * Process delay node in flow
     */
    protected function processFlowDelay($nodeData, $phoneNumberId, $contactData, $context)
    {
        try {
            $delayType = $nodeData['delayType'] ?? 'fixed';
            $delaySeconds = $this->calculateDelaySeconds($nodeData);

            whatsapp_log('Processing delay node', 'info', [
                'node_id' => $context['current_node'] ?? 'unknown',
                'delay_type' => $delayType,
                'delay_seconds' => $delaySeconds,
            ]);

            // For immediate processing, we'll return the delay info
            // The actual delay implementation would depend on your queue system
            return [
                'status' => true,
                'delay_seconds' => $delaySeconds,
                'delay_type' => $delayType,
                'show_typing' => $nodeData['showTyping'] ?? true,
                'message' => "Delay of {$delaySeconds} seconds configured"
            ];

        } catch (\Throwable $e) {
            whatsapp_log('Delay processing error', 'error', [
                'error' => $e->getMessage(),
                'node_data' => $nodeData,
            ], $e);

            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate delay in seconds based on node configuration
     */
    protected function calculateDelaySeconds($nodeData)
    {
        $delayType = $nodeData['delayType'] ?? 'fixed';

        switch ($delayType) {
            case 'fixed':
                $duration = $nodeData['duration'] ?? 3;
                $unit = $nodeData['unit'] ?? 'seconds';
                return $this->convertToSeconds($duration, $unit);

            case 'random':
                $minDuration = $nodeData['minDuration'] ?? 1;
                $maxDuration = $nodeData['maxDuration'] ?? 5;
                $unit = $nodeData['unit'] ?? 'seconds';
                $randomDuration = rand($minDuration, $maxDuration);
                return $this->convertToSeconds($randomDuration, $unit);

            case 'typing':
                $messageLength = $nodeData['messageLength'] ?? 100;
                $typingSpeed = $nodeData['typingSpeed'] ?? 'normal';
                $wpm = $typingSpeed === 'slow' ? 40 : ($typingSpeed === 'fast' ? 80 : 60);
                return max(1, ceil(($messageLength / 5) / ($wpm / 60)));

            case 'scheduled':
                // For scheduled delays, return a large number to indicate special handling needed
                return 86400; // 24 hours as placeholder

            default:
                return 3;
        }
    }

    /**
     * Convert duration to seconds
     */
    protected function convertToSeconds($duration, $unit)
    {
        switch ($unit) {
            case 'minutes':
                return $duration * 60;
            case 'hours':
                return $duration * 3600;
            case 'days':
                return $duration * 86400;
            case 'seconds':
            default:
                return $duration;
        }
    }

    /**
     * Process input collection node in flow
     */
    protected function processFlowInputCollection($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        try {
            $message = $nodeData['message'] ?? 'Please provide the following information:';
            $fields = $nodeData['fields'] ?? [];
            $collectionMode = $nodeData['collectionMode'] ?? 'sequential';

            if (empty($fields)) {
                return ['status' => false, 'message' => 'No input fields defined'];
            }

            // Replace variables in message
            $processedMessage = $this->replaceFlowVariables($message, $contactData);

            // For sequential mode, start with the first field
            if ($collectionMode === 'sequential') {
                $firstField = $fields[0];
                $fieldMessage = $this->buildFieldMessage($firstField, $processedMessage);

                // Send the collection message
                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => $fieldMessage,
                    'bot_header' => '',
                    'bot_footer' => '',
                    'tenant_id' => $this->wa_tenant_id,
                ];

                $result = $this->sendMessage($to, $messageData, $phoneNumberId);

                // Log the input collection start
                $this->logFlowActivity($to, $result, [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'collection_mode' => $collectionMode,
                    'fields_count' => count($fields),
                    'current_field' => 0,
                ], 'flow_input_collection_start');

                return array_merge($result, [
                    'collection_active' => true,
                    'current_field' => 0,
                    'total_fields' => count($fields),
                    'collection_mode' => $collectionMode,
                ]);
            }

            // For form mode, create a comprehensive message
            else {
                $formMessage = $this->buildFormMessage($fields, $processedMessage);

                $messageData = [
                    'rel_type' => $contactData->type ?? 'guest',
                    'rel_id' => $contactData->id ?? '',
                    'reply_text' => $formMessage,
                    'bot_header' => '',
                    'bot_footer' => '',
                    'tenant_id' => $this->wa_tenant_id,
                ];

                return $this->sendMessage($to, $messageData, $phoneNumberId);
            }

        } catch (\Throwable $e) {
            whatsapp_log('Input collection processing error', 'error', [
                'error' => $e->getMessage(),
                'node_data' => $nodeData,
            ], $e);

            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Build message for a single field collection
     */
    protected function buildFieldMessage($field, $introMessage)
    {
        $message = $introMessage . "\n\n";
        $message .= $field['label'] ?? 'Please enter value:';

        if ($field['required'] ?? false) {
            $message .= ' *';
        }

        if ($field['type'] === 'choice' && !empty($field['options'])) {
            $options = explode("\n", $field['options']);
            $message .= "\n\nOptions:";
            foreach ($options as $index => $option) {
                $message .= "\n" . ($index + 1) . ". " . trim($option);
            }
        }

        return $message;
    }

    /**
     * Build comprehensive form message for all fields
     */
    protected function buildFormMessage($fields, $introMessage)
    {
        $message = $introMessage . "\n\n";

        foreach ($fields as $index => $field) {
            $message .= ($index + 1) . ". " . ($field['label'] ?? 'Field ' . ($index + 1));

            if ($field['required'] ?? false) {
                $message .= ' *';
            }

            if ($field['type'] === 'choice' && !empty($field['options'])) {
                $options = explode("\n", $field['options']);
                $message .= " (Choose: " . implode(', ', array_map('trim', $options)) . ")";
            }

            $message .= "\n";
        }

        $message .= "\n* Required fields\n";
        $message .= "Please provide all information in your response.";

        return $message;
    }

    /**
     * Send quick replies message from flow (Phase 2)
     */
    protected function sendFlowQuickReplies($to, $nodeData, $phoneNumberId, $contactData, $context)
    {
        try {
            $this->logFlowDebug('Quick Replies: Starting', [
                'to' => $to,
                'nodeData' => $nodeData,
                'phoneNumberId' => $phoneNumberId
            ]);

            $message = $this->replaceFlowVariables($nodeData['message'] ?? '', $contactData);
            $header = $this->replaceFlowVariables($nodeData['header'] ?? '', $contactData);
            $footer = $this->replaceFlowVariables($nodeData['footer'] ?? '', $contactData);
            $replies = $nodeData['replies'] ?? [];

            // Filter out empty replies
            $validReplies = array_filter($replies, function ($reply) {
                return !empty($reply['text']);
            });

            if (empty($validReplies)) {
                $this->logFlowDebug('Quick Replies: No valid replies', ['replies' => $replies]);
                return ['status' => false, 'message' => 'No valid quick replies configured'];
            }

            // Build WhatsApp quick reply buttons (interactive message)
            $buttons = [];
            foreach (array_values($validReplies) as $index => $reply) {
                if ($index >= 13)
                    break; // WhatsApp limit

                $buttons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => $reply['id'] ?? "reply-{$index}",
                        'title' => substr($reply['text'], 0, 20) // WhatsApp limit 20 chars
                    ]
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => $buttons
                    ]
                ]
            ];

            // Add optional header
            if (!empty($header)) {
                $payload['interactive']['header'] = [
                    'type' => 'text',
                    'text' => $header
                ];
            }

            // Add optional footer
            if (!empty($footer)) {
                $payload['interactive']['footer'] = [
                    'text' => $footer
                ];
            }

            $this->logFlowDebug('Quick Replies: Payload built', [
                'payload' => $payload,
                'buttons_count' => count($buttons)
            ]);

            // Track analytics if enabled
            if ($nodeData['trackAnalytics'] ?? true) {
                whatsapp_log('Quick replies sent', 'info', [
                    'to' => $to,
                    'replies_count' => count($buttons),
                    'message' => substr($message, 0, 50)
                ]);
            }

            // Load WhatsApp Cloud API config
            $whatsapp_cloud_api = $this->loadConfig($phoneNumberId);

            $this->logFlowDebug('Quick Replies: Sending via API', [
                'api_loaded' => !empty($whatsapp_cloud_api),
                'to' => $to
            ]);

            // Send using WhatsApp Cloud API sendRequest method
            $response = $whatsapp_cloud_api->sendRequest(
                'POST',
                '/' . $phoneNumberId . '/messages',
                $payload
            );

            $result = [
                'status' => $response->httpStatusCode() >= 200 && $response->httpStatusCode() < 300,
                'data' => json_decode($response->body()),
                'responseCode' => $response->httpStatusCode(),
                'message' => $response->httpStatusCode() >= 200 && $response->httpStatusCode() < 300
                    ? 'Quick replies sent successfully'
                    : 'Failed to send quick replies'
            ];

            $this->logFlowDebug('Quick Replies: API Response', [
                'result' => $result,
                'success' => $result['status'],
                'response_code' => $response->httpStatusCode(),
                'response_body' => $response->body()
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logFlowDebug('Quick Replies: EXCEPTION', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'nodeData' => $nodeData
            ]);

            return [
                'status' => false,
                'message' => 'Quick Replies Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process tag management from flow (Phase 2)
     */
    protected function processFlowTagManagement($nodeData, $phoneNumberId, $contactData, $context)
    {
        $action = $nodeData['action'] ?? 'add';
        $tags = $nodeData['tags'] ?? [];
        $createIfNotExist = $nodeData['createIfNotExist'] ?? true;
        $logActivity = $nodeData['logActivity'] ?? true;

        $appliedTags = [];

        try {
            // Process based on action type
            switch ($action) {
                case 'add':
                    foreach ($tags as $tag) {
                        if (empty($tag['name']))
                            continue;

                        $this->addContactTag($contactData->id, $tag['name'], $createIfNotExist);
                        $appliedTags[] = $tag['name'];
                    }
                    break;

                case 'remove':
                    foreach ($tags as $tag) {
                        if (empty($tag['name']))
                            continue;

                        $this->removeContactTag($contactData->id, $tag['name']);
                        $appliedTags[] = $tag['name'];
                    }
                    break;

                case 'replace':
                    // Remove all existing tags first
                    $this->clearContactTags($contactData->id);

                    // Then add new tags
                    foreach ($tags as $tag) {
                        if (empty($tag['name']))
                            continue;

                        $this->addContactTag($contactData->id, $tag['name'], $createIfNotExist);
                        $appliedTags[] = $tag['name'];
                    }
                    break;

                case 'conditional':
                    $conditions = $nodeData['conditions'] ?? [];

                    foreach ($conditions as $condition) {
                        $variable = $this->getVariableValue($condition['variable'] ?? '', $contactData, $context);
                        $operator = $condition['operator'] ?? 'equals';
                        $value = $condition['value'] ?? '';
                        $tagToApply = $condition['tagToApply'] ?? '';

                        if ($this->evaluateSingleCondition($variable, $operator, $value) && !empty($tagToApply)) {
                            $this->addContactTag($contactData->id, $tagToApply, $createIfNotExist);
                            $appliedTags[] = $tagToApply;
                        }
                    }
                    break;
            }

            // Log activity if enabled
            if ($logActivity) {
                whatsapp_log('Tag management executed', 'info', [
                    'contact_id' => $contactData->id,
                    'action' => $action,
                    'tags_applied' => $appliedTags
                ]);
            }

            return [
                'status' => true,
                'message' => 'Tags processed successfully',
                'applied_tags' => $appliedTags,
                'next_handle' => $action === 'conditional' && empty($appliedTags) ? 'no_match' : 'success'
            ];

        } catch (\Exception $e) {
            whatsapp_log('Tag management error', 'error', [
                'error' => $e->getMessage(),
                'contact_id' => $contactData->id
            ], $e);

            return ['status' => false, 'message' => 'Failed to process tags: ' . $e->getMessage()];
        }
    }

    /**
     * Process variable management from flow (Phase 2)
     */
    protected function processFlowVariableManagement($nodeData, $phoneNumberId, $contactData, $context)
    {
        $action = $nodeData['action'] ?? 'set';
        $variableName = $nodeData['variableName'] ?? '';
        $value = $nodeData['value'] ?? '';
        $scope = $nodeData['scope'] ?? 'contact';
        $encrypt = $nodeData['encrypt'] ?? false;
        $logChanges = $nodeData['logChanges'] ?? true;

        if (empty($variableName)) {
            return ['status' => false, 'message' => 'Variable name is required'];
        }

        try {
            $processedValue = $value;

            // Process action
            switch ($action) {
                case 'set':
                    // Replace variables in value
                    $processedValue = $this->replaceFlowVariables($value, $contactData);
                    break;

                case 'increment':
                    // Get current value and increment
                    $currentValue = $this->getFlowVariable($variableName, $contactData, $scope);
                    $processedValue = (floatval($currentValue) + floatval($value));
                    break;

                case 'append':
                    // Get current value and append
                    $currentValue = $this->getFlowVariable($variableName, $contactData, $scope);
                    $processedValue = $currentValue . $value;
                    break;

                case 'clear':
                    $processedValue = null;
                    break;
            }

            // Encrypt if needed
            if ($encrypt && $processedValue !== null) {
                $processedValue = encrypt($processedValue);
            }

            // Calculate expiration timestamp
            $expiresAt = null;
            if ($nodeData['expirationUnit'] !== 'never' && !empty($nodeData['expirationValue'])) {
                $expiresAt = $this->calculateVariableExpiration(
                    $nodeData['expirationValue'],
                    $nodeData['expirationUnit']
                );
            }

            // Store variable based on scope
            $this->setFlowVariable($variableName, $processedValue, $contactData, $scope, $expiresAt);

            // Log changes if enabled
            if ($logChanges) {
                whatsapp_log('Variable management executed', 'info', [
                    'variable' => $variableName,
                    'action' => $action,
                    'scope' => $scope,
                    'encrypted' => $encrypt,
                    'contact_id' => $contactData->id
                ]);
            }

            return [
                'status' => true,
                'message' => 'Variable processed successfully',
                'variable' => $variableName,
                'value' => $encrypt ? '[encrypted]' : $processedValue
            ];

        } catch (\Exception $e) {
            whatsapp_log('Variable management error', 'error', [
                'error' => $e->getMessage(),
                'variable' => $variableName
            ], $e);

            return ['status' => false, 'message' => 'Failed to process variable: ' . $e->getMessage()];
        }
    }

    /**
     * Helper methods for tag management
     */
    private function addContactTag($contactId, $tagName, $createIfNotExist = true)
    {
        // Implementation would depend on your tag system
        // This is a placeholder - adapt to your actual tag implementation
        whatsapp_log('Adding tag', 'debug', ['contact_id' => $contactId, 'tag' => $tagName]);
    }

    private function removeContactTag($contactId, $tagName)
    {
        whatsapp_log('Removing tag', 'debug', ['contact_id' => $contactId, 'tag' => $tagName]);
    }

    private function clearContactTags($contactId)
    {
        whatsapp_log('Clearing all tags', 'debug', ['contact_id' => $contactId]);
    }

    /**
     * Helper methods for variable management
     */
    private function getFlowVariable($variableName, $contactData, $scope)
    {
        // Implementation would depend on your variable storage system
        // This is a placeholder - adapt to your actual implementation
        return '';
    }

    private function setFlowVariable($variableName, $value, $contactData, $scope, $expiresAt = null)
    {
        // Implementation would depend on your variable storage system
        whatsapp_log('Setting variable', 'debug', [
            'variable' => $variableName,
            'scope' => $scope,
            'expires_at' => $expiresAt
        ]);
    }

    private function calculateVariableExpiration($value, $unit)
    {
        $seconds = match ($unit) {
            'minutes' => $value * 60,
            'hours' => $value * 3600,
            'days' => $value * 86400,
            'weeks' => $value * 604800,
            default => 0
        };

        return $seconds > 0 ? now()->addSeconds($seconds) : null;
    }

    /**
     * Log flow debugging information to file
     */
    private function logFlowDebug($message, $data = [])
    {
        // Logging disabled - can be re-enabled in the future if needed
        return;
    }

    /**
     * Send typing indicator to WhatsApp
     */
    public function sendTypingIndicator($to, $phoneNumberId)
    {
        try {
            $whatsappApi = $this->getWhatsAppApi();

            // WhatsApp typing indicator API call
            $response = $whatsappApi->sendTyping($to, true, $phoneNumberId);

            return [
                'status' => true,
                'message' => 'Typing indicator sent',
                'data' => $response
            ];
        } catch (\Exception $e) {
            whatsapp_log('Failed to send typing indicator', 'warning', [
                'to' => $to,
                'phone_number_id' => $phoneNumberId,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => 'Failed to send typing indicator: ' . $e->getMessage()
            ];
        }
    }
}
