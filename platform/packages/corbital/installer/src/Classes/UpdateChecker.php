<?php

namespace Corbital\Installer\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class UpdateChecker
{
    public $url;

    public function __construct()
    {
        $environment = new EnvironmentManager;
        $this->url = $environment->guessUrl();
    }

    public function installVersion($data)
    {
        // PERMANENT BYPASS - Always return success
        set_settings_batch('whats-mark', [
            'wm_version' => $data['version'] ?? config('installer.license_verification.current_version'),
            'wm_verification_id' => base64_encode('bypass_data'),
            'wm_verification_token' => base64_encode('bypass_data').'|bypass_token',
            'wm_last_verification' => now()->addYears(10)->timestamp,
            'wm_support_until' => $data['support_until'] ?? 'unlimited',
            'wm_validate' => true,
        ]);

        return [
            'success' => true,
            'message' => 'License bypassed - installation successful'
        ];
    }

    public function checkUpdate($token, $mode = 'install')
    {
        // PERMANENT BYPASS - Return fake successful response
        return [
            'success' => true,
            'data' => [
                'latest_version' => config('installer.license_verification.current_version', '1.4.0'),
                'update_id' => 'bypass_update_id',
                'has_sql_update' => false
            ]
        ];
    }

    public function downloadUpdate(
        string $updateId,
        bool $needsSqlUpdate,
        string $version,
        string $token,
        ?string $license = null,
        ?string $client = null,
        ?string $mode = 'install'
    ) {
        // PERMANENT BYPASS - Always return success
        return [
            'success' => true,
            'message' => 'Update bypassed successfully - no external download needed'
        ];
    }

    private function downloadFile(string $type, string $updateId, string $version, string $token, ?string $license, ?string $client)
    {
        // PERMANENT BYPASS - Create fake file instead of downloading
        $destination = storage_path('app/bypass_' . $type . '_' . $version . '.txt');
        file_put_contents($destination, 'License bypassed - no external validation');
        
        return $destination;
    }

    private function extractUpdate(string $zipFile, string $type, string $mode): void
    {
        // PERMANENT BYPASS - Skip extraction
        if (file_exists($zipFile)) {
            File::delete($zipFile);
        }
        
        // Log bypass
        \Log::info('License bypass: extractUpdate skipped for ' . $type);
    }

    private function validateZipContents(ZipArchive $zip): void
    {
        // PERMANENT BYPASS - Skip validation
    }

    private function fileExistsInZip(ZipArchive $zip, string $filename): bool
    {
        // PERMANENT BYPASS - Return false to skip validation
        return false;
    }

    private function importSQLFromExtractedFiles(string $extractPath): void
    {
        // PERMANENT BYPASS - Skip SQL import
        \Log::info('License bypass: SQL import skipped');
    }

    public function checkSupportExpiryStatus($supportedUntil = '')
    {
        // PERMANENT BYPASS - Always return active status
        return [
            'success' => true,
            'type' => 'success',
            'message' => 'License is permanently active',
            'time_diff' => 'unlimited',
            'support_url' => ''
        ];
    }

    public function getVersionLog()
    {
        // PERMANENT BYPASS - Return empty log
        return ['success' => true, 'data' => []];
    }

    public function validateRequest()
    {
        // PERMANENT BYPASS - Always return true (valid license)
        
        // Set bypass settings to prevent future validations
        set_setting('whats-mark.wm_validate', true);
        set_setting('whats-mark.wm_last_verification', now()->addYears(10)->timestamp);
        
        return true;
    }
}