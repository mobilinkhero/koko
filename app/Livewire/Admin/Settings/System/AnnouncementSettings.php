<?php

namespace App\Livewire\Admin\Settings\System;

use App\Rules\PurifiedInput;
use Livewire\Component;

class AnnouncementSettings extends Component
{
    public ?bool $isEnable = false;

    public ?string $message = '';

    public ?string $link;

    public ?string $link_text;

    public ?string $background_color;

    public ?string $link_text_color;

    public ?string $message_color;

    public function validateMessage()
    {
        $this->validate([
            'message' => [$this->isEnable ? 'required' : 'nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
        ]);
    }

    protected function rules()
    {
        return [
            'isEnable' => 'nullable|boolean',
            'message' => [$this->isEnable ? 'required' : 'nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'link' => [empty($this->link_text) ? 'nullable' : 'required', 'string', 'url'],
            'link_text' => [empty($this->link_text) ? 'nullable' : 'required', empty($this->link) ? 'nullable' : 'required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'background_color' => 'required|string|max:255',
            'link_text_color' => [empty($this->link_text) ? 'nullable' : 'required', 'string', 'max:255'],
            'message_color' => 'required|string|max:255',
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $settings = get_settings_by_group('announcement');

        $this->isEnable = $settings->isEnable ?? false;
        $this->message = $settings->message;
        $this->link = $settings->link;
        $this->link_text = $settings->link_text;
        $this->background_color = $settings->background_color;
        $this->link_text_color = $settings->link_text_color;
        $this->message_color = $settings->message_color;
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();

            $originalSettings = get_settings_by_group('announcement');

            $newSettings = [
                'isEnable' => $this->isEnable,
                'message' => $this->message,
                'link' => $this->link,
                'link_text' => $this->link_text,
                'background_color' => $this->background_color,
                'link_text_color' => $this->link_text_color,
                'message_color' => $this->message_color,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return $value !== $originalSettings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            if (! empty($modifiedSettings)) {
                set_settings_batch('announcement', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.announcement-settings');
    }
}
