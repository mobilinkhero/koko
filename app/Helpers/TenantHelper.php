<?php

use App\Models\Tenant;
use Corbital\Settings\Models\TenantSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

if (! function_exists('current_tenant')) {
    /**
     * Get the current tenant using Spatie's optimized resolution.
     */
    function current_tenant(): ?Tenant
    {
        // Use a static variable to avoid repeated logic in same request
        static $cachedTenant = null;

        if ($cachedTenant !== null) {
            return $cachedTenant;
        }

        // Primary: Use Spatie's tenant resolution
        $tenant = BaseTenant::current();

        if ($tenant && get_class($tenant) === BaseTenant::class) {
            $tenant = Cache::remember(
                "tenant_{$tenant->getKey()}",
                now()->addMinutes(60),
                fn () => Tenant::find($tenant->getKey())
            );
        }

        // Fallback: Try from authenticated user's tenant_id
        if (! $tenant && Auth::check() && Auth::user()->tenant_id) {
            $tenant = Cache::remember(
                'tenant_'.Auth::user()->tenant_id,
                now()->addMinutes(60),
                fn () => Tenant::find(Auth::user()->tenant_id)
            );
        }

        // Essential: Handle Livewire AJAX requests by parsing referer URL
        if (! $tenant) {
            $subdomain = request()->route('subdomain');

            // Parse Livewire requests (essential for AJAX functionality)
            if (! $subdomain && in_array(request()->path(), ['livewire/update', 'livewire/message']) && request()->header('referer')) {
                $referer = request()->header('referer');
                $parts = parse_url($referer);
                $useTenantPrefix = config('multitenancy.use_tenant_prefix', false);
                $tenantPrefix = config('multitenancy.tenant_prefix_name', '');

                if (isset($parts['path'])) {
                    $pathParts = explode('/', trim($parts['path'], '/'));

                    if (! empty($pathParts[0])) {
                        $subdomain = $pathParts[0];
                    }

                    // Handle tenant prefix configuration
                    if ($useTenantPrefix && $pathParts[0] === $tenantPrefix && ! empty($pathParts[1])) {
                        $subdomain = $pathParts[1];
                    }
                }
            }

            // Resolve tenant by subdomain if found
            if ($subdomain) {
                $tenant = Cache::remember(
                    "tenant_subdomain_{$subdomain}",
                    now()->addMinutes(60),
                    fn () => Tenant::where('subdomain', $subdomain)->first()
                );

                // Store tenant ID in session for persistence
                if ($tenant) {
                    session(['current_tenant_id' => $tenant->getKey()]);
                }
            }
        }

        $cachedTenant = $tenant instanceof Tenant ? $tenant : null;

        return $cachedTenant;
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Get the ID of the current tenant.
     */
    function tenant_id(): ?int
    {
        return current_tenant()?->getKey();
    }
}

if (! function_exists('tenant_check')) {
    /**
     * Check if a tenant is currently active.
     */
    function tenant_check(): bool
    {
        return current_tenant() !== null;
    }
}

if (! function_exists('tenant_setting')) {
    /**
     * Get a tenant setting value.
     *
     * @param  string  $key  The setting key in format "group.setting_name"
     * @param  mixed  $default  The default value if setting not found
     * @return mixed
     */
    function tenant_setting(string $key, $default = null)
    {
        if (! tenant_check()) {
            return $default;
        }

        $parts = explode('.', $key);

        if (count($parts) !== 2) {
            return $default;
        }

        [$group, $setting] = $parts;

        // First check in-memory config (faster)
        $configValue = config("tenant.{$group}.{$setting}", null);
        if ($configValue !== null) {
            return $configValue;
        }

        // Not in config, try to load from settings
        return get_tenant_setting_from_db($group, $setting, $default);
    }
}

if (! function_exists('get_tenant_setting_from_db')) {
    /**
     * Internal function to get a tenant setting from the database.
     *
     * @param  string  $group  Setting group
     * @param  string  $key  Setting key
     * @param  mixed  $default  Default value
     * @return mixed
     */
    function get_tenant_setting_from_db(string $group, string $key, $default = null)
    {
        static $settingsCache = [];

        $tenant = current_tenant();
        if (! $tenant) {
            return $default;
        }

        $cacheKey = "{$tenant->id}.{$group}.{$key}";

        // Check in-memory cache first (per request)
        if (array_key_exists($cacheKey, $settingsCache)) {
            return $settingsCache[$cacheKey];
        }

        // Use simple caching
        $value = Cache::remember("tenant_{$tenant->id}_setting_{$group}_{$key}", now()->addMinutes(60), function () use ($tenant, $group, $key, $default) {
            $setting = TenantSetting::where('tenant_id', $tenant->id)
                ->where('group', $group)
                ->where('key', $key)
                ->value('value');

            return $setting ?? $default;
        });

        // Store in in-memory cache and config
        $settingsCache[$cacheKey] = $value;
        config(["tenant.{$group}.{$key}" => $value]);

        return $value;
    }
}

if (! function_exists('save_tenant_setting')) {
    /**
     * Save a setting value for the current tenant.
     *
     * @param  string  $group  Setting group
     * @param  string  $key  Setting key
     * @param  mixed  $value  Setting value
     * @return \Corbital\Settings\Models\TenantSetting|bool
     */
    function save_tenant_setting(string $group, string $key, $value, $tenant_id = null)
    {

        $tenant_id = $tenant_id ?? tenant_id();

        if (! $tenant_id) {
            return false;
        }

        $setting = TenantSetting::updateOrCreate(
            [
                'tenant_id' => $tenant_id,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );

        // Update in-memory config
        config(["tenant.{$group}.{$key}" => $value]);

        // Clear specific cache keys
        Cache::forget("tenant_{$tenant_id}_setting_{$group}_{$key}");
        Cache::forget("tenant_{$tenant_id}_settings_group_{$group}");

        return $setting;
    }
}

if (! function_exists('save_batch_tenant_setting')) {
    /**
     * Save multiple settings for a tenant under a single group.
     */
    function save_batch_tenant_setting(string $group, array $settings): bool
    {
        $tenant = current_tenant();

        if (! $tenant || empty($settings)) {
            return false;
        }

        DB::transaction(function () use ($tenant, $group, $settings) {
            $now = now();

            $data = collect($settings)->map(function ($value, $key) use ($tenant, $group, $now) {
                return [
                    'tenant_id' => $tenant->id,
                    'group' => $group,
                    'key' => $key,
                    'value' => $value,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            })->values()->toArray();

            TenantSetting::where('tenant_id', $tenant->id)
                ->where('group', $group)
                ->whereIn('key', array_keys($settings))
                ->delete();

            TenantSetting::insert($data);

            foreach ($settings as $key => $value) {
                config(["tenant.{$group}.{$key}" => $value]);
            }

            // Clear group cache
            Cache::forget("tenant_{$tenant->id}_settings_group_{$group}");
        });

        return true;
    }
}

if (! function_exists('tenant_settings_by_group')) {
    /**
     * Get all settings for a specific group.
     *
     * @param  string  $group  The settings group
     */
    function tenant_settings_by_group(string $group, $tenant_id = null): array
    {
        $tenant_id = $tenant_id ?? tenant_id();

        if (! $tenant_id) {
            return [];
        }

        return Cache::remember("tenant_{$tenant_id}_settings_group_{$group}", now()->addMinutes(60), function () use ($tenant_id, $group) {
            return TenantSetting::where('tenant_id', $tenant_id)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}

if (! function_exists('tenant_url')) {
    /**
     * Generate a URL with the tenant prefix.
     *
     * @param  string  $path  The URL path
     * @param  bool  $absolute  Whether to generate an absolute URL
     */
    function tenant_url(string $path = '', bool $absolute = true): string
    {
        $tenant = current_tenant();

        if (! $tenant) {
            throw new \Exception('Cannot generate tenant URL without active tenant');
        }

        $path = trim($path, '/');
        $tenantPath = $tenant->subdomain.($path ? '/'.$path : '');

        return url($tenantPath, [], $absolute);
    }
}

if (! function_exists('tenant_route')) {
    /**
     * Generate a route URL with the tenant prefix.
     *
     * @param  string  $name  The route name
     * @param  array  $parameters  The route parameters
     * @param  bool  $absolute  Whether to generate an absolute URL
     */
    function tenant_route(string $name, $parameters = [], bool $absolute = true): string
    {
        $tenant = current_tenant();

        // Get subdomain from current tenant or route parameter as fallback
        $subdomain = $tenant?->subdomain ?? request()->route('subdomain');

        if (! $subdomain) {
            // Fall back to non-tenant route as last resort
            return route($name, $parameters, $absolute);
        }

        // Generate route with subdomain parameter
        return route($name, array_merge(['subdomain' => $subdomain], $parameters), $absolute);
    }
}

if (! function_exists('tenant_subdomain')) {
    /**
     * Get the subdomain of the current tenant.
     */
    function tenant_subdomain(): ?string
    {
        return current_tenant()?->subdomain;
    }
}
if (! function_exists('tenant_domain')) {
    /**
     * Get the domain of the current tenant.
     */
    function tenant_domain(): ?string
    {
        return current_tenant()?->domain;
    }
}
if (! function_exists('tenant_name')) {
    /**
     * Get the name of the current tenant.
     */
    function tenant_name(): ?string
    {
        return current_tenant()?->company_name;
    }
}

if (! function_exists('get_tenant_setting_by_tenant_id')) {
    /**
     * Get a tenant setting by tenant ID (optimized version).
     * Wrapper around get_tenant_setting_from_db for backward compatibility.
     */
    function get_tenant_setting_by_tenant_id(string $group, string $key, $default = null, $tenant_id = null)
    {
        $tenant_id = $tenant_id ?? tenant_id();

        if (! $tenant_id) {
            return $default;
        }

        return Cache::remember("tenant_{$tenant_id}_setting_{$group}_{$key}", now()->addMinutes(60), function () use ($tenant_id, $group, $key, $default) {
            $setting = TenantSetting::where('tenant_id', $tenant_id)
                ->where('group', $group)
                ->where('key', $key)
                ->value('value');

            return $setting ?? $default;
        });
    }
}

if (! function_exists('tenant_subdomain_by_tenant_id')) {
    /**
     * Get the subdomain by tenant ID with caching.
     */
    function tenant_subdomain_by_tenant_id($tenant_id): ?string
    {
        return Cache::remember(
            "tenant_subdomain_by_id_{$tenant_id}",
            now()->addMinutes(60),
            fn () => Tenant::where('id', $tenant_id)->value('subdomain')
        );
    }
}

if (! function_exists('check_is_superadmin')) {
    /**
     * Check if the current user is a superadmin.
     */
    function check_is_superadmin(): bool
    {
        $install = new \Corbital\Installer\Installer;

        return $install->isAppInstalled();
    }
}

if (! function_exists('whatsapp_log')) {
    /**
     * Write WhatsApp-specific logs with tenant isolation
     *
     * @param  string  $message  Main log message
     * @param  string  $level  Log level (error, warning, info, debug, critical, alert, emergency)
     * @param  array  $context  Additional context data
     * @param  \Throwable|null  $exception  Optional exception object
     */
    function whatsapp_log(string $message, string $level = 'info', array $context = [], ?\Throwable $exception = null, $tenantId = null): void
    {
        // Skip logging if WhatsApp logging is disabled in settings

        $settings = get_tenant_setting_by_tenant_id('whatsapp', 'logging', '', tenant_id());

        $logging = is_string($settings)
            ? json_decode($settings, true)
            : (array) $settings;

        // Skip logging if disabled
        if (! ($logging['enabled'] ?? false)) {
            return;
        }

        // Get tenant info for isolation
        $tenantId = $tenantId ?? tenant_id();

        // Build the log context with consistent metadata
        $logContext = array_merge([
            'timestamp' => now()->setTimezone(config('app.timezone'))->toDateTimeString(),
            'tenant_id' => $tenantId,
            'user_id' => Auth::id() ?? 'guest',
            'url' => request()->fullUrl() ?? 'CLI',
            'method' => request()->method() ?? 'CLI',
            'ip' => request()->ip() ?? '127.0.0.1',
        ], $context);

        // Add exception details if provided
        if ($exception) {
            $logContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile().':'.$exception->getLine(),
                'trace' => array_slice(
                    array_filter(
                        array_map(
                            'trim',
                            explode("\n", $exception->getTraceAsString())
                        )
                    ),
                    0,
                    10
                ),
            ];
        }

        try {
            // Determine log file path based on tenant
            $logDir = $tenantId
                ? storage_path("logs/tenant/{$tenantId}")
                : storage_path('logs/whatsapp');

            // Create directory if it doesn't exist
            if (! File::exists($logDir)) {
                File::makeDirectory($logDir, 0755, true);
            }

            // Create filename with date (daily rotation pattern)
            $filename = 'whatsapp-'.now()->format('Y-m-d').'.log';
            $logPath = $logDir.'/'.$filename;

            // Create a simple file logger
            $logger = Log::build([
                'driver' => 'single',
                'path' => $logPath,
                'level' => $level,
            ]);

            // Write to the log
            $logger->{$level}($message, $logContext);
        } catch (\Exception $e) {
            // Fallback logging if tenant-specific logging fails
            Log::error("WhatsApp logging error: {$e->getMessage()}", [
                'original_message' => $message,
                'tenant_id' => $tenantId,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
