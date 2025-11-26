<?php

namespace Corbital\Installer\Classes;

use Corbital\Installer\Installer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InstallFinalizer
{
    /**
     * The installer instance.
     */
    protected Installer $installer;

    /**
     * Create a new InstallFinalizer instance.
     */
    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }

    /**
     * Finalize the installation.
     *
     * @throws \Exception
     */
    public function handle(array $adminData): array
    {
        try {
            // 1. Create storage link
            $this->createStorageLink();

            // 2. Run migrations
            $this->runMigrations();

            // 3. Create admin user
            $user = $this->createAdminUser($adminData);

            if ($user && session('license_data')) {
                $update_checker = new UpdateChecker;
                $update_checker->installVersion(session('license_data'));
            }

            $this->runSeeders();

            // 4. Mark as installed
            $this->markAsInstalled();

            return [
                'success' => true,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create the storage link.
     *
     * @throws \Exception
     */
    protected function createStorageLink(): void
    {
        try {
            // Check if the symbolic link already exists
            $publicPath = public_path('storage');
            if (! file_exists($publicPath)) {
                Artisan::call('storage:link');
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to create storage link: '.$e->getMessage());
        }
    }

    /**
     * Run migrations.
     *
     * @throws \Exception
     */
    protected function runMigrations(): void
    {
        try {
            // Run all outstanding migrations
            Artisan::call('migrate', ['--force' => true]);

        } catch (\Exception $e) {
            throw new \Exception('Failed to run migrations: '.$e->getMessage());
        }
    }

    protected function runSeeders()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $e) {
            throw new \Exception('Failed to run migrations: '.$e->getMessage());
        }
    }

    /**
     * Create the admin user.
     *
     * @throws \Exception
     */
    protected function createAdminUser(array $adminData): object
    {
        try {
            // Get user model class from config
            $userModel = config('installer.admin_setup.model', '\App\Models\User');

            // Create admin user with proper fields
            $userData = [
                'firstname' => $adminData['firstname'] ?? null,
                'lastname' => $adminData['lastname'] ?? null,
                'email' => $adminData['email'],
                'password' => Hash::make($adminData['password']),
                'user_type' => 'admin',
            ];

            // Add email verification if configured
            $verifiedField = config('installer.admin_setup.verified_field');
            if ($verifiedField && Schema::hasColumn((new $userModel)->getTable(), $verifiedField)) {
                $userData[$verifiedField] = now();
            }

            // Add role field if configured
            $roleField = config('installer.admin_setup.role_field');
            $roleValue = config('installer.admin_setup.admin_role_value');
            if ($roleField && $roleValue !== null && Schema::hasColumn((new $userModel)->getTable(), $roleField)) {
                $userData[$roleField] = $roleValue;
            }

            // Add admin flag if configured
            $adminField = config('installer.admin_setup.admin_flag_field');
            $adminValue = config('installer.admin_setup.admin_flag_value');
            if ($adminField && $adminValue !== null && Schema::hasColumn((new $userModel)->getTable(), $adminField)) {
                $userData[$adminField] = $adminValue;
            }

            set_setting('system.timezone', $adminData['timezone'] ?? '');

            // Create the user and return it
            return $userModel::updateOrCreate(
                ['email' => $userData['email']], // If a user with this email exists, update it
                $userData
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to create admin user: '.$e->getMessage());
        }
    }

    /**
     * Mark the application as installed.
     *
     * @throws \Exception
     */
    protected function markAsInstalled(): void
    {
        if (! $this->installer->markAsInstalled()) {
            throw new \Exception('Failed to mark application as installed.');
        }
    }
}
