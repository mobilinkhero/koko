<?php

namespace Modules\ApiWebhookManager\Livewire\Admin\Settings\System;

use Exception;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\ApiWebhookManager\Settings\ApiSettings as SettingsApiSettings;

class ApiSettings extends Component
{
    public bool $isEnabled = false;

    public ?string $currentToken = null;

    public bool $newTokenGenerated = false;

    public string $last_used_at;

    public array $originalState = [];

    public function mount()
    {
        if (! checkPermission('system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = app(SettingsApiSettings::class);

        $this->isEnabled = $settings->enable_api ?? false;
        $this->currentToken = $settings->api_token ?? '';

        // Store original state for isDirty check
        $this->originalState = [
            'enable_api' => $this->isEnabled,
            'currentToken' => $this->currentToken,
        ];
    }

    protected function rules()
    {
        return [
            'isEnabled' => 'nullable|boolean',
            'currentToken' => 'nullable',
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
        return $this->isEnabled !== $this->originalState['enable_api'] || $this->currentToken !== $this->originalState['currentToken'];
    }

    public function save()
    {
        try {
            if (checkPermission('admin.system_settings.edit')) {
                $this->validate();

                $originalSettings = app(SettingsApiSettings::class);

                $newSettings = [
                    'enable_api' => $this->isEnabled,
                    'api_token' => $this->currentToken,
                    'abilities' => config('ApiWebhookManager.admin_abilities', []),
                    'last_used_at' => now()->toDateTimeString(),
                ];

                if ($this->newTokenGenerated) {
                    $newSettings['token_generated_at'] = now()->toDateTimeString();
                    $this->newTokenGenerated = false;
                }

                // Compare and filter only modified settings
                $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                    return $value !== $originalSettings->$key;
                }, ARRAY_FILTER_USE_BOTH);

                $originalSettings->enable_api = $newSettings['enable_api'];
                $originalSettings->api_token = $newSettings['api_token'];
                $originalSettings->abilities = $newSettings['abilities'];
                $originalSettings->last_used_at = $newSettings['last_used_at'];
                $originalSettings->token_generated_at = $newSettings['token_generated_at'] ?? '';

                // Save only if there are modifications
                if (! empty($modifiedSettings)) {
                    $originalSettings->save();

                    $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
                }
            }
        } catch (Exception $e) {
            $this->notify(['type' => 'danger', 'message' => t('something_went_wrong')]);
        }

    }

    public function render()
    {
        return view('ApiWebhookManager::livewire.admin.settings.system.api-settings', [
            'abilities' => config('ApiWebhookManager.admin_abilities', []),
        ]);
    }
}
