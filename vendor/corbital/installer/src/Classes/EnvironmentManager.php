<?php

namespace Corbital\Installer\Classes;

use Exception;
use Illuminate\Support\Str;

class EnvironmentManager
{
    /**
     * Environment file path.
     */
    protected string $envFilePath;

    /**
     * Initialize new EnvironmentManager instance.
     */
    public function __construct(?string $envFilePath = null)
    {
        $this->envFilePath = $envFilePath ?: app()->environmentFilePath();
    }

    /**
     * Save the environment variables to the .env file.
     *
     * @param  array  $values  Key-value pairs of environment variables
     */
    public function saveEnv(array $values): bool
    {
        // First check if the .env file exists
        if (! file_exists($this->getEnvFilePath())) {
            if (! file_exists(dirname($this->getEnvFilePath()))) {
                // Create directory if it doesn't exist
                if (! mkdir(dirname($this->getEnvFilePath()), 0755, true)) {
                    throw new Exception('Could not create directory: '.dirname($this->getEnvFilePath()));
                }
            }

            // Create the file if it doesn't exist
            if (! touch($this->getEnvFilePath())) {
                throw new Exception('Could not create environment file: '.$this->getEnvFilePath());
            }
        }

        // Check if the file is writable
        if (! is_writable($this->getEnvFilePath())) {
            throw new Exception('Environment file is not writable: '.$this->getEnvFilePath());
        }

        // Read current env file content
        try {
            $envFile = file_get_contents($this->getEnvFilePath());
        } catch (Exception $e) {
            throw new Exception('Could not read environment file: '.$e->getMessage());
        }

        // If the file is empty, let's prepare a new one
        if (empty($envFile)) {
            $envFile = $this->getDefaultEnvContent();
        }

        // Loop through the values and update the .env file
        foreach ($values as $key => $value) {
            // Format the value properly for the .env file
            $value = $this->formatEnvValue($value);

            if (strpos($envFile, $key.'=') !== false) {
                // Replace the existing value
                $envFile = preg_replace(
                    '/^'.preg_quote($key, '/').'=.*$/m',
                    $key.'='.$value,
                    $envFile
                );
            } else {
                // Add the variable to the end of the file
                $envFile .= PHP_EOL.$key.'='.$value;
            }
        }

        // Write updated content back to the file
        try {
            $result = file_put_contents($this->getEnvFilePath(), $envFile);

            return $result !== false;
        } catch (Exception $e) {
            throw new Exception('Could not write to environment file: '.$e->getMessage());
        }
    }

    /**
     * Format the environment value.
     *
     * @param  mixed  $value
     */
    protected function formatEnvValue($value): string
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === '') {
            return '';
        }

        // Check if the value contains a space, quote, or special characters
        if (preg_match('/[\s\'"\\\\#]/', $value)) {
            // Escape single quotes
            $value = str_replace("'", "\'", $value);

            // Wrap in single quotes
            return "'".$value."'";
        }

        return $value;
    }

    /**
     * Generate a new APP_KEY.
     */
    public function generateAppKey(): string
    {
        return 'base64:'.base64_encode(Str::random(32));
    }

    /**
     * Generate a new identification key.
     */
    public function generateIdentificationKey(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Get default environment file content.
     */
    protected function getDefaultEnvContent(): string
    {
        return <<<'EOT'
APP_NAME=WhatsMark
APP_ENV=production
APP_KEY=base64:dk1nSmxyUGhoUml3WGFXWWpqSEU0NTc2ajdKcEtFWEo=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lv
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

VITE_APP_NAME="${APP_NAME}"
EOT;
    }

    /**
     * Get the environment file path.
     */
    public function getEnvFilePath(): string
    {
        return $this->envFilePath;
    }

    /**
     * Guess the application URL.
     */
    public static function guessUrl(): string
    {
        $guessedUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $guessedUrl .= '://'.($_SERVER['HTTP_HOST'] ?? 'localhost');

        if (! isset($_SERVER['HERD_SITE_PATH']) && ! isset($_SERVER['HERD_HOME'])) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            if ($scriptName) {
                $guessedUrl .= str_replace(basename($scriptName), '', $scriptName);
            }
        }

        $guessedUrl = preg_replace('/install.*/', '', $guessedUrl);

        return rtrim($guessedUrl, '/');
    }
}
