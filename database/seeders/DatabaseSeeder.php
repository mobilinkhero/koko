<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FeatureSeeder::class,
            CurrencySeeder::class,
            DefaultTaxSeeder::class,
            PermissionSeeder::class,
            LanguageSeeder::class,
            EmailLayoutSeeder::class,
            EmailTemplatesSeeder::class,
            DepartmentSeeder::class,
            FaqSeeder::class,
            TenantSettingsSeeder::class,
            DefaultFeatureSeeder::class,
            TenantWebhookFieldsSeeder::class,
            DefaultLanguageSeeder::class,
            TemplateFieldSeerder::class,
        ]);
    }
}
