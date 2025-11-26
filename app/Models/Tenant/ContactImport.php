<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactImport extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'file_path',
        'total_records',
        'processed_records',
        'valid_records',
        'invalid_records',
        'skipped_records',
        'status',
        'error_messages',
    ];

    protected $casts = [
        'error_messages' => 'array',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'valid_records' => 'integer',
        'invalid_records' => 'integer',
        'skipped_records' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    // Relationship to tenant
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    // Scope methods
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // Helper methods
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_records <= 0) {
            return 0;
        }

        return ($this->processed_records / $this->total_records) * 100;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->error_messages);
    }

    public function getErrorCount(): int
    {
        return count($this->error_messages ?? []);
    }

    public function getSuccessRate(): float
    {
        if ($this->processed_records <= 0) {
            return 0;
        }

        return ($this->valid_records / $this->processed_records) * 100;
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            default => 'gray'
        };
    }

    public function getStatusIcon(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING => 'clock',
            self::STATUS_COMPLETED => 'check-circle',
            self::STATUS_FAILED => 'x-circle',
            default => 'question-mark-circle'
        };
    }
}
