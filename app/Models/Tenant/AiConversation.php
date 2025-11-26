<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use Carbon\Carbon;

class AiConversation extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'contact_id', 
        'contact_phone',
        'thread_id',
        'system_prompt',
        'conversation_data',
        'last_activity_at',
        'expires_at',
        'is_active',
        'message_count',
        'total_tokens_used'
    ];

    protected $casts = [
        'conversation_data' => 'array',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get or create conversation for contact
     */
    public static function getOrCreate($tenantId, $contactId, $contactPhone, $systemPrompt): self
    {
        // Look for active conversation for this contact (within last 30 minutes)
        $conversation = self::where('tenant_id', $tenantId)
            ->where('contact_id', $contactId)
            ->where('is_active', true)
            ->where('last_activity_at', '>', now()->subMinutes(30))
            ->first();

        if (!$conversation) {
            // Create new conversation
            $conversation = self::create([
                'tenant_id' => $tenantId,
                'contact_id' => $contactId,
                'contact_phone' => $contactPhone,
                'thread_id' => 'conv_' . uniqid(),
                'system_prompt' => $systemPrompt,
                'conversation_data' => [
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                            'timestamp' => now()->toISOString()
                        ]
                    ]
                ],
                'last_activity_at' => now(),
                'expires_at' => now()->addHours(2),
                'is_active' => true,
                'message_count' => 1,
                'total_tokens_used' => 0
            ]);
        }

        return $conversation;
    }

    /**
     * Add user message to conversation
     */
    public function addUserMessage(string $message): void
    {
        $messages = $this->conversation_data['messages'] ?? [];
        
        $messages[] = [
            'role' => 'user',
            'content' => $message,
            'timestamp' => now()->toISOString()
        ];

        $this->update([
            'conversation_data' => ['messages' => $messages],
            'last_activity_at' => now(),
            'message_count' => count($messages)
        ]);
    }

    /**
     * Add AI response to conversation
     */
    public function addAiResponse(string $response, int $tokensUsed = 0): void
    {
        $messages = $this->conversation_data['messages'] ?? [];
        
        $messages[] = [
            'role' => 'assistant',
            'content' => $response,
            'timestamp' => now()->toISOString(),
            'tokens_used' => $tokensUsed
        ];

        $this->update([
            'conversation_data' => ['messages' => $messages],
            'last_activity_at' => now(),
            'message_count' => count($messages),
            'total_tokens_used' => $this->total_tokens_used + $tokensUsed
        ]);
    }

    /**
     * Get conversation messages for OpenAI API
     */
    public function getMessagesForApi(): array
    {
        return $this->conversation_data['messages'] ?? [];
    }

    /**
     * Close conversation
     */
    public function close(): void
    {
        $this->update([
            'is_active' => false,
            'expires_at' => now()
        ]);
    }

    /**
     * Clean up expired conversations
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Get conversation statistics
     */
    public function getStats(): array
    {
        return [
            'message_count' => $this->message_count,
            'total_tokens' => $this->total_tokens_used,
            'duration_minutes' => $this->last_activity_at->diffInMinutes($this->created_at),
            'is_active' => $this->is_active
        ];
    }
}
