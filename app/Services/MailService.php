<?php

namespace App\Services;

use App\Facades\AdminCache;

/**
 * MailService - Handles email configuration global contexts
 *
 * This service optimizes email configuration by:
 * - Using AdminCache for improved cache management with tags
 * - Avoiding N+1 query problems through batch settings retrieval
 * - Implementing proper error handling and logging
 * - Following PSR-12 coding standards
 */
class MailService
{
    private const CACHE_KEY = 'mail_config';

    /**
     * Set mail configuration with caching and error handling.
     */
    public function setMailConfig(): bool
    {
        try {
            // Skip mail configuration during tenant cleanup to prevent cache access issues
            if (app()->runningInConsole()) {
                $argv = $_SERVER['argv'] ?? [];
                if (in_array('tenants:cleanup-deleted', $argv)) {
                    return true; // Skip gracefully during cleanup
                }
            }

            // Try to get settings from AdminCache first
            $config = $this->getMailConfigFromCache();
            if ($config === null) {
                // Batch retrieve all settings at once to avoid N+1 queries
                $settings = [
                    'smtp_host' => get_setting('email.smtp_host'),
                    'smtp_username' => get_setting('email.smtp_username'),
                    'smtp_password' => get_setting('email.smtp_password'),
                    'mailer' => get_setting('email.mailer', 'smtp'),
                    'smtp_port' => get_setting('email.smtp_port', 587),
                    'smtp_encryption' => get_setting('email.smtp_encryption', 'tls'),
                    'sender_email' => get_setting('email.sender_email', ''),
                    'sender_name' => get_setting('email.sender_name', ''),
                ];

                // Validate required settings
                if (empty($settings['smtp_host']) || empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
                    throw new \RuntimeException('Required email settings are missing.');
                }

                // Create config array
                $config = [
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.driver' => $settings['mailer'],
                    'mail.mailers.smtp.host' => $settings['smtp_host'],
                    'mail.mailers.smtp.port' => (int) $settings['smtp_port'],
                    'mail.mailers.smtp.username' => $settings['smtp_username'],
                    'mail.mailers.smtp.password' => $settings['smtp_password'],
                    'mail.mailers.smtp.encryption' => $settings['smtp_encryption'],
                    'mail.from.address' => $settings['sender_email'],
                    'mail.from.name' => $settings['sender_name'],
                ];

                // Cache the configuration using AdminCache
                $this->cacheMailConfig($config);
            }

            // Apply the configuration
            config($config);

            do_action('email.config_updated', $config);

            return true;

        } catch (\RuntimeException $e) {
            return false;

        } catch (\Exception $e) {
            // Log unexpected errors as errors
            app_log('Unexpected mail configuration error: '.$e->getMessage(), 'error', $e);

            return false;
        }
    }

    /**
     * Get mail configuration from AdminCache.
     */
    private function getMailConfigFromCache(): ?array
    {
        return AdminCache::get(self::CACHE_KEY);
    }

    /**
     * Cache mail configuration using AdminCache.
     */
    private function cacheMailConfig(array $config): bool
    {
        return AdminCache::put(self::CACHE_KEY, $config, ['admin.settings', 'admin.mail']);
    }

    /**
     * Clear the mail configuration cache using AdminCache.
     */
    public function clearMailConfigCache(): bool
    {
        return AdminCache::invalidateTag('admin.mail');
    }
}
