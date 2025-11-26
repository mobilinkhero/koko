<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BotFlow extends BaseModel
{
    use BelongsToTenant, TracksFeatureUsage;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'flow_data',
        'is_active',
        'id',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFeatureSlug(): ?string
    {
        return 'bot_flow';
    }
}
