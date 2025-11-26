<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class FeatureThreeSettings extends Component
{
    use WithFileUploads;

    public $feature_title_three;

    public $feature_subtitle_three;

    public $feature_description_three;

    public $feature_list_three = [];

    public $feature_image_three;

    public ?bool $feature_three_enabled = false;

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('theme') ?? (object) [];
        $this->feature_three_enabled = in_array($settings->feature_three_enabled ?? false, [1, '1', true, 'true'], true);
        $this->feature_title_three = $settings->feature_title_three ?? '';
        $this->feature_subtitle_three = $settings->feature_subtitle_three ?? '';
        $this->feature_description_three = $settings->feature_description_three ?? '';
        $this->feature_list_three = $settings->feature_list_three ?? [];
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate([
                'feature_three_enabled' => 'nullable|boolean',
                'feature_title_three' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_subtitle_three' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_description_three' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
                'feature_list_three' => ['array', new PurifiedInput(t('sql_injection_error'))],
                'feature_list_three.*' => ['string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_image_three' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5024'],
            ]);

            $originalSettings = get_settings_by_group('theme');

            $newSettings = [
                'feature_three_enabled' => $this->feature_three_enabled,
                'feature_title_three' => $this->feature_title_three,
                'feature_subtitle_three' => $this->feature_subtitle_three,
                'feature_description_three' => $this->feature_description_three,
                'feature_list_three' => $this->feature_list_three,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                $propertyName = str_replace('theme.', '', $key);

                return ! isset($originalSettings->$propertyName) || $value !== $originalSettings->$propertyName;
            }, ARRAY_FILTER_USE_BOTH);

            if ($this->feature_image_three) {
                $featureImagePath = $this->handleFileUpload($this->feature_image_three, 'feature_image_three');
                $modifiedSettings['feature_image_three'] = $featureImagePath;
                $this->feature_image_three = null;
            }

            if (! empty($modifiedSettings)) {
                // Ensure toggle is stored as string for consistency with settings storage
                if (isset($modifiedSettings['feature_three_enabled'])) {
                    $modifiedSettings['feature_three_enabled'] = $modifiedSettings['feature_three_enabled'] ? '1' : '0';
                }

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

        $settings = get_batch_settings(['theme.feature_image_three']);
        $feature_image_three = $settings['theme.feature_image_three'] ?? null;

        if ($type === 'feature_image_three' && $feature_image_three && file_exists('storage/'.$feature_image_three)) {
            @unlink('storage/'.$feature_image_three);
        }

        $filename = $type.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');

        return $path;
    }

    public function removeFeatureImage()
    {
        if (checkPermission('admin.website_settings.edit')) {
            if ($this->removeSettingFile('theme.feature_image_three')) {
                $this->feature_image_three = null;
                $this->notify([
                    'type' => 'success',
                    'message' => t('image_removed_successfully'),
                ]);
            }
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.feature-three.settings.view'));
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
        $this->feature_list_three[] = '';
    }

    public function removeList($index)
    {
        if (isset($this->feature_list_three[$index])) {
            unset($this->feature_list_three[$index]);
            $this->feature_list_three = array_values($this->feature_list_three);
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.feature-three-settings');
    }
}
