<?php

namespace Corbital\ModuleManager\Classes;

// use Illuminate\Support\Facades\Config;
// use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Corbital\Installer\Classes\EnvironmentManager;
use Corbital\ModuleManager\Models\Module;
use Corbital\ModuleManager\Services\ModuleManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class ModuleUpdateChecker
{
    public $url;

    protected $moduleManager;

    public function __construct()
    {
        $this->moduleManager = app(ModuleManager::class);
        $environment = new EnvironmentManager;
        $this->url = $environment->guessUrl();
    }

    public function validateEnvatoPurchase(string $username, string $purchaseCode, string $moduleName, string $item_id)
    {
        try {
            $activatedDomain = $this->url;

            // Step 1: Pre-validate the license
            $apiEndpoint = rtrim(base64_decode(config('installer.license_verification.api_endpoint')), '/').'/pre-validate';

            $response = Http::timeout(60)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($apiEndpoint, [
                    'purchase_code' => $purchaseCode,
                    'username' => $username,
                    'activated_domain' => $activatedDomain,
                    'product_id' => $item_id,
                ]);

            // Parse the response
            $responseData = $response->json();
            if (! isset($responseData['success']) || $responseData['success'] !== true) {
                $errorMessages = $responseData['errors'] ?? [$responseData['message'] ?? 'License validation failed'];

                if (is_array($errorMessages) && isset($errorMessages['purchase_code'])) {
                    return [
                        'success' => false,
                        'message' => $errorMessages['purchase_code'][0] ?? 'Invalid purchase code',
                    ];
                }

                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'License validation failed',
                ];
            }

            $envatoRes = $responseData['data'] ?? [];

            if (empty($envatoRes)) {
                return [
                    'success' => false,
                    'message' => 'Something went wrong, please try again',
                ];
            }

            if ($item_id != $envatoRes['item']['id']) {
                return [
                    'success' => false,
                    'message' => 'Purchase key is not valid',
                ];
            }

            $moduleData = $this->moduleManager->get($moduleName);

            // Get system information
            $userAgent = request()->header('User-Agent');

            // Prepare data for registration
            $registrationData = [
                'user_agent' => $this->getBrowserFromUserAgent($userAgent),
                'activated_domain' => $activatedDomain,
                'requested_at' => now()->format('Y-m-d H:i:s'),
                'ip' => request()->ip(),
                'os' => $this->getOSFromUserAgent($userAgent),
                'purchase_code' => $purchaseCode,
                'installed_version' => $moduleData['info']['version'] ?? '1.0.0',
                'envato_res' => $envatoRes,
                'username' => $username,
            ];

            $supported_until = $envatoRes['supported_until'];

            // Send registration request
            $registrationEndpoint = rtrim(base64_decode(config('installer.license_verification.api_endpoint')), '/').'/register';

            $registrationResponse = Http::timeout(60)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($registrationEndpoint, $registrationData);

            // Parse the registration response
            $registrationResponseData = $registrationResponse->json();

            // If registration failed
            if (! isset($registrationResponseData['success']) || $registrationResponseData['success'] !== true) {
                return [
                    'success' => false,
                    'message' => $registrationResponseData['message'] ?? 'License registration failed',
                ];
            }

            session([
                'module_license_data' => [
                    'username' => $username,
                    'purchase_code' => $purchaseCode,
                    'verified' => true,
                    'details' => $responseData['data'] ?? [],
                    'token' => $registrationResponseData['data']['token'] ?? '',
                    'verification_id' => $registrationResponseData['data']['verification_id'] ?? '',
                    'support_until' => $supported_until,
                    'module_name' => $moduleName,
                    'item_id' => $item_id,
                ],
            ]);

            return [
                'success' => true,
                'message' => 'License validated successfully',
            ];
        } catch (\Exception $e) {
            app_log('Envato API validation failed: '.$e->getMessage(), 'error', $e, [
                'module' => $moduleName,
                'username' => $username,
            ]);

            return [
                'success' => false,
                'message' => 'License registration failed: '.$e->getMessage(),
            ];
        }
    }

    public function installVersion($data, $moduleData = [])
    {
        $update = $this->checkUpdate($data['token'], $moduleData);

        if ($update['success'] == true) {
            $download = $this->downloadUpdate($update['data']['update_id'], $update['data']['has_sql_update'], $update['data']['latest_version'], $data['token'], $data['purchase_code'], $data['username'], 'install', $data['item_id']);
            if ($download['success'] == true) {
                $data['last_verification'] = now()->timestamp;
                $data['verification_id'] = base64_encode($data['verification_id']);
                $data['verification_token'] = $data['verification_id'].'|'.$data['token'];
                Module::updateOrCreate(
                    ['item_id' => $data['item_id']], // Condition to find
                    [
                        'name' => $data['module_name'],
                        'version' => $update['data']['latest_version'],
                        'active' => false,
                        'payload' => json_encode($data),
                    ]
                );
            }
        }
    }

    public function checkUpdate($token, $moduleData = [], $mode = 'install')
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ])
            ->post(rtrim(base64_decode(config('installer.license_verification.api_endpoint')), '/').'/check-update', [
                'item_id' => $moduleData['item_id'],
                'version' => $moduleData['version'],
                'initial' => true,
                'mode' => $mode,
            ]);

        return json_decode($response->getBody(), true);
    }

    public function downloadUpdate(
        string $updateId,
        bool $needsSqlUpdate,
        string $version,
        string $token,
        ?string $license,
        ?string $client,
        ?string $mode,
        string $item_id
    ) {
        try {
            $mainFile = $this->downloadFile('main', $updateId, $version, $token, $license, $client);

            if (isset($mainFile['success'])) {
                return [
                    'success' => false,
                    'message' => $mainFile['message'],
                ];
            }
            $this->extractUpdate($mainFile, 'main', $mode, $item_id);

            if ($needsSqlUpdate) {
                $sqlFile = $this->downloadFile('sql', $updateId, $version, $token, $license, $client);
                $this->extractUpdate($sqlFile, 'sql', $mode, $item_id);
            }

            return [
                'success' => true,
                'message' => 'Update completed',
            ];
        } catch (\Throwable $e) {

            app_log('Update failed', 'error', $e, [
                $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function downloadFile(string $type, string $updateId, string $version, string $token, ?string $license, ?string $client)
    {
        $destination = config('installer.license_verification.root_path')."/modules/update_{$type}_{$version}.zip";

        if (! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ])
            ->sink($destination)
            ->post(rtrim(base64_decode(config('installer.license_verification.api_endpoint')), '/')."/download-update/$type/$updateId", [
                'license_code' => $license,
                'client_name' => $client,
                'activated_domain' => $this->url,
            ]);

        $responseData = $response->json();

        // If validation failed
        if (isset($responseData['success']) && $responseData['success'] != true) {
            $errorMessages = $responseData['errors'] ?? [$responseData['message'] ?? 'License validation failed'];

            if (is_array($errorMessages) && isset($errorMessages['license_code'])) {
                return [
                    'success' => $response['success'],
                    'message' => $errorMessages['license_code'][0],
                ];
            }

            return [
                'success' => $response['success'],
                'message' => $response['message'],
            ];
        }

        return $destination;
    }

    private function extractUpdate(string $zipFile, string $type, string $mode, string $item_id): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipFile) !== true) {
            throw new \RuntimeException('Failed to open update file');
        }

        try {

            if ($type === 'main' && ! empty($item_id)) {
                // VALIDATE ZIP CONTENTS BEFORE EXTRACTION
                $this->validateZipContents($zip, $item_id);
            }

            // Define extraction path
            $extractPath = base_path('Modules');

            // Ensure directory exists
            if (! File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }

            // Extract ZIP file
            $zip->extractTo($extractPath);
            $zip->close();

            // Delete the original ZIP file after extraction
            File::delete($zipFile);

            // Handle SQL type (extract and import SQL)
            if ($type === 'sql') {
                $this->importSQLFromExtractedFiles($extractPath);
                if ($mode != 'install') {
                    Artisan::call('db:seed', ['--force' => true]);
                    Artisan::call('migrate', ['--force' => true]);

                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');

                    Artisan::call('files:cleanup', [
                        '--file' => 'unused-files.json',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Clean up resources
            $zip->close();
            File::delete($zipFile);
            throw $e;
        }
    }

    /**
     * Validate ZIP contents before extraction
     */
    private function validateZipContents(ZipArchive $zip, string $item_id): void
    {
        $module = get_module($item_id);

        if (! empty($module)) {
            $requiredFiles = [
                '/vendor/composer/autoload_classmap.php',
                '/vendor/composer/autoload_psr4.php',
                '/vendor/composer/autoload_static.php',
            ];

            $missingFiles = [];

            foreach ($requiredFiles as $file) {
                if ($this->fileExistsInZip($zip, $file)) {
                    File::delete(module_path($module['name']).$file);
                }
            }
        }
    }

    /**
     * Check if file exists in ZIP with flexible matching
     */
    private function fileExistsInZip(ZipArchive $zip, string $filename): bool
    {
        // Try exact match first
        if ($zip->locateName($filename) !== false) {
            return true;
        }

        // Try case-insensitive match
        if ($zip->locateName($filename, ZIPARCHIVE::FL_NOCASE) !== false) {

            return true;
        }

        // Try filename only (ignore directory structure)
        if ($zip->locateName(basename($filename), ZIPARCHIVE::FL_NODIR) !== false) {

            return true;
        }

        // Try both case-insensitive AND ignore directory
        if ($zip->locateName(basename($filename), ZIPARCHIVE::FL_NOCASE | ZIPARCHIVE::FL_NODIR) !== false) {

            return true;
        }

        return false;
    }

    /**
     * Check for SQL file and import it into the database.
     */
    private function importSQLFromExtractedFiles(string $extractPath): void
    {
        $sqlFile = null;

        // Search for an SQL file in the extracted directory
        foreach (scandir($extractPath) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $sqlFile = $extractPath.'/'.$file;
                break;
            }
        }

        if (! $sqlFile) {
            throw new \RuntimeException('No SQL file found in extracted update.');
        }

        // Read and execute the SQL file
        try {
            $sql = file_get_contents($sqlFile);
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::unprepared($sql);
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Delete SQL file after successful import
            File::delete($sqlFile);
        } catch (\Exception $e) {
            throw new \RuntimeException('SQL import failed: '.$e->getMessage());
        }
    }

    public function checkSupportExpiryStatus($supportedUntil = '')
    {
        if ($supportedUntil) {
            $supportedDate = Carbon::parse($supportedUntil)->addDay();
            $currentDate = Carbon::now();

            if ($currentDate->greaterThanOrEqualTo($supportedDate)) {
                return [
                    'success' => false,
                    'type' => 'danger',
                    'message' => 'Support has already expired.',
                    'time_diff' => '',
                    'support_url' => trim(base64_decode(config('installer.license_verification.support_url'))),
                ];
            }

            $timeDiff = $currentDate->diff($supportedDate)->format('%m months %d days');

            return [
                'success' => true,
                'type' => 'success',
                'message' => "Support will expire on {$supportedDate->format('d M, Y')} ({$timeDiff}).",
                'time_diff' => "{$timeDiff} left",
                'support_url' => trim(base64_decode(config('installer.license_verification.support_url'))),
            ];
        }

        return [];
    }

    public function getVersionLog($item_id)
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(rtrim(base64_decode(config('installer.license_verification.api_endpoint')), '/')."/products/$item_id");

        return json_decode($response->getBody(), true);
    }

    public function validateRequest(string $moduleName)
    {
        $settings = get_module($moduleName);
        if (empty($settings)) {
            return false;
        }
        $token = explode('|', $settings['payload']['verification_token'])[1];
        $verification_id = ! empty($settings['payload']['verification_id']) ? base64_decode($settings['payload']['verification_id']) : '';
        $id_data = explode('|', $verification_id);
        $verified = ! ((empty($verification_id)) || (\count($id_data) != 4));
        $verification_id = ! empty($settings['payload']['verification_id']) ? base64_decode($settings['payload']['verification_id']) : '';

        if (\count($id_data) === 4) {
            $verified = ! empty($token);
            try {
                $data = json_decode(base64_decode(explode('.', $token)[0]));

                if (! empty($data)) {
                    $verified = $data->item_id == $settings['item_id'] && $data->item_id == $id_data[0] && $data->buyer == $id_data[2] && $data->purchase_code == $id_data[3];
                }
            } catch (\Exception $e) {
                $verified = false;
            }

            $last_verification = (int) $settings['payload']['last_verification'];
            $seconds = $data->check_interval ?? 0;
            if (! empty($seconds) && time() > ($last_verification + $seconds)) {
                $verified = false;
                try {
                    $response = Http::timeout(60)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer '.$token,
                        ])
                        ->post(rtrim(base64_decode(config('installer.license_verification.api_endpoint')), '/').'/validate', [
                            'verification_id' => $verification_id,
                            'item_id' => $settings['item_id'],
                            'activated_domain' => $this->url,
                            'version' => $settings['version'],
                            'purchase_code' => $id_data[3],
                        ]);

                    $result = json_decode($response->getBody(), true);
                    $verified = $result['success'] ?? false;
                } catch (\Exception $e) {
                    $verified = false;
                }
                $settings['payload'] = array_merge($settings['payload'] ?? [], [
                    'last_verification' => now()->timestamp,
                ]);
                Module::updateOrCreate(
                    ['item_id' => $settings['item_id']], // Condition to find
                    ['payload' => json_encode($settings['payload'])]
                );
            }
        }

        return $verified;
    }

    /**
     * Extract OS information from User-Agent
     *
     * @param  string  $userAgent
     * @return string
     */
    private function getOSFromUserAgent($userAgent)
    {
        $os = 'Unknown OS';

        $osPlatforms = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
        ];

        foreach ($osPlatforms as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $os = $value;
                break;
            }
        }

        return $os;
    }

    /**
     * Extract browser information from User-Agent
     *
     * @param  string  $userAgent
     * @return string
     */
    private function getBrowserFromUserAgent($userAgent)
    {
        $browser = 'Unknown Browser';

        $browsers = [
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Mobile Browser',
        ];

        foreach ($browsers as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $browser = $value;
                break;
            }
        }

        // Get version number
        $knownBrowsers = ['Firefox', 'Chrome', 'Safari', 'Opera', 'Edge'];
        if (in_array($browser, $knownBrowsers)) {
            $pattern = '#(?<browser>'.preg_quote($browser, '#').')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (preg_match($pattern, $userAgent, $matches)) {
                $browser = $browser.' '.$matches['version'];
            }
        }

        return $browser;
    }
}

