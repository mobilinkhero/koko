<?php

namespace App\Livewire;

use App\Services\LanguageService;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public function mount()
    {
        $locale = Session::get('locale', config('app.locale'));
        $this->currentLocale = ! empty($locale) ? $locale : 'en';
    }

    public function setLocale($lang, $persist = false)
    {
        $languageService = app(LanguageService::class);

        // Validate that the language is available
        if (! $languageService->isValidLanguage($lang)) {
            $this->dispatch('notify', [
                'type' => 'danger',
                'message' => t('language_not_available').$lang,
                'timeout' => 5000, // Extended timeout to 5 seconds
            ]);

            return;
        }

        try {
            // For top bar language switcher, always use session-only (never persist)
            // This ensures that after logout and login, the user's default language is used
            $languageService->switchLanguage($lang);
            $this->currentLocale = $lang;
            // Show success notification with extended timeout
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => t('language_switched_successfully'),
                'timeout' => 5000, // Extended timeout to 5 seconds
            ]);

            // Redirect to apply language changes
            return redirect(request()->header('Referer'));
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'danger',
                'message' => t('failed_switch_language').$e->getMessage(),
                'timeout' => 5000, // Extended timeout for error messages too
            ]);
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
