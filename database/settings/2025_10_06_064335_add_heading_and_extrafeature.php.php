<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        // Hero Section
        'theme.hero_heading' => 'Empower Your Business with Our Smart Solutions',

        // Feature 2
        'theme.feature_two_enabled' => true,
        'theme.feature_title_two' => 'No-code, drag-and-drop Bot Flow Builder',
        'theme.feature_subtitle_two' => 'Create AI-powered WhatsApp chatbots easily with our no-code drag & drop flow builder. Automate chats, capture leads, and engage customers 24 7.',
        'theme.feature_description_two' => 'all without writing a single line of code.',
        'theme.feature_list_two' => ['Free up your team! Automatically reply to customer messages on WhatsApp using AI.', 'Transfer complex cases to human agents for better support and customer experience.', 'Automatically trigger custom message flows based on keywords messages for maximum engagement.', 'Keep your WhatsApp active round-the-clock with automated responses and lead collection.'],
        'theme.feature_image_two' => '',

        // Feature 3
        'theme.feature_three_enabled' => true,
        'theme.feature_title_three' => 'Ecommerce Integration & Personal AI Assistant & Knowledgebase',
        'theme.feature_subtitle_three' => 'no team require, ai will manage it everything',
        'theme.feature_description_three' => 'Trigger WhatsApp messages instantly with ecommerce events. Automate cart, checkout, and payment notifications for higher engagement and conversions.',
        'theme.feature_list_three' => ['Automate WhatsApp Notifications with Ecommerce Webhooks', 'AI WhatsApp Assistant | Automate Conversations & Support', 'Engage Customers Smarter with Personal AI Assistant & Knowledgebase', 'Answer common queries instantly with AI-powered responses.'],
        'theme.feature_image_three' => '',

    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->settings) as $key) {
            if ($this->migrator->exists($key)) {
                $this->migrator->delete($key);
            }
        }
    }
};
