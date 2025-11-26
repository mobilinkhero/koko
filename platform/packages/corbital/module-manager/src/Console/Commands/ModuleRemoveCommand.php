<?php

namespace Corbital\ModuleManager\Console\Commands;

use Corbital\ModuleManager\Facades\ModuleManager;
use Illuminate\Console\Command;

class ModuleRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:remove {name : The name of the module to remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        if (! ModuleManager::has($name)) {
            $this->error("[{$name}] module not found.");

            return 1;
        }

        if (ModuleManager::isCore($name)) {
            // Show a warning but allow removal with confirmation
            $this->warn("[{$name}] module is a core module. Removing it may affect system functionality.");
        }

        if (ModuleManager::isActive($name)) {
            $this->error("[{$name}] module is still active. Please deactivate it first using 'php artisan module:deactivate {$name}'");

            return 1;
        }

        if (! $this->confirm("Are you sure you want to remove the [{$name}] module?")) {
            $this->info('Operation cancelled.');

            return 0;
        }

        $result = ModuleManager::remove($name);

        if ($result['success']) {
            $this->info($result['message']);

            return 0;
        }

        $this->error($result['message']);

        return 1;
    }
}
