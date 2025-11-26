<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use App\Rules\PurifiedInput;
use Livewire\Component;

class StopBotSettings extends Component
{
    public $stop_bots_keyword = [];

    public $restart_bots_after = null;

    protected function rules()
    {
        return [
            'stop_bots_keyword' => ['required', 'array', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'restart_bots_after' => 'nullable|numeric|min:0',
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');

        $this->stop_bots_keyword = $settings['stop_bots_keyword'] ?? [];
        $this->restart_bots_after = $settings['restart_bots_after'] ?? null;
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $settings = tenant_settings_by_group('whats-mark');

            $originalStopBotsKeyword = $settings['stop_bots_keyword'] ?? [];
            $originalRestartBotsAfter = $settings['restart_bots_after'] ?? null;

            $newStopBotsKeyword = $this->stop_bots_keyword;
            $newRestartBotsAfter = $this->restart_bots_after;

            $modifiedSettings = [];

            if ($originalStopBotsKeyword !== $newStopBotsKeyword) {
                $modifiedSettings['stop_bots_keyword'] = $newStopBotsKeyword;
            }

            if ($originalRestartBotsAfter !== $newRestartBotsAfter) {
                $modifiedSettings['restart_bots_after'] = $newRestartBotsAfter;
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
        return view('livewire.tenant.settings.whats-mark.stop-bot-settings');
    }
}
