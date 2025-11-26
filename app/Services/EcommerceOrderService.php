<?php

namespace App\Services;

use App\Models\Tenant\Contact;
use App\Models\Tenant\Product;
use App\Models\Tenant\EcommerceConfiguration;
use App\Models\Tenant\EcommerceOrder;
use App\Models\Tenant\EcommerceUserSession;
use App\Services\EcommerceLogger;
use App\Services\AiEcommerceService;
use App\Traits\Ai;
use App\Traits\WhatsApp;
use Carbon\Carbon;

/**
 * E-commerce Order Processing Service - AI-ONLY MODE
 * Handles WhatsApp-based product catalog, orders, and AI-powered interactions
 */
class EcommerceOrderService
{
    use Ai, WhatsApp;

    protected $tenantId;
    protected $config;
    protected $currentContact;

    public function __construct($tenantId = null)
    {
        $this->tenantId = $tenantId ?? tenant_id();
        $this->config = EcommerceConfiguration::where('tenant_id', $this->tenantId)->first();
    }

    /**
     * Process incoming WhatsApp message for e-commerce - AI ONLY
     */
    public function processMessage(string $message, Contact $contact): array
    {
        EcommerceLogger::info('🔍 PROCESSING: Starting message processing', [
            'tenant_id' => $this->tenantId,
            'message' => $message,
            'contact_id' => $contact->id ?? 'unknown',
            'contact_phone' => $contact->phone ?? 'unknown'
        ]);

        try {
            if (!$this->config) {
                EcommerceLogger::error('🔍 PROCESSING: No ecommerce config found', [
                    'tenant_id' => $this->tenantId,
                    'message' => $message
                ]);
                return [
                    'handled' => true, // Force handled to prevent fallbacks
                    'response' => 'E-commerce not configured. Please set up AI configuration.'
                ];
            }

            EcommerceLogger::info('🔍 PROCESSING: Config found, checking if fully configured', [
                'tenant_id' => $this->tenantId,
                'is_configured' => $this->config->is_configured ?? 'unknown',
                'ai_powered_mode' => $this->config->ai_powered_mode ?? 'unknown'
            ]);

            if (!$this->config->isFullyConfigured()) {
                EcommerceLogger::error('🔍 PROCESSING: Ecommerce config incomplete', [
                    'tenant_id' => $this->tenantId,
                    'message' => $message,
                    'is_configured' => $this->config->is_configured ?? 'unknown'
                ]);
                return [
                    'handled' => true, // Force handled to prevent fallbacks
                    'response' => 'E-commerce configuration incomplete. Please complete the setup.'
                ];
            }

            EcommerceLogger::info('🔍 PROCESSING: E-commerce configuration verified', [
                'tenant_id' => $this->tenantId,
                'message' => $message,
                'ai_powered_mode' => $this->config->ai_powered_mode ?? false
            ]);

            $this->currentContact = $contact;
            
            // FORCE AI-ONLY MODE - NO TRADITIONAL FALLBACK
            EcommerceLogger::info('🤖 AI-POWERED MODE: Starting AI processing', [
                'tenant_id' => $this->tenantId,
                'contact_phone' => $contact->phone,
                'message' => $message,
                'openai_api_key_exists' => !empty($this->config->openai_api_key),
                'model' => $this->config->openai_model ?? 'not_set'
            ]);
            
            try {
                $aiService = new AdvancedAiEcommerceService($this->tenantId);
                $aiResult = $aiService->processAdvancedMessage($message, $contact);
                
                EcommerceLogger::info('🤖 AI-RESPONSE: Received AI result', [
                    'tenant_id' => $this->tenantId,
                    'handled' => $aiResult['handled'] ?? false,
                    'response_length' => strlen($aiResult['response'] ?? ''),
                    'response_preview' => substr($aiResult['response'] ?? '', 0, 100) . '...',
                    'has_buttons' => !empty($aiResult['buttons']),
                    'buttons_count' => count($aiResult['buttons'] ?? []),
                    'has_actions' => !empty($aiResult['actions']),
                    'actions_count' => count($aiResult['actions'] ?? []),
                    'full_ai_result' => $aiResult
                ]);
                
                if ($aiResult['handled']) {
                    // Execute any AI-generated actions
                    if (!empty($aiResult['actions'])) {
                        EcommerceLogger::info('🤖 AI-ACTIONS: Executing AI actions', [
                            'tenant_id' => $this->tenantId,
                            'actions' => $aiResult['actions']
                        ]);
                        $aiService->executeActions($aiResult['actions']);
                    }
                    
                    // Return AI response with buttons if provided
                    $response = [
                        'handled' => true,
                        'response' => $aiResult['response']
                    ];
                    
                    if (!empty($aiResult['buttons'])) {
                        EcommerceLogger::info('🤖 AI-BUTTONS: Adding interactive buttons', [
                            'tenant_id' => $this->tenantId,
                            'buttons' => $aiResult['buttons']
                        ]);
                        
                        $response['message_data'] = [
                            'reply_text' => $aiResult['response'],
                            'bot_header' => 'AI Shopping Assistant',
                            'bot_footer' => 'Powered by AI'
                        ];
                        
                        // Add up to 3 buttons
                        foreach ($aiResult['buttons'] as $index => $button) {
                            if ($index < 3) {
                                $buttonNum = $index + 1;
                                $response['message_data']["button{$buttonNum}_id"] = $button['id'];
                                $response['message_data']["button{$buttonNum}"] = $button['text'];
                            }
                        }
                        
                        $response['buttons'] = array_slice($aiResult['buttons'], 0, 3);
                    }
                    
                    EcommerceLogger::info('🤖 AI-SUCCESS: AI processed successfully, returning response', [
                        'tenant_id' => $this->tenantId,
                        'contact_phone' => $contact->phone,
                        'final_response' => $response
                    ]);
                    
                    EcommerceLogger::botInteraction($contact->phone, $message, 'AI processed successfully');
                    return $response;
                } else {
                    // AI failed - return AI-only failure response
                    EcommerceLogger::error('🤖 AI-FAILED: AI could not handle message', [
                        'tenant_id' => $this->tenantId,
                        'contact_phone' => $contact->phone,
                        'message' => $message,
                        'ai_response' => $aiResult['response'] ?? 'no_response'
                    ]);
                    
                    return [
                        'handled' => true,
                        'response' => $aiResult['response'] ?? 'I apologize, but I\'m having trouble understanding your request. Please try rephrasing your question or ask about our products.'
                    ];
                }
            } catch (\Exception $e) {
                // AI error - return AI-only error response
                EcommerceLogger::error('🤖 AI-ERROR: Exception in AI processing', [
                    'tenant_id' => $this->tenantId,
                    'contact_phone' => $contact->phone,
                    'message' => $message,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return [
                    'handled' => true,
                    'response' => 'I apologize, but I\'m experiencing technical difficulties. Please try again in a moment or contact our support team.'
                ];
            }
            
        } catch (\Exception $e) {
            EcommerceLogger::error('Error processing message', [
                'tenant_id' => $this->tenantId,
                'contact_phone' => $contact->phone ?? 'unknown',
                'message' => $message,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return [
                'handled' => true, // Force handled to prevent further fallbacks
                'response' => 'An error occurred while processing your message. Please try again later.'
            ];
        }
    }
}
