<?php

namespace Corbital\Settings\Commands;

use Corbital\Settings\Facades\Settings;
use Illuminate\Console\Command;

class RefreshSettingsCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the settings cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->task('Refreshing settings cache', function () {
            return Settings::refreshCache();
        });

        return self::SUCCESS;
    }
}
