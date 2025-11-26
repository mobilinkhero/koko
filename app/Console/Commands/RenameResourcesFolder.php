<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RenameResourcesFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:rename-resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rename Resources folder to resources in all modules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulesBasePath = base_path('Modules');

        if (! File::exists($modulesBasePath)) {
            $this->error('Modules directory does not exist.');

            return;
        }

        // Get all module directories
        $modules = File::directories($modulesBasePath);

        $this->info('Processing '.count($modules).' module(s)...');

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $oldPath = $modulePath.'/Resources';
            $newPath = $modulePath.'/resources';

            // Check if Resources folder exists
            if (! File::exists($oldPath)) {
                continue;
            }

            // Check if resources folder already exists
            if (File::exists($newPath)) {
                continue;
            }

            try {
                // Rename Resources to resources
                File::move($oldPath, $newPath);
            } catch (\Exception $e) {
                $this->line("  ❌ Module '{$moduleName}': Failed to rename - ".$e->getMessage());
            }
        }

        $this->info('Resources folder rename operation completed.');
    }
}
