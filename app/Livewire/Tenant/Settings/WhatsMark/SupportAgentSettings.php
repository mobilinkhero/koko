<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use Livewire\Component;

class SupportAgentSettings extends Component
{
    public ?bool $only_agents_can_chat = false;

    protected function rules()
    {
        return [
            'only_agents_can_chat' => 'nullable|boolean',
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');
        $this->only_agents_can_chat = $settings['only_agents_can_chat'] ?? false;
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $settings = tenant_settings_by_group('whats-mark');

            $originalOnlyAgentsCanChat = $settings['only_agents_can_chat'] ?? false;

            $modifiedSettings = [];

            if (! array_key_exists('only_agents_can_chat', $settings) || $originalOnlyAgentsCanChat !== $this->only_agents_can_chat) {
                $modifiedSettings['only_agents_can_chat'] = $this->only_agents_can_chat;
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
        return view('livewire.tenant.settings.whats-mark.support-agent-settings');
    }
}
