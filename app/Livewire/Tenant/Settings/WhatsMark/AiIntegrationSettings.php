<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use App\Rules\PurifiedInput;
use App\Traits\Ai;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AiIntegrationSettings extends Component
{
    use Ai;

    public ?bool $enable_openai_in_chat = false;

    public ?string $openai_secret_key = '';

    public ?string $chat_model = '';

    public ?array $chatGptModels = [];

    protected function rules()
    {
        return [
            'enable_openai_in_chat' => 'nullable|boolean',

            'openai_secret_key' => [
                'nullable',
                'string',
                'max:255',
                new PurifiedInput(t('sql_injection_error')),
                'required_if:enable_openai_in_chat,true',
            ],

            'chat_model' => [
                'nullable',
                'string',
                Rule::in(array_column($this->chatGptModels, 'id')),
                'required_if:enable_openai_in_chat,true',
            ],
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');

        $this->enable_openai_in_chat = $settings['enable_openai_in_chat'] ?? false;
        $this->openai_secret_key = $settings['openai_secret_key'] ?? null;
        $this->chat_model = $settings['chat_model'] ?? null;

        $this->chatGptModels = config('aimodel.models');
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $originalSettings = tenant_settings_by_group('whats-mark');

            $newSettings = [
                'enable_openai_in_chat' => $this->enable_openai_in_chat,
                'openai_secret_key' => $this->openai_secret_key,
                'chat_model' => $this->chat_model,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return ! array_key_exists($key, $originalSettings) || $originalSettings[$key] !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (
                isset($this->openai_secret_key) &&
                (! isset($originalSettings['openai_secret_key']) || $originalSettings['openai_secret_key'] !== $this->openai_secret_key || ($originalSettings['is_open_ai_key_verify'] ?? false) == false)
            ) {

                save_tenant_setting('whats-mark', 'openai_secret_key', $this->openai_secret_key);

                $response = $this->listModel();

                if (! $response['status']) {
                    $this->notify(['type' => 'danger', 'message' => $response['message']]);

                    return;
                }
            }

            // Save the rest of the modified settings
            foreach ($modifiedSettings as $key => $value) {
                // Skip openai_secret_key if already handled
                if ($key === 'openai_secret_key') {
                    continue;
                }

                save_tenant_setting('whats-mark', $key, $value);
            }

            $this->notify([
                'type' => 'success',
                'message' => t('setting_save_successfully'),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.settings.whats-mark.ai-integration-settings');
    }
}
