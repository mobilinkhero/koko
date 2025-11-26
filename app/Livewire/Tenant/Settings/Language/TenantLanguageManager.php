<?php

namespace App\Livewire\Tenant\Settings\Language;

use App\Models\Language;
use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TenantLanguageManager extends Component
{
    public Language $language;

    public $showLanguageModal = false;

    public $confirmingDeletion = false;

    public $language_id = null;

    public $name;

    public $code;

    protected $listeners = [
        'editLanguage' => 'editLanguage',
        'confirmDelete' => 'confirmDelete',
        'translateLanguage' => 'translateLanguage',
    ];

    public function mount()
    {
        $this->language = new Language;
    }

    public function translateLanguage($code)
    {

        return redirect()->to(tenant_route('tenant.languages.translations', ['code' => $code]));
    }

    public function createLanguage()
    {
        $this->resetForm();
        $this->showLanguageModal = true;
    }

    private function resetForm()
    {
        $this->reset();
        $this->resetValidation();
        $this->language = new Language;
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('languages', 'name')
                    ->where(function ($query) {
                        return $query->where('tenant_id', tenant_id());
                    })
                    ->ignore($this->language->id ?? null),
                new PurifiedInput(t('sql_injection_error')),
            ],
            'code' => [
                'required',
                'string',
                'min:2',
                'max:3',
                'regex:/^[a-zA-Z]+$/',
                Rule::unique('languages', 'code')
                    ->where(function ($query) {
                        return $query->where('tenant_id', tenant_id());
                    })
                    ->ignore($this->language->id ?? null),
                new PurifiedInput(t('sql_injection_error')),
            ],
        ];
    }

    public function editLanguage($languageCode)
    {
        if ($languageCode === 'en') {
            return $this->notify([
                'type' => 'danger',
                'message' => t('edit_english_language_not_allowed'),
            ]);
        }

        $language = Language::query()
            ->where('code', $languageCode)
            ->when(current_tenant(), function ($query) {
                $query->where('tenant_id', tenant_id());
            }, function ($query) {
                $query->whereNull('tenant_id');
            })
            ->firstOrFail();
        $this->language_id = $language->id;
        $this->name = $language->name;
        $this->code = $language->code;

        $this->resetValidation();
        $this->showLanguageModal = true;
    }

    public function save()
    {
        $isUpdate = isset($this->language_id);
        $tenant = current_tenant();
        $code = strtolower($this->code);

        if ($isUpdate) {
            $this->language = Language::findOrFail($this->language_id);
        }

        $this->validate();

        if ($isUpdate) {
            $originalName = $this->language->name;
            $originalCode = $this->language->code;

            if ($this->name === $originalName && $code === $originalCode) {
                $this->showLanguageModal = false;

                return;
            }

            // Handle file operations for updates
            if ($originalCode !== $code) {
                $oldPath = resource_path("lang/translations/tenant/{$tenant->id}/tenant_{$originalCode}.json");
                $newPath = resource_path("lang/translations/tenant/{$tenant->id}/tenant_{$code}.json");

                if (File::exists($oldPath)) {
                    File::move($oldPath, $newPath);
                }
            }
        } else {
            // Handle file operations for new languages
            $this->language = new Language;

            $source = resource_path('lang/tenant_en.json');
            $destination = resource_path("lang/translations/tenant/{$tenant->id}/tenant_{$code}.json");

            try {
                File::ensureDirectoryExists(dirname($destination));
                if (File::exists($source)) {
                    File::copy($source, $destination);
                } else {
                    File::put($destination, '{}');
                }
            } catch (\Exception $e) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('failed_create_language_file ').$e->getMessage(),
                ]);

                return;
            }
        }

        // Set language properties
        $this->language->name = $this->name;
        $this->language->code = $code;
        $this->language->tenant_id = tenant_id();

        try {
            $this->language->save();
            // Cache will be automatically cleared by the Language model's boot() method
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_save_language').$e->getMessage(),
            ]);

            return;
        }

        $this->showLanguageModal = false;
        $this->dispatch('pg:eventRefresh-tenant-language-table');

        $this->notify([
            'type' => 'success',
            'message' => $isUpdate ? t('language_update_successfully') : t('language_added_successfully'),
            'timeout' => 5000, // Extended timeout to 5 seconds
        ]);
    }

    public function confirmDelete($languageId)
    {
        $this->language_id = $languageId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        try {
            $language = Language::findOrFail($this->language_id);
            $tenant = current_tenant();

            // Prevent deletion of English language
            if ($language->code === 'en') {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('edit_english_language_not_allowed'),
                ]);

                return;
            }

            // Delete tenant-specific language files if they exist
            $filePath = resource_path("lang/translations/tenant/{$tenant->id}/tenant_{$language->code}.json");

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            // Delete language record
            $language->delete();
            // Cache will be automatically cleared by the Language model's boot() method

            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-tenant-language-table');

            $this->notify([
                'type' => 'success',
                'message' => t('language_delete_successfully'),
                'timeout' => 5000, // Extended timeout to 5 seconds
            ]);
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_delete_language').$e->getMessage(),
            ]);
        }
    }

    public function syncTenantLanguage()
    {
        $tenant_id = tenant_id();
        Artisan::call('languages:reset', [
            '--tenant-id' => $tenant_id,
            '--sync-public' => true,  // This signals to check public/lang files first
        ]);
        $this->notify([
            'type' => 'success',
            'message' => t('language_synchronized_successfully'),
        ]);
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-tenant-language-table');
    }

    public function render()
    {
        return view('livewire.tenant.settings.language.tenant-language-manager');
    }
}
