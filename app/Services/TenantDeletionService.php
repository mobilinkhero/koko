<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantDeletionService
{
    /**
     * Mark tenant for deletion by setting deleted_date
     * This just marks the tenant - actual deletion happens during cleanup
     */
    public function markTenantForDeletion(Tenant $tenant): bool
    {
        try {
            $tenant->deleted_date = now();
            $result = $tenant->save();

            app_log('Tenant marked for deletion', 'info', null, [
                'tenant_id' => $tenant->id,
                'subdomain' => $tenant->subdomain,
            ]);

            return $result;
        } catch (\Exception $e) {
            app_log('Failed to mark tenant for deletion', 'error', $e, [
                'tenant_id' => $tenant->id,
            ]);

            return false;
        }
    }

    /**
     * Permanently delete all tenant data
     * Called by cleanup command when subscription has ended
     */
    public function deleteAllTenantData(Tenant $tenant): void
    {
        app_log('Starting complete tenant data deletion', 'info', null, [
            'tenant_id' => $tenant->id,
            'subdomain' => $tenant->subdomain,
        ]);

        // Get all tables in the database
        $tables = DB::select('SHOW TABLES');
        $tableColumn = 'Tables_in_'.DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->{$tableColumn};

            // Skip system tables that should never be touched
            $systemTables = [
                'migrations', 'personal_access_tokens', 'password_reset_tokens',
                'failed_jobs', 'jobs', 'job_batches', 'cache', 'cache_locks',
                'sessions', 'countries', 'currencies', 'languages',
                'permissions', 'roles', 'role_has_permissions',
                'model_has_permissions', 'model_has_roles', 'tenants',
            ];

            if (in_array($tableName, $systemTables)) {
                continue;
            }

            // Drop tenant-specific tables completely
            if (str_contains($tableName, "tenant_{$tenant->id}_") ||
                str_starts_with($tableName, $tenant->subdomain.'_')) {
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");

                continue;
            }

            // Delete records from shared tables that have tenant_id column
            try {
                $hasColumn = DB::select("SHOW COLUMNS FROM `{$tableName}` LIKE 'tenant_id'");
                if (! empty($hasColumn)) {
                    DB::table($tableName)->where('tenant_id', $tenant->id)->delete();
                }
            } catch (\Exception $e) {
                app_log('Error processing table during deletion', 'error', $e, [
                    'tenant_id' => $tenant->id,
                    'table_name' => $tableName,
                ]);
            }
        }

        // Finally delete the tenant record
        $tenant->delete();

        app_log('Tenant deleted completely', 'info', null, [
            'tenant_id' => $tenant->id,
            'subdomain' => $tenant->subdomain,
            'message' => 'All tenant data including invoices, subscriptions, transactions, users, settings, bots, campaigns and tenant tables have been permanently deleted',
        ]);
    }

    /**
     * Restore tenant by removing deleted_date
     */
    public function restoreTenant(Tenant $tenant): bool
    {
        try {
            $tenant->deleted_date = null;
            $result = $tenant->save();

            app_log('Tenant restored from deletion', 'info', null, [
                'tenant_id' => $tenant->id,
                'subdomain' => $tenant->subdomain,
            ]);

            return $result;
        } catch (\Exception $e) {
            app_log('Failed to restore tenant', 'error', $e, [
                'tenant_id' => $tenant->id,
            ]);

            return false;
        }
    }
}
