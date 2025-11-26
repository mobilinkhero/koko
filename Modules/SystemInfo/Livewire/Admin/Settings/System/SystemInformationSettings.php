<?php

namespace Modules\SystemInfo\Livewire\Admin\Settings\System;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\SystemInfo\Services\SystemManagement;

class SystemInformationSettings extends Component
{
    public $system;

    public $server;

    public $packages;

    protected $systemManagement;

    public $isLoaded = false;

    public $isLoadingServer = false;

    public $isLoadingSystem = false;

    public $optionalExtensions = [];

    public $isLoadingExtensions = false;

    public function mount(SystemManagement $systemManagement)
    {
        /*  if (! checkPermission('system_settings.view')) {
             $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

             return redirect(route('admin.dashboard'));
         } */

        $this->systemManagement = $systemManagement;

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

        // Only load basic information initially
        $this->system = [
            'versions' => [
                'core' => 'Loading...',
                'framework' => app()->version(),
            ],
            'environment' => [
                'timezone' => config('app.timezone'),
                'debug' => config('app.debug'),
            ],
        ];

        $this->server = [
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'display_errors' => ini_get('display_errors'),
                'opcache_enabled' => ini_get('opcache.enable'),
                'max_execution_time' => ini_get('max_execution_time').' seconds',
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_input_vars' => ini_get('max_input_vars'),
                'error_reporting' => ini_get('error_reporting'),
                'interface' => PHP_SAPI,
                'timezone' => ini_get('date.timezone'),
            ],
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
            'server' => [
                'ssl' => request()->isSecure(),
            ],
            'extensions' => $info['extensions'],
            'database' => $dbInfo,
        ];
    }

    public function loadFullSystemInfo()
    {
        $this->isLoadingSystem = true;

        try {
            $info = $this->systemManagement->getInfo();
            $this->system = $info['system'];
            $this->isLoaded = true;
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => t('error_loading_system_info').$e->getMessage()]);
        }

        $this->isLoadingSystem = false;
    }

    public function loadFullServerInfo()
    {
        $this->isLoadingServer = true;

        try {
            $info = $this->systemManagement->getInfo();
            $this->server = $info['server'];
            $this->isLoaded = true;
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => t('error_loading_server_info').$e->getMessage()]);
        }

        $this->isLoadingServer = false;
    }

    public function loadOptionalExtensions()
    {
        $this->isLoadingExtensions = true;

        try {
            $this->optionalExtensions = $this->systemManagement->getOptionalExtensionsInfo();
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => t('error_loading_extension_info').$e->getMessage()]);
        }

        $this->isLoadingExtensions = false;
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

    public function render()
    {
        return view('SystemInfo::livewire.admin.settings.system.system-information-settings');
    }
}
