<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EcommerceUserSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'contact_id', 
        'phone',
        'current_step',
        'session_data',
        'expires_at'
    ];

    protected $casts = [
        'session_data' => 'array',
        'expires_at' => 'datetime'
    ];

    /**
     * Get or create session for user
     */
    public static function getOrCreate($tenantId, $contactId, $phone)
    {
        // Clean expired sessions first
        self::where('expires_at', '<', now())->delete();
        
        return self::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'contact_id' => $contactId,
                'phone' => $phone
            ],
            [
                'current_step' => 'idle',
                'session_data' => [],
                'expires_at' => now()->addHours(2) // 2 hour session
            ]
        );
    }

    /**
     * Update session step and data
     */
    public function updateStep($step, $data = [])
    {
        $this->update([
            'current_step' => $step,
            'session_data' => array_merge($this->session_data ?? [], $data),
            'expires_at' => now()->addHours(2) // Extend session
        ]);
    }

    /**
     * Clear session
     */
    public function clearSession()
    {
        $this->update([
            'current_step' => 'idle',
            'session_data' => [],
            'expires_at' => now()->addHours(2)
        ]);
    }

    /**
     * Get session data value
     */
    public function getData($key, $default = null)
    {
        return data_get($this->session_data, $key, $default);
    }
}
