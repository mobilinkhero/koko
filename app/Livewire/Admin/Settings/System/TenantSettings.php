<?php

namespace App\Livewire\Admin\Settings\System;

use Livewire\Component;

class TenantSettings extends Component
{
    public ?bool $isRegistrationEnabled;

    public ?bool $isVerificationEnabled;

    public ?bool $isEmailConfirmationEnabled;

    public ?bool $isEnableWelcomeEmail;

    public ?string $set_default_tenant_language;

    protected function rules()
    {
        return [
            'isRegistrationEnabled' => 'nullable|bool',
            'isVerificationEnabled' => 'nullable|bool',
            'isEmailConfirmationEnabled' => 'nullable|bool',
            'isEnableWelcomeEmail' => 'nullable|bool',
            'set_default_tenant_language' => 'nullable|string',
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('tenant');
        $this->isRegistrationEnabled = $settings->isRegistrationEnabled ?? '';
        $this->isVerificationEnabled = $settings->isVerificationEnabled ?? '';
        $this->isEmailConfirmationEnabled = $settings->isEmailConfirmationEnabled ?? '';
        $this->isEnableWelcomeEmail = $settings->isEnableWelcomeEmail ?? '';
        $this->set_default_tenant_language = $settings->set_default_tenant_language ?? '';
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();
            $settings = get_settings_by_group('tenant');

            $newSettings = [
                'isRegistrationEnabled' => $this->isRegistrationEnabled,
                'isVerificationEnabled' => $this->isVerificationEnabled,
                'isEmailConfirmationEnabled' => $this->isEmailConfirmationEnabled,
                'isEnableWelcomeEmail' => $this->isEnableWelcomeEmail,
                'set_default_tenant_language' => $this->set_default_tenant_language,
            ];

            // Filter the settings that have been modified
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($settings) {
                return $value !== $settings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('tenant', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.tenant-settings');
    }
}
