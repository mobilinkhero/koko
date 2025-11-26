<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use App\Traits\TracksFeatureUsage;
use Carbon\Carbon;

/**
 * Class AiPrompt
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiPrompt whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AiPrompt extends BaseModel
{
    use BelongsToTenant,TracksFeatureUsage;

    protected $casts = [
        'tenant_id' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'action',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFeatureSlug(): ?string
    {
        return 'ai_prompts';
    }
}
