<?php

use App\Enum\SubscriptionStatus;
use App\Facades\TenantCache;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CurrencyCache;
use App\Services\SubscriptionCache;
use App\Services\TaxCache;
use Carbon\Carbon;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\PermissionRegistrar;

if (! function_exists('format_date_time')) {
    function format_date_time($dateTime)
    {
        if (tenant_check()) {

            $tenantSeetings = tenant_settings_by_group('system');

            $timezone = $tenantSeetings['timezone'] ?? config('app.timezone');
            $dateFormat = $tenantSeetings['date_format'] ?? config('app.date_format');
            $timeFormat = $tenantSeetings['time_format'] ?? '12' == '12' ? 'h:i A' : 'H:i';
        } else {

            $systemSettings = get_batch_settings([
                'system.timezone',
                'system.date_format',
                'system.time_format',
            ]);

            $timezone = $systemSettings['system.timezone'] ?? config('app.timezone');
            $dateFormat = $systemSettings['system.date_format'] ?? config('app.date_format');
            $timeFormat = $systemSettings['system.time_format'] == '12' ? 'h:i A' : 'H:i';
        }

        return Carbon::parse($dateTime)
            ->setTimezone($timezone)
            ->format("$dateFormat $timeFormat");
    }
}

if (! function_exists('get_super_admin_current_time')) {
    function get_super_admin_current_time()
    {
        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        // Get current time in that timezone
        return Carbon::now($timezone);
    }
}

if (! function_exists('get_tenant_current_time')) {
    function get_tenant_current_time()
    {
        $timezone = get_tenant_setting_from_db('system', 'timezone', config('app.timezone'));

        // Get current time in that timezone
        return Carbon::now($timezone);
    }
}

if (! function_exists('get_new_tenants_count')) {
    function get_new_tenants_count(): int
    {
        $now = Carbon::now();
        $lastViewed = Session::get('last_viewed_tenants');

        if (Request::routeIs('admin.tenants.list')) {
            Session::put('last_viewed_tenants', $now->toDateTimeString());

            return 0;
        }

        $queryTime = $lastViewed ? Carbon::parse($lastViewed) : $now->copy()->subHours(24);

        return Tenant::where('created_at', '>=', $queryTime)->count();
    }
}
if (! function_exists('get_new_transactions_count')) {
    function get_new_transactions_count(): int
    {
        // If currently on the transactions index page, reset count to 0
        if (Request::routeIs('admin.transactions.index')) {
            return 0;
        }

        // Simply count all transactions with 'new' status
        return Transaction::where('status', 'pending')->count();
    }
}

if (! function_exists('get_pending_subscriptions_count')) {
    function get_pending_subscriptions_count(): int
    {
        $now = Carbon::now();
        $lastViewed = Session::get('last_viewed_subscriptions');

        if (Request::routeIs('admin.subscription.list')) {
            Session::put('last_viewed_subscriptions', $now->toDateTimeString());

            return 0;
        }

        return Subscription::where('status', SubscriptionStatus::PENDING)->count();
    }
}

if (! function_exists('get_base_currency')) {
    function get_base_currency()
    {
        return CurrencyCache::getBaseCurrency();
    }
}

if (! function_exists('get_default_tax')) {
    /**
     * Get the default taxes from settings
     *
     * Returns a collection of tax objects that are set as default in invoice settings
     *
     * @param  bool  $single  If true, returns only the first default tax, otherwise returns all default taxes
     * @return \Illuminate\Support\Collection|null
     */
    function get_default_tax($single = false)
    {
        $settings = get_settings_by_group('invoice');
        $defaultTaxesJson = $settings->default_taxes ?? '[]';

        try {
            $default_tax_ids = json_decode($defaultTaxesJson, true);

            // Ensure it's an array
            if (! is_array($default_tax_ids)) {
                $default_tax_ids = [];
            }

            if (empty($default_tax_ids)) {
                // Fallback to first tax if no defaults are set
                return $single ? TaxCache::getAllTaxes()->first() : collect([]);
            }

            $taxes = TaxCache::getAllTaxes()->whereIn('id', $default_tax_ids);

            return $single ? $taxes->first() : $taxes;
        } catch (\Exception $e) {
            app_log('Error parsing default taxes: '.$e->getMessage(), 'error', $e);

            // Fallback to first tax if there's an error
            return $single ? TaxCache::getAllTaxes()->first() : collect([]);
        }
    }
}

if (! function_exists('get_all_taxes')) {
    /**
     * Get all taxes from cache
     *
     * @return \Illuminate\Support\Collection
     */
    function get_all_taxes()
    {
        return TaxCache::getAllTaxes();
    }
}

if (! function_exists('get_default_taxes')) {
    /**
     * Get all default taxes from settings
     *
     * Returns a collection of tax objects that are set as default in invoice settings
     *
     * @return \Illuminate\Support\Collection
     */
    function get_default_taxes()
    {
        $settings = get_settings_by_group('invoice');
        $defaultTaxesJson = $settings->default_taxes ?? '[]';

        try {
            $default_tax_ids = json_decode($defaultTaxesJson, true);

            // Ensure it's an array
            if (! is_array($default_tax_ids)) {
                $default_tax_ids = [];
            }

            if (empty($default_tax_ids)) {
                // Return empty collection if no defaults are set
                return collect([]);
            }

            return TaxCache::getAllTaxes()->whereIn('id', $default_tax_ids);
        } catch (\Exception $e) {
            app_log('Error parsing default taxes: '.$e->getMessage(), 'error', $e);

            // Return empty collection if there's an error
            return collect([]);
        }
    }
}

if (! function_exists('optimize_clear')) {
    /**
     * Clear all cached files including config, route, and view caches.
     *
     * @return void
     */
    function optimize_clear()
    {
        Artisan::call('optimize:clear');
    }
}

if (! function_exists('clear_cache')) {
    /**
     * Clear the application cache.
     * If tenant_id is provided, clears only tenant-specific cache if possible.
     *
     * @param  int|null  $tenant_id  The tenant ID if clearing for a specific tenant
     * @return void
     */
    function clear_cache(?int $tenant_id = null)
    {
        Artisan::call('cache:clear');

        // Additional tenant-specific cache clearing can be added here
        // Currently, Laravel's cache:clear affects the entire application
    }
}

if (! function_exists('clear_config')) {
    /**
     * Clear the cached configuration files.
     * If tenant_id is provided, clears only tenant-specific config cache if possible.
     *
     * @param  int|null  $tenant_id  The tenant ID if clearing for a specific tenant
     * @return void
     */
    function clear_config(?int $tenant_id = null)
    {
        Artisan::call('config:clear');

        // Additional tenant-specific config clearing can be added here
        // Currently, Laravel's config:clear affects the entire application
    }
}

if (! function_exists('clear_route')) {
    /**
     * Clear the cached route files.
     * If tenant_id is provided, clears only tenant-specific route cache if possible.
     *
     * @param  int|null  $tenant_id  The tenant ID if clearing for a specific tenant
     * @return void
     */
    function clear_route(?int $tenant_id = null)
    {
        Artisan::call('route:clear');

        // Additional tenant-specific route clearing can be added here
        // Currently, Laravel's route:clear affects the entire application
    }
}

if (! function_exists('clear_view')) {
    /**
     * Clear all compiled view files.
     * If tenant_id is provided, clears only tenant-specific view cache if possible.
     *
     * @param  int|null  $tenant_id  The tenant ID if clearing for a specific tenant
     * @return void
     */
    function clear_view(?int $tenant_id = null)
    {
        Artisan::call('view:clear');

        // Additional tenant-specific view clearing can be added here
        // Currently, Laravel's view:clear affects the entire application
    }
}

if (! function_exists('rebuild_cache')) {
    /**
     * Rebuild and cache configuration, route, and view files.
     *
     * @return void
     */
    function rebuild_cache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
    }
}

if (! function_exists('optimize')) {
    /**
     * Cache the framework bootstrap files for optimized performance.
     *
     * @return void
     */
    function optimize()
    {
        Artisan::call('optimize');
    }
}

if (! function_exists('create_storage_link')) {
    /**
     * Create a symbolic link from "public/storage" to "storage/app/public".
     *
     * @return void
     */
    function create_storage_link()
    {
        if (! is_link(public_path('storage'))) {
            Artisan::call('storage:link');
        }
    }
}

if (! function_exists('get_whatsmark_allowed_extension')) {
    /**
     * Get the allowed file extensions for WhatsMark uploads.
     *
     * @return array<string, array{extension: string}> An associative array containing:
     *                                                 - 'file_types': Allowed file extensions for WhatsMark uploads.
     */
    function get_whatsmark_allowed_extension()
    {
        return [
            'file_types' => [
                'extension' => '.png,.jpg,.jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt,.webp,.aac, .amr, .mp3, .m4a, .ogg,.mp4, .3gp',
            ],
        ];
    }
}

if (! function_exists('getLanguageFilePath')) {
    function getLanguageFilePath($languageCode)
    {
        $isTenant = current_tenant() !== null;
        $tenantId = $isTenant ? tenant_id() : null;

        // Default file logic
        if ($languageCode === 'en') {
            $filePath = $isTenant
                ? resource_path('lang/tenant_en.json')
                : resource_path('lang/en.json');
        } else {
            if ($isTenant) {
                $filePath = resource_path("lang/translations/tenant/{$tenantId}/tenant_{$languageCode}.json");
            } else {
                $filePath = resource_path("lang/translations/{$languageCode}.json");
                $code = $languageCode;
            }
        }

        return [
            'filePath' => $filePath,
        ];
    }
}

// Temporary function for tenant transalation for handle tenant_en.json file
if (! function_exists('t')) {
    function t($key, $replace = [], $locale = null)
    {
        // Use LanguageService to get the current language consistently
        if (! $locale) {
            $locale = app('App\Services\LanguageService')->resolveLanguage();
        }
        $data = getLanguageFilePath($locale);
        // Check if file exists, fallback to default English file
        if (! file_exists($data['filePath'])) {
            $data['filePath'] = (tenant_check()) ? resource_path('lang/tenant_en.json') : resource_path('lang/en.json');
        }

        if (tenant_check()) {
            $tenant = current_tenant();
            $locale = $tenant->id.'_tenant_'.$locale;
        }

        $translations = Cache::remember("translations.{$locale}", 3600, function () use ($data) {
            if (file_exists($data['filePath'])) {
                return json_decode(file_get_contents($data['filePath']), true) ?? [];
            }

            return [];
        });

        $translation = $translations[$key] ?? $key;

        // If no translation found and replace is a string, use it as fallback
        if ($translation === $key && is_string($replace)) {
            return $replace;
        }

        // Ensure $replace is an array for foreach loop
        if (! is_array($replace)) {
            $replace = [];
        }

        // Handle replacements
        foreach ($replace as $k => $v) {
            $translation = str_replace(":{$k}", $v, $translation);
        }

        return $translation;
    }
}

if (! function_exists('app_log')) {
    /**
     * Write application logs with consistent formatting
     *
     * @param  string  $message  Main log message
     * @param  string  $level  Log level (error, info, debug, warning)
     * @param  \Throwable|null  $exception  Optional exception object
     * @param  array  $context  Additional context data
     */
    function app_log(string $message, string $level = 'error', ?\Throwable $exception = null, array $context = [], $tenantId = null): void
    {
        // Skip debug logs if app.debug is false
        if ($level === 'debug' && ! config('app.debug')) {
            return;
        }

        // Get tenant info for isolation
        $tenantId = $tenantId ?? tenant_id();

        // Determine timezone
        $timezone = $tenantId
            ? get_tenant_setting_from_db('system', 'timezone', config('app.timezone'))
            : get_setting('system.timezone', config('app.timezone'));

        // Ensure request() is available and not running in the console
        $request = request();

        // Build log context
        $logContext = array_merge([
            'timestamp' => now()->setTimezone($timezone)->toDateTimeString(),
            'env' => config('app.env'),
            'request' => [
                'id' => $request && $request->header('X-Request-ID') ? $request->header('X-Request-ID') : Uuid::uuid4()->toString(),
                'url' => $request ? $request->fullUrl() : 'CLI',
                'method' => $request ? $request->method() : 'CLI',
                'ip' => $request ? $request->ip() : '127.0.0.1',
            ],
            'user_id' => Auth::check() ? Auth::id() : 'guest',
        ], $context);
        // Add exception details if provided
        if ($exception) {
            $logContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile().':'.$exception->getLine(),
                'trace' => array_slice(
                    array_filter(
                        array_map(
                            'trim',
                            explode("\n", $exception->getTraceAsString())
                        )
                    ),
                    0,
                    5
                ),
            ];
        }
        try {
            // Determine log file path based on tenant
            $logDir = $tenantId
                ? storage_path("logs/tenant/{$tenantId}")
                : storage_path('logs');

            // Create directory if it doesn't exist
            if (! File::exists($logDir)) {
                File::makeDirectory($logDir, 0755, true);
            }

            // Create filename with date (daily rotation pattern)
            $filename = 'laravel-'.now()->format('Y-m-d').'.log';
            $logPath = $logDir.'/'.$filename;

            // Create a simple file logger
            $logger = Log::build([
                'driver' => 'single',
                'path' => $logPath,
            ]);

            // Write to the log
            $logger->{$level}($message, $logContext);
        } catch (\Exception $e) {
            // Fallback logging if tenant-specific logging fails
            Log::error("Laravel logging error: {$e->getMessage()}", [
                'original_message' => $message,
                'tenant_id' => $tenantId,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}

if (! function_exists('getLanguageJson')) {
    function getLanguageJson(string $languageCode): array
    {
        try {
            $data = getLanguageFilePath($languageCode);
            if (! file_exists($data['filePath'])) {
                throw new Exception("Language file not found for code: {$languageCode}");
            }

            $jsonData = file_get_contents($data['filePath']);
            $decodedData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to decode JSON from: {$data['filePath']}");
            }

            return $decodedData;
        } catch (Exception $e) {
            // Error loading language file
            return [];
        }
    }
}

if (! function_exists('getLangugeValue')) {
    function getLangugeValue(string $languageCode, string $key, $default = null)
    {
        try {
            $data = getLanguageFilePath($languageCode);

            if (! file_exists($data['filePath'])) {
                throw new Exception("Language file not found for code: {$languageCode}");
            }

            $jsonData = file_get_contents($data['filePath']);
            $decodedData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to decode JSON from: {$data['filePath']}");
            }

            return $decodedData[$key] ?? $default;
        } catch (Exception $e) {
            app_log('Error fetching language key value:', 'error', $e);

            return $default;
        }
    }
}

if (! function_exists('getLanguage')) {
    /**
     * Retrieve language(s) from the DB (context-aware).
     *
     * @param  mixed  $filter  (null, id, name, code, or associative array for custom where)
     * @param  array  $columns  (columns to select, default ['*'])
     * @return mixed
     */
    function getLanguage($filter = null, $columns = ['*'])
    {
        $query = \App\Models\Language::query();

        // Apply context: if tenant exists, filter by tenant_id, else only admin/global languages
        if (current_tenant()) {
            $query->where('tenant_id', tenant_id());
        } else {
            $query->whereNull('tenant_id');
        }

        if (is_array($filter)) {
            // If filter is an associative array, apply where conditions.
            $query->where($filter);
        } elseif (! is_null($filter)) {
            if (is_numeric($filter)) {
                $query->where('id', $filter);
            } else {
                // Try matching by code first.
                $record = $query->where('code', $filter)->select($columns)->first();
                if ($record) {
                    return $record;
                }

                // If not found, try matching by name.
                $query = \App\Models\Language::query();

                // Reapply context
                if (current_tenant()) {
                    $query->where('tenant_id', tenant_id());
                } else {
                    $query->whereNull('tenant_id');
                }

                $query->where('name', $filter);

                return $query->select($columns)->first();
            }
        }

        return $query->select($columns)->get();
    }
}

if (! function_exists('getArrayItem')) {
    /**
     * Retrieve an item from an array using a given key.
     *
     * This function checks if the provided key exists in the array and returns its value.
     * If the key is not found or the value is 'null', it returns the default value.
     *
     * @param  string|int  $key  The key to search for in the array.
     * @param  array  $array  The array to search within.
     * @param  mixed  $default  The default value to return if the key does not exist or the value is 'null'.
     * @return mixed The value corresponding to the key or the default value if not found.
     */
    function getArrayItem($key, $array, $default = null)
    {
        return (! empty($array) && array_key_exists($key, $array) && $array[$key] !== 'null')
            ? $array[$key]
            : $default;
    }
}

if (! function_exists('is_smtp_valid')) {
    /**
     * Check if SMTP configuration is valid.
     *
     * This function verifies that all required SMTP settings are properly configured.
     * It checks for the presence of essential SMTP configuration values.
     *
     * @return bool Returns true if all required SMTP configurations are set, otherwise false.
     */
    function is_smtp_valid()
    {
        $requiredConfigs = [
            'mail.default',
            'mail.mailers.smtp.host',
            'mail.mailers.smtp.port',
            'mail.mailers.smtp.encryption',
            'mail.mailers.smtp.username',
            'mail.mailers.smtp.password',
        ];

        foreach ($requiredConfigs as $config) {
            if (empty(config($config))) {
                return false;
            }
        }

        return true;
    }
}

if (! function_exists('get_country_list')) {
    function get_country_list()
    {
        return Cache::remember('countries.all', now()->addHours(24), function () {
            $path = base_path('platform/packages/corbital/installer/countries.json');

            if (! File::exists($path)) {
                return [];
            }

            return json_decode(File::get($path), true);
        });
    }
}

if (! function_exists('get_country_name')) {
    function get_country_name($id)
    {
        $countries = get_country_list();

        foreach ($countries as $country) {
            if (isset($country['id']) && $country['id'] == $id) {
                return $country['short_name'] ?? '-';
            }
        }

        return '-';
    }
}

if (! function_exists('get_country_id_by_name')) {
    function get_country_id_by_name($name)
    {
        $countries = get_country_list();
        foreach ($countries as $country) {
            if (isset($country['short_name']) && $country['short_name'] == ucfirst($name)) {
                return $country['id'] ?? null;
            }
        }

        return null;
    }
}

if (! function_exists('payment_log')) {
    /**
     * Write payment logs with consistent formatting
     *
     * @param  string  $message  Main log message
     * @param  string  $level  Log level (error, warning, info, debug, critical, alert, emergency)
     * @param  array  $context  Additional context data
     * @param  \Throwable|null  $exception  Optional exception object
     */
    function payment_log(string $message, string $level = 'info', array $context = [], ?\Throwable $exception = null, $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant_id();

        // Add exception details if provided
        if ($exception) {
            $context['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile().':'.$exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        try {

            $logDir = storage_path('logs');

            // Create directory if it doesn't exist
            if (! File::exists($logDir)) {
                File::makeDirectory($logDir, 0755, true);
            }

            // Create filename with date (daily rotation pattern)
            $filename = 'payment-'.now()->format('Y-m-d').'.log';
            $logPath = $logDir.'/'.$filename;

            // Create a simple file logger
            $logger = Log::build([
                'driver' => 'single',
                'path' => $logPath,
                'level' => 'debug',
            ]);

            // Write to the log
            $logger->{$level}($message, $context);
        } catch (\Exception $e) {
            // Fallback logging if tenant-specific logging fails
            Log::error("Payment logging error: {$e->getMessage()}", [
                'original_message' => $message,
                'tenant_id' => $tenantId,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}

if (! function_exists('checkPermission')) {
    /**
     * Check if the authenticated user has the required permission(s).
     *
     * This function verifies whether the current user has the specified permission(s).
     * Admin users are granted all permissions by default.
     *
     * @param  string|array  $permissions  The permission or array of permissions to check.
     * @return bool True if the user has any of the specified permissions, false otherwise.
     */
    function checkPermission($permissions)
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->is_admin == 1) {
            return true;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        if (is_array($permissions)) {
            foreach ($permissions as $permi) {
                if ($user->can($permi)) {
                    return true;
                }
            }

            return false;
        }

        return $user->can($permissions);
    }
}

if (! function_exists('getUserByTenantId')) {
    function getUserByTenantId($tenant_id = '')
    {
        if (empty($tenant_id)) {
            $tenant_id = tenant_id();
        }

        return User::where('tenant_id', $tenant_id)->where('user_type', 'tenant')->first();
    }
}

if (! function_exists('render_email_template')) {
    /**
     * Render an email template with dynamic data.
     *
     * @param  string  $slug  The template slug
     * @param  array  $data  Custom data to be used in the template
     * @return string The rendered HTML
     */
    function render_email_template($slug, array $data = [], $table = null)
    {
        if ($table) {
            $template = EmailTemplate::fromTable($table)->where(['slug' => $slug, 'tenant_id' => $data['tenantId']])->first();
        } else {
            $template = EmailTemplate::where('slug', $slug)->first();
        }

        if (! $template) {
            throw new \Exception("Email template with slug '{$slug}' not found.");
        }

        // Render the template with all the data
        $html = $template->renderContent($data);

        return $html;
    }
}

if (! function_exists('formateInvoiceNumber')) {
    function formateInvoiceNumber($id = '', $invoice_number = 'DRAFT')
    {
        $settings = get_batch_settings(['invoice.prefix']);
        $prefix = ! empty($settings['invoice.prefix']) ? $settings['invoice.prefix'] : 'INV';
        $invoice_number = ! empty($invoice_number) ? $invoice_number : 'DRAFT-'.$id;

        return $prefix.'-'.$invoice_number;
    }
}

if (! function_exists('can_send_email')) {
    /**
     * Check if an email template is active based on the slug.
     *
     * @param  string  $slug  The email template slug.
     * @return bool True if the email can be sent, otherwise false.
     */
    function can_send_email(string $slug, $table = null): bool
    {
        if ($table) {
            return EmailTemplate::fromTable($table)->where('slug', $slug)
                ->where('is_active', true)
                ->exists();
        }

        return EmailTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->exists();
    }
    // chats methods
    if (! function_exists('checkRemoteFile')) {
        /**
         * Check if a remote file exists and is accessible via HTTP.
         *
         * This function verifies whether a given URL is valid and returns whether the remote file is accessible.
         *
         * @param  string  $url  The URL of the remote file to check.
         * @return bool True if the file is accessible, false otherwise.
         */
        function checkRemoteFile($url)
        {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::head($url);

                    return $response->successful();
                } catch (\Exception $e) {
                    return false;
                }
            }

            return false;
        }
    }

    if (! function_exists('truncate_text')) {
        function truncate_text($text, $limit = 50, $suffix = '......')
        {
            return strlen($text) > $limit ? substr($text, 0, $limit).$suffix : $text;
        }
    }
}

if (! function_exists('clear_tenant_cache')) {
    /**
     * Clear tenant-specific cache.
     *
     * @param  int  $tenant_id  The tenant ID to clear cache for
     * @return void
     */
    function clear_tenant_cache(int $tenant_id)
    {
        if (! $tenant_id) {
            return;
        }

        try {
            // Clear tenant-specific translation cache
            $cacheKeys = [
                "translations.{$tenant_id}_tenant_en",
                "translations.{$tenant_id}_tenant_.*",
                "tenant_{$tenant_id}_settings",
                "tenant.{$tenant_id}.features",
                "tenant.{$tenant_id}.subscription",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Clear tenant-specific log files older than 7 days
            $tenantLogPath = storage_path("logs/tenant/{$tenant_id}");
            if (File::exists($tenantLogPath)) {
                $oldFiles = File::glob("{$tenantLogPath}/*.log");
                $sevenDaysAgo = now()->subDays(7)->timestamp;

                foreach ($oldFiles as $file) {
                    // Skip recent files
                    if (File::lastModified($file) > $sevenDaysAgo) {
                        continue;
                    }

                    // Skip WhatsApp logs
                    $fileName = basename($file);
                    if (Str::startsWith($fileName, 'whats')) {
                        continue;
                    }

                    File::delete($file);
                }
            }

            $tenantCacheList = [
                "dashboard_usage_stats_tenant_{$tenant_id}",
                "dashboard_chart_data_tenant_{$tenant_id}",
                "dashboard_subscription_tenant_{$tenant_id}",
                "dashboard_feature_usage_tenant_{$tenant_id}",
                "dashboard_app_settings_tenant_{$tenant_id}",
                "dashboard_data_tenant_{$tenant_id}",
                "dashboard_weekly_messages_tenant_{$tenant_id}",
                "dashboard_contact_sources_tenant_{$tenant_id}",
                "dashboard_audience_growth_tenant_{$tenant_id}",
                "dashboard_campaign_statistics_tenant_{$tenant_id}",
            ];

            foreach ($tenantCacheList as $key) {
                TenantCache::forget($key);
            }

            SubscriptionCache::clearCache($tenant_id);

            app_log('Tenant-specific cache cleared', 'info', null, [], $tenant_id);
        } catch (\Exception $e) {
            app_log('Failed to clear tenant cache', 'error', $e, [], $tenant_id);
        }
    }
}

if (! function_exists('format_draft_invoice_number')) {
    function format_draft_invoice_number()
    {
        $settings = get_batch_settings(['invoice.prefix']);
        $prefix = ! empty($settings['invoice.prefix']) ? $settings['invoice.prefix'] : 'INV';

        return rtrim($prefix, '-').'-'.'DRAFT';
    }
}

if (! function_exists('tenant_on_active_plan')) {
    /**
     * Determine if the current tenant is on a free plan or has no subscription.
     *
     * @return bool
     */
    function tenant_on_active_plan()
    {
        try {
            $subscription = SubscriptionCache::getActiveSubscription(tenant_id());

            return is_null($subscription) ? true : (($subscription->status === Subscription::STATUS_TRIAL) ? true : false);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('table_pagination_settings')) {
    /**
     * Return the current table‑pagination limit and the full list of options.
     *
     * Detects whether we’re in tenant scope (via tenant_id() or explicit $tenantId)
     * and fetches the limit from the correct settings store.
     *
     * @param  int|null  $tenantId  Explicit tenant id; fallback to tenant_id() helper.
     * @param  int  $default  Fallback limit if nothing is set.
     * @param  array  $baseOpts  The standard choices you always want to show.
     * @return array{current:int, options:array<int>} ['current' => 10, 'options' => [0,10,50,100,500]]
     *
     * @throws \Throwable Let callers decide how to handle unexpected errors.
     */
    function table_pagination_settings(
        ?int $tenantId = null,
        int $default = 10,
        array $baseOpts = [10, 25, 50, 100],
    ): array {

        $tenantId ??= function_exists('tenant_id') ? tenant_id() : null;

        if ($tenantId) {
            // Tenant context
            $current = get_tenant_setting_by_tenant_id(
                'miscellaneous',
                'tables_pagination_limit',
                null,
                $tenantId
            ) ?? $default;
        } else {

            $batch = get_batch_settings(['system.tables_pagination_limit']);
            $current = $batch['system.tables_pagination_limit'] ?? $default;
        }

        $options = $baseOpts;
        if (! in_array($current, $options, true)) {
            $options[] = $current;
        }
        sort($options);
        $options = array_values($options);
        array_push($options, 0);

        return [
            'current' => (int) $current,
            'options' => $options,
        ];
    }
}

if (! function_exists('getTenantDefaultLanguage')) {
    /**
     * Get the default language for the current tenant.
     * Checks for available tenant language files in public/lang/ directory.
     *
     * @return array Array of available languages with default language marked
     */
    function getTenantDefaultLanguage()
    {
        try {
            $languages = [];

            // Always include English as the default language
            $languages[] = [
                'name' => 'English',
                'code' => 'en',
                'is_empty' => false,
            ];

            // Get all active tenant languages from the database
            $dbLanguages = \App\Models\TenantLanguage::query()
                ->orderBy('name')
                ->get(['name', 'code'])
                ->map(function ($lang) {
                    // Check if the language file exists and has content
                    $filePath = getLanguageFilePath($lang->code)['filePath'];
                    $is_empty = true;

                    if (file_exists($filePath)) {
                        $content = file_get_contents($filePath);
                        $data = json_decode($content, true);
                        $is_empty = empty($data);
                    }

                    return [
                        'name' => $lang->name,
                        'code' => $lang->code,
                        'is_empty' => $is_empty,
                    ];
                });

            return array_merge($languages, $dbLanguages->toArray());

        } catch (\Exception $e) {
            // Log error and return default language on failure
            app_log('Error getting tenant languages: '.$e->getMessage(), 'error', $e);

            return [[
                'name' => 'English',
                'code' => 'en',
                'is_empty' => false,
            ]];
        }
    }
}
