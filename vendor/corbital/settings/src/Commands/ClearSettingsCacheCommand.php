<?php

namespace Corbital\Settings\Commands;

use Corbital\Settings\Facades\Settings;
use Illuminate\Console\Command;

class ClearSettingsCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the settings cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->task('Clearing settings cache', function () {
            return Settings::clearCache();
        });

        return self::SUCCESS;
    }
}
