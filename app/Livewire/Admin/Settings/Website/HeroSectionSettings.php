<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class HeroSectionSettings extends Component
{
    use WithFileUploads;

    public $title;

    public $hero_heading;

    public $description;

    public $primary_button_text;

    public $primary_button_url;

    public $primary_button_type;

    public $secondary_button_text;

    public $secondary_button_url;

    public $secondary_button_type;

    public $image_path;

    public $image_alt_text;

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('theme') ?? (object) [];
        $this->title = $settings->title ?? '';
        $this->hero_heading = $settings->hero_heading ?? '';
        $this->description = $settings->description ?? '';
        $this->primary_button_text = $settings->primary_button_text ?? '';
        $this->primary_button_url = $settings->primary_button_url ?? '';
        $this->primary_button_type = $settings->primary_button_type ?? '';
        $this->secondary_button_text = $settings->secondary_button_text ?? '';
        $this->secondary_button_url = $settings->secondary_button_url ?? '';
        $this->secondary_button_type = $settings->secondary_button_type ?? '';
        $this->image_path = $settings->image_path ?? '';
        $this->image_alt_text = $settings->image_alt_text ?? '';

    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate([
                'title' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'hero_heading' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'description' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
                'primary_button_text' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'primary_button_url' => ['nullable', 'string', 'url', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'primary_button_type' => ['nullable', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
                'secondary_button_text' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'secondary_button_url' => ['nullable', 'string', 'url', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'secondary_button_type' => ['nullable', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
                'image_alt_text' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            ]);

            $originalSettings = get_settings_by_group('theme');

            $newSettings = [
                'title' => $this->title,
                'hero_heading' => $this->hero_heading,
                'description' => $this->description,
                'primary_button_text' => $this->primary_button_text,
                'primary_button_url' => $this->primary_button_url,
                'primary_button_type' => $this->primary_button_type,
                'secondary_button_text' => $this->secondary_button_text,
                'secondary_button_url' => $this->secondary_button_url,
                'secondary_button_type' => $this->secondary_button_type,
                'image_alt_text' => $this->image_alt_text,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                $propertyName = str_replace('theme.', '', $key);

                return $value !== $originalSettings->$propertyName;
            }, ARRAY_FILTER_USE_BOTH);

            $uploadedFiles = [];

            if (is_object($this->image_path)) {
                $imagePath = $this->handleFileUpload($this->image_path, 'hero_image');
                $uploadedFiles['image_path'] = $imagePath;
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

        $settings = get_batch_settings(['theme.image_path']);
        $image_path = $settings['theme.image_path'] ?? null;
        if ($type === 'hero_image' && $image_path && file_exists('storage/'.$image_path)) {
            @unlink('storage/'.$image_path);
        }

        $filename = $type.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename, 'public');

        return $path;
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

    public function removeHeroImage()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->removeSettingFile('theme.image_path');
            $this->image_path = null;

            $this->notify([
                'type' => 'success',
                'message' => t('image_removed_successfully'),
            ]);
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.hero-section.settings.view'));
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.website.hero-section-settings');
    }
}
