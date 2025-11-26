<?php

namespace App\Multitenancy\Tasks;

use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        // Set the tenant ID in the DB session for raw queries
        DB::statement("SET @current_tenant_id = {$tenant->getKey()}");

        // Set a global tenant ID variable that can be accessed elsewhere
        app()->instance('current_tenant_id', $tenant->getKey());
    }

    public function forgetCurrent(): void
    {
        // Reset the tenant ID in the DB session
        DB::statement('SET @current_tenant_id = NULL');

        // Remove the global tenant ID variable
        app()->forgetInstance('current_tenant_id');
    }
}
