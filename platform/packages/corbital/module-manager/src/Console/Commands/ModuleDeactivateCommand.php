<?php

namespace Corbital\ModuleManager\Console\Commands;

use Corbital\ModuleManager\Facades\ModuleManager;
use Illuminate\Console\Command;

class ModuleDeactivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:deactivate {name : The name of the module to deactivate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate a module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        if (! ModuleManager::has($name)) {
            $this->error(" [{$name}] module not found.");

            return 1;
        }

        if (! ModuleManager::isActive($name)) {
            $this->info(" [{$name}] module is already inactive.");

            return 0;
        }

        if (ModuleManager::isCore($name)) {
            // Show a warning but allow deactivation with confirmation
            $this->warn(" [{$name}] module is a core module. Deactivating it may affect system functionality.");
            if (! $this->confirm('Are you sure you want to deactivate this core module?')) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        if (ModuleManager::deactivate($name)) {
            $this->info(" [{$name}] module deactivated successfully.");

            return 0;
        }

        $this->error("Failed to deactivate  [{$name}] module. It may be required by other active modules.");

        return 1;
    }
}
