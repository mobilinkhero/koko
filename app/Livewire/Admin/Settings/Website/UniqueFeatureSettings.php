<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class UniqueFeatureSettings extends Component
{
    use WithFileUploads;

    public $uni_feature_title;

    public $uni_feature_sub_title;

    public $uni_feature_description;

    public $uni_feature_list = [];

    public $uni_feature_image;

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $themeSettimgs = get_batch_settings([
            'theme.uni_feature_title',
            'theme.uni_feature_sub_title',
            'theme.uni_feature_description',
            'theme.uni_feature_list',
        ]);
        $this->uni_feature_title = $themeSettimgs['theme.uni_feature_title'] ?? '';
        $this->uni_feature_sub_title = $themeSettimgs['theme.uni_feature_sub_title'] ?? '';
        $this->uni_feature_description = $themeSettimgs['theme.uni_feature_description'] ?? '';
        $this->uni_feature_list = json_decode($themeSettimgs['theme.uni_feature_list'] ?? [], true);
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate([
                'uni_feature_title' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'uni_feature_sub_title' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'uni_feature_description' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
                'uni_feature_list' => ['array', new PurifiedInput(t('sql_injection_error'))],
                'uni_feature_list.*' => ['string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'uni_feature_image' => ['nullable', 'image', 'max:5024'],
            ]);

            $originalSettings = get_settings_by_group('theme');

            $newSettings = [
                'uni_feature_title' => $this->uni_feature_title,
                'uni_feature_sub_title' => $this->uni_feature_sub_title,
                'uni_feature_description' => $this->uni_feature_description,
                'uni_feature_list' => json_encode(array_filter($this->uni_feature_list)),
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                $propertyName = str_replace('theme.', '', $key);

                return $value !== $originalSettings->$propertyName;
            }, ARRAY_FILTER_USE_BOTH);

            $uploadedFiles = [];

            if (is_object($this->uni_feature_image)) {
                $uniFeatureImagePath = $this->handleFileUpload($this->uni_feature_image, 'uni_feature_image');
                $uploadedFiles['uni_feature_image'] = $uniFeatureImagePath;
            }

            $finalUpdates = array_merge($modifiedSettings, $uploadedFiles);

            if (! empty($finalUpdates)) {

                set_settings_batch('theme', $finalUpdates);

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

        $settings = get_batch_settings(['theme.uni_feature_image']);
        $uni_feature_image = $settings['theme.uni_feature_image'] ?? null;
        if ($type === 'uni_feature_image' && $uni_feature_image && file_exists('storage/'.$uni_feature_image)) {
            @unlink('storage/'.$uni_feature_image);
        }
        $filename = $type.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');

        return $path;
    }

    public function removeUniFeatureimage()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->removeSettingFile('theme.uni_feature_image');
            $this->uni_feature_image = null;
            $this->notify([
                'type' => 'success',
                'message' => t('image_removed_successfully'),
            ]);
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.unique-feature.settings.view'));
        }
    }

    protected function removeSettingFile(string $pathSetting)
    {
        try {
            $settings = get_batch_settings([$pathSetting]);
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
        $this->uni_feature_list[] = '';
    }

    public function removeList($index)
    {
        if (isset($this->uni_feature_list[$index])) {
            unset($this->uni_feature_list[$index]);
            $this->uni_feature_list = array_values($this->uni_feature_list);
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.unique-feature-settings');
    }
}
