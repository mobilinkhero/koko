<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * System Management Service
 *
 * Provides comprehensive system information gathering and management capabilities for the multi-tenant SaaS application.
 * This service collects detailed information about the server environment, system resources, database configuration,
 * and application status for monitoring, debugging, and administrative purposes.
 *
 * Key Responsibilities:
 * - Server environment analysis and reporting
 * - Database configuration monitoring
 * - PHP and Laravel configuration tracking
 * - Resource usage assessment
 * - Storage and cache information
 * - Security and SSL status monitoring
 * - Environment change detection
 *
 * Information Categories:
 * - **Server Info**: OS details, software versions, SSL status
 * - **PHP Info**: Version, configuration, memory limits, extensions
 * - **Laravel Info**: Framework version, environment, debugging status
 * - **Database Info**: Version, connection details, configuration
 * - **Storage Info**: Disk usage, permissions, paths
 * - **Security Info**: SSL status, configuration security
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Services\EnvWatcher
 * @see \Illuminate\Support\Facades\DB
 * @see \Illuminate\Support\Facades\File
 *
 * @example
 * ```php
 * // Get comprehensive system information
 * $systemMgmt = app(SystemManagement::class);
 * $info = $systemMgmt->getInfo();
 *
 * // Access specific information
 * echo "PHP Version: " . $info['server']['php']['version'];
 * echo "Laravel Environment: " . $info['server']['laravel']['environment'];
 * echo "Database Version: " . $info['server']['database']['version'];
 *
 * // Check system resources
 * $storageInfo = $systemMgmt->getStorageInfo();
 * echo "Free Storage: " . $storageInfo['storage_path']['free_space'];
 * ```
 */
class SystemManagement
{
    /**
     * Environment watcher service instance
     */
    protected EnvWatcher $envWatcher;

    /**
     * Create a new system management service instance.
     *
     * @param  EnvWatcher  $envWatcher  Service for monitoring environment changes
     */
    public function __construct(EnvWatcher $envWatcher)
    {
        $this->envWatcher = $envWatcher;
    }

    /**
     * Get comprehensive system information.
     *
     * Retrieves detailed information about the server environment, system resources,
     * and application configuration. Includes automatic environment change detection.
     *
     * @return array Comprehensive system information array with 'server' and 'system' sections
     *
     * @example
     * ```php
     * $info = $this->systemManagement->getInfo();
     *
     * // Server information
     * $phpVersion = $info['server']['php']['version'];
     * $laravelEnv = $info['server']['laravel']['environment'];
     * $dbVersion = $info['server']['database']['version'];
     *
     * // System information
     * $diskUsage = $info['system']['storage'];
     * $permissions = $info['system']['permissions'];
     * ```
     *
     * @see getServerInfo()
     * @see getSystemInfo()
     * @see EnvWatcher::checkForChanges()
     */
    public function getInfo(): array
    {
        $this->envWatcher->checkForChanges();

        return [
            'server' => $this->getServerInfo(),
            'system' => $this->getSystemInfo(),
        ];
    }

    /**
     * Get detailed server environment information.
     *
     * Collects information about Laravel configuration, PHP settings, server software,
     * database configuration, and environment variables for system monitoring.
     *
     * @return array Server information including Laravel, PHP, server, and database details
     *
     * @example
     * ```php
     * $serverInfo = $this->getServerInfo();
     *
     * // Check PHP configuration
     * $memoryLimit = $serverInfo['php']['memory_limit'];
     * $maxExecTime = $serverInfo['php']['max_execution_time'];
     *
     * // Check Laravel settings
     * $isDebug = $serverInfo['laravel']['debug'];
     * $environment = $serverInfo['laravel']['environment'];
     *
     * // Check database status
     * $dbVersion = $serverInfo['database']['version'];
     * $maxConnections = $serverInfo['database']['max_connections'];
     * ```
     *
     * @see formatSize()
     *
     * @throws \Exception When database information cannot be retrieved
     */
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
            $dbConnection = DB::connection();
            $pdo = $dbConnection->getPdo();

            $dbInfo = [
                'version' => $pdo->query('SELECT version() as version')->fetch()['version'],
                'sql_mode' => DB::select('SELECT @@sql_mode as sql_mode')[0]->sql_mode,
                'max_connections' => DB::select('SELECT @@max_connections as max_conn')[0]->max_conn,
                'timezone' => DB::select('SELECT @@time_zone as timezone')[0]->timezone,
                'character_set' => DB::select('SELECT @@character_set_database as charset')[0]->charset,
                'collation' => DB::select('SELECT @@collation_database as collation')[0]->collation,
                'wait_timeout' => DB::select('SELECT @@wait_timeout as timeout')[0]->timeout.' seconds',
                'max_packet_size' => $this->formatSize(DB::select('SELECT @@max_allowed_packet as packet')[0]->packet),
                'buffer_pool_size' => $this->formatSize(DB::select('SELECT @@innodb_buffer_pool_size as buffer')[0]->buffer),
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

        $extensions = [
            'bcmath', 'ctype', 'fileinfo', 'json', 'mbstring',
            'openssl', 'pdo', 'tokenizer', 'xml', 'curl', 'zip',
            'gd', 'imagick', 'intl', 'redis', 'memcached', 'swoole',
        ];

        $info['extensions'] = [];
        foreach ($extensions as $ext) {
            $info['extensions'][$ext] = [
                'installed' => extension_loaded($ext),
                'version' => phpversion($ext),
            ];
        }

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
        return cache()->remember('app_size_'.md5($path), now()->addHour(), function () use ($path) {
            return collect(File::allFiles($path))->sum->getSize();
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
