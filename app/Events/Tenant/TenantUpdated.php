<?php

namespace App\Events\Tenant;

use App\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public array $changedAttributes = []
    ) {}
}
