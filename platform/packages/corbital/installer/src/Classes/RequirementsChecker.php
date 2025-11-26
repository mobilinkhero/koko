<?php

namespace Corbital\Installer\Classes;

class RequirementsChecker
{
    /**
     * The required PHP extensions and modules.
     */
    protected array $requirements;

    /**
     * The minimum PHP version required.
     */
    protected string $minPhpVersion;

    /**
     * Initialize new RequirementsChecker instance.
     */
    public function __construct(?array $requirements = null, ?string $minPhpVersion = null)
    {
        $this->requirements = $requirements ?? config('installer.requirements', []);
        $this->minPhpVersion = $minPhpVersion ?? config('installer.minPhpVersion', '8.3');
    }

    /**
     * Check the installer requirements.
     */
    public function check(): array
    {
        $results = $this->createEmptyResultSet();

        foreach ($this->requirements as $type => $requirement) {
            switch ($type) {
                case 'php':
                    $checks = $this->checkPHPRequirements($this->requirements[$type]);

                    $results['results'][$type] = array_merge($results['results'][$type], $checks);

                    if ($this->determineIfFails($checks)) {
                        $results['errors'] = true;
                    }

                    break;

                case 'functions':
                    $checks = $this->checkPHPFunctions($this->requirements[$type]);

                    $results['results'][$type] = array_merge($results['results'][$type], $checks);

                    if ($this->determineIfFails($checks)) {
                        $results['errors'] = true;
                    }

                    break;

                case 'apache':
                    // Only check Apache modules if Apache is detected
                    if ($this->isApacheServer()) {
                        foreach ($this->requirements[$type] as $requirement) {
                            if (function_exists('apache_get_modules')) {
                                $results['results'][$type][$requirement] = true;

                                if (! in_array($requirement, apache_get_modules())) {
                                    $results['results'][$type][$requirement] = false;

                                    $results['errors'] = true;
                                }
                            } else {
                                // Mark as not applicable if apache_get_modules function is not available
                                $results['results'][$type][$requirement] = 'not_applicable';
                            }
                        }
                    } else {
                        // Mark as not applicable if not using Apache
                        foreach ($this->requirements[$type] as $requirement) {
                            $results['results'][$type][$requirement] = 'not_applicable';
                        }
                    }
                    break;

                case 'nginx':
                    // Only check Nginx requirements if Nginx is detected
                    if ($this->isNginxServer()) {
                        $results['results'][$type]['installed'] = true;
                    } else {
                        $results['results'][$type]['installed'] = 'not_applicable';
                    }
                    break;

                case 'recommended':
                    $results['recommended']['php'] = $this->checkPHPRequirements($this->requirements[$type]['php'] ?? []);
                    $results['recommended']['functions'] = $this->checkPHPFunctions($this->requirements[$type]['functions'] ?? []);
                    break;
            }
        }

        // Add web server info
        $results['web_server'] = $this->getWebServerInfo();

        return $results;
    }

    /**
     * Get web server information.
     */
    protected function getWebServerInfo(): array
    {
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';

        if ($this->isApacheServer()) {
            return [
                'name' => 'Apache',
                'version' => $this->extractServerVersion($serverSoftware),
            ];
        } elseif ($this->isNginxServer()) {
            return [
                'name' => 'Nginx',
                'version' => $this->extractServerVersion($serverSoftware),
            ];
        }

        return [
            'name' => $serverSoftware,
            'version' => $this->extractServerVersion($serverSoftware),
        ];
    }

    /**
     * Extract server version from SERVER_SOFTWARE.
     */
    protected function extractServerVersion(string $serverSoftware): string
    {
        if (preg_match('/([^\s]+)\/([^\s]+)/', $serverSoftware, $matches)) {
            return $matches[2] ?? '';
        }

        return '';
    }

    /**
     * Check if the server is running Apache.
     */
    protected function isApacheServer(): bool
    {
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';

        return stripos($serverSoftware, 'apache') !== false;
    }

    /**
     * Check if the server is running Nginx.
     */
    protected function isNginxServer(): bool
    {
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';

        return stripos($serverSoftware, 'nginx') !== false;
    }

    /**
     * Check whether the given PHP requirement passes.
     */
    public function passes(string $requirement): bool
    {
        $requirements = $this->check();

        if (! array_key_exists($requirement, $requirements['recommended']['php'])) {
            return $requirements['results']['php'][$requirement] ?? true;
        }

        return $requirements['recommended']['php'][$requirement];
    }

    /**
     * Check whether the given PHP requirement fails.
     */
    public function fails(string $requirement): bool
    {
        return ! $this->passes($requirement);
    }

    /**
     * Check the php requirements.
     */
    protected function checkPHPRequirements(array $requirements): array
    {
        $results = [];

        foreach ($requirements as $requirement) {
            $results[$requirement] = $this->extensionLoaded($requirement);
        }

        return $results;
    }

    /**
     * Check the PHP functions requirements.
     */
    protected function checkPHPFunctions(array $functions): array
    {
        $results = [];

        foreach ($functions as $function) {
            $results[$function] = $this->functionExists($function);
        }

        return $results;
    }

    /**
     * Determine if all checks fails.
     */
    protected function determineIfFails(array $checks): bool
    {
        $filtered = array_filter($checks, function ($value) {
            // Skip 'not_applicable' values when checking for failures
            return $value !== 'not_applicable';
        });

        return count(array_filter($filtered)) !== count($filtered);
    }

    /**
     * Check PHP version requirement.
     */
    public function checkPHPVersion(): array
    {
        $currentPhpVersion = static::getPhpVersionInfo();

        return [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $this->minPhpVersion,
            'supported' => $this->isSupportedPHPVersion($currentPhpVersion['version']),
        ];
    }

    /**
     * Check whether the given extension is loaded.
     */
    protected function extensionLoaded(string $extension): bool
    {
        return extension_loaded($extension);
    }

    /**
     * Check whether the given function exists.
     */
    protected function functionExists(string $function): bool
    {
        return function_exists($function);
    }

    /**
     * Check whether the PHP version is supported.
     */
    protected function isSupportedPHPVersion(string $currentPhpVersion): bool
    {
        return version_compare($currentPhpVersion, $this->minPhpVersion, '>=');
    }

    /**
     * Get current PHP version information.
     */
    protected static function getPhpVersionInfo(): array
    {
        $currentVersionFull = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);
        $currentVersion = $filtered[0];

        return [
            'full' => $currentVersionFull,
            'version' => $currentVersion,
        ];
    }

    /**
     * Create empty result set.
     */
    protected function createEmptyResultSet(): array
    {
        return [
            'results' => [
                'php' => [],
                'functions' => [],
                'apache' => [],
                'nginx' => [],
            ],
            'recommended' => [
                'php' => [],
                'functions' => [],
            ],
            'errors' => false,
        ];
    }
}
