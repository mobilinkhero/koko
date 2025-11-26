<?php

namespace Corbital\ModuleManager\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'version',
        'item_id',
        'payload',
        'active',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;
}
