<?php

namespace App\Livewire\Admin\Settings\System;

use Livewire\Component;

class ReCaptchaSettings extends Component
{
    public ?bool $isReCaptchaEnable = false;

    public ?string $site_key = '';

    public ?string $secret_key = '';

    protected function rules()
    {
        return [
            'isReCaptchaEnable' => 'nullable|boolean',
            'site_key' => 'required_if:isReCaptchaEnable,true|string|max:255',
            'secret_key' => 'required_if:isReCaptchaEnable,true|string|max:255',
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $settings = get_settings_by_group('re-captcha');

        $this->isReCaptchaEnable = $settings->isReCaptchaEnable ?? false;
        $this->site_key = $settings->site_key;
        $this->secret_key = $settings->secret_key;
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();

            $originalSettings = get_settings_by_group('re-captcha');

            $newSettings = [
                'isReCaptchaEnable' => $this->isReCaptchaEnable,
                'site_key' => $this->site_key,
                'secret_key' => $this->secret_key,
            ];

            // Filter the settings that have been modified
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return $value !== $originalSettings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('re-captcha', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.re-captcha-settings');
    }
}
