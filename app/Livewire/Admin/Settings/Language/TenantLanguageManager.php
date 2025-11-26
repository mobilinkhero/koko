<?php

namespace App\Livewire\Admin\Settings\Language;

use App\Models\TenantLanguage;
use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class TenantLanguageManager extends Component
{
    use WithFileUploads;

    public TenantLanguage $language;

    public $showLanguageModal = false;

    public $confirmingDeletion = false;

    public $language_id = null;

    public $name;

    public $code;

    // File upload properties for edit modal
    public $showEditModal = false;

    public $editLanguageId = null;

    public $editUploadFile = null;

    public $editUploadResults = null;

    public $editUploadProgress = 0;

    public $editFilePreview = null;

    public $editIsUploading = false;

    protected $listeners = [
        'editLanguage' => 'editLanguage',
        'confirmDelete' => 'confirmDelete',
        'downloadLanguage' => 'downloadLanguage',
        'translateLanguage' => 'translateLanguage',
    ];

    public function mount()
    {
        $this->language = new TenantLanguage;
    }

    public function createLanguage()
    {
        $this->resetForm();
        $this->showLanguageModal = true;
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-tenant-language-table-table');
    }

    private function resetForm()
    {
        $this->reset(['name', 'code']);
        $this->resetValidation();
        $this->language = new TenantLanguage;
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tenant_languages', 'name')
                    ->ignore($this->language->id ?? null),
                new PurifiedInput(t('sql_injection_error')),
            ],
            'code' => [
                'required',
                'string',
                'min:2',
                'max:3',
                'regex:/^[a-zA-Z]+$/',
                Rule::unique('tenant_languages', 'code')
                    ->ignore($this->language->id ?? null),
                new PurifiedInput(t('sql_injection_error')),
            ],
        ];
    }

    public function save()
    {
        $this->validate();

        $this->language->fill([
            'name' => $this->name,
            'code' => strtolower($this->code),
        ]);

        // Create language file in public/lang directory by copying tenant_en.json
        $targetPath = $this->language->getFilePathAttribute();
        $sourcePath = resource_path('lang/tenant_en.json');

        if (! File::exists(dirname($targetPath))) {
            File::makeDirectory(dirname($targetPath), 0755, true);
        }

        // Copy the source file or create a default JSON object
        if (File::exists($sourcePath)) {
            File::copy($sourcePath, $targetPath);
        } else {
            File::put($targetPath, '{}');
        }

        $this->language->save();

        $this->showLanguageModal = false;
        $this->resetForm();
        $this->dispatch('pg:eventRefresh-tenant-language-table-table');

        $this->notify([
            'type' => 'success',
            'message' => t('language_added_successfully'),
        ]);
    }

    public function editLanguage($languageCode)
    {
        if ($languageCode === 'en') {
            return $this->notify([
                'type' => 'danger',
                'message' => t('edit_english_language_not_allowed'),
            ]);
        }

        $this->language = TenantLanguage::where('code', $languageCode)->firstOrFail();
        $this->language_id = $this->language->id;
        $this->name = $this->language->name;
        $this->code = $this->language->code;
        $this->editLanguageId = $this->language->id;

        $this->resetEditUploadState();
        $this->showEditModal = true;
    }

    public function updateLanguage()
    {
        $this->validate();

        $originalCode = $this->language->code;
        $newCode = strtolower($this->code);

        // If code changed, handle file renaming
        if ($originalCode !== $newCode) {
            $oldPath = public_path("lang/tenant_{$originalCode}.json");
            $newPath = public_path("lang/tenant_{$newCode}.json");

            if (File::exists($oldPath)) {
                File::move($oldPath, $newPath);
            }
        }

        $this->language->update([
            'name' => $this->name,
            'code' => $newCode,
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        $this->dispatch('pg:eventRefresh-tenant-language-table-table');

        $this->notify([
            'type' => 'success',
            'message' => t('language_updated_successfully'),
        ]);
    }

    public function confirmDelete($languageId)
    {
        $this->language_id = $languageId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->language_id) {
            $language = TenantLanguage::find($this->language_id);

            if ($language) {
                $language->delete(); // This will trigger the model's delete event to clean up files
            }
        }

        $this->confirmingDeletion = false;
        $this->language_id = null;
        $this->dispatch('pg:eventRefresh-tenant-language-table-table');

        $this->notify([
            'type' => 'success',
            'message' => t('language_deleted_successfully'),
        ]);
    }

    public function downloadLanguage($languageId)
    {
        try {
            $language = TenantLanguage::findOrFail($languageId);
            $filePath = $language->getFilePathAttribute();

            if (! File::exists($filePath)) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('language_file_not_found'),
                ]);

                return;
            }

            return response()->download($filePath);
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('download_failed').': '.$e->getMessage(),
            ]);
        }
    }

    // Edit modal upload methods
    public function updatedEditUploadFile()
    {
        if ($this->editUploadFile) {
            $this->generateEditFilePreview();
            $this->validateEditFile();
        }
    }

    private function validateEditFile()
    {
        try {
            $this->validate([
                'editUploadFile' => [
                    'required',
                    'file',
                    'mimes:json',
                    'max:2048',
                    function ($attribute, $value, $fail) {
                        if (strtolower($value->getClientOriginalExtension()) !== 'json') {
                            $fail(t('file_must_be_valid_json'));
                        }

                        $allowedMimes = ['application/json', 'text/json', 'text/plain'];
                        if (! in_array($value->getMimeType(), $allowedMimes)) {
                            $fail(t('file_must_be_valid_json'));
                        }

                        // Validate file name pattern: tenant_{code}.json
                        $expectedFileName = "tenant_{$this->code}.json";
                        $actualFileName = $value->getClientOriginalName();

                        if (strtolower($actualFileName) !== strtolower($expectedFileName)) {
                            $fail("The file name must be exactly '{$expectedFileName}'. Current file name: '{$actualFileName}'");
                        }
                    },
                ],
            ]);

            $content = file_get_contents($this->editUploadFile->getRealPath());
            $validation = $this->validateJsonFile($content);
            $this->editUploadResults = $validation;

        } catch (\Exception $e) {
            $this->editUploadResults = [
                'valid' => false,
                'errors' => ['File validation failed: '.$e->getMessage()],
                'warnings' => [],
                'key_count' => 0,
            ];
        }
    }

    public function processEditUpload()
    {
        if (! $this->editUploadFile) {
            return true; // No file to process, continue with update
        }

        if (! $this->editUploadResults || ! $this->editUploadResults['valid']) {
            $this->notify([
                'type' => 'danger',
                'message' => t('file_must_be_valid_json'),
            ]);

            return false;
        }

        $this->editIsUploading = true;
        $this->editUploadProgress = 25;

        try {
            $uploadedContent = file_get_contents($this->editUploadFile->getRealPath());
            if (! $uploadedContent) {
                return false;
            }
            $this->editUploadProgress = 50;

            $language = TenantLanguage::findOrFail($this->editLanguageId);
            $targetPath = public_path("lang/tenant_{$language->code}.json");

            // Create directory if it doesn't exist
            if (! File::exists(dirname($targetPath))) {
                File::makeDirectory(dirname($targetPath), 0755, true);
            }

            $this->editUploadProgress = 75;

            // Directly write the file content to the target path
            if (! File::put($targetPath, $uploadedContent)) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('language_file_not_found'),
                ]);

                return false;
            }

            $this->editUploadProgress = 100;
            $this->editIsUploading = false;

            $this->notify([
                'type' => 'success',
                'message' => t('language_file_updated_successfully'),
            ]);

            $this->resetEditUploadState();

            return true;

        } catch (\Exception $e) {
            $this->editIsUploading = false;
            $this->editUploadProgress = 0;

            $this->notify([
                'type' => 'danger',
                'message' => t('upload_failed').': '.$e->getMessage(),
            ]);
        }
    }

    private function generateEditFilePreview()
    {
        try {
            $content = file_get_contents($this->editUploadFile->getRealPath());
            $size = $this->editUploadFile->getSize();

            $this->editFilePreview = [
                'name' => $this->editUploadFile->getClientOriginalName(),
                'size' => $this->formatBytes($size),
                'is_valid_json' => false,
                'key_count' => 0,
                'sample_keys' => [],
                'error' => null,
                'debug_info' => null,
            ];

            // Check file name pattern first
            $expectedFileName = "tenant_{$this->code}.json";
            $actualFileName = $this->editUploadFile->getClientOriginalName();

            if (strtolower($actualFileName) !== strtolower($expectedFileName)) {
                $this->editFilePreview['error'] = "File name must be '{$expectedFileName}', but got '{$actualFileName}'";

                return;
            }

            // Try to parse JSON
            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $this->editFilePreview['is_valid_json'] = true;

                // Count only string values as valid translations
                $keyCount = 0;
                $sampleKeys = [];
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        $keyCount++;
                        if (count($sampleKeys) < 3) {
                            $sampleKeys[] = $key;
                        }
                    }
                }

                $this->editFilePreview['key_count'] = $keyCount;
                $this->editFilePreview['sample_keys'] = $sampleKeys;
                $this->editFilePreview['debug_info'] = [
                    'content_preview' => substr($content, 0, 100).'...',
                    'total_array_keys' => count($data),
                    'valid_string_keys' => $keyCount,
                    'first_few_items' => array_slice($data, 0, 3, true),
                ];
            } else {
                $this->editFilePreview['error'] = t('invalid_json_format').': '.json_last_error_msg();
                $this->editFilePreview['debug_info'] = [
                    'content_preview' => substr($content, 0, 100).'...',
                ];
            }
        } catch (\Exception $e) {
            $this->editFilePreview = [
                'name' => $this->editUploadFile->getClientOriginalName(),
                'size' => 'Unknown',
                'is_valid_json' => false,
                'key_count' => 0,
                'sample_keys' => [],
                'error' => $e->getMessage(),
                'debug_info' => ['exception_trace' => $e->getTraceAsString()],
            ];
        }
    }

    public function resetEditUploadState()
    {
        $this->editUploadFile = null;
        $this->editUploadResults = null;
        $this->editUploadProgress = 0;
        $this->editFilePreview = null;
        $this->editIsUploading = false;
    }

    private function validateJsonFile(string $jsonContent): array
    {
        $results = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'key_count' => 0,
            'file_size' => strlen($jsonContent),
            'structure_info' => [],
        ];

        try {
            $jsonContent = trim($jsonContent);
            if ($jsonContent === '') {
                $results['errors'][] = t('empty_json_file');

                return $results;
            }

            $data = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $results['errors'][] = t('invalid_json_format').': '.json_last_error_msg();

                return $results;
            }

            if (! is_array($data)) {
                $results['errors'][] = t('json_must_be_object_or_array');

                return $results;
            }

            // Count only string values in the translation file
            $keyCount = 0;
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $keyCount++;
                }
            }

            $results['key_count'] = $keyCount;
            $results['structure_info'] = [
                'sample_keys' => array_slice(array_keys($data), 0, 5, true),
                'total_keys' => $keyCount,
                'raw_content' => substr($jsonContent, 0, 100).'...', // First 100 chars for debugging
                'decoded_data' => array_slice($data, 0, 3, true), // First 3 items for debugging
            ];

            if ($keyCount > 0) {
                $results['valid'] = true;
            } else {
                $results['warnings'][] = t('no_valid_translation_strings_found');
                $results['errors'][] = t('file_structure').': '.gettype($data).', '.t('keys_found').': '.count($data);
            }

        } catch (\Exception $e) {
            $results['errors'][] = t('processing_error').': '.$e->getMessage();
        }

        return $results;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }

    private function getAllKeys(string $content): array
    {
        $data = json_decode($content, true);

        return is_array($data) ? array_keys($data) : [];
    }

    public function syncTranslations()
    {
        try {
            // Get the English source file content
            $sourcePath = resource_path('lang/tenant_en.json');
            if (! File::exists($sourcePath)) {
                throw new \Exception('English source file not found');
            }

            $sourceContent = File::get($sourcePath);
            $sourceData = json_decode($sourceContent, true);

            if (! is_array($sourceData)) {
                throw new \Exception('Invalid English source file format');
            }

            $sourceKeys = array_keys($sourceData);

            // Get all tenant languages except English
            $languages = TenantLanguage::where('code', '!=', 'en')->get();
            $syncedCount = 0;

            foreach ($languages as $language) {
                $targetPath = public_path("lang/tenant_{$language->code}.json");

                if (! File::exists($targetPath)) {
                    // Create new file if it doesn't exist
                    File::put($targetPath, json_encode($sourceData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $syncedCount++;

                    continue;
                }

                // Read existing translations
                $targetContent = File::get($targetPath);
                $targetData = json_decode($targetContent, true) ?: [];

                $updated = false;

                // Add missing keys with English values
                foreach ($sourceKeys as $key) {
                    if (! isset($targetData[$key])) {
                        $targetData[$key] = $sourceData[$key];
                        $updated = true;
                    }
                }

                if ($updated) {
                    // Sort keys to match English file
                    $sortedData = [];
                    foreach ($sourceKeys as $key) {
                        $sortedData[$key] = $targetData[$key];
                    }

                    File::put($targetPath, json_encode($sortedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $syncedCount++;
                }
            }

            $this->notify([
                'type' => 'success',
                'message' => $syncedCount > 0
                    ? t('language_synchronized_successfully')
                    : t('language_already_synced'),
            ]);

        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('sync_failed').': '.$e->getMessage(),
            ]);
        }
    }

    public function translateLanguage($code)
    {
        return redirect()->route('admin.tenant.languages.translations', ['code' => $code]);
    }

    public function render()
    {
        return view('livewire.admin.settings.language.tenant-language-manager');
    }
}
