<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class FeatureSettings extends Component
{
    use WithFileUploads;

    public $feature_title;

    public $feature_subtitle;

    public $feature_description;

    public $feature_list = [];

    public $feature_image;

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('theme') ?? (object) [];
        $this->feature_title = $settings->feature_title ?? '';
        $this->feature_subtitle = $settings->feature_subtitle ?? '';
        $this->feature_description = $settings->feature_description ?? '';
        $this->feature_list = json_decode($settings->feature_list ?? [], true) ?? [];
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate([
                'feature_title' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_subtitle' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_description' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
                'feature_list' => ['array', new PurifiedInput(t('sql_injection_error'))],
                'feature_list.*' => ['string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5024'],
            ]);

            $originalSettings = get_settings_by_group('theme');

            $newSettings = [
                'feature_title' => $this->feature_title,
                'feature_subtitle' => $this->feature_subtitle,
                'feature_description' => $this->feature_description,
                'feature_list' => json_encode($this->feature_list),
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                $propertyName = str_replace('theme.', '', $key);

                return $value !== $originalSettings->$propertyName;
            }, ARRAY_FILTER_USE_BOTH);

            if ($this->feature_image) {
                $featureImagePath = $this->handleFileUpload($this->feature_image, 'feature_image');
                $modifiedSettings['feature_image'] = $featureImagePath;
                $this->feature_image = null;
            }

            if (! empty($modifiedSettings)) {
                set_settings_batch('theme', $modifiedSettings);

                $this->dispatch('setting-saved');

                $this->notify([
                    'type' => 'success',
                    'message' => t('setting_save_successfully'),
                ]);
            }
        }
    }

    protected function handleFileUpload($file, $type)
    {
        create_storage_link();

        $settings = get_batch_settings(['theme.feature_image']);
        $feature_image = $settings['theme.feature_image'] ?? null;

        if ($type === 'feature_image' && $feature_image && file_exists('storage/'.$feature_image)) {
            @unlink('storage/'.$feature_image);
        }

        $filename = $type.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');

        return $path;
    }

    public function removeFeatureImage()
    {
        if (checkPermission('admin.website_settings.edit')) {
            if ($this->removeSettingFile('theme.feature_image')) {
                $this->feature_image = null;
                $this->notify([
                    'type' => 'success',
                    'message' => t('image_removed_successfully'),
                ]);
            }
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.feature.settings.view'));
        }
    }

    protected function removeSettingFile(string $pathSetting)
    {
        try {
            $settings = get_batch_settings([
                $pathSetting,
            ]);
            $filePath = $settings[$pathSetting];
            if ($filePath && file_exists(public_path('storage/'.$filePath))) {
                @unlink(public_path('storage/'.$filePath));
            }
            set_setting($pathSetting, null);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public function addList()
    {
        $this->feature_list[] = '';
    }

    public function removeList($index)
    {
        if (isset($this->feature_list[$index])) {
            unset($this->feature_list[$index]);
            $this->feature_list = array_values($this->feature_list);
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.feature-settings');
    }
}
