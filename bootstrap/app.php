<?php

use App\Http\Middleware\SetLocale;
use App\Listeners\TenantCacheManager;
use App\Multitenancy\PathTenantFinder;
use App\Services\PlanFeatureCache;
use Corbital\Installer\Http\Middleware\CheckDatabaseVersion;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

return Application::configure(basePath: dirname(__DIR__))
    /**
     * Register global event listeners
     */
    ->withEvents([
        // Register the tenant event subscriber for cache management
        TenantCacheManager::class,
    ])
    /**
     * Configure application routing
     */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin routes with shared middleware
            Route::middleware(['web', 'check-database-version', 'validate'])
                ->prefix('admin')
                ->as('admin.')
                ->group(function () {
                    // Group admin routes from different files
                    require __DIR__.'/../routes/admin/admin.php';
                    require __DIR__.'/../routes/admin/payment-settings.php';
                    require __DIR__.'/../routes/admin/website-settings.php';
                    require __DIR__.'/../routes/admin/system-settings.php';
                });

            // API routes
            Route::middleware(['api'])
                ->prefix('api')
                ->as('api.')
                ->group(function () {
                    Route::middleware(['api.token'])->group(base_path('routes/admin/api.php'));
                });

            // Tenant routes configuration with conditional prefixing
            $tenantRoutes = Route::middleware(['web', 'tenant']);

            // Apply tenant prefix if configured
            $useTenantPrefix = config('multitenancy.use_tenant_prefix', false);
            $tenantPrefixName = config('multitenancy.tenant_prefix_name', '');

            if ($useTenantPrefix && ! empty($tenantPrefixName)) {
                $tenantRoutes->prefix($tenantPrefixName);
            }

            // Register tenant route files
            $tenantRoutes->group(function () {
                require __DIR__.'/../routes/tenant/tenant.php';
                require __DIR__.'/../routes/tenant/system-settings.php';
                require __DIR__.'/../routes/tenant/whatsmark-settings.php';
            });
        },
    )
    /**
     * Configure application middleware
     */
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware that applies to all requests
        $middleware->append(SetLocale::class);

        // Register middleware aliases for easy reference
        $middleware->alias([
            'verify.webhook' => \App\Http\Middleware\VerifyWebhookSignature::class,
            'check-database-version' => CheckDatabaseVersion::class,
            'validate' => \Corbital\Installer\Http\Middleware\ValidateBackendRequest::class,
            'senitize.inputs' => \App\Http\Middleware\SanitizeInputs::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        // Tenant middleware group - defines the stack for tenant requests
        $middleware->group('tenant', [
            \App\Http\Middleware\DetermineTenantFromPath::class,
            \App\Http\Middleware\EnsureTenantSecurity::class,
            NeedsTenant::class,
            EnsureValidTenantSession::class,
            \App\Http\Middleware\EnsureTenantForLivewire::class,
            \App\Http\Middleware\CheckStatus::class,
        ]);

        // Add auth.session middleware to the existing web middleware group
        $middleware->web(append: [
            'auth.session',
        ]);

        // Installer middleware - must run first to redirect if installation is needed
        $middleware->prepend(\Corbital\Installer\Http\Middleware\RedirectIfNotInstalled::class);
        $middleware->prepend(\Corbital\ModuleManager\Http\Middleware\ValidateModuleBackendRequest::class);
    })
    /**
     * Register additional service providers
     */
    ->withProviders([
        App\Providers\CsrfMiddlewareServiceProvider::class,
        App\Providers\TenantServiceProvider::class,
        App\Providers\FeatureServiceProvider::class,
    ])
    /**
     * Register custom bindings in the service container
     */
    ->withBindings([
        // Custom tenant finder implementation
        'tenant.finder' => PathTenantFinder::class,

        // PlanFeatureCache singleton for consistent feature availability checks
        PlanFeatureCache::class => function () {
            return new PlanFeatureCache;
        },
    ])
    /**
     * Configure application exception handling
     */
    ->withExceptions(function (Exceptions $exceptions) {
        // Register custom exception renderers for tenant-specific errors
        $exceptions->reportable(function (\Throwable $e) {
            // Additional error reporting logic can be added here
        });

        // Custom exception rendering based on environment
        $exceptions->renderable(function (\Throwable $e, $request) {
            // Tenant-specific error responses can be configured here
        });
    })
    ->create();
