<?php

namespace Modules\ApiWebhookManager\Livewire\Tenant\Settings\System;

use Livewire\Component;

class WebhookSettingsManager extends Component
{
    public bool $webhook_enabled = false;

    public string $webhook_url = '';

    public array $contacts_actions = [];

    public array $status_actions = [];

    public array $source_actions = [];

    public function mount()
    {

        if (! checkPermission('system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $settings = tenant_settings_by_group('webhook');

        $this->webhook_enabled = $settings['webhook_enabled'] ?? false;
        $this->webhook_url = $settings['webhook_url'] ?? '';

        $this->contacts_actions = is_string($settings['contacts_actions'] ?? null)
            ? json_decode($settings['contacts_actions'], true)
            : ($settings['contacts_actions'] ?? []);

        $this->status_actions = is_string($settings['status_actions'] ?? null)
            ? json_decode($settings['status_actions'], true)
            : ($settings['status_actions'] ?? []);

        $this->source_actions = is_string($settings['source_actions'] ?? null)
            ? json_decode($settings['source_actions'], true)
            : ($settings['source_actions'] ?? []);
    }

    protected function rules()
    {
        return [
            'webhook_enabled' => 'boolean',
            'webhook_url' => 'required_if:webhook_enabled,true|url',
            'contacts_actions' => 'array',
            'status_actions' => 'array',
            'source_actions' => 'array',
        ];
    }

    public function save()
    {
        if (checkPermission('system_settings.edit')) {
            $this->validate();

            $originalSettings = tenant_settings_by_group('webhook');

            $newSettings = [
                'webhook_enabled' => $this->webhook_enabled,
                'webhook_url' => $this->webhook_url,
                'contacts_actions' => $this->contacts_actions,
                'status_actions' => $this->status_actions,
                'source_actions' => $this->source_actions,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return ! array_key_exists($key, $originalSettings) || $originalSettings[$key] !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (! empty($modifiedSettings)) {
                foreach ($modifiedSettings as $key => $value) {
                    save_tenant_setting('webhook', $key, $value);
                }

                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }

        }
    }

    public function render()
    {
        return view('ApiWebhookManager::livewire.tenant.settings.system.webhook-settings-manager');
    }
}
