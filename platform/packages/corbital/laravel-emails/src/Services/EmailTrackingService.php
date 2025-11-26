<?php

namespace Corbital\LaravelEmails\Services;

use Corbital\LaravelEmails\Models\EmailLog;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Support\Str;

class EmailTrackingService
{
    /**
     * @var EmailSettings
     */
    protected $settings;

    /**
     * Create a new email tracking service instance.
     */
    public function __construct(EmailSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Add tracking pixel to email content.
     */
    public function addTrackingPixel(string $content, EmailLog $log): string
    {
        if (! $this->settings->track_opens) {
            return $content;
        }

        $trackingPixel = $this->generateTrackingPixel($log->id);

        // Add the tracking pixel just before the closing </body> tag
        if (Str::contains($content, '</body>')) {
            return Str::replaceLast('</body>', $trackingPixel.'</body>', $content);
        }

        // If no body tag, append to the end
        return $content.$trackingPixel;
    }

    /**
     * Add tracking to links in email content.
     */
    public function addLinkTracking(string $content, EmailLog $log): string
    {
        if (! $this->settings->track_clicks) {
            return $content;
        }

        // Replace links with tracking URLs
        return preg_replace_callback(
            '/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']*)["\']([^>]*)>/i',
            function ($matches) use ($log) {
                $url = $matches[1];
                $rest = $matches[2];

                if (empty($url) || Str::startsWith($url, '#') || Str::startsWith($url, 'mailto:')) {
                    return $matches[0]; // Skip anchors, mailto links, etc.
                }

                $trackingUrl = $this->generateTrackingUrl($log->id, $url);

                return "<a href=\"{$trackingUrl}\"{$rest}>";
            },
            $content
        );
    }

    /**
     * Generate a tracking pixel HTML.
     */
    protected function generateTrackingPixel(int $logId): string
    {
        $url = route('laravel-emails.tracking.pixel', ['id' => $logId]);

        return '<img src="'.$url.'" alt="" width="1" height="1" style="display:none;">';
    }

    /**
     * Generate a tracking URL for link clicks.
     */
    protected function generateTrackingUrl(int $logId, string $originalUrl): string
    {
        return route('laravel-emails.tracking.link', [
            'id' => $logId,
            'url' => base64_encode($originalUrl),
        ]);
    }

    /**
     * Track email open.
     */
    public function trackOpen(int $logId): void
    {
        try {
            $log = EmailLog::find($logId);

            if ($log) {
                $log->update([
                    'is_opened' => true,
                    'opened_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            app_log('Failed to track email open: ', 'error', $e, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Track link click.
     *
     * @return string Original URL to redirect to
     */
    public function trackClick(int $logId, string $encodedUrl): string
    {
        try {
            $originalUrl = base64_decode($encodedUrl);
            $log = EmailLog::find($logId);

            if ($log) {
                $log->update([
                    'is_clicked' => true,
                    'clicked_at' => now(),
                ]);
            }

            return $originalUrl;
        } catch (\Exception $e) {
            app_log('Failed to track email click: ', 'error', $e, ['error' => $e->getMessage()]);

            return '/'; // Fallback to homepage if something goes wrong
        }
    }
}
