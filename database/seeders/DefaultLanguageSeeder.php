<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DefaultLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('tenant_languages')) {
            Schema::create('tenant_languages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 3)->unique();
                $table->timestamps();
            });
        }

        // Add to main languages table if not exists
        $exists = DB::table('languages')
            ->where('code', 'br')
            ->where('tenant_id', null)
            ->exists();

        if (! $exists) {
            DB::table('languages')->insert([
                'name' => 'Portuguese',
                'code' => 'br',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $source = public_path('lang').'/br.json';
            $destination = resource_path('lang/translations/br.json');

            File::ensureDirectoryExists(dirname($destination));
            if (File::exists($source)) {
                File::copy($source, $destination);
            } else {
                File::put($destination, '{}');
            }
        }

        if (Schema::hasTable('tenant_languages')) {
            // Add to tenant_languages table if not exists
            $tenantLanguageExists = DB::table('tenant_languages')
                ->where('code', 'br')
                ->exists();

            if (! $tenantLanguageExists) {
                DB::table('tenant_languages')->insert([
                    'name' => 'Portuguese',
                    'code' => 'br',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add language to all existing tenants if not exists
            $tenants = DB::table('tenants')->get();
            foreach ($tenants as $tenant) {
                $tenantLanguageExists = DB::table('languages')
                    ->where('code', 'br')
                    ->where('tenant_id', $tenant->id)
                    ->exists();

                if (! $tenantLanguageExists) {
                    DB::table('languages')->insert([
                        'name' => 'Portuguese',
                        'code' => 'br',
                        'tenant_id' => $tenant->id,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Create language file for this tenant
                    $tenantLangDir = resource_path("lang/translations/tenant/{$tenant->id}");
                    $tenantLangFile = "{$tenantLangDir}/tenant_br.json";

                    File::ensureDirectoryExists($tenantLangDir);

                    // First try to copy from public/lang source
                    $source = public_path('lang/tenant_br.json');
                    if (File::exists($source)) {
                        File::copy($source, $tenantLangFile);
                    } else {
                        File::put($tenantLangFile, '{}');
                    }
                }
            }
        }
    }
}
