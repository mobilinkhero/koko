<?php

return [
    'feature_model_mappings' => [
        'canned_replies' => \App\Models\Tenant\CannedReply::class,
        'ai_prompts' => \App\Models\Tenant\AiPrompt::class,
        'contacts' => \App\Models\Tenant\Contact::class,
        'campaigns' => \App\Models\Tenant\Campaign::class,
        'message_bots' => \App\Models\Tenant\MessageBot::class,
        'template_bots' => \App\Models\Tenant\TemplateBot::class,
        'staff' => \App\Models\User::class,
        'conversation' => \App\Models\Tenant\ChatMessage::class,
        'bot_flow' => \App\Models\Tenant\BotFlow::class,
    ],
];
