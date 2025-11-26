<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LanguageService
{
    /**
     * Admin translations base path.
     */
    protected string $adminTranslationsPath;

    /**
     * Tenant translations base path.
     */
    protected string $tenantTranslationsPath;

    /**
     * Default admin language code.
     */
    protected string $defaultAdminLanguage = 'en';

    /**
     * Default tenant language code.
     */
    protected string $defaultTenantLanguage = 'tenant_en';

    public function __construct()
    {
        $this->adminTranslationsPath = resource_path('lang/translations');
        $this->tenantTranslationsPath = resource_path('lang/translations/tenant');
    }

    /**
     * Resolve the current language for the application.
     * Priority: User preference -> Session -> System default -> 'en'
     */
    public function resolveLanguage(): string
    {
        $default_language = get_setting('system.active_language');

        $language = Auth::user() ? Session::get('locale', config('app.locale')) : (! empty($default_language) ? Session::get('locale', $default_language) : 'en');

        return ! is_null($language) ? $language : 'en';
    }

    /**
     * Switch the application language.
     *
     * @param  string  $code  Language code
     * @param  bool  $persist  Whether to persist to user preferences
     */
    public function switchLanguage(string $code): void
    {
        $previousLanguage = $this->resolveLanguage();

        if (tenant_check()) {
            $tenant = current_tenant();
            $previousLanguage = $tenant->id.'_tenant_'.$previousLanguage;
        }

        // Clear translation cache for both old and new languages
        Cache::forget("translations.{$previousLanguage}");
        Session::put('locale', $code);
        App::setLocale($code);
    }

    /**
     * Get available languages for the current context.
     */
    public function getAvailableLanguages(): \Illuminate\Database\Eloquent\Collection
    {
        return Language::getCached();
    }

    /**
     * Check if a language code is valid and available.
     * Now accepts both active and inactive languages since they appear in the UI.
     */
    public function isValidLanguage(string $code): bool
    {
        return Language::getCached()->where('code', $code)->isNotEmpty();
    }

    /**
     * Reset admin language translations.
     */
    public function resetAdminLanguage(string $languageCode): array
    {
        // Skip if trying to reset the master language file itself
        if ($languageCode === 'en') {
            return ['added' => 0, 'skipped' => 0, 'error' => 'Cannot reset master language file'];
        }

        $masterFilePath = resource_path('lang/en.json');
        $targetFilePath = $this->adminTranslationsPath.'/'.$languageCode.'.json';

        return $this->resetLanguageFile($masterFilePath, $targetFilePath, 'admin', $languageCode);
    }

    /**
     * Reset tenant language translations for a specific tenant.
     */
    public function resetSpecificTenantLanguageWithPublic(string $languageCode, int $tenantId, string $publicFilePath): array
    {
        // Skip if trying to reset the master language file itself
        if ($languageCode === 'en') {
            return ['added' => 0, 'skipped' => 0, 'merged' => 0, 'value_changes' => 0, 'error' => 'Cannot reset master tenant language file'];
        }

        $masterFilePath = resource_path('lang/tenant_en.json');
        $targetFilePath = $this->tenantTranslationsPath."/{$tenantId}/tenant_{$languageCode}.json";
        $publicDir = dirname($publicFilePath);

        $results = [
            'added' => 0,
            'skipped' => 0,
            'merged' => 0,
            'value_changes' => 0,
            'public_updated' => false,
            'error' => null,
        ];
        try {
            // Read master translations
            if (! File::exists($masterFilePath)) {
                throw new \RuntimeException("Master language file does not exist: {$masterFilePath}");
            }
            $masterData = json_decode(File::get($masterFilePath), true);
            if (! is_array($masterData)) {
                throw new \RuntimeException("Invalid master file format in: {$masterFilePath}");
            }

            // STEP 1: Sync tenant_en.json to public/lang/tenant_{code}.json (keys only)
            if (! File::exists($publicFilePath)) {
                // Create new public file from master if it doesn't exist
                if (! File::exists($publicDir)) {
                    File::makeDirectory($publicDir, 0755, true);
                }
                File::put($publicFilePath, json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $results['public_updated'] = true;
                $publicData = $masterData;
            } else {
                // Read existing public translations
                $publicData = json_decode(File::get($publicFilePath), true);
                if (! is_array($publicData)) {
                    throw new \RuntimeException("Invalid public file format in: {$publicFilePath}");
                }

                // Only add missing keys to public file (preserve existing values)
                $missingKeys = $this->findMissingKeys($masterData, $publicData);
                if (! empty($missingKeys)) {
                    foreach ($missingKeys as $key) {
                        $publicData[$key] = $masterData[$key];
                    }

                    // Update the public file with new keys only
                    File::put($publicFilePath, json_encode($publicData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $results['public_updated'] = true;
                }
            }

            // STEP 2: Replace tenant-specific file with public file content (keys AND values)
            $originalTargetExists = File::exists($targetFilePath);
            $originalTargetData = [];

            if ($originalTargetExists) {
                $targetContent = File::get($targetFilePath);
                $originalTargetData = json_decode($targetContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning("Invalid JSON in target file {$targetFilePath}, will be replaced with public file");
                    $originalTargetData = [];
                }
            }

            // Use public file as the complete source for tenant file
            $targetTranslations = $publicData;

            // Count what changed compared to the original target file
            if ($originalTargetExists && is_array($originalTargetData)) {
                $addedKeys = $this->findMissingKeys($publicData, $originalTargetData);
                $results['added'] = count($addedKeys);
                $results['skipped'] = count($publicData) - $results['added'];

                // Check for value changes (existing keys with different values)
                $valueChanges = 0;
                foreach ($publicData as $key => $value) {
                    if (isset($originalTargetData[$key]) && $originalTargetData[$key] !== $value) {
                        $valueChanges++;
                    }
                }
                $results['value_changes'] = $valueChanges;
            } else {
                // New file - all keys are "added"
                $results['added'] = count($publicData);
                $results['skipped'] = 0;
                $results['value_changes'] = 0;
            }

            $results['merged'] = count($publicData);

            // Ensure target directory exists
            $targetDir = dirname($targetFilePath);
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // Write merged translations
            File::put($targetFilePath, json_encode($targetTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $results;

        } catch (\Exception $e) {
            return array_merge($results, ['error' => $e->getMessage()]);
        }
    }

    public function resetSpecificTenantLanguage(string $languageCode, int $tenantId): array
    {
        // Skip if trying to reset the master language file itself
        if ($languageCode === 'en') {
            return ['added' => 0, 'skipped' => 0, 'error' => 'Cannot reset master tenant language file'];
        }

        $masterFilePath = resource_path('lang/tenant_en.json');
        $targetFilePath = $this->tenantTranslationsPath."/{$tenantId}/tenant_{$languageCode}.json";

        return $this->resetLanguageFile($masterFilePath, $targetFilePath, 'tenant', $languageCode, $tenantId);
    }

    /**
     * Reset a specific language file by synchronizing with master file.
     */
    protected function resetLanguageFile(string $masterFilePath, string $targetFilePath, string $type, string $languageCode, ?int $tenantId = null): array|bool
    {
        $results = [
            'added' => 0,
            'skipped' => 0,
            'error' => null,
        ];

        try {
            // Ensure master file exists
            if (! File::exists($masterFilePath)) {
                return false;
            }

            // Read master translations
            $masterTranslations = json_decode(File::get($masterFilePath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            // Read existing target translations or create empty array
            $targetTranslations = [];
            if (File::exists($targetFilePath)) {
                $targetContent = File::get($targetFilePath);
                $targetTranslations = json_decode($targetContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning("Invalid JSON in target file {$targetFilePath}, will be recreated");
                    $targetTranslations = [];
                }
            }

            // Find missing keys
            $missingKeys = $this->findMissingKeys($masterTranslations, $targetTranslations);

            // Add missing translations
            foreach ($missingKeys as $key) {
                $targetTranslations[$key] = $masterTranslations[$key];
                $results['added']++;
            }

            // Count skipped (existing) keys
            $results['skipped'] = count($masterTranslations) - $results['added'];

            // Ensure target directory exists
            $targetDir = dirname($targetFilePath);
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // Write updated translations (preserving original order)
            $jsonContent = json_encode($targetTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            File::put($targetFilePath, $jsonContent);

            $logContext = $tenantId ? "{$type}:{$languageCode}:tenant_{$tenantId}" : "{$type}:{$languageCode}";
            Log::info("Language reset completed for {$logContext}", $results);

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            $logContext = $tenantId ? "{$type}:{$languageCode}:tenant_{$tenantId}" : "{$type}:{$languageCode}";
            Log::error("Language reset failed for {$logContext}", [
                'error' => $e->getMessage(),
                'master_file' => $masterFilePath,
                'target_file' => $targetFilePath,
            ]);
            throw $e;
        }

        return $results;
    }

    /**
     * Find missing keys in target translations compared to master.
     */
    protected function findMissingKeys(array $masterTranslations, array $targetTranslations): array
    {
        $masterKeys = array_keys($masterTranslations);
        $targetKeys = array_keys($targetTranslations);

        return array_diff($masterKeys, $targetKeys);
    }

    /**
     * Get list of active modules.
     */
    protected function getActiveModules(): array
    {
        $modules = [];
        $modulesPath = base_path('Modules');

        if (File::exists($modulesPath)) {
            $directories = File::directories($modulesPath);

            foreach ($directories as $directory) {
                $moduleName = basename($directory);
                $moduleJsonPath = $directory.'/module.json';

                // Check if module.json exists and module is active
                if (File::exists($moduleJsonPath)) {
                    $moduleConfig = json_decode(File::get($moduleJsonPath), true);

                    // Add module if it's active (you can modify this logic based on your module manager)
                    if (isset($moduleConfig['active']) && $moduleConfig['active']) {
                        $modules[] = $moduleName;
                    } elseif (! isset($moduleConfig['active'])) {
                        // If no active status is defined, assume it's active
                        $modules[] = $moduleName;
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Sync module translations to master language files.
     */
    public function syncModulesToMasterFile(string $type = 'admin'): array
    {
        $results = [
            'added' => 0,
            'skipped' => 0,
            'modules_processed' => 0,
            'errors' => [],
        ];

        try {
            // Determine master file path
            if ($type === 'admin') {
                $masterFile = resource_path('lang/en.json');
            } else {
                $masterFile = resource_path('lang/tenant_en.json');
            }

            // Read master file or create empty array
            $masterTranslations = [];
            if (File::exists($masterFile)) {
                $masterContent = File::get($masterFile);
                $masterTranslations = json_decode($masterContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning("Invalid JSON in master file {$masterFile}, will be recreated");
                    $masterTranslations = [];
                }
            }

            // Get all active modules
            $modules = $this->getActiveModules();

            foreach ($modules as $module) {
                try {
                    $moduleResult = $this->syncSingleModuleToMaster($module, $masterTranslations, $type);
                    $results['added'] += $moduleResult['added'];
                    $results['skipped'] += $moduleResult['skipped'];
                    if ($moduleResult['processed']) {
                        $results['modules_processed']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Module {$module}: ".$e->getMessage();
                }
            }

            // Write updated master file if there are changes
            if ($results['added'] > 0) {
                // Ensure master directory exists
                $masterDir = dirname($masterFile);
                if (! File::exists($masterDir)) {
                    File::makeDirectory($masterDir, 0755, true);
                }

                $jsonContent = json_encode($masterTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                File::put($masterFile, $jsonContent);

                Log::info("Master file sync completed for {$type}", $results);
            }

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Sync a single module to master file.
     */
    protected function syncSingleModuleToMaster(string $module, array &$masterTranslations, string $type = 'admin'): array
    {
        $results = ['added' => 0, 'skipped' => 0, 'processed' => false];

        // Determine module file path
        if ($type === 'admin') {
            $moduleFile = base_path("Modules/{$module}/resources/lang/en.json");
        } else {
            $moduleFile = base_path("Modules/{$module}/resources/lang/tenant_en.json");
        }

        // Skip if module file doesn't exist
        if (! File::exists($moduleFile)) {
            return $results;
        }

        // Read module translations
        $moduleContent = File::get($moduleFile);
        $moduleTranslations = json_decode($moduleContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Invalid JSON in module file {$moduleFile}, skipping");

            return $results;
        }

        // Find missing keys in master file that exist in module file
        $missingKeys = $this->findMissingKeys($moduleTranslations, $masterTranslations);

        // Add missing translations from module file to master file
        foreach ($missingKeys as $key) {
            if (isset($moduleTranslations[$key])) {
                $masterTranslations[$key] = $moduleTranslations[$key];
                $results['added']++;
            }
        }

        // Count skipped (existing) keys
        $results['skipped'] = count(array_intersect(array_keys($moduleTranslations), array_keys($masterTranslations))) - $results['added'];

        $results['processed'] = true;

        return $results;
    }
}
