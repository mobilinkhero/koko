<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class FeatureTwoSettings extends Component
{
    use WithFileUploads;

    public $feature_title_two;

    public $feature_subtitle_two;

    public $feature_description_two;

    public $feature_list_two = [];

    public $feature_image_two;

    public ?bool $feature_two_enabled = false;

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('theme') ?? (object) [];
        $this->feature_two_enabled = in_array($settings->feature_two_enabled ?? false, [1, '1', true, 'true'], true);
        $this->feature_title_two = $settings->feature_title_two ?? '';
        $this->feature_subtitle_two = $settings->feature_subtitle_two ?? '';
        $this->feature_description_two = $settings->feature_description_two ?? '';
        $this->feature_list_two = $settings->feature_list_two ?? [];
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate([
                'feature_two_enabled' => 'nullable|boolean',
                'feature_title_two' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_subtitle_two' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_description_two' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
                'feature_list_two' => ['array', new PurifiedInput(t('sql_injection_error'))],
                'feature_list_two.*' => ['string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'feature_image_two' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5024'],
            ]);

            $originalSettings = get_settings_by_group('theme');

            $newSettings = [
                'feature_title_two' => $this->feature_title_two,
                'feature_two_enabled' => $this->feature_two_enabled,
                'feature_subtitle_two' => $this->feature_subtitle_two,
                'feature_description_two' => $this->feature_description_two,
                'feature_list_two' => $this->feature_list_two,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                $propertyName = str_replace('theme.', '', $key);

                return ! isset($originalSettings->$propertyName) || $value !== $originalSettings->$propertyName;
            }, ARRAY_FILTER_USE_BOTH);

            if ($this->feature_image_two) {
                $featureImagePath = $this->handleFileUpload($this->feature_image_two, 'feature_image_two');
                $modifiedSettings['feature_image_two'] = $featureImagePath;
                $this->feature_image_two = null;
            }

            if (! empty($modifiedSettings)) {
                // Ensure toggle is stored as string for consistency with settings storage
                if (isset($modifiedSettings['feature_two_enabled'])) {
                    $modifiedSettings['feature_two_enabled'] = $modifiedSettings['feature_two_enabled'] ? '1' : '0';
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

        $settings = get_batch_settings(['theme.feature_image_two']);
        $feature_image_two = $settings['theme.feature_image_two'] ?? null;

        if ($type === 'feature_image_two' && $feature_image_two && file_exists('storage/'.$feature_image_two)) {
            @unlink('storage/'.$feature_image_two);
        }

        $filename = $type.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');

        return $path;
    }

    public function removeFeatureImage()
    {
        if (checkPermission('admin.website_settings.edit')) {
            if ($this->removeSettingFile('theme.feature_image_two')) {
                $this->feature_image_two = null;
                $this->notify([
                    'type' => 'success',
                    'message' => t('image_removed_successfully'),
                ]);
            }
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.feature-two.settings.view'));
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
        $this->feature_list_two[] = '';
    }

    public function removeList($index)
    {
        if (isset($this->feature_list_two[$index])) {
            unset($this->feature_list_two[$index]);
            $this->feature_list_two = array_values($this->feature_list_two);
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.feature-two-settings');
    }
}
