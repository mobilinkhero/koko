<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;

class SectionTitleSubtitleSettings extends Component
{
    public $pricing_section_title = '';

    public $pricing_section_subtitle = '';

    public $faq_section_title = '';

    public $faq_section_subtitle = '';

    protected function rules()
    {
        return [
            'pricing_section_title' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'pricing_section_subtitle' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'faq_section_title' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'faq_section_subtitle' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('theme');

        $this->pricing_section_title = $settings->pricing_section_title ?? '';
        $this->pricing_section_subtitle = $settings->pricing_section_subtitle ?? '';
        $this->faq_section_title = $settings->faq_section_title ?? '';
        $this->faq_section_subtitle = $settings->faq_section_subtitle ?? '';
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate();

            $originalSettings = get_settings_by_group('theme');

            $newSettings = [
                'pricing_section_title' => $this->pricing_section_title,
                'pricing_section_subtitle' => $this->pricing_section_subtitle,
                'faq_section_title' => $this->faq_section_title,
                'faq_section_subtitle' => $this->faq_section_subtitle,
            ];

            // Filter the settings that have been modified
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return $value !== $originalSettings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('theme', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.section-title-subtitle-settings');
    }
}
