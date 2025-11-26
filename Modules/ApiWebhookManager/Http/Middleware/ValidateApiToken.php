<?php

namespace Modules\ApiWebhookManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next, ?string $ability = null)
    {
        $tenantId = $request->get('tenant_id');
        $settings = tenant_settings_by_group('api', $tenantId);

        if (! isset($settings['enabled'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'API settings not found for this tenant',
            ], 404);
        }

        if (! $settings['enabled']) {
            return response()->json([
                'status' => 'error',
                'message' => 'API access is disabled',
            ], 403);
        }

        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'API token is required',
            ], 401);
        }

        // Validate token
        if ($token !== $settings['token']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API token',
            ], 401);
        }

        // Check specific ability if provided
        if ($ability && ! in_array($ability, $settings['abilities'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token does not have the required ability: '.$ability,
            ], 403);
        }

        // Rate limiting
        $key = 'api_token_'.$token;
        $maxAttempts = $settings['rate_limit_max'] ?? 60;
        $decayMinutes = $settings['rate_limit_decay'] ?? 1;

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'message' => t('too_many_requests'),
                'retry_after' => $retryAfter,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Increment attempts
        RateLimiter::hit($key, $decayMinutes * 60);

        // Update last used timestamp
        $settings['last_used_at'] = now();
        save_tenant_setting('api', 'last_used_at', now());
        // $this->settings->save();

        return $next($request);
    }
}
