<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class WhatsappTemplate
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $template_id
 * @property string $template_name
 * @property string $language
 * @property string $status
 * @property string $category
 * @property string|null $header_data_format
 * @property string|null $header_data_text
 * @property int|null $header_params_count
 * @property string $body_data
 * @property int|null $body_params_count
 * @property string|null $footer_data
 * @property int|null $footer_params_count
 * @property string|null $buttons_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereBodyData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereBodyParamsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereButtonsData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereFooterData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereFooterParamsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereHeaderDataFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereHeaderDataText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereHeaderParamsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereTemplateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappTemplate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class WhatsappTemplate extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'template_id' => 'int',
        'header_params_count' => 'int',
        'body_params_count' => 'int',
        'footer_params_count' => 'int',
    ];

    protected $fillable = [
        'tenant_id',
        'template_id',
        'template_name',
        'language',
        'status',
        'category',
        'header_data_format',
        'header_data_text',
        'header_params_count',
        'body_data',
        'body_params_count',
        'footer_data',
        'footer_params_count',
        'buttons_data',
        'updated_at',
        'header_file_url',
        'header_variable_value',
        'body_variable_value',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (function_exists('tenant_id')) {
                $model->tenant_id = $model->tenant_id ?? tenant_id();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
