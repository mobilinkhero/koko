<?php

namespace App\Livewire\Tenant\AI;

use App\Models\PersonalAssistant;
use App\Models\Tenant;
use App\Services\PersonalAssistantFileService;
use App\Services\OpenAIAssistantService;
use App\Services\FeatureService;
use App\Traits\Ai;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonalAssistantManager extends Component
{
    use WithFileUploads, Ai;

    public $assistant;
    public $assistants;
    public $showCreateForm = false;
    public $files = [];
    public $editingAssistantId = null;
    public $showChatModal = false;
    public $chattingAssistantId = null;
    public $chatMessages = [];
    public $currentMessage = '';
    public $showDetailsModal = false;
    public $detailsAssistantId = null;

    // Form fields
    public $name = '';
    public $description = '';
    public $system_instructions = '';
    public $model = 'gpt-4o-mini';
    public $temperature = 0.7;
    public $max_tokens = 1000;
    public $use_case_tags = [];
    public $file_analysis_enabled = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'system_instructions' => 'required|string|max:5000',
        'model' => 'required|string|in:gpt-3.5-turbo,gpt-3.5-turbo-16k,gpt-4,gpt-4-turbo,gpt-4o-mini',
        'temperature' => 'required|numeric|between:0,2',
        'max_tokens' => 'required|integer|between:100,4000',
        'use_case_tags' => 'array|max:5',
        'use_case_tags.*' => 'string|in:faq,product,onboarding,csv,sop,general',
        'file_analysis_enabled' => 'boolean',
        'files.*' => 'file|max:5120|mimes:txt,md,csv,json,pdf,doc,docx', // 5MB max
    ];

    protected $messages = [
        'files.*.max' => 'Each file must be smaller than 5MB',
        'files.*.mimes' => 'Only text, markdown, CSV, JSON files are supported currently',
    ];

    /**
     * Get the current tenant using session-based context with fallback
     */
    protected function getCurrentTenant()
    {
        try {
            // Primary method: Use session-based tenant identification
            $tenantId = session('current_tenant_id');

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant instanceof Tenant) {
                    return $tenant;
                }
            }

            // Fallback method: Use traditional tenant context
            if (Tenant::checkCurrent()) {
                $tenant = Tenant::current();
                if ($tenant instanceof Tenant) {
                    // Sync session with current tenant for consistency
                    session(['current_tenant_id' => $tenant->id]);
                    return $tenant;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function mount(FeatureService $featureService)
    {
        // Check if user has access to AI Assistant feature
        if (!$featureService->hasAccess('ai_assistant')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - AI Assistant feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->loadAssistant();
    }

    public function loadAssistant()
    {
        // Load all assistants for current tenant instead of just one
        $this->assistants = PersonalAssistant::getAllForCurrentTenant();
        $this->assistant = $this->assistants->first(); // For compatibility with existing form logic

        if ($this->assistant) {
            $this->name = $this->assistant->name;
            $this->description = $this->assistant->description;
            $this->system_instructions = $this->assistant->system_instructions;
            $this->model = $this->assistant->model;
            $this->temperature = $this->assistant->temperature;
            $this->max_tokens = $this->assistant->max_tokens;
            $this->use_case_tags = $this->assistant->use_case_tags ?? [];
            $this->file_analysis_enabled = $this->assistant->file_analysis_enabled;
        } else {
            $this->resetForm();
        }
    }

    public function createAssistant()
    {
        $this->resetForm();
        $this->showCreateForm = true;

        // Set default instructions based on use cases
        $this->system_instructions = $this->getDefaultInstructions();
    }

    public function editAssistant()
    {
        $this->showCreateForm = true;
    }

    public function cancelForm()
    {
        $this->showCreateForm = false;
        $this->files = [];
        $this->resetErrorBag();
        $this->loadAssistant();
    }

    public function saveAssistant()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'system_instructions' => $this->system_instructions,
                'model' => $this->model,
                'temperature' => $this->temperature,
                'max_tokens' => $this->max_tokens,
                'use_case_tags' => $this->use_case_tags,
                'file_analysis_enabled' => $this->file_analysis_enabled,
            ];

            // Check if we're editing an existing assistant
            if ($this->editingAssistantId) {
                $assistant = PersonalAssistant::find($this->editingAssistantId);
                if ($assistant) {
                    $assistant->update($data);
                } else {
                    throw new \Exception('Assistant not found');
                }
            } else {
                // Create new assistant
                $tenant = PersonalAssistant::getCurrentTenant();
                if (!$tenant) {
                    throw new \Exception('No current tenant found');
                }
                $data['tenant_id'] = $tenant->id;
                $assistant = PersonalAssistant::create($data);
            }

            // Process uploaded files if any
            if (!empty($this->files)) {
                $fileService = new PersonalAssistantFileService();
                $result = $fileService->uploadFiles($assistant, $this->files);

                if (!$result['success']) {
                    throw new \Exception('File processing failed');
                }

                session()->flash('file-upload-success', "Processed {$result['files_processed']} files successfully");
            }

            DB::commit();

            $this->assistant = $assistant;
            $this->showCreateForm = false;
            $this->files = [];

            session()->flash('success', $this->assistant->wasRecentlyCreated ? 'Personal assistant created successfully!' : 'Personal assistant updated successfully!');

            $this->dispatch('assistant-saved');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save assistant: ' . $e->getMessage());
        }
    }

    public function deleteAssistant()
    {
        if (!$this->assistant) {
            return;
        }

        try {
            // Clear all files first
            $fileService = new PersonalAssistantFileService();
            $fileService->clearAllFiles($this->assistant);

            // Delete assistant
            $this->assistant->delete();

            $this->assistant = null;
            session()->flash('success', 'Personal assistant deleted successfully!');

            $this->dispatch('assistant-deleted');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete assistant: ' . $e->getMessage());
        }
    }

    public function removeFile($fileName)
    {
        if (!$this->assistant) {
            return;
        }

        try {
            $fileService = new PersonalAssistantFileService();
            $fileService->removeFile($this->assistant, $fileName);

            $this->loadAssistant(); // Refresh data
            session()->flash('success', 'File removed successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to remove file: ' . $e->getMessage());
        }
    }

    public function clearAllFiles()
    {
        if (!$this->assistant) {
            return;
        }

        try {
            $fileService = new PersonalAssistantFileService();
            $fileService->clearAllFiles($this->assistant);

            $this->loadAssistant(); // Refresh data
            session()->flash('success', 'All files cleared successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to clear files: ' . $e->getMessage());
        }
    }

    public function refreshFileStatus()
    {
        if (!$this->assistant) {
            return;
        }

        // Force refresh of file status
        $this->assistant->refresh();
        session()->flash('success', 'File status refreshed');
    }

    public function toggleAssistant($assistantId = null)
    {
        $assistant = $assistantId ? PersonalAssistant::find($assistantId) : $this->assistant;

        if (!$assistant) {
            return;
        }

        try {
            $assistant->update([
                'is_active' => !$assistant->is_active
            ]);

            $status = $assistant->is_active ? 'activated' : 'deactivated';
            session()->flash('success', "Assistant {$status} successfully!");

            // Refresh the assistant data
            $this->loadAssistant();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to toggle assistant: ' . $e->getMessage());
        }
    }

    public function editSpecificAssistant($assistantId)
    {
        // SECURITY: Verify assistant belongs to current tenant
        $tenant = PersonalAssistant::getCurrentTenant();
        if (!$tenant) {
            session()->flash('error', 'Unable to determine current tenant');
            return;
        }

        $assistant = PersonalAssistant::where('id', $assistantId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$assistant) {
            session()->flash('error', 'Assistant not found or access denied');
            return;
        }

        $this->editingAssistantId = $assistantId;
        $this->assistant = $assistant;

        $this->name = $assistant->name;
        $this->description = $assistant->description;
        $this->system_instructions = $assistant->system_instructions;
        $this->model = $assistant->model;
        $this->temperature = $assistant->temperature;
        $this->max_tokens = $assistant->max_tokens;
        $this->use_case_tags = $assistant->use_case_tags ?? [];
        $this->file_analysis_enabled = $assistant->file_analysis_enabled;

        $this->showCreateForm = true;
    }

    public function deleteSpecificAssistant($assistantId)
    {
        // SECURITY: Verify assistant belongs to current tenant
        $tenant = PersonalAssistant::getCurrentTenant();
        if (!$tenant) {
            session()->flash('error', 'Unable to determine current tenant');
            return;
        }

        $assistant = PersonalAssistant::where('id', $assistantId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$assistant) {
            session()->flash('error', 'Assistant not found or access denied');
            return;
        }

        try {
            // Clear all files first
            $fileService = new PersonalAssistantFileService();
            $fileService->clearAllFiles($assistant);

            // Delete assistant
            $assistant->delete();

            session()->flash('success', 'Assistant deleted successfully!');
            $this->loadAssistant();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete assistant: ' . $e->getMessage());
        }
    }

    public function syncAssistant($assistantId)
    {
        // SECURITY: Verify assistant belongs to current tenant
        $tenant = PersonalAssistant::getCurrentTenant();
        if (!$tenant) {
            session()->flash('error', 'Unable to determine current tenant');
            return;
        }

        $assistant = PersonalAssistant::where('id', $assistantId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$assistant) {
            session()->flash('error', 'Assistant not found or access denied');
            return;
        }

        try {
            // Use OpenAI Assistant Service to create real assistant
            $service = new OpenAIAssistantService();
            $result = $service->syncAssistant($assistant);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to sync assistant');
            }

            // Update file sync status
            if ($assistant->hasUploadedFiles()) {
                $files = $assistant->uploaded_files ?? [];
                foreach ($files as &$file) {
                    $file['synced'] = true;
                    $file['sync_status'] = 'synced';
                }
                $assistant->update([
                    'uploaded_files' => $files,
                ]);
            }

            $filesCount = $result['files_uploaded'] ?? 0;
            $message = 'Assistant synced successfully with OpenAI!';
            if ($filesCount > 0) {
                $message .= " {$filesCount} file(s) uploaded to vector store.";
            }

            session()->flash('success', $message);
            $this->loadAssistant();

        } catch (\Exception $e) {
            Log::error('Sync Assistant Error', [
                'assistant_id' => $assistantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Failed to sync assistant: ' . $e->getMessage());
        }
    }

    public function openChat($assistantId)
    {
        // SECURITY: Verify assistant belongs to current tenant
        $tenant = PersonalAssistant::getCurrentTenant();
        if (!$tenant) {
            return;
        }

        $assistant = PersonalAssistant::where('id', $assistantId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$assistant) {
            session()->flash('error', 'Assistant not found or access denied');
            return;
        }

        $this->chattingAssistantId = $assistantId;
        $this->showChatModal = true;
        $this->chatMessages = []; // Start with empty chat
        $this->currentMessage = '';
    }

    public function closeChat()
    {
        $this->showChatModal = false;
        $this->chattingAssistantId = null;
        $this->chatMessages = [];
        $this->currentMessage = '';
    }

    public function openDetails($assistantId)
    {
        // SECURITY: Verify assistant belongs to current tenant before opening details
        $tenant = PersonalAssistant::getCurrentTenant();
        if (!$tenant) {
            return;
        }

        $assistant = PersonalAssistant::where('id', $assistantId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$assistant) {
            session()->flash('error', 'Assistant not found or access denied');
            return;
        }

        $this->detailsAssistantId = $assistantId;
        $this->showDetailsModal = true;
    }

    public function closeDetails()
    {
        $this->showDetailsModal = false;
        $this->detailsAssistantId = null;
    }

    public function getAssistantDetails($assistantId)
    {
        $assistant = PersonalAssistant::find($assistantId);
        if (!$assistant) {
            return null;
        }

        // Calculate sync status
        $totalFiles = $assistant->hasUploadedFiles() ? count($assistant->uploaded_files) : 0;
        $syncedFiles = 0;
        if ($assistant->hasUploadedFiles()) {
            foreach ($assistant->uploaded_files as $file) {
                if (isset($file['synced']) && $file['synced']) {
                    $syncedFiles++;
                }
            }
        }
        $syncProgress = $totalFiles > 0 ? round(($syncedFiles / $totalFiles) * 100) : ($assistant->openai_assistant_id ? 100 : 0);
        $overallStatus = $assistant->openai_assistant_id ? 'Fully Synced' : 'Not Synced';

        return [
            'id' => $assistant->id,
            'name' => $assistant->name,
            'model' => $assistant->model,
            'is_active' => $assistant->is_active,
            'openai_assistant_id' => $assistant->openai_assistant_id,
            'openai_vector_store_id' => $assistant->openai_vector_store_id,
            'total_documents' => $totalFiles,
            'synced_documents' => $syncedFiles,
            'sync_progress' => $syncProgress,
            'overall_status' => $overallStatus,
            'last_synced_at' => $assistant->last_synced_at,
            'has_knowledge_base' => $assistant->hasUploadedFiles(),
        ];
    }

    public function clearChat()
    {
        $this->chatMessages = [];
        session()->flash('success', 'Chat history cleared');
    }

    public function sendMessage()
    {
        if (empty(trim($this->currentMessage))) {
            return;
        }

        $assistant = PersonalAssistant::find($this->chattingAssistantId);
        if (!$assistant) {
            return;
        }

        // Add user message
        $this->chatMessages[] = [
            'role' => 'user',
            'content' => $this->currentMessage,
            'timestamp' => now()->format('h:i A')
        ];

        $userMessage = $this->currentMessage;
        $this->currentMessage = '';

        // Show typing indicator
        $this->chatMessages[] = [
            'role' => 'assistant',
            'content' => 'typing',
            'timestamp' => now()->format('h:i A')
        ];

        $this->dispatch('scroll-to-bottom');

        try {
            // Build context from uploaded documents
            $context = $this->buildContextFromDocuments($assistant);

            // Build conversation history for AI
            $messages = [
                [
                    'role' => 'system',
                    'content' => $assistant->system_instructions . "\n\n" .
                        "You have access to the following documents and information:\n" .
                        $context
                ]
            ];

            // Add conversation history (exclude typing indicator)
            foreach ($this->chatMessages as $msg) {
                if ($msg['content'] !== 'typing') {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }

            // Get AI response using the existing AI trait
            $response = $this->getAiResponse(
                $messages,
                $assistant->model,
                $assistant->temperature,
                $assistant->max_tokens
            );

            // Remove typing indicator
            array_pop($this->chatMessages);

            // Add AI response
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => $response,
                'timestamp' => now()->format('h:i A')
            ];

        } catch (\Exception $e) {
            // Remove typing indicator
            array_pop($this->chatMessages);

            // Show error message
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => 'I apologize, but I encountered an error processing your request. Please try again or contact support if the issue persists.',
                'timestamp' => now()->format('h:i A')
            ];

            Log::error('AI Chat Error: ' . $e->getMessage());
        }

        $this->dispatch('scroll-to-bottom');
    }

    private function buildContextFromDocuments($assistant)
    {
        $context = '';

        // Add processed content if available
        if ($assistant->processed_content) {
            $context .= "Processed Knowledge Base:\n" . $assistant->processed_content . "\n\n";
        }

        // Add file information
        if ($assistant->hasUploadedFiles()) {
            $context .= "Available Documents:\n";
            foreach ($assistant->getFilesWithStatus() as $file) {
                $fileContent = isset($file['content']) ? $file['content'] : 'File content available';
                $context .= "- {$file['original_name']}: {$fileContent}\n";
            }
        }

        return $context;
    }

    private function getAiResponse($messages, $model, $temperature, $maxTokens)
    {
        try {
            // Get OpenAI key using the trait's method
            $openaiKey = $this->getOpenAiKey();

            if (!$openaiKey) {
                throw new \Exception('OpenAI API key not configured. Please add your OpenAI API key in the settings.');
            }

            // Use LLPhant OpenAI Chat like in the trait
            $config = new \LLPhant\OpenAIConfig();
            $config->apiKey = $openaiKey;
            $config->model = $model;
            $config->temperature = $temperature;
            $config->maxTokens = $maxTokens;

            $chat = new \LLPhant\Chat\OpenAIChat($config);

            // Generate response
            $response = $chat->generateChat($messages);

            return $response;

        } catch (\Exception $e) {
            Log::error('AI Response Error: ' . $e->getMessage());

            // Return a fallback message instead of throwing
            return 'I apologize, but I encountered an error processing your request. Please ensure your OpenAI API key is configured correctly in the settings.';
        }
    }

    public function updatedUseCaseTags()
    {
        // Auto-update system instructions based on selected use cases
        if (empty($this->system_instructions) || $this->system_instructions === $this->getDefaultInstructions()) {
            $this->system_instructions = $this->getDefaultInstructions();
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->system_instructions = '';
        $this->model = 'gpt-4o-mini';
        $this->temperature = 0.7;
        $this->max_tokens = 1000;
        $this->use_case_tags = [];
        $this->file_analysis_enabled = true;
        $this->files = [];
        $this->editingAssistantId = null;
    }

    private function getDefaultInstructions(): string
    {
        $instructions = "You are a helpful AI assistant";

        if (empty($this->use_case_tags)) {
            return $instructions . " designed to help with various tasks and answer questions based on uploaded documents and data.";
        }

        $useCases = [];
        foreach ($this->use_case_tags as $tag) {
            $useCases[] = match ($tag) {
                'faq' => 'answer frequently asked questions',
                'product' => 'provide product information and handle inquiries',
                'onboarding' => 'guide users through onboarding and setup processes',
                'csv' => 'search and analyze CSV data',
                'sop' => 'help with standard operating procedures and internal guidelines',
                'general' => 'assist with general tasks',
                default => $tag
            };
        }

        $instructions .= " specialized in " . implode(', ', $useCases) . ".";

        $instructions .= "\n\nKey guidelines:";
        $instructions .= "\n- Use the uploaded documents and data as your primary knowledge source";
        $instructions .= "\n- Provide accurate, helpful, and concise responses";
        $instructions .= "\n- If information is not available in the documents, clearly state this";
        $instructions .= "\n- Maintain a professional and friendly tone";
        $instructions .= "\n- For CSV data, provide specific lookups when requested";

        return $instructions;
    }

    public function render()
    {
        return view('livewire.tenant.ai.personal-assistant-manager', [
            'availableModels' => PersonalAssistant::AVAILABLE_MODELS,
            'useCaseOptions' => PersonalAssistant::USE_CASES,
        ]);
    }
}
