<?php

namespace Corbital\LaravelEmails\Http\Middleware;

use Closure;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmailRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if rate limiting is enabled
            $settings = app(EmailSettings::class);

            if (! $settings->enable_rate_limiting) {
                return $next($request);
            }

            // Get limits
            $maxPerMinute = $settings->max_emails_per_minute;
            $maxPerHour = $settings->max_emails_per_hour;
            $maxPerDay = $settings->max_emails_per_day;

            // Check limits if they are set
            if ($maxPerMinute && ! $this->checkRateLimit('minute', $maxPerMinute)) {
                return response()->json([
                    'error' => 'Rate limit exceeded. Too many emails sent per minute.',
                ], 429);
            }

            if ($maxPerHour && ! $this->checkRateLimit('hour', $maxPerHour)) {
                return response()->json([
                    'error' => 'Rate limit exceeded. Too many emails sent per hour.',
                ], 429);
            }

            if ($maxPerDay && ! $this->checkRateLimit('day', $maxPerDay)) {
                return response()->json([
                    'error' => 'Rate limit exceeded. Too many emails sent per day.',
                ], 429);
            }

            // Increment counters
            $this->incrementCounter('minute');
            $this->incrementCounter('hour');
            $this->incrementCounter('day');

            return $next($request);

        } catch (\Exception $e) {
            // Log error but don't block the request
            app_log('Error in email rate limiter: '.$e->getMessage(), 'error', $e);

            return $next($request);
        }
    }

    /**
     * Check if the current rate is within limits.
     */
    protected function checkRateLimit(string $period, int $limit): bool
    {
        $key = "email_rate_limit:{$period}";
        $count = Cache::get($key, 0);

        return $count < $limit;
    }

    /**
     * Increment the counter for a specific period.
     */
    protected function incrementCounter(string $period): void
    {
        $key = "email_rate_limit:{$period}";
        $ttl = match ($period) {
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            default => 60,
        };

        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, $ttl);
        }
    }
}
