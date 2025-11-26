<?php

namespace App\Livewire\Admin\Settings\System;

use App\Rules\PurifiedInput;
use Livewire\Component;

class PrivacyPolicySettings extends Component
{
    public ?string $title = null;

    public ?string $content = null;

    public $updated_at = null;

    protected function rules()
    {
        return [
            'title' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            'content' => ['required', 'string'],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $settings = get_settings_by_group('privacy-policy');
        $this->title = $settings->title ?? '';
        $this->content = $settings->content ?? '';
        $this->updated_at = $settings->updated_at ?? '';
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();
            $settings = get_settings_by_group('privacy-policy');

            $newSettings = [
                'title' => $this->title,
                'content' => $this->content,
                'updated_at' => now()->setTimezone(config('app.timezone')),
            ];

            // Filter the settings that have been modified
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($settings) {
                return $value !== $settings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('privacy-policy', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.privacy-policy-settings');
    }
}
