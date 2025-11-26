<?php

namespace Corbital\ModuleManager\Console\Commands;

use Corbital\ModuleManager\Facades\ModuleManager;
use Illuminate\Console\Command;

class ModuleActivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:activate {name : The name of the module to activate} {--skip-migrations : Skip running migrations} {--skip-seeders : Skip running seeders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate a module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $skipMigrations = $this->option('skip-migrations');
        $skipSeeders = $this->option('skip-seeders');

        if (! ModuleManager::has($name)) {
            $this->error("{$name} module not found.");

            return 1;
        }

        if (ModuleManager::isActive($name)) {
            $this->info("{$name} module is already active.");

            return 0;
        }

        // Override config settings based on command options
        if ($skipMigrations) {
            config(['modules.auto_migrations.enabled' => false]);
        }

        if ($skipSeeders) {
            config(['modules.auto_migrations.seed' => false]);
        }

        $startTime = microtime(true);

        $result = ModuleManager::activate($name);
        $success = is_array($result) ? ($result['success'] ?? false) : (bool) $result;

        if ($success) {
            $this->info("{$name} module activated successfully.");

            // Show migration and seeder info if not skipped
            $autoMigrate = config('modules.auto_migrations.enabled', true);
            $autoSeed = config('modules.auto_migrations.seed', true);

            if ($autoMigrate) {
                $this->info("Migrations for [{$name}] module were processed automatically.");
            }

            if ($autoMigrate && $autoSeed) {
                $this->info("Seeders for [{$name}] module were run automatically.");
            }

            $elapsedTime = round(microtime(true) - $startTime, 2);
            $this->info("Activation completed in {$elapsedTime} seconds.");

            return 0;
        }

        // Get error message from result if available
        $errorMessage = is_array($result) ? ($result['message'] ?? "Failed to activate [{$name}] module.") : "Failed to activate [{$name}] module.";
        $this->error($errorMessage);

        return 1;
    }
}
