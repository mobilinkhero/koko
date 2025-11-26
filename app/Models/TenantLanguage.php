<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantLanguage extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the language file path in the public directory
     */
    public function getFilePathAttribute()
    {
        return public_path("lang/tenant_{$this->code}.json");
    }

    protected static function boot()
    {
        parent::boot();

        // When creating/updating
        static::saving(function ($language) {
            $language->code = strtolower($language->code);
        });

        // When deleting
        static::deleted(function ($language) {
            $filePath = public_path("lang/tenant_{$language->code}.json");
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        });
    }
}
