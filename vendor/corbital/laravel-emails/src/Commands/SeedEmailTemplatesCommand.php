<?php

namespace Corbital\LaravelEmails\Commands;

use Corbital\LaravelEmails\Database\Seeders\EmailLayoutSeeder;
use Corbital\LaravelEmails\Database\Seeders\EmailTemplateSeeder;
use Illuminate\Console\Command;

class SeedEmailTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:seed-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default email templates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Seeding email layouts...');

        // First seed layouts
        $layoutSeeder = new EmailLayoutSeeder;
        $layoutSeeder->run();

        $this->info('Email layouts seeded successfully!');

        $this->info('Seeding email templates...');

        $templateSeeder = new EmailTemplateSeeder;
        $templateSeeder->run();

        $this->info('Email templates seeded successfully!');

        return 0;
    }
}
