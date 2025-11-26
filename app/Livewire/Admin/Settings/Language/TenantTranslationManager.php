<?php

namespace App\Livewire\Admin\Settings\Language;

use App\Models\TenantLanguage;

class TenantTranslationManager extends TranslationManager
{
    public function mount()
    {
        $this->code = request()->route('code');
        $this->languageName = TenantLanguage::where('code', $this->code)
            ->pluck('name');
    }

    public function render()
    {
        return view('livewire.admin.settings.language.translation-manager', [
            'translationPath' => resource_path("lang/tenant_{$this->code}.json"),
        ]);
    }
}
