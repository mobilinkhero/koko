<?php

namespace App\Livewire\Tenant\Settings\Language;

use App\Models\Language;
use Livewire\Component;

class TenantTranslationManager extends Component
{
    public $code;

    public $languageName;

    public function mount()
    {
        $this->code = request()->route('code');
        $this->languageName = Language::query()
            ->where('code', $this->code)
            ->when(current_tenant(), function ($query) {
                $query->where('tenant_id', tenant_id());
            }, function ($query) {
                $query->whereNull('tenant_id');
            })
            ->pluck('name');
    }

    public function render()
    {
        return view('livewire.tenant.settings.language.tenant-translation-manager');
    }
}
