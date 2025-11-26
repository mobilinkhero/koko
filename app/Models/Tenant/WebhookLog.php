<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class WebhookLog
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $event
 * @property string $model
 * @property string $url
 * @property string $status
 * @property int $attempt
 * @property array $payload
 * @property array|null $response
 * @property string|null $error_message
 * @property int|null $status_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant|null $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereStatusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereUrl($value)
 *
 * @mixin \Eloquent
 */
class WebhookLog extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'attempt' => 'int',
        'payload' => 'json',
        'response' => 'json',
        'status_code' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'event',
        'model',
        'url',
        'status',
        'attempt',
        'payload',
        'response',
        'error_message',
        'status_code',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
