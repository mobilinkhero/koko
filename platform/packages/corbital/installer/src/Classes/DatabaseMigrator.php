<?php

namespace Corbital\Installer\Classes;

use Exception;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseMigrator
{
    /**
     * Migration status constants.
     */
    const STATUS_SUCCESS = 'success';

    const STATUS_FAILED = 'failed';

    const STATUS_SKIPPED = 'skipped';

    /**
     * Migration results.
     */
    protected array $migrationResults = [];

    /**
     * The migrator instance.
     */
    protected Migrator $migrator;

    /**
     * Initialize a new Migration instance.
     */
    public function __construct()
    {
        // Resolve the migrator from the container to avoid direct dependency on the interface
        $this->migrator = App::make('migrator');
    }

    /**
     * Run the application migrations.
     *
     * @param  bool  $pretend  Run the migrations in "pretend" mode
     * @param  callable|null  $callback  Optional callback for each migration
     * @return array Migration results
     */
    public function run(bool $pretend = false, ?callable $callback = null): array
    {
        $this->migrationResults = [];

        try {
            $this->prepareDatabase();

            // Get and run all migration files
            $migrationFiles = $this->getAllMigrationFiles();

            if (empty($migrationFiles)) {
                $this->logInfo('No migrations to run.');

                return $this->migrationResults;
            }

            $this->logInfo('Running '.count($migrationFiles).' migrations...');

            // Set up options for migrations
            $options = [
                'pretend' => $pretend,
            ];

            // Run the migrations
            $this->migrator->run($migrationFiles, $options);

            // Process results
            foreach ($migrationFiles as $file => $path) {
                $migrationName = $this->getMigrationName($file);
                $this->migrationResults[$migrationName] = [
                    'status' => self::STATUS_SUCCESS,
                    'file' => $file,
                ];

                if ($callback) {
                    $callback($migrationName, self::STATUS_SUCCESS);
                }
            }

            $this->logInfo('Migrations completed successfully.');

        } catch (Exception $e) {
            $this->logError('Migration failed: '.$e->getMessage());

            // Record the failed migration
            if (isset($migrationName)) {
                $this->migrationResults[$migrationName] = [
                    'status' => self::STATUS_FAILED,
                    'file' => $file ?? null,
                    'error' => $e->getMessage(),
                ];

                if ($callback) {
                    $callback($migrationName, self::STATUS_FAILED, $e->getMessage());
                }
            }
        }

        return $this->migrationResults;
    }

    /**
     * Rollback the last batch of migrations.
     *
     * @param  int  $steps  Number of migration batches to rollback
     * @param  bool  $pretend  Run the migrations in "pretend" mode
     * @return array Rollback results
     */
    public function rollback(int $steps = 1, bool $pretend = false): array
    {
        $results = [];

        try {
            $this->logInfo("Rolling back {$steps} migration batch(es)...");

            // Set up options for rollback
            $options = [
                'pretend' => $pretend,
                'step' => $steps,
            ];

            $migrationsRolledBack = $this->migrator->rollback(
                $this->getMigrationPaths(),
                $options
            );

            foreach ($migrationsRolledBack as $migration) {
                $migrationName = $this->getMigrationName($migration);
                $results[$migrationName] = [
                    'status' => self::STATUS_SUCCESS,
                    'file' => $migration,
                ];
            }

            $this->logInfo('Rollback completed successfully.');

        } catch (Exception $e) {
            $this->logError('Rollback failed: '.$e->getMessage());

            if (isset($migrationName)) {
                $results[$migrationName] = [
                    'status' => self::STATUS_FAILED,
                    'file' => $migration ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Check whether the application requires migrations to be run.
     */
    public function needed(): bool
    {
        try {
            $ranMigrations = $this->migrator->getRepository()->getRan();
            $allMigrations = $this->getAllMigrationFiles();

            return count($allMigrations) > count($ranMigrations);
        } catch (QueryException $e) {
            // If migrations table doesn't exist yet, migrations are needed
            $this->logWarning('Error checking migrations: '.$e->getMessage());

            return true;
        }
    }

    /**
     * Get pending migrations that need to be run.
     *
     * @return array Array of pending migration files
     */
    public function getPendingMigrations(): array
    {
        try {
            $ranMigrations = $this->migrator->getRepository()->getRan();
            $allMigrations = $this->getAllMigrationFiles();

            // Filter out migrations that have already been run
            return array_filter($allMigrations, function ($migration) use ($ranMigrations) {
                return ! in_array($this->getMigrationName($migration), $ranMigrations);
            });
        } catch (QueryException $e) {
            $this->logWarning('Error getting pending migrations: '.$e->getMessage());

            return $this->getAllMigrationFiles();
        }
    }

    /**
     * Get an array of all migration files.
     */
    protected function getAllMigrationFiles(): array
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }

    /**
     * Get all the migration paths.
     */
    protected function getMigrationPaths(): array
    {
        // Include additional migration paths if needed
        return array_merge($this->migrator->paths(), [$this->getBaseMigrationPath()]);
    }

    /**
     * Get the base migration path.
     */
    protected function getBaseMigrationPath(): string
    {
        return database_path('migrations');
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase(): void
    {
        if (! $this->repositoryExists()) {
            $this->logInfo('Creating migrations table...');
            Artisan::call('migrate:install');
        }
    }

    /**
     * Check if the migration repository exists.
     */
    protected function repositoryExists(): bool
    {
        // Use a retry mechanism to handle potential database connection issues
        $attempts = 0;
        $maxAttempts = 3;
        $delay = 500; // milliseconds

        while ($attempts < $maxAttempts) {
            try {
                return $this->migrator->repositoryExists();
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                usleep($delay * 1000);
                $delay *= 2; // Exponential backoff
            }
        }

        return false;
    }

    /**
     * Extract migration name from filename.
     */
    protected function getMigrationName(string $migration): string
    {
        return str_replace('.php', '', basename($migration));
    }

    /**
     * Log an info message.
     */
    protected function logInfo(string $message): void
    {
        Log::info('[Database Migrator] '.$message);
    }

    /**
     * Log an error message.
     */
    protected function logError(string $message): void
    {
        Log::error('[Database Migrator] '.$message);
    }

    /**
     * Log a warning message.
     */
    protected function logWarning(string $message): void
    {
        Log::warning('[Database Migrator] '.$message);
    }

    /**
     * Get migration results.
     */
    public function getMigrationResults(): array
    {
        return $this->migrationResults;
    }
}
