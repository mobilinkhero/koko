<?php

namespace App\Multitenancy\Tasks;

use Illuminate\Support\Facades\Event;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantEventTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        // Fire tenant switched event
        Event::dispatch('tenant.switched', [$tenant]);

        // You can add more tenant-specific initialization here
        // like loading tenant-specific configuration, etc.
    }

    public function forgetCurrent(): void
    {
        // Fire tenant forgotten event
        Event::dispatch('tenant.forgotten');
    }
}
