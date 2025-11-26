<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class WhatsappConnection
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $phone_number_id
 * @property string $phone_number
 * @property string $business_account_id
 * @property string $access_token
 * @property string|null $waba_id
 * @property string|null $app_id
 * @property string|null $app_secret
 * @property string|null $verified_name
 * @property string $status
 * @property bool $webhook_verified
 * @property Carbon|null $webhook_verified_at
 * @property Carbon|null $token_expires_at
 * @property array|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereAppSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereBusinessAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection wherePhoneNumberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereVerifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereWabaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereWebhookVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappConnection whereWebhookVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
class WhatsappConnection extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'webhook_verified' => 'bool',
        'webhook_verified_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'settings' => 'json',
    ];

    protected $hidden = [
        'access_token',
        'app_secret',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'phone_number_id',
        'phone_number',
        'business_account_id',
        'access_token',
        'waba_id',
        'app_id',
        'app_secret',
        'verified_name',
        'status',
        'webhook_verified',
        'webhook_verified_at',
        'token_expires_at',
        'settings',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
