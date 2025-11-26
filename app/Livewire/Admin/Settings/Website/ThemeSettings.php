<?php

namespace App\Livewire\Admin\Settings\Website;

use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class ThemeSettings extends Component
{
    use WithFileUploads;

    public $site_logo;

    public $favicon;

    public $cover_page_image;

    public $dark_logo;

    public function rules(): array
    {
        return [
            'site_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'dark_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'cover_page_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'dimensions:width=729,height=152'],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    protected function handleFileUpload($file, $type)
    {
        create_storage_link();

        $themeSettings = get_batch_settings([
            'theme.site_logo',
            'theme.dark_logo',
            'theme.favicon',
            'theme.cover_page_image',
        ]);

        if ($type === 'site_logo' && $themeSettings['theme.site_logo'] && file_exists('storage/'.$themeSettings['theme.site_logo'])) {
            @unlink('storage/'.$themeSettings['theme.site_logo']);
        }

        if ($type === 'dark_logo' && $themeSettings['theme.dark_logo'] && file_exists('storage/'.$themeSettings['theme.dark_logo'])) {
            @unlink('storage/'.$themeSettings['theme.dark_logo']);
        }

        if ($type === 'favicon' && $themeSettings['theme.favicon'] && file_exists('storage/'.$themeSettings['theme.favicon'])) {
            @unlink('storage/'.$themeSettings['theme.favicon']);
        }

        if ($type === 'cover_page_image' && $themeSettings['theme.cover_page_image'] && file_exists('storage/'.$themeSettings['theme.cover_page_image'])) {
            @unlink('storage/'.$themeSettings['theme.cover_page_image']);
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

    public function removeSetting(string $type)
    {
        if (checkPermission('admin.website_settings.edit')) {
            switch ($type) {
                case 'favicon':
                    $this->removeSettingFile('theme.favicon');
                    $this->favicon = null;
                    break;

                case 'site_logo':
                    $this->removeSettingFile('theme.site_logo');
                    $this->site_logo = null;
                    break;

                case 'dark_logo':
                    $this->removeSettingFile('theme.dark_logo');
                    $this->dark_logo = null;
                    break;

                case 'cover_page_image':
                    $this->removeSettingFile('theme.cover_page_image');
                    $this->cover_page_image = null;
                    break;
            }

            $this->notify([
                'type' => 'success',
                'message' => ucfirst(str_replace('_', ' ', $type.' ')).t('remove_successfully'),
            ]);
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.themes.settings.view'));
        }
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate();

            $uploadedFiles = [];

            if (is_object($this->favicon)) {
                $faviconPath = $this->handleFileUpload($this->favicon, 'favicon');
                $uploadedFiles['favicon'] = $faviconPath;
            }

            if (is_object($this->site_logo)) {
                $siteLogoPath = $this->handleFileUpload($this->site_logo, 'site_logo');
                $uploadedFiles['site_logo'] = $siteLogoPath;
            }

            if (is_object($this->dark_logo)) {
                $siteLogoPath = $this->handleFileUpload($this->dark_logo, 'dark_logo');
                $uploadedFiles['dark_logo'] = $siteLogoPath;
            }

            if (is_object($this->cover_page_image)) {
                $siteLogoPath = $this->handleFileUpload($this->cover_page_image, 'cover_page_image');
                $uploadedFiles['cover_page_image'] = $siteLogoPath;
            }

            if (! empty($uploadedFiles)) {
                set_settings_batch('theme', $uploadedFiles);

                // Clear the file inputs after successful upload
                $this->favicon = null;
                $this->site_logo = null;
                $this->dark_logo = null;
                $this->cover_page_image = null;

                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')], true);

                // Redirect to refresh the page and show uploaded images
                return to_route('admin.themes.settings.view');
            } else {
                $this->notify(['type' => 'info', 'message' => t('no_changes_to_save')], true);
            }
        }
    }

    public function render()
    {
        $themeSettings = get_batch_settings([
            'theme.site_logo',
            'theme.dark_logo',
            'theme.favicon',
            'theme.cover_page_image',
        ]);

        return view('livewire.admin.settings.website.theme-settings', [
            'themeSettings' => $themeSettings,
        ]);
    }
}
