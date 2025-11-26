<?php

namespace Modules\ApiWebhookManager\Livewire\Tenant\Settings\System;

use Illuminate\Support\Str;
use Livewire\Component;

class ManageApiTokens extends Component
{
    public bool $isEnabled = false;

    public ?string $currentToken = null;

    public bool $newTokenGenerated = false;

    public array $originalState = [];

    public $subdomain;

    public function mount()
    {
        if (! checkPermission('system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = tenant_settings_by_group('api');
        // $this->isEnabled    = (bool) get_setting('api.enabled', false);
        $this->isEnabled = $settings['enabled'] ?? false;

        $this->subdomain = request()->route('subdomain') ?? '';

        // $this->currentToken = get_setting('api.');
        $this->currentToken = $settings['token'] ?? '';

        // Store original state for isDirty check
        $this->originalState = [
            'isEnabled' => $this->isEnabled,
            'currentToken' => $this->currentToken,
        ];
    }

    public function toggleApiAccess($value)
    {
        $this->isEnabled = (bool) $value;

        if ($this->isEnabled && empty($this->currentToken)) {
            $this->generateNewToken();
        }
    }

    public function generateNewToken()
    {
        $this->currentToken = hash('sha256', Str::random(64));
        $this->newTokenGenerated = true;
    }

    public function isDirty(): bool
    {
        return $this->isEnabled !== $this->originalState['isEnabled'] || $this->currentToken !== $this->originalState['currentToken'];
    }

    public function save()
    {
        if (checkPermission('system_settings.edit')) {
            if (! $this->isDirty()) {
                return;
            }

            $updates = [
                'enabled' => $this->isEnabled,
                'token' => $this->currentToken,
                'abilities' => config('ApiWebhookManager.abilities', []),
                'last_used_at' => now(),
            ];

            if ($this->newTokenGenerated) {
                $updates['token_generated_at'] = now();
                $this->newTokenGenerated = false;
            }

            foreach ($updates as $key => $value) {
                save_tenant_setting('api', $key, $value);
            }

            // Update original state after saving
            $this->originalState = [
                'isEnabled' => $this->isEnabled,
                'currentToken' => $this->currentToken,
            ];

            $this->notify([
                'type' => 'success',
                'message' => t('api_setting_update_successfully'),
            ]);
        }
    }

    public function render()
    {

        return view('ApiWebhookManager::livewire.tenant.settings.system.manage-api-tokens', [
            'abilities' => config('ApiWebhookManager.abilities', []),
        ]);
    }
}
