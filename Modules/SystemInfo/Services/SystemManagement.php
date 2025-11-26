<?php

namespace Modules\SystemInfo\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemManagement
{
    protected EnvWatcher $envWatcher;

    public function __construct(EnvWatcher $envWatcher)
    {
        $this->envWatcher = $envWatcher;
    }

    public function getInfo(): array
    {
        $this->envWatcher->checkForChanges();

        // Add cache to avoid recalculating on every request
        return cache()->remember('system_info_cache', now()->addHour(), function () {
            return [
                'server' => $this->getServerInfo(),
                'system' => $this->getSystemInfo(),
            ];
        });
    }

    protected function getServerInfo(): array
    {
        $safeShellExec = function ($command) {
            if (! function_exists('shell_exec') || ! is_callable('shell_exec')) {
                return 'N/A (shell_exec disabled)';
            }
            try {
                $result = shell_exec($command);

                return $result ? trim($result) : 'N/A';
            } catch (\Exception $e) {
                app_log(t('error_executing_shell_command').' '.$e->getMessage(), 'error');

                return 'N/A (error)';
            }
        };

        try {
            // Combine multiple queries into a single query to reduce round trips
            $dbConnection = DB::connection();
            $pdo = $dbConnection->getPdo();

            // Get version separately as it needs a different format
            $version = $pdo->query('SELECT version() as version')->fetch()['version'];

            // Get multiple settings in a single query to reduce round trips and memory usage
            $dbSettings = DB::select('
                SELECT
                    @@sql_mode as sql_mode,
                    @@max_connections as max_conn,
                    @@time_zone as timezone,
                    @@character_set_database as charset,
                    @@collation_database as collation,
                    @@wait_timeout as timeout,
                    @@max_allowed_packet as packet,
                    @@innodb_buffer_pool_size as buffer
            ')[0];

            $dbInfo = [
                'version' => $version,
                'sql_mode' => $dbSettings->sql_mode,
                'max_connections' => $dbSettings->max_conn,
                'timezone' => $dbSettings->timezone,
                'character_set' => $dbSettings->charset,
                'collation' => $dbSettings->collation,
                'wait_timeout' => $dbSettings->timeout.' seconds',
                'max_packet_size' => $this->formatSize($dbSettings->packet),
                'buffer_pool_size' => $this->formatSize($dbSettings->buffer),
            ];
        } catch (\Exception $e) {
            $dbInfo = ['status' => t('unable_to_retrieve_database_information').' '.$e->getMessage()];
            app_log(t('database_info_retrieval_failed').' '.$e->getMessage(), 'error');
        }

        $info = [
            'laravel' => [
                'version' => app()->version(),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'maintenance' => app()->isDownForMaintenance(),
                'timezone' => config('app.timezone'),
                'locale' => app()->getLocale(),
                'cache_driver' => config('cache.default'),
                'log_channel' => config('logging.default'),
                'queue_driver' => config('queue.default'),
                'session_driver' => config('session.driver'),
                'storage_path' => storage_path(),
            ],

            'php' => [
                'version' => PHP_VERSION,
                'interface' => PHP_SAPI,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time').' seconds',
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_input_vars' => ini_get('max_input_vars'),
                'display_errors' => ini_get('display_errors'),
                'error_reporting' => ini_get('error_reporting'),
                'opcache_enabled' => ini_get('opcache.enable'),
                'timezone' => ini_get('date.timezone'),
            ],

            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'os' => php_uname(),
                'os_version' => php_uname('r'),
                'architecture' => php_uname('m'),
                'hostname' => gethostname(),
                'ssl' => request()->isSecure(),
                'ip' => request()->server('SERVER_ADDR') ?? request()->server('SERVER_NAME'),
                'port' => request()->server('SERVER_PORT'),
                'document_root' => request()->server('DOCUMENT_ROOT'),
                'server_admin' => request()->server('SERVER_ADMIN') ?? 'N/A',
                'total_disk_space' => $this->formatSize(disk_total_space('/')),
                'free_disk_space' => $this->formatSize(disk_free_space('/')),
                'cpu_cores' => php_sapi_name() === 'cli' ? $safeShellExec('nproc') : 'N/A',
                'total_ram' => php_sapi_name() === 'cli' ? $safeShellExec('grep MemTotal /proc/meminfo') : 'N/A',
            ],

            'database' => $dbInfo,

            'permissions' => [
                'storage_writeable' => is_writable(storage_path()),
                'cache_writeable' => is_writable(storage_path('framework/cache')),
                'logs_writeable' => is_writable(storage_path('logs')),
                'framework_writeable' => is_writable(storage_path('framework')),
                'bootstrap_cache_writeable' => is_writable(base_path('bootstrap/cache')),
            ],

            'env_status' => [
                'file_exists' => File::exists(base_path('.env')),
                'last_modified' => File::exists(base_path('.env'))
                ? date('Y-m-d H:i:s', File::lastModified(base_path('.env')))
                : null,
                'is_writable' => File::exists(base_path('.env'))
                ? File::isWritable(base_path('.env'))
                : false,
                'cache_status' => [
                    'config_cached' => File::exists(base_path('bootstrap/cache/config.php')),
                    'routes_cached' => File::exists(base_path('bootstrap/cache/routes-v7.php')),
                    'events_cached' => File::exists(base_path('bootstrap/cache/events.php')),
                ],
            ],
        ];

        // Check fewer extensions by default, group into critical and optional
        $criticalExtensions = [
            'bcmath', 'ctype', 'fileinfo', 'json', 'mbstring',
            'openssl', 'pdo', 'tokenizer', 'xml', 'curl', 'zip',
        ];

        $optionalExtensions = [
            'gd', 'imagick', 'intl', 'redis', 'memcached', 'swoole',
        ];

        // Only check critical extensions by default to save resources
        $info['extensions'] = [];
        foreach ($criticalExtensions as $ext) {
            $info['extensions'][$ext] = [
                'installed' => extension_loaded($ext),
                'version' => extension_loaded($ext) ? phpversion($ext) : null,
            ];
        }

        // Add placeholder for optional extensions
        $info['optional_extensions_count'] = count($optionalExtensions);

        if (extension_loaded('redis')) {
            try {
                $redis = Cache::store('redis')->connection();
                $info['redis'] = [
                    'connected' => $redis->ping(),
                    'version' => $redis->info()['redis_version'] ?? 'N/A',
                    'memory' => $redis->info()['used_memory_human'] ?? 'N/A',
                ];
            } catch (\Exception $e) {
                $info['redis'] = t('redis_connection_failed').' '.$e->getMessage();
                app_log(t('redis_connection_failed').' '.$e->getMessage(), 'error');
            }
        }

        return $info;
    }

    public function getOptionalExtensionsInfo(): array
    {
        $optionalExtensions = [
            'gd', 'imagick', 'intl', 'redis', 'memcached', 'swoole',
        ];

        $extensionsInfo = [];
        foreach ($optionalExtensions as $ext) {
            $extensionsInfo[$ext] = [
                'installed' => extension_loaded($ext),
                'version' => extension_loaded($ext) ? phpversion($ext) : null,
            ];
        }

        return $extensionsInfo;
    }

    protected function getSystemInfo(): array
    {
        return [
            'versions' => [
                'core' => $this->getCoreVersion(),
                'framework' => app()->version(),
            ],
            'environment' => [
                'timezone' => config('app.timezone'),
                'debug' => config('app.debug'),
            ],
            'storage' => [
                'directory_writable' => File::isWritable(storage_path()),
                'cache_writable' => File::isWritable(storage_path('framework/cache')),
                'app_size' => $this->formatSize($this->calculateSize(base_path())),
                'disk_space' => sprintf(
                    '%s/%s',
                    $this->formatSize(disk_free_space('/')),
                    $this->formatSize(disk_total_space('/'))
                ),
            ],
        ];
    }

    protected function getCoreVersion(): string
    {
        return collect(File::json(base_path('composer.lock'))['packages'])
            ->firstWhere('name', 'laravel/framework')['version'] ?? 'Unknown';
    }

    protected function calculateSize(string $path): float
    {
        return cache()->remember('app_size_'.md5($path), now()->addDay(), function () use ($path) {
            // Use more efficient approach to calculate directory size
            if (! is_dir($path)) {
                return 0;
            }

            // For Linux/Unix systems, use shell command for better performance
            if (PHP_OS !== 'WINNT' && function_exists('shell_exec')) {
                $size = shell_exec('du -sb '.escapeshellarg($path).' | cut -f1');
                if (is_numeric(trim($size))) {
                    return (float) $size;
                }
            }

            // Fallback to a more memory-efficient PHP implementation
            $size = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            $counter = 0;
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();

                    // Free memory periodically
                    if (++$counter % 1000 === 0) {
                        clearstatcache();
                    }
                }
            }

            return $size;
        });
    }

    protected function formatSize(float $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return sprintf(
            '%.2f %s',
            $size / (1024 ** $power),
            $units[$power]
        );
    }
}
