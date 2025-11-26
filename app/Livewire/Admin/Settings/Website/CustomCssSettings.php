<?php

namespace App\Livewire\Admin\Settings\Website;

use Livewire\Component;

class CustomCssSettings extends Component
{
    public $customCss = '';

    protected function rules()
    {
        return [
            'customCss' => ['nullable', 'string'],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_batch_settings(['theme.customCss']);
        $this->customCss = $settings['theme.customCss'] ?? '';
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate();

            $settings = get_batch_settings(['theme.customCss']);
            $originalValue = $settings['theme.customCss'] ?? '';

            if ($originalValue !== $this->customCss) {
                set_setting('theme.customCss', $this->customCss);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.custom-css-settings');
    }
}
