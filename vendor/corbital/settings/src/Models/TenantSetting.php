<?php

namespace Corbital\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    protected $fillable = ['tenant_id', 'group', 'key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];
}
