<?php

namespace Corbital\ModuleManager\Console\Commands;

use Corbital\ModuleManager\Facades\ModuleManager;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available modules';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modules = ModuleManager::all();

        if (empty($modules)) {
            $this->info('No modules found.');

            return 0;
        }

        $headers = ['Name', 'Version', 'Description', 'Author', 'Status', 'Type'];
        $rows = [];

        foreach ($modules as $module) {
            $status = $module['active'] ? '<fg=green>Active</>' : '<fg=yellow>Inactive</>';
            $type = isset($module['info']['type']) && $module['info']['type'] === 'core'
                ? '<fg=blue;options=bold>CORE</>'
                : ($module['info']['type'] === 'custom' ? '<fg=cyan>CUSTOM</>' : '<fg=cyan>ADDON</>');

            // Get the description and truncate it if it's too long
            $description = $module['info']['description'] ?? 'No description available';
            $description = $this->truncateDescription($description, 50);

            $rows[] = [
                $module['name'],
                $module['info']['version'] ?? '1.0.0',
                $description,
                $module['info']['author'] ?? 'Unknown',
                $status,
                $type,
            ];
        }

        $this->table($headers, $rows);

        return 0;
    }

    /**
     * Truncate a string to a specific length and append ellipsis.
     */
    protected function truncateDescription(string $text, int $length = 50): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'...';
    }
}
