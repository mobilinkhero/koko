<?php

namespace App\Livewire\Admin\Settings\Website;

use Livewire\Component;

class CustomJsSettings extends Component
{
    public $custom_js_header = '';

    public $custom_js_footer = '';

    protected function rules()
    {
        return [
            'custom_js_header' => ['nullable', 'string'],
            'custom_js_footer' => ['nullable', 'string'],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_batch_settings(['theme.custom_js_header', 'theme.custom_js_footer']);
        $this->custom_js_header = $settings['theme.custom_js_header'] ?? '';
        $this->custom_js_footer = $settings['theme.custom_js_footer'] ?? '';
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate();

            $settings = get_batch_settings(['theme.custom_js_header', 'theme.custom_js_footer']);
            $originalHeader = $settings['theme.custom_js_header'] ?? '';
            $originalFooter = $settings['theme.custom_js_footer'] ?? '';

            $updates = [];
            if ($originalHeader !== $this->custom_js_header) {
                $updates['theme.custom_js_header'] = $this->custom_js_header;
            }
            if ($originalFooter !== $this->custom_js_footer) {
                $updates['theme.custom_js_footer'] = $this->custom_js_footer;
            }

            if (! empty($updates)) {
                foreach ($updates as $key => $value) {
                    set_setting($key, $value);
                }
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.custom-js-settings');
    }
}
