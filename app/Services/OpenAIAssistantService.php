<?php

namespace App\Services;

use App\Models\PersonalAssistant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OpenAIAssistantService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = get_tenant_setting_from_db('whats-mark', 'openai_secret_key');
        
        if (!$this->apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }
    }

    /**
     * Create or update an OpenAI assistant
     */
    public function createOrUpdateAssistant(PersonalAssistant $assistant): array
    {
        try {
            $assistantId = $assistant->openai_assistant_id;
            
            $data = [
                'name' => $assistant->name,
                'instructions' => $assistant->getFullSystemContext(),
                'model' => $assistant->model,
                'temperature' => $assistant->temperature,
                // Note: max_tokens is set at the run level, not assistant creation level
            ];

            // If assistant already exists, update it
            if ($assistantId) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2',
                ])->post("{$this->baseUrl}/assistants/{$assistantId}", $data);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'assistant_id' => $assistantId,
                        'data' => $response->json(),
                    ];
                } else {
                    // If update fails, create new one
                    Log::warning("Failed to update assistant {$assistantId}, creating new one", [
                        'error' => $response->body(),
                    ]);
                    $assistantId = null;
                }
            }

            // Create new assistant
            if (!$assistantId) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2',
                ])->post("{$this->baseUrl}/assistants", $data);

                if (!$response->successful()) {
                    throw new \Exception('Failed to create assistant: ' . $response->body());
                }

                $responseData = $response->json();
                $assistantId = $responseData['id'] ?? null;

                if (!$assistantId) {
                    throw new \Exception('Assistant ID not returned from OpenAI');
                }
            }

            return [
                'success' => true,
                'assistant_id' => $assistantId,
                'data' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Assistant Creation Error', [
                'assistant_id' => $assistant->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a vector store for file uploads
     */
    public function createVectorStore(PersonalAssistant $assistant): array
    {
        try {
            $vectorStoreId = $assistant->openai_vector_store_id;

            // If vector store already exists, return it
            if ($vectorStoreId) {
                // Verify it still exists
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'OpenAI-Beta' => 'assistants=v2',
                ])->get("{$this->baseUrl}/vector_stores/{$vectorStoreId}");

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'vector_store_id' => $vectorStoreId,
                    ];
                } else {
                    // Vector store doesn't exist, create new one
                    $vectorStoreId = null;
                }
            }

            // Create new vector store
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/vector_stores", [
                'name' => $assistant->name . ' - Vector Store',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to create vector store: ' . $response->body());
            }

            $responseData = $response->json();
            $vectorStoreId = $responseData['id'] ?? null;

            if (!$vectorStoreId) {
                throw new \Exception('Vector store ID not returned from OpenAI');
            }

            return [
                'success' => true,
                'vector_store_id' => $vectorStoreId,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Vector Store Creation Error', [
                'assistant_id' => $assistant->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload files to vector store
     */
    public function uploadFilesToVectorStore(PersonalAssistant $assistant, string $vectorStoreId): array
    {
        try {
            $uploadedFiles = [];
            $files = $assistant->getFilesWithStatus();

            foreach ($files as $file) {
                if (!isset($file['path']) || !$file['exists']) {
                    continue;
                }

                try {
                    // Step 1: Upload file to OpenAI
                    $fileContent = Storage::get($file['path']);
                    $fileName = $file['original_name'] ?? basename($file['path']);

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                    ])->attach(
                        'file',
                        $fileContent,
                        $fileName
                    )->post("{$this->baseUrl}/files", [
                        'purpose' => 'assistants',
                    ]);

                    if (!$response->successful()) {
                        Log::warning("Failed to upload file to OpenAI", [
                            'file' => $fileName,
                            'error' => $response->body(),
                        ]);
                        continue;
                    }

                    $fileData = $response->json();
                    $openaiFileId = $fileData['id'] ?? null;

                    if (!$openaiFileId) {
                        continue;
                    }

                    // Step 2: Add file to vector store
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                        'OpenAI-Beta' => 'assistants=v2',
                    ])->post("{$this->baseUrl}/vector_stores/{$vectorStoreId}/files", [
                        'file_id' => $openaiFileId,
                    ]);

                    if ($response->successful()) {
                        $uploadedFiles[] = [
                            'original_name' => $fileName,
                            'openai_file_id' => $openaiFileId,
                            'status' => 'synced',
                        ];
                    } else {
                        Log::warning("Failed to add file to vector store", [
                            'file' => $fileName,
                            'error' => $response->body(),
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error("Error uploading file to OpenAI", [
                        'file' => $file['original_name'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'success' => true,
                'files_uploaded' => count($uploadedFiles),
                'files' => $uploadedFiles,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI File Upload Error', [
                'assistant_id' => $assistant->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Attach vector store to assistant
     */
    public function attachVectorStoreToAssistant(string $assistantId, string $vectorStoreId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/assistants/{$assistantId}", [
                'tool_resources' => [
                    'file_search' => [
                        'vector_store_ids' => [$vectorStoreId],
                    ],
                ],
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('OpenAI Attach Vector Store Error', [
                'assistant_id' => $assistantId,
                'vector_store_id' => $vectorStoreId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sync assistant with OpenAI (complete process)
     */
    public function syncAssistant(PersonalAssistant $assistant): array
    {
        try {
            // Step 1: Create or update assistant
            $assistantResult = $this->createOrUpdateAssistant($assistant);
            if (!$assistantResult['success']) {
                return $assistantResult;
            }

            $openaiAssistantId = $assistantResult['assistant_id'];

            // Step 2: Create vector store
            $vectorStoreResult = $this->createVectorStore($assistant);
            if (!$vectorStoreResult['success']) {
                return $vectorStoreResult;
            }

            $vectorStoreId = $vectorStoreResult['vector_store_id'];

            // Step 3: Upload files to vector store (if any)
            $fileUploadResult = ['success' => true, 'files_uploaded' => 0];
            if ($assistant->hasUploadedFiles()) {
                $fileUploadResult = $this->uploadFilesToVectorStore($assistant, $vectorStoreId);
            }

            // Step 4: Attach vector store to assistant
            $this->attachVectorStoreToAssistant($openaiAssistantId, $vectorStoreId);

            // Step 5: Update assistant record
            $assistant->update([
                'openai_assistant_id' => $openaiAssistantId,
                'openai_vector_store_id' => $vectorStoreId,
                'last_synced_at' => now(),
            ]);

            return [
                'success' => true,
                'assistant_id' => $openaiAssistantId,
                'vector_store_id' => $vectorStoreId,
                'files_uploaded' => $fileUploadResult['files_uploaded'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Sync Assistant Error', [
                'assistant_id' => $assistant->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

