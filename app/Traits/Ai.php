<?php

namespace App\Traits;

use App\Exceptions\WhatsAppException;
use App\Models\PersonalAssistant;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;
use OpenAI;

trait Ai
{
    /**
     * Store AI settings to avoid multiple database calls
     */
    protected $aiSettings;

    /**
     * Load all AI settings in a single batch call
     */
    protected function loadAiSettings()
    {
        if (!isset($this->aiSettings)) {
            $this->aiSettings = get_batch_settings([
                'whats-mark.chat_model',
            ]);
        }

        return $this->aiSettings;
    }

    public function listModel(): array
    {
        try {
            $openAiKey = $this->getOpenAiKey();
            $openAi = new OpenAI;
            $client = $openAi->client($openAiKey);
            $response = $client->models()->list();

            if ($response === null || !is_object($response)) {
                throw new \RuntimeException('Invalid response format from OpenAI API.');
            }

            if (property_exists($response, 'error')) {
                save_tenant_setting('whats-mark', 'is_open_ai_key_verify', false);

                return [
                    'status' => false,
                    'message' => $response->error->message ?? 'Unknown error occurred.',
                ];
            }

            save_tenant_setting('whats-mark', 'is_open_ai_key_verify', true);

            return [
                'status' => true,
                'data' => 'Model list fetched successfully.',
            ];
        } catch (\Throwable $th) {
            save_tenant_setting('whats-mark', 'is_open_ai_key_verify', false);
            whatsapp_log('OpenAI Model List Error', 'error', [
                'error' => $th->getMessage(),
            ], $th);

            return [
                'status' => false,
                'message' => $th->getMessage(),
            ];
            throw new WhatsAppException($th->getMessage());
        }
    }

    /**
     * Sends a request to the OpenAI API to get a response based on provided data.
     *
     * @param  array  $data  The data to be sent to the OpenAI API.
     * @return array Contains status and message of the response.
     */
    public function aiResponse(array $data)
    {
        try {
            $config = new OpenAIConfig;
            $config->apiKey = $this->getOpenAiKey();

            // Load settings and use from batch
            $this->loadAiSettings();
            $config->model = $this->aiSettings['whats-mark.chat_model'] ?? 'gpt-3.5-turbo';

            $chat = new OpenAIChat($config);
            $message = $data['input_msg'];
            $menuItem = $data['menu'];
            $submenuItem = $data['submenu'];
            $status = true;

            $prompt = match ($menuItem) {
                'Simplify Language' => 'You will be provided with statements, and your task is to convert them to Simplify Language. but don\'t change inputed language.',
                'Fix Spelling & Grammar' => 'You will be provided with statements, and your task is to convert them to standard Language. but don\'t change inputed language.',
                'Translate' => 'You will be provided with a sentence, and your task is to translate it into ' . $submenuItem . ', only give translated sentence',
                'Change Tone' => 'You will be provided with statements, and your task is to change tone into ' . $submenuItem . '. but don\'t change inputed language.',
                'Custom Prompt' => $submenuItem,
            };

            $messages = [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $message],
            ];

            // Send the structured messages to OpenAI's chat API
            $response = $chat->generateChat($messages);
        } catch (\Throwable $th) {
            whatsapp_log('OpenAI Chat Generation Error', 'error', [
                'error' => $th->getMessage(),
            ], $th);

            $status = false;
            $message = $th->getMessage();
        }

        return [
            'status' => $status,
            'message' => $status ? $response : $message,
        ];
    }

    /**
     * Retrieves the OpenAI API key from the options.
     *
     * @param int|null $tenantId Optional tenant ID. If not provided, uses current tenant context.
     * @return string|null The OpenAI API key.
     */
    public function getOpenAiKey($tenantId = null)
    {
        // If tenant ID is explicitly provided, use it
        if ($tenantId !== null) {
            return get_tenant_setting_by_tenant_id('whats-mark', 'openai_secret_key', null, $tenantId);
        }

        // Try to get tenant ID from wa_tenant_id property if available (WhatsApp trait)
        if (property_exists($this, 'wa_tenant_id') && !empty($this->wa_tenant_id)) {
            return get_tenant_setting_by_tenant_id('whats-mark', 'openai_secret_key', null, $this->wa_tenant_id);
        }

        // Fallback to current tenant context
        return get_tenant_setting_from_db('whats-mark', 'openai_secret_key');
    }

    /**
     * Send message to personal assistant with context
     *
     * @param string $message User message
     * @param array $conversationHistory Previous messages for context
     * @param PersonalAssistant|null $assistant The assistant to use
     * @param int|null $contactId Contact ID for thread persistence
     * @param string|null $contactPhone Contact phone for thread persistence
     * @param int|null $tenantId Tenant ID for thread persistence
     * @return array Contains status and response
     */
    public function personalAssistantResponse(
        string $message,
        array $conversationHistory = [],
        ?PersonalAssistant $assistant = null,
        ?int $contactId = null,
        ?string $contactPhone = null,
        ?int $tenantId = null
    ): array {
        $logFile = storage_path('logs/aipersonaldebug.log');
        $timestamp = now()->format('Y-m-d H:i:s');

        // Get tenant ID for API key retrieval
        $tenantId = null;
        if (property_exists($this, 'wa_tenant_id') && !empty($this->wa_tenant_id)) {
            $tenantId = $this->wa_tenant_id;
        } elseif (function_exists('tenant_id')) {
            $tenantId = tenant_id();
        }

        // Log request start
        $this->logToFile($logFile, "================================================================================");
        $this->logToFile($logFile, "[$timestamp] PERSONAL AI ASSISTANT - REQUEST START");
        $this->logToFile($logFile, "================================================================================");
        $this->logToFile($logFile, "USER MESSAGE: " . $message);
        $this->logToFile($logFile, "CONVERSATION HISTORY COUNT: " . count($conversationHistory));
        $this->logToFile($logFile, "TENANT ID: " . ($tenantId ?? 'N/A'));

        try {
            // Use provided assistant or fall back to getForCurrentTenant for backward compatibility
            if (!$assistant) {
                // SECURITY FIX: In webhook contexts (WhatsApp), Laravel tenant context may not be set
                // Use wa_tenant_id property if available to ensure correct tenant isolation
                if (property_exists($this, 'wa_tenant_id') && !empty($this->wa_tenant_id)) {
                    $this->logToFile($logFile, "FALLBACK: Using wa_tenant_id ({$this->wa_tenant_id}) to get assistant");
                    $assistant = PersonalAssistant::getForTenant($this->wa_tenant_id);
                } else {
                    $this->logToFile($logFile, "FALLBACK: Using getCurrentTenant() to get assistant");
                    $assistant = PersonalAssistant::getForCurrentTenant();
                }
            }

            if (!$assistant) {
                $this->logToFile($logFile, "ERROR: No personal assistant configured for this tenant");
                $this->logToFile($logFile, "RESPONSE: No personal assistant configured");
                $this->logToFile($logFile, "================================================================================\n");

                return [
                    'status' => false,
                    'message' => 'No personal assistant configured for this tenant.',
                ];
            }

            // Log assistant info
            $this->logToFile($logFile, "ASSISTANT FOUND:");
            $this->logToFile($logFile, "  - ID: " . $assistant->id);
            $this->logToFile($logFile, "  - Name: " . $assistant->name);
            $this->logToFile($logFile, "  - Model: " . $assistant->model);
            $this->logToFile($logFile, "  - OpenAI Assistant ID: " . ($assistant->openai_assistant_id ?? 'NOT SYNCED'));
            $this->logToFile($logFile, "  - Temperature: " . $assistant->temperature);
            $this->logToFile($logFile, "  - Max Tokens: " . $assistant->max_tokens);
            $this->logToFile($logFile, "  - Is Active: " . ($assistant->is_active ? 'Yes' : 'No'));
            $this->logToFile($logFile, "  - Files Loaded: " . $assistant->getFileCount());

            if (!$assistant->is_active) {
                $this->logToFile($logFile, "ERROR: Personal assistant is disabled");
                $this->logToFile($logFile, "RESPONSE: Assistant currently disabled");
                $this->logToFile($logFile, "================================================================================\n");

                return [
                    'status' => false,
                    'message' => 'Personal assistant is currently disabled.',
                ];
            }

            // Check if assistant is synced with OpenAI - use Assistants API if available
            if ($assistant->openai_assistant_id) {
                $this->logToFile($logFile, "USING OPENAI ASSISTANTS API (Real Assistant)");
                return $this->useOpenAIAssistantsAPI($assistant, $message, $conversationHistory, $logFile, $timestamp, $contactId, $contactPhone, $tenantId);
            }

            // Fallback to Chat Completions API if not synced
            $this->logToFile($logFile, "USING CHAT COMPLETIONS API (Fallback - Assistant not synced)");

            // Get and validate API key with tenant ID
            $openAiKey = $this->getOpenAiKey($tenantId);
            if (empty($openAiKey)) {
                $this->logToFile($logFile, "ERROR: OpenAI API key is not configured");
                $this->logToFile($logFile, "  - Tenant ID: " . ($tenantId ?? 'N/A'));
                $this->logToFile($logFile, "  - Setting Key: whats-mark.openai_secret_key");
                $this->logToFile($logFile, "  - Current Tenant Context: " . (tenant_id() ?? 'N/A'));
                $this->logToFile($logFile, "  - Has wa_tenant_id: " . (property_exists($this, 'wa_tenant_id') ? ($this->wa_tenant_id ?? 'NULL') : 'NO PROPERTY'));

                return [
                    'status' => false,
                    'message' => 'OpenAI API key is not configured. Please configure it in settings.',
                ];
            }

            $config = new OpenAIConfig;
            $config->apiKey = $openAiKey;
            $config->model = $assistant->model;

            $chat = new OpenAIChat($config);

            // Build message array with system context
            $messages = [];

            // Add system instructions with knowledge base
            $systemContext = $assistant->getFullSystemContext();
            $messages[] = ['role' => 'system', 'content' => $systemContext];

            $this->logToFile($logFile, "SYSTEM CONTEXT:");
            $this->logToFile($logFile, "  - System Instructions Length: " . strlen($assistant->system_instructions) . " chars");
            $this->logToFile($logFile, "  - Processed Content Length: " . strlen($assistant->processed_content ?? '') . " chars");
            $this->logToFile($logFile, "  - Total Context Length: " . strlen($systemContext) . " chars");

            // Add conversation history if provided
            if (!empty($conversationHistory)) {
                $this->logToFile($logFile, "CONVERSATION HISTORY:");
                foreach ($conversationHistory as $index => $historyMessage) {
                    if (isset($historyMessage['role']) && isset($historyMessage['content'])) {
                        $messages[] = [
                            'role' => $historyMessage['role'],
                            'content' => $historyMessage['content']
                        ];
                        $this->logToFile($logFile, "  [$index] " . strtoupper($historyMessage['role']) . ": " . substr($historyMessage['content'], 0, 100) . "...");
                    }
                }
            }

            // Add current user message
            $messages[] = ['role' => 'user', 'content' => $message];

            // Configure chat parameters
            $config->temperature = $assistant->temperature;
            $config->maxTokens = $assistant->max_tokens;

            // Log what's being sent to OpenAI
            $this->logToFile($logFile, "SENDING TO OPENAI:");
            $this->logToFile($logFile, "  - Model: " . $assistant->model);
            $this->logToFile($logFile, "  - Temperature: " . $assistant->temperature);
            $this->logToFile($logFile, "  - Max Tokens: " . $assistant->max_tokens);
            $this->logToFile($logFile, "  - Total Messages: " . count($messages));
            $this->logToFile($logFile, "  - API Call Time: " . now()->format('H:i:s'));
            $this->logToFile($logFile, "");
            $this->logToFile($logFile, "MESSAGES BEING SENT TO OPENAI:");
            foreach ($messages as $idx => $msg) {
                $role = $msg['role'] ?? 'unknown';
                $content = $msg['content'] ?? '';
                $preview = strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                $this->logToFile($logFile, "  Message " . ($idx + 1) . " [{$role}]: " . $preview);
            }

            // Generate response
            $apiStartTime = microtime(true);
            $response = $chat->generateChat($messages);
            $apiEndTime = microtime(true);
            $apiDuration = round(($apiEndTime - $apiStartTime) * 1000, 2);

            $this->logToFile($logFile, "");
            $this->logToFile($logFile, "OPENAI RESPONSE RECEIVED:");
            $this->logToFile($logFile, "  - Response Time: " . $apiDuration . " ms");
            $this->logToFile($logFile, "  - Response Length: " . strlen($response) . " chars");
            $this->logToFile($logFile, "  - Response Preview: " . substr($response, 0, 200) . "...");
            $this->logToFile($logFile, "");
            $this->logToFile($logFile, "FULL AI RESPONSE:");
            $this->logToFile($logFile, "---");
            $this->logToFile($logFile, $response);
            $this->logToFile($logFile, "---");

            // Convert markdown formatting to WhatsApp formatting
            $formattedResponse = $this->convertMarkdownToWhatsApp($response);

            $result = [
                'status' => true,
                'message' => $formattedResponse,
                'assistant_name' => $assistant->name,
                'model_used' => $assistant->model,
                'tokens_used' => $assistant->max_tokens, // Approximate
            ];

            $this->logToFile($logFile, "FINAL RESPONSE TO USER:");
            $this->logToFile($logFile, "  - Status: SUCCESS");
            $this->logToFile($logFile, "  - Assistant: " . $assistant->name);
            $this->logToFile($logFile, "  - Model: " . $assistant->model);
            $this->logToFile($logFile, "  - Message: " . $response);
            $this->logToFile($logFile, "[$timestamp] PERSONAL AI ASSISTANT - REQUEST END (SUCCESS)");
            $this->logToFile($logFile, "================================================================================\n");

            return $result;

        } catch (\Throwable $th) {
            $this->logToFile($logFile, "EXCEPTION OCCURRED:");
            $this->logToFile($logFile, "  - Error: " . $th->getMessage());
            $this->logToFile($logFile, "  - File: " . $th->getFile());
            $this->logToFile($logFile, "  - Line: " . $th->getLine());
            $this->logToFile($logFile, "  - Trace:");
            $this->logToFile($logFile, $th->getTraceAsString());
            $this->logToFile($logFile, "");
            $this->logToFile($logFile, "FINAL RESPONSE TO USER:");
            $this->logToFile($logFile, "  - Status: ERROR");
            $this->logToFile($logFile, "  - Message: Assistant temporarily unavailable: " . $th->getMessage());
            $this->logToFile($logFile, "[$timestamp] PERSONAL AI ASSISTANT - REQUEST END (ERROR)");
            $this->logToFile($logFile, "================================================================================\n");

            whatsapp_log('Personal Assistant Error', 'error', [
                'error' => $th->getMessage(),
                'message' => $message,
            ], $th);

            return [
                'status' => false,
                'message' => 'Assistant temporarily unavailable: ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Use OpenAI Assistants API with threads
     */
    protected function useOpenAIAssistantsAPI(
        PersonalAssistant $assistant,
        string $message,
        array $conversationHistory,
        $logFile,
        $timestamp,
        ?int $contactId = null,
        ?string $contactPhone = null,
        ?int $tenantId = null
    ): array {
        try {
            // Get tenant ID for API key retrieval
            $tenantId = null;
            if (property_exists($this, 'wa_tenant_id') && !empty($this->wa_tenant_id)) {
                $tenantId = $this->wa_tenant_id;
            } elseif (function_exists('tenant_id')) {
                $tenantId = tenant_id();
            }

            $apiKey = $this->getOpenAiKey($tenantId);

            if (empty($apiKey)) {
                $this->logToFile($logFile, "ERROR: OpenAI API key is not configured");
                $this->logToFile($logFile, "  - Tenant ID Used: " . ($tenantId ?? 'N/A'));
                $this->logToFile($logFile, "  - Current Tenant Context: " . (tenant_id() ?? 'N/A'));
                $this->logToFile($logFile, "  - Setting Key: whats-mark.openai_secret_key");
                $this->logToFile($logFile, "  - Has wa_tenant_id: " . (property_exists($this, 'wa_tenant_id') ? ($this->wa_tenant_id ?? 'NULL') : 'NO PROPERTY'));

                throw new \Exception('OpenAI API key is not configured. Please configure it in settings.');
            }

            $baseUrl = 'https://api.openai.com/v1';
            $assistantId = $assistant->openai_assistant_id;

            $this->logToFile($logFile, "OPENAI ASSISTANTS API CALL:");
            $this->logToFile($logFile, "  - Assistant ID: " . $assistantId);
            $this->logToFile($logFile, "  - User Message: " . $message);
            $this->logToFile($logFile, "  - Message Length: " . strlen($message) . " chars");
            $this->logToFile($logFile, "  - Conversation History: " . count($conversationHistory) . " messages");
            $this->logToFile($logFile, "  - Contact ID: " . ($contactId ?? 'N/A'));
            $this->logToFile($logFile, "  - Contact Phone: " . ($contactPhone ?? 'N/A'));

            // Step 1: Get or create OpenAI thread for this contact
            $threadId = null;
            $aiConversation = null;

            if ($contactId && $tenantId) {
                // Try to get existing conversation with OpenAI thread_id stored in conversation_data
                $aiConversation = \App\Models\Tenant\AiConversation::where('tenant_id', $tenantId)
                    ->where('contact_id', $contactId)
                    ->where('is_active', true)
                    ->where('last_activity_at', '>', now()->subHours(24)) // Use same thread for 24 hours
                    ->first();

                if ($aiConversation) {
                    $conversationData = $aiConversation->conversation_data ?? [];
                    $threadId = $conversationData['openai_thread_id'] ?? null;

                    if ($threadId) {
                        $this->logToFile($logFile, "  - Reusing existing OpenAI Thread ID: " . $threadId);
                        $this->logToFile($logFile, "  - Conversation ID: " . $aiConversation->id);
                    }
                }
            }

            // If no existing thread, create a new one
            if (!$threadId) {
                $this->logToFile($logFile, "  - Creating new OpenAI thread...");
                $threadResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2',
                ])->post("{$baseUrl}/threads", []);

                if (!$threadResponse->successful()) {
                    throw new \Exception('Failed to create thread: ' . $threadResponse->body());
                }

                $threadData = $threadResponse->json();
                $threadId = $threadData['id'] ?? null;

                if (!$threadId) {
                    throw new \Exception('Thread ID not returned from OpenAI');
                }

                $this->logToFile($logFile, "  - New OpenAI Thread ID: " . $threadId);

                // Store the OpenAI thread_id in the conversation record
                if ($contactId && $tenantId) {
                    if (!$aiConversation) {
                        // Create new conversation record
                        $conversationData = [
                            'messages' => [],
                            'openai_thread_id' => $threadId,
                        ];

                        $aiConversation = \App\Models\Tenant\AiConversation::create([
                            'tenant_id' => $tenantId,
                            'contact_id' => $contactId,
                            'contact_phone' => $contactPhone ?? '',
                            'thread_id' => 'conv_' . uniqid(), // Internal conversation ID
                            'system_prompt' => $assistant->getFullSystemContext(),
                            'conversation_data' => $conversationData,
                            'last_activity_at' => now(),
                            'expires_at' => now()->addHours(24),
                            'is_active' => true,
                            'message_count' => 0,
                            'total_tokens_used' => 0,
                        ]);
                    } else {
                        // Update existing conversation with OpenAI thread_id
                        $conversationData = $aiConversation->conversation_data ?? [];
                        $conversationData['openai_thread_id'] = $threadId;

                        $aiConversation->update([
                            'conversation_data' => $conversationData,
                            'last_activity_at' => now(),
                            'expires_at' => now()->addHours(24),
                        ]);
                    }
                    $this->logToFile($logFile, "  - Stored OpenAI Thread ID in conversation record");
                }
            }

            // Step 2: Add conversation history to thread (only if this is a new thread)
            // If we're reusing an existing thread, it already has all the messages
            if (!$aiConversation && !empty($conversationHistory)) {
                $this->logToFile($logFile, "ADDING CONVERSATION HISTORY TO NEW THREAD:");
                foreach ($conversationHistory as $historyMessage) {
                    if (isset($historyMessage['role']) && isset($historyMessage['content']) && $historyMessage['role'] !== 'system') {
                        $role = $historyMessage['role'] === 'assistant' ? 'assistant' : 'user';
                        \Illuminate\Support\Facades\Http::withHeaders([
                            'Authorization' => 'Bearer ' . $apiKey,
                            'Content-Type' => 'application/json',
                            'OpenAI-Beta' => 'assistants=v2',
                        ])->post("{$baseUrl}/threads/{$threadId}/messages", [
                                    'role' => $role,
                                    'content' => $historyMessage['content'],
                                ]);
                    }
                }
            } elseif ($aiConversation) {
                $this->logToFile($logFile, "SKIPPING CONVERSATION HISTORY - Using existing thread with full context");
            }

            // Step 3: Add current user message to thread
            \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$baseUrl}/threads/{$threadId}/messages", [
                        'role' => 'user',
                        'content' => $message,
                    ]);

            // Step 4: Run the assistant on the thread
            $this->logToFile($logFile, "RUNNING ASSISTANT ON THREAD...");
            $runRequestData = [
                'assistant_id' => $assistantId,
            ];

            // Note: max_completion_tokens is not a valid parameter for Assistants API
            // The max_tokens setting is handled by the model itself

            $runResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$baseUrl}/threads/{$threadId}/runs", $runRequestData);

            if (!$runResponse->successful()) {
                throw new \Exception('Failed to run assistant: ' . $runResponse->body());
            }

            $runData = $runResponse->json();
            $runId = $runData['id'] ?? null;

            if (!$runId) {
                throw new \Exception('Run ID not returned from OpenAI');
            }

            $this->logToFile($logFile, "  - Run ID: " . $runId);

            // Step 5: Poll for completion
            $maxAttempts = 30;
            $attempt = 0;
            $status = 'queued';

            while ($status !== 'completed' && $status !== 'failed' && $attempt < $maxAttempts) {
                sleep(1);
                $attempt++;

                $statusResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'OpenAI-Beta' => 'assistants=v2',
                ])->get("{$baseUrl}/threads/{$threadId}/runs/{$runId}");

                if (!$statusResponse->successful()) {
                    throw new \Exception('Failed to check run status: ' . $statusResponse->body());
                }

                $statusData = $statusResponse->json();
                $status = $statusData['status'] ?? 'unknown';

                $this->logToFile($logFile, "  - Run Status (Attempt {$attempt}): " . $status);
            }

            if ($status !== 'completed') {
                throw new \Exception("Run did not complete. Status: {$status}");
            }

            // Step 6: Retrieve messages from thread
            $messagesResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$baseUrl}/threads/{$threadId}/messages");

            if (!$messagesResponse->successful()) {
                throw new \Exception('Failed to retrieve messages: ' . $messagesResponse->body());
            }

            $messagesData = $messagesResponse->json();
            $messages = $messagesData['data'] ?? [];

            // Get the latest assistant message
            $response = '';
            foreach ($messages as $msg) {
                if (isset($msg['role']) && $msg['role'] === 'assistant') {
                    if (isset($msg['content'][0]['text']['value'])) {
                        $response = $msg['content'][0]['text']['value'];
                        break;
                    }
                }
            }

            if (empty($response)) {
                throw new \Exception('No response from assistant');
            }

            $this->logToFile($logFile, "");
            $this->logToFile($logFile, "OPENAI ASSISTANTS API RESPONSE RECEIVED:");
            $this->logToFile($logFile, "  - Thread ID: " . $threadId);
            $this->logToFile($logFile, "  - Run ID: " . $runId);
            $this->logToFile($logFile, "  - Response Length: " . strlen($response) . " chars");
            $this->logToFile($logFile, "  - Response Preview: " . substr($response, 0, 200) . "...");
            $this->logToFile($logFile, "");
            $this->logToFile($logFile, "FULL AI RESPONSE:");
            $this->logToFile($logFile, "---");
            $this->logToFile($logFile, $response);
            $this->logToFile($logFile, "---");

            // Convert markdown formatting to WhatsApp formatting
            $formattedResponse = $this->convertMarkdownToWhatsApp($response);

            // Update conversation record with last activity
            if ($aiConversation) {
                $aiConversation->update([
                    'last_activity_at' => now(),
                ]);
            }

            $result = [
                'status' => true,
                'message' => $formattedResponse,
                'assistant_name' => $assistant->name,
                'model_used' => $assistant->model,
                'tokens_used' => $assistant->max_tokens, // Approximate
            ];

            $this->logToFile($logFile, "FINAL RESPONSE TO USER:");
            $this->logToFile($logFile, "  - Status: SUCCESS");
            $this->logToFile($logFile, "  - Assistant: " . $assistant->name);
            $this->logToFile($logFile, "  - Model: " . $assistant->model);
            $this->logToFile($logFile, "  - Message: " . $response);
            $this->logToFile($logFile, "[$timestamp] PERSONAL AI ASSISTANT - REQUEST END (SUCCESS - ASSISTANTS API)");
            $this->logToFile($logFile, "================================================================================\n");

            return $result;

        } catch (\Exception $e) {
            $this->logToFile($logFile, "OPENAI ASSISTANTS API ERROR:");
            $this->logToFile($logFile, "  - Error: " . $e->getMessage());
            $this->logToFile($logFile, "[$timestamp] PERSONAL AI ASSISTANT - REQUEST END (ERROR - ASSISTANTS API)");
            $this->logToFile($logFile, "================================================================================\n");

            // Fallback to Chat Completions API
            $this->logToFile($logFile, "FALLING BACK TO CHAT COMPLETIONS API...");

            $config = new OpenAIConfig;
            $config->apiKey = $this->getOpenAiKey();
            $config->model = $assistant->model;
            $config->temperature = $assistant->temperature;
            $config->maxTokens = $assistant->max_tokens;

            $chat = new OpenAIChat($config);

            $messages = [];
            $systemContext = $assistant->getFullSystemContext();
            $messages[] = ['role' => 'system', 'content' => $systemContext];

            if (!empty($conversationHistory)) {
                foreach ($conversationHistory as $historyMessage) {
                    if (isset($historyMessage['role']) && isset($historyMessage['content'])) {
                        $messages[] = [
                            'role' => $historyMessage['role'],
                            'content' => $historyMessage['content']
                        ];
                    }
                }
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            try {
                $response = $chat->generateChat($messages);

                return [
                    'status' => true,
                    'message' => $response,
                    'assistant_name' => $assistant->name,
                    'model_used' => $assistant->model,
                    'tokens_used' => $assistant->max_tokens,
                ];
            } catch (\Exception $fallbackError) {
                return [
                    'status' => false,
                    'message' => 'Assistant temporarily unavailable: ' . $fallbackError->getMessage(),
                ];
            }
        }
    }

    /**
     * Log to dedicated file
     */
    private function logToFile($filePath, $message)
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
     * Get personal assistant info for current tenant
     */
    public function getPersonalAssistantInfo(): ?array
    {
        $assistant = PersonalAssistant::getForCurrentTenant();

        if (!$assistant) {
            return null;
        }

        return [
            'id' => $assistant->id,
            'name' => $assistant->name,
            'description' => $assistant->description,
            'model' => $assistant->model,
            'is_active' => $assistant->is_active,
            'has_files' => $assistant->hasUploadedFiles(),
            'file_count' => $assistant->getFileCount(),
            'use_cases' => $assistant->getUseCaseBadges(),
        ];
    }

    /**
     * Check if personal assistant is available and configured
     */
    public function hasPersonalAssistant(): bool
    {
        $assistant = PersonalAssistant::getForCurrentTenant();
        return $assistant && $assistant->is_active;
    }

    /**
     * Convert markdown formatting to WhatsApp formatting
     * 
     * Converts:
     * - **text** (markdown bold) → *text* (WhatsApp bold)
     * - `text` (markdown code) → ```text``` (WhatsApp monospace)
     * - ~~text~~ (markdown strikethrough) → ~text~ (WhatsApp strikethrough)
     * 
     * @param string $text Text with markdown formatting
     * @return string Text with WhatsApp formatting
     */
    protected function convertMarkdownToWhatsApp(string $text): string
    {
        // Convert markdown bold **text** to WhatsApp bold *text*
        $text = preg_replace('/\*\*(.+?)\*\*/', '*$1*', $text);

        // Convert markdown code `text` to WhatsApp monospace ```text```
        $text = preg_replace('/`([^`]+)`/', '```$1```', $text);

        // Convert markdown strikethrough ~~text~~ to WhatsApp strikethrough ~text~
        $text = preg_replace('/~~(.+?)~~/', '~$1~', $text);

        return $text;
    }
}
