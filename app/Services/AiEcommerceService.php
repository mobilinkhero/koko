<?php

namespace App\Services;

use App\Models\Tenant\EcommerceConfiguration;
use App\Models\Tenant\Product;
use App\Models\Tenant\AiConversation;
use App\Services\EcommerceLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI-Powered E-commerce Service
 * Uses OpenAI to handle all customer interactions with local products database
 */
class AiEcommerceService
{
    protected $tenantId;
    protected $config;

    public function __construct($tenantId = null)
    {
        $this->tenantId = $tenantId ?? tenant_id();
        $this->config = EcommerceConfiguration::where('tenant_id', $this->tenantId)->first();
    }

    /**
     * Process customer message with AI
     */
    public function processMessage(string $message, $contact): array
    {
        EcommerceLogger::info('🤖 AI-SERVICE: Starting message processing', [
            'tenant_id' => $this->tenantId,
            'message' => $message,
            'contact_phone' => $contact->phone ?? 'unknown'
        ]);

        try {
            if (!$this->isAiConfigured()) {
                EcommerceLogger::error('🤖 AI-CONFIG: AI not properly configured', [
                    'tenant_id' => $this->tenantId,
                    'config_exists' => $this->config ? 'yes' : 'no',
                    'ai_mode' => $this->config->ai_powered_mode ?? 'unknown',
                    'api_key_exists' => !empty($this->config->openai_api_key ?? '')
                ]);
                return [
                    'handled' => true, // Return true to prevent fallbacks
                    'response' => 'AI is not properly configured. Please set up your OpenAI API key in the e-commerce settings.'
                ];
            }

            EcommerceLogger::info('🤖 AI-CONFIG: AI configuration validated', [
                'tenant_id' => $this->tenantId,
                'model' => $this->config->openai_model,
                'temperature' => $this->config->ai_temperature,
                'max_tokens' => $this->config->ai_max_tokens,
                'direct_sheets' => $this->config->direct_sheets_integration
            ]);

            // Get current product data from local database
            EcommerceLogger::info('🤖 AI-DATABASE: Fetching products from local database', [
                'tenant_id' => $this->tenantId
            ]);

            $productData = $this->getProductDataFromDatabase();

            EcommerceLogger::info('🤖 AI-DATABASE: Products fetched', [
                'tenant_id' => $this->tenantId,
                'products_count' => count($productData),
                'products_preview' => array_slice($productData, 0, 3)
            ]);

            if (empty($productData)) {
                EcommerceLogger::error('🤖 AI-DATABASE: No products available', [
                    'tenant_id' => $this->tenantId
                ]);
                return [
                    'handled' => false,
                    'response' => 'No products available'
                ];
            }

            // Get or create conversation thread
            EcommerceLogger::info('🤖 AI-THREAD: Getting conversation thread', [
                'tenant_id' => $this->tenantId,
                'contact_id' => $contact->id,
                'contact_phone' => $contact->phone
            ]);

            $systemPrompt = $this->buildSystemPrompt($productData, $contact);
            $conversation = AiConversation::getOrCreate(
                $this->tenantId, 
                $contact->id, 
                $contact->phone, 
                $systemPrompt
            );

            EcommerceLogger::info('🤖 AI-THREAD: Conversation thread ready', [
                'tenant_id' => $this->tenantId,
                'thread_id' => $conversation->thread_id,
                'message_count' => $conversation->message_count,
                'is_new_conversation' => $conversation->wasRecentlyCreated
            ]);

            // Add user message to conversation
            $conversation->addUserMessage($message);

            // Call OpenAI with conversation context
            EcommerceLogger::info('🤖 AI-OPENAI: Calling OpenAI API with thread context', [
                'tenant_id' => $this->tenantId,
                'thread_id' => $conversation->thread_id,
                'model' => $this->config->openai_model,
                'total_messages' => $conversation->message_count
            ]);

            $aiResponse = $this->callOpenAIWithConversation($conversation);

            EcommerceLogger::info('🤖 AI-OPENAI: OpenAI response received', [
                'tenant_id' => $this->tenantId,
                'response_received' => !empty($aiResponse),
                'response_length' => strlen($aiResponse ?? ''),
                'response_preview' => substr($aiResponse ?? '', 0, 200) . '...',
                'full_response' => $aiResponse // Log full response to debug format
            ]);

            if (!$aiResponse) {
                EcommerceLogger::error('🤖 AI-OPENAI: No response from OpenAI', [
                    'tenant_id' => $this->tenantId
                ]);
                return [
                    'handled' => false,
                    'response' => 'AI service unavailable'
                ];
            }

            // Parse AI response for actions
            EcommerceLogger::info('🤖 AI-PARSE: Parsing AI response', [
                'tenant_id' => $this->tenantId
            ]);

            $parsedResponse = $this->parseAiResponse($aiResponse);

            // Save AI response to conversation
            $conversation->addAiResponse($aiResponse);

            EcommerceLogger::info('🤖 AI-PARSE: AI response parsed', [
                'tenant_id' => $this->tenantId,
                'thread_id' => $conversation->thread_id,
                'type' => $parsedResponse['type'] ?? 'unknown',
                'response_length' => strlen($parsedResponse['message'] ?? ''),
                'has_buttons' => !empty($parsedResponse['buttons']),
                'button_count' => count($parsedResponse['buttons'] ?? []),
                'has_actions' => !empty($parsedResponse['actions']),
                'full_parsed_response' => $parsedResponse
            ]);

            EcommerceLogger::info('AI processed message', [
                'tenant_id' => $this->tenantId,
                'contact_id' => $contact->id,
                'message' => substr($message, 0, 100),
                'ai_response_type' => $parsedResponse['type'] ?? 'text'
            ]);

            return [
                'handled' => true,
                'response' => $parsedResponse['message'],
                'buttons' => $parsedResponse['buttons'] ?? [],
                'actions' => $parsedResponse['actions'] ?? []
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('AI processing failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $this->tenantId
            ]);

            return [
                'handled' => false,
                'response' => 'I apologize, but I encountered an error. Please try again.'
            ];
        }
    }

    /**
     * Check if AI is properly configured
     */
    protected function isAiConfigured(): bool
    {
        return $this->config 
            && $this->config->ai_powered_mode 
            && !empty($this->config->openai_api_key);
    }

    /**
     * Get product data from local database
     */
    protected function getProductDataFromDatabase(): array
    {
        try {
            EcommerceLogger::info('🤖 AI-DATABASE: Fetching products from local database', [
                'tenant_id' => $this->tenantId
            ]);

            // Get active, in-stock products from the database
            $products = Product::where('tenant_id', $this->tenantId)
                ->active()
                ->inStock()
                ->get();

            EcommerceLogger::info('🤖 AI-DATABASE: Products query executed', [
                'tenant_id' => $this->tenantId,
                'total_products_found' => $products->count()
            ]);

            // Convert to array format expected by AI
            $productData = [];
            foreach ($products as $product) {
                $productArray = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->effective_price,
                    'original_price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => $product->stock_quantity,
                    'category' => $product->category,
                    'subcategory' => $product->subcategory,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'featured' => $product->featured,
                    'is_on_sale' => $product->is_on_sale,
                    'is_low_stock' => $product->is_low_stock,
                    'formatted_price' => $product->formatted_price,
                    'tags' => $product->tags,
                    'images' => $product->images,
                    'primary_image' => $product->primary_image
                ];
                
                $productData[] = $productArray;
            }

            EcommerceLogger::info('🤖 AI-DATABASE: Product data formatted for AI', [
                'tenant_id' => $this->tenantId,
                'products_count' => count($productData),
                'sample_product' => $productData[0] ?? null
            ]);

            return $productData;

        } catch (\Exception $e) {
            EcommerceLogger::error('🤖 AI-DATABASE: Failed to get product data from database', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Build system prompt for AI
     */
    protected function buildSystemPrompt(array $products, $contact): string
    {
        $customerName = trim($contact->firstname . ' ' . $contact->lastname);
        $productsJson = json_encode($products, JSON_UNESCAPED_UNICODE);
        
        $systemPrompt = $this->config->ai_system_prompt ?: $this->getDefaultSystemPrompt();
        
        $context = [
            'store_name' => $this->config->store_name ?? 'Our Store',
            'currency' => $this->config->currency ?? 'USD',
            'customer_name' => $customerName ?: 'Customer',
            'customer_phone' => $contact->phone ?? '',
            'products_data' => $productsJson,
            'total_products' => count($products),
            'payment_methods' => $this->getEnabledPaymentMethods(),
            'collection_policy' => $this->getCustomerDetailsPolicy()
        ];

        // Replace placeholders in system prompt
        foreach ($context as $key => $value) {
            $systemPrompt = str_replace("{{$key}}", $value, $systemPrompt);
        }

        return $systemPrompt;
    }

    /**
     * Get default system prompt
     */
    protected function getDefaultSystemPrompt(): string
    {
        return "
You are an AI shopping assistant for {store_name}. You help customers complete orders efficiently via WhatsApp.

CUSTOMER INFO:
- Name: {customer_name}
- Phone: {customer_phone}

AVAILABLE PRODUCTS:
{products_data}

AVAILABLE PAYMENT METHODS:
{payment_methods}

REQUIRED CUSTOMER DETAILS FOR CHECKOUT:
{collection_policy}

LANGUAGE SUPPORT: Detect customer language (English/Arabic/Urdu) and respond in the same language.

ORDER FLOW INSTRUCTIONS:
1. **Product Display**: Show products with JSON format + buttons
2. **Payment Selection**: When customer wants to buy, DIRECTLY show payment options as buttons, don't ask \"how would you like to pay?\"
3. **Customer Details**: Collect ALL required details based on policy above
4. **Order Creation**: When all details collected, create order with [ORDER:product_id:quantity:contact_id]

PAYMENT METHOD BUTTONS:
When customer selects a product, immediately show:
{
  \"message\": \"Great choice! *Product Name* - \$XX\\n\\nPlease select your payment method:\",
  \"buttons\": [
    {\"id\": \"pay_cod\", \"text\": \"💵 Cash on Delivery\"},
    {\"id\": \"pay_bank\", \"text\": \"🏦 Bank Transfer\"},
    {\"id\": \"pay_card\", \"text\": \"💳 Credit/Debit Card\"}
  ],
  \"type\": \"interactive\"
}

CUSTOMER DETAILS COLLECTION:
After payment method selected, collect details step by step:
- \"Please provide your full name:\"
- \"Please provide your delivery address:\" 
- \"Please confirm your phone number: {customer_phone}\"

ORDER COMPLETION:
When all details collected, create order:
{
  \"message\": \"Thank you! Your order for [quantity] x *[Product Name]* with [Payment Method] has been confirmed.\\n\\nDelivery Address: [address]\\n\\nYou can expect delivery within 3-5 business days. 🚚\",
  \"actions\": [
    {
      \"type\": \"create_order\",
      \"data\": {
        \"product_id\": \"[product_id]\",
        \"quantity\": [quantity],
        \"contact_id\": \"[contact_id]\",
        \"payment_method\": \"[payment_method]\",
        \"customer_details\": {
          \"name\": \"[customer_name]\",
          \"phone\": \"[customer_phone]\",
          \"address\": \"[delivery_address]\"
        }
      }
    }
  ]
}

RESPONSE FORMATS:

PRODUCTS (Always JSON):
{
  \"message\": \"Here are our products:\\n\\n1. *Product Name*\\n💰 \$XX\\n📋 Description\\n📦 In Stock\\n\\nWhich interests you?\",
  \"buttons\": [
    {\"id\": \"select_1\", \"text\": \"🛒 Select This\"},
    {\"id\": \"info_1\", \"text\": \"ℹ️ More Info\"}
  ],
  \"type\": \"interactive\"
}

MANDATORY RULES:
- Always detect and match customer's language
- Never ask \"how would you like to pay?\" - show payment buttons directly
- Collect ALL required customer details before order creation
- Always use JSON format for products and payment selection
- Create order with proper action format when ready
- Be conversational and helpful throughout the process

CRITICAL: Orders must be saved to database using the actions format above.
        ";
    }

    /**
     * Call OpenAI API with conversation context (COST EFFICIENT)
     */
    protected function callOpenAIWithConversation(AiConversation $conversation): string
    {
        $apiKey = $this->config->openai_api_key;
        $model = $this->config->openai_model ?: 'gpt-3.5-turbo';
        $temperature = (float) ($this->config->ai_temperature ?: 0.7);
        $maxTokens = (int) ($this->config->ai_max_tokens ?: 500);

        // Get conversation messages (includes system prompt + history)
        $messages = $conversation->getMessagesForApi();

        EcommerceLogger::info('🤖 AI-CONTEXT: Sending conversation to OpenAI', [
            'tenant_id' => $this->tenantId,
            'thread_id' => $conversation->thread_id,
            'total_messages' => count($messages),
            'system_prompt_included' => isset($messages[0]) && $messages[0]['role'] === 'system'
        ]);

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        $aiResponse = $data['choices'][0]['message']['content'] ?? '';
        
        // Track token usage if available
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;
        if ($tokensUsed > 0) {
            EcommerceLogger::info('🤖 AI-TOKENS: Token usage tracked', [
                'tenant_id' => $this->tenantId,
                'thread_id' => $conversation->thread_id,
                'tokens_used' => $tokensUsed,
                'total_conversation_tokens' => $conversation->total_tokens_used + $tokensUsed
            ]);
        }

        return $aiResponse;
    }

    /**
     * Legacy method - Call OpenAI API (DEPRECATED - Use conversation method instead)
     */
    protected function callOpenAI(string $systemPrompt, string $userMessage): string
    {
        $apiKey = $this->config->openai_api_key;
        $model = $this->config->openai_model ?: 'gpt-3.5-turbo';
        $temperature = (float) ($this->config->ai_temperature ?: 0.7);
        $maxTokens = (int) ($this->config->ai_max_tokens ?: 500);

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Parse AI response for actions and buttons
     */
    protected function parseAiResponse(string $aiResponse): array
    {
        // Try to parse as JSON first
        $jsonData = json_decode($aiResponse, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            EcommerceLogger::info('🤖 AI-JSON: Successfully parsed JSON response', [
                'tenant_id' => $this->tenantId,
                'has_message' => !empty($jsonData['message']),
                'has_buttons' => !empty($jsonData['buttons']),
                'button_count' => count($jsonData['buttons'] ?? []),
                'buttons_preview' => $jsonData['buttons'] ?? []
            ]);
            
            return [
                'type' => $jsonData['type'] ?? 'interactive',
                'message' => $jsonData['message'] ?? $aiResponse,
                'buttons' => $jsonData['buttons'] ?? [],
                'actions' => $jsonData['actions'] ?? []
            ];
        }

        // Check if response contains [BUTTONS:...] format (fallback parsing)
        if (preg_match_all('/\[BUTTONS:([^\]]+)\]/', $aiResponse, $matches)) {
            EcommerceLogger::info('🤖 AI-FALLBACK: Found button tags in text, converting to buttons', [
                'tenant_id' => $this->tenantId,
                'button_tags_found' => $matches[1],
                'total_buttons' => count($matches[1])
            ]);
            
            // Remove button tags from message
            $cleanMessage = preg_replace('/\[BUTTONS:[^\]]+\]/', '', $aiResponse);
            $cleanMessage = trim($cleanMessage);
            
            // Generate buttons from tags with smarter text
            $buttons = [];
            foreach ($matches[1] as $index => $buttonId) {
                // Create smarter button text based on product ID
                $buttonText = '🛒 Buy Now';
                if (strpos($buttonId, 'pen') !== false) {
                    $buttonText = '🖊️ Buy Pen';
                } elseif (strpos($buttonId, 'jeans') !== false) {
                    $buttonText = '👖 Buy Jeans';
                } elseif (strpos($buttonId, 'info') !== false) {
                    $buttonText = 'ℹ️ More Info';
                }
                
                $buttons[] = [
                    'id' => 'buy_' . $buttonId,
                    'text' => $buttonText
                ];
                
                // Limit to 3 buttons (WhatsApp limit)
                if (count($buttons) >= 3) break;
            }
            
            return [
                'type' => 'interactive',
                'message' => $cleanMessage,
                'buttons' => $buttons,
                'actions' => []
            ];
        }
        
        EcommerceLogger::info('🤖 AI-TEXT: Using plain text response (no JSON, no buttons)', [
            'tenant_id' => $this->tenantId,
            'response_preview' => substr($aiResponse, 0, 100) . '...'
        ]);

        // Return as plain text response
        return [
            'type' => 'text',
            'message' => $aiResponse,
            'buttons' => [],
            'actions' => []
        ];
    }

    /**
     * Get enabled payment methods for context
     */
    protected function getEnabledPaymentMethods(): string
    {
        $methods = $this->config->getEnabledPaymentMethods();
        $enabled = [];
        
        foreach ($methods as $key => $enabled_status) {
            if ($enabled_status) {
                $enabled[] = match($key) {
                    'cod' => 'Cash on Delivery',
                    'bank_transfer' => 'Bank Transfer', 
                    'card' => 'Credit/Debit Card',
                    'online' => 'Online Payment',
                    default => ucfirst(str_replace('_', ' ', $key))
                };
            }
        }

        return implode(', ', $enabled) ?: 'Cash on Delivery';
    }

    /**
     * Get customer details collection policy
     */
    protected function getCustomerDetailsPolicy(): string
    {
        if (!$this->config->collect_customer_details) {
            return 'No customer details required';
        }

        $fields = $this->config->getRequiredCustomerFields();
        $required = [];
        
        foreach ($fields as $field => $isRequired) {
            if ($isRequired) {
                $required[] = match($field) {
                    'name' => 'Full Name',
                    'phone' => 'Phone Number',
                    'address' => 'Address', 
                    'city' => 'City',
                    'email' => 'Email',
                    'notes' => 'Special Instructions',
                    default => ucfirst($field)
                };
            }
        }

        return 'Required: ' . implode(', ', $required);
    }

    /**
     * Execute actions like creating orders
     */
    public function executeActions(array $actions): array
    {
        $results = [];

        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'create_order':
                    $result = $this->createOrder($action['data'] ?? []);
                    $results[] = $result;
                    break;

                case 'update_stock':
                    $result = $this->updateProductStock($action['data'] ?? []);
                    $results[] = $result;
                    break;

                case 'add_customer':
                    $result = $this->addCustomerData($action['data'] ?? []);
                    $results[] = $result;
                    break;
            }
        }

        return $results;
    }

    /**
     * Create order in database
     */
    protected function createOrder(array $orderData): array
    {
        try {
            // Extract order details
            $productId = $orderData['product_id'] ?? null;
            $quantity = $orderData['quantity'] ?? 1;
            $contactId = $orderData['contact_id'] ?? null;
            $customerDetails = $orderData['customer_details'] ?? [];

            if (!$productId || !$contactId) {
                return ['success' => false, 'action' => 'order_creation_failed', 'message' => 'Missing required order data'];
            }

            // Get product details
            $product = Product::where('tenant_id', $this->tenantId)->find($productId);
            if (!$product) {
                return ['success' => false, 'action' => 'order_creation_failed', 'message' => 'Product not found'];
            }

            // Check stock
            if ($product->stock_quantity < $quantity) {
                return ['success' => false, 'action' => 'order_creation_failed', 'message' => 'Insufficient stock'];
            }

            // Calculate totals
            $unitPrice = $product->effective_price;
            $totalAmount = $unitPrice * $quantity;

            // Create order record
            $order = \App\Models\Tenant\EcommerceOrder::create([
                'tenant_id' => $this->tenantId,
                'contact_id' => $contactId,
                'order_number' => 'ORD-' . time() . '-' . $productId,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'payment_method' => $orderData['payment_method'] ?? 'cod',
                'customer_details' => json_encode($customerDetails),
                'order_items' => json_encode([
                    [
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalAmount
                    ]
                ]),
                'notes' => $orderData['notes'] ?? ''
            ]);

            // Update product stock
            $product->decrement('stock_quantity', $quantity);

            EcommerceLogger::info('🛍️ ORDER-CREATED: Order successfully created', [
                'tenant_id' => $this->tenantId,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'product_id' => $productId,
                'quantity' => $quantity,
                'total_amount' => $totalAmount
            ]);

            return [
                'success' => true, 
                'action' => 'order_created', 
                'message' => "Order {$order->order_number} created successfully",
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('🛍️ ORDER-ERROR: Failed to create order', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);

            return ['success' => false, 'action' => 'order_creation_failed', 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    /**
     * Update product stock
     */
    protected function updateProductStock(array $stockData): array
    {
        try {
            $productId = $stockData['product_id'] ?? null;
            $quantity = $stockData['quantity'] ?? 0;

            if (!$productId) {
                return ['success' => false, 'action' => 'stock_update_failed', 'message' => 'Product ID required'];
            }

            $product = Product::where('tenant_id', $this->tenantId)->find($productId);
            if (!$product) {
                return ['success' => false, 'action' => 'stock_update_failed', 'message' => 'Product not found'];
            }

            $product->decrement('stock_quantity', $quantity);

            return ['success' => true, 'action' => 'stock_updated', 'message' => "Stock updated for {$product->name}"];

        } catch (\Exception $e) {
            return ['success' => false, 'action' => 'stock_update_failed', 'message' => 'Failed to update stock'];
        }
    }

    /**
     * Add customer data  
     */
    protected function addCustomerData(array $customerData): array
    {
        // This could update the contact record with additional details
        return ['success' => true, 'action' => 'customer_data_added', 'message' => 'Customer data processed'];
    }
}
