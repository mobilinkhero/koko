<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use Livewire\Component;

class NotificationSoundSettings extends Component
{
    public ?bool $enable_chat_notification_sound = false;

    protected function rules()
    {
        return [
            'enable_chat_notification_sound' => 'nullable|boolean',
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');
        $this->enable_chat_notification_sound = $settings['enable_chat_notification_sound'] ?? false;
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $settings = tenant_settings_by_group('whats-mark');

            $originalSetting = $settings['enable_chat_notification_sound'] ?? false;

            $newSetting = $this->enable_chat_notification_sound;

            if (! array_key_exists('enable_chat_notification_sound', $settings) || $originalSetting !== $newSetting) {
                save_tenant_setting('whats-mark', 'enable_chat_notification_sound', $newSetting);

                $this->notify([
                    'type' => 'success',
                    'message' => t('setting_save_successfully'),
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.settings.whats-mark.notification-sound-settings');
    }
}
