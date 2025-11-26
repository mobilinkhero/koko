<?php

namespace Corbital\Installer\Http\Middleware;

use Closure;
use Corbital\Installer\Installer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class CheckDatabaseVersion
{
    /**
     * The installer instance.
     */
    protected Installer $installer;

    /**
     * Create a new middleware instance.
     */
    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('database-upgrade')) {
            return $this->databaseNeedsUpgrade()
                ? $next($request)
                : redirect()->route('admin.dashboard');
        }

        if ($request->is('upgrade')) {
            return $next($request);
        }

        if ($this->databaseNeedsUpgrade()) {

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');

            return redirect()->route('database.upgrade');
        }

        return $next($request);

    }

    /**
     * Determine if the database needs an upgrade
     */
    protected function databaseNeedsUpgrade(): bool
    {
        if ($this->installer->isAppInstalled()) {
            try {
                $currentVersion = config('installer.license_verification.current_version'); // Your actual app version

                // Get database version
                // This is a placeholder - implement according to your version tracking system
                $settings = get_batch_settings(['whats-mark.wm_version']);
                $databaseVersion = $settings['whats-mark.wm_version']; // Replace with actual version retrieval

                // Compare versions using semantic versioning
                return version_compare($currentVersion, $databaseVersion, '>');
            } catch (\Exception $e) {
                whatsapp_log('Error checking database version', 'error', [
                    'error' => $e->getMessage(),
                ], $e);

                // If we can't check, assume no upgrade is needed
                return false;
            }
        }

        return false; // If the app is not installed, no upgrade is needed
    }
}
