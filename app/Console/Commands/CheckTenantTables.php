<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class CheckTenantTables extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:check-tables {--tenant=1}';

    /**
     * The console command description.
     */
    protected $description = 'Check what tables exist for a tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = (int) $this->option('tenant');

        try {
            // Get tenant info
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant {$tenantId} not found");
                return 1;
            }

            $this->info("🔍 Checking tables for Tenant: {$tenantId} ({$tenant->subdomain})");
            $this->info("----------------------------------------");

            // Get database name
            $database = config('database.connections.mysql.database');
            $this->info("Database: {$database}");
            $this->info("----------------------------------------");

            // Check for tenant-specific tables
            $tables = DB::select("SHOW TABLES LIKE '%{$tenant->subdomain}%'");
            
            if (empty($tables)) {
                $this->error("❌ No tenant-specific tables found for subdomain: {$tenant->subdomain}");
                
                // Check for generic tables
                $this->info("🔍 Checking for generic tables...");
                $genericTables = ['contacts', 'ecommerce_configurations'];
                
                foreach ($genericTables as $tableName) {
                    $exists = DB::select("SHOW TABLES LIKE '{$tableName}'");
                    $status = $exists ? '✅' : '❌';
                    $this->line("{$status} {$tableName}");
                }
            } else {
                $this->info("✅ Found tenant-specific tables:");
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    $this->line("  - {$tableName}");
                }
            }

            // Specifically check for contacts table
            $contactsTable = $tenant->subdomain . '_contacts';
            $contactsExists = DB::select("SHOW TABLES LIKE '{$contactsTable}'");
            
            $this->info("----------------------------------------");
            if ($contactsExists) {
                $this->info("✅ Contacts table exists: {$contactsTable}");
                
                // Show table structure
                $columns = DB::select("DESCRIBE {$contactsTable}");
                $this->info("Columns:");
                foreach ($columns as $column) {
                    $this->line("  - {$column->Field} ({$column->Type})");
                }
            } else {
                $this->error("❌ Contacts table does not exist: {$contactsTable}");
                
                // Check if there's a generic contacts table
                $genericContacts = DB::select("SHOW TABLES LIKE 'contacts'");
                if ($genericContacts) {
                    $this->info("ℹ️  Generic 'contacts' table exists");
                } else {
                    $this->error("❌ No contacts table found at all");
                }
            }

            // Check ecommerce_configurations
            $ecommerceExists = DB::select("SHOW TABLES LIKE 'ecommerce_configurations'");
            if ($ecommerceExists) {
                $this->info("✅ E-commerce configurations table exists");
            } else {
                $this->error("❌ E-commerce configurations table does not exist");
            }

            $this->info("----------------------------------------");
            $this->info("🎉 Table check completed!");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
