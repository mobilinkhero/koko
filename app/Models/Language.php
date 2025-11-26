<?php

namespace App\Models;

use App\Events\Language\LanguageEvent;
use App\Facades\AdminCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $code
 * @property bool|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Language extends BaseModel
{
    protected $fillable = ['name', 'code', 'tenant_id', 'status'];

    protected $casts = [
        'status' => 'bool',
    ];

    /**
     * Get the tenant that owns the language.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear admin cache when languages are modified
        static::created(function (Language $language) {
            $tenant = current_tenant();
            $baseKey = $tenant ? "{$tenant->id}_tenant_languages" : 'languages';
            Cache::forget($baseKey);
            event(LanguageEvent::created($language));
        });

        static::updated(function (Language $language) {
            $tenant = current_tenant();
            $baseKey = $tenant ? "{$tenant->id}_tenant_languages" : 'languages';
            Cache::forget($baseKey);
            event(LanguageEvent::updated($language));
        });

        static::deleted(function (Language $language) {
            $tenant = current_tenant();
            $baseKey = $tenant ? "{$tenant->id}_tenant_languages" : 'languages';
            $translationKey = $tenant ? "{$tenant->id}_tenant_{$language->code}" : $language->code;
            Cache::forget($baseKey);
            Cache::forget("translations.{$translationKey}");
            event(LanguageEvent::deleted($language));
        });
    }

    /**
     * Get cached languages using AdminCache
     */
    public static function getCached()
    {
        $isTenant = current_tenant();
        $baseKey = $isTenant ? "{$isTenant->id}_tenant_languages" : 'languages';

        return Cache::remember($baseKey, 3600, function () {
            return static::query()
                ->when(current_tenant(), function ($query) {
                    $query->where('tenant_id', tenant_id());
                }, function ($query) {
                    $query->whereNull('tenant_id');
                })
                ->get();
        });
    }
}
