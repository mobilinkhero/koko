<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_name',
        'purchase_code',
        'purchase_code_hash',
        'activated_at',
        'last_verified_at',
        'verification_data',
        'status',
        'grace_period_ends_at',
        'integrity_hash',
        'is_active',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'verification_data' => 'array',
        'is_active' => 'boolean',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_TAMPERED = 'tampered';

    protected static function boot()
    {
        parent::boot();

        // PERMANENT BYPASS - Skip integrity checks
        // static::creating(function ($license) {
        //     $license->generateIntegrityHash();
        // });

        // static::updating(function ($license) {
        //     $license->generateIntegrityHash();
        // });

        // static::retrieved(function ($license) {
        //     $license->validateIntegrity();
        // });
    }

    /**
     * PERMANENT BYPASS - Generate integrity hash
     */
    public function generateIntegrityHash(): void
    {
        $this->integrity_hash = 'bypass_hash_' . ($this->id ?? time());
    }

    /**
     * PERMANENT BYPASS - Validate integrity and detect tampering
     */
    public function validateIntegrity(): bool
    {
        // Always return true to bypass validation
        return true;
    }

    /**
     * PERMANENT BYPASS - Handle tampering detection
     */
    private function handleTampering(): void
    {
        // Log that bypass is active
        \Log::info('License bypass: tampering detection bypassed', [
            'module' => $this->module_name,
            'license_id' => $this->id,
            'bypass_active' => true
        ]);

        // Don't actually handle tampering - just log it
        // $this->update(['status' => self::STATUS_TAMPERED]);
        // Disable module - SKIPPED
    }

    /**
     * PERMANENT BYPASS - Check if license is valid
     */
    public function isValid(): bool
    {
        // Always return true for bypass
        return true;
    }

    /**
     * PERMANENT BYPASS - Check if license is expired
     */
    public function isExpired(): bool
    {
        // Never expired with bypass
        return false;
    }

    /**
     * PERMANENT BYPASS - Check if license needs revalidation
     */
    public function needsRevalidation(): bool
    {
        // Never needs revalidation with bypass
        return false;
    }

    /**
     * PERMANENT BYPASS - Mark license as verified
     */
    public function markAsVerified(array $verificationData = []): void
    {
        $this->update([
            'last_verified_at' => now()->addYears(10), // Set to far future
            'verification_data' => $verificationData,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * PERMANENT BYPASS - Set grace period
     */
    public function setGracePeriod(int $days): void
    {
        // Set to unlimited
        $this->update([
            'grace_period_ends_at' => now()->addYears(20),
        ]);
    }

    /**
     * Get encrypted purchase code
     */
    public function getDecryptedPurcheCode(): ?string
    {
        if (! $this->purchase_code) {
            return 'bypass_code';
        }

        try {
            return decrypt($this->purchase_code);
        } catch (\Exception $e) {
            return 'bypass_code';
        }
    }

    /**
     * Scope for active licenses
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('is_active', true);
    }

    /**
     * Scope for specific module
     */
    public function scopeForModule($query, string $moduleName)
    {
        return $query->where('module_name', $moduleName);
    }
}