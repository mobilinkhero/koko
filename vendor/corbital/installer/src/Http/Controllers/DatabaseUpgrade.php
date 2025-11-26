<?php

namespace Corbital\Installer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class DatabaseUpgrade extends Controller
{
    public function index()
    {
        $settings = get_batch_settings([
            'whats-mark.wm_version',
            'whats-mark.wm_verification_token',
            'whats-mark.wm_verification_id',
        ]);
        $data = [
            'currentVersion' => config('installer.license_verification.current_version'), // Replace with your actual version source
            'requiredVersion' => $settings['whats-mark.wm_version'],
        ];

        return view('installer::installation.database-upgrade', $data);
    }

    public function upgrade()
    {
        try {
            // 1. Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // 2. Run seeders if needed
            Artisan::call('db:seed', ['--force' => true]);

            // 3. Update version in database or settings
            set_setting('whats-mark.wm_version', config('installer.license_verification.current_version'));

            Artisan::call('files:cleanup', [
                '--file' => 'unused-files.json',
            ]);

            // Return a redirect URL instead of redirect()
            return response()->json([
                'message' => 'Database Upgraded Successfully',
                'redirect_url' => route('admin.dashboard'),
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database upgrade failed : '.$e->getMessage(),
            ], 500);
        }
    }
}
