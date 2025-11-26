<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;

class WebsiteSeoSettings extends Component
{
    public $author_name = '';

    public $seo_meta_title = '';

    public $seo_meta_keywords = '';

    public $seo_meta_description = '';

    public $og_title = '';

    public $og_description = '';

    protected function rules()
    {
        return [
            'author_name' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'seo_meta_title' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'seo_meta_keywords' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'seo_meta_description' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'og_title' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'og_description' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('theme') ?? (object) [];

        $this->author_name = $settings->author_name ?? '';
        $this->seo_meta_title = $settings->seo_meta_title ?? '';
        $this->seo_meta_keywords = $settings->seo_meta_keywords ?? '';
        $this->seo_meta_description = $settings->seo_meta_description ?? '';
        $this->og_title = $settings->og_title ?? '';
        $this->og_description = $settings->og_description ?? '';
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate();

            $originalSettings = get_settings_by_group('theme') ?? (object) [];

            $newSettings = [
                'author_name' => $this->author_name,
                'seo_meta_title' => $this->seo_meta_title,
                'seo_meta_keywords' => $this->seo_meta_keywords,
                'seo_meta_description' => $this->seo_meta_description,
                'og_title' => $this->og_title,
                'og_description' => $this->og_description,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return property_exists($originalSettings, $key) && $value !== $originalSettings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            if (! empty($modifiedSettings)) {
                set_settings_batch('theme', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.website-seo-settings');
    }
}
