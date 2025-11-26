<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use App\Rules\PurifiedInput;
use Livewire\Component;

class WebHooksSettings extends Component
{
    public ?bool $enable_webhook_resend = false;

    public ?string $webhook_resend_method = '';

    public ?string $whatsapp_data_resend_to = '';

    public ?bool $only_agents_can_chat = false;

    public array $webhook_selected_fields = [];

    /**
     * Get available webhook event fields as a computed property
     */
    public function getAvailableFieldsProperty()
    {
        return [
            // Account related events
            ['value' => 'account_alerts', 'label' => 'Account Alerts'],
            ['value' => 'account_review_update', 'label' => 'Account Review Update'],
            ['value' => 'account_settings_update', 'label' => 'Account Settings Update'],
            ['value' => 'account_update', 'label' => 'Account Update'],

            // Automatic events
            ['value' => 'automatic_events', 'label' => 'Automatic Events'],

            // Business related events
            ['value' => 'business_capability_update', 'label' => 'Business Capability Update'],
            ['value' => 'business_status_update', 'label' => 'Business Status Update'],

            // Calls
            ['value' => 'calls', 'label' => 'Calls'],

            // Flows
            ['value' => 'flows', 'label' => 'Flows'],

            // Group events
            ['value' => 'group_lifecycle_update', 'label' => 'Group Lifecycle Update'],
            ['value' => 'group_participants_update', 'label' => 'Group Participants Update'],
            ['value' => 'group_settings_update', 'label' => 'Group Settings Update'],
            ['value' => 'group_status_update', 'label' => 'Group Status Update'],

            // History
            ['value' => 'history', 'label' => 'History'],

            // Message events
            ['value' => 'message_echoes', 'label' => 'Message Echoes'],
            ['value' => 'message_template_components_update', 'label' => 'Message Template Components Update'],
            ['value' => 'message_template_quality_update', 'label' => 'Message Template Quality Update'],
            ['value' => 'message_template_status_update', 'label' => 'Message Template Status Update'],
            ['value' => 'messages', 'label' => 'Messages'],
            ['value' => 'messaging_handovers', 'label' => 'Messaging Handovers'],

            // Partner solutions
            ['value' => 'partner_solutions', 'label' => 'Partner Solutions'],

            // Payment events
            ['value' => 'payment_configuration_update', 'label' => 'Payment Configuration Update'],

            // Phone number events
            ['value' => 'phone_number_name_update', 'label' => 'Phone Number Name Update'],
            ['value' => 'phone_number_quality_update', 'label' => 'Phone Number Quality Update'],

            // Security
            ['value' => 'security', 'label' => 'Security'],

            // Web app data sync
            ['value' => 'web_app_data_sync', 'label' => 'Web App Data Sync'],

            // Web message echoes
            ['value' => 'web_message_echoes', 'label' => 'Web Message Echoes'],

            // Template category update
            ['value' => 'template_category_update', 'label' => 'Template Category Update'],

            // Tracking events
            ['value' => 'tracking_events', 'label' => 'Tracking Events'],

            // User preferences
            ['value' => 'user_preferences', 'label' => 'User Preferences'],
        ];
    }

    protected function rules()
    {
        return [
            'enable_webhook_resend' => 'nullable|boolean',
            'webhook_resend_method' => [
                'nullable',
                'string',
                'max:255',
                new PurifiedInput(t('sql_injection_error')),
                'required_if:enable_webhook_resend,true',
            ],
            'whatsapp_data_resend_to' => [
                'nullable',
                'url',
                'max:255',
                new PurifiedInput(t('sql_injection_error')),
                'required_if:enable_webhook_resend,true',
            ],
            'only_agents_can_chat' => 'nullable|boolean',
            'webhook_selected_fields' => 'nullable|array',
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');
        $this->enable_webhook_resend = $settings['enable_webhook_resend'] ?? false;
        $this->only_agents_can_chat = $settings['only_agents_can_chat'] ?? false;
        $this->webhook_resend_method = $settings['webhook_resend_method'] ?? null;
        $this->whatsapp_data_resend_to = $settings['whatsapp_data_resend_to'] ?? null;
        $this->webhook_selected_fields = is_array($settings['webhook_selected_fields'] ?? null)
            ? $settings['webhook_selected_fields']
            : (is_string($settings['webhook_selected_fields'] ?? null) ? json_decode($settings['webhook_selected_fields'], true) ?? [] : []);
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $settings = tenant_settings_by_group('whats-mark');
            $originalSettings = [
                'enable_webhook_resend' => $settings['enable_webhook_resend'] ?? false,
                'only_agents_can_chat' => $settings['only_agents_can_chat'] ?? false,
                'webhook_resend_method' => $settings['webhook_resend_method'] ?? null,
                'whatsapp_data_resend_to' => $settings['whatsapp_data_resend_to'] ?? null,
                'webhook_selected_fields' => is_array($settings['webhook_selected_fields'] ?? null)
                    ? $settings['webhook_selected_fields']
                    : (is_string($settings['webhook_selected_fields'] ?? null) ? json_decode($settings['webhook_selected_fields'], true) ?? [] : []),
            ];

            $modifiedSettings = [];
            foreach ($originalSettings as $key => $originalValue) {
                $newValue = $this->{$key};
                if (! array_key_exists($key, $settings) || $originalValue !== $newValue) {
                    $modifiedSettings[$key] = $newValue;
                }
            }

            if (! empty($modifiedSettings)) {
                foreach ($modifiedSettings as $key => $value) {
                    save_tenant_setting('whats-mark', $key, $value);
                }
                $this->notify([
                    'type' => 'success',
                    'message' => t('setting_save_successfully'),
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.settings.whats-mark.web-hooks-settings');
    }
}
