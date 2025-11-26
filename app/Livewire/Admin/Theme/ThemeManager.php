<?php

namespace App\Livewire\Admin\Theme;

use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ThemeManager extends Component
{
    use WithFileUploads;

    private $themes_folder;

    public $themes = [];

    public $confirmingDeletion = false;

    public $themeToDelete = null;

    // Required theme structure
    protected $listeners = [
        'confirmDelete' => 'confirmDelete',

    ];

    public function mount()
    {
        $this->themes_folder = config('themes.folder', resource_path('views/themes'));

        $this->refreshThemes();
    }

    private function refreshThemes()
    {
        // Load all themes from DB. If you only want themes with a folder present, filter here.
        if (module_exists('ThemeBuilder') && module_enabled('ThemeBuilder')) {
            $this->themes = Theme::all();
        } else {
            $this->themes = Theme::where('type', 'core')->get();
        }
    }

    public function confirmDelete($id)
    {
        // store the id of the theme pending deletion and open the confirmation modal
        $this->themeToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {

        if (! $this->themeToDelete) {
            // nothing to delete
            $this->confirmingDeletion = false;

            return;
        }

        $theme = Theme::findOrFail($this->themeToDelete);

        // Attempt to delete stored image if present. The DB stores the relative path (e.g. "themes/foo/theme.jpg").
        if (! empty($theme->theme_url)) {
            $storedPath = $theme->theme_url;

            // If the value looks like a Storage::url() (starts with '/storage' or contains '://'), normalize to the relative path
            if (str_starts_with($storedPath, '/storage/')) {
                $storedPath = ltrim(substr($storedPath, strlen('/storage/')), '/');
            } elseif (preg_match('#https?://#', $storedPath)) {
                // Try to strip the app url and leading /storage/ if present
                $parsed = parse_url($storedPath);
                $pathOnly = $parsed['path'] ?? $storedPath;
                if (str_starts_with($pathOnly, '/storage/')) {
                    $storedPath = ltrim(substr($pathOnly, strlen('/storage/')), '/');
                } else {
                    // fallback: take basename
                    $storedPath = basename($pathOnly);
                }
            }

            if (Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);

                // If folder is now empty, attempt to remove it (safe operation)
                $folder = dirname($storedPath);
                if ($folder && $folder !== '.' && empty(Storage::disk('public')->allFiles($folder))) {
                    Storage::disk('public')->deleteDirectory($folder);
                }
            }
        }

        // Now delete the DB record
        $theme->delete();
        $this->themeToDelete = null;
        $this->confirmingDeletion = false;
        $this->refreshThemes();
        $this->notify(['type' => 'success', 'message' => t('theme_delete_successfully')]);

        return redirect()->route('admin.theme');
    }

    public function activate($theme_folder)
    {
        $theme = Theme::where('folder', '=', $theme_folder)->first();

        if (isset($theme->id)) {
            $this->deactivateThemes();
            $theme->active = 1;
            $theme->save();
            $this->writeThemeJson($theme_folder);
            // Replace welcome.blade.php with the marketing layout
            $this->replaceWelcomePage($theme_folder);
        }

        $this->refreshThemes();
    }

    private function replaceWelcomePage($themeFolder)
    {
        $themeMarketingPath = resource_path("views/themes/{$themeFolder}/components/layouts/index.blade.php");
        $welcomePath = resource_path('views/welcome.blade.php');

        if (File::exists($themeMarketingPath)) {
            // Copy content from marketing.blade.php to welcome.blade.php
            File::put($welcomePath, File::get($themeMarketingPath));
        }
    }

    private function writeThemeJson($themeName)
    {
        $themeJsonPath = base_path('theme.json');
        $themeJsonContent = json_encode(['name' => $themeName], JSON_PRETTY_PRINT);
        File::put($themeJsonPath, $themeJsonContent);
    }

    private function deactivateThemes()
    {
        Theme::query()->update(['active' => 0]);
    }

    public function render()
    {
        // Return the view using the component's themes property (populated in mount/refreshThemes)
        return view('livewire.admin.theme.theme-manager', [
            'themes' => $this->themes,
        ]);
    }
}
