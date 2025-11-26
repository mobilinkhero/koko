<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;

class Group extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
