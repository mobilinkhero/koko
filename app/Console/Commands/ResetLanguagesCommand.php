<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Console\Command;

class ResetLanguagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'languages:reset {--admin : Reset only admin languages} {--tenant-id= : Reset languages for specific tenant ID} {--sync-public : Check public/lang files first for tenant languages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and sync admin and tenant language files with latest translations';

    /**
     * The language service instance.
     */
    protected LanguageService $languageService;

    /**
     * Create a new command instance.
     */
    public function __construct(LanguageService $languageService)
    {
        parent::__construct();
        $this->languageService = $languageService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $resetAdmin = $this->option('admin');
        $tenantId = $this->option('tenant-id');

        // If no options provided, ask user what to reset
        if (! $resetAdmin && ! $tenantId) {
            $choice = $this->choice(
                'What would you like to reset?',
                [
                    'admin' => 'Admin languages only',
                    'tenant' => 'Specific tenant by ID',
                ],
                'admin'
            );

            if ($choice === 'admin') {
                $resetAdmin = true;
            } else {
                $tenantId = $this->ask('Enter tenant ID:');
            }
        }

        $this->info('Starting language reset process...');
        $this->newLine();

        try {
            if ($resetAdmin) {
                $this->syncModulesToMasterFiles('admin');
                $this->resetAdminLanguages();
            }

            if ($tenantId) {
                $this->syncModulesToMasterFiles('tenant');
                $this->resetSpecificTenantLanguages($tenantId);
            }

            $this->newLine();
            $this->info('Language reset completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during language reset: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Reset admin language files.
     */
    protected function resetAdminLanguages(): void
    {
        $this->info('Resetting admin languages...');

        // Get all admin languages except English (master)
        $adminLanguages = Language::whereNull('tenant_id')
            ->where('code', '!=', 'en')
            ->get();

        if ($adminLanguages->isEmpty()) {
            $this->warn('No active admin languages found (excluding master English file).');

            return;
        }

        $this->info("Found {$adminLanguages->count()} admin language(s): ".$adminLanguages->pluck('name')->implode(', '));

        // Reset each admin language
        foreach ($adminLanguages as $language) {
            try {
                $this->line("Processing {$language->name} ({$language->code})...");
                $result = $this->languageService->resetAdminLanguage($language->code);

                if (isset($result['error'])) {
                    $this->error("{$result['error']}");

                    continue;
                }

                $this->line("Added {$result['added']} new translations");
                if ($result['skipped'] > 0) {
                    $this->line("Skipped {$result['skipped']} existing translations");
                }
            } catch (\Exception $e) {
                $this->error(" Failed to reset {$language->name}: ".$e->getMessage());
            }
        }

        $this->info('Admin languages reset completed.');
    }

    /**
     * Reset languages for a specific tenant.
     */
    protected function resetSpecificTenantLanguages(int $tenantId): void
    {
        $this->info("Resetting languages for tenant ID: {$tenantId}...");

        // Check if tenant exists
        $tenantExists = Language::where('tenant_id', $tenantId)->exists();
        if (! $tenantExists) {
            $this->error("No languages found for tenant ID: {$tenantId}");

            return;
        }

        // Get languages for this specific tenant (except English master)
        $tenantLanguages = Language::where('tenant_id', $tenantId)
            ->where('code', '!=', 'en')
            ->get();

        if ($tenantLanguages->isEmpty()) {
            $this->warn("No active languages found for tenant ID: {$tenantId} (excluding master English file).");

            return;
        }

        $this->info("Found {$tenantLanguages->count()} language(s) for tenant {$tenantId}: ".$tenantLanguages->pluck('name')->implode(', '));

        $syncPublic = $this->option('sync-public');
        if ($syncPublic) {
            $this->info('Checking public/lang directory for existing translations first...');
        }

        // Reset each language for this tenant
        foreach ($tenantLanguages as $language) {
            try {
                $this->line("Processing {$language->name} ({$language->code})...");

                if ($syncPublic) {
                    // Try to find public language file first
                    $publicPath = public_path("lang/tenant_{$language->code}.json");
                    if (file_exists($publicPath)) {
                        $this->line("Found public language file for {$language->code}");
                        $result = $this->languageService->resetSpecificTenantLanguageWithPublic(
                            $language->code,
                            $tenantId,
                            $publicPath
                        );
                    } else {
                        $this->line("No public language file found for {$language->code}, using default sync");
                        $result = $this->languageService->resetSpecificTenantLanguage($language->code, $tenantId);
                    }
                } else {
                    $result = $this->languageService->resetSpecificTenantLanguage($language->code, $tenantId);
                }

                if (isset($result['error'])) {
                    $this->error(" {$result['error']}");

                    continue;
                }

                if (isset($result['public_updated']) && $result['public_updated']) {
                    $this->line(' ⟲ Updated public language file');
                }

                $this->line(" ✓ Added {$result['added']} new translations");
                if ($result['skipped'] > 0) {
                    $this->line(" ⊝ Skipped {$result['skipped']} existing translations");
                }
                if (isset($result['merged']) && $result['merged'] > 0) {
                    $this->line(" ⟳ Merged {$result['merged']} translations from public file");
                }
                if (isset($result['value_changes']) && $result['value_changes'] > 0) {
                    $this->line(" ⟲ Updated {$result['value_changes']} translation values");
                }
            } catch (\Exception $e) {
                $this->error("Failed to reset {$language->name}: ".$e->getMessage());
            }
        }

        $this->info("Languages reset completed for tenant ID: {$tenantId}.");
    }

    /**
     * Sync module language files to master language files.
     */
    protected function syncModulesToMasterFiles(string $type): void
    {
        if ($type === 'admin') {
            $sourceType = 'en.json';
        } else {
            $sourceType = 'tenant_en.json';
        }

        try {
            $result = $this->languageService->syncModulesToMasterFile($type);

            if ($result['modules_processed'] > 0) {
                if ($result['skipped'] > 0) {
                    $this->line("  ⊝ Skipped {$result['skipped']} existing translations");
                }
            } else {
                $this->line("  ⊝ No modules found with {$sourceType} files");
            }

            // Show any errors
            if (! empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->error("  ✗ {$error}");
                }
            }

        } catch (\Exception $e) {
            $this->error('  ✗ Failed to sync modules to master file: '.$e->getMessage());
        }
    }
}
